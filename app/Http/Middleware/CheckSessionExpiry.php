<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class CheckSessionExpiry
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Se não há usuário autenticado, prosseguir normalmente
        if (!Auth::check()) {
            return $next($request);
        }

        // Verificar se a sessão ainda é válida
        $lastActivity = Session::get('last_activity');
        $sessionLifetime = config('session.lifetime') * 60; // converter minutos para segundos
        
        if ($lastActivity && (time() - $lastActivity) > $sessionLifetime) {
            // Sessão expirou, fazer logout e limpar
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            // Para requisições AJAX, retornar JSON
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Sua sessão expirou por inatividade. Por favor, faça login novamente.',
                    'error' => 'SessionExpired',
                    'redirect' => route('login')
                ], 401);
            }
            
            // Para requisições normais, redirecionar para login
            return redirect()->route('login')
                ->with('error', 'Sua sessão expirou por inatividade. Por favor, faça login novamente.');
        }
        
        // Verificar se é uma requisição de notificações ou escola-switch e pular completamente o middleware
        if ($request->is('notifications/*') || $request->is('escola-switch/*')) {
            return $next($request);
        }
        
        // Atualizar timestamp da última atividade
        Session::put('last_activity', time());
        
        return $next($request);
    }
}