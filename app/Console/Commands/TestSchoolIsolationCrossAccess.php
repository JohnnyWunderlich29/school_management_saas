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

class TestSchoolIsolationCrossAccess extends Command
{
    protected $signature = 'test:school-isolation-cross';
    protected $description = 'Testa se usuários não conseguem acessar dados de outras escolas';

    public function handle()
    {
        $this->info('=== TESTE DE ISOLAMENTO CRUZADO ENTRE ESCOLAS ===');
        $this->newLine();
        
        // Buscar usuários de escolas diferentes
        $escola1Users = User::where('escola_id', 1)->where('email', '!=', 'admin@escola.com')->first();
        $escola2Users = User::where('escola_id', 2)->where('email', '!=', 'admin@escola.com')->first();
        
        if (!$escola1Users || !$escola2Users) {
            $this->error('Não foi possível encontrar usuários de escolas diferentes para o teste.');
            $this->info('Criando dados de teste...');
            $this->createTestData();
            return 0;
        }
        
        $this->info("Usuário Escola 1: {$escola1Users->name} (ID: {$escola1Users->escola_id})");
        $this->info("Usuário Escola 2: {$escola2Users->name} (ID: {$escola2Users->escola_id})");
        $this->newLine();
        
        // Teste 1: Usuário da escola 1 tentando acessar dados da escola 2
        $this->testCrossSchoolAccess($escola1Users, 2, 'Escola 1 → Escola 2');
        
        // Teste 2: Usuário da escola 2 tentando acessar dados da escola 1
        $this->testCrossSchoolAccess($escola2Users, 1, 'Escola 2 → Escola 1');
        
        $this->info('=== TESTES DE ISOLAMENTO CRUZADO CONCLUÍDOS ===');
        return 0;
    }
    
    private function testCrossSchoolAccess($user, $targetSchoolId, $testName)
    {
        $this->info("Testando acesso cruzado: {$testName}");
        
        Auth::login($user);
        
        $models = [
            'Sala' => Sala::class,
            'ModalidadeEnsino' => ModalidadeEnsino::class,
            'Grupo' => Grupo::class,
            'Turno' => Turno::class,
            'Disciplina' => Disciplina::class
        ];
        
        foreach ($models as $modelName => $modelClass) {
            $this->testModelCrossAccess($modelName, $modelClass, $targetSchoolId);
        }
        
        Auth::logout();
        $this->newLine();
    }
    
    private function testModelCrossAccess($modelName, $modelClass, $targetSchoolId)
    {
        try {
            // Buscar um registro da escola alvo
            $targetRecord = $modelClass::where('escola_id', $targetSchoolId)->first();
            
            if (!$targetRecord) {
                $this->line("   <fg=yellow>⚠</> {$modelName}: Nenhum registro encontrado na escola {$targetSchoolId}");
                return;
            }
            
            // Tentar resolver via route binding
            $resolved = (new $modelClass)->resolveRouteBinding($targetRecord->id);
            
            if ($resolved === null) {
                $this->line("   <fg=green>✓</> {$modelName}: Acesso negado corretamente (isolamento funcionando)");
            } else {
                $this->line("   <fg=red>✗</> {$modelName}: FALHA DE SEGURANÇA - Conseguiu acessar registro de outra escola!");
            }
            
        } catch (\Exception $e) {
            $this->line("   <fg=red>✗</> {$modelName}: Erro durante teste - {$e->getMessage()}");
        }
    }
    
    private function createTestData()
    {
        $this->info('Verificando se existem pelo menos 2 escolas...');
        
        $escolas = \App\Models\Escola::count();
        $this->info("Escolas encontradas: {$escolas}");
        
        if ($escolas < 2) {
            $this->error('É necessário ter pelo menos 2 escolas para testar o isolamento cruzado.');
            $this->info('Por favor, crie mais escolas no sistema antes de executar este teste.');
        }
        
        $users = User::whereNotNull('escola_id')->where('email', '!=', 'admin@escola.com')->count();
        $this->info("Usuários com escola_id encontrados: {$users}");
        
        if ($users < 2) {
            $this->error('É necessário ter usuários de escolas diferentes para testar o isolamento.');
        }
    }
}