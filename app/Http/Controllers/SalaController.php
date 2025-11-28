<?php

namespace App\Http\Controllers;

use App\Models\Sala;
use App\Models\Escola;
use App\Models\Aluno;
use App\Models\Historico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;

class SalaController extends Controller
{
    /**
     * Gera um código único para a sala baseado no nome
     * 
     * @param string $nome Nome da sala
     * @return string Código único
     */
    private function gerarCodigoUnico($nome)
    {
        // Remover acentos e caracteres especiais
        $nome = preg_replace('/[^a-zA-Z0-9]/', '', iconv('UTF-8', 'ASCII//TRANSLIT', $nome));
        
        // Converter para maiúsculas e pegar as primeiras letras (até 3)
        $prefixo = strtoupper(substr($nome, 0, 3));
        
        // Adicionar timestamp para garantir unicidade
        $codigo = $prefixo . date('YmdHis');
        
        // Verificar se já existe e adicionar um número aleatório se necessário
        while (Sala::where('codigo', $codigo)->exists()) {
            $codigo = $prefixo . date('YmdHis') . rand(10, 99);
        }
        
        return $codigo;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Determinar escola_id para filtros (seguindo padrão dos outros controllers)
        $user = auth()->user();
        $isSuperAdminOrSupport = $user->isSuperAdmin() || $user->temCargo('Suporte');
        $escolaId = $isSuperAdminOrSupport ? (session('escola_atual') ?: $user->escola_id) : $user->escola_id;

        // Se não há escola definida, não mostrar nenhuma sala
        if (!$escolaId) {
            $salas = new LengthAwarePaginator([], 0, 15, 1, [
                'path' => $request->url(),
                'pageName' => 'page',
            ]);
            $escolas = collect();
        } else {
            $query = Sala::with(['escola'])
                ->where('escola_id', $escolaId);

            // Filtros
            if ($request->filled('nome')) {
                $query->where('nome', 'like', '%' . $request->nome . '%');
            }
            if ($request->filled('codigo')) {
                $query->where('codigo', 'like', '%' . $request->codigo . '%');
            }
            if ($request->filled('ativo')) {
                $query->where('ativo', $request->ativo == '1');
            }

            // Permitir seleção de escola para superadmin/suporte
            if ($isSuperAdminOrSupport && $request->filled('escola_id')) {
                $query->where('escola_id', $request->escola_id);
            }

            // Ordenação dinâmica
            $sort = $request->get('sort');
            $direction = strtolower($request->get('direction', 'asc')) === 'desc' ? 'desc' : 'asc';
            $allowedSorts = ['id', 'nome', 'codigo', 'capacidade', 'tipo', 'ativo', 'created_at', 'escola'];

            if ($sort && in_array($sort, $allowedSorts)) {
                if ($sort === 'escola') {
                    $query->leftJoin('escolas', 'escolas.id', '=', 'salas.escola_id')
                        ->select('salas.*')
                        ->orderBy('escolas.nome', $direction)
                        ->orderBy('salas.nome');
                } else {
                    $query->orderBy('salas.' . $sort, $direction);
                }
            } else {
                $query->orderBy('salas.nome');
            }

            $salas = $query->paginate(15);
            // Preservar parâmetros de busca/ordenação na paginação
            $salas->appends($request->query());

            // Carregar escolas para filtro (apenas para superadmin/suporte)
            $escolas = $isSuperAdminOrSupport ? Escola::orderBy('nome')->get() : collect();
        }

        return view('salas.index', compact('salas', 'escolas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('salas.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'capacidade' => 'required|integer|min:1',
            'tipo' => 'required|string|max:100',
            'ativo' => 'boolean',
        ]);

        $data = $request->all();
        
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

        // Gerar código automaticamente
        $codigo = $this->gerarCodigoUnico($data['nome']);
        $data['codigo'] = $codigo;

        $sala = Sala::create($data);
        Historico::registrar('criado', 'Sala', $sala->id, null, $sala->toArray());

        return redirect()->route('salas.index')
            ->with('success', 'Sala criada com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Sala $sala)
    {
        $sala->load(['escola']);
        
        return view('salas.show', compact('sala'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Sala $sala)
    {
        return view('salas.edit', compact('sala'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Sala $sala)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'capacidade' => 'required|integer|min:1',
            'tipo' => 'required|string|max:100',
            'ativo' => 'boolean'
        ]);

        $dadosAntigos = $sala->toArray();
        $sala->update([
            'nome' => $request->nome,
            'descricao' => $request->descricao,
            'capacidade' => $request->capacidade,
            'tipo' => $request->tipo,
            'ativo' => $request->boolean('ativo', true)
        ]);
        $dadosNovos = $sala->fresh()->toArray();
        Historico::registrar('atualizado', 'Sala', $sala->id, $dadosAntigos, $dadosNovos);

        return redirect()->route('salas.index')
            ->with('success', 'Sala atualizada com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sala $sala)
    {
        try {
            // Verificar se há usuários vinculados
            if ($sala->usuarios()->count() > 0) {
                return redirect()->back()
                    ->with('error', 'Não é possível excluir uma sala que possui usuários vinculados.');
            }

            // Verificar se há grade de aulas vinculadas
            if ($sala->gradeAulas()->count() > 0) {
                return redirect()->back()
                    ->with('error', 'Não é possível excluir uma sala que possui grade de aulas vinculadas.');
            }

            $dadosAntigos = $sala->toArray();
            $sala->delete();
            Historico::registrar('excluido', 'Sala', $sala->id, $dadosAntigos, null);

            return redirect()->route('salas.index')
                ->with('success', 'Sala excluída com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erro ao excluir sala: ' . $e->getMessage());
        }
    }

    /**
     * Toggle the active status of a sala.
     */
    public function toggleStatus(Sala $sala)
    {
        try {
            $dadosAntigos = $sala->toArray();
            $sala->update(['ativo' => !$sala->ativo]);
            $dadosNovos = $sala->fresh()->toArray();
            
            $status = $sala->ativo ? 'ativada' : 'desativada';
            Historico::registrar($sala->ativo ? 'ativado' : 'inativado', 'Sala', $sala->id, $dadosAntigos, $dadosNovos);
            return redirect()->route('salas.index')
                ->with('success', "Sala {$status} com sucesso!");
        } catch (\Exception $e) {
            return redirect()->route('salas.index')
                ->with('error', 'Erro ao alterar status da sala: ' . $e->getMessage());
        }
    }

    /**
     * Solicitar transferência de aluno entre salas
     */
    public function solicitarTransferencia(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'aluno_id' => 'required|exists:alunos,id',
            'sala_destino_id' => 'required|exists:salas,id',
            'motivo' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $aluno = Aluno::findOrFail($request->aluno_id);
            $salaDestino = Sala::findOrFail($request->sala_destino_id);
            $salaOrigem = $aluno->sala;

            // Verificar se o aluno já está na sala de destino
            if ($aluno->sala_id == $salaDestino->id) {
                return redirect()->back()
                    ->with('warning', 'O aluno já está matriculado nesta sala.');
            }

            // Verificar se já existe uma transferência pendente para este aluno
            $transferenciaPendente = \App\Models\Transferencia::where('aluno_id', $request->aluno_id)
                ->where('status', 'pendente')
                ->first();

            if ($transferenciaPendente) {
                return redirect()->back()
                    ->with('warning', 'Já existe uma transferência pendente para este aluno.');
            }

            // Verificar capacidade da sala de destino
            if ($salaDestino->alunos()->count() >= $salaDestino->capacidade) {
                return redirect()->back()
                    ->with('error', 'A sala de destino já atingiu sua capacidade máxima.');
            }


            // Criar solicitação de transferência
            \App\Models\Transferencia::create([
                'aluno_id' => $request->aluno_id,
                'sala_origem_id' => $aluno->sala_id,
                'sala_destino_id' => $request->sala_destino_id,
                'solicitante_id' => Auth::id(),
                'motivo' => $request->motivo ?? 'Transferência solicitada via edição de sala',
                'status' => 'pendente',
                'data_solicitacao' => \Carbon\Carbon::now()
            ]);

            $mensagem = "Solicitação de transferência criada com sucesso para {$aluno->nome_completo}";
            if ($salaOrigem) {
                $mensagem .= " da sala {$salaOrigem->codigo} para a sala {$salaDestino->codigo}";
            } else {
                $mensagem .= " para a sala {$salaDestino->codigo}";
            }
            $mensagem .= ". A transferência aguarda aprovação.";

            return redirect()->back()
                ->with('success', $mensagem);

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erro ao criar solicitação de transferência: ' . $e->getMessage());
        }
    }

    /**
     * Remover aluno da sala
     */
    public function removerAluno(Request $request, Sala $sala)
    {
        $validator = Validator::make($request->all(), [
            'aluno_id' => 'required|exists:alunos,id'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $aluno = Aluno::findOrFail($request->aluno_id);
            
            // Verificar se o aluno está realmente matriculado nesta sala
            if ($aluno->sala_id != $sala->id) {
                return redirect()->back()
                    ->with('warning', 'O aluno não está matriculado nesta sala.');
            }

            // Remover o aluno da sala (definir sala_id como null)
            $aluno->update(['sala_id' => null]);

            // Registrar no histórico
            Historico::create([
                'user_id' => Auth::id(),
                'acao' => 'Aluno removido da sala',
                'detalhes' => "Aluno {$aluno->nome_completo} (matrícula: {$aluno->matricula}) foi removido da sala {$sala->codigo} - {$sala->nome}",
                'modelo' => 'Sala',
                'modelo_id' => $sala->id
            ]);

            return redirect()->back()
                ->with('success', "Aluno {$aluno->nome_completo} removido da sala com sucesso!");

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erro ao remover aluno da sala: ' . $e->getMessage());
        }
    }

    /**
     * Adicionar aluno à sala
     */
    public function adicionarAluno(Request $request, Sala $sala)
    {
        $request->validate([
            'aluno_id' => 'required|exists:alunos,id'
        ]);

        try {
            $aluno = Aluno::findOrFail($request->aluno_id);

            // Verificar se o aluno já está em uma sala
            if ($aluno->sala_id && $aluno->sala_id != $sala->id) {
                return redirect()->back()
                    ->with('error', 'Este aluno já está matriculado em outra sala.');
            }

            // Verificar capacidade da sala
            if ($sala->capacidade && $sala->alunos->count() >= $sala->capacidade) {
                return redirect()->back()
                    ->with('error', 'A capacidade máxima da sala foi atingida.');
            }

            // Adicionar aluno à sala
            $aluno->update(['sala_id' => $sala->id]);

            // Log da ação
            Log::create([
                'user_id' => auth()->id(),
                'acao' => 'adicionar_aluno_sala',
                'detalhes' => "Aluno {$aluno->nome_completo} (matrícula: {$aluno->matricula}) foi adicionado à sala {$sala->codigo} - {$sala->nome}",
                'modelo' => 'Sala',
                'modelo_id' => $sala->id
            ]);

            return redirect()->back()
                ->with('success', "Aluno {$aluno->nome_completo} adicionado à sala com sucesso!");

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erro ao adicionar aluno à sala: ' . $e->getMessage());
        }
    }

    /**
     * Transferir aluno para esta sala
     */
    public function transferirAluno(Request $request, Sala $sala)
    {
        $request->validate([
            'aluno_id' => 'required|exists:alunos,id'
        ]);

        try {
            $aluno = Aluno::findOrFail($request->aluno_id);
            $salaAnterior = $aluno->sala;

            // Verificar capacidade da sala de destino
            if ($sala->capacidade && $sala->alunos->count() >= $sala->capacidade) {
                return redirect()->back()
                    ->with('error', 'A capacidade máxima da sala de destino foi atingida.');
            }

            // Transferir aluno
            $aluno->update(['sala_id' => $sala->id]);

            // Log da ação
            Log::create([
                'user_id' => auth()->id(),
                'acao' => 'transferir_aluno_sala',
                'detalhes' => "Aluno {$aluno->nome_completo} (matrícula: {$aluno->matricula}) foi transferido da sala {$salaAnterior->codigo} - {$salaAnterior->nome} para a sala {$sala->codigo} - {$sala->nome}",
                'modelo' => 'Sala',
                'modelo_id' => $sala->id
            ]);

            return redirect()->back()
                ->with('success', "Aluno {$aluno->nome_completo} transferido com sucesso!");

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erro ao transferir aluno: ' . $e->getMessage());
        }
    }

}
