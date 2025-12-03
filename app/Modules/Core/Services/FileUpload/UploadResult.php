<?php

declare(strict_types=1);

namespace App\Modules\Core\Services\FileUpload;

use JsonSerializable;

final class UploadResult implements JsonSerializable
{
    public function __construct(
        public readonly bool $success,
        public readonly ?string $path = null,
        public readonly ?string $url = null,
        public readonly ?string $originalName = null,
        public readonly ?string $mimeType = null,
        public readonly ?int $size = null,
        public readonly ?string $disk = null,
        public readonly ?string $error = null,
        public readonly array $metadata = [],
    ) {}

    public static function success(
        string $path,
        string $url,
        string $originalName,
        string $mimeType,
        int $size,
        string $disk,
        array $metadata = [],
    ): self {
        return new self(
            success: true,
            path: $path,
            url: $url,
            originalName: $originalName,
            mimeType: $mimeType,
            size: $size,
            disk: $disk,
            metadata: $metadata,
        );
    }

    public static function failure(string $error): self
    {
        return new self(
            success: false,
            error: $error,
        );
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function isFailure(): bool
    {
        return ! $this->success;
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'path' => $this->path,
            'url' => $this->url,
            'original_name' => $this->originalName,
            'mime_type' => $this->mimeType,
            'size' => $this->size,
            'disk' => $this->disk,
            'error' => $this->error,
            'metadata' => $this->metadata,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
