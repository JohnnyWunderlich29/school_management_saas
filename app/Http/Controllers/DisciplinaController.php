<?php

namespace App\Http\Controllers;

use App\Models\Disciplina;
use App\Models\ModalidadeEnsino;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Services\AlertService;

class DisciplinaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Disciplina::ordenadas();

        // Filtros
        if ($request->filled('area_conhecimento')) {
            $query->porAreaConhecimento($request->area_conhecimento);
        }

        if ($request->filled('obrigatoria')) {
            if ($request->obrigatoria == '1') {
                $query->obrigatoria();
            } else {
                $query->where('obrigatoria', false);
            }
        }

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

        $disciplinas = $query->paginate(15);
        $modalidades = ModalidadeEnsino::ativas()->ordenados()->get();
        $areasConhecimento = Disciplina::getAreasConhecimento();

        return view('admin.disciplinas.index', compact('disciplinas', 'modalidades', 'areasConhecimento'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Disciplinas são padronizadas nacionalmente; criação não depende de escola
        
        // Filtrar modalidades pela escola
        $modalidadesQuery = ModalidadeEnsino::ativas();
        
        if (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte')) {
            if (session('escola_atual')) {
                $modalidadesQuery->where('escola_id', session('escola_atual'));
            }
        } else {
            if (auth()->user()->escola_id) {
                $modalidadesQuery->where('escola_id', auth()->user()->escola_id);
            }
        }
        
        $modalidades = $modalidadesQuery->ordenados()->get();
        $areasConhecimento = Disciplina::getAreasConhecimento();
        return view('admin.disciplinas.create', compact('modalidades', 'areasConhecimento'));
    }

    /**
     * Gera um código único para a disciplina baseado no nome
     */
    private function gerarCodigoUnico($nome)
    {
        // Converter o nome para minúsculas e remover acentos
        $codigo = \Illuminate\Support\Str::slug($nome, '');
        
        // Pegar as primeiras letras (até 6 caracteres para disciplinas)
        $codigo = strtoupper(substr($codigo, 0, 6));
        
        // Verificar se o código já existe globalmente
        $codigoBase = $codigo;
        $contador = 1;
        
        while (Disciplina::where('codigo', $codigo)->exists()) {
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
            'area_conhecimento' => 'required|string|max:100',
            'descricao' => 'nullable|string|max:500',
            'cor_hex' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'ordem' => 'nullable|integer|min:1'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->all();
        $data['obrigatoria'] = $request->has('obrigatoria');
        $data['ativo'] = $request->has('ativo');
        
        // Gerar código automaticamente baseado no nome (global)
        $data['codigo'] = $this->gerarCodigoUnico($data['nome']);

        $disciplina = Disciplina::create($data);

        // Registrar histórico de criação
        try {
            Historico::registrar(
                'criado',
                'Disciplina',
                $disciplina->id,
                null,
                $disciplina->toArray(),
                'Disciplina criada'
            );
        } catch (\Exception $e) {
            // Não interromper fluxo caso histórico falhe
        }

        return redirect()->route('admin.configuracoes.index', ['tab' => 'disciplinas'])
            ->with('success', 'Disciplina criada com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Disciplina $disciplina)
    {
        $disciplina->load('salas');
        return view('admin.disciplinas.show', compact('disciplina'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Disciplina $disciplina)
    {
        // Filtrar modalidades pela escola
        $modalidadesQuery = ModalidadeEnsino::ativas();
        
        if (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte')) {
            if (session('escola_atual')) {
                $modalidadesQuery->where('escola_id', session('escola_atual'));
            }
        } else {
            if (auth()->user()->escola_id) {
                $modalidadesQuery->where('escola_id', auth()->user()->escola_id);
            }
        }
        
        $modalidades = $modalidadesQuery->ordenados()->get();
        $areasConhecimento = Disciplina::getAreasConhecimento();
        return view('admin.disciplinas.edit', compact('disciplina', 'modalidades', 'areasConhecimento'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Disciplina $disciplina)
    {
        $validator = Validator::make($request->all(), [
            'nome' => 'required|string|max:255',
            'codigo' => 'required|string|max:20|unique:disciplinas,codigo,' . $disciplina->id,
            'area_conhecimento' => 'required|string|max:100',
            'descricao' => 'nullable|string|max:500',
            'cor_hex' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'ordem' => 'nullable|integer|min:1'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->all();
        $data['obrigatoria'] = $request->has('obrigatoria');
        $data['ativo'] = $request->has('ativo');

        // Capturar dados antigos para histórico
        $dadosAntigos = $disciplina->toArray();

        $disciplina->update($data);

        // Registrar histórico de atualização
        try {
            Historico::registrar(
                'atualizado',
                'Disciplina',
                $disciplina->id,
                $dadosAntigos,
                $disciplina->fresh()->toArray(),
                'Disciplina atualizada'
            );
        } catch (\Exception $e) {
            // Não interromper fluxo caso histórico falhe
        }

        return redirect()->route('admin.configuracoes.index', ['tab' => 'disciplinas'])
            ->with('success', 'Disciplina atualizada com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Disciplina $disciplina)
    {
        try {
            // Verificar se a disciplina está sendo usada em alguma sala
            if ($disciplina->salas()->exists()) {
                $salasCount = $disciplina->salas()->count();
                AlertService::error(
                    "Não é possível excluir a disciplina '{$disciplina->nome}' pois ela está sendo utilizada em {$salasCount} sala(s). Remova a disciplina das salas antes de excluí-la.",
                    [
                        'timeout' => 8000,
                        'actions' => [
                            [
                                'label' => 'Ver Salas',
                                'action' => 'redirect',
                                'url' => route('salas.index'),
                                'class' => 'bg-blue-600 hover:bg-blue-700 text-white'
                            ]
                        ]
                    ]
                );
                return redirect()->back();
            }

            $nomeDisciplina = $disciplina->nome;
            // Capturar dados antigos
            $dadosAntigos = $disciplina->toArray();
            $disciplina->delete();

            // Registrar histórico de exclusão
            try {
                Historico::registrar(
                    'excluido',
                    'Disciplina',
                    $disciplina->id,
                    $dadosAntigos,
                    null,
                    'Disciplina excluída'
                );
            } catch (\Exception $e) {
                // Não interromper fluxo caso histórico falhe
            }

            AlertService::success("Disciplina '{$nomeDisciplina}' excluída com sucesso!");
            return redirect()->route('admin.disciplinas.index');
            
        } catch (\Exception $e) {
            AlertService::systemError(
                'Erro interno ao excluir a disciplina. Tente novamente ou entre em contato com o suporte.',
                $e
            );
            return redirect()->back();
        }
    }
}
