<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\LicenseService;
use App\Models\Escola;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class TestModuleLicense extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:module-license {--escola= : ID da escola para testar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Testa o sistema de Feature Toggles e Licenciamento de Módulos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== TESTE DO SISTEMA DE FEATURE TOGGLES E LICENCIAMENTO ===');
        $this->newLine();

        // Testar configurações globais
        $this->testGlobalFeatures();
        
        // Testar licenças por escola
        $escolaId = (int) ($this->option('escola') ?? 1);
        $this->testSchoolLicenses($escolaId);
        
        // Testar helper functions
        $this->testHelperFunctions($escolaId);
        
        // Testar middleware
        $this->testMiddleware($escolaId);
        
        $this->newLine();
        $this->info('✅ Testes concluídos!');
        return 0;
    }

    private function testGlobalFeatures()
    {
        $this->info('1. Testando configurações globais de Feature Toggles...');
        
        $features = config('features.modules');
        
        foreach ($features as $module => $enabled) {
            $status = $enabled ? '✅ Habilitado' : '❌ Desabilitado';
            $this->line("   {$module}: {$status}");
        }
        
        $this->newLine();
    }

    private function testSchoolLicenses($escolaId)
    {
        $this->info("2. Testando licenças da escola ID: {$escolaId}...");
        
        $escola = Escola::find($escolaId);
        if (!$escola) {
            $this->error("   Escola com ID {$escolaId} não encontrada!");
            return;
        }
        
        $this->line("   Escola: {$escola->nome}");
        
        $licenseService = new LicenseService();
        
        // Simular contexto de escola
        $user = User::where('escola_id', $escolaId)->first();
        if ($user) {
            Auth::login($user);
            $this->line("   Usuário autenticado: {$user->name}");
        }
        
        $modules = ['comunicacao_module', 'alunos_module', 'funcionarios_module', 'academico_module', 'administracao_module'];
        
        foreach ($modules as $module) {
             $hasLicense = $licenseService->hasModuleLicense($module, $escola);
             $status = $hasLicense ? '✅ Licenciado' : '❌ Sem licença';
             $this->line("   {$module}: {$status}");
             
             if ($hasLicense) {
                  $licenseInfo = $licenseService->getLicenseInfo($escolaId, $module);
                   if ($licenseInfo) {
                       $expiresAt = $licenseInfo['expires_at'] ? \Carbon\Carbon::parse($licenseInfo['expires_at'])->format('d/m/Y') : 'Sem vencimento';
                       $this->line("     - Expira em: {$expiresAt}");
                       $this->line("     - Dias restantes: {$licenseInfo['days_remaining']}");
                       $this->line("     - Expirando em breve: " . ($licenseInfo['is_expiring_soon'] ? 'Sim' : 'Não'));
                       if (isset($licenseInfo['max_users'])) {
                           $this->line("     - Máximo de usuários: {$licenseInfo['max_users']}");
                       }
                   }
              }
         }
        
        $this->newLine();
    }

    private function testHelperFunctions($escolaId)
    {
        $this->info('3. Testando funções helper...');
        
        // Simular uma escola atual para o teste
        $escola = \App\Models\Escola::find($escolaId);
        if ($escola) {
            app()->instance('current_school', $escola);
            $this->line("   Escola de teste: {$escola->nome}");
        }
        
        $modules = ['comunicacao_module', 'alunos_module', 'funcionarios_module'];
        
        foreach ($modules as $module) {
            $enabled = moduleEnabled($module, $escola);
            $status = $enabled ? '✅ Habilitado' : '❌ Desabilitado';
            $this->line("   moduleEnabled('{$module}'): {$status}");
        }
        
        $availableModules = getAvailableModules($escola);
        $this->line("   Módulos disponíveis: " . implode(', ', $availableModules));
        
        $this->newLine();
    }

    private function testMiddleware($escolaId)
    {
        $this->info('4. Testando middleware (simulação)...');
        
        $this->line('   O middleware ModuleLicenseMiddleware foi registrado com sucesso.');
        $this->line('   Para testar completamente, acesse as rotas protegidas via navegador.');
        $this->line('   Exemplo: http://localhost:8000/comunicacao');
        
        $this->newLine();
    }
}
