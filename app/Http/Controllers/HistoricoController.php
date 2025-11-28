<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Historico;
use App\Models\User;
use App\Http\Middleware\EscolaContext;

class HistoricoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $escolaId = EscolaContext::getEscolaAtual();
        $query = Historico::with('usuario')
            ->where(function ($q) use ($escolaId) {
                $q->where('escola_id', $escolaId)
                  ->orWhereHas('usuario', function ($uq) use ($escolaId) {
                      $uq->where('escola_id', $escolaId);
                  });
            })
            ->orderBy('created_at', 'desc');
        
        // Filtros
        if ($request->has('modelo') && $request->modelo) {
            $query->doModelo($request->modelo);
        }
        
        if ($request->has('acao') && $request->acao) {
            $query->daAcao($request->acao);
        }
        
        if ($request->has('usuario_id') && $request->usuario_id) {
            $query->doUsuario($request->usuario_id);
        }
        
        if ($request->has('data_inicio') && $request->has('data_fim')) {
            $query->whereBetween('created_at', [$request->data_inicio . ' 00:00:00', $request->data_fim . ' 23:59:59']);
        } elseif ($request->has('data')) {
            $query->whereDate('created_at', $request->data);
        }
        
        $historicos = $query->paginate(20);

        // Buscar usuários para o filtro
        $usuarios = User::where('escola_id', $escolaId)->orderBy('name')->get();
        
        // Modelos disponíveis
        $modelos = ['Escala', 'Funcionario', 'Sala'];
        
        // Ações disponíveis (padronizadas)
        $acoes = ['criado', 'atualizado', 'excluido', 'ativado', 'inativado'];
        
        return view('historico.index', compact('historicos', 'usuarios', 'modelos', 'acoes'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $escolaId = EscolaContext::getEscolaAtual();
        $historico = Historico::with('usuario')
            ->where(function ($q) use ($escolaId) {
                $q->where('escola_id', $escolaId)
                  ->orWhereHas('usuario', function ($uq) use ($escolaId) {
                      $uq->where('escola_id', $escolaId);
                  });
            })
            ->findOrFail($id);
        return view('historico.show', compact('historico'));
    }

    /**
     * Método para buscar histórico de um modelo específico
     */
    public function porModelo(Request $request, string $modelo, int $modeloId)
    {
        $escolaId = EscolaContext::getEscolaAtual();
        $historicos = Historico::with('usuario')
            ->where(function ($q) use ($escolaId) {
                $q->where('escola_id', $escolaId)
                  ->orWhereHas('usuario', function ($uq) use ($escolaId) {
                      $uq->where('escola_id', $escolaId);
                  });
            })
            ->where('modelo', $modelo)
            ->where('modelo_id', $modeloId)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('historicos.por-modelo', compact('historicos', 'modelo', 'modeloId'));
    }
}
