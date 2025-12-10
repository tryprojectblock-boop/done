<?php

declare(strict_types=1);

namespace App\Modules\Document\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Document\Contracts\DocumentServiceInterface;
use App\Modules\Document\Http\Requests\StoreDocumentRequest;
use App\Modules\Document\Http\Requests\UpdateDocumentRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DocumentController extends Controller
{
    public function __construct(
        private readonly DocumentServiceInterface $documentService
    ) {}

    public function index(Request $request): View|\Illuminate\Http\JsonResponse
    {
        $user = $request->user();

        $filters = [
            'search' => $request->get('search'),
            'sort' => $request->get('sort', 'last_edited_at'),
            'direction' => $request->get('direction', 'desc'),
        ];

        $documents = $this->documentService->getDocumentsForUser($user, $filters, 20);

        // Handle AJAX request for real-time search
        if ($request->ajax() || $request->get('ajax')) {
            $html = '';
            if ($documents->isEmpty()) {
                $html = '<div class="card bg-base-100 shadow col-span-full">
                    <div class="card-body text-center py-12">
                        <div class="flex justify-center mb-4">
                            <span class="icon-[tabler--file-text] size-16 text-base-content/20"></span>
                        </div>
                        <h3 class="text-lg font-semibold text-base-content">No documents found</h3>
                        <p class="text-base-content/60">Try a different search term</p>
                    </div>
                </div>';
            } else {
                $html = '<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">';
                foreach ($documents as $document) {
                    $html .= view('document::partials.document-card-new', ['document' => $document])->render();
                }
                $html .= '</div>';
            }

            return response()->json([
                'html' => $html,
                'total' => $documents->total(),
            ]);
        }

        return view('document::index', compact('documents', 'filters'));
    }

    public function create(): View
    {
        $user = auth()->user();
        $members = \App\Models\User::where('company_id', $user->company_id)
            ->where('id', '!=', $user->id)
            ->orderBy('name')
            ->get();

        $workspaces = \App\Modules\Workspace\Models\Workspace::where(function ($query) use ($user) {
                $query->where('owner_id', $user->id)
                    ->orWhereHas('members', function ($q) use ($user) {
                        $q->where('user_id', $user->id);
                    });
            })
            ->orderBy('name')
            ->get();

        return view('document::create', compact('members', 'workspaces'));
    }

    public function store(StoreDocumentRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Handle collaborators with individual roles
        $collaborators = $request->input('collaborators', []);
        $data['collaborators'] = [];
        foreach ($collaborators as $collab) {
            if (!empty($collab['user_id'])) {
                $data['collaborators'][] = [
                    'user_id' => $collab['user_id'],
                    'role' => $collab['role'] ?? 'editor',
                ];
            }
        }

        $document = $this->documentService->createDocument($data, $request->user());

        return redirect()
            ->route('documents.show', $document->uuid)
            ->with('success', 'Document created successfully!');
    }

    public function show(string $uuid): View
    {
        $document = $this->documentService->getDocumentByUuid($uuid);

        if (!$document) {
            abort(404);
        }

        $user = auth()->user();

        if (!$document->canView($user)) {
            abort(403, 'You don\'t have permission to view this document.');
        }

        $canEdit = $document->canEdit($user);

        return view('document::show', compact('document', 'user', 'canEdit'));
    }

    public function edit(string $uuid): View
    {
        $document = $this->documentService->getDocumentByUuid($uuid);

        if (!$document) {
            abort(404);
        }

        $user = auth()->user();

        if (!$document->canEdit($user)) {
            abort(403);
        }

        return view('document::edit', compact('document'));
    }

    public function update(UpdateDocumentRequest $request, string $uuid): RedirectResponse
    {
        $document = $this->documentService->getDocumentByUuid($uuid);

        if (!$document) {
            abort(404);
        }

        $user = $request->user();

        if (!$document->canEdit($user)) {
            abort(403);
        }

        $data = $request->validated();

        $this->documentService->updateDocument($document, $data, $user);

        return redirect()
            ->route('documents.show', $document->uuid)
            ->with('success', 'Document updated successfully!');
    }

    public function destroy(string $uuid): RedirectResponse
    {
        $document = $this->documentService->getDocumentByUuid($uuid);

        if (!$document) {
            abort(404);
        }

        $user = auth()->user();

        if (!$document->canDelete($user)) {
            abort(403);
        }

        $this->documentService->deleteDocument($document, $user);

        return redirect()
            ->route('documents.index')
            ->with('success', 'Document deleted successfully!');
    }

    public function versions(string $uuid): View
    {
        $document = $this->documentService->getDocumentByUuid($uuid);

        if (!$document) {
            abort(404);
        }

        $user = auth()->user();

        if (!$document->canViewVersions($user)) {
            abort(403);
        }

        $versions = $this->documentService->getVersions($document);

        return view('document::versions', compact('document', 'versions'));
    }

    public function viewVersion(string $uuid, int $versionId): View
    {
        $document = $this->documentService->getDocumentByUuid($uuid);

        if (!$document) {
            abort(404);
        }

        $user = auth()->user();

        if (!$document->canViewVersions($user)) {
            abort(403);
        }

        $version = $this->documentService->getVersionById($document, $versionId);

        if (!$version) {
            abort(404, 'Version not found');
        }

        // Get previous and next versions for navigation
        $previousVersion = $document->versions()
            ->where('version_number', '<', $version->version_number)
            ->orderBy('version_number', 'desc')
            ->first();

        $nextVersion = $document->versions()
            ->where('version_number', '>', $version->version_number)
            ->orderBy('version_number', 'asc')
            ->first();

        $canRestore = $document->canRestoreVersion($user);

        return view('document::version-view', compact('document', 'version', 'previousVersion', 'nextVersion', 'canRestore'));
    }

    public function restoreVersion(Request $request, string $uuid, int $versionId): RedirectResponse
    {
        $document = $this->documentService->getDocumentByUuid($uuid);

        if (!$document) {
            abort(404);
        }

        $user = $request->user();

        if (!$document->canRestoreVersion($user)) {
            abort(403);
        }

        $version = $this->documentService->getVersionById($document, $versionId);

        if (!$version) {
            abort(404, 'Version not found');
        }

        $this->documentService->restoreVersion($document, $version->version_number, $user);

        return redirect()
            ->route('documents.show', $document->uuid)
            ->with('success', "Document restored to version {$version->version_number}.");
    }
}
