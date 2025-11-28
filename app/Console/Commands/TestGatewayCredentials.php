<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Finance\FinanceGateway;

class TestGatewayCredentials extends Command
{
    protected $signature = 'test:gateway-credentials {school_id}';
    protected $description = 'Test gateway credentials reading for a school';

    public function handle()
    {
        $schoolId = $this->argument('school_id');
        
        $gateways = FinanceGateway::where('school_id', $schoolId)->get();
        
        if ($gateways->isEmpty()) {
            $this->error("No gateways found for school_id: {$schoolId}");
            return 1;
        }
        
        foreach ($gateways as $gw) {
            $this->info("Gateway ID: {$gw->id}, Alias: {$gw->alias}");
            $this->info("Raw credentials_encrypted: " . substr($gw->credentials_encrypted, 0, 50) . "...");
            
            // Teste de JSON decode direto
            try {
                $jsonDecode = json_decode($gw->credentials_encrypted, true);
                if (is_array($jsonDecode)) {
                    $this->info("JSON decode SUCCESS: " . json_encode($jsonDecode, JSON_PRETTY_PRINT));
                } else {
                    $this->error("JSON decode returned non-array");
                }
            } catch (Exception $e2) {
                $this->error("JSON decode FAILED: " . $e2->getMessage());
            }
            
            $credentials = $gw->credentials;
            if ($credentials) {
                $this->info("Credentials decoded successfully:");
                $this->info("Full credentials: " . json_encode($credentials, JSON_PRETTY_PRINT));
                $this->info("- API Key: " . ($credentials['api_key'] ?? 'NOT_FOUND'));
                $this->info("- Environment: " . ($credentials['environment'] ?? 'NOT_FOUND'));
            } else {
                $this->error("Failed to decode credentials");
            }
            $this->info("---");
        }
        
        return 0;
    }
}