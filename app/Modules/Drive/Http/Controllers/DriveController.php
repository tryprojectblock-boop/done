<?php

declare(strict_types=1);

namespace App\Modules\Drive\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Discussion\Models\DiscussionAttachment;
use App\Modules\Discussion\Models\DiscussionCommentAttachment;
use App\Modules\Task\Models\TaskAttachment;
use App\Modules\Task\Models\TaskCommentAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DriveController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $companyId = $user->company_id;

        $filters = [
            'type' => $request->get('type'), // all, images, documents, videos, audio
            'source' => $request->get('source'), // all, tasks, discussions
            'search' => $request->get('search'),
            'sort' => $request->get('sort', 'created_at'),
            'direction' => $request->get('direction', 'desc'),
        ];

        // Collect all attachments from different sources
        $attachments = collect();

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

        // Paginate manually
        $page = $request->get('page', 1);
        $perPage = 24;
        $total = $attachments->count();
        $attachments = $attachments->forPage($page, $perPage)->values();

        return view('drive::index', compact('attachments', 'filters', 'stats', 'page', 'perPage', 'total'));
    }

    private function normalizeAttachment($attachment, string $source): array
    {
        $filename = $attachment->original_name ?? $attachment->original_filename ?? 'Unknown';
        $mimeType = $attachment->mime_type ?? '';
        $size = $attachment->file_size ?? $attachment->size ?? 0;

        // Determine parent info
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

        // Get URL
        $url = '';
        if (method_exists($attachment, 'getUrl')) {
            $url = $attachment->getUrl();
        } elseif (isset($attachment->url)) {
            $url = $attachment->url;
        } elseif ($attachment->file_path ?? $attachment->path ?? null) {
            $url = asset('storage/' . ($attachment->file_path ?? $attachment->path));
        }

        // Get uploader
        $uploader = null;
        if ($attachment->uploader ?? null) {
            $uploader = $attachment->uploader;
        }

        return [
            'id' => $attachment->id,
            'filename' => $filename,
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
