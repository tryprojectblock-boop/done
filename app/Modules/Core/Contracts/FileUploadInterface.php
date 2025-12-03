<?php

declare(strict_types=1);

namespace App\Modules\Core\Contracts;

use Illuminate\Http\UploadedFile;
use App\Modules\Core\Services\FileUpload\UploadResult;

interface FileUploadInterface
{
    /**
     * Upload a file to the configured storage.
     */
    public function upload(UploadedFile $file, string $directory = '', array $options = []): UploadResult;

    /**
     * Upload multiple files.
     *
     * @return array<UploadResult>
     */
    public function uploadMultiple(array $files, string $directory = '', array $options = []): array;

    /**
     * Delete a file from storage.
     */
    public function delete(string $path): bool;

    /**
     * Delete multiple files from storage.
     */
    public function deleteMultiple(array $paths): bool;

    /**
     * Get a temporary URL for a file.
     */
    public function getTemporaryUrl(string $path, int $expirationMinutes = 60): string;

    /**
     * Get the public URL for a file.
     */
    public function getUrl(string $path): string;

    /**
     * Check if a file exists.
     */
    public function exists(string $path): bool;

    /**
     * Get file metadata.
     */
    public function getMetadata(string $path): array;

    /**
     * Move a file to a new location.
     */
    public function move(string $from, string $to): bool;

    /**
     * Copy a file to a new location.
     */
    public function copy(string $from, string $to): bool;
}
