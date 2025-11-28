<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class ForceAuthForNotifications
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Se já está autenticado, continua
        if (Auth::check()) {
            return $next($request);
        }

        // Para requisições de notificações, tenta autenticar automaticamente
        if ($request->is('notifications/*')) {
            // Tenta autenticar com o primeiro usuário disponível
            $adminUser = User::first();
            
            if ($adminUser) {
                Auth::login($adminUser);
                
                // Log da autenticação forçada
                \Log::info('Autenticação forçada para notificações', [
                    'user_id' => $adminUser->id,
                    'email' => $adminUser->email,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
                
                return $next($request);
            }
        }

        // Se não conseguiu autenticar, retorna erro JSON para AJAX
        if ($request->ajax()) {
            return response()->json([
                'error' => 'Não autenticado',
                'message' => 'Usuário não está autenticado',
                'notifications' => [],
                'count' => 0
            ], 401);
        }

        // Para requisições normais, redireciona para login
        return redirect()->route('login');
    }
}