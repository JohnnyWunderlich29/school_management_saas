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

class TestSchoolIsolationSimple extends Command
{
    protected $signature = 'test:school-isolation-simple';
    protected $description = 'Teste simples de isolamento por escola';

    public function handle()
    {
        $this->info('=== TESTE SIMPLES DE ISOLAMENTO POR ESCOLA ===');
        $this->newLine();
        
        // Verificar estrutura de dados
        $this->info('1. Verificando estrutura de dados...');
        $this->checkDataStructure();
        $this->newLine();
        
        // Verificar métodos resolveRouteBinding
        $this->info('2. Verificando métodos resolveRouteBinding...');
        $this->checkResolveRouteBindingMethods();
        $this->newLine();
        
        // Teste funcional básico
        $this->info('3. Teste funcional básico...');
        $this->basicFunctionalTest();
        
        $this->info('=== TESTE CONCLUÍDO ===');
        return 0;
    }
    
    private function checkDataStructure()
    {
        $escolas = \App\Models\Escola::count();
        $users = User::count();
        $usersWithSchool = User::whereNotNull('escola_id')->count();
        
        $this->line("   Escolas: {$escolas}");
        $this->line("   Usuários: {$users}");
        $this->line("   Usuários com escola_id: {$usersWithSchool}");
        
        $models = [
            'Salas' => Sala::class,
            'ModalidadeEnsino' => ModalidadeEnsino::class,
            'Grupos' => Grupo::class,
            'Turnos' => Turno::class,
            'Disciplinas' => Disciplina::class
        ];
        
        foreach ($models as $name => $class) {
            $count = $class::count();
            $withSchool = $class::whereNotNull('escola_id')->count();
            $this->line("   {$name}: {$count} (com escola_id: {$withSchool})");
        }
    }
    
    private function checkResolveRouteBindingMethods()
    {
        $models = [
            'Sala' => Sala::class,
            'User' => User::class,
            'ModalidadeEnsino' => ModalidadeEnsino::class,
            'Grupo' => Grupo::class,
            'Turno' => Turno::class,
            'Disciplina' => Disciplina::class
        ];
        
        foreach ($models as $name => $class) {
            if (method_exists($class, 'resolveRouteBinding')) {
                $this->line("   <fg=green>✓</> {$name}: método resolveRouteBinding implementado");
            } else {
                $this->line("   <fg=red>✗</> {$name}: método resolveRouteBinding NÃO implementado");
            }
        }
    }
    
    private function basicFunctionalTest()
    {
        // Buscar um usuário com escola_id
        $user = User::whereNotNull('escola_id')->first();
        
        if (!$user) {
            $this->error('   Nenhum usuário com escola_id encontrado');
            return;
        }
        
        $this->line("   Testando com usuário: {$user->name} (Escola ID: {$user->escola_id})");
        
        Auth::login($user);
        
        // Testar cada modelo
        $models = [
            'Sala' => Sala::class,
            'ModalidadeEnsino' => ModalidadeEnsino::class,
            'Grupo' => Grupo::class,
            'Turno' => Turno::class,
            'Disciplina' => Disciplina::class
        ];
        
        foreach ($models as $name => $class) {
            $this->testModelBinding($name, $class, $user->escola_id);
        }
        
        Auth::logout();
    }
    
    private function testModelBinding($modelName, $modelClass, $userSchoolId)
    {
        try {
            // Buscar um registro da mesma escola do usuário
            $sameSchoolRecord = $modelClass::where('escola_id', $userSchoolId)->first();
            
            if ($sameSchoolRecord) {
                $resolved = (new $modelClass)->resolveRouteBinding($sameSchoolRecord->id);
                if ($resolved && $resolved->escola_id == $userSchoolId) {
                    $this->line("   <fg=green>✓</> {$modelName}: Acesso permitido à própria escola");
                } else {
                    $this->line("   <fg=red>✗</> {$modelName}: Falha ao acessar própria escola");
                }
            } else {
                $this->line("   <fg=yellow>⚠</> {$modelName}: Nenhum registro encontrado na escola {$userSchoolId}");
            }
            
            // Buscar um registro de outra escola
            $otherSchoolRecord = $modelClass::where('escola_id', '!=', $userSchoolId)->first();
            
            if ($otherSchoolRecord) {
                $resolved = (new $modelClass)->resolveRouteBinding($otherSchoolRecord->id);
                if ($resolved === null) {
                    $this->line("   <fg=green>✓</> {$modelName}: Acesso negado a outra escola (isolamento OK)");
                } else {
                    $this->line("   <fg=red>✗</> {$modelName}: FALHA DE SEGURANÇA - Acessou outra escola!");
                }
            } else {
                $this->line("   <fg=yellow>⚠</> {$modelName}: Nenhum registro de outra escola para testar");
            }
            
        } catch (\Exception $e) {
            $this->line("   <fg=red>✗</> {$modelName}: Erro - {$e->getMessage()}");
        }
    }
}