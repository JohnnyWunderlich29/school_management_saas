<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Escola;
use App\Models\SchoolLicense;

class SuperAdminController extends Controller
{
    /**
     * Middleware para garantir que apenas super administradores acessem
     */
    public function __construct()
    {
        $this->middleware('superadmin.only')->except(['showLogin', 'processLogin']);
    }

    /**
     * Exibe o painel centralizado de super administrador
     */
    public function dashboard(): View
    {
        // Estatísticas gerais do sistema
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('ativo', true)->count(),
            'super_admins' => User::where('is_super_admin', true)->count(),
            'total_schools' => Escola::count(),
            'active_schools' => Escola::where('ativo', true)->count(),
            'total_licenses' => SchoolLicense::count(),
            'active_licenses' => SchoolLicense::where('ativo', true)->count(),
        ];

        return view('superadmin.dashboard', compact('stats'));
    }

    /**
     * Exibe a página de login específica para super administradores
     */
    public function showLogin(): View
    {
        return view('corporativo.login');
    }

    /**
     * Processa o login específico para super administradores
     */
    public function processLogin(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            
            // Verificar se o usuário é super administrador
            if ($user->isSuperAdmin()) {
                $request->session()->regenerate();
                
                // Log de acesso de super administrador
                Log::info('Super Admin Login', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'timestamp' => now()
                ]);
                
                return redirect()->route('admin.superadmin.dashboard')
                    ->with('success', 'Bem-vindo, Super Administrador!');
            } else {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Acesso negado. Esta área é exclusiva para super administradores.',
                ])->withInput($request->only('email'));
            }
        }

        return back()->withErrors([
            'email' => 'Credenciais inválidas.',
        ])->withInput($request->only('email'));
    }

    /**
     * Exibe o formulário para criar um novo super administrador
     */
    public function createAdmin(): View
    {
        return view('superadmin.create-admin');
    }

    /**
     * Armazena um novo super administrador
     */
    public function storeAdmin(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'is_super_admin' => true,
            'ativo' => true,
        ]);

        // Log da criação de super administrador
        Log::info('Super Admin Created', [
            'created_by' => Auth::id(),
            'new_admin_id' => $user->id,
            'new_admin_email' => $user->email,
            'timestamp' => now()
        ]);

        return redirect()->route('admin.superadmin.dashboard')
            ->with('success', 'Super administrador criado com sucesso!');
    }

    /**
     * Exibe a página de licenças
     */
    public function licencas(): View
    {
        $licencas = SchoolLicense::with('escola')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.superadmin.licencas', compact('licencas'));
    }

    /**
     * Exibe relatórios consolidados
     */
    public function relatorios(): View
    {
        // Dados para relatórios consolidados
        $data = [
            'escolas_ativas' => Escola::where('ativo', true)->count(),
            'escolas_inativas' => Escola::where('ativo', false)->count(),
            'usuarios_ativos' => User::where('ativo', true)->count(),
            'usuarios_inativos' => User::where('ativo', false)->count(),
            'licencas_ativas' => SchoolLicense::where('ativo', true)->count(),
            'licencas_vencidas' => SchoolLicense::where('data_vencimento', '<', now())->count(),
        ];

        return view('admin.superadmin.relatorios', compact('data'));
    }

    /**
     * Exibe configurações do sistema
     */
    public function systemSettings(): View
    {
        return view('admin.superadmin.system.settings');
    }

    /**
     * Atualiza configurações do sistema
     */
    public function updateSystemSettings(Request $request): RedirectResponse
    {
        // Implementar lógica de atualização de configurações
        return redirect()->back()->with('success', 'Configurações atualizadas com sucesso!');
    }

    /**
     * Exibe logs do sistema
     */
    public function systemLogs(): View
    {
        return view('admin.superadmin.system.logs');
    }

    /**
     * Cria backup do sistema
     */
    public function createBackup(): RedirectResponse
    {
        try {
            // Implementar lógica de backup
            Log::info('System Backup Created', [
                'created_by' => Auth::id(),
                'timestamp' => now()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Backup criado com sucesso!'
            ]);
        } catch (\Exception $e) {
            Log::error('Backup Creation Failed', [
                'error' => $e->getMessage(),
                'created_by' => Auth::id(),
                'timestamp' => now()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar backup: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Limpa cache do sistema
     */
    public function clearCache(): RedirectResponse
    {
        try {
            \Artisan::call('cache:clear');
            \Artisan::call('config:clear');
            \Artisan::call('view:clear');
            \Artisan::call('route:clear');
            
            Log::info('System Cache Cleared', [
                'cleared_by' => Auth::id(),
                'timestamp' => now()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Cache limpo com sucesso!'
            ]);
        } catch (\Exception $e) {
            Log::error('Cache Clear Failed', [
                'error' => $e->getMessage(),
                'cleared_by' => Auth::id(),
                'timestamp' => now()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao limpar cache: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lista todos os usuários do sistema (exclusivo para super admin)
     */
    public function users(): View
    {
        $users = User::with(['escola', 'roles'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('superadmin.users.index', compact('users'));
    }
}