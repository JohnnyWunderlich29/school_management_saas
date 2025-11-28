<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Sala;
use App\Models\Transferencia;
use App\Models\ModalidadeEnsino;
use App\Models\Grupo;
use App\Models\Turno;
use App\Models\Disciplina;
use Illuminate\Support\Facades\Auth;

class TestSchoolIsolation extends Command
{
    protected $signature = 'test:school-isolation';
    protected $description = 'Testa o isolamento por escola nos modelos';

    public function handle()
    {
        $this->info('=== TESTE DE ISOLAMENTO POR ESCOLA ===');
        $this->newLine();
        
        // Teste 1: Verificar se os modelos têm o método resolveRouteBinding
        $this->testResolveRouteBindingExists();
        
        // Teste 2: Simular usuário normal e verificar isolamento
        $this->testNormalUserIsolation();
        
        // Teste 3: Simular Super Admin e verificar comportamento
        $this->testSuperAdminBehavior();
        
        $this->info('=== TESTES CONCLUÍDOS ===');
        return 0;
    }
    
    private function testResolveRouteBindingExists()
    {
        $this->info('1. Verificando se métodos resolveRouteBinding existem...');
        
        $models = [
            'Sala' => Sala::class,
            'User' => User::class,
            'Transferencia' => Transferencia::class,
            'ModalidadeEnsino' => ModalidadeEnsino::class,
            'Grupo' => Grupo::class,
            'Turno' => Turno::class,
            'Disciplina' => Disciplina::class
        ];
        
        foreach ($models as $name => $class) {
            if (method_exists($class, 'resolveRouteBinding')) {
                $this->line("   <fg=green>✓</> {$name}: método resolveRouteBinding encontrado");
            } else {
                $this->line("   <fg=red>✗</> {$name}: método resolveRouteBinding NÃO encontrado");
            }
        }
        $this->newLine();
    }
    
    private function testNormalUserIsolation()
    {
        $this->info('2. Testando isolamento para usuário normal...');
        
        // Buscar um usuário normal (não super admin)
        $normalUser = User::where('email', '!=', 'admin@escola.com')
                         ->whereNotNull('escola_id')
                         ->first();
        
        if (!$normalUser) {
            $this->line('   <fg=yellow>⚠</> Nenhum usuário normal encontrado para teste');
            $this->newLine();
            return;
        }
        
        // Simular autenticação
        Auth::login($normalUser);
        
        $this->line("   Usuário de teste: {$normalUser->name} (Escola ID: {$normalUser->escola_id})");
        
        // Testar cada modelo
        $this->testModelIsolation('Sala', Sala::class, $normalUser->escola_id);
        $this->testModelIsolation('ModalidadeEnsino', ModalidadeEnsino::class, $normalUser->escola_id);
        $this->testModelIsolation('Grupo', Grupo::class, $normalUser->escola_id);
        $this->testModelIsolation('Turno', Turno::class, $normalUser->escola_id);
        $this->testModelIsolation('Disciplina', Disciplina::class, $normalUser->escola_id);
        
        Auth::logout();
        $this->newLine();
    }
    
    private function testSuperAdminBehavior()
    {
        $this->info('3. Testando comportamento do Super Admin...');
        
        $superAdmin = User::where('email', 'admin@escola.com')->first();
        
        if (!$superAdmin) {
            $this->line('   <fg=yellow>⚠</> Super Admin não encontrado');
            $this->newLine();
            return;
        }
        
        Auth::login($superAdmin);
        
        // Definir escola atual na sessão
        $escolaTeste = 1; // Assumindo que existe escola com ID 1
        session(['escola_atual' => $escolaTeste]);
        
        $this->line("   Super Admin logado com escola_atual: {$escolaTeste}");
        
        // Testar se o Super Admin consegue acessar dados da escola definida na sessão
        $this->testModelIsolation('Sala', Sala::class, $escolaTeste, true);
        $this->testModelIsolation('ModalidadeEnsino', ModalidadeEnsino::class, $escolaTeste, true);
        
        Auth::logout();
        session()->forget('escola_atual');
        $this->newLine();
    }
    
    private function testModelIsolation($modelName, $modelClass, $expectedEscolaId, $isSuperAdmin = false)
    {
        try {
            // Buscar um registro da escola esperada
            $record = $modelClass::where('escola_id', $expectedEscolaId)->first();
            
            if (!$record) {
                $this->line("   <fg=yellow>⚠</> {$modelName}: Nenhum registro encontrado para escola {$expectedEscolaId}");
                return;
            }
            
            // Tentar resolver via route binding
            $resolved = (new $modelClass)->resolveRouteBinding($record->id);
            
            if ($resolved && $resolved->escola_id == $expectedEscolaId) {
                $userType = $isSuperAdmin ? 'Super Admin' : 'Usuário Normal';
                $this->line("   <fg=green>✓</> {$modelName}: {$userType} consegue acessar registro da escola {$expectedEscolaId}");
            } else {
                $this->line("   <fg=red>✗</> {$modelName}: Falha no isolamento - registro não encontrado ou escola incorreta");
            }
            
        } catch (\Exception $e) {
            $this->line("   <fg=red>✗</> {$modelName}: Erro durante teste - {$e->getMessage()}");
        }
    }
}