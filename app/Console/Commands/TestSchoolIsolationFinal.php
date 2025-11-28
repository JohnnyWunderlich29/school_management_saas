<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Sala;
use App\Models\ModalidadeEnsino;
use App\Models\Grupo;
use App\Models\Turno;
use App\Models\Disciplina;
use App\Models\Transferencia;
use Illuminate\Support\Facades\Auth;

class TestSchoolIsolationFinal extends Command
{
    protected $signature = 'test:school-isolation-final';
    protected $description = 'Teste final completo de isolamento por escola';

    private $models = [
        'Sala' => Sala::class,
        'User' => User::class,
        'ModalidadeEnsino' => ModalidadeEnsino::class,
        'Grupo' => Grupo::class,
        'Turno' => Turno::class,
        'Disciplina' => Disciplina::class,
        'Transferencia' => Transferencia::class,
    ];

    public function handle()
    {
        $this->info('=== TESTE FINAL DE ISOLAMENTO POR ESCOLA ===');
        $this->newLine();
        
        // Teste 1: Usuário normal
        $this->testNormalUser();
        
        // Teste 2: Super Admin com escola_atual na sessão
        $this->testSuperAdminWithSession();
        
        // Teste 3: Super Admin sem escola_atual na sessão
        $this->testSuperAdminWithoutSession();
        
        // Teste 4: Usuário não autenticado
        $this->testUnauthenticatedUser();
        
        $this->info('=== TESTE FINAL CONCLUÍDO ===');
        return 0;
    }
    
    private function testNormalUser()
    {
        $this->info('1. Testando usuário normal...');
        
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
        
        foreach ($this->models as $modelName => $modelClass) {
            $this->testModelAccess($modelName, $modelClass, $normalUser->escola_id, true);
        }
        
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
        
        $escolaAtual = 1;
        session(['escola_atual' => $escolaAtual]);
        
        $this->line("   Super Admin: {$superAdmin->name} com escola_atual: {$escolaAtual}");
        
        foreach ($this->models as $modelName => $modelClass) {
            $this->testModelAccess($modelName, $modelClass, $escolaAtual, true);
        }
        
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
        session()->forget('escola_atual');
        
        $this->line("   Super Admin: {$superAdmin->name} SEM escola_atual na sessão");
        
        foreach ($this->models as $modelName => $modelClass) {
            $this->testModelAccessDenied($modelName, $modelClass);
        }
        
        Auth::logout();
        $this->newLine();
    }
    
    private function testUnauthenticatedUser()
    {
        $this->info('4. Testando usuário não autenticado...');
        
        Auth::logout();
        
        foreach ($this->models as $modelName => $modelClass) {
            $this->testModelAccessDenied($modelName, $modelClass);
        }
        
        $this->newLine();
    }
    
    private function testModelAccess($modelName, $modelClass, $schoolId, $shouldAccess)
    {
        try {
            if ($modelName === 'Transferencia') {
                // Para transferências, buscar por aluno da escola
                $record = $modelClass::whereHas('aluno', function($q) use ($schoolId) {
                    $q->where('escola_id', $schoolId);
                })->first();
            } else {
                $record = $modelClass::where('escola_id', $schoolId)->first();
            }
            
            if (!$record) {
                $this->line("   <fg=yellow>⚠</> {$modelName}: Nenhum registro encontrado na escola {$schoolId}");
                return;
            }
            
            $resolved = (new $modelClass)->resolveRouteBinding($record->id);
            
            if ($shouldAccess) {
                if ($resolved) {
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
    
    private function testModelAccessDenied($modelName, $modelClass)
    {
        try {
            $record = $modelClass::first();
            
            if (!$record) {
                $this->line("   <fg=yellow>⚠</> {$modelName}: Nenhum registro encontrado");
                return;
            }
            
            $resolved = (new $modelClass)->resolveRouteBinding($record->id);
            
            if ($resolved === null) {
                $this->line("   <fg=green>✓</> {$modelName}: Acesso negado (correto)");
            } else {
                $this->line("   <fg=red>✗</> {$modelName}: FALHA DE SEGURANÇA - Conseguiu acessar sem autorização!");
            }
            
        } catch (\Exception $e) {
            $this->line("   <fg=red>✗</> {$modelName}: Erro - {$e->getMessage()}");
        }
    }
}