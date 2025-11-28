<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Escola;

class EscolaScope
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        
        if (!$user) {
            return $next($request);
        }

        // Para superadmin e suporte, garantir que escola_atual esteja definida
        if ($user->isSuperAdmin() || $user->temCargo('Suporte')) {
            $escolaAtual = session('escola_atual');
            
            // Se não há escola na sessão, definir a primeira escola disponível
            if (!$escolaAtual) {
                $primeiraEscola = Escola::where('ativo', true)->first();
                if ($primeiraEscola) {
                    session(['escola_atual' => $primeiraEscola->id]);
                    $request->session()->put('escola_atual', $primeiraEscola->id);
                }
            }
            
            // Verificar se a escola atual ainda existe e está ativa
            if ($escolaAtual) {
                $escola = Escola::where('id', $escolaAtual)->where('ativo', true)->first();
                if (!$escola) {
                    // Se a escola não existe mais, definir uma nova
                    $primeiraEscola = Escola::where('ativo', true)->first();
                    if ($primeiraEscola) {
                        session(['escola_atual' => $primeiraEscola->id]);
                        $request->session()->put('escola_atual', $primeiraEscola->id);
                    } else {
                        // Se não há escolas ativas, remover da sessão
                        session()->forget('escola_atual');
                    }
                }
            }
        }

        return $next($request);
    }
}
