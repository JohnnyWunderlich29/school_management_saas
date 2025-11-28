<?php

namespace App\Http\Controllers;

use App\Models\ModalidadeEnsino;
use App\Models\Historico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ModalidadeEnsinoController extends Controller
{
    /**
     * Gera um código único para a modalidade baseado no nome
     */
    private function gerarCodigoUnico($nome, $escolaId)
    {
        // Converter o nome para minúsculas e remover acentos
        $codigo = \Illuminate\Support\Str::slug($nome, '_');
        
        // Verificar se o código já existe para esta escola
        $codigoBase = $codigo;
        $contador = 1;
        
        while (ModalidadeEnsino::where('codigo', $codigo)
                ->where('escola_id', $escolaId)
                ->exists()) {
            $codigo = $codigoBase . '_' . $contador;
            $contador++;
        }
        
        return $codigo;
    }
    
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ModalidadeEnsino::query();
        
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

        // Filtro por nome
        if ($request->filled('nome')) {
            $query->where('nome', 'like', '%' . $request->nome . '%');
        }

        // Filtro por código
        if ($request->filled('codigo')) {
            $query->where('codigo', 'like', '%' . $request->codigo . '%');
        }

        // Filtro por status
        if ($request->filled('ativo')) {
            $query->where('ativo', $request->ativo === 'true');
        }

        $modalidades = $query->orderBy('nome')->paginate(10);
        
        // Preservar parâmetros de busca na paginação
        $modalidades->appends($request->query());

        return view('admin.modalidades.index', compact('modalidades'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Verificar se usuário tem escola associada
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->temCargo('Suporte') && !auth()->user()->escola_id) {
            return redirect()->route('admin.modalidades.index')
                ->with('error', 'Você precisa estar associado a uma escola para criar modalidades.');
        }
        
        return view('admin.modalidades.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Definir escola_id baseado no usuário
        $escolaId = null;
        if (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte')) {
            $escolaId = session('escola_atual') ?: auth()->user()->escola_id;
        } else {
            $escolaId = auth()->user()->escola_id;
        }
        
        $validator = Validator::make($request->all(), [
            'nome' => 'required|string|max:100',
            'descricao' => 'nullable|string|max:500',
            'ativo' => 'boolean'
        ], [
            'nome.required' => 'O nome é obrigatório.',
            'nome.max' => 'O nome deve ter no máximo 100 caracteres.',
            'descricao.max' => 'A descrição deve ter no máximo 500 caracteres.'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Gerar código automaticamente baseado no nome
            $codigo = $this->gerarCodigoUnico($request->nome, $escolaId);
            
            $data = [
                'codigo' => $codigo,
                'nome' => $request->nome,
                'nivel' => $request->nivel,
                'descricao' => $request->descricao,
                'ativo' => $request->has('ativo')
            ];
            
            // Definir escola_id baseado no usuário
            if (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte')) {
                $data['escola_id'] = session('escola_atual') ?: auth()->user()->escola_id;
            } else {
                $data['escola_id'] = auth()->user()->escola_id;
            }
            
            // Verificar se escola_id foi definido
            if (!$data['escola_id']) {
                return redirect()->back()
                    ->with('error', 'Não foi possível determinar a escola. Selecione uma escola primeiro.')
                    ->withInput();
            }

            $modalidade = ModalidadeEnsino::create($data);
            Historico::registrar('criado', 'ModalidadeEnsino', $modalidade->id, null, $modalidade->toArray());

            return redirect()->route('admin.configuracoes.index', ['tab' => 'modalidades'])
                ->with('success', 'Modalidade de ensino criada com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erro ao criar modalidade de ensino: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ModalidadeEnsino $modalidade)
    {
        // Verificar se o usuário pode acessar esta modalidade
        if (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte')) {
            if (session('escola_atual') && $modalidade->escola_id !== session('escola_atual')) {
                abort(404);
            }
        } else {
            if (auth()->user()->escola_id && $modalidade->escola_id !== auth()->user()->escola_id) {
                abort(404);
            }
        }
        
        // Buscar salas que usam esta modalidade de ensino
        $salas = \App\Models\Sala::where('modalidade_ensino', $modalidade->codigo)
                                 ->where('ativo', true)
                                 ->orderBy('codigo')
                                 ->get();

        return view('admin.modalidades.show', compact('modalidade', 'salas'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ModalidadeEnsino $modalidade)
    {
        // Verificar se o usuário pode editar esta modalidade
        if (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte')) {
            if (session('escola_atual') && $modalidade->escola_id !== session('escola_atual')) {
                abort(404);
            }
        } else {
            if (auth()->user()->escola_id && $modalidade->escola_id !== auth()->user()->escola_id) {
                abort(404);
            }
        }
        
        return view('admin.modalidades.edit', compact('modalidade'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ModalidadeEnsino $modalidade)
    {
        // Verificar se o usuário pode atualizar esta modalidade
        if (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte')) {
            if (session('escola_atual') && $modalidade->escola_id !== session('escola_atual')) {
                abort(404);
            }
        } else {
            if (auth()->user()->escola_id && $modalidade->escola_id !== auth()->user()->escola_id) {
                abort(404);
            }
        }
        
        $validator = Validator::make($request->all(), [
            'nome' => 'required|string|max:100',
            'descricao' => 'nullable|string|max:500',
            'ativo' => 'boolean'
        ], [
            'nome.required' => 'O nome é obrigatório.',
            'nome.max' => 'O nome deve ter no máximo 100 caracteres.',
            'descricao.max' => 'A descrição deve ter no máximo 500 caracteres.'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $dadosAntigos = $modalidade->toArray();
            $modalidade->update([
                'nome' => $request->nome,
                'nivel' => $request->nivel,
                'descricao' => $request->descricao,
                'ativo' => $request->has('ativo')
            ]);
            $dadosNovos = $modalidade->fresh()->toArray();
            Historico::registrar('atualizado', 'ModalidadeEnsino', $modalidade->id, $dadosAntigos, $dadosNovos);

            return redirect()->route('admin.configuracoes.index', ['tab' => 'modalidades'])
                ->with('success', 'Modalidade de ensino atualizada com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erro ao atualizar modalidade de ensino: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ModalidadeEnsino $modalidade)
    {
        // Verificar se o usuário pode excluir esta modalidade
        if (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte')) {
            if (session('escola_atual') && $modalidade->escola_id !== session('escola_atual')) {
                abort(404);
            }
        } else {
            if (auth()->user()->escola_id && $modalidade->escola_id !== auth()->user()->escola_id) {
                abort(404);
            }
        }
        
        try {
            // Verificar se existem salas vinculadas através de turmas e grupos
            $salasCount = \App\Models\Sala::whereHas('turmas.grupo', function($query) use ($modalidade) {
                $query->where('modalidade_ensino_id', $modalidade->id);
            })->count();
            
            if ($salasCount > 0) {
                return redirect()->back()
                    ->with('error', "Não é possível excluir esta modalidade pois existem {$salasCount} sala(s) vinculada(s) a ela.");
            }

            $dadosAntigos = $modalidade->toArray();
            $modalidade->delete();
            Historico::registrar('excluido', 'ModalidadeEnsino', $modalidade->id, $dadosAntigos, null);

            return redirect()->route('admin.modalidades.index')
                ->with('success', 'Modalidade de ensino excluída com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erro ao excluir modalidade de ensino: ' . $e->getMessage());
        }
    }

    /**
     * Toggle the active status of the specified resource.
     */
    public function toggleStatus(ModalidadeEnsino $modalidade)
    {
        // Verificar se o usuário pode alterar o status desta modalidade
        if (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte')) {
            if (session('escola_atual') && $modalidade->escola_id !== session('escola_atual')) {
                abort(404);
            }
        } else {
            if (auth()->user()->escola_id && $modalidade->escola_id !== auth()->user()->escola_id) {
                abort(404);
            }
        }
        
        $dadosAntigos = $modalidade->toArray();
        $modalidade->update(['ativo' => !$modalidade->ativo]);
        $dadosNovos = $modalidade->fresh()->toArray();
        
        $status = $modalidade->ativo ? 'ativada' : 'desativada';
        Historico::registrar($modalidade->ativo ? 'ativado' : 'inativado', 'ModalidadeEnsino', $modalidade->id, $dadosAntigos, $dadosNovos);
        
        return redirect()->route('admin.modalidades.index')
             ->with('success', "Modalidade de ensino {$status} com sucesso!");
     }
}
