<?php

namespace App\Console\Commands;

use App\Models\Finance\FinanceGateway;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class FixGatewayCredentials extends Command
{
    protected $signature = 'fix:gateway-credentials {school_id}';
    protected $description = 'Fix gateway credentials that cannot be decrypted';

    public function handle()
    {
        $schoolId = $this->argument('school_id');
        $gateways = FinanceGateway::where('school_id', $schoolId)->get();

        if ($gateways->isEmpty()) {
            $this->error("No gateways found for school ID: {$schoolId}");
            return;
        }

        foreach ($gateways as $gw) {
            $this->info("Processing Gateway ID: {$gw->id}, Alias: {$gw->alias}");
            
            // Tenta diferentes métodos de recuperação
            $credentials = $this->tryRecoverCredentials($gw);
            
            if ($credentials && isset($credentials['api_key'])) {
                $this->info("✓ Credentials recovered successfully!");
                $this->info("  API Key: " . substr($credentials['api_key'], 0, 10) . "...");
                $this->info("  Environment: " . ($credentials['environment'] ?? 'NOT_SET'));
                
                // Pergunta se deve salvar as credenciais re-criptografadas
                if ($this->confirm("Re-encrypt and save these credentials?")) {
                    $gw->credentials_encrypted = encrypt(json_encode($credentials));
                    $gw->save();
                    $this->info("✓ Credentials re-encrypted and saved!");
                }
            } else {
                $this->error("✗ Could not recover credentials for this gateway");
                
                // Permite entrada manual
                if ($this->confirm("Enter credentials manually?")) {
                    $apiKey = $this->ask("Enter API Key:");
                    $environment = $this->choice("Select environment:", ['sandbox', 'production'], 0);
                    
                    $newCredentials = [
                        'api_key' => $apiKey,
                        'environment' => $environment
                    ];
                    
                    $gw->credentials_encrypted = encrypt(json_encode($newCredentials));
                    $gw->save();
                    $this->info("✓ New credentials saved!");
                }
            }
            
            $this->info("---");
        }
    }

    private function tryRecoverCredentials($gateway)
    {
        $encrypted = $gateway->credentials_encrypted;
        
        // Método 1: Descriptografia normal (já sabemos que falha)
        try {
            $decrypted = decrypt($encrypted);
            if (is_array($decrypted)) {
                return $decrypted;
            }
            $decoded = json_decode($decrypted, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        } catch (\Exception $e) {
            // Esperado falhar
        }

        // Método 2: JSON decode direto (para dados em texto puro)
        try {
            $decoded = json_decode($encrypted, true);
            if (is_array($decoded) && isset($decoded['api_key'])) {
                return $decoded;
            }
        } catch (\Exception $e) {
            // Continua tentando
        }

        // Método 3: Base64 decode + JSON (para dados codificados)
        try {
            $base64Decoded = base64_decode($encrypted, true);
            if ($base64Decoded !== false) {
                $decoded = json_decode($base64Decoded, true);
                if (is_array($decoded) && isset($decoded['api_key'])) {
                    return $decoded;
                }
            }
        } catch (\Exception $e) {
            // Continua tentando
        }

        // Método 4: Tentar com diferentes APP_KEYs comuns
        $commonKeys = [
            'base64:' . base64_encode('32-char-key-for-laravel-app-test'),
            'base64:' . base64_encode(str_repeat('a', 32)),
            'base64:' . base64_encode(str_repeat('1', 32)),
        ];

        foreach ($commonKeys as $testKey) {
            try {
                config(['app.key' => $testKey]);
                $decrypted = decrypt($encrypted);
                if (is_array($decrypted)) {
                    return $decrypted;
                }
                $decoded = json_decode($decrypted, true);
                if (is_array($decoded) && isset($decoded['api_key'])) {
                    return $decoded;
                }
            } catch (\Exception $e) {
                // Continua tentando
            }
        }

        return null;
    }
}