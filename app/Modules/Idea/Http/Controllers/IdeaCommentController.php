<?php

declare(strict_types=1);

namespace App\Modules\Idea\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Idea\Contracts\IdeaServiceInterface;
use App\Modules\Idea\Models\Idea;
use App\Modules\Idea\Models\IdeaComment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class IdeaCommentController extends Controller
{
    public function __construct(
        private readonly IdeaServiceInterface $ideaService
    ) {}

    public function store(Request $request, Idea $idea): RedirectResponse
    {
        $request->validate([
            'content' => 'required|string|max:10000',
            'parent_id' => 'nullable|exists:idea_comments,id',
        ]);

        $parentId = $request->input('parent_id');
        $this->ideaService->addComment(
            $idea,
            $request->input('content'),
            $request->user(),
            $parentId ? (int) $parentId : null
        );

        return back()->with('success', 'Comment added successfully.');
    }

    public function update(Request $request, IdeaComment $comment): RedirectResponse
    {
        $user = $request->user();

        if (!$comment->canEdit($user)) {
            return back()->with('error', 'You do not have permission to edit this comment.');
        }

        $request->validate([
            'content' => 'required|string|max:10000',
        ]);

        $this->ideaService->updateComment($comment, $request->input('content'), $user);

        return back()->with('success', 'Comment updated successfully.');
    }

    public function destroy(IdeaComment $comment): RedirectResponse
    {
        $user = auth()->user();

        if (!$comment->canDelete($user)) {
            return back()->with('error', 'You do not have permission to delete this comment.');
        }

        $this->ideaService->deleteComment($comment, $user);

        return back()->with('success', 'Comment deleted successfully.');
    }
}
