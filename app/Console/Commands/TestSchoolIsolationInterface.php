<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Sala;
use Illuminate\Support\Facades\Auth;

class TestSchoolIsolationInterface extends Command
{
    protected $signature = 'test:school-isolation-interface';
    protected $description = 'Testa o isolamento por escola nas interfaces (listagens)';

    public function handle()
    {
        $this->info('=== TESTE DE ISOLAMENTO NAS INTERFACES ===');
        $this->newLine();

        // Teste 1: Usuário normal
        $this->info('1. Testando usuário normal...');
        $user = User::whereNotNull('escola_id')->first();
        if (!$user) {
            $this->error('Nenhum usuário normal encontrado');
            return;
        }
        
        Auth::login($user);
        $this->info("   Usuário: {$user->name} (Escola ID: {$user->escola_id})");
        
        // Simular consulta do SalaController::index
        $query = Sala::query();
        if (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte')) {
            if (session('escola_atual')) {
                $query->where('escola_id', session('escola_atual'));
            } else {
                // Se não há escola_atual na sessão, não mostrar nenhuma sala
                $query->where('escola_id', -1);
            }
        } else {
            if (auth()->user()->escola_id) {
                $query->where('escola_id', auth()->user()->escola_id);
            } else {
                // Se usuário não tem escola_id, não mostrar nenhuma sala
                $query->where('escola_id', -1);
            }
        }
        $salas = $query->get();
        
        $this->info("   Salas encontradas: {$salas->count()}");
        foreach ($salas as $sala) {
            $this->info("   - {$sala->nome} (Escola ID: {$sala->escola_id})");
        }
        
        // Verificar se todas as salas são da escola correta
        $salasIncorretas = $salas->where('escola_id', '!=', $user->escola_id);
        if ($salasIncorretas->count() > 0) {
            $this->error("   ❌ ERRO: Encontradas {$salasIncorretas->count()} salas de outras escolas!");
        } else {
            $this->info('   ✓ Todas as salas são da escola correta');
        }
        
        $this->newLine();
        
        // Teste 2: Super Admin com escola_atual
        $this->info('2. Testando Super Admin COM escola_atual na sessão...');
        $superAdmin = User::whereHas('cargos', function($query) {
            $query->where('nome', 'Super Administrador');
        })->first();
        if (!$superAdmin) {
            $this->error('Super Admin não encontrado');
            return;
        }
        
        Auth::login($superAdmin);
        session(['escola_atual' => 1]);
        $this->info("   Super Admin: {$superAdmin->name} com escola_atual: 1");
        
        // Simular consulta do SalaController::index
        $query = Sala::query();
        if (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte')) {
            if (session('escola_atual')) {
                $query->where('escola_id', session('escola_atual'));
            } else {
                // Se não há escola_atual na sessão, não mostrar nenhuma sala
                $query->where('escola_id', -1);
            }
        } else {
            if (auth()->user()->escola_id) {
                $query->where('escola_id', auth()->user()->escola_id);
            } else {
                // Se usuário não tem escola_id, não mostrar nenhuma sala
                $query->where('escola_id', -1);
            }
        }
        $salas = $query->get();
        
        $this->info("   Salas encontradas: {$salas->count()}");
        foreach ($salas as $sala) {
            $this->info("   - {$sala->nome} (Escola ID: {$sala->escola_id})");
        }
        
        // Verificar se todas as salas são da escola da sessão
        $salasIncorretas = $salas->where('escola_id', '!=', 1);
        if ($salasIncorretas->count() > 0) {
            $this->error("   ❌ ERRO: Encontradas {$salasIncorretas->count()} salas de outras escolas!");
        } else {
            $this->info('   ✓ Todas as salas são da escola da sessão (1)');
        }
        
        $this->newLine();
        
        // Teste 3: Super Admin sem escola_atual
        $this->info('3. Testando Super Admin SEM escola_atual na sessão...');
        session()->forget('escola_atual');
        
        // Temporariamente remover escola_id do Super Admin para simular o cenário
        $escolaIdOriginal = $superAdmin->escola_id;
        $superAdmin->escola_id = null;
        $superAdmin->save();
        
        $this->info("   Super Admin: {$superAdmin->name} SEM escola_atual na sessão e SEM escola_id");
        
        // Simular consulta do SalaController::index
        $query = Sala::query();
        if (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte')) {
            $escolaId = session('escola_atual') ?: auth()->user()->escola_id;
            if ($escolaId) {
                $query->where('escola_id', $escolaId);
            } else {
                // Se não há escola_atual na sessão, não mostrar nenhuma sala
                $query->where('escola_id', -1);
            }
        } else {
            if (auth()->user()->escola_id) {
                $query->where('escola_id', auth()->user()->escola_id);
            } else {
                // Se usuário não tem escola_id, não mostrar nenhuma sala
                $query->where('escola_id', -1);
            }
        }
        $salas = $query->get();
        
        $this->info("   Salas encontradas: {$salas->count()}");
        if ($salas->count() > 0) {
            $this->error('   ❌ ERRO: Super Admin sem escola_atual deveria ver 0 salas!');
            foreach ($salas as $sala) {
                $this->error("   - {$sala->nome} (Escola ID: {$sala->escola_id})");
            }
        } else {
            $this->info('   ✓ Nenhuma sala encontrada (correto)');
        }
        
        // Restaurar escola_id original do Super Admin
        $superAdmin->escola_id = $escolaIdOriginal;
        $superAdmin->save();
        
        $this->newLine();
        $this->info('=== TESTE DE INTERFACES CONCLUÍDO ===');
        
        Auth::logout();
        session()->flush();
    }
}