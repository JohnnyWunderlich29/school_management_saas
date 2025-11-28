<?php

namespace App\Http\Controllers;

use App\Models\Grupo;
use App\Models\Historico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GrupoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Grupo::with('modalidadeEnsino')->ordenados();
        
        // Para super admins e suporte, filtrar pela escola da sessão se definida
        if (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte')) {
            if (session('escola_atual')) {
                $query->where('escola_id', session('escola_atual'));
            }
        } else {
            // Para usuários normais, filtrar por sua escola
            if (auth()->user()->escola_id) {
                $query->where('escola_id', auth()->user()->escola_id);
            }
        }

        // Filtro de modalidade removido conforme solicitado

        if ($request->filled('ativo')) {
            if ($request->ativo == '1') {
                $query->ativas();
            } else {
                $query->where('ativo', false);
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nome', 'like', "%{$search}%")
                  ->orWhere('codigo', 'like', "%{$search}%")
                  ->orWhere('descricao', 'like', "%{$search}%");
            });
        }

        $grupos = $query->paginate(15);
        return view('admin.grupos.index', compact('grupos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Verificar se usuário tem escola associada
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->temCargo('Suporte') && !auth()->user()->escola_id) {
            return redirect()->route('admin.grupos.index')
                ->with('error', 'Você precisa estar associado a uma escola para criar grupos.');
        }
        
        // Filtrar modalidades pela escola
        $modalidadesQuery = \App\Models\ModalidadeEnsino::where('ativo', true);

        if (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte')) {
            if (session('escola_atual')) {
                $modalidadesQuery->where('escola_id', session('escola_atual'));
            }
        } else {
            if (auth()->user()->escola_id) {
                $modalidadesQuery->where('escola_id', auth()->user()->escola_id);
            }
        }
        
        $modalidades = $modalidadesQuery->orderBy('nome')->get();
        
        return view('admin.grupos.create', compact('modalidades'));
    }

    /**
     * Gera um código único para o grupo baseado no nome
     */
    private function gerarCodigoUnico($nome, $escolaId)
    {
        // Converter o nome para minúsculas e remover acentos
        $codigo = \Illuminate\Support\Str::slug($nome, '');
        
        // Pegar as primeiras letras (até 4 caracteres)
        $codigo = strtoupper(substr($codigo, 0, 4));
        
        // Verificar se o código já existe para esta escola
        $codigoBase = $codigo;
        $contador = 1;
        
        while (Grupo::where('codigo', $codigo)
                ->where('escola_id', $escolaId)
                ->exists()) {
            $codigo = $codigoBase . $contador;
            $contador++;
        }
        
        return $codigo;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nome' => 'required|string|max:255',
            'modalidade_ensino_id' => 'required|exists:modalidades_ensino,id',
            // Campo código removido, será gerado automaticamente
            'idade_minima' => 'nullable|integer|min:0|max:25',
            'idade_maxima' => 'nullable|integer|min:0|max:25|gte:idade_minima',
            // Campo ano_serie removido conforme solicitado
            'descricao' => 'nullable|string|max:500',
            'ordem' => 'nullable|integer|min:1'
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->all();
        $data['ativo'] = $request->has('ativo');
        
        // Definir escola_id baseado no usuário
        if (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte')) {
            $data['escola_id'] = session('escola_atual') ?: auth()->user()->escola_id;
        } else {
            $data['escola_id'] = auth()->user()->escola_id;
        }
        
        // Verificar se escola_id foi definido
        if (!$data['escola_id']) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Não foi possível determinar a escola. Selecione uma escola primeiro.'
                ], 422);
            }
            
            return redirect()->back()
                ->with('error', 'Não foi possível determinar a escola. Selecione uma escola primeiro.')
                ->withInput();
        }
        
        // Gerar código automaticamente baseado no nome
        $data['codigo'] = $this->gerarCodigoUnico($request->nome, $data['escola_id']);

        $grupo = Grupo::create($data);
        Historico::registrar('criado', 'Grupo', $grupo->id, null, $grupo->toArray());

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Grupo criado com sucesso!'
            ]);
        }

        return redirect()->route('admin.configuracoes.index', ['tab' => 'grupos'])
            ->with('success', 'Grupo criado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Grupo $grupo)
    {
        $grupo->load('salas');
        return view('admin.grupos.show', compact('grupo'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Grupo $grupo)
    {
        // Verificar se o usuário pode editar este grupo
        if (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte')) {
            if (session('escola_atual') && $grupo->escola_id !== session('escola_atual')) {
                abort(404);
            }
        } else {
            if (auth()->user()->escola_id && $grupo->escola_id !== auth()->user()->escola_id) {
                abort(404);
            }
        }
        
        // Filtrar modalidades pela escola
        $modalidadesQuery = \App\Models\ModalidadeEnsino::where('ativo', true);
        
        if (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte')) {
            if (session('escola_atual')) {
                $modalidadesQuery->where('escola_id', session('escola_atual'));
            }
        } else {
            if (auth()->user()->escola_id) {
                $modalidadesQuery->where('escola_id', auth()->user()->escola_id);
            }
        }
        
        $modalidades = $modalidadesQuery->orderBy('nome')->get();
        
        return view('admin.grupos.edit', compact('grupo', 'modalidades'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Grupo $grupo)
    {
        // Verificar se o usuário pode atualizar este grupo
        if (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte')) {
            if (session('escola_atual') && $grupo->escola_id !== session('escola_atual')) {
                abort(404);
            }
        } else {
            if (auth()->user()->escola_id && $grupo->escola_id !== auth()->user()->escola_id) {
                abort(404);
            }
        }
        
        $validator = Validator::make($request->all(), [
            'nome' => 'required|string|max:255',
            'codigo' => 'required|string|max:20|unique:grupos,codigo,' . $grupo->id,
            'modalidade_ensino_id' => 'required|exists:modalidades_ensino,id',
            'idade_minima' => 'nullable|integer|min:0|max:25',
            'idade_maxima' => 'nullable|integer|min:0|max:25|gte:idade_minima',
            // Campo ano_serie removido conforme solicitado
            'descricao' => 'nullable|string|max:500',
            'ordem' => 'nullable|integer|min:1'
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->all();
        $data['ativo'] = $request->has('ativo');

        $dadosAntigos = $grupo->toArray();
        $grupo->update($data);
        $dadosNovos = $grupo->fresh()->toArray();
        Historico::registrar('atualizado', 'Grupo', $grupo->id, $dadosAntigos, $dadosNovos);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Grupo atualizado com sucesso!'
            ]);
        }

        return redirect()->route('admin.configuracoes.index', ['tab' => 'grupos'])
            ->with('success', 'Grupo atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Grupo $grupo)
    {
        // Verificar se o usuário pode excluir este grupo
        if (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte')) {
            if (session('escola_atual') && $grupo->escola_id !== session('escola_atual')) {
                abort(404);
            }
        } else {
            if (auth()->user()->escola_id && $grupo->escola_id !== auth()->user()->escola_id) {
                abort(404);
            }
        }
        
        // Verificar se o grupo tem salas associadas
        if ($grupo->salas()->count() > 0) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Não é possível excluir este grupo pois existem salas associadas a ele.'
                ], 422);
            }
            
            return redirect()->back()
                ->with('error', 'Não é possível excluir este grupo pois existem salas associadas a ele.');
        }

        $dadosAntigos = $grupo->toArray();
        $grupo->delete();
        Historico::registrar('excluido', 'Grupo', $grupo->id, $dadosAntigos, null);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Grupo excluído com sucesso!'
            ]);
        }

        return redirect()->route('admin.grupos.index')
            ->with('success', 'Grupo excluído com sucesso!');
    }

    /**
     * Retorna as modalidades de ensino para o modal de criação
     */
    public function getModalidadesEnsino()
    {
        try {
            $modalidadesQuery = \App\Models\ModalidadeEnsino::where('ativo', true);
            
            $user = auth()->user();
            $escolaId = null;
            
            // Verificar se o usuário está autenticado
            if ($user) {
                if ($user->isSuperAdmin() || $user->temCargo('Suporte')) {
                    $escolaId = session('escola_atual');
                    if ($escolaId) {
                        // Incluir modalidades globais (escola_id NULL) e específicas da escola
                        $modalidadesQuery->where(function($query) use ($escolaId) {
                            $query->whereNull('escola_id')
                                  ->orWhere('escola_id', $escolaId);
                        });
                    }
                } else {
                    $escolaId = $user->escola_id;
                    if ($escolaId) {
                        // Incluir modalidades globais (escola_id NULL) e específicas da escola
                        $modalidadesQuery->where(function($query) use ($escolaId) {
                            $query->whereNull('escola_id')
                                  ->orWhere('escola_id', $escolaId);
                        });
                    }
                }
            } else {
                // Se não estiver autenticado, retornar apenas modalidades globais
                $modalidadesQuery->whereNull('escola_id');
            }
            
            $modalidades = $modalidadesQuery->orderBy('nome')->get(['id', 'nome']);
            
            return response()->json($modalidades);
        } catch (\Exception $e) {
            \Log::error('Erro ao buscar modalidades:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json(['error' => 'Erro interno do servidor'], 500);
        }
    }

    /**
     * Retorna o formulário de edição para o modal
     */
    public function editModal(Grupo $grupo)
    {
        // Verificar se o usuário pode editar este grupo
        if (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte')) {
            if (session('escola_atual') && $grupo->escola_id !== session('escola_atual')) {
                abort(404);
            }
        } else {
            if (auth()->user()->escola_id && $grupo->escola_id !== auth()->user()->escola_id) {
                abort(404);
            }
        }
        
        // Filtrar modalidades pela escola
        $modalidadesQuery = \App\Models\ModalidadeEnsino::where('ativo', true);
        
        if (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte')) {
            if (session('escola_atual')) {
                $modalidadesQuery->where('escola_id', session('escola_atual'));
            }
        } else {
            if (auth()->user()->escola_id) {
                $modalidadesQuery->where('escola_id', auth()->user()->escola_id);
            }
        }
        
        $modalidades = $modalidadesQuery->orderBy('nome')->get();
        
        return view('admin.grupos.partials.edit-form', compact('grupo', 'modalidades'))->render();
    }

    /**
     * Retorna os detalhes do grupo para o modal de visualização
     */
    public function showModal(Grupo $grupo)
    {
        // Verificar se o usuário pode visualizar este grupo
        if (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte')) {
            if (session('escola_atual') && $grupo->escola_id !== session('escola_atual')) {
                abort(404);
            }
        } else {
            if (auth()->user()->escola_id && $grupo->escola_id !== auth()->user()->escola_id) {
                abort(404);
            }
        }
        
        $grupo->load('modalidadeEnsino', 'salas');
        
        return view('admin.grupos.partials.show-details', compact('grupo'))->render();
    }

    /**
     * Listar grupos para uso em modais/AJAX
     */
    public function listar(Request $request)
    {
        $query = Grupo::where('ativo', true)->ordenados();
        
        // Para super admins e suporte, filtrar pela escola da sessão se definida
        if (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte')) {
            if (session('escola_atual')) {
                $query->where('escola_id', session('escola_atual'));
            }
        } else {
            // Para usuários normais, filtrar por sua escola
            if (auth()->user()->escola_id) {
                $query->where('escola_id', auth()->user()->escola_id);
            }
        }

        $grupos = $query->with('modalidadeEnsino:id,nome')->get(['id', 'nome', 'codigo', 'modalidade_ensino_id']);

        return response()->json($grupos);
    }

    /**
     * Listar grupos filtrados por níveis configurados
     * Este método retorna apenas grupos que possuem níveis de ensino
     * configurados para a escola
     */
    public function listarPorModalidade(Request $request)
    {
        try {
            // Determinar escola_id baseado no usuário
            $user = auth()->user();
            if ($user->isSuperAdmin() || $user->temCargo('Suporte')) {
                $escolaId = session('escola_atual') ?: $user->escola_id;
            } else {
                $escolaId = $user->escola_id;
            }

            if (!$escolaId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Não foi possível determinar a escola.'
                ], 422);
            }

            // Buscar níveis de ensino configurados para a escola
            $niveisEnsino = \DB::table('escola_niveis_config')
                ->join('niveis_ensino', 'escola_niveis_config.nivel_ensino_id', '=', 'niveis_ensino.id')
                ->where('escola_niveis_config.escola_id', $escolaId)
                ->where('escola_niveis_config.ativo', true)
                ->select(
                    'niveis_ensino.id',
                    'niveis_ensino.nome',
                    'niveis_ensino.codigo'
                )
                ->orderBy('niveis_ensino.nome')
                ->get();

            return response()->json($niveisEnsino);

        } catch (\Exception $e) {
            \Log::error('Erro ao buscar níveis de ensino configurados:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }
}
