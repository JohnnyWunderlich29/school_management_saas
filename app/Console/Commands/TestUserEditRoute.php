<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

class TestUserEditRoute extends Command
{
    protected $signature = 'test:user-edit-route {user_id}';
    protected $description = 'Testa a rota de edição de usuários';

    public function handle()
    {
        $userId = $this->argument('user_id');
        
        $this->info('=== TESTE DA ROTA DE EDIÇÃO DE USUÁRIOS ===');
        
        // Verificar se o usuário existe
        $user = User::find($userId);
        if (!$user) {
            $this->error("Usuário com ID {$userId} não encontrado!");
            return Command::FAILURE;
        }
        
        $this->line("Usuário encontrado: {$user->name} ({$user->email})");
        
        // Verificar se a rota existe
        $routeName = 'admin.users.edit';
        if (!Route::has($routeName)) {
            $this->error("Rota '{$routeName}' não encontrada!");
            return Command::FAILURE;
        }
        
        $this->info("Rota '{$routeName}' encontrada!");
        
        // Gerar URL da rota
        try {
            $url = route($routeName, $user);
            $this->line("URL gerada: {$url}");
        } catch (\Exception $e) {
            $this->error("Erro ao gerar URL: " . $e->getMessage());
            return Command::FAILURE;
        }
        
        // Verificar super admin
        $superAdmin = User::whereHas('cargos', function($query) {
            $query->where('nome', 'Super Administrador');
        })->first();
        
        if (!$superAdmin) {
            $this->error('Nenhum super administrador encontrado!');
            return Command::FAILURE;
        }
        
        $this->line("Super Admin encontrado: {$superAdmin->name}");
        $this->line("isSuperAdmin(): " . ($superAdmin->isSuperAdmin() ? 'true' : 'false'));
        
        // Simular login
        Auth::login($superAdmin);
        $this->info('Super Admin logado com sucesso!');
        
        // Verificar se pode acessar a rota
        if (auth()->check() && auth()->user()->isSuperAdmin()) {
            $this->info('✅ Usuário autenticado e é Super Admin!');
        } else {
            $this->error('❌ Problema com autenticação ou permissões!');
        }
        
        // Verificar se a view existe
        $viewPath = 'admin.users.edit';
        if (view()->exists($viewPath)) {
            $this->info("✅ View '{$viewPath}' existe!");
        } else {
            $this->error("❌ View '{$viewPath}' não encontrada!");
        }
        
        Auth::logout();
        
        return Command::SUCCESS;
    }
}