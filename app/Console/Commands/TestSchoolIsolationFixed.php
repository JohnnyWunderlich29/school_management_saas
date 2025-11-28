<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Sala;
use App\Models\ModalidadeEnsino;
use App\Models\Grupo;
use App\Models\Turno;
use App\Models\Disciplina;
use Illuminate\Support\Facades\Auth;

class TestSchoolIsolationFixed extends Command
{
    protected $signature = 'test:school-isolation-fixed';
    protected $description = 'Teste corrigido de isolamento por escola';

    public function handle()
    {
        $this->info('=== TESTE CORRIGIDO DE ISOLAMENTO POR ESCOLA ===');
        $this->newLine();
        
        // Teste 1: Usuário normal
        $this->testNormalUser();
        
        // Teste 2: Super Admin com escola_atual na sessão
        $this->testSuperAdminWithSession();
        
        // Teste 3: Super Admin sem escola_atual na sessão
        $this->testSuperAdminWithoutSession();
        
        $this->info('=== TESTE CONCLUÍDO ===');
        return 0;
    }
    
    private function testNormalUser()
    {
        $this->info('1. Testando usuário normal...');
        
        // Buscar um usuário que não seja Super Admin
        $normalUser = User::whereNotNull('escola_id')
                         ->whereDoesntHave('cargos', function($query) {
                             $query->where('nome', 'Super Administrador');
                         })
                         ->first();
        
        if (!$normalUser) {
            $this->line('   <fg=yellow>⚠</> Nenhum usuário normal encontrado');
            $this->newLine();
            return;
        }
        
        $this->line("   Usuário: {$normalUser->name} (Escola ID: {$normalUser->escola_id})");
        
        Auth::login($normalUser);
        
        // Testar acesso à própria escola
        $this->testModelAccess('Sala', Sala::class, $normalUser->escola_id, true);
        
        // Testar acesso negado a outra escola
        $otherSchoolId = $normalUser->escola_id == 1 ? 2 : 1;
        $this->testModelAccess('Sala', Sala::class, $otherSchoolId, false);
        
        Auth::logout();
        $this->newLine();
    }
    
    private function testSuperAdminWithSession()
    {
        $this->info('2. Testando Super Admin COM escola_atual na sessão...');
        
        $superAdmin = User::whereHas('cargos', function($query) {
            $query->where('nome', 'Super Administrador');
        })->first();
        
        if (!$superAdmin) {
            $this->line('   <fg=yellow>⚠</> Super Admin não encontrado');
            $this->newLine();
            return;
        }
        
        Auth::login($superAdmin);
        
        // Definir escola_atual na sessão
        $escolaAtual = 1;
        session(['escola_atual' => $escolaAtual]);
        
        $this->line("   Super Admin: {$superAdmin->name} com escola_atual: {$escolaAtual}");
        
        // Testar acesso à escola da sessão
        $this->testModelAccess('Sala', Sala::class, $escolaAtual, true);
        
        // Testar acesso negado a outra escola
        $otherSchoolId = $escolaAtual == 1 ? 2 : 1;
        $this->testModelAccess('Sala', Sala::class, $otherSchoolId, false);
        
        session()->forget('escola_atual');
        Auth::logout();
        $this->newLine();
    }
    
    private function testSuperAdminWithoutSession()
    {
        $this->info('3. Testando Super Admin SEM escola_atual na sessão...');
        
        $superAdmin = User::whereHas('cargos', function($query) {
            $query->where('nome', 'Super Administrador');
        })->first();
        
        if (!$superAdmin) {
            $this->line('   <fg=yellow>⚠</> Super Admin não encontrado');
            $this->newLine();
            return;
        }
        
        Auth::login($superAdmin);
        
        // NÃO definir escola_atual na sessão
        session()->forget('escola_atual');
        
        $this->line("   Super Admin: {$superAdmin->name} SEM escola_atual na sessão");
        
        // Testar se consegue acessar qualquer escola (deveria retornar null)
        $anyRecord = Sala::first();
        if ($anyRecord) {
            $resolved = (new Sala)->resolveRouteBinding($anyRecord->id);
            if ($resolved === null) {
                $this->line('   <fg=green>✓</> Sala: Acesso negado sem escola_atual (correto)');
            } else {
                $this->line('   <fg=red>✗</> Sala: FALHA - Conseguiu acessar sem escola_atual!');
            }
        }
        
        Auth::logout();
        $this->newLine();
    }
    
    private function testModelAccess($modelName, $modelClass, $schoolId, $shouldAccess)
    {
        try {
            $record = $modelClass::where('escola_id', $schoolId)->first();
            
            if (!$record) {
                $this->line("   <fg=yellow>⚠</> {$modelName}: Nenhum registro encontrado na escola {$schoolId}");
                return;
            }
            
            $resolved = (new $modelClass)->resolveRouteBinding($record->id);
            
            if ($shouldAccess) {
                if ($resolved && $resolved->escola_id == $schoolId) {
                    $this->line("   <fg=green>✓</> {$modelName}: Acesso permitido à escola {$schoolId} (correto)");
                } else {
                    $this->line("   <fg=red>✗</> {$modelName}: FALHA - Não conseguiu acessar escola {$schoolId}!");
                }
            } else {
                if ($resolved === null) {
                    $this->line("   <fg=green>✓</> {$modelName}: Acesso negado à escola {$schoolId} (correto)");
                } else {
                    $this->line("   <fg=red>✗</> {$modelName}: FALHA DE SEGURANÇA - Conseguiu acessar escola {$schoolId}!");
                }
            }
            
        } catch (\Exception $e) {
            $this->line("   <fg=red>✗</> {$modelName}: Erro - {$e->getMessage()}");
        }
    }
}