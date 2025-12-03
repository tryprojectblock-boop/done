<?php

declare(strict_types=1);

namespace App\Modules\Core\Traits;

use App\Modules\Core\Contracts\EncryptionInterface;

trait HasEncryptedAttributes
{
    public static function bootHasEncryptedAttributes(): void
    {
        static::saving(function ($model) {
            $model->encryptAttributes();
        });

        static::retrieved(function ($model) {
            $model->decryptAttributes();
        });
    }

    protected function encryptAttributes(): void
    {
        $encryption = app(EncryptionInterface::class);

        foreach ($this->getEncryptedAttributes() as $attribute) {
            if (isset($this->attributes[$attribute]) && ! empty($this->attributes[$attribute])) {
                $this->attributes[$attribute] = $encryption->encryptForDatabase($this->attributes[$attribute]);
            }
        }
    }

    protected function decryptAttributes(): void
    {
        $encryption = app(EncryptionInterface::class);

        foreach ($this->getEncryptedAttributes() as $attribute) {
            if (isset($this->attributes[$attribute]) && ! empty($this->attributes[$attribute])) {
                try {
                    $this->attributes[$attribute] = $encryption->decryptFromDatabase($this->attributes[$attribute]);
                } catch (\Throwable $e) {
                    // If decryption fails, the value might already be decrypted or corrupted
                    report($e);
                }
            }
        }
    }

    protected function getEncryptedAttributes(): array
    {
        return property_exists($this, 'encrypted') ? $this->encrypted : [];
    }
}
