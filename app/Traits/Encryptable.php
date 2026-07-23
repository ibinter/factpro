<?php

namespace App\Traits;

use App\Services\EncryptionService;

trait Encryptable
{
    // Define $encryptable = ['email', 'phone'] in the model

    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);
        if (in_array($key, $this->encryptable ?? []) && !empty($value)) {
            return app(EncryptionService::class)->decrypt($value);
        }
        return $value;
    }

    public function setAttribute($key, $value)
    {
        if (in_array($key, $this->encryptable ?? []) && !empty($value)) {
            $value = app(EncryptionService::class)->encrypt($value);
        }
        return parent::setAttribute($key, $value);
    }
}
