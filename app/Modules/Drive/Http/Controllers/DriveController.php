<?php

declare(strict_types=1);

namespace App\Modules\Drive\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Discussion\Models\DiscussionAttachment;
use App\Modules\Discussion\Models\DiscussionCommentAttachment;
use App\Modules\Drive\Models\DriveAttachment;
use App\Modules\Drive\Models\DriveAttachmentTag;
use App\Modules\Task\Models\TaskAttachment;
use App\Modules\Task\Models\TaskCommentAttachment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DriveController extends Controller
{
    // 10GB storage limit in bytes
    private const STORAGE_LIMIT = 10 * 1024 * 1024 * 1024;
    // 500MB max file size in KB
    private const MAX_FILE_SIZE_KB = 512000;

    public function index(Request $request): View
    {
        $user = $request->user();
        $companyId = $user->company_id;
        $company = $user->company;
        $driveTab = $request->get('drive_tab', 'block');

        $filters = [
            'type' => $request->get('type'),
            'source' => $request->get('source'),
            'search' => $request->get('search'),
            'sort' => $request->get('sort', 'created_at'),
            'direction' => $request->get('direction', 'desc'),
        ];

        // Collect all attachments from different sources
        $attachments = collect();

        // Drive Attachments (direct uploads) - accessible by user
        if (!$filters['source'] || $filters['source'] === 'drive') {
            $driveAttachments = DriveAttachment::query()
                ->where('company_id', $companyId)
                ->where(function ($q) use ($user) {
                    $q->where('uploaded_by', $user->id)
                        ->orWhereHas('sharedWith', function ($sq) use ($user) {
                            $sq->where('user_id', $user->id);
                        });
                })
                ->when($user->isAdminOrHigher(), function ($q) use ($companyId) {
                    // Admins can see all company files
                    $q->orWhere('company_id', $companyId);
                })
                ->with(['uploader:id,first_name,last_name,avatar_path', 'tags'])
                ->get()
                ->map(function ($attachment) use ($user) {
                    return $this->normalizeDriveAttachment($attachment, $user);
                });
            $attachments = $attachments->merge($driveAttachments);
        }

        // Task Attachments
        if (!$filters['source'] || $filters['source'] === 'tasks') {
            $taskAttachments = TaskAttachment::query()
                ->whereHas('task', function ($query) use ($companyId) {
                    $query->where('company_id', $companyId);
                })
                ->with(['task:id,uuid,title,task_number', 'uploader:id,first_name,last_name,avatar_path'])
                ->get()
                ->map(function ($attachment) {
                    return $this->normalizeAttachment($attachment, 'task');
                });
            $attachments = $attachments->merge($taskAttachments);

            // Task Comment Attachments
            $taskCommentAttachments = TaskCommentAttachment::query()
                ->whereHas('comment.task', function ($query) use ($companyId) {
                    $query->where('company_id', $companyId);
                })
                ->with(['comment.task:id,uuid,title,task_number'])
                ->get()
                ->map(function ($attachment) {
                    return $this->normalizeAttachment($attachment, 'task_comment');
                });
            $attachments = $attachments->merge($taskCommentAttachments);
        }

        // Discussion Attachments
        if (!$filters['source'] || $filters['source'] === 'discussions') {
            $discussionAttachments = DiscussionAttachment::query()
                ->whereHas('discussion', function ($query) use ($companyId) {
                    $query->where('company_id', $companyId);
                })
                ->with(['discussion:id,uuid,title', 'uploader:id,first_name,last_name,avatar_path'])
                ->get()
                ->map(function ($attachment) {
                    return $this->normalizeAttachment($attachment, 'discussion');
                });
            $attachments = $attachments->merge($discussionAttachments);

            // Discussion Comment Attachments
            $discussionCommentAttachments = DiscussionCommentAttachment::query()
                ->whereHas('comment.discussion', function ($query) use ($companyId) {
                    $query->where('company_id', $companyId);
                })
                ->with(['comment.discussion:id,uuid,title'])
                ->get()
                ->map(function ($attachment) {
                    return $this->normalizeAttachment($attachment, 'discussion_comment');
                });
            $attachments = $attachments->merge($discussionCommentAttachments);
        }

        // Filter by type
        if ($filters['type'] && $filters['type'] !== 'all') {
            $attachments = $attachments->filter(function ($attachment) use ($filters) {
                return $attachment['file_category'] === $filters['type'];
            });
        }

        // Filter by search
        if ($filters['search']) {
            $search = strtolower($filters['search']);
            $attachments = $attachments->filter(function ($attachment) use ($search) {
                return str_contains(strtolower($attachment['filename']), $search) ||
                       str_contains(strtolower($attachment['name'] ?? ''), $search) ||
                       str_contains(strtolower($attachment['parent_title'] ?? ''), $search);
            });
        }

        // Sort
        $sortField = $filters['sort'] === 'name' ? 'filename' : 'created_at';
        $attachments = $filters['direction'] === 'asc'
            ? $attachments->sortBy($sortField)
            : $attachments->sortByDesc($sortField);

        // Calculate stats
        $stats = [
            'total' => $attachments->count(),
            'total_size' => $attachments->sum('size'),
            'images' => $attachments->where('file_category', 'images')->count(),
            'documents' => $attachments->where('file_category', 'documents')->count(),
            'videos' => $attachments->where('file_category', 'videos')->count(),
            'audio' => $attachments->where('file_category', 'audio')->count(),
            'other' => $attachments->where('file_category', 'other')->count(),
        ];

        // Get storage usage for company
        $storageUsed = $this->getCompanyStorageUsed($companyId);
        $storageLimit = self::STORAGE_LIMIT;
        $storagePercentage = min(100, ($storageUsed / $storageLimit) * 100);

        // Paginate manually
        $page = $request->get('page', 1);
        $perPage = 24;
        $total = $attachments->count();
        $attachments = $attachments->forPage($page, $perPage)->values();

        // Check Google Drive status
        $settings = $company->settings ?? [];
        $googleDriveEnabled = $settings['google_drive_enabled'] ?? false;

        return view('drive::index', compact(
            'attachments',
            'filters',
            'stats',
            'page',
            'perPage',
            'total',
            'storageUsed',
            'storageLimit',
            'storagePercentage',
            'driveTab',
            'googleDriveEnabled'
        ));
    }

    public function create(Request $request): View
    {
        $user = $request->user();
        $companyId = $user->company_id;

        // Check storage limit
        $storageUsed = $this->getCompanyStorageUsed($companyId);
        $storageRemaining = self::STORAGE_LIMIT - $storageUsed;

        // Get team members for sharing
        $teamMembers = User::where('company_id', $companyId)
            ->where('id', '!=', $user->id)
            ->where('role', '!=', User::ROLE_GUEST)
            ->orderBy('first_name')
            ->get();

        // Get existing tags
        $existingTags = DriveAttachmentTag::forCompany($companyId)
            ->orderBy('name')
            ->get();

        return view('drive::create', compact(
            'teamMembers',
            'existingTags',
            'storageUsed',
            'storageRemaining'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $companyId = $user->company_id;

        // Pre-check Content-Length header (defense in depth)
        $contentLength = $request->header('Content-Length');
        $maxContentLengthBytes = self::MAX_FILE_SIZE_KB * 1024;
        if ($contentLength !== null && (int) $contentLength > $maxContentLengthBytes) {
            return back()
                ->withInput()
                ->with('error', 'File size exceeds the maximum allowed size of 500MB.');
        }

        $request->validate([
            'file' => ['required', 'file', 'max:' . self::MAX_FILE_SIZE_KB], // 500MB max per file
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:50'],
            'share_with' => ['nullable', 'array'],
            'share_with.*' => ['integer', 'exists:users,id'],
        ]);

        // Check storage limit
        $file = $request->file('file');
        $fileSize = $file->getSize();

        // Post-check: Verify actual file size (defense in depth)
        if ($fileSize > $maxContentLengthBytes) {
            return back()
                ->withInput()
                ->with('error', 'File size exceeds the maximum allowed size of 500MB.');
        }
        $storageUsed = $this->getCompanyStorageUsed($companyId);

        if (($storageUsed + $fileSize) > self::STORAGE_LIMIT) {
            return back()
                ->withInput()
                ->with('error', 'Storage limit exceeded. You have ' . $this->formatSize(self::STORAGE_LIMIT - $storageUsed) . ' remaining.');
        }

        // Upload file to DigitalOcean Spaces
        $originalFilename = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $storagePath = 'drive/' . $companyId . '/' . Str::uuid() . '.' . $extension;

        Storage::disk('do_spaces')->putFileAs(
            dirname($storagePath),
            $file,
            basename($storagePath),
            'public'
        );

        // Create attachment record
        $attachment = DriveAttachment::create([
            'company_id' => $companyId,
            'uploaded_by' => $user->id,
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'original_filename' => $originalFilename,
            'file_path' => $storagePath,
            'mime_type' => $file->getMimeType(),
            'file_size' => $fileSize,
        ]);

        // Handle tags
        if ($request->has('tags')) {
            $tagIds = [];
            foreach ($request->input('tags') as $tagName) {
                $tag = DriveAttachmentTag::firstOrCreate(
                    ['company_id' => $companyId, 'name' => trim($tagName)],
                    ['color' => $this->generateTagColor()]
                );
                $tagIds[] = $tag->id;
            }
            $attachment->tags()->sync($tagIds);
        }

        // Share with team members
        if ($request->has('share_with')) {
            foreach ($request->input('share_with') as $userId) {
                $shareUser = User::find($userId);
                if ($shareUser && $shareUser->company_id === $companyId) {
                    $attachment->shareWith($shareUser, $user);
                }
            }
        }

        return redirect()
            ->route('drive.index')
            ->with('success', 'File uploaded successfully!');
    }

    public function show(string $uuid): View
    {
        $user = auth()->user();

        $attachment = DriveAttachment::where('uuid', $uuid)
            ->with(['uploader', 'tags', 'sharedWith'])
            ->firstOrFail();

        if (!$attachment->canView($user)) {
            abort(403);
        }

        return view('drive::show', compact('attachment'));
    }

    public function edit(string $uuid): View
    {
        $user = auth()->user();
        $companyId = $user->company_id;

        $attachment = DriveAttachment::where('uuid', $uuid)
            ->with(['uploader', 'tags', 'sharedWith'])
            ->firstOrFail();

        if (!$attachment->canEdit($user)) {
            abort(403);
        }

        $teamMembers = User::where('company_id', $companyId)
            ->where('id', '!=', $attachment->uploaded_by)
            ->where('role', '!=', User::ROLE_GUEST)
            ->orderBy('first_name')
            ->get();

        $existingTags = DriveAttachmentTag::forCompany($companyId)
            ->orderBy('name')
            ->get();

        return view('drive::edit', compact('attachment', 'teamMembers', 'existingTags'));
    }

    public function update(Request $request, string $uuid): RedirectResponse
    {
        $user = $request->user();
        $companyId = $user->company_id;

        $attachment = DriveAttachment::where('uuid', $uuid)->firstOrFail();

        if (!$attachment->canEdit($user)) {
            abort(403);
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:50'],
            'share_with' => ['nullable', 'array'],
            'share_with.*' => ['integer', 'exists:users,id'],
        ]);

        $attachment->update([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
        ]);

        // Handle tags
        $tagIds = [];
        if ($request->has('tags')) {
            foreach ($request->input('tags') as $tagName) {
                $tag = DriveAttachmentTag::firstOrCreate(
                    ['company_id' => $companyId, 'name' => trim($tagName)],
                    ['color' => $this->generateTagColor()]
                );
                $tagIds[] = $tag->id;
            }
        }
        $attachment->tags()->sync($tagIds);

        // Update sharing
        $shareWith = $request->input('share_with', []);
        $currentShares = $attachment->sharedWith->pluck('id')->toArray();

        // Remove shares
        foreach (array_diff($currentShares, $shareWith) as $userId) {
            $attachment->unshareWith(User::find($userId));
        }

        // Add new shares
        foreach (array_diff($shareWith, $currentShares) as $userId) {
            $shareUser = User::find($userId);
            if ($shareUser && $shareUser->company_id === $companyId) {
                $attachment->shareWith($shareUser, $user);
            }
        }

        return redirect()
            ->route('drive.index')
            ->with('success', 'File updated successfully!');
    }

    public function destroy(string $uuid): RedirectResponse
    {
        $user = auth()->user();

        $attachment = DriveAttachment::where('uuid', $uuid)->firstOrFail();

        if (!$attachment->canDelete($user)) {
            abort(403);
        }

        // Delete from storage
        Storage::disk('do_spaces')->delete($attachment->file_path);

        // Delete record (soft delete)
        $attachment->delete();

        return redirect()
            ->route('drive.index')
            ->with('success', 'File deleted successfully!');
    }

    public function download(string $uuid)
    {
        $user = auth()->user();

        $attachment = DriveAttachment::where('uuid', $uuid)->firstOrFail();

        if (!$attachment->canView($user)) {
            abort(403);
        }

        return Storage::disk('do_spaces')->download($attachment->file_path, $attachment->original_filename);
    }

    public function tags(Request $request): JsonResponse
    {
        $user = $request->user();
        $companyId = $user->company_id;

        $tags = DriveAttachmentTag::forCompany($companyId)
            ->when($request->get('search'), function ($q, $search) {
                $q->where('name', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'name', 'color']);

        return response()->json($tags);
    }

    private function getCompanyStorageUsed(int $companyId): int
    {
        return (int) DriveAttachment::where('company_id', $companyId)->sum('file_size');
    }

    private function generateTagColor(): string
    {
        $colors = ['#3b82f6', '#22c55e', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#06b6d4', '#84cc16'];
        return $colors[array_rand($colors)];
    }

    private function normalizeDriveAttachment(DriveAttachment $attachment, User $user): array
    {
        return [
            'id' => $attachment->id,
            'uuid' => $attachment->uuid,
            'filename' => $attachment->original_filename,
            'name' => $attachment->name,
            'description' => $attachment->description,
            'mime_type' => $attachment->mime_type,
            'size' => $attachment->file_size,
            'formatted_size' => $attachment->formatted_size,
            'url' => $attachment->url,
            'source' => 'drive',
            'parent_title' => $attachment->name,
            'parent_url' => route('drive.show', $attachment->uuid),
            'parent_type' => 'Drive',
            'uploader' => $attachment->uploader,
            'created_at' => $attachment->created_at,
            'is_image' => $attachment->is_image,
            'file_category' => $attachment->file_category,
            'icon' => $attachment->icon,
            'tags' => $attachment->tags,
            'is_shared' => $attachment->uploaded_by !== $user->id,
            'can_edit' => $attachment->canEdit($user),
            'can_delete' => $attachment->canDelete($user),
        ];
    }

    private function normalizeAttachment($attachment, string $source): array
    {
        $filename = $attachment->original_name ?? $attachment->original_filename ?? 'Unknown';
        $mimeType = $attachment->mime_type ?? '';
        $size = $attachment->file_size ?? $attachment->size ?? 0;

        $parentTitle = '';
        $parentUrl = '';
        $parentType = '';

        switch ($source) {
            case 'task':
                $parentTitle = $attachment->task?->title ?? 'Unknown Task';
                $parentUrl = $attachment->task ? route('tasks.show', $attachment->task->uuid) : '#';
                $parentType = 'Task';
                break;
            case 'task_comment':
                $parentTitle = $attachment->comment?->task?->title ?? 'Unknown Task';
                $parentUrl = $attachment->comment?->task ? route('tasks.show', $attachment->comment->task->uuid) : '#';
                $parentType = 'Task Comment';
                break;
            case 'discussion':
                $parentTitle = $attachment->discussion?->title ?? 'Unknown Discussion';
                $parentUrl = $attachment->discussion ? route('discussions.show', $attachment->discussion->uuid) : '#';
                $parentType = 'Discussion';
                break;
            case 'discussion_comment':
                $parentTitle = $attachment->comment?->discussion?->title ?? 'Unknown Discussion';
                $parentUrl = $attachment->comment?->discussion ? route('discussions.show', $attachment->comment->discussion->uuid) : '#';
                $parentType = 'Discussion Comment';
                break;
        }

        $url = '';
        if (method_exists($attachment, 'getUrl')) {
            $url = $attachment->getUrl();
        } elseif (isset($attachment->url)) {
            $url = $attachment->url;
        } elseif ($attachment->file_path ?? $attachment->path ?? null) {
            $url = asset('storage/' . ($attachment->file_path ?? $attachment->path));
        }

        $uploader = null;
        if ($attachment->uploader ?? null) {
            $uploader = $attachment->uploader;
        }

        return [
            'id' => $attachment->id,
            'uuid' => null,
            'filename' => $filename,
            'name' => null,
            'description' => null,
            'mime_type' => $mimeType,
            'size' => $size,
            'formatted_size' => $this->formatSize($size),
            'url' => $url,
            'source' => $source,
            'parent_title' => $parentTitle,
            'parent_url' => $parentUrl,
            'parent_type' => $parentType,
            'uploader' => $uploader,
            'created_at' => $attachment->created_at,
            'is_image' => $this->isImage($mimeType),
            'file_category' => $this->getFileCategory($mimeType),
            'icon' => $this->getFileIcon($mimeType),
            'tags' => collect(),
            'is_shared' => false,
            'can_edit' => false,
            'can_delete' => false,
        ];
    }

    private function formatSize(int $bytes): string
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' B';
    }

    private function isImage(string $mimeType): bool
    {
        return str_starts_with($mimeType, 'image/');
    }

    private function getFileCategory(string $mimeType): string
    {
        if (str_starts_with($mimeType, 'image/')) {
            return 'images';
        }
        if (str_starts_with($mimeType, 'video/')) {
            return 'videos';
        }
        if (str_starts_with($mimeType, 'audio/')) {
            return 'audio';
        }
        if (in_array($mimeType, [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'text/plain',
            'text/csv',
        ])) {
            return 'documents';
        }
        return 'other';
    }

    private function getFileIcon(string $mimeType): string
    {
        if (str_starts_with($mimeType, 'image/')) {
            return 'tabler--photo';
        }
        if (str_starts_with($mimeType, 'video/')) {
            return 'tabler--video';
        }
        if (str_starts_with($mimeType, 'audio/')) {
            return 'tabler--music';
        }
        if ($mimeType === 'application/pdf') {
            return 'tabler--file-type-pdf';
        }
        if (in_array($mimeType, ['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])) {
            return 'tabler--file-type-doc';
        }
        if (in_array($mimeType, ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])) {
            return 'tabler--file-type-xls';
        }
        if (in_array($mimeType, ['application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation'])) {
            return 'tabler--file-type-ppt';
        }
        if (str_starts_with($mimeType, 'text/')) {
            return 'tabler--file-type-txt';
        }
        if (str_contains($mimeType, 'zip') || str_contains($mimeType, 'rar') || str_contains($mimeType, 'tar')) {
            return 'tabler--file-zip';
        }
        return 'tabler--file';
    }
}
