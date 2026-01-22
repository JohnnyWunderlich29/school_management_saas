<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Aluno;
use App\Models\AlunoDocumento;
use App\Models\Responsavel;
use App\Models\Sala;
use App\Models\Historico;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Services\AlertService;

class AlunoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Aluno::with(['sala:id,codigo,nome', 'turma:id,codigo,nome', 'responsaveis:id,nome,sobrenome,telefone_principal'])
            ->select('id', 'nome', 'sobrenome', 'data_nascimento', 'email', 'telefone', 'ativo', 'sala_id', 'turma_id', 'created_at');

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

        // Por padrão, mostrar apenas alunos ativos
        if (!$request->has('mostrar_inativos')) {
            $query->ativos();
        }

        // Filtros usando scopes
        if ($request->filled('nome')) {
            $query->buscarPorNome($request->nome);
        }

        if ($request->filled('ativo')) {
            if ($request->ativo == 'true') {
                $query->ativos();
            } else {
                $query->where('ativo', false);
            }
        }

        if ($request->has('sala_id') && !empty($request->sala_id)) {
            $query->where('sala_id', $request->sala_id);
        }

        // Ordenação dinâmica a partir dos parâmetros sort/direction
        $sort = $request->get('sort');
        $direction = strtolower($request->get('direction', 'asc')) === 'desc' ? 'desc' : 'asc';
        $allowedSorts = ['id', 'nome', 'data_nascimento', 'telefone', 'ativo'];

        if ($sort && in_array($sort, $allowedSorts)) {
            if ($sort === 'nome') {
                $query->orderBy('nome', $direction)->orderBy('sobrenome', $direction);
            } else {
                $query->orderBy($sort, $direction);
            }
        } else {
            // Ordenação padrão
            $query->orderBy('nome')->orderBy('sobrenome');
        }

        $alunos = $query->paginate(15);
        // Preservar parâmetros de busca/ordenação na paginação
        $alunos->appends($request->query());

        // Carregar salas para o filtro (filtradas por escola)
        $salas = \App\Models\Sala::select('id', 'codigo', 'nome')
            ->where('ativo', true)
            ->when(auth()->user()->escola_id, function ($q) {
                $q->where('escola_id', auth()->user()->escola_id);
            })
            ->orderBy('codigo')
            ->get();

        return view('alunos.index', compact('alunos', 'salas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Aplicar filtro de escola nas salas
        $salasQuery = Sala::where('ativo', true);
        if (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte')) {
            if (session('escola_atual')) {
                $salasQuery->where('escola_id', session('escola_atual'));
            }
        } else {
            if (auth()->user()->escola_id) {
                $salasQuery->where('escola_id', auth()->user()->escola_id);
            }
        }
        $salas = $salasQuery->orderBy('codigo')->get();
        return view('alunos.create', compact('salas'));
    }

    /**
     * Search responsaveis via AJAX
     */
    public function searchResponsaveis(Request $request)
    {
        $query = $request->get('q', '');

        $responsaveis = Responsavel::where(function ($q) use ($query) {
            $q->where('nome', 'like', '%' . $query . '%')
                ->orWhere('sobrenome', 'like', '%' . $query . '%')
                ->orWhere('cpf', 'like', '%' . $query . '%');
        })
            ->orderBy('nome')
            ->limit(20)
            ->get()
            ->map(function ($responsavel) {
                return [
                    'id' => $responsavel->id,
                    'text' => $responsavel->nome_completo . ' - ' . ($responsavel->cpf ?? 'Sem CPF'),
                    'nome_completo' => $responsavel->nome_completo
                ];
            });

        return response()->json($responsaveis);
    }

    /**
     * Create new responsavel via AJAX
     */
    public function createResponsavel(Request $request)
    {
        /**
         * Converte data do formato brasileiro (dd/mm/yyyy) para formato do banco (Y-m-d)
         */
        $convertDateFormat = function ($date) {
            if (!$date)
                return null;

            // Se já está no formato Y-m-d, retorna como está
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                return $date;
            }

            // Se está no formato dd/mm/yyyy, converte
            if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $date, $matches)) {
                return $matches[3] . '-' . $matches[2] . '-' . $matches[1];
            }

            return null;
        };

        $validator = Validator::make($request->all(), [
            'nome' => 'required|string|max:100',
            'sobrenome' => 'required|string|max:100',
            'cpf' => 'nullable|string|max:14|unique:responsaveis,cpf',
            'telefone_principal' => 'required|string|max:15',
            'parentesco' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $responsavel = Responsavel::create([
                'nome' => $request->nome,
                'sobrenome' => $request->sobrenome,
                'cpf' => $request->cpf,
                'telefone_principal' => $request->telefone_principal,
                'parentesco' => $request->parentesco,
                'autorizado_buscar' => true,
                'contato_emergencia' => false,
            ]);

            return response()->json([
                'success' => true,
                'responsavel' => [
                    'id' => $responsavel->id,
                    'text' => $responsavel->nome_completo . ' - ' . ($responsavel->cpf ?? 'Sem CPF'),
                    'nome_completo' => $responsavel->nome_completo
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar responsável: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        /**
         * Converte data do formato brasileiro (dd/mm/yyyy) para formato do banco (Y-m-d)
         */
        $convertDateFormat = function ($date) {
            if (!$date)
                return null;

            // Se já está no formato Y-m-d, retorna como está
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                return $date;
            }

            // Se está no formato dd/mm/yyyy, converte
            if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $date, $matches)) {
                return $matches[3] . '-' . $matches[2] . '-' . $matches[1];
            }

            return null;
        };

        // Filtrar arquivos vazios antes da validação
        $documentos = $request->file('documentos');
        if ($documentos) {
            $documentosValidos = [];
            foreach ($documentos as $index => $file) {
                if ($file && $file->isValid() && $file->getSize() > 0) {
                    $documentosValidos[] = $file;
                    \Log::debug("DEBUG STORE - Arquivo válido encontrado", [
                        'index' => $index,
                        'name' => $file->getClientOriginalName(),
                        'size' => $file->getSize()
                    ]);
                } else {
                    \Log::debug("DEBUG STORE - Arquivo inválido ignorado", [
                        'index' => $index,
                        'is_valid' => $file ? $file->isValid() : false,
                        'size' => $file ? $file->getSize() : 0
                    ]);
                }
            }

            // Substituir o array de documentos pelos válidos
            if (empty($documentosValidos)) {
                $request->request->remove('documentos');
                \Log::debug('DEBUG STORE - Removendo campo documentos (nenhum arquivo válido)');
            } else {
                // Criar um novo request com apenas os arquivos válidos
                $request->files->set('documentos', $documentosValidos);
                \Log::debug('DEBUG STORE - Mantendo apenas arquivos válidos', ['count' => count($documentosValidos)]);
            }
        }

        $validator = Validator::make($request->all(), [
            'nome' => 'required|string|max:100',
            'sobrenome' => 'required|string|max:100',
            'data_nascimento' => 'required|date',
            'cpf' => 'nullable|string|max:14|unique:alunos,cpf',
            'rg' => 'nullable|string|max:20',
            'endereco' => 'nullable|string|max:255',
            'cidade' => 'nullable|string|max:100',
            'estado' => 'nullable|string|max:2',
            'cep' => 'nullable|string|max:10',
            'telefone' => 'nullable|string|max:15',
            'email' => 'nullable|email|max:100',
            'genero' => 'nullable|string|max:20',
            'tipo_sanguineo' => 'nullable|string|max:5',
            'alergias' => 'nullable|string',
            'medicamentos' => 'nullable|string',
            'observacoes' => 'nullable|string',
            'sala_id' => 'nullable|exists:salas,id',
            'documentos.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
        ]);



        // Validação customizada: responsável principal deve estar entre os responsáveis selecionados
        $validator->after(function ($validator) use ($request) {
            if ($request->responsavel_principal && $request->responsaveis) {
                // Converter para string para comparação correta
                $responsavelPrincipal = (string) $request->responsavel_principal;
                $responsaveis = array_map('strval', $request->responsaveis);

                if (!in_array($responsavelPrincipal, $responsaveis)) {
                    $validator->errors()->add('responsavel_principal', 'O responsável principal deve estar entre os responsáveis selecionados.');
                }
            }
        });

        if ($validator->fails()) {
            \Log::error('DEBUG STORE - Erro de validação', [
                'errors' => $validator->errors()->toArray(),
                'documentos_errors' => $validator->errors()->get('documentos.*'),
                'all_errors' => $validator->errors()->all()
            ]);
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        \Log::info('DEBUG STORE - Validação passou, iniciando transação');

        // Debug: Log da escola atual antes de criar aluno
        \Log::info('DEBUG ALUNO - Criando aluno', [
            'user_id' => auth()->user()->id,
            'user_email' => auth()->user()->email,
            'user_escola_id' => auth()->user()->escola_id,
            'session_escola_atual' => session('escola_atual'),
            'is_super_admin' => auth()->user()->isSuperAdmin(),
            'aluno_nome' => $request->nome . ' ' . $request->sobrenome,
            'escola_id_para_aluno' => auth()->user()->escola_id,
            'url' => request()->url()
        ]);

        DB::beginTransaction();
        try {
            $aluno = Aluno::create([
                'nome' => $request->nome,
                'matricula' => $request->matricula,
                'sobrenome' => $request->sobrenome,
                'data_nascimento' => $convertDateFormat($request->data_nascimento),
                'cpf' => $request->cpf,
                'rg' => $request->rg,
                'endereco' => $request->endereco,
                'cidade' => $request->cidade,
                'estado' => $request->estado,
                'cep' => $request->cep,
                'telefone' => $request->telefone,
                'escola_id' => session('escola_atual') ?: auth()->user()->escola_id,
                'email' => $request->email,
                'genero' => $request->genero,
                'tipo_sanguineo' => $request->tipo_sanguineo,
                'alergias' => $request->alergias,
                'medicamentos' => $request->medicamentos,
                'observacoes' => $request->observacoes,
                'sala_id' => $request->sala_id,
                'ativo' => true,
            ]);

            // Processar upload de documentos
            \Log::info('DEBUG STORE - Verificando upload de documentos', [
                'hasFile_documentos' => $request->hasFile('documentos'),
                'file_documentos_exists' => $request->file('documentos') !== null,
                'file_documentos_count' => $request->file('documentos') ? count($request->file('documentos')) : 0
            ]);

            if ($request->hasFile('documentos')) {
                \Log::info('DEBUG STORE - Iniciando processamento de documentos');
                foreach ($request->file('documentos') as $index => $arquivo) {
                    \Log::info('DEBUG STORE - Processando arquivo', [
                        'index' => $index,
                        'nome_original' => $arquivo->getClientOriginalName(),
                        'tamanho' => $arquivo->getSize(),
                        'mime_type' => $arquivo->getMimeType(),
                        'is_valid' => $arquivo->isValid()
                    ]);

                    $nomeOriginal = $arquivo->getClientOriginalName();
                    $nomeArquivo = time() . '_' . uniqid() . '.' . $arquivo->getClientOriginalExtension();
                    $caminho = $arquivo->storeAs('documentos/alunos/' . $aluno->id, $nomeArquivo, 'public');

                    $documento = AlunoDocumento::create([
                        'aluno_id' => $aluno->id,
                        'nome_original' => $nomeOriginal,
                        'nome_arquivo' => $nomeArquivo,
                        'tipo_mime' => $arquivo->getMimeType(),
                        'tamanho' => $arquivo->getSize(),
                        'caminho' => $caminho
                    ]);

                    \Log::info('DEBUG STORE - Documento salvo no banco', [
                        'documento_id' => $documento->id,
                        'aluno_id' => $documento->aluno_id,
                        'nome_arquivo' => $documento->nome_arquivo
                    ]);
                }
            } else {
                \Log::info('DEBUG STORE - Nenhum arquivo de documento detectado para upload');
            }

            // Debug: Log após criar aluno
            \Log::info('DEBUG ALUNO - Aluno criado com sucesso', [
                'aluno_id' => $aluno->id,
                'aluno_escola_id' => $aluno->escola_id,
                'aluno_nome' => $aluno->nome . ' ' . $aluno->sobrenome
            ]);

            // Associar responsáveis
            if ($request->responsaveis && is_array($request->responsaveis)) {
                foreach ($request->responsaveis as $responsavelId) {
                    $aluno->responsaveis()->attach($responsavelId, [
                        'responsavel_principal' => $responsavelId == $request->responsavel_principal
                    ]);
                }
            }

            // Registrar no histórico
            Historico::registrar(
                'criado',
                'Aluno',
                $aluno->id,
                null,
                $aluno->toArray(),
                'Aluno criado com sucesso'
            );

            DB::commit();
            AlertService::success('Aluno cadastrado com sucesso!');
            return redirect()->route('alunos.index');
        } catch (\Exception $e) {
            DB::rollBack();
            AlertService::systemError('Erro ao cadastrar aluno', $e);
            return redirect()->back()->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $query = Aluno::with(['responsaveis', 'documentos', 'sala']);

        // Aplicar filtro de escola
        if (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte')) {
            if (session('escola_atual')) {
                $query->where('escola_id', session('escola_atual'));
            }
        } else {
            if (auth()->user()->escola_id) {
                $query->where('escola_id', auth()->user()->escola_id);
            }
        }

        $aluno = $query->findOrFail($id);
        $responsavelPrincipal = $aluno->responsavelPrincipal();

        return view('alunos.show', compact('aluno', 'responsavelPrincipal'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $query = Aluno::with('responsaveis');

        // Aplicar filtro de escola
        if (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte')) {
            if (session('escola_atual')) {
                $query->where('escola_id', session('escola_atual'));
            }
        } else {
            if (auth()->user()->escola_id) {
                $query->where('escola_id', auth()->user()->escola_id);
            }
        }

        $aluno = $query->findOrFail($id);

        // Filtrar responsáveis e salas pela escola
        $responsaveisQuery = Responsavel::orderBy('nome');
        $salasQuery = Sala::where('ativo', true)->orderBy('codigo');

        if (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte')) {
            if (session('escola_atual')) {
                $responsaveisQuery->where('escola_id', session('escola_atual'));
                $salasQuery->where('escola_id', session('escola_atual'));
            }
        } else {
            if (auth()->user()->escola_id) {
                $responsaveisQuery->where('escola_id', auth()->user()->escola_id);
                $salasQuery->where('escola_id', auth()->user()->escola_id);
            }
        }

        $responsaveis = $responsaveisQuery->get();
        $salas = $salasQuery->get();

        $responsavelPrincipalId = $aluno->responsaveis()
            ->wherePivot('responsavel_principal', true)
            ->first()?->id;

        return view('alunos.edit', compact('aluno', 'responsaveis', 'salas', 'responsavelPrincipalId'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        \Log::info('DEBUG UPDATE - Iniciando atualização do aluno', [
            'aluno_id' => $id,
            'request_data' => $request->except(['_token', '_method']),
            'has_files' => $request->hasFile('documentos'),
            'files_info' => $request->file('documentos') ? 'Files present' : 'No files',
            'all_files' => $request->allFiles(),
            'content_type' => $request->header('Content-Type'),
            'method' => $request->method(),
            'raw_files' => $request->file('documentos')
        ]);

        // Filtrar arquivos vazios antes da validação
        $documentos = $request->file('documentos');
        if ($documentos) {
            $documentosValidos = [];
            foreach ($documentos as $index => $file) {
                if ($file && $file->isValid() && $file->getSize() > 0) {
                    $documentosValidos[] = $file;
                    \Log::debug("DEBUG UPDATE - Arquivo válido encontrado", [
                        'index' => $index,
                        'name' => $file->getClientOriginalName(),
                        'size' => $file->getSize()
                    ]);
                } else {
                    \Log::debug("DEBUG UPDATE - Arquivo inválido ignorado", [
                        'index' => $index,
                        'is_valid' => $file ? $file->isValid() : false,
                        'size' => $file ? $file->getSize() : 0
                    ]);
                }
            }

            // Substituir o array de documentos pelos válidos
            if (empty($documentosValidos)) {
                $request->request->remove('documentos');
                \Log::debug('DEBUG UPDATE - Removendo campo documentos (nenhum arquivo válido)');
            } else {
                // Criar um novo request com apenas os arquivos válidos
                $request->files->set('documentos', $documentosValidos);
                \Log::debug('DEBUG UPDATE - Mantendo apenas arquivos válidos', ['count' => count($documentosValidos)]);
            }
        }

        $validator = Validator::make($request->all(), [
            'nome' => 'required|string|max:100',
            'sobrenome' => 'required|string|max:100',
            'data_nascimento' => 'required|date',
            'cpf' => 'nullable|string|max:14|unique:alunos,cpf,' . $id,
            'rg' => 'nullable|string|max:20',
            'endereco' => 'nullable|string|max:255',
            'cidade' => 'nullable|string|max:100',
            'estado' => 'nullable|string|max:2',
            'cep' => 'nullable|string|max:10',
            'telefone' => 'nullable|string|max:15',
            'email' => 'nullable|email|max:100',
            'genero' => 'nullable|string|max:20',
            'tipo_sanguineo' => 'nullable|string|max:5',
            'alergias' => 'nullable|string',
            'medicamentos' => 'nullable|string',
            'observacoes' => 'nullable|string',
            'sala_id' => 'nullable|exists:salas,id',
            'ativo' => 'boolean',
            'responsaveis' => 'nullable|array',
            'responsavel_principal' => 'nullable|integer|exists:responsaveis,id',
            'documentos.*' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
        ]);

        // Validação customizada: responsável principal deve estar entre os responsáveis selecionados
        $validator->after(function ($validator) use ($request) {
            if ($request->responsavel_principal && $request->responsaveis) {
                // Converter para string para comparação correta
                $responsavelPrincipal = (string) $request->responsavel_principal;
                $responsaveis = array_map('strval', $request->responsaveis);

                if (!in_array($responsavelPrincipal, $responsaveis)) {
                    $validator->errors()->add('responsavel_principal', 'O responsável principal deve estar entre os responsáveis selecionados.');
                }
            }
        });

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        /**
         * Converte data do formato brasileiro (dd/mm/yyyy) para formato do banco (Y-m-d)
         */
        $convertDateFormat = function ($date) {
            if (!$date)
                return null;

            // Se já está no formato Y-m-d, retorna como está
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                return $date;
            }

            // Se está no formato dd/mm/yyyy, converte
            if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $date, $matches)) {
                return $matches[3] . '-' . $matches[2] . '-' . $matches[1];
            }

            return null;
        };

        DB::beginTransaction();
        try {
            $aluno = Aluno::findOrFail($id);
            $dadosAntigos = $aluno->toArray();

            $aluno->update([
                'nome' => $request->nome,
                'matricula' => $request->matricula,
                'sobrenome' => $request->sobrenome,
                'data_nascimento' => $convertDateFormat($request->data_nascimento),
                'cpf' => $request->cpf,
                'rg' => $request->rg,
                'endereco' => $request->endereco,
                'cidade' => $request->cidade,
                'estado' => $request->estado,
                'cep' => $request->cep,
                'telefone' => $request->telefone,
                'email' => $request->email,
                'genero' => $request->genero,
                'tipo_sanguineo' => $request->tipo_sanguineo,
                'alergias' => $request->alergias,
                'medicamentos' => $request->medicamentos,
                'observacoes' => $request->observacoes,
                'sala_id' => $request->sala_id,
                'ativo' => $request->ativo ?? false,
            ]);

            // Processar remoção de documentos
            if ($request->has('remover_documentos')) {
                foreach ($request->remover_documentos as $documentoId) {
                    $documento = AlunoDocumento::where('aluno_id', $aluno->id)->find($documentoId);
                    if ($documento) {
                        Storage::disk('public')->delete($documento->caminho);
                        $documento->delete();
                    }
                }
            }

            // Processar upload de novos documentos
            \Log::info('DEBUG UPLOAD - Verificando upload de documentos', [
                'has_file' => $request->hasFile('documentos'),
                'files_count' => $request->hasFile('documentos') ? count($request->file('documentos')) : 0,
                'aluno_id' => $aluno->id,
                'files_array' => $request->file('documentos'),
                'is_array' => is_array($request->file('documentos'))
            ]);

            if ($request->hasFile('documentos')) {
                foreach ($request->file('documentos') as $arquivo) {
                    \Log::info('DEBUG UPLOAD - Processando arquivo', [
                        'nome_original' => $arquivo->getClientOriginalName(),
                        'tamanho' => $arquivo->getSize(),
                        'tipo_mime' => $arquivo->getMimeType()
                    ]);

                    $nomeOriginal = $arquivo->getClientOriginalName();
                    $nomeArquivo = time() . '_' . uniqid() . '.' . $arquivo->getClientOriginalExtension();
                    $caminho = $arquivo->storeAs('documentos/alunos/' . $aluno->id, $nomeArquivo, 'public');

                    \Log::info('DEBUG UPLOAD - Arquivo salvo', [
                        'caminho' => $caminho,
                        'nome_arquivo' => $nomeArquivo
                    ]);

                    $documento = AlunoDocumento::create([
                        'aluno_id' => $aluno->id,
                        'nome_original' => $nomeOriginal,
                        'nome_arquivo' => $nomeArquivo,
                        'tipo_mime' => $arquivo->getMimeType(),
                        'tamanho' => $arquivo->getSize(),
                        'caminho' => $caminho
                    ]);

                    \Log::info('DEBUG UPLOAD - Documento criado no banco', [
                        'documento_id' => $documento->id
                    ]);
                }
            }

            // Atualizar responsáveis
            $aluno->responsaveis()->detach();
            if ($request->responsaveis && is_array($request->responsaveis)) {
                foreach ($request->responsaveis as $responsavelId) {
                    $aluno->responsaveis()->attach($responsavelId, [
                        'responsavel_principal' => $responsavelId == $request->responsavel_principal
                    ]);
                }
            }

            // Registrar no histórico
            Historico::registrar(
                'atualizado',
                'Aluno',
                $aluno->id,
                $dadosAntigos,
                $aluno->fresh()->toArray(),
                'Aluno atualizado com sucesso'
            );

            DB::commit();
            AlertService::success('Aluno atualizado com sucesso!');
            return redirect()->route('alunos.index');
        } catch (\Exception $e) {
            DB::rollBack();
            AlertService::systemError('Erro ao atualizar aluno', $e);
            return redirect()->back()->withInput();
        }
    }

    /**
     * Toggle the active status of the specified resource.
     */
    public function toggleStatus(Aluno $aluno)
    {
        // Verificar se o usuário pode alterar o status deste aluno
        if (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte')) {
            if (session('escola_atual') && $aluno->escola_id !== session('escola_atual')) {
                abort(404);
            }
        } else {
            if (auth()->user()->escola_id && $aluno->escola_id !== auth()->user()->escola_id) {
                abort(404);
            }
        }

        $statusAnterior = $aluno->ativo;
        $aluno->update(['ativo' => !$aluno->ativo]);

        $status = $aluno->ativo ? 'ativado' : 'inativado';

        // Registrar no histórico
        Historico::registrar(
            $aluno->ativo ? 'ativado' : 'inativado',
            'Aluno',
            $aluno->id,
            ['ativo' => $statusAnterior],
            ['ativo' => $aluno->ativo],
            "Aluno {$status} com sucesso"
        );

        AlertService::success("Aluno {$status} com sucesso!");
        return redirect()->route('alunos.index');
    }

    /**
     * Transferir aluno para outra turma
     */
    public function transferir(Request $request, Aluno $aluno)
    {
        // Verificar permissões de escola
        if (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte')) {
            if (session('escola_atual') && $aluno->escola_id !== session('escola_atual')) {
                return response()->json(['success' => false, 'message' => 'Aluno não encontrado.'], 404);
            }
        } else {
            if (auth()->user()->escola_id && $aluno->escola_id !== auth()->user()->escola_id) {
                return response()->json(['success' => false, 'message' => 'Aluno não encontrado.'], 404);
            }
        }

        $request->validate([
            'turma_id' => 'required|exists:turmas,id',
            'solicitar_transferencia_sala' => 'sometimes|boolean',
            'motivo' => 'nullable|string|max:500'
        ]);

        $turmaDestino = \App\Models\Turma::findOrFail($request->turma_id);

        // Verificar se a turma pertence à mesma escola
        if ($turmaDestino->escola_id !== $aluno->escola_id) {
            return response()->json([
                'success' => false,
                'message' => 'Não é possível transferir para turma de outra escola.'
            ], 400);
        }

        // Verificar capacidade da turma (apenas quando transferência imediata)
        $solicitarTransferenciaSala = (bool) $request->boolean('solicitar_transferencia_sala');
        $ocupacaoAtual = $turmaDestino->alunos()->count();
        if (!$solicitarTransferenciaSala && $ocupacaoAtual >= $turmaDestino->capacidade) {
            return response()->json([
                'success' => false,
                'message' => "Turma lotada. Capacidade: {$turmaDestino->capacidade}, Ocupação: {$ocupacaoAtual}"
            ], 400);
        }

        // Verificar se não é a mesma turma
        if ($aluno->turma_id == $turmaDestino->id) {
            return response()->json([
                'success' => false,
                'message' => 'O aluno já está nesta turma.'
            ], 400);
        }

        try {
            DB::beginTransaction();

            $transferenciaCriada = false;
            $transferenciaId = null;

            if ($solicitarTransferenciaSala) {
                // Solicitação: não alterar a turma agora, apenas criar transferência por turma para aprovação
                $turmaAnterior = $aluno->turma;
                $turmaAnteriorNome = $turmaAnterior ? $turmaAnterior->nome : 'Sem turma';

                // Verificar se já existe transferência pendente para o aluno
                $transferenciaPendente = \App\Models\Transferencia::where('aluno_id', $aluno->id)
                    ->where('status', 'pendente')
                    ->first();

                if (!$transferenciaPendente) {
                    // Criar solicitação SEM checar capacidade aqui (capacidade será validada na aprovação)
                    $novaTransferencia = \App\Models\Transferencia::create([
                        'aluno_id' => $aluno->id,
                        'turma_id' => $aluno->turma_id, // pode ser null
                        'turma_destino_id' => $turmaDestino->id,
                        'solicitante_id' => auth()->id(),
                        'motivo' => $request->input('motivo') ?: 'Transferência solicitada via tela de turmas',
                        'status' => 'pendente',
                        'data_solicitacao' => \Carbon\Carbon::now(),
                    ]);
                    $transferenciaCriada = true;
                    $transferenciaId = $novaTransferencia->id;
                }
            } else {
                // Transferência imediata de turma (sem aprovação)
                $turmaAnterior = $aluno->turma;
                $turmaAnteriorNome = $turmaAnterior ? $turmaAnterior->nome : 'Sem turma';

                // Atualizar turma do aluno
                $aluno->update(['turma_id' => $turmaDestino->id]);

                // Registrar no histórico
                Historico::registrar(
                    'transferencia_turma',
                    'Aluno',
                    $aluno->id,
                    ['turma_id' => $turmaAnterior?->id, 'turma_nome' => $turmaAnteriorNome],
                    ['turma_id' => $turmaDestino->id, 'turma_nome' => $turmaDestino->nome],
                    "Aluno transferido de '{$turmaAnteriorNome}' para '{$turmaDestino->nome}'"
                );
            }

            DB::commit();

            if ($solicitarTransferenciaSala) {
                return response()->json([
                    'success' => true,
                    'message' => 'Solicitação de transferência criada com sucesso!',
                    'transferencia_criada' => $transferenciaCriada,
                    'transferencia_id' => $transferenciaId
                ]);
            } else {
                return response()->json([
                    'success' => true,
                    'message' => "Aluno transferido para '{$turmaDestino->nome}' com sucesso!",
                    'transferencia_criada' => $transferenciaCriada,
                    'transferencia_id' => $transferenciaId
                ]);
            }

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor. Tente novamente.'
            ], 500);
        }
    }
}
