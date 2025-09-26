<?php

namespace App\Traits;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Log;

trait EncryptedAttributes
{
    public $decryptionFailed = [];

    abstract protected function getEncryptedFields(): array;

    public function initializeEncryptedAttributes()
    {
        foreach ($this->getEncryptedFields() as $field) {
            $this->mergeCasts([$field => 'encrypted']);
        }
    }

    public function setAttribute($key, $value)
    {
        if ($this->shouldEncryptAttribute($key)) {
            $value = $this->encryptAttribute($value);
        }

        return parent::setAttribute($key, $value);
    }

    public function getAttributeValue($key)
    {
        $value = parent::getAttributeValue($key);

        if ($this->shouldDecryptAttribute($key, $value)) {
            return $this->decryptAttribute($key, $value);
        }

        return $value;
    }

    public function getAttributeFromArray($key)
    {
        $value = parent::getAttributeFromArray($key);

        if ($this->shouldDecryptAttribute($key, $value)) {
            return $this->decryptAttribute($key, $value);
        }

        return $value;
    }

    public function attributesToArray()
    {
        $attributes = parent::attributesToArray();

        foreach ($attributes as $key => $value) {
            if ($this->shouldDecryptAttribute($key, $value)) {
                $attributes[$key] = $this->decryptAttribute($key, $value);
            }
        }

        return $attributes;
    }

    protected function shouldEncryptAttribute($key): bool
    {
        return in_array($key, $this->getEncryptedFields(), true) && !$this->isAlreadyEncrypted($key);
    }

    protected function shouldDecryptAttribute($key, $value): bool
    {
        return in_array($key, $this->getEncryptedFields(), true)
            && !is_null($value)
            && !in_array($key, $this->decryptionFailed, true);
    }

    protected function isAlreadyEncrypted($key): bool
    {
        $rawValue = $this->getOriginal($key);

        if (is_null($rawValue)) {
            return false;
        }

        try {
            Crypt::decryptString($rawValue);
            return true;
        } catch (DecryptException $e) {
            try {
                Crypt::decrypt($rawValue);
                return true;
            } catch (DecryptException $e2) {
                return false;
            }
        }
    }

    protected function encryptAttribute($value)
    {
        if (is_null($value) || $value === '') {
            return $value;
        }

        try {
            return Crypt::encryptString($value);
        } catch (\Exception $e) {
            Log::error('Encryption failed', [
                'model' => get_class($this),
                'id' => $this->id ?? 'new',
                'error' => $e->getMessage(),
            ]);

            return $value;
        }
    }

    protected function decryptAttribute($key, $value)
    {
        if (is_null($value) || $value === '') {
            return $value;
        }

        try {
            return Crypt::decryptString($value);
        } catch (DecryptException $e) {
            try {
                return Crypt::decrypt($value);
            } catch (DecryptException $e2) {
                $this->decryptionFailed[] = $key;

                Log::warning('Decryption failed for field, returning original value', [
                    'model' => get_class($this),
                    'id' => $this->id ?? 'unknown',
                    'field' => $key,
                    'error' => $e2->getMessage(),
                ]);

                return $value;
            }
        }
    }

    public function getEncryptedRawAttribute($key)
    {
        return $this->getOriginal($key);
    }

    public function forceDecrypt($key)
    {
        if (isset($this->decryptionFailed[$key])) {
            unset($this->decryptionFailed[$key]);
        }

        $rawValue = $this->getOriginal($key);

        return $this->decryptAttribute($key, $rawValue);
    }

    public function isFieldEncrypted($key): bool
    {
        if (!in_array($key, $this->getEncryptedFields(), true)) {
            return false;
        }

        $rawValue = $this->getOriginal($key);

        if (is_null($rawValue)) {
            return false;
        }

        try {
            Crypt::decryptString($rawValue);
            return true;
        } catch (DecryptException $e) {
            try {
                Crypt::decrypt($rawValue);
                return true;
            } catch (DecryptException $e2) {
                return false;
            }
        }
    }

    public static function scopeWhereEncrypted($query, $field, $value)
    {
        try {
            $encryptedValue = Crypt::encryptString($value);
            return $query->where($field, $encryptedValue);
        } catch (\Exception $e) {
            Log::warning('Failed to encrypt search value', [
                'field' => $field,
                'error' => $e->getMessage(),
            ]);

            return $query->where($field, $value);
        }
    }
}
