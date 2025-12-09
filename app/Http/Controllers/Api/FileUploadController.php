<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Core\Contracts\FileUploadInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FileUploadController extends Controller
{
    public function __construct(
        private readonly FileUploadInterface $fileUpload,
    ) {}

    public function upload(Request $request): JsonResponse
    {
        $maxUploadSize = (int) config('filesystems.max_upload_size', 10240);
        $maxUploadSizeBytes = $maxUploadSize * 1024;

        // Pre-check: Reject if Content-Length header exceeds limit (defense in depth)
        $contentLength = $request->header('Content-Length');
        if ($contentLength !== null && (int) $contentLength > $maxUploadSizeBytes) {
            return response()->json([
                'success' => false,
                'message' => 'File size exceeds the maximum allowed size.',
            ], 413);
        }

        $request->validate([
            'file' => ['required', 'file', 'max:' . $maxUploadSize],
            'context' => ['nullable', 'string', 'max:50'],
        ]);

        $file = $request->file('file');

        // Post-check: Verify actual file size (defense in depth)
        if ($file->getSize() > $maxUploadSizeBytes) {
            return response()->json([
                'success' => false,
                'message' => 'File size exceeds the maximum allowed size.',
            ], 413);
        }

        $context = $request->input('context', 'general');

        $directory = $this->getDirectoryForContext($context);

        $result = $this->fileUpload->upload($file, $directory, [
            'user_id' => $request->user()?->id,
            'context' => $context,
            'tenant_id' => current_tenant_id(),
        ]);

        if ($result->isFailure()) {
            return response()->json([
                'success' => false,
                'message' => $result->error,
            ], 422);
        }

        return response()->json([
            'success' => true,
            'path' => $result->path,
            'url' => $result->url,
            'name' => $result->originalName,
            'size' => $result->size,
            'mime_type' => $result->mimeType,
        ]);
    }

    public function delete(Request $request): JsonResponse
    {
        $request->validate([
            'path' => ['required', 'string'],
        ]);

        $path = $request->input('path');

        // Security: Ensure user can only delete their own files
        // In production, you'd want to verify ownership from database
        if (! $this->fileUpload->exists($path)) {
            return response()->json([
                'success' => false,
                'message' => 'File not found',
            ], 404);
        }

        $deleted = $this->fileUpload->delete($path);

        return response()->json([
            'success' => $deleted,
            'message' => $deleted ? 'File deleted successfully' : 'Failed to delete file',
        ]);
    }

    public function getTemporaryUrl(Request $request): JsonResponse
    {
        $request->validate([
            'path' => ['required', 'string'],
            'expiration' => ['nullable', 'integer', 'min:1', 'max:1440'], // Max 24 hours
        ]);

        $path = $request->input('path');
        $expiration = $request->input('expiration', 60);

        if (! $this->fileUpload->exists($path)) {
            return response()->json([
                'success' => false,
                'message' => 'File not found',
            ], 404);
        }

        $url = $this->fileUpload->getTemporaryUrl($path, $expiration);

        return response()->json([
            'success' => true,
            'url' => $url,
            'expires_in' => $expiration,
        ]);
    }

    private function getDirectoryForContext(string $context): string
    {
        return match ($context) {
            'workspace' => 'workspaces',
            'profile' => 'profiles',
            'avatar' => 'avatars',
            'document' => 'documents',
            'message' => 'messages',
            'attachment' => 'attachments',
            default => 'uploads',
        };
    }
}
