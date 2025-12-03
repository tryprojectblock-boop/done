<?php

declare(strict_types=1);

namespace App\Modules\Core\Services\FileUpload;

use App\Modules\Core\Contracts\FileUploadInterface;
use App\Modules\Core\Exceptions\FileUploadException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

final class FileUploadService implements FileUploadInterface
{
    private string $disk;
    private array $allowedMimeTypes;
    private int $maxFileSize;

    public function __construct()
    {
        $this->disk = config('filesystems.default_upload_disk', 'do_spaces');
        $this->allowedMimeTypes = config('filesystems.allowed_mime_types', []);
        $this->maxFileSize = config('filesystems.max_upload_size', 10485760); // 10MB default
    }

    public function upload(UploadedFile $file, string $directory = '', array $options = []): UploadResult
    {
        try {
            $this->validateFile($file, $options);

            $disk = $options['disk'] ?? $this->disk;
            $visibility = $options['visibility'] ?? 'private';
            $preserveName = $options['preserve_name'] ?? false;

            $filename = $preserveName
                ? $this->sanitizeFilename($file->getClientOriginalName())
                : $this->generateUniqueFilename($file);

            $path = $this->buildPath($directory, $filename, $options);

            Storage::disk($disk)->put(
                $path,
                file_get_contents($file->getRealPath()),
                $visibility
            );

            $url = $visibility === 'public'
                ? Storage::disk($disk)->url($path)
                : $this->getTemporaryUrl($path);

            return UploadResult::success(
                path: $path,
                url: $url,
                originalName: $file->getClientOriginalName(),
                mimeType: $file->getMimeType() ?? 'application/octet-stream',
                size: $file->getSize(),
                disk: $disk,
                metadata: $this->extractMetadata($file, $options),
            );
        } catch (FileUploadException $e) {
            return UploadResult::failure($e->getMessage());
        } catch (Throwable $e) {
            report($e);
            return UploadResult::failure('An error occurred while uploading the file.');
        }
    }

    public function uploadMultiple(array $files, string $directory = '', array $options = []): array
    {
        $results = [];

        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $results[] = $this->upload($file, $directory, $options);
            }
        }

        return $results;
    }

    public function delete(string $path): bool
    {
        try {
            return Storage::disk($this->disk)->delete($path);
        } catch (Throwable $e) {
            report($e);
            return false;
        }
    }

    public function deleteMultiple(array $paths): bool
    {
        try {
            return Storage::disk($this->disk)->delete($paths);
        } catch (Throwable $e) {
            report($e);
            return false;
        }
    }

    public function getTemporaryUrl(string $path, int $expirationMinutes = 60): string
    {
        return Storage::disk($this->disk)->temporaryUrl(
            $path,
            now()->addMinutes($expirationMinutes)
        );
    }

    public function getUrl(string $path): string
    {
        return Storage::disk($this->disk)->url($path);
    }

    public function exists(string $path): bool
    {
        return Storage::disk($this->disk)->exists($path);
    }

    public function getMetadata(string $path): array
    {
        if (! $this->exists($path)) {
            return [];
        }

        return [
            'size' => Storage::disk($this->disk)->size($path),
            'last_modified' => Storage::disk($this->disk)->lastModified($path),
            'mime_type' => Storage::disk($this->disk)->mimeType($path),
        ];
    }

    public function move(string $from, string $to): bool
    {
        try {
            return Storage::disk($this->disk)->move($from, $to);
        } catch (Throwable $e) {
            report($e);
            return false;
        }
    }

    public function copy(string $from, string $to): bool
    {
        try {
            return Storage::disk($this->disk)->copy($from, $to);
        } catch (Throwable $e) {
            report($e);
            return false;
        }
    }

    public function setDisk(string $disk): self
    {
        $clone = clone $this;
        $clone->disk = $disk;
        return $clone;
    }

    private function validateFile(UploadedFile $file, array $options): void
    {
        if (! $file->isValid()) {
            throw new FileUploadException('The uploaded file is not valid.');
        }

        $maxSize = $options['max_size'] ?? $this->maxFileSize;
        if ($file->getSize() > $maxSize) {
            throw new FileUploadException(
                sprintf('File size exceeds the maximum allowed size of %s bytes.', $maxSize)
            );
        }

        $allowedTypes = $options['allowed_types'] ?? $this->allowedMimeTypes;
        if (! empty($allowedTypes) && ! in_array($file->getMimeType(), $allowedTypes, true)) {
            throw new FileUploadException(
                sprintf('File type %s is not allowed.', $file->getMimeType())
            );
        }
    }

    private function generateUniqueFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $timestamp = now()->format('Ymd_His');
        $random = Str::random(8);

        return sprintf('%s_%s.%s', $timestamp, $random, $extension);
    }

    private function sanitizeFilename(string $filename): string
    {
        $name = pathinfo($filename, PATHINFO_FILENAME);
        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        $sanitized = Str::slug($name);

        return sprintf('%s_%s.%s', $sanitized, Str::random(6), $extension);
    }

    private function buildPath(string $directory, string $filename, array $options): string
    {
        $parts = array_filter([
            $options['tenant_id'] ?? null,
            trim($directory, '/'),
            $options['subfolder'] ?? null,
            $filename,
        ]);

        return implode('/', $parts);
    }

    private function extractMetadata(UploadedFile $file, array $options): array
    {
        $metadata = [
            'uploaded_at' => now()->toIso8601String(),
            'original_extension' => $file->getClientOriginalExtension(),
        ];

        if (isset($options['user_id'])) {
            $metadata['uploaded_by'] = $options['user_id'];
        }

        if (isset($options['context'])) {
            $metadata['context'] = $options['context'];
        }

        return $metadata;
    }
}
