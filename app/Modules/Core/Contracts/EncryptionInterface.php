<?php

declare(strict_types=1);

namespace App\Modules\Core\Contracts;

interface EncryptionInterface
{
    /**
     * Encrypt a value using AES-256.
     */
    public function encrypt(string $value): string;

    /**
     * Decrypt a value.
     */
    public function decrypt(string $encrypted): string;

    /**
     * Encrypt an array of data.
     */
    public function encryptArray(array $data): string;

    /**
     * Decrypt to an array.
     */
    public function decryptArray(string $encrypted): array;

    /**
     * Generate a secure hash for a value.
     */
    public function hash(string $value): string;

    /**
     * Verify a value against a hash.
     */
    public function verifyHash(string $value, string $hash): bool;

    /**
     * Generate a secure random token.
     */
    public function generateToken(int $length = 32): string;

    /**
     * Encrypt a file and return the encrypted content.
     */
    public function encryptFile(string $filePath): string;

    /**
     * Decrypt file content and return decrypted data.
     */
    public function decryptFile(string $encryptedContent): string;
}
