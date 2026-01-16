<?php

declare(strict_types=1);

namespace App\Modules\Task\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Core\Contracts\FileUploadInterface;
use App\Modules\Task\Enums\ActivityType;
use App\Modules\Task\Models\Task;
use App\Modules\Task\Models\TaskActivity;
use App\Modules\Task\Models\TaskAttachment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TaskAttachmentController extends Controller
{
    private const MAX_FILE_SIZE_KB = 10240; // 10MB
    private const MAX_FILES_COUNT = 10;

    public function __construct(
        private readonly FileUploadInterface $fileUploadService
    ) {}

    public function store(Request $request, Task $task): RedirectResponse|JsonResponse
    {
        $request->validate([
            'files' => 'required|array|max:' . self::MAX_FILES_COUNT,
            'files.*' => 'file|max:' . self::MAX_FILE_SIZE_KB,
            'description' => 'nullable|string|max:255',
        ], [
            'files.max' => 'You can upload a maximum of ' . self::MAX_FILES_COUNT . ' files at once.',
            'files.*.max' => 'Each file must be less than 10MB.',
        ]);

        $user = auth()->user();
        $uploadedCount = 0;
        $errors = [];
        $uploadedAttachments = [];

        foreach ($request->file('files') as $file) {
            $result = $this->fileUploadService->upload(
                $file,
                "tasks/{$task->id}/attachments",
                [
                    'user_id' => $user->id,
                    'context' => 'task_attachment',
                    'tenant_id' => $user->company_id,
                ]
            );

            if ($result->isSuccess()) {
                $attachment = TaskAttachment::create([
                    'task_id' => $task->id,
                    'uploaded_by' => $user->id,
                    'original_name' => $result->originalName,
                    'file_path' => $result->path,
                    'file_type' => pathinfo($result->originalName, PATHINFO_EXTENSION),
                    'mime_type' => $result->mimeType,
                    'file_size' => $result->size,
                    'disk' => $result->disk,
                    'description' => $request->input('description'),
                ]);

                TaskActivity::log(
                    $task,
                    $user,
                    ActivityType::ATTACHMENT_ADDED,
                    null,
                    ['name' => $attachment->original_name]
                );

                $uploadedAttachments[] = [
                    'id' => $attachment->id,
                    'original_name' => $attachment->original_name,
                    'formatted_size' => $attachment->getFormattedSize(),
                    'icon_class' => $attachment->getIconClass(),
                    'download_url' => route('tasks.attachments.download', $attachment),
                    'delete_url' => route('tasks.attachments.destroy', $attachment),
                    'can_delete' => $attachment->uploaded_by === $user->id || $user->isAdminOrHigher(),
                ];

                $uploadedCount++;
            } else {
                $errors[] = $file->getClientOriginalName() . ': ' . $result->error;
            }
        }

        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            $totalAttachments = $task->attachments()->count();

            if ($uploadedCount > 0 && empty($errors)) {
                return response()->json([
                    'success' => true,
                    'message' => $uploadedCount . ' file(s) uploaded successfully.',
                    'attachments' => $uploadedAttachments,
                    'total_count' => $totalAttachments,
                ]);
            } elseif ($uploadedCount > 0 && !empty($errors)) {
                return response()->json([
                    'success' => true,
                    'message' => $uploadedCount . ' file(s) uploaded successfully.',
                    'warning' => 'Some files failed to upload: ' . implode(', ', $errors),
                    'attachments' => $uploadedAttachments,
                    'total_count' => $totalAttachments,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to upload files: ' . implode(', ', $errors),
                ], 422);
            }
        }

        if ($uploadedCount > 0 && empty($errors)) {
            return back()->with('success', $uploadedCount . ' file(s) uploaded successfully.');
        } elseif ($uploadedCount > 0 && !empty($errors)) {
            return back()
                ->with('success', $uploadedCount . ' file(s) uploaded successfully.')
                ->with('warning', 'Some files failed to upload: ' . implode(', ', $errors));
        } else {
            return back()->with('error', 'Failed to upload files: ' . implode(', ', $errors));
        }
    }

    public function download(TaskAttachment $attachment): StreamedResponse
    {
        $disk = $attachment->disk ?? config('filesystems.default_upload_disk', 'do_spaces');

        if (!Storage::disk($disk)->exists($attachment->file_path)) {
            abort(404, 'File not found.');
        }

        return Storage::disk($disk)->download(
            $attachment->file_path,
            $attachment->original_name
        );
    }

    public function destroy(TaskAttachment $attachment): RedirectResponse
    {
        $user = auth()->user();
        $task = $attachment->task;

        // Only uploader or admin can delete
        if ($attachment->uploaded_by !== $user->id && !$user->isAdminOrHigher()) {
            return back()->with('error', 'You do not have permission to delete this attachment.');
        }

        $fileName = $attachment->original_name;
        $disk = $attachment->disk ?? config('filesystems.default_upload_disk', 'do_spaces');

        // Delete file from storage
        if ($attachment->file_path) {
            Storage::disk($disk)->delete($attachment->file_path);
        }

        $attachment->delete();

        TaskActivity::log(
            $task,
            $user,
            ActivityType::ATTACHMENT_REMOVED,
            ['name' => $fileName],
            null
        );

        return back()->with('success', 'Attachment deleted successfully.');
    }

    /**
     * Get a temporary URL for viewing/downloading an attachment
     */
    public function view(TaskAttachment $attachment): RedirectResponse
    {
        $disk = $attachment->disk ?? config('filesystems.default_upload_disk', 'do_spaces');

        if (!Storage::disk($disk)->exists($attachment->file_path)) {
            abort(404, 'File not found.');
        }

        // Generate a temporary signed URL (valid for 60 minutes)
        $temporaryUrl = Storage::disk($disk)->temporaryUrl(
            $attachment->file_path,
            now()->addMinutes(60)
        );

        return redirect($temporaryUrl);
    }
}
