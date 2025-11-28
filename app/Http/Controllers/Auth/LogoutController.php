<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogoutController extends Controller
{
    /**
     * Log the user out of the application.
     */
    public function logout(Request $request)
    {
        // Fazer logout do usuário
        Auth::logout();

        // Invalidar a sessão atual
        $request->session()->invalidate();
        
        // Regenerar o token CSRF
        $request->session()->regenerateToken();
        
        // Limpar todos os cookies de sessão
        $request->session()->flush();

        // Redirecionar para login com mensagem de sucesso
        return redirect()->route('login')
            ->with('success', 'Logout realizado com sucesso!');
    }
}