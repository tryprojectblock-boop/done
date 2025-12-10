<?php

declare(strict_types=1);

namespace App\Modules\Document\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Document\Models\Document;
use App\Modules\Document\Models\DocumentPage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DocumentPageController extends Controller
{
    /**
     * Get all pages for a document.
     */
    public function index(string $documentUuid): JsonResponse
    {
        $document = Document::where('uuid', $documentUuid)->firstOrFail();
        $user = auth()->user();

        if (!$document->canView($user)) {
            return response()->json(['error' => 'Access denied.'], 403);
        }

        $pages = $document->pages()->get();

        return response()->json([
            'success' => true,
            'pages' => $pages->map(fn($page) => [
                'id' => $page->id,
                'uuid' => $page->uuid,
                'title' => $page->title,
                'sort_order' => $page->sort_order,
                'last_edited_at' => $page->last_edited_at?->toIso8601String(),
            ]),
        ]);
    }

    /**
     * Get a specific page content.
     */
    public function show(string $documentUuid, string $pageUuid): JsonResponse
    {
        $document = Document::where('uuid', $documentUuid)->firstOrFail();
        $user = auth()->user();

        if (!$document->canView($user)) {
            return response()->json(['error' => 'Access denied.'], 403);
        }

        $page = DocumentPage::where('uuid', $pageUuid)
            ->where('document_id', $document->id)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'page' => [
                'id' => $page->id,
                'uuid' => $page->uuid,
                'title' => $page->title,
                'content' => $page->content,
                'sort_order' => $page->sort_order,
                'last_edited_at' => $page->last_edited_at?->toIso8601String(),
                'last_edited_by' => $page->lastEditor ? [
                    'id' => $page->lastEditor->id,
                    'name' => $page->lastEditor->name,
                ] : null,
            ],
        ]);
    }

    /**
     * Create a new page.
     */
    public function store(Request $request, string $documentUuid): JsonResponse
    {
        $document = Document::where('uuid', $documentUuid)->firstOrFail();
        $user = $request->user();

        if (!$document->canEdit($user)) {
            return response()->json(['error' => 'You cannot edit this document.'], 403);
        }

        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string', 'max:500000'],
            'sort_order' => ['nullable', 'integer', 'min:1'],
        ]);

        $page = DocumentPage::create([
            'document_id' => $document->id,
            'title' => $request->input('title'),
            'content' => $request->input('content', ''),
            'sort_order' => $request->input('sort_order'),
            'created_by' => $user->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Page created.',
            'page' => [
                'id' => $page->id,
                'uuid' => $page->uuid,
                'title' => $page->title,
                'content' => $page->content,
                'sort_order' => $page->sort_order,
            ],
        ]);
    }

    /**
     * Update a page's title.
     */
    public function update(Request $request, string $documentUuid, string $pageUuid): JsonResponse
    {
        $document = Document::where('uuid', $documentUuid)->firstOrFail();
        $user = $request->user();

        if (!$document->canEdit($user)) {
            return response()->json(['error' => 'You cannot edit this document.'], 403);
        }

        $page = DocumentPage::where('uuid', $pageUuid)
            ->where('document_id', $document->id)
            ->firstOrFail();

        $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
        ]);

        if ($request->has('title')) {
            $page->update(['title' => $request->input('title')]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Page updated.',
            'page' => [
                'id' => $page->id,
                'uuid' => $page->uuid,
                'title' => $page->title,
                'sort_order' => $page->sort_order,
            ],
        ]);
    }

    /**
     * Save page content.
     */
    public function saveContent(Request $request, string $documentUuid, string $pageUuid): JsonResponse
    {
        $document = Document::where('uuid', $documentUuid)->firstOrFail();
        $user = $request->user();

        if (!$document->canEdit($user)) {
            return response()->json(['error' => 'You cannot edit this document.'], 403);
        }

        $page = DocumentPage::where('uuid', $pageUuid)
            ->where('document_id', $document->id)
            ->firstOrFail();

        $request->validate([
            'content' => ['required', 'string', 'max:500000'],
            'title' => ['nullable', 'string', 'max:255'],
        ]);

        $updateData = [
            'content' => $request->input('content'),
            'last_edited_by' => $user->id,
            'last_edited_at' => now(),
        ];

        if ($request->has('title') && $request->input('title')) {
            $updateData['title'] = $request->input('title');
        }

        $page->update($updateData);

        // Also update the parent document's last edited
        $document->updateLastEdited($user);

        return response()->json([
            'success' => true,
            'message' => 'Page saved.',
            'last_edited_at' => $page->last_edited_at->toIso8601String(),
        ]);
    }

    /**
     * Delete a page.
     */
    public function destroy(Request $request, string $documentUuid, string $pageUuid): JsonResponse
    {
        $document = Document::where('uuid', $documentUuid)->firstOrFail();
        $user = $request->user();

        if (!$document->canEdit($user)) {
            return response()->json(['error' => 'You cannot edit this document.'], 403);
        }

        $page = DocumentPage::where('uuid', $pageUuid)
            ->where('document_id', $document->id)
            ->firstOrFail();

        // Don't allow deleting the last page
        $pageCount = $document->pages()->count();
        if ($pageCount <= 1) {
            return response()->json(['error' => 'Cannot delete the last page.'], 400);
        }

        $page->delete();

        // Reorder remaining pages
        $document->pages()->orderBy('sort_order')->get()->each(function ($p, $index) {
            $p->update(['sort_order' => $index + 1]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Page deleted.',
        ]);
    }

    /**
     * Reorder pages.
     */
    public function reorder(Request $request, string $documentUuid): JsonResponse
    {
        $document = Document::where('uuid', $documentUuid)->firstOrFail();
        $user = $request->user();

        if (!$document->canEdit($user)) {
            return response()->json(['error' => 'You cannot edit this document.'], 403);
        }

        $request->validate([
            'pages' => ['required', 'array'],
            'pages.*' => ['required', 'string'], // page UUIDs in order
        ]);

        $pageUuids = $request->input('pages');

        foreach ($pageUuids as $index => $uuid) {
            DocumentPage::where('uuid', $uuid)
                ->where('document_id', $document->id)
                ->update(['sort_order' => $index + 1]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Pages reordered.',
        ]);
    }
}
