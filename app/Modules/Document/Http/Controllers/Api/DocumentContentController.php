<?php

declare(strict_types=1);

namespace App\Modules\Document\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Document\Contracts\DocumentServiceInterface;
use App\Modules\Document\Models\Document;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DocumentContentController extends Controller
{
    public function __construct(
        private readonly DocumentServiceInterface $documentService
    ) {}

    /**
     * Get document content.
     */
    public function getContent(string $uuid): JsonResponse
    {
        $document = Document::where('uuid', $uuid)->firstOrFail();
        $user = auth()->user();

        if (!$document->canView($user)) {
            return response()->json(['error' => 'Access denied.'], 403);
        }

        return response()->json([
            'success' => true,
            'content' => $document->content,
            'last_edited_at' => $document->last_edited_at?->toIso8601String(),
            'last_edited_by' => $document->lastEditor ? [
                'id' => $document->lastEditor->id,
                'name' => $document->lastEditor->name,
            ] : null,
        ]);
    }

    /**
     * Save document content (manual save - creates version).
     */
    public function save(Request $request, string $uuid): JsonResponse
    {
        $document = Document::where('uuid', $uuid)->firstOrFail();
        $user = $request->user();

        if (!$document->canEdit($user)) {
            return response()->json(['error' => 'You cannot edit this document.'], 403);
        }

        $request->validate([
            'content' => ['required', 'string', 'max:500000'],
            'title' => ['nullable', 'string', 'max:255'],
            'summary' => ['nullable', 'string', 'max:255'],
        ]);

        // Update title if provided
        if ($request->has('title') && $request->input('title')) {
            $document->update(['title' => $request->input('title')]);
        }

        $document = $this->documentService->updateContent(
            $document,
            $request->input('content'),
            $user,
            $request->input('summary')
        );

        return response()->json([
            'success' => true,
            'message' => 'Document saved.',
            'version' => $document->version_count,
            'last_edited_at' => $document->last_edited_at->toIso8601String(),
        ]);
    }

    /**
     * Auto-save document content (may or may not create version).
     */
    public function autoSave(Request $request, string $uuid): JsonResponse
    {
        $document = Document::where('uuid', $uuid)->firstOrFail();
        $user = $request->user();

        if (!$document->canEdit($user)) {
            return response()->json(['error' => 'You cannot edit this document.'], 403);
        }

        $request->validate([
            'content' => ['required', 'string', 'max:500000'],
            'title' => ['nullable', 'string', 'max:255'],
        ]);

        // Update title if provided
        if ($request->has('title') && $request->input('title')) {
            $document->update(['title' => $request->input('title')]);
        }

        $document = $this->documentService->autoSave(
            $document,
            $request->input('content'),
            $user
        );

        return response()->json([
            'success' => true,
            'message' => 'Auto-saved.',
            'last_edited_at' => $document->last_edited_at->toIso8601String(),
        ]);
    }

    /**
     * Add a collaborator to the document.
     */
    public function addCollaborator(Request $request, string $uuid): JsonResponse
    {
        $document = Document::where('uuid', $uuid)->firstOrFail();
        $user = $request->user();

        if (!$document->canInvite($user)) {
            return response()->json(['error' => 'You cannot invite collaborators to this document.'], 403);
        }

        $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'role' => ['required', 'in:editor,reader'],
        ]);

        $this->documentService->addCollaborator(
            $document,
            (int) $request->input('user_id'),
            $request->input('role'),
            $user
        );

        $collaborator = \App\Models\User::find($request->input('user_id'));

        return response()->json([
            'success' => true,
            'message' => 'Collaborator added.',
            'collaborator' => [
                'id' => $collaborator->id,
                'name' => $collaborator->name,
                'email' => $collaborator->email,
                'avatar_url' => $collaborator->avatar_url,
                'role' => $request->input('role'),
            ],
        ]);
    }

    /**
     * Update a collaborator's role.
     */
    public function updateCollaboratorRole(Request $request, string $uuid, int $userId): JsonResponse
    {
        $document = Document::where('uuid', $uuid)->firstOrFail();
        $user = $request->user();

        if (!$document->canInvite($user)) {
            return response()->json(['error' => 'You cannot manage collaborators for this document.'], 403);
        }

        $request->validate([
            'role' => ['required', 'in:editor,reader'],
        ]);

        $this->documentService->updateCollaboratorRole(
            $document,
            $userId,
            $request->input('role')
        );

        return response()->json([
            'success' => true,
            'message' => 'Role updated.',
        ]);
    }

    /**
     * Remove a collaborator from the document.
     */
    public function removeCollaborator(Request $request, string $uuid, int $userId): JsonResponse
    {
        $document = Document::where('uuid', $uuid)->firstOrFail();
        $user = $request->user();

        if (!$document->canInvite($user)) {
            return response()->json(['error' => 'You cannot manage collaborators for this document.'], 403);
        }

        $this->documentService->removeCollaborator($document, $userId);

        return response()->json([
            'success' => true,
            'message' => 'Collaborator removed.',
        ]);
    }
}
