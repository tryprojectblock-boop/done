<?php

declare(strict_types=1);

namespace App\Modules\Core\Exceptions;

use Exception;

final class EncryptionException extends Exception
{
    public static function encryptionFailed(): self
    {
        return new self('Failed to encrypt the data.');
    }

    public static function decryptionFailed(): self
    {
        return new self('Failed to decrypt the data. The data may be corrupted or the key may have changed.');
    }

    public static function missingKey(): self
    {
        return new self('Application encryption key is not set.');
    }

    public static function fileNotFound(string $path): self
    {
        return new self(sprintf('File not found: %s', $path));
    }

    public static function fileReadFailed(string $path): self
    {
        return new self(sprintf('Failed to read file: %s', $path));
    }

    public static function invalidData(): self
    {
        return new self('The provided data is invalid for encryption/decryption.');
    }
}
