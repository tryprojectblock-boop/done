<?php

declare(strict_types=1);

namespace App\Modules\Document\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Document\Contracts\DocumentServiceInterface;
use App\Modules\Document\Http\Requests\StoreCommentRequest;
use App\Modules\Document\Models\Document;
use App\Modules\Document\Models\DocumentComment;
use App\Modules\Document\Models\DocumentCommentReply;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DocumentCommentController extends Controller
{
    public function __construct(
        private readonly DocumentServiceInterface $documentService
    ) {}

    public function store(StoreCommentRequest $request, string $documentUuid): JsonResponse
    {
        $document = Document::where('uuid', $documentUuid)->firstOrFail();
        $user = $request->user();

        if (!$document->canComment($user)) {
            return response()->json(['error' => 'You cannot comment on this document.'], 403);
        }

        $comment = $this->documentService->addComment($document, $request->validated(), $user);

        // Return rendered HTML for AJAX insertion
        $html = view('document::partials.comment', ['comment' => $comment])->render();

        return response()->json([
            'success' => true,
            'html' => $html,
            'comment' => [
                'id' => $comment->id,
                'uuid' => $comment->uuid,
                'content' => $comment->content,
                'selection_id' => $comment->selection_id,
                'selection_text' => $comment->selection_text,
                'selection_start' => $comment->selection_start,
                'selection_end' => $comment->selection_end,
                'is_resolved' => $comment->is_resolved,
                'created_at' => $comment->created_at->diffForHumans(),
                'user' => [
                    'id' => $comment->user->id,
                    'name' => $comment->user->name,
                    'avatar_url' => $comment->user->avatar_url,
                ],
            ],
        ]);
    }

    public function update(Request $request, int $commentId): JsonResponse
    {
        $comment = DocumentComment::findOrFail($commentId);
        $user = $request->user();

        if (!$comment->canEdit($user)) {
            return response()->json(['error' => 'You cannot edit this comment.'], 403);
        }

        $request->validate([
            'content' => ['required', 'string', 'max:10000'],
        ]);

        $comment = $this->documentService->updateComment($comment, $request->input('content'), $user);

        return response()->json([
            'success' => true,
            'comment' => [
                'id' => $comment->id,
                'uuid' => $comment->uuid,
                'content' => $comment->content,
                'is_edited' => $comment->is_edited,
                'edited_at' => $comment->edited_at?->diffForHumans(),
            ],
        ]);
    }

    public function destroy(int $commentId): JsonResponse
    {
        $comment = DocumentComment::findOrFail($commentId);
        $user = auth()->user();

        if (!$comment->canDelete($user)) {
            return response()->json(['error' => 'You cannot delete this comment.'], 403);
        }

        $this->documentService->deleteComment($comment, $user);

        return response()->json(['success' => true]);
    }

    public function resolve(int $commentId): JsonResponse
    {
        $comment = DocumentComment::with('document')->findOrFail($commentId);
        $user = auth()->user();

        if (!$comment->canResolve($user)) {
            return response()->json(['error' => 'You cannot resolve this comment.'], 403);
        }

        $comment = $this->documentService->resolveComment($comment, $user);

        return response()->json([
            'success' => true,
            'comment' => [
                'id' => $comment->id,
                'uuid' => $comment->uuid,
                'is_resolved' => $comment->is_resolved,
                'resolved_at' => $comment->resolved_at?->diffForHumans(),
                'resolved_by' => $comment->resolvedByUser ? [
                    'id' => $comment->resolvedByUser->id,
                    'name' => $comment->resolvedByUser->name,
                ] : null,
            ],
        ]);
    }

    public function unresolve(int $commentId): JsonResponse
    {
        $comment = DocumentComment::with('document')->findOrFail($commentId);
        $user = auth()->user();

        if (!$comment->canResolve($user)) {
            return response()->json(['error' => 'You cannot unresolve this comment.'], 403);
        }

        $comment = $this->documentService->unresolveComment($comment, $user);

        return response()->json([
            'success' => true,
            'comment' => [
                'id' => $comment->id,
                'uuid' => $comment->uuid,
                'is_resolved' => $comment->is_resolved,
            ],
        ]);
    }

    public function storeReply(Request $request, int $commentId): JsonResponse
    {
        $comment = DocumentComment::with('document')->findOrFail($commentId);
        $user = $request->user();

        if (!$comment->document->canComment($user)) {
            return response()->json(['error' => 'You cannot reply to this comment.'], 403);
        }

        $request->validate([
            'content' => ['required', 'string', 'max:10000'],
        ]);

        $reply = $this->documentService->addReply($comment, $request->input('content'), $user);

        // Return rendered HTML for AJAX insertion
        $html = view('document::partials.reply', ['reply' => $reply])->render();

        return response()->json([
            'success' => true,
            'html' => $html,
            'reply' => [
                'id' => $reply->id,
                'uuid' => $reply->uuid,
                'content' => $reply->content,
                'created_at' => $reply->created_at->diffForHumans(),
                'user' => [
                    'id' => $reply->user->id,
                    'name' => $reply->user->name,
                    'avatar_url' => $reply->user->avatar_url,
                ],
            ],
        ]);
    }

    public function updateReply(Request $request, int $replyId): JsonResponse
    {
        $reply = DocumentCommentReply::findOrFail($replyId);
        $user = $request->user();

        if (!$reply->canEdit($user)) {
            return response()->json(['error' => 'You cannot edit this reply.'], 403);
        }

        $request->validate([
            'content' => ['required', 'string', 'max:10000'],
        ]);

        $reply = $this->documentService->updateReply($reply, $request->input('content'), $user);

        return response()->json([
            'success' => true,
            'reply' => [
                'id' => $reply->id,
                'uuid' => $reply->uuid,
                'content' => $reply->content,
                'is_edited' => $reply->is_edited,
                'edited_at' => $reply->edited_at?->diffForHumans(),
            ],
        ]);
    }

    public function destroyReply(int $replyId): JsonResponse
    {
        $reply = DocumentCommentReply::with('comment.document')->findOrFail($replyId);
        $user = auth()->user();

        if (!$reply->canDelete($user)) {
            return response()->json(['error' => 'You cannot delete this reply.'], 403);
        }

        $this->documentService->deleteReply($reply, $user);

        return response()->json(['success' => true]);
    }
}
