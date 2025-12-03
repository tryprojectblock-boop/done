<?php

declare(strict_types=1);

namespace App\Modules\Core\Services\Encryption;

use App\Modules\Core\Contracts\EncryptionInterface;
use App\Modules\Core\Exceptions\EncryptionException;
use Illuminate\Support\Facades\Crypt;
use Throwable;

final class EncryptionService implements EncryptionInterface
{
    private const CIPHER = 'aes-256-gcm';
    private const HASH_ALGO = 'sha256';

    private string $key;

    public function __construct()
    {
        $this->key = $this->deriveKey();
    }

    public function encrypt(string $value): string
    {
        try {
            $iv = random_bytes(openssl_cipher_iv_length(self::CIPHER));

            $encrypted = openssl_encrypt(
                $value,
                self::CIPHER,
                $this->key,
                OPENSSL_RAW_DATA,
                $iv,
                $tag
            );

            if ($encrypted === false) {
                throw EncryptionException::encryptionFailed();
            }

            return base64_encode($iv . $tag . $encrypted);
        } catch (EncryptionException $e) {
            throw $e;
        } catch (Throwable $e) {
            report($e);
            throw EncryptionException::encryptionFailed();
        }
    }

    public function decrypt(string $encrypted): string
    {
        try {
            $data = base64_decode($encrypted, true);

            if ($data === false) {
                throw EncryptionException::decryptionFailed();
            }

            $ivLength = openssl_cipher_iv_length(self::CIPHER);
            $tagLength = 16;

            $iv = substr($data, 0, $ivLength);
            $tag = substr($data, $ivLength, $tagLength);
            $ciphertext = substr($data, $ivLength + $tagLength);

            $decrypted = openssl_decrypt(
                $ciphertext,
                self::CIPHER,
                $this->key,
                OPENSSL_RAW_DATA,
                $iv,
                $tag
            );

            if ($decrypted === false) {
                throw EncryptionException::decryptionFailed();
            }

            return $decrypted;
        } catch (EncryptionException $e) {
            throw $e;
        } catch (Throwable $e) {
            report($e);
            throw EncryptionException::decryptionFailed();
        }
    }

    public function encryptArray(array $data): string
    {
        return $this->encrypt(json_encode($data, JSON_THROW_ON_ERROR));
    }

    public function decryptArray(string $encrypted): array
    {
        $decrypted = $this->decrypt($encrypted);
        return json_decode($decrypted, true, 512, JSON_THROW_ON_ERROR);
    }

    public function hash(string $value): string
    {
        return hash_hmac(self::HASH_ALGO, $value, $this->key);
    }

    public function verifyHash(string $value, string $hash): bool
    {
        return hash_equals($this->hash($value), $hash);
    }

    public function generateToken(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }

    public function encryptFile(string $filePath): string
    {
        if (! file_exists($filePath)) {
            throw EncryptionException::fileNotFound($filePath);
        }

        $content = file_get_contents($filePath);

        if ($content === false) {
            throw EncryptionException::fileReadFailed($filePath);
        }

        return $this->encrypt($content);
    }

    public function decryptFile(string $encryptedContent): string
    {
        return $this->decrypt($encryptedContent);
    }

    /**
     * Encrypt sensitive database fields.
     */
    public function encryptForDatabase(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return Crypt::encryptString(is_array($value) ? json_encode($value) : (string) $value);
    }

    /**
     * Decrypt sensitive database fields.
     */
    public function decryptFromDatabase(?string $value, bool $asArray = false): mixed
    {
        if ($value === null) {
            return null;
        }

        $decrypted = Crypt::decryptString($value);

        return $asArray ? json_decode($decrypted, true) : $decrypted;
    }

    private function deriveKey(): string
    {
        $appKey = config('app.key');

        if (empty($appKey)) {
            throw EncryptionException::missingKey();
        }

        if (str_starts_with($appKey, 'base64:')) {
            $appKey = base64_decode(substr($appKey, 7));
        }

        return hash(self::HASH_ALGO, $appKey, true);
    }
}
