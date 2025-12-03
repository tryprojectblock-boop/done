<?php

declare(strict_types=1);

namespace App\Modules\Core\Exceptions;

use Exception;

final class FileUploadException extends Exception
{
    public static function invalidFile(): self
    {
        return new self('The uploaded file is not valid.');
    }

    public static function fileTooLarge(int $maxSize): self
    {
        return new self(
            sprintf('File size exceeds the maximum allowed size of %s bytes.', $maxSize)
        );
    }

    public static function invalidMimeType(string $mimeType, array $allowed): self
    {
        return new self(
            sprintf(
                'File type %s is not allowed. Allowed types: %s',
                $mimeType,
                implode(', ', $allowed)
            )
        );
    }

    public static function uploadFailed(string $reason = ''): self
    {
        $message = 'File upload failed.';
        if ($reason) {
            $message .= ' Reason: ' . $reason;
        }
        return new self($message);
    }
}
