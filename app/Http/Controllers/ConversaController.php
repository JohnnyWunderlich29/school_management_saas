<?php

namespace App\Http\Controllers;

use App\Models\Conversa;
use App\Models\Mensagem;
use App\Models\User;
use App\Models\Turma;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ConversaController extends Controller
{
    /**
     * Listar conversas do usuário
     */
    public function index(Request $request): View|RedirectResponse
    {
        $this->authorize('viewAny', Conversa::class);

        $user = Auth::user();

        $conversasQuery = Conversa::participante($user->id)
            ->ativas()
            ->comContagemMensagensNaoLidas($user->id)
            ->with(['ultimaMensagem.remetente', 'participantesAtivos', 'turma']);

        // Filtrar conversas por escola
        if ($user->isSuperAdmin() || $user->temCargo('Suporte')) {
            if (session('escola_atual')) {
                $conversasQuery->whereHas('participantes', function ($query) {
                    $query->where('escola_id', session('escola_atual'));
                });
            }
        } else {
            if ($user->escola_id) {
                $conversasQuery->whereHas('participantes', function ($query) use ($user) {
                    $query->where('escola_id', $user->escola_id);
                });
            }
        }
        
        // Verificar se existe alguma conversa e redirecionar para a primeira
        $primeiraConversa = $conversasQuery->orderBy('ultima_mensagem_at', 'desc')->first();
        
        if ($primeiraConversa) {
            return redirect()->route('conversas.show', $primeiraConversa);
        }
        
        // Se não existir nenhuma conversa, mostrar a página de índice
        $conversas = $conversasQuery->orderBy('ultima_mensagem_at', 'desc')
            ->paginate(20);

        return view('comunicacao.conversas.index', compact('conversas'));
    }

    /**
     * Exibir uma conversa específica
     */
    public function show(Conversa $conversa): View
    {
        $this->authorize('view', $conversa);

        $user = Auth::user();

        // Verificar se o usuário é participante
        if (!$conversa->isParticipante($user->id)) {
            abort(403, 'Você não tem acesso a esta conversa.');
        }

        // Carregar apenas as últimas 30 mensagens inicialmente
        $mensagens = $conversa->mensagens()
            ->with([
                'remetente:id,name,email,avatar',
                'leituras:id,mensagem_id,user_id,lida_em'
            ])
            ->orderBy('created_at', 'desc')
            ->take(30)
            ->get()
            ->reverse(); // Reverter para ordem cronológica

        // Marcar mensagens como lidas
        $conversa->marcarComoLida($user->id);

        // Carregar participantes com eager loading
        $participantes = $conversa->participantesAtivos()
            ->select('users.id', 'users.name', 'users.email')
            ->get();

        // Carregar todos os usuários da escola (exceto o usuário atual)
        $usuariosDisponiveis = User::select('id', 'name', 'email', 'avatar')
            ->where('id', '!=', $user->id)
            ->where('escola_id', $user->escola_id)
            ->whereNotNull('name') // Garantir que apenas usuários com nome sejam exibidos
            ->orderBy('name')
            ->get();

        // Carregar todas as conversas do usuário para o sidebar com contagem otimizada
        $conversasQuery = Conversa::participante($user->id)
            ->ativas()
            ->comContagemMensagensNaoLidas($user->id)
            ->with(['ultimaMensagem.remetente', 'participantesAtivos', 'turma']);

        // Filtrar conversas por escola
        if ($user->isSuperAdmin() || $user->temCargo('Suporte')) {
            if (session('escola_atual')) {
                $conversasQuery->whereHas('participantes', function ($query) {
                    $query->where('escola_id', session('escola_atual'));
                });
            }
        } else {
            if ($user->escola_id) {
                $conversasQuery->whereHas('participantes', function ($query) use ($user) {
                    $query->where('escola_id', $user->escola_id);
                });
            }
        }

        $todasConversas = $conversasQuery->orderBy('ultima_mensagem_at', 'desc')->get();
        
        // Carregar turmas para o modal de nova conversa
        $turmasQuery = Turma::ativas();
        
        // Filtrar turmas por escola
        if ($user->isSuperAdmin() || $user->temCargo('Suporte')) {
            if (session('escola_atual')) {
                $turmasQuery->where('escola_id', session('escola_atual'));
            }
        } else {
            if ($user->escola_id) {
                $turmasQuery->where('escola_id', $user->escola_id);
            }
        }
        
        $turmas = $turmasQuery->orderBy('nome')->get();

        return view('comunicacao.conversas.show', compact('conversa', 'mensagens', 'participantes', 'usuariosDisponiveis', 'todasConversas', 'turmas'));
    }

    /**
     * Criar nova conversa
     */
    public function create(): View
    {
        $this->authorize('create', Conversa::class);

        $usuariosQuery = User::where('id', '!=', Auth::id());

        // Filtrar usuários por escola
        if (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte')) {
            if (session('escola_atual')) {
                $usuariosQuery->where('escola_id', session('escola_atual'));
            }
        } else {
            if (auth()->user()->escola_id) {
                $usuariosQuery->where('escola_id', auth()->user()->escola_id);
            }
        }

        $usuarios = $usuariosQuery->with('cargos')->orderBy('name')->get();

        $turmasQuery = Turma::ativas();

        // Filtrar turmas por escola
        if (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte')) {
            if (session('escola_atual')) {
                $turmasQuery->where('escola_id', session('escola_atual'));
            }
        } else {
            if (auth()->user()->escola_id) {
                $turmasQuery->where('escola_id', auth()->user()->escola_id);
            }
        }

        $turmas = $turmasQuery->orderBy('nome')->get();

        return view('comunicacao.conversas.create', compact('usuarios', 'turmas'));
    }

    /**
     * Armazenar nova conversa
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Conversa::class);

        // Log dos dados enviados pelo formulário
        \Log::info('=== DADOS ENVIADOS PARA CRIAR CONVERSA ===');
        \Log::info('Usuário logado:', ['user_id' => Auth::id(), 'user_name' => Auth::user()->name]);
        \Log::info('Dados do request completo:', $request->all());
        \Log::info('Título:', ['titulo' => $request->titulo]);
        \Log::info('Tipo:', ['tipo' => $request->tipo]);
        \Log::info('Descrição:', ['descricao' => $request->descricao]);
        \Log::info('Turma ID:', ['turma_id' => $request->turma_id]);
        \Log::info('Participantes:', ['participantes' => $request->participantes]);
        \Log::info('Mensagem inicial:', ['mensagem_inicial' => $request->mensagem_inicial]);
        \Log::info('Ativo:', ['ativo' => $request->ativo]);
        \Log::info('Headers:', $request->headers->all());
        \Log::info('=== FIM DOS DADOS ENVIADOS ===');

        $validator = Validator::make($request->all(), [
            'titulo' => 'required|string|max:255',
            'tipo' => 'required|in:individual,grupo,turma,geral',
            'descricao' => 'nullable|string|max:1000',
            'turma_id' => 'nullable|exists:turmas,id',
            'participantes' => 'required|array|min:1',
            'participantes.*' => 'exists:users,id',
            'mensagem_inicial' => 'required|string|max:2000'
        ]);

        if ($validator->fails()) {
            \Log::info('Validação falhou:', ['errors' => $validator->errors()->toArray()]);
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        \Log::info('Validação passou com sucesso');

        try {
            \Log::info('Iniciando criação da conversa...');

            // Criar conversa
            $dadosConversa = [
                'titulo' => $request->titulo,
                'tipo' => $request->tipo,
                'descricao' => $request->descricao,
                'turma_id' => $request->turma_id,
                'criador_id' => Auth::id(),
                'ativo' => true
            ];
            \Log::info('Dados para criar conversa:', $dadosConversa);

            $conversa = Conversa::create($dadosConversa);
            \Log::info('Conversa criada com sucesso:', ['conversa_id' => $conversa->id]);

            // Adicionar criador como participante
            \Log::info('Adicionando criador como participante admin:', ['user_id' => Auth::id()]);
            $conversa->adicionarParticipante(Auth::id(), 'admin');

            // Adicionar outros participantes
            \Log::info('Adicionando outros participantes:', ['participantes' => $request->participantes]);
            foreach ($request->participantes as $participanteId) {
                if ($participanteId != Auth::id()) {
                    \Log::info('Adicionando participante:', ['participante_id' => $participanteId, 'papel' => 'responsavel']);
                    $conversa->adicionarParticipante($participanteId, 'responsavel');
                }
            }

            // Criar mensagem inicial
            $dadosMensagem = [
                'remetente_id' => Auth::id(),
                'conteudo' => $request->mensagem_inicial,
                'tipo' => 'texto'
            ];
            \Log::info('Criando mensagem inicial:', $dadosMensagem);
            $mensagem = $conversa->mensagens()->create($dadosMensagem);
            \Log::info('Mensagem inicial criada:', ['mensagem_id' => $mensagem->id]);

            // Criar notificações para os participantes
             \Log::info('Criando notificações para participantes...');
             foreach ($request->participantes as $participanteId) {
                 if ($participanteId != Auth::id()) {
                     Notification::createForUser(
                         $participanteId,
                         'info',
                         'Nova conversa criada',
                         'Você foi adicionado à conversa: ' . $conversa->titulo . ' por ' . Auth::user()->name,
                         [
                             'conversa_id' => $conversa->id,
                             'criador_id' => Auth::id(),
                             'criador_nome' => Auth::user()->name,
                             'tipo_conversa' => $conversa->tipo
                         ],
                         route('conversas.show', $conversa->id),
                         'Ver conversa'
                     );
                     \Log::info('Notificação criada para participante:', ['participante_id' => $participanteId]);
                 }
             }

            \Log::info('Conversa criada com sucesso, redirecionando...', ['conversa_id' => $conversa->id]);
            return redirect()->route('conversas.show', $conversa)
                ->with('success', 'Conversa criada com sucesso!');

        } catch (\Exception $e) {
            \Log::error('Erro ao criar conversa:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()
                ->with('error', 'Erro ao criar conversa: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Enviar mensagem
     */
    public function enviarMensagem(Request $request, Conversa $conversa): JsonResponse
    {
        $this->authorize('sendMessage', $conversa);

        $user = Auth::user();

        // Verificar se o usuário é participante
        if (!$conversa->isParticipante($user->id)) {
            return response()->json(['error' => 'Você não tem acesso a esta conversa.'], 403);
        }

        // Validação robusta de arquivos
        $rules = [
            'conteudo' => 'required_without:arquivo|string|max:2000',
            'importante' => 'boolean'
        ];

        if ($request->hasFile('arquivo')) {
            $rules['arquivo'] = [
                'required',
                'file',
                'max:20480', // 20MB
                'mimes:jpg,jpeg,png,gif,webp,svg,mp4,avi,mov,wmv,flv,webm,mp3,wav,ogg,aac,m4a,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,zip,rar'
            ];
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $dadosMensagem = [
                'conversa_id' => $conversa->id,
                'remetente_id' => $user->id,
                'conteudo' => $request->conteudo ?? '',
                'tipo' => 'texto',
                'importante' => $request->boolean('importante', false)
            ];

            // Processar arquivo se enviado
            if ($request->hasFile('arquivo')) {
                $arquivo = $request->file('arquivo');

                // Validação adicional de segurança
                if (!$this->validarArquivoSeguro($arquivo)) {
                    return response()->json(['error' => 'Tipo de arquivo não permitido ou arquivo corrompido.'], 422);
                }

                $nomeArquivo = time() . '_' . Str::slug(pathinfo($arquivo->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $arquivo->getClientOriginalExtension();
                $caminhoArquivo = $arquivo->storeAs('comunicacao/arquivos', $nomeArquivo, 'public');

                $dadosMensagem['arquivo_path'] = $caminhoArquivo;
                $dadosMensagem['arquivo_nome'] = $arquivo->getClientOriginalName();
                $dadosMensagem['arquivo_tamanho'] = $arquivo->getSize();
                $dadosMensagem['tipo'] = $this->determinarTipoArquivo($arquivo);
            }

            $mensagem = Mensagem::create($dadosMensagem);
            $mensagem->load('remetente');

            return response()->json([
                'success' => true,
                'mensagem' => [
                    'id' => $mensagem->id,
                    'conteudo' => $mensagem->conteudo,
                    'tipo' => $mensagem->tipo,
                    'importante' => $mensagem->importante,
                    'remetente_id' => $mensagem->remetente_id,
                    'arquivo_url' => $mensagem->arquivo_url,
                    'arquivo_nome' => $mensagem->arquivo_nome,
                    'arquivo_tamanho_formatado' => $mensagem->arquivo_tamanho_formatado,
                    'remetente' => [
                        'id' => $mensagem->remetente->id,
                        'name' => $mensagem->remetente->name,
                        'initials' => $this->getInitials($mensagem->remetente->name),
                        'avatar' => $mensagem->remetente->avatar_url ?? null
                    ],
                    'created_at' => $mensagem->created_at->format('d/m/Y H:i'),
                    'foi_editada' => $mensagem->foiEditada(),
                    'is_own' => $mensagem->remetente_id === Auth::id()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao enviar mensagem: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Marcar mensagens como lidas
     */
    public function marcarComoLida(Conversa $conversa): JsonResponse
    {
        $user = Auth::user();

        if (!$conversa->isParticipante($user->id)) {
            return response()->json(['error' => 'Você não tem acesso a esta conversa.'], 403);
        }

        $conversa->marcarComoLida($user->id);

        return response()->json(['success' => true]);
    }

    /**
     * Buscar mensagens (AJAX)
     */
    public function buscarMensagens(Request $request, Conversa $conversa): JsonResponse
    {
        $user = Auth::user();

        if (!$conversa->isParticipante($user->id)) {
            return response()->json(['error' => 'Você não tem acesso a esta conversa.'], 403);
        }

        $termo = $request->get('termo');
        $page = $request->get('page', 1);

        $mensagens = $conversa->mensagens()
            ->select('id', 'conversa_id', 'remetente_id', 'conteudo', 'tipo', 'importante', 'arquivo_path', 'arquivo_nome', 'arquivo_tamanho', 'created_at', 'updated_at')
            ->with('remetente:id,name,email,avatar')
            ->when($termo, function ($query) use ($termo) {
                $query->where('conteudo', 'like', '%' . $termo . '%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20, ['*'], 'page', $page);

        return response()->json($mensagens);
    }

    /**
     * Buscar novas mensagens (AJAX) - para polling
     */
    public function mensagens(Request $request, Conversa $conversa): JsonResponse
    {
        $user = Auth::user();

        if (!$conversa->isParticipante($user->id)) {
            return response()->json(['error' => 'Você não tem acesso a esta conversa.'], 403);
        }

        $after = $request->get('after');

        $query = $conversa->mensagens()
            ->select('id', 'conversa_id', 'remetente_id', 'conteudo', 'tipo', 'importante', 'arquivo_path', 'arquivo_nome', 'arquivo_tamanho', 'created_at', 'updated_at')
            ->with([
                'remetente:id,name,email,avatar',
                'leituras:id,mensagem_id,user_id,lida_em'
            ])
            ->orderBy('created_at', 'asc');

        if ($after) {
            $query->where('id', '>', $after);
        }

        $mensagens = $query->get();

        return response()->json([
            'mensagens' => $mensagens->map(function ($mensagem) {
                return [
                    'id' => $mensagem->id,
                    'conteudo' => $mensagem->conteudo,
                    'tipo' => $mensagem->tipo,
                    'importante' => $mensagem->importante,
                    'remetente_id' => $mensagem->remetente_id,
                    'arquivo_url' => $mensagem->arquivo_url,
                    'arquivo_nome' => $mensagem->arquivo_nome,
                    'arquivo_tamanho_formatado' => $mensagem->arquivo_tamanho_formatado,
                    'remetente' => [
                        'id' => $mensagem->remetente->id,
                        'name' => $mensagem->remetente->name,
                        'avatar' => $mensagem->remetente->avatar_url ?? null
                    ],
                    'created_at' => $mensagem->created_at->format('d/m/Y H:i'),
                    'foi_editada' => $mensagem->foiEditada()
                ];
            })
        ]);
    }

    /**
     * Carregar mensagens anteriores da conversa
     */
    public function mensagensAnteriores(Request $request, Conversa $conversa): JsonResponse
    {
        $user = Auth::user();

        if (!$conversa->isParticipante($user->id)) {
            return response()->json(['error' => 'Você não tem acesso a esta conversa.'], 403);
        }

        $before = $request->get('before'); // ID da mensagem mais antiga já carregada
        $limit = $request->get('limit', 20); // Quantidade de mensagens a carregar

        $query = $conversa->mensagens()
            ->select('id', 'conversa_id', 'remetente_id', 'conteudo', 'tipo', 'importante', 'arquivo_path', 'arquivo_nome', 'arquivo_tamanho', 'created_at', 'updated_at')
            ->with([
                'remetente:id,name,email,avatar',
                'leituras:id,mensagem_id,user_id,lida_em'
            ])
            ->orderBy('created_at', 'desc');

        if ($before) {
            $query->where('id', '<', $before);
        }

        $mensagens = $query->limit($limit)->get()->reverse()->values();

        return response()->json([
            'mensagens' => $mensagens->map(function ($mensagem) {
                return [
                    'id' => $mensagem->id,
                    'conteudo' => $mensagem->conteudo,
                    'tipo' => $mensagem->tipo,
                    'importante' => $mensagem->importante,
                    'remetente_id' => $mensagem->remetente_id,
                    'arquivo_url' => $mensagem->arquivo_url,
                    'arquivo_nome' => $mensagem->arquivo_nome,
                    'arquivo_tamanho_formatado' => $mensagem->arquivo_tamanho_formatado,
                    'remetente' => [
                        'id' => $mensagem->remetente->id,
                        'name' => $mensagem->remetente->name,
                        'avatar' => $mensagem->remetente->avatar_url ?? null
                    ],
                    'created_at' => $mensagem->created_at->format('d/m/Y H:i'),
                    'foi_editada' => $mensagem->foiEditada()
                ];
            }),
            'has_more' => $mensagens->count() === $limit
        ]);
    }




    /**
     * Arquivar conversa
     */
    public function arquivar(Conversa $conversa): JsonResponse
    {
        $user = Auth::user();

        if (!$conversa->isParticipante($user->id)) {
            return response()->json(['error' => 'Você não tem acesso a esta conversa.'], 403);
        }

        try {
            $conversa->removerParticipante($user->id);

            return response()->json(['success' => true, 'message' => 'Conversa arquivada com sucesso!']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao arquivar conversa: ' . $e->getMessage()], 500);
        }
    }




    /**
     * Determinar tipo de arquivo baseado na extensão
     */
    /**
     * Validar se o arquivo é seguro
     */
    private function validarArquivoSeguro($arquivo): bool
    {
        // Verificar MIME type real do arquivo
        $mimeType = $arquivo->getMimeType();
        $extensao = strtolower($arquivo->getClientOriginalExtension());

        // MIME types permitidos
        $mimeTypesPermitidos = [
            // Imagens
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/gif',
            'image/webp',
            'image/svg+xml',
            // Vídeos
            'video/mp4',
            'video/avi',
            'video/quicktime',
            'video/x-msvideo',
            'video/x-flv',
            'video/webm',
            // Áudios
            'audio/mpeg',
            'audio/wav',
            'audio/ogg',
            'audio/aac',
            'audio/mp4',
            // Documentos
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'text/plain',
            // Compactados
            'application/zip',
            'application/x-rar-compressed'
        ];

        // Verificar se o MIME type é permitido
        if (!in_array($mimeType, $mimeTypesPermitidos)) {
            return false;
        }

        // Verificar correspondência entre extensão e MIME type
        $correspondencias = [
            'jpg' => ['image/jpeg', 'image/jpg'],
            'jpeg' => ['image/jpeg', 'image/jpg'],
            'png' => ['image/png'],
            'gif' => ['image/gif'],
            'webp' => ['image/webp'],
            'svg' => ['image/svg+xml'],
            'mp4' => ['video/mp4', 'audio/mp4'],
            'avi' => ['video/avi', 'video/x-msvideo'],
            'mov' => ['video/quicktime'],
            'wmv' => ['video/x-ms-wmv'],
            'flv' => ['video/x-flv'],
            'webm' => ['video/webm'],
            'mp3' => ['audio/mpeg'],
            'wav' => ['audio/wav'],
            'ogg' => ['audio/ogg'],
            'aac' => ['audio/aac'],
            'm4a' => ['audio/mp4'],
            'pdf' => ['application/pdf'],
            'doc' => ['application/msword'],
            'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            'xls' => ['application/vnd.ms-excel'],
            'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
            'ppt' => ['application/vnd.ms-powerpoint'],
            'pptx' => ['application/vnd.openxmlformats-officedocument.presentationml.presentation'],
            'txt' => ['text/plain'],
            'zip' => ['application/zip'],
            'rar' => ['application/x-rar-compressed']
        ];

        if (isset($correspondencias[$extensao])) {
            return in_array($mimeType, $correspondencias[$extensao]);
        }

        return false;
    }

    /**
     * Determinar tipo do arquivo baseado na extensão
     */
    private function determinarTipoArquivo($arquivo): string
    {
        $extensao = strtolower($arquivo->getClientOriginalExtension());

        $tiposImagem = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
        $tiposVideo = ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm'];
        $tiposAudio = ['mp3', 'wav', 'ogg', 'aac', 'm4a'];
        $tiposDocumento = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt'];
        $tiposCompactado = ['zip', 'rar'];

        if (in_array($extensao, $tiposImagem)) {
            return 'imagem';
        } elseif (in_array($extensao, $tiposVideo)) {
            return 'video';
        } elseif (in_array($extensao, $tiposAudio)) {
            return 'audio';
        } elseif (in_array($extensao, $tiposDocumento)) {
            return 'documento';
        } elseif (in_array($extensao, $tiposCompactado)) {
            return 'compactado';
        }

        return 'arquivo';
    }

    /**
     * Finalizar uma conversa
     */
    public function finalizar(Conversa $conversa)
    {
        // Verificar se o usuário pode finalizar a conversa
        if (!$conversa->isParticipante(auth()->id()) && $conversa->criador_id !== auth()->id()) {
            abort(403, 'Você não tem permissão para finalizar esta conversa.');
        }

        try {
            $conversa->update([
                'ativa' => false,
                'finalizada_em' => now(),
                'finalizada_por' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Conversa finalizada com sucesso.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao finalizar conversa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Excluir uma conversa
     */
    public function destroy(Conversa $conversa)
    {
        // Verificar se o usuário pode excluir a conversa (apenas criador)
        if ($conversa->criador_id !== auth()->id()) {
            abort(403, 'Apenas o criador da conversa pode excluí-la.');
        }

        try {
            // Excluir todas as mensagens da conversa
            $conversa->mensagens()->delete();

            // Remover todos os participantes
            $conversa->participantes()->detach();

            // Excluir a conversa
            $conversa->delete();

            return response()->json([
                'success' => true,
                'message' => 'Conversa excluída com sucesso.',
                'redirect' => route('conversas.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir conversa: ' . $e->getMessage()
            ], 500);
        }
    }

    // Métodos de API para funcionalidades do chat
    public function buscarUsuarios(Request $request)
    {
        $termo = $request->get('q', '');
        $conversaId = $request->get('conversa_id');

        $usuarios = User::where('name', 'like', "%{$termo}%")
            ->orWhere('email', 'like', "%{$termo}%")
            ->when($conversaId, function ($query) use ($conversaId) {
                // Excluir usuários que já são participantes
                $query->whereNotIn('id', function ($subQuery) use ($conversaId) {
                    $subQuery->select('user_id')
                        ->from('conversa_participantes')
                        ->where('conversa_id', $conversaId);
                });
            })
            ->limit(10)
            ->get(['id', 'name', 'email']);

        return response()->json($usuarios);
    }

    public function adicionarParticipante(Request $request, Conversa $conversa)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        // Verificar se o usuário já é participante
        $jaParticipante = $conversa->participantes()->where('user_id', $request->user_id)->exists();

        if ($jaParticipante) {
            return response()->json(['error' => 'Usuário já é participante desta conversa'], 400);
        }

        $conversa->participantes()->create([
            'user_id' => $request->user_id,
            'adicionado_por' => auth()->id(),
            'adicionado_em' => now()
        ]);

        $usuario = User::find($request->user_id);

        return response()->json([
            'success' => true,
            'message' => 'Participante adicionado com sucesso',
            'participante' => [
                'id' => $usuario->id,
                'name' => $usuario->name,
                'email' => $usuario->email
            ]
        ]);
    }

    public function removerParticipante(Conversa $conversa, User $usuario)
    {
        $participante = $conversa->participantes()->where('user_id', $usuario->id)->first();

        if (!$participante) {
            return response()->json(['error' => 'Usuário não é participante desta conversa'], 404);
        }

        $participante->delete();

        return response()->json([
            'success' => true,
            'message' => 'Participante removido com sucesso'
        ]);
    }

    public function listarParticipantes(Conversa $conversa)
    {
        $participantes = $conversa->participantes()
            ->with('user:id,name,email')
            ->get()
            ->map(function ($participante) {
                return [
                    'id' => $participante->user->id,
                    'name' => $participante->user->name,
                    'email' => $participante->user->email,
                    'adicionado_em' => $participante->adicionado_em,
                    'is_creator' => $participante->conversa->criado_por === $participante->user->id
                ];
            });

        return response()->json($participantes);
    }

    public function indicarDigitacao(Request $request, Conversa $conversa)
    {
        // Armazenar indicação de digitação em cache por 5 segundos
        $key = "typing_{$conversa->id}_{$request->user()->id}";
        Cache::put($key, [
            'user_id' => $request->user()->id,
            'user_name' => $request->user()->name,
            'timestamp' => now()
        ], 5);

        return response()->json(['success' => true]);
    }

    public function obterStatus(Conversa $conversa)
    {
        $this->authorize('view', $conversa);

        $user = Auth::user();

        // Verificar se o usuário é participante
        if (!$conversa->isParticipante($user->id)) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }

        // Obter usuários digitando
        $digitando = [];
        $participantes = $conversa->participantes()->pluck('user_id');

        foreach ($participantes as $userId) {
            $key = "typing_{$conversa->id}_{$userId}";
            $typing = Cache::get($key);

            if ($typing && $userId !== auth()->id()) {
                $digitando[] = $typing;
            }
        }

        // Obter última atividade dos participantes
        $ultimaAtividade = $conversa->mensagens()
            ->with('remetente:id,name')
            ->latest()
            ->first();

        return response()->json([
            'digitando' => $digitando,
            'ultima_atividade' => $ultimaAtividade ? [
                'usuario' => $ultimaAtividade->remetente->name,
                'timestamp' => $ultimaAtividade->created_at
            ] : null,
            'total_participantes' => $conversa->participantes()->count(),
            'total_mensagens' => $conversa->mensagens()->count()
        ]);
    }

    // Métodos web para compatibilidade com rotas existentes
    public function adicionarParticipanteWeb(Request $request, Conversa $conversa)
    {
        $request->validate([
            'usuario_id' => 'required|exists:users,id'
        ]);

        // Verificar se o usuário já é participante
        $jaParticipante = $conversa->participantes()->where('user_id', $request->usuario_id)->exists();

        if ($jaParticipante) {
            return response()->json(['success' => false, 'message' => 'Usuário já é participante desta conversa'], 400);
        }

        $conversa->participantes()->create([
            'user_id' => $request->usuario_id,
            'adicionado_por' => auth()->id(),
            'adicionado_em' => now()
        ]);

        $usuario = User::find($request->usuario_id);

        return response()->json([
            'success' => true,
            'message' => 'Participante adicionado com sucesso',
            'participante' => [
                'id' => $usuario->id,
                'name' => $usuario->name,
                'email' => $usuario->email
            ]
        ]);
    }

    public function listarParticipantesWeb(Conversa $conversa)
    {
        $participantes = $conversa->participantes()
            ->with('user:id,name,email')
            ->get()
            ->map(function ($participante) {
                return [
                    'id' => $participante->user->id,
                    'name' => $participante->user->name,
                    'email' => $participante->user->email,
                    'adicionado_em' => $participante->adicionado_em,
                    'is_creator' => $participante->conversa->criado_por === $participante->user->id
                ];
            });

        return response()->json($participantes);
    }

    public function removerParticipanteWeb(Conversa $conversa, User $user)
    {
        $participante = $conversa->participantes()->where('user_id', $user->id)->first();

        if (!$participante) {
            return response()->json(['success' => false, 'message' => 'Usuário não é participante desta conversa'], 404);
        }

        $participante->delete();

        return response()->json([
            'success' => true,
            'message' => 'Participante removido com sucesso'
        ]);
    }

    /**
     * Carregar mensagens com paginação via AJAX
     */
    public function carregarMensagens(Conversa $conversa, Request $request)
    {
        $this->authorize('view', $conversa);

        $user = Auth::user();

        if (!$conversa->isParticipante($user->id)) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }

        try {
            $page = $request->get('page', 1);
            $perPage = min($request->get('per_page', 10), 50);

            $latest = filter_var($request->get('latest', false), FILTER_VALIDATE_BOOLEAN);

        $query = $conversa->mensagens()
            ->with([
                'remetente:id,name,email,avatar',
                'leituras:id,mensagem_id,user_id,lida_em'
            ]);

        if ($latest) {
            $lastMessageId = (int) $request->get('last_message_id', 0);
            
            // Verificar se o ID da última mensagem é válido
            if ($lastMessageId <= 0) {
                $lastMessageId = $conversa->mensagens()->max('id') ?? 0;
            }

            $mensagens = $query->where('id', '>', $lastMessageId)
                ->orderBy('created_at', 'asc')
                ->get();

            $totalMensagens = $conversa->mensagens()->count() ?? 0;
            $mensagensData = $mensagens;
        } else {
            $mensagens = $query->orderBy('created_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);

            $mensagensData = $mensagens->getCollection()->reverse()->values();
            $totalMensagens = $mensagens->total();
        }

        $mensagensFormatadas = $mensagensData->map(function ($mensagem) use ($user) {
            $isOwn = $mensagem->remetente_id === $user->id;
            $leitura = $mensagem->leituras?->where('user_id', $user->id)->first();

            $remetente = $mensagem->remetente;

            return [
                'id' => $mensagem->id,
                'conteudo' => $mensagem->conteudo,
                'created_at' => optional($mensagem->created_at)->format('H:i') ?? '',
                'remetente' => [
                    'id' => $remetente?->id ?? null,
                    'name' => $remetente?->name ?? 'Usuário desconhecido',
                    'initials' => $this->getInitials($remetente?->name ?? 'U')
                ],
                'is_own' => $isOwn,
                'status' => $isOwn ? ($leitura ? 'Lido' : 'Enviado') : null
            ];
        });

        if ($latest) {
            return response()->json([
                'mensagens' => $mensagensFormatadas,
                'total' => $totalMensagens
            ]);
        }

        return response()->json([
            'mensagens' => $mensagensFormatadas,
            'current_page' => $mensagens->currentPage(),
            'last_page' => $mensagens->lastPage(),
            'has_more' => $mensagens->hasMorePages(),
            'total' => $totalMensagens
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Erro ao carregar mensagens: ' . $e->getMessage(),
            'mensagens' => []
        ], 200); // Retornando 200 para evitar erros no cliente
    }
    }


    /**
     * Obter iniciais do nome
     */
    private function getInitials($name)
    {
        $words = explode(' ', $name);
        $initials = '';

        foreach ($words as $word) {
            if (!empty($word)) {
                $initials .= strtoupper(substr($word, 0, 1));
                if (strlen($initials) >= 2)
                    break;
            }
        }

        return $initials ?: 'U';
    }

    /**
     * Carregar lista de conversas para atualização da sidebar
     */
    public function carregarListaConversas(): JsonResponse
    {
        $user = Auth::user();

        // Carregar todas as conversas do usuário para o sidebar com contagem otimizada
        $conversasQuery = Conversa::participante($user->id)
            ->ativas()
            ->comContagemMensagensNaoLidas($user->id)
            ->with(['ultimaMensagem.remetente', 'participantesAtivos', 'turma', 'mensagens' => function($query) {
                $query->latest()->limit(1);
            }]);

        // Filtrar conversas por escola
        if ($user->isSuperAdmin() || $user->temCargo('Suporte')) {
            if (session('escola_atual')) {
                $conversasQuery->whereHas('participantes', function ($query) {
                    $query->where('escola_id', session('escola_atual'));
                });
            }
        } else {
            if ($user->escola_id) {
                $conversasQuery->whereHas('participantes', function ($query) use ($user) {
                    $query->where('escola_id', $user->escola_id);
                });
            }
        }

        $conversas = $conversasQuery->orderBy('ultima_mensagem_at', 'desc')->get();

        // Formatar dados para o frontend
        $conversasFormatadas = $conversas->map(function ($conversa) {
            $ultimaMensagem = $conversa->mensagens->first();
            return [
                'id' => $conversa->id ?? null,
                'titulo' => $conversa->titulo ?? 'Titulo',
                'updated_at' => $conversa->updated_at->format('H:i'),
                'participantes_count' => $conversa->participantes->count(),
                'ultima_mensagem' => $ultimaMensagem ? Str::limit($ultimaMensagem->conteudo, 50) : '',
                'iniciais' => substr($conversa->titulo, 0, 2),
                'mensagens_nao_lidas' => $conversa->mensagens_nao_lidas ?? 0
            ];
        });

        return response()->json([
            'conversas' => $conversasFormatadas
        ]);
    }
}