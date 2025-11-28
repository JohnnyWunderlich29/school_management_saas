<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class MailSettings extends Model
{
    protected $table = 'mail_settings';

    protected $fillable = [
        'school_id',
        'provider',
        'sending_domain',
        'from_email',
        'from_name',
        'reply_to_email',
        'credentials_encrypted',
        'dns_requirements',
        'dns_status',
        'verified',
        'active',
        'last_checked_at',
    ];

    protected $casts = [
        'dns_requirements' => 'array',
        'dns_status' => 'array',
        'verified' => 'boolean',
        'active' => 'boolean',
        'last_checked_at' => 'datetime',
    ];

    protected $hidden = [
        'credentials_encrypted',
    ];

    // Credentials accessor/mutator (encrypted JSON)
    public function getCredentialsAttribute(): ?array
    {
        if (!$this->credentials_encrypted) {
            return null;
        }
        try {
            $json = Crypt::decryptString($this->credentials_encrypted);
            $decoded = json_decode($json, true);
            return is_array($decoded) ? $decoded : null;
        } catch (\Throwable $e) {
            Log::warning('MailSettings credentials decrypt failed', [
                'mail_settings_id' => $this->id,
                'school_id' => $this->school_id,
                'error' => $e->getMessage(),
            ]);
            // Fallback: tentar interpretar como JSON puro
            try {
                $fallback = json_decode($this->credentials_encrypted, true);
                if (is_array($fallback)) {
                    return $fallback;
                }
            } catch (\Throwable $fallbackError) {
                // Ignorar
            }
            return null;
        }
    }

    public function setCredentialsAttribute($value): void
    {
        $json = is_array($value) ? json_encode($value) : (string) $value;
        $this->attributes['credentials_encrypted'] = Crypt::encryptString($json);
    }
}