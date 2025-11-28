<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckSalaAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Se não estiver autenticado, redirecionar para login
        if (!$user) {
            return redirect()->route('login');
        }
        
        // Admin e Coordenador têm acesso total
        if ($user->isAdminOrCoordinator()) {
            return $next($request);
        }
        
        // Verificar se o usuário tem pelo menos uma sala atribuída
        if ($user->salas()->count() === 0) {
            return response()->view('errors.403', [
                'message' => 'Você não tem salas atribuídas. Entre em contato com o administrador.'
            ], 403);
        }
        
        // Se há um parâmetro sala_id na requisição, verificar acesso específico
        if ($request->has('sala_id')) {
            $salaId = $request->get('sala_id');
            if (!$user->temAcessoSala($salaId)) {
                return response()->view('errors.403', [
                    'message' => 'Você não tem acesso a esta sala.'
                ], 403);
            }
        }
        
        return $next($request);
    }
}
