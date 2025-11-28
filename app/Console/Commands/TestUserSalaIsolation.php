<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Sala;
use App\Models\Escola;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class TestUserSalaIsolation extends Command
{
    protected $signature = 'test:user-sala-isolation';
    protected $description = 'Testa o isolamento por escola no UserSalaController';

    public function handle()
    {
        $this->info('=== TESTE DE ISOLAMENTO - USER SALAS ===');
        $this->newLine();

        // Buscar escolas e usuários para teste
        $escola1 = Escola::first();
        $escola2 = Escola::skip(1)->first();
        
        if (!$escola1 || !$escola2) {
            $this->error('É necessário ter pelo menos 2 escolas para o teste.');
            return 1;
        }

        $this->info("Escola 1: {$escola1->nome} (ID: {$escola1->id})");
        $this->info("Escola 2: {$escola2->nome} (ID: {$escola2->id})");
        $this->newLine();

        // Buscar usuários de cada escola
        $userEscola1 = User::where('escola_id', $escola1->id)->whereHas('cargos', function($q) {
            $q->where('nome', 'not like', '%Super Admin%')
              ->where('nome', 'not like', '%Suporte%');
        })->first();
        
        $superAdmin = User::whereHas('cargos', function($q) {
            $q->where('nome', 'like', '%Super Admin%')
              ->orWhere('nome', 'like', '%Suporte%');
        })->first();

        if (!$userEscola1 || !$superAdmin) {
            $this->error('Usuários necessários não encontrados.');
            return 1;
        }

        $this->info("Usuário Normal: {$userEscola1->name} (Escola: {$userEscola1->escola_id})");
        $this->info("Super Admin: {$superAdmin->name} (Escola: {$superAdmin->escola_id})");
        $this->newLine();

        // Contar usuários professores por escola
        $professoresEscola1 = User::where('escola_id', $escola1->id)
            ->whereHas('cargos', function($query) {
                $query->where(function($q) {
                    $q->where('tipo_cargo', 'professor')
                      ->orWhere('nome', 'like', '%professor%');
                })->where('ativo', true);
            })->count();
            
        $professoresEscola2 = User::where('escola_id', $escola2->id)
            ->whereHas('cargos', function($query) {
                $query->where(function($q) {
                    $q->where('tipo_cargo', 'professor')
                      ->orWhere('nome', 'like', '%professor%');
                })->where('ativo', true);
            })->count();

        $this->info("Professores na Escola 1: {$professoresEscola1}");
        $this->info("Professores na Escola 2: {$professoresEscola2}");
        $this->newLine();

        // Teste 1: Usuário normal vê apenas professores da sua escola
        $this->info('=== TESTE 1: Usuário Normal ===');
        Auth::login($userEscola1);
        Session::forget('escola_atual');
        
        $professoresVisiveis = $this->simularUserSalaIndex($userEscola1->escola_id);
        $this->info("Professores visíveis: {$professoresVisiveis}");
        
        if ($professoresVisiveis === $professoresEscola1) {
            $this->info('✅ PASSOU: Usuário normal vê apenas professores da sua escola');
        } else {
            $this->error('❌ FALHOU: Usuário normal deveria ver apenas professores da sua escola');
        }
        $this->newLine();

        // Teste 2: Super Admin com escola_atual na sessão
        $this->info('=== TESTE 2: Super Admin com escola_atual ===');
        Auth::login($superAdmin);
        Session::put('escola_atual', $escola1->id);
        
        $professoresVisiveis = $this->simularUserSalaIndex($escola1->id);
        $this->info("Professores visíveis: {$professoresVisiveis}");
        
        if ($professoresVisiveis === $professoresEscola1) {
            $this->info('✅ PASSOU: Super Admin com escola_atual vê professores da escola selecionada');
        } else {
            $this->error('❌ FALHOU: Super Admin com escola_atual deveria ver professores da escola selecionada');
        }
        $this->newLine();

        // Teste 3: Super Admin sem escola_atual na sessão
        $this->info('=== TESTE 3: Super Admin sem escola_atual ===');
        Auth::login($superAdmin);
        Session::forget('escola_atual');
        
        // Temporariamente remover escola_id do Super Admin para simular cenário sem escola
        $originalEscolaId = $superAdmin->escola_id;
        $superAdmin->update(['escola_id' => null]);
        
        $professoresVisiveis = $this->simularUserSalaIndex(null);
        $this->info("Professores visíveis: {$professoresVisiveis}");
        
        if ($professoresVisiveis === 0) {
            $this->info('✅ PASSOU: Super Admin sem escola não vê nenhum professor');
        } else {
            $this->error('❌ FALHOU: Super Admin sem escola deveria ver 0 professores');
        }
        
        // Restaurar escola_id do Super Admin
        $superAdmin->update(['escola_id' => $originalEscolaId]);
        $this->newLine();

        // Teste 4: Usuário não autenticado
        $this->info('=== TESTE 4: Usuário não autenticado ===');
        Auth::logout();
        Session::flush();
        
        $professoresVisiveis = $this->simularUserSalaIndex(null);
        $this->info("Professores visíveis: {$professoresVisiveis}");
        
        if ($professoresVisiveis === 0) {
            $this->info('✅ PASSOU: Usuário não autenticado não vê nenhum professor');
        } else {
            $this->error('❌ FALHOU: Usuário não autenticado deveria ver 0 professores');
        }
        
        $this->newLine();
        $this->info('=== TESTE CONCLUÍDO ===');
        
        return 0;
    }

    private function simularUserSalaIndex($escolaId)
    {
        // Simular a lógica do UserSalaController::index
        $user = auth()->user();
        
        if (!$user) {
            return 0; // Usuário não autenticado
        }
        
        // Determinar escola_id para filtros (seguindo padrão do CargoController)
        if ($user->isSuperAdmin() || $user->temCargo('Suporte')) {
            $escolaIdFiltro = session('escola_atual') ?: $user->escola_id;
        } else {
            $escolaIdFiltro = $user->escola_id;
        }
        
        // Se não há escola definida, não mostrar nenhum usuário
        if (!$escolaIdFiltro) {
            return 0;
        }
        
        return User::where('escola_id', $escolaIdFiltro)
            ->whereHas('cargos', function($query) use ($escolaIdFiltro) {
                $query->where(function($q) {
                    $q->where('tipo_cargo', 'professor')
                      ->orWhere('nome', 'like', '%professor%');
                })
                ->where('ativo', true)
                ->where(function($subQuery) use ($escolaIdFiltro) {
                    $subQuery->whereNull('escola_id') // Cargos globais
                             ->orWhere('escola_id', $escolaIdFiltro); // Cargos da escola
                });
            })->count();
    }
}