<?php

declare(strict_types=1);

namespace App\Modules\Discussion\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Discussion\Contracts\DiscussionServiceInterface;
use App\Modules\Discussion\Models\Discussion;
use App\Modules\Discussion\Models\DiscussionComment;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DiscussionCommentController extends Controller
{
    public function __construct(
        private readonly DiscussionServiceInterface $discussionService
    ) {}

    private const MAX_ATTACHMENT_SIZE_KB = 10240; // 10MB
    private const MAX_ATTACHMENTS_COUNT = 10;

    public function store(Request $request, Discussion $discussion): RedirectResponse
    {
        $user = $request->user();

        if (!$discussion->canComment($user)) {
            return back()->with('error', 'You do not have permission to comment on this discussion.');
        }

        // Pre-check Content-Length header (defense in depth)
        $contentLength = $request->header('Content-Length');
        $maxContentLength = self::MAX_ATTACHMENT_SIZE_KB * 1024 * self::MAX_ATTACHMENTS_COUNT;
        if ($contentLength !== null && (int) $contentLength > $maxContentLength) {
            return back()->with('error', 'Request size exceeds the maximum allowed size.');
        }

        $request->validate([
            'content' => ['required', 'string', 'max:10000'],
            'parent_id' => ['nullable', 'exists:discussion_comments,id'],
            'attachments' => ['nullable', 'array', 'max:' . self::MAX_ATTACHMENTS_COUNT],
            'attachments.*' => ['file', 'max:' . self::MAX_ATTACHMENT_SIZE_KB],
        ]);

        // Post-check: Verify actual file sizes (defense in depth)
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                if ($file->getSize() > self::MAX_ATTACHMENT_SIZE_KB * 1024) {
                    return back()->with('error', 'One or more attachments exceed the maximum allowed size of 10MB.');
                }
            }
        }

        $attachments = $request->hasFile('attachments') ? $request->file('attachments') : [];

        $parentId = $request->input('parent_id');

        $this->discussionService->addComment(
            $discussion,
            $request->input('content'),
            $user,
            $parentId ? (int) $parentId : null,
            $attachments
        );

        return back()->with('success', 'Comment added successfully!');
    }

    public function update(Request $request, DiscussionComment $comment): RedirectResponse
    {
        $user = $request->user();

        if (!$comment->canEdit($user)) {
            return back()->with('error', 'You do not have permission to edit this comment.');
        }

        $request->validate([
            'content' => ['required', 'string', 'max:10000'],
        ]);

        $this->discussionService->updateComment($comment, $request->input('content'), $user);

        return back()->with('success', 'Comment updated successfully!');
    }

    public function destroy(DiscussionComment $comment): RedirectResponse
    {
        $user = auth()->user();

        if (!$comment->canDelete($user)) {
            return back()->with('error', 'You do not have permission to delete this comment.');
        }

        $this->discussionService->deleteComment($comment, $user);

        return back()->with('success', 'Comment deleted successfully!');
    }

    /**
     * Poll for new comments since a given timestamp
     */
    public function poll(Request $request, Discussion $discussion): JsonResponse
    {
        $user = $request->user();

        if (!$discussion->canView($user)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $lastTs = $request->input('last_ts');
        $lastTimestamp = null;

        if ($lastTs) {
            try {
                $lastTimestamp = Carbon::parse($lastTs);
            } catch (\Exception $e) {
                $lastTimestamp = null;
            }
        }

        // Get new top-level comments since the timestamp (newest first)
        $newTopLevelQuery = $discussion->comments()
            ->whereNull('parent_id')
            ->with(['user', 'replies.user', 'attachments', 'replies.attachments'])
            ->orderBy('created_at', 'desc');

        if ($lastTimestamp) {
            $newTopLevelQuery->where('created_at', '>', $lastTimestamp);
        }

        $newTopLevelComments = $newTopLevelQuery->get();

        // Also get parent comments that have new replies since the timestamp
        $updatedParentIds = [];
        if ($lastTimestamp) {
            $updatedParentIds = $discussion->comments()
                ->whereNotNull('parent_id')
                ->where('created_at', '>', $lastTimestamp)
                ->pluck('parent_id')
                ->unique()
                ->toArray();
        }

        // Render new top-level comments
        $commentsHtml = [];
        foreach ($newTopLevelComments as $comment) {
            $commentsHtml[] = [
                'id' => $comment->id,
                'html' => view('discussion::partials.comment', [
                    'comment' => $comment,
                    'discussion' => $discussion,
                ])->render(),
                'created_at' => $comment->created_at->toIso8601String(),
                'type' => 'new',
            ];
        }

        // Render updated parent comments (with new replies)
        $updatedComments = [];
        if (!empty($updatedParentIds)) {
            $parentsWithNewReplies = $discussion->comments()
                ->whereIn('id', $updatedParentIds)
                ->whereNull('parent_id')
                ->with(['user', 'replies.user', 'attachments', 'replies.attachments'])
                ->get();

            foreach ($parentsWithNewReplies as $comment) {
                // Skip if this comment is already in the new comments list
                if ($newTopLevelComments->contains('id', $comment->id)) {
                    continue;
                }

                $updatedComments[] = [
                    'id' => $comment->id,
                    'html' => view('discussion::partials.comment', [
                        'comment' => $comment,
                        'discussion' => $discussion,
                    ])->render(),
                    'created_at' => $comment->created_at->toIso8601String(),
                    'type' => 'updated',
                ];
            }
        }

        // Get the latest timestamp from all comments (including replies)
        $latestComment = $discussion->comments()
            ->orderBy('created_at', 'desc')
            ->first();

        $latestTs = $latestComment
            ? $latestComment->created_at->format('Y-m-d\TH:i:s.u\Z')
            : ($lastTs ?: now()->format('Y-m-d\TH:i:s.u\Z'));

        return response()->json([
            'comments' => $commentsHtml,
            'updated_comments' => $updatedComments,
            'last_ts' => $latestTs,
            'count' => $discussion->comments()->count(),
        ]);
    }
}
