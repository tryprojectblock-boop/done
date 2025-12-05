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

    public function store(Request $request, Discussion $discussion): RedirectResponse
    {
        $user = $request->user();

        if (!$discussion->canComment($user)) {
            return back()->with('error', 'You do not have permission to comment on this discussion.');
        }

        $request->validate([
            'content' => ['required', 'string', 'max:10000'],
            'parent_id' => ['nullable', 'exists:discussion_comments,id'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'max:10240'],
        ]);

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
