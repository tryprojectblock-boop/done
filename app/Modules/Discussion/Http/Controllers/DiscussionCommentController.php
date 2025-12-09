<?php

declare(strict_types=1);

namespace App\Modules\Discussion\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Discussion\Contracts\DiscussionServiceInterface;
use App\Modules\Discussion\Models\Discussion;
use App\Modules\Discussion\Models\DiscussionComment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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

        $this->discussionService->addComment(
            $discussion,
            $request->input('content'),
            $user,
            $request->input('parent_id'),
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
}
