<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use App\Models\Escola;

class ProfileController extends Controller
{
    /**
     * Exibe o perfil do usuário
     */
    public function show()
    {
        $user = Auth::user();
        return view('profile.show', compact('user'));
    }

    /**
     * Exibe o formulário de edição do perfil
     */
    public function edit()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    /**
     * Atualiza o perfil do usuário
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'current_password' => ['nullable', 'required_with:password'],
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ]);

        // Verifica a senha atual se uma nova senha foi fornecida
        if ($request->filled('password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'A senha atual está incorreta.']);
            }
        }

        // Atualiza os dados do usuário
        $user->name = $request->name;
        $user->email = $request->email;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('profile.show')->with('success', 'Perfil atualizado com sucesso!');
    }

    /**
     * Exibe a página de configurações
     */
    public function settings()
    {
        $user = Auth::user();
        return view('profile.settings', compact('user'));
    }

    /**
     * Atualiza as configurações do usuário
     */
    public function updateSettings(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'email_notifications' => 'boolean',
            'system_notifications' => 'boolean',
            'weekly_reports' => 'boolean',
            'theme' => 'in:light,dark,auto',
            'language' => 'in:pt-BR,en-US,es-ES',
            'two_factor_enabled' => 'boolean'
        ]);

        // Atualizar preferências do usuário
        $preferences = [
            'email_notifications' => $request->boolean('email_notifications'),
            'system_notifications' => $request->boolean('system_notifications'),
            'weekly_reports' => $request->boolean('weekly_reports'),
            'theme' => $request->input('theme', 'light'),
            'language' => $request->input('language', 'pt-BR'),
            'two_factor_enabled' => $request->boolean('two_factor_enabled')
        ];

        // Salvar as preferências
        $user->preferences = $preferences;
        $user->save();

        return redirect()->route('profile.settings')->with('success', 'Configurações atualizadas com sucesso!');
    }

    /**
     * Exibe os dados da escola do usuário
     */
    public function escola()
    {
        $user = Auth::user();

        // Determinar a escola considerando SuperAdmin/Suporte com escola_atual
        if ($user->isSuperAdmin() || $user->temCargo('Suporte')) {
            $escolaId = session('escola_atual') ?: $user->escola_id;
        } else {
            $escolaId = $user->escola_id;
        }

        if (!$escolaId) {
            return redirect()->route('dashboard')
                ->with('error', 'Nenhuma escola associada ao seu perfil. Selecione uma escola.');
        }

        // Buscar a escola diretamente para garantir todas as colunas carregadas
        $escola = Escola::find($escolaId);

        if (!$escola) {
            return redirect()->route('dashboard')
                ->with('error', 'Escola não encontrada ou inacessível.');
        }

        // Carregar estatísticas básicas da escola
        $escola->loadCount(['users', 'funcionarios']);

        // Estatísticas adicionais
        $stats = [
            'total_alunos' => \App\Models\Aluno::where('escola_id', $escola->id)->count(),
            'alunos_ativos' => \App\Models\Aluno::where('escola_id', $escola->id)->where('ativo', true)->count(),
            'salas_ativas' => \App\Models\Sala::where('escola_id', $escola->id)->where('ativo', true)->count(),
            'funcionarios_ativos' => \App\Models\Funcionario::where('escola_id', $escola->id)->where('ativo', true)->count(),
        ];

        return view('profile.escola', compact('escola', 'stats'));
    }
}