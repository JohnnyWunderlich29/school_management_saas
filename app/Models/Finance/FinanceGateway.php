<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class FinanceGateway extends Model
{
    protected $table = 'finance_gateways';

    protected $fillable = [
        'school_id',
        'alias',
        'name',
        'credentials_encrypted',
        'webhook_secret_encrypted',
        'active',
        'environment',
    ];

    protected $casts = [
        'active' => 'boolean',
        'environment' => 'string',
    ];

    protected $hidden = [
        'credentials_encrypted',
        'webhook_secret_encrypted',
    ];

    // Credentials accessor/mutator (encrypted JSON)
    public function getCredentialsAttribute(): ?array
    {
        if (!$this->credentials_encrypted) {
            return null;
        }
        
        // Try decrypt first (current format)
        try {
            $json = Crypt::decryptString($this->credentials_encrypted);
            $decoded = json_decode($json, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        } catch (\Throwable $e) {
            Log::warning('FinanceGateway credentials decrypt failed, trying fallbacks', [
                'gateway_id' => $this->id,
                'alias' => $this->alias,
                'school_id' => $this->school_id,
                'error' => $e->getMessage(),
                'encrypted_length' => strlen($this->credentials_encrypted),
            ]);
            
            // Try plaintext JSON fallback
            try {
                $fallback = json_decode($this->credentials_encrypted, true);
                if (is_array($fallback)) {
                    // Check if this is Laravel encryption structure
                    if (isset($fallback['iv'], $fallback['value'], $fallback['mac'])) {
                        Log::warning('Found Laravel encryption structure, trying to decrypt inner value', [
                            'gateway_id' => $this->id,
                            'alias' => $this->alias,
                            'school_id' => $this->school_id,
                        ]);
                        try {
                            // Reconstruct encrypted payload and decrypt
                            $payload = base64_encode(json_encode($fallback));
                            $innerDecrypted = Crypt::decryptString($payload);
                            $result = json_decode($innerDecrypted, true);
                            if (is_array($result)) {
                                Log::info('Successfully decrypted inner value', [
                                    'gateway_id' => $this->id,
                                    'alias' => $this->alias,
                                    'school_id' => $this->school_id,
                                    'keys' => array_keys($result),
                                ]);
                                return $result;
                            }
                        } catch (\Throwable $innerError) {
                            Log::warning('Failed to decrypt inner value', [
                                'gateway_id' => $this->id,
                                'alias' => $this->alias,
                                'school_id' => $this->school_id,
                                'inner_error' => $innerError->getMessage(),
                            ]);
                        }

                        // Do NOT return the encryption payload structure as credentials
                        Log::warning('Returning empty credentials due to undecryptable payload structure', [
                            'gateway_id' => $this->id,
                            'alias' => $this->alias,
                            'school_id' => $this->school_id,
                        ]);
                        return [];
                    }
                    
                    Log::info('FinanceGateway credentials using plaintext JSON fallback', [
                        'gateway_id' => $this->id,
                        'alias' => $this->alias,
                        'school_id' => $this->school_id,
                        'keys' => array_keys($fallback),
                    ]);
                    return $fallback;
                }
            } catch (\Throwable $fallbackError) {
                Log::error('FinanceGateway credentials plaintext fallback also failed', [
                    'gateway_id' => $this->id,
                    'alias' => $this->alias,
                    'school_id' => $this->school_id,
                    'fallback_error' => $fallbackError->getMessage(),
                ]);
            }
            
            // Try base64 decode + JSON (another possible legacy format)
            try {
                $base64Decoded = base64_decode($this->credentials_encrypted, true);
                if ($base64Decoded !== false) {
                    $jsonDecoded = json_decode($base64Decoded, true);
                    if (is_array($jsonDecoded)) {
                        Log::info('FinanceGateway credentials using base64+JSON fallback', [
                            'gateway_id' => $this->id,
                            'alias' => $this->alias,
                            'school_id' => $this->school_id,
                            'keys' => array_keys($jsonDecoded),
                        ]);
                        return $jsonDecoded;
                    }
                }
            } catch (\Throwable $base64Error) {
                // Ignore base64 errors
            }
        }
        
        Log::error('FinanceGateway credentials could not be decoded with any method', [
            'gateway_id' => $this->id,
            'alias' => $this->alias,
            'school_id' => $this->school_id,
        ]);
        
        return null;
    }

    public function setCredentialsAttribute($value): void
    {
        $json = is_array($value) ? json_encode($value) : (string) $value;
        $this->attributes['credentials_encrypted'] = Crypt::encryptString($json);
    }

    // Webhook secret accessor/mutator (encrypted string)
    public function getWebhookSecretAttribute(): ?string
    {
        if (!$this->webhook_secret_encrypted) {
            return null;
        }
        try {
            return Crypt::decryptString($this->webhook_secret_encrypted);
        } catch (\Throwable $e) {
            // If it looks like plaintext (legacy), return as-is
            // Heuristic: if decrypt fails but the stored value doesn't look like base64-encoded ciphertext,
            // treat as plaintext. We avoid logging the secret for safety.
            $val = (string) $this->webhook_secret_encrypted;
            // Basic check: typical Laravel encrypted strings are base64 and contain ':' when serialized.
            // If extremely short or non-base64, assume plaintext.
            $isBase64 = base64_encode(base64_decode($val, true) ?: '') === $val;
            if (!$isBase64) {
                Log::warning('FinanceGateway webhook_secret using plaintext fallback', [
                    'gateway_id' => $this->id,
                    'alias' => $this->alias,
                    'school_id' => $this->school_id,
                ]);
                return $val;
            }
            return null;
        }
    }

    public function setWebhookSecretAttribute(?string $value): void
    {
        $this->attributes['webhook_secret_encrypted'] = $value ? Crypt::encryptString($value) : null;
    }
}