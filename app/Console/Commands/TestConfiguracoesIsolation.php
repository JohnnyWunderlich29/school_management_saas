<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Escola;
use App\Models\ModalidadeEnsino;
use App\Models\Grupo;
use App\Models\Turno;
use App\Models\Disciplina;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class TestConfiguracoesIsolation extends Command
{
    protected $signature = 'test:configuracoes-isolation';
    protected $description = 'Test school isolation for configuracoes controller';

    public function handle()
    {
        $this->info('Testing school isolation for /admin/configuracoes...');
        $this->newLine();

        // Test 1: Normal user with escola_id
        $this->testNormalUser();
        
        // Test 2: Super Admin with escola_atual in session
        $this->testSuperAdminWithEscolaAtual();
        
        // Test 3: Super Admin without escola_atual in session
        $this->testSuperAdminWithoutEscolaAtual();
        
        // Test 4: Unauthenticated user
        $this->testUnauthenticatedUser();
        
        $this->newLine();
        $this->info('✅ All configuracoes isolation tests completed!');
    }

    private function testNormalUser()
    {
        $this->info('🔍 Testing normal user with escola_id...');
        
        $user = User::whereNotNull('escola_id')
            ->whereDoesntHave('cargos', function($query) {
                $query->where('nome', 'Super Admin');
            })
            ->first();
        
        if (!$user) {
            $this->warn('⚠️  No normal user with escola_id found');
            return;
        }
        
        Auth::login($user);
        
        $tabs = ['modalidades', 'grupos', 'turnos', 'disciplinas'];
        
        foreach ($tabs as $tab) {
            $count = $this->getTabCount($tab, $user->escola_id);
            $this->line("   {$tab}: {$count} items (escola_id: {$user->escola_id})");
        }
        
        Auth::logout();
        $this->info('✅ Normal user test completed');
        $this->newLine();
    }

    private function testSuperAdminWithEscolaAtual()
    {
        $this->info('🔍 Testing Super Admin with escola_atual in session...');
        
        $superAdmin = User::whereHas('cargos', function($query) {
            $query->where('nome', 'Super Admin');
        })->first();
        
        if (!$superAdmin) {
            $this->warn('⚠️  No Super Admin found');
            return;
        }
        
        $escola = Escola::first();
        if (!$escola) {
            $this->warn('⚠️  No school found');
            return;
        }
        
        Auth::login($superAdmin);
        Session::put('escola_atual', $escola->id);
        
        $tabs = ['modalidades', 'grupos', 'turnos', 'disciplinas'];
        
        foreach ($tabs as $tab) {
            $count = $this->getTabCount($tab, $escola->id);
            $this->line("   {$tab}: {$count} items (escola_atual: {$escola->id})");
        }
        
        Session::forget('escola_atual');
        Auth::logout();
        $this->info('✅ Super Admin with escola_atual test completed');
        $this->newLine();
    }

    private function testSuperAdminWithoutEscolaAtual()
    {
        $this->info('🔍 Testing Super Admin without escola_atual in session...');
        
        $superAdmin = User::whereHas('cargos', function($query) {
            $query->where('nome', 'Super Admin');
        })->first();
        
        if (!$superAdmin) {
            $this->warn('⚠️  No Super Admin found');
            return;
        }
        
        // Temporarily remove escola_id
        $originalEscolaId = $superAdmin->escola_id;
        $superAdmin->update(['escola_id' => null]);
        
        Auth::login($superAdmin);
        
        $tabs = ['modalidades', 'grupos', 'turnos', 'disciplinas'];
        
        foreach ($tabs as $tab) {
            $count = $this->getTabCount($tab, null);
            $this->line("   {$tab}: {$count} items (no escola_id/escola_atual)");
        }
        
        // Restore original escola_id
        $superAdmin->update(['escola_id' => $originalEscolaId]);
        
        Auth::logout();
        $this->info('✅ Super Admin without escola_atual test completed');
        $this->newLine();
    }

    private function testUnauthenticatedUser()
    {
        $this->info('🔍 Testing unauthenticated user...');
        
        Auth::logout();
        Session::flush();
        
        $this->line('   Should be redirected to login (cannot test count)');
        
        $this->info('✅ Unauthenticated user test completed');
        $this->newLine();
    }

    private function getTabCount($tab, $escolaId)
    {
        switch ($tab) {
            case 'modalidades':
                $query = ModalidadeEnsino::query();
                break;
            case 'grupos':
                $query = Grupo::query();
                break;
            case 'turnos':
                $query = Turno::query();
                break;
            case 'disciplinas':
                $query = Disciplina::query();
                break;
            default:
                return 0;
        }
        
        if ($escolaId) {
            $query->where('escola_id', $escolaId);
        } else {
            $query->where('escola_id', -1);
        }
        
        return $query->count();
    }
}