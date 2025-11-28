<?php

namespace App\Http\Controllers;

use App\Models\Escola;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class EscolaSwitchController extends Controller
{
    /**
     * Lista as escolas disponíveis para o usuário
     */
    public function index()
    {
        $user = Auth::user();
        $escolas = collect();
        
        if ($user->isSuperAdmin() || $user->temCargo('Suporte')) {
            // Super admins e suporte podem acessar todas as escolas
            $escolas = Escola::where('ativo', true)
                ->orderBy('nome')
                ->get();
        } else {
            // Usuários comuns só podem ver suas escolas
            // Verificar se o usuário está associado a múltiplas escolas
            $escolasUsuario = Escola::whereHas('users', function($query) use ($user) {
                $query->where('users.id', $user->id);
            })->where('ativo', true)->orderBy('nome')->get();
            
            if ($escolasUsuario->count() > 1) {
                $escolas = $escolasUsuario;
            }
        }
        
        return response()->json([
            'escolas' => $escolas,
            'escola_atual' => $user->escola_id,
            'escola_atual_sessao' => Session::get('escola_atual'),
            'pode_trocar' => $escolas->count() > 1
        ]);
    }
    
    /**
     * Troca a escola do usuário
     */
    public function switch(Request $request)
    {
        $request->validate([
            'escola_id' => 'required|exists:escolas,id'
        ]);
        
        $user = Auth::user();
        $escolaId = $request->escola_id;
        
        // Verificar se o usuário pode acessar esta escola
        if (!$this->podeAcessarEscola($user, $escolaId)) {
            return response()->json([
                'success' => false,
                'message' => 'Você não tem permissão para acessar esta escola.'
            ], 403);
        }
        
        // Para super admins e suporte, apenas definir na sessão
        if ($user->isSuperAdmin() || $user->temCargo('Suporte')) {
            Session::put('escola_atual', $escolaId);
        } else {
            // Para usuários normais, atualizar a escola do usuário
            $user->update(['escola_id' => $escolaId]);
            Session::put('escola_atual', $escolaId);
        }
        
        $escola = Escola::find($escolaId);
        
        // Regenerar token CSRF para evitar problemas com requisições subsequentes
        $request->session()->regenerateToken();
        
        return response()->json([
            'success' => true,
            'message' => 'Escola alterada com sucesso!',
            'escola' => [
                'id' => $escola->id,
                'nome' => $escola->nome
            ],
            'csrf_token' => csrf_token(), // Enviar novo token para o frontend
            'redirect_url' => route('dashboard') // URL para redirecionamento
        ]);
    }
    
    /**
     * Verifica se o usuário pode acessar uma escola específica
     */
    private function podeAcessarEscola(User $user, int $escolaId): bool
    {
        // Super admins e suporte podem acessar qualquer escola
        if ($user->isSuperAdmin() || $user->temCargo('Suporte')) {
            return Escola::where('id', $escolaId)->where('ativo', true)->exists();
        }
        
        // Usuários comuns só podem acessar escolas onde estão cadastrados
        return Escola::where('id', $escolaId)
            ->where('ativo', true)
            ->whereHas('users', function($query) use ($user) {
                $query->where('users.id', $user->id);
            })->exists();
    }
    
    /**
     * Retorna informações da escola atual
     */
    public function current()
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Usuário não autenticado'], 401);
        }
        
        // Para super admins e suporte, verificar escola da sessão primeiro
        if ($user->isSuperAdmin() || $user->temCargo('Suporte')) {
            $escolaId = Session::get('escola_atual');
            if ($escolaId) {
                $escola = Escola::find($escolaId);
                if ($escola) {
                    return response()->json([
                        'success' => true,
                        'escola' => [
                            'id' => $escola->id,
                            'nome' => $escola->nome ?? 'Escola sem nome',
                            'razao_social' => $escola->razao_social ?? null
                        ]
                    ]);
                }
            }
            
            // Se não há escola na sessão, retornar nome do sistema
            return response()->json([
                'success' => true,
                'escola' => [
                    'id' => null,
                    'nome' => config('app.name'),
                    'razao_social' => null
                ]
            ]);
        }
        
        // Para usuários normais, usar escola associada
        $escola = $user->escola;
        
        if (!$escola) {
            return response()->json([
                'success' => false,
                'message' => 'Nenhuma escola associada ao usuário.'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'escola' => [
                'id' => $escola->id,
                'nome' => $escola->nome ?? 'Escola sem nome',
                'razao_social' => $escola->razao_social ?? null
            ]
        ]);
    }
}