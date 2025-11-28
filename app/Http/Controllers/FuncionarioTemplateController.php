<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FuncionarioTemplate;
use App\Models\Funcionario;
use App\Models\Escala;
use App\Models\GradeAula;
use App\Models\Historico;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class FuncionarioTemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, Funcionario $funcionario)
    {
        $query = FuncionarioTemplate::with('funcionario')
            ->where('funcionario_id', $funcionario->id);
        
        // Filtro por busca no nome do template
        if ($request->filled('search')) {
            $query->where('nome_template', 'like', '%' . $request->search . '%');
        }
        
        // Filtro por status ativo
        if ($request->filled('status')) {
            $query->where('ativo', $request->status);
        }
        
        $templates = $query->orderBy('created_at', 'desc')->paginate(15);
        $funcionarios = Funcionario::ativos()->orderBy('nome')->get();
        
        return view('funcionarios.templates.index', compact('templates', 'funcionarios', 'funcionario'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Funcionario $funcionario)
    {
        $funcionarios = Funcionario::ativos()->orderBy('nome')->get();
        $tiposEscala = ['Normal', 'Extra', 'Substituição', 'PL'];
        
        return view('funcionarios.templates.create', compact('funcionarios', 'tiposEscala', 'funcionario'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Funcionario $funcionario)
    {

        // Processar dados do formulário para o formato esperado
        $processedData = $this->processFormData($request->all(), $funcionario->id);
        
        // Validação customizada para horários
        $rules = [
            'funcionario_id' => 'required|exists:funcionarios,id',
            'nome_template' => 'required|string|max:255',
            'ativo' => 'boolean',
        ];
        
        $messages = [
            'nome_template.required' => 'O nome do template é obrigatório.',
            'nome_template.max' => 'O nome do template não pode ter mais de 255 caracteres.',
        ];
        
        // Adicionar validações dinâmicas para cada dia
        $dias = ['segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado', 'domingo'];
        foreach ($dias as $dia) {
            $rules["{$dia}_inicio"] = 'nullable|date_format:H:i';
            $rules["{$dia}_fim"] = 'nullable|date_format:H:i';
            $rules["{$dia}_tipo"] = 'nullable|in:Normal,Extra,Substituição,PL';
            
            // Adicionar validações para períodos adicionais
            $rules["{$dia}_manha2_inicio"] = 'nullable|date_format:H:i';
            $rules["{$dia}_manha2_fim"] = 'nullable|date_format:H:i';
            $rules["{$dia}_manha2_tipo"] = 'nullable|in:Normal,Extra,Substituição,PL';
            $rules["{$dia}_tarde_inicio"] = 'nullable|date_format:H:i';
            $rules["{$dia}_tarde_fim"] = 'nullable|date_format:H:i';
            $rules["{$dia}_tarde_tipo"] = 'nullable|in:Normal,Extra,Substituição,PL';
            $rules["{$dia}_tarde2_inicio"] = 'nullable|date_format:H:i';
            $rules["{$dia}_tarde2_fim"] = 'nullable|date_format:H:i';
            $rules["{$dia}_tarde2_tipo"] = 'nullable|in:Normal,Extra,Substituição,PL';
            
            $messages["{$dia}_inicio.date_format"] = "O formato da hora de início de {$dia} deve ser HH:MM.";
            $messages["{$dia}_fim.date_format"] = "O formato da hora de fim de {$dia} deve ser HH:MM.";
            $messages["{$dia}_tipo.in"] = "O tipo selecionado para {$dia} é inválido.";
            $messages["{$dia}_manha2_inicio.date_format"] = "O formato da hora de início manhã 2 de {$dia} deve ser HH:MM.";
            $messages["{$dia}_manha2_fim.date_format"] = "O formato da hora de fim manhã 2 de {$dia} deve ser HH:MM.";
            $messages["{$dia}_tarde2_inicio.date_format"] = "O formato da hora de início tarde 2 de {$dia} deve ser HH:MM.";
            $messages["{$dia}_tarde2_fim.date_format"] = "O formato da hora de fim tarde 2 de {$dia} deve ser HH:MM.";
        }
        
        $validator = Validator::make($processedData, $rules, $messages);

        // Validação adicional para horários
        $validator->after(function ($validator) use ($processedData) {
            $dias = ['segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado', 'domingo'];
            foreach ($dias as $dia) {
                $inicio = $processedData["{$dia}_inicio"] ?? null;
                $fim = $processedData["{$dia}_fim"] ?? null;
                
                if ($inicio && $fim && $fim <= $inicio) {
                    $validator->errors()->add("{$dia}_fim", "A hora de saída de {$dia} deve ser posterior à hora de entrada.");
                }
                
                // Validação para períodos adicionais
                $manha2_inicio = $processedData["{$dia}_manha2_inicio"] ?? null;
                $manha2_fim = $processedData["{$dia}_manha2_fim"] ?? null;
                
                if ($manha2_inicio && $manha2_fim && $manha2_fim <= $manha2_inicio) {
                    $validator->errors()->add("{$dia}_manha2_fim", "A hora de saída manhã 2 de {$dia} deve ser posterior à hora de entrada.");
                }
                
                $tarde2_inicio = $processedData["{$dia}_tarde2_inicio"] ?? null;
                $tarde2_fim = $processedData["{$dia}_tarde2_fim"] ?? null;
                
                if ($tarde2_inicio && $tarde2_fim && $tarde2_fim <= $tarde2_inicio) {
                    $validator->errors()->add("{$dia}_tarde2_fim", "A hora de saída tarde 2 de {$dia} deve ser posterior à hora de entrada.");
                }
            }
        });
        
        if ($validator->fails()) {
            \App\Services\AlertService::validationErrors($validator->errors()->toArray());
            return redirect()->back()->withInput();
        }
        
        // Continuar com a validação padrão
        $validator = Validator::make($processedData, [
            'funcionario_id' => 'required|exists:funcionarios,id',
            'nome_template' => 'required|string|max:255',
            'ativo' => 'boolean',
        ], [
            'nome_template.required' => 'O nome do template é obrigatório.',
            'nome_template.max' => 'O nome do template não pode ter mais de 255 caracteres.',
            '*.date_format' => 'O formato da hora deve ser HH:MM.',
            '*.after' => 'A hora de saída deve ser posterior à hora de entrada.',
            '*.in' => 'O tipo selecionado é inválido.',
        ]);
        
        if ($validator->fails()) {
            \Log::info('Validation errors:', $validator->errors()->toArray());
            \App\Services\AlertService::validationErrors($validator->errors());
            return redirect()->back()->withInput();
        }
        
        // Se ativo for true, desativar outros templates do mesmo funcionário
        if ($request->ativo) {
            FuncionarioTemplate::where('funcionario_id', $funcionario->id)
                ->update(['ativo' => false]);
        }
        
        $processedData['funcionario_id'] = $funcionario->id;
        $template = FuncionarioTemplate::create($processedData);
        
        // Registrar no histórico
        Historico::registrar(
            'criado',
            'FuncionarioTemplate',
            $template->id,
            null,
            $template->toArray(),
            "Template '{$template->nome_template}' criado para o funcionário {$funcionario->nome}"
        );
        
        return redirect()->route('funcionarios.templates.index', $funcionario)
            ->with('success', 'Template criado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Funcionario $funcionario, FuncionarioTemplate $template)
    {
        $template->load('funcionario');
        $diasConfigurados = $template->getDiasConfigurados();
        
        return view('funcionarios.templates.show', compact('template', 'diasConfigurados', 'funcionario'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Funcionario $funcionario, FuncionarioTemplate $template)
    {
        $funcionarios = Funcionario::ativos()->orderBy('nome')->get();
        $tiposEscala = ['Normal', 'Extra', 'Substituição', 'PL'];
        
        return view('funcionarios.templates.edit', compact('template', 'funcionarios', 'tiposEscala', 'funcionario'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Funcionario $funcionario, FuncionarioTemplate $template)
    {

        // Processar dados do formulário para o formato esperado
        $processedData = $this->processFormData($request->all(), $funcionario->id);
        
        $validator = Validator::make($processedData, [
            'funcionario_id' => 'required|exists:funcionarios,id',
            'nome_template' => 'required|string|max:255',
            'ativo' => 'boolean',
            // Validações para cada dia da semana
            'segunda_inicio' => 'nullable|date_format:H:i',
            'segunda_fim' => 'nullable|date_format:H:i|after_or_equal:segunda_inicio',
            'segunda_tipo' => 'nullable|in:Normal,Extra,Substituição,PL',
            'terca_inicio' => 'nullable|date_format:H:i',
            'terca_fim' => 'nullable|date_format:H:i|after_or_equal:terca_inicio',
            'terca_tipo' => 'nullable|in:Normal,Extra,Substituição,PL',
            'quarta_inicio' => 'nullable|date_format:H:i',
            'quarta_fim' => 'nullable|date_format:H:i|after_or_equal:quarta_inicio',
            'quarta_tipo' => 'nullable|in:Normal,Extra,Substituição,PL',
            'quinta_inicio' => 'nullable|date_format:H:i',
            'quinta_fim' => 'nullable|date_format:H:i|after_or_equal:quinta_inicio',
            'quinta_tipo' => 'nullable|in:Normal,Extra,Substituição,PL',
            'sexta_inicio' => 'nullable|date_format:H:i',
            'sexta_fim' => 'nullable|date_format:H:i|after_or_equal:sexta_inicio',
            'sexta_tipo' => 'nullable|in:Normal,Extra,Substituição,PL',
            'sabado_inicio' => 'nullable|date_format:H:i',
            'sabado_fim' => 'nullable|date_format:H:i|after_or_equal:sabado_inicio',
            'sabado_tipo' => 'nullable|in:Normal,Extra,Substituição,PL',
            'domingo_inicio' => 'nullable|date_format:H:i',
            'domingo_fim' => 'nullable|date_format:H:i|after_or_equal:domingo_inicio',
            'domingo_tipo' => 'nullable|in:Normal,Extra,Substituição,PL',
            // Validações para períodos adicionais
            'segunda_manha2_inicio' => 'nullable|date_format:H:i',
            'segunda_manha2_fim' => 'nullable|date_format:H:i|after_or_equal:segunda_manha2_inicio',
            'segunda_tarde2_inicio' => 'nullable|date_format:H:i',
            'segunda_tarde2_fim' => 'nullable|date_format:H:i|after_or_equal:segunda_tarde2_inicio',
            'terca_manha2_inicio' => 'nullable|date_format:H:i',
            'terca_manha2_fim' => 'nullable|date_format:H:i|after_or_equal:terca_manha2_inicio',
            'terca_tarde2_inicio' => 'nullable|date_format:H:i',
            'terca_tarde2_fim' => 'nullable|date_format:H:i|after_or_equal:terca_tarde2_inicio',
            'quarta_manha2_inicio' => 'nullable|date_format:H:i',
            'quarta_manha2_fim' => 'nullable|date_format:H:i|after_or_equal:quarta_manha2_inicio',
            'quarta_tarde2_inicio' => 'nullable|date_format:H:i',
            'quarta_tarde2_fim' => 'nullable|date_format:H:i|after_or_equal:quarta_tarde2_inicio',
            'quinta_manha2_inicio' => 'nullable|date_format:H:i',
            'quinta_manha2_fim' => 'nullable|date_format:H:i|after_or_equal:quinta_manha2_inicio',
            'quinta_tarde2_inicio' => 'nullable|date_format:H:i',
            'quinta_tarde2_fim' => 'nullable|date_format:H:i|after_or_equal:quinta_tarde2_inicio',
            'sexta_manha2_inicio' => 'nullable|date_format:H:i',
            'sexta_manha2_fim' => 'nullable|date_format:H:i|after_or_equal:sexta_manha2_inicio',
            'sexta_tarde2_inicio' => 'nullable|date_format:H:i',
            'sexta_tarde2_fim' => 'nullable|date_format:H:i|after_or_equal:sexta_tarde2_inicio',
            'sabado_manha2_inicio' => 'nullable|date_format:H:i',
            'sabado_manha2_fim' => 'nullable|date_format:H:i|after_or_equal:sabado_manha2_inicio',
            'sabado_tarde2_inicio' => 'nullable|date_format:H:i',
            'sabado_tarde2_fim' => 'nullable|date_format:H:i|after_or_equal:sabado_tarde2_inicio',
            'domingo_manha2_inicio' => 'nullable|date_format:H:i',
            'domingo_manha2_fim' => 'nullable|date_format:H:i|after_or_equal:domingo_manha2_inicio',
            'domingo_tarde2_inicio' => 'nullable|date_format:H:i',
            'domingo_tarde2_fim' => 'nullable|date_format:H:i|after_or_equal:domingo_tarde2_inicio',
        ], [
            'nome_template.required' => 'O nome do template é obrigatório.',
            'nome_template.max' => 'O nome do template não pode ter mais de 255 caracteres.',
            '*.date_format' => 'O formato da hora deve ser HH:MM.',
            '*.after' => 'A hora de saída deve ser posterior à hora de entrada.',
            '*.in' => 'O tipo selecionado é inválido.',
        ]);

        if ($validator->fails()) {
            \Log::info('Update validation errors:', $validator->errors()->toArray());
            \App\Services\AlertService::validationErrors($validator->errors()->toArray());
            return redirect()->back()->withInput();
        }
        
        // Se ativo for true, desativar outros templates do mesmo funcionário
        if ($request->ativo && !$template->ativo) {
            FuncionarioTemplate::where('funcionario_id', $funcionario->id)
                ->where('id', '!=', $template->id)
                ->update(['ativo' => false]);
        }
        
        // Capturar dados antigos antes da atualização
        $dadosAntigos = $template->toArray();
        
        $template->update($processedData);
        
        // Registrar no histórico
        Historico::registrar(
            'atualizado',
            'FuncionarioTemplate',
            $template->id,
            $dadosAntigos,
            $template->fresh()->toArray(),
            "Template '{$template->nome_template}' atualizado para o funcionário {$funcionario->nome}"
        );
        
        return redirect()->route('funcionarios.templates.index', $funcionario)
            ->with('success', 'Template atualizado com sucesso!');
    }

    /**
     * Processar dados do formulário para o formato esperado pelo banco
     */
    private function processFormData($data, $funcionarioId = null)
    {
        $processedData = [
            'nome_template' => $data['nome_template'] ?? null,
            'ativo' => isset($data['ativo']) ? true : false,
        ];
        
        // Adicionar funcionario_id se fornecido
        if ($funcionarioId) {
            $processedData['funcionario_id'] = $funcionarioId;
        }
        
        // Processar dados dos dias da semana
        $diasSemana = ['segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado', 'domingo'];
        
        foreach ($diasSemana as $dia) {
            if (isset($data['dias'][$dia]['ativo'])) {
                // Período Manhã - só processa se estiver ativo
                if (isset($data['dias'][$dia]['manha_ativo'])) {
                    $processedData[$dia . '_inicio'] = $data['dias'][$dia]['manha_inicio'] ?? null;
                    $processedData[$dia . '_fim'] = $data['dias'][$dia]['manha_fim'] ?? null;
                    $processedData[$dia . '_tipo'] = $data['dias'][$dia]['manha_tipo'] ?? 'Normal';
                } else {
                    $processedData[$dia . '_inicio'] = null;
                    $processedData[$dia . '_fim'] = null;
                    $processedData[$dia . '_tipo'] = null;
                }
                
                // Manhã Opcional - só processa se estiver ativo
                if (isset($data['dias'][$dia]['manha2_ativo'])) {
                    $processedData[$dia . '_manha2_inicio'] = $data['dias'][$dia]['manha2_inicio'] ?? null;
                    $processedData[$dia . '_manha2_fim'] = $data['dias'][$dia]['manha2_fim'] ?? null;
                    $processedData[$dia . '_manha2_tipo'] = $data['dias'][$dia]['manha2_tipo'] ?? 'Normal';
                } else {
                    $processedData[$dia . '_manha2_inicio'] = null;
                    $processedData[$dia . '_manha2_fim'] = null;
                    $processedData[$dia . '_manha2_tipo'] = null;
                }
                
                // Período Tarde - só processa se estiver ativo
                if (isset($data['dias'][$dia]['tarde_ativo'])) {
                    $processedData[$dia . '_tarde_inicio'] = $data['dias'][$dia]['tarde_inicio'] ?? null;
                    $processedData[$dia . '_tarde_fim'] = $data['dias'][$dia]['tarde_fim'] ?? null;
                    $processedData[$dia . '_tarde_tipo'] = $data['dias'][$dia]['tarde_tipo'] ?? 'Normal';
                } else {
                    $processedData[$dia . '_tarde_inicio'] = null;
                    $processedData[$dia . '_tarde_fim'] = null;
                    $processedData[$dia . '_tarde_tipo'] = null;
                }
                
                // Tarde Opcional - só processa se estiver ativo
                if (isset($data['dias'][$dia]['tarde2_ativo'])) {
                    $processedData[$dia . '_tarde2_inicio'] = $data['dias'][$dia]['tarde2_inicio'] ?? null;
                    $processedData[$dia . '_tarde2_fim'] = $data['dias'][$dia]['tarde2_fim'] ?? null;
                    $processedData[$dia . '_tarde2_tipo'] = $data['dias'][$dia]['tarde2_tipo'] ?? 'Normal';
                } else {
                    $processedData[$dia . '_tarde2_inicio'] = null;
                    $processedData[$dia . '_tarde2_fim'] = null;
                    $processedData[$dia . '_tarde2_tipo'] = null;
                }
            } else {
                // Dia não ativo - limpar todos os campos
                $processedData[$dia . '_inicio'] = null;
                $processedData[$dia . '_fim'] = null;
                $processedData[$dia . '_tipo'] = null;
                $processedData[$dia . '_manha2_inicio'] = null;
                $processedData[$dia . '_manha2_fim'] = null;
                $processedData[$dia . '_manha2_tipo'] = null;
                $processedData[$dia . '_tarde_inicio'] = null;
                $processedData[$dia . '_tarde_fim'] = null;
                $processedData[$dia . '_tarde_tipo'] = null;
                $processedData[$dia . '_tarde2_inicio'] = null;
                $processedData[$dia . '_tarde2_fim'] = null;
                $processedData[$dia . '_tarde2_tipo'] = null;
            }
        }

        return $processedData;
    }
    
    /**
     * Copiar template para múltiplos funcionários
     */
    public function copiar(Request $request, Funcionario $funcionario, FuncionarioTemplate $template)
    {
        $validator = Validator::make($request->all(), [
            'funcionarios_destino' => 'required|array|min:1',
            'funcionarios_destino.*' => 'exists:funcionarios,id',
            'nome_template' => 'required|string|max:255',
        ], [
            'funcionarios_destino.required' => 'Selecione pelo menos um funcionário de destino.',
            'funcionarios_destino.array' => 'Dados de funcionários inválidos.',
            'funcionarios_destino.min' => 'Selecione pelo menos um funcionário de destino.',
            'funcionarios_destino.*.exists' => 'Um ou mais funcionários selecionados não existem.',
            'nome_template.required' => 'O nome do template é obrigatório.',
            'nome_template.max' => 'O nome do template não pode ter mais de 255 caracteres.',
        ]);

        if ($validator->fails()) {
            \App\Services\AlertService::validationErrors($validator->errors());
            return redirect()->back()->withInput();
        }

        $funcionariosDestino = Funcionario::whereIn('id', $request->funcionarios_destino)->get();
        $templatesCriados = 0;
        $erros = [];
        
        // Copiar todos os dados do template original
        $dadosTemplate = $template->toArray();
        
        // Remover campos que não devem ser copiados
        unset($dadosTemplate['id'], $dadosTemplate['created_at'], $dadosTemplate['updated_at']);
        
        foreach ($funcionariosDestino as $funcionarioDestino) {
            // Verificar se já existe um template com o mesmo nome para o funcionário destino
            $templateExistente = FuncionarioTemplate::where('funcionario_id', $funcionarioDestino->id)
                ->where('nome_template', $request->nome_template)
                ->first();
                
            if ($templateExistente) {
                $erros[] = "Já existe um template com o nome '{$request->nome_template}' para {$funcionarioDestino->nome}";
                continue;
            }
            
            // Definir novos valores para este funcionário
            $dadosNovoTemplate = $dadosTemplate;
            $dadosNovoTemplate['funcionario_id'] = $funcionarioDestino->id;
            $dadosNovoTemplate['nome_template'] = $request->nome_template;
            $dadosNovoTemplate['ativo'] = true; // Novo template inicia ativo
            
            // Criar o novo template
            $novoTemplate = FuncionarioTemplate::create($dadosNovoTemplate);
            
            // Registrar no histórico
            Historico::registrar(
                'copiado',
                'FuncionarioTemplate',
                $novoTemplate->id,
                null,
                $novoTemplate->toArray(),
                "Template '{$template->nome_template}' copiado de {$funcionario->nome} para {$funcionarioDestino->nome} como '{$request->nome_template}'"
            );
            
            $templatesCriados++;
        }
        
        // Exibir mensagens de resultado
        if ($templatesCriados > 0) {
            $mensagem = "Template copiado com sucesso para {$templatesCriados} funcionário(s)!";
            if (count($erros) > 0) {
                $mensagem .= " Alguns funcionários foram ignorados devido a conflitos de nome.";
            }
            \App\Services\AlertService::success($mensagem);
        }
        
        if (count($erros) > 0 && $templatesCriados === 0) {
            \App\Services\AlertService::error('Nenhum template foi criado. ' . implode(' ', $erros));
        } elseif (count($erros) > 0) {
            \App\Services\AlertService::warning('Avisos: ' . implode(' ', $erros));
        }
        
        return redirect()->route('funcionarios.templates.index', $funcionario);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Funcionario $funcionario, FuncionarioTemplate $template)
    {
        // Capturar dados antes da exclusão
        $dadosTemplate = $template->toArray();
        $nomeTemplate = $template->nome_template;
        
        $template->delete();
        
        // Registrar no histórico
        Historico::registrar(
            'excluído',
            'FuncionarioTemplate',
            $dadosTemplate['id'],
            $dadosTemplate,
            null,
            "Template '{$nomeTemplate}' excluído do funcionário {$funcionario->nome}"
        );
        
        return redirect()->route('funcionarios.templates.index', $funcionario)
            ->with('success', 'Template excluído com sucesso!');
    }

    /**
     * Ativar/Desativar template
     */
    public function toggleAtivo(Funcionario $funcionario, FuncionarioTemplate $template)
    {
        // Capturar estado anterior
        $estadoAnterior = $template->ativo;
        $dadosAntigos = $template->toArray();
        
        if (!$template->ativo) {
            // Desativar outros templates do mesmo funcionário
            FuncionarioTemplate::where('funcionario_id', $funcionario->id)
                ->where('id', '!=', $template->id)
                ->update(['ativo' => false]);
        }
        
        $template->update(['ativo' => !$template->ativo]);
        
        $status = $template->ativo ? 'ativado' : 'desativado';
        
        // Registrar no histórico
        Historico::registrar(
            'atualizado',
            'FuncionarioTemplate',
            $template->id,
            $dadosAntigos,
            $template->fresh()->toArray(),
            "Template '{$template->nome_template}' {$status} para o funcionário {$funcionario->nome}"
        );
        
        return redirect()->back()
            ->with('success', "Template {$status} com sucesso!");
    }

    /**
     * Gerar escalas baseadas no template
     */
    public function gerarEscalas(Request $request, Funcionario $funcionario)
    {
        $validator = Validator::make($request->all(), [
            'funcionario_id' => 'required|exists:funcionarios,id',
            'template_id' => 'required|exists:funcionario_templates,id',
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date|after_or_equal:data_inicio',
            'sobrescrever_existentes' => 'boolean',
            'incluir_feriados' => 'boolean',
            'observacoes_padrao' => 'nullable|string|max:500',
            'notificar_funcionario' => 'boolean',
            'notificar_sistema' => 'boolean',
            'enviar_relatorio' => 'boolean',
            'preview' => 'boolean'
        ]);
        
        if ($validator->fails()) {
            if ($request->ajax() || $request->preview || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dados inválidos',
                    'errors' => $validator->errors()
                ]);
            }
            
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $funcionarioTemplate = FuncionarioTemplate::findOrFail($request->template_id);
        $dataInicio = Carbon::parse($request->data_inicio);
        $dataFim = Carbon::parse($request->data_fim);
        
        // Se é preview, apenas retorna as escalas que seriam geradas
        if ($request->preview) {
            $escalasPreview = $this->gerarEscalasArray($funcionarioTemplate, $dataInicio, $dataFim, $request);
            
            $previewHtml = view('funcionarios.templates.preview-escalas', [
                'escalas' => $escalasPreview,
                'funcionario' => $funcionarioTemplate->funcionario,
                'template' => $funcionarioTemplate,
                'dataInicio' => $dataInicio,
                'dataFim' => $dataFim
            ])->render();
            
            return response()->json([
                'success' => true,
                'preview' => $previewHtml,
                'total_escalas' => count($escalasPreview)
            ]);
        }
        
        // Verificar se já existem escalas no período
        $escalasExistentes = Escala::where('funcionario_id', $funcionarioTemplate->funcionario_id)
            ->whereBetween('data', [$dataInicio, $dataFim])
            ->count();
        
        if ($escalasExistentes > 0 && !$request->sobrescrever_existentes) {
            return redirect()->back()
                ->with('warning', 'Já existem escalas para este funcionário no período selecionado. Marque a opção "Sobrescrever escalas existentes" se deseja substituí-las.');
        }
        
        DB::beginTransaction();
        
        try {
            // Se sobrescrever, deletar escalas existentes
            if ($request->sobrescrever_existentes) {
                Escala::where('funcionario_id', $funcionarioTemplate->funcionario_id)
                    ->whereBetween('data', [$dataInicio, $dataFim])
                    ->delete();
            }
            
            // Gerar novas escalas
            $escalas = $this->gerarEscalasArray($funcionarioTemplate, $dataInicio, $dataFim, $request);
            
            if (empty($escalas)) {
                DB::rollBack();
                return redirect()->back()
                    ->with('warning', 'Nenhuma escala foi gerada. Verifique se o template possui dias configurados.');
            }
            
            // Inserir escalas no banco
            $escalasIds = [];
            foreach ($escalas as $escala) {
                $novaEscala = Escala::create($escala);
                $escalasIds[] = $novaEscala->id;
            }
            
            // Registrar no histórico a geração de escalas baseada no template
            Historico::registrar(
                'geração_escalas',
                'FuncionarioTemplate',
                $funcionarioTemplate->id,
                null,
                [
                    'template_id' => $funcionarioTemplate->id,
                    'template_nome' => $funcionarioTemplate->nome_template,
                    'funcionario_id' => $funcionarioTemplate->funcionario_id,
                    'funcionario_nome' => $funcionarioTemplate->funcionario->nome,
                    'data_inicio' => $dataInicio->format('Y-m-d'),
                    'data_fim' => $dataFim->format('Y-m-d'),
                    'total_escalas' => count($escalas),
                    'escalas_ids' => $escalasIds,
                    'sobrescrever_existentes' => $request->sobrescrever_existentes ?? false,
                    'incluir_feriados' => $request->incluir_feriados ?? false,
                    'observacoes_padrao' => $request->observacoes_padrao
                ],
                "Geradas " . count($escalas) . " escalas baseadas no template '{$funcionarioTemplate->nome_template}' para o funcionário {$funcionarioTemplate->funcionario->nome} no período de {$dataInicio->format('d/m/Y')} a {$dataFim->format('d/m/Y')}"
            );
            
            DB::commit();
            
            $totalEscalas = count($escalas);
            
            // Enviar notificações se solicitado
            if ($request->notificar_funcionario) {
                // TODO: Implementar notificação por email
            }
            
            if ($request->notificar_sistema) {
                \App\Models\Notification::createForUser(
                    $funcionarioTemplate->funcionario->user_id,
                    'info',
                    'Escala Atualizada',
                    "Sua escala foi atualizada para o período de {$dataInicio->format('d/m/Y')} a {$dataFim->format('d/m/Y')}. Total de {$totalEscalas} escalas geradas.",
                    [
                        'funcionario_id' => $funcionarioTemplate->funcionario_id,
                        'template_id' => $funcionarioTemplate->id,
                        'data_inicio' => $dataInicio->format('Y-m-d'),
                        'data_fim' => $dataFim->format('Y-m-d'),
                        'total_escalas' => $totalEscalas
                    ],
                    route('funcionarios.escalas.index', $funcionarioTemplate->funcionario),
                    'Ver Escalas'
                );
            }
            
            if ($request->enviar_relatorio) {
                // TODO: Implementar envio de relatório
            }
            
            return redirect()->route('funcionarios.templates.index', $funcionarioTemplate->funcionario)
                ->with('success', "{$totalEscalas} escalas geradas com sucesso para o período de {$dataInicio->format('d/m/Y')} a {$dataFim->format('d/m/Y')}!");
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->with('error', 'Erro ao gerar escalas: ' . $e->getMessage());
        }
    }

    /**
     * Formulário para gerar escalas
     */
    public function formGerarEscalas(Request $request, Funcionario $funcionario)
    {
        $funcionarios = Funcionario::with(['templates' => function($query) {
            $query->where('ativo', true);
        }])->get();
        
        // Adicionar funcionario_id ao request para pré-seleção
        $request->merge(['funcionario_id' => $funcionario->id]);
        
        return view('funcionarios.templates.gerar-escalas', compact('funcionarios', 'funcionario'));
    }
    
    /**
     * Gera array de escalas baseado no template
     */
    private function gerarEscalasArray(FuncionarioTemplate $template, Carbon $dataInicio, Carbon $dataFim, Request $request)
    {
        $escalas = [];
        $dataAtual = $dataInicio->copy();
        
        // Mapear dias da semana
        $diasSemana = [
            0 => 'domingo',
            1 => 'segunda', 
            2 => 'terca',
            3 => 'quarta',
            4 => 'quinta',
            5 => 'sexta',
            6 => 'sabado'
        ];
        
        // Períodos disponíveis com mapeamento correto
        $periodos = [
            ['sufixo' => '', 'nome' => 'manha'],
            ['sufixo' => '_manha2', 'nome' => 'manha2'],
            ['sufixo' => '_tarde', 'nome' => 'tarde'],
            ['sufixo' => '_tarde2', 'nome' => 'tarde2']
        ];
        
        while ($dataAtual->lte($dataFim)) {
            $diaSemana = $diasSemana[$dataAtual->dayOfWeek];
            
            // Verificar se deve incluir feriados
            if (!$request->incluir_feriados && $this->isFeriado($dataAtual)) {
                $dataAtual->addDay();
                continue;
            }
            
            // Verificar cada período do dia
            foreach ($periodos as $periodo) {
                $horaInicio = $template->{$diaSemana . $periodo['sufixo'] . '_inicio'};
                $horaFim = $template->{$diaSemana . $periodo['sufixo'] . '_fim'};
                $tipo = $template->{$diaSemana . $periodo['sufixo'] . '_tipo'};
                
                if ($horaInicio && $horaFim) {
                    $escalas[] = [
                        'funcionario_id' => $template->funcionario_id,
                        'data' => $dataAtual->format('Y-m-d'),
                        'hora_inicio' => $horaInicio,
                        'hora_fim' => $horaFim,
                        'tipo_escala' => $this->mapTipoEscala($tipo),
                        'tipo_atividade' => $this->mapTipoAtividade($tipo ?: 'presencial'),
                        'status' => 'Agendada',
                        'observacoes' => $request->observacoes_padrao,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
            
            $dataAtual->addDay();
        }
        
        return $escalas;
    }
    
    /**
     * Verifica se a data é feriado
     */
    private function isFeriado(Carbon $data)
    {
        // Lista básica de feriados nacionais fixos
        $feriadosFixos = [
            '01-01', // Ano Novo
            '04-21', // Tiradentes
            '05-01', // Dia do Trabalhador
            '09-07', // Independência do Brasil
            '10-12', // Nossa Senhora Aparecida
            '11-02', // Finados
            '11-15', // Proclamação da República
            '12-25', // Natal
        ];
        
        $dataFormatada = $data->format('m-d');
        
        return in_array($dataFormatada, $feriadosFixos);
    }
    
    /**
     * Mapeia tipo do template para tipo de escala
     */
    private function mapTipoEscala($tipoTemplate)
    {
        $mapeamento = [
            'PL' => 'Normal',
            'presencial' => 'Normal',
            'remoto' => 'Normal',
            'hibrido' => 'Normal',
            'extra' => 'Extra',
            'substituicao' => 'Substituição'
        ];
        
        return $mapeamento[$tipoTemplate] ?? 'Normal';
    }

    /**
     * Mapeia tipo do template para tipo de atividade da escala
     */
    private function mapTipoAtividade($tipoTemplate)
    {
        $mapeamento = [
            'presencial' => 'em_sala',
            'remoto' => 'pl',
            'hibrido' => 'em_sala',
            'PL' => 'pl'
        ];
        
        return $mapeamento[$tipoTemplate] ?? 'em_sala';
    }

    /**
     * Exibe a visualização em calendário das escalas
     */
    public function calendarioEscalas(Request $request)
    {
        // Determinar escola_id baseado no usuário e sessão (seguindo padrão do sistema)
        $escolaId = null;
        if (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte')) {
            $escolaId = session('escola_atual') ?: auth()->user()->escola_id;
        } else {
            $escolaId = auth()->user()->escola_id;
        }

        $query = Funcionario::ativos()
            ->when($escolaId, function($query) use ($escolaId) {
                $query->where('escola_id', $escolaId);
            });

        if (auth()->user()->isProfessor()) {
            $meuFuncionarioId = auth()->user()->funcionario->id ?? null;
            if ($meuFuncionarioId) {
                $query->where('id', $meuFuncionarioId);
            } else {
                $query->where('id', 0);
            }
        }

        $funcionarios = $query->orderBy('nome')->get();

        $funcionarioSelecionado = null;
        $escalas = collect();

        return view('funcionarios.templates.calendario-escalas', compact(
            'funcionarios',
            'funcionarioSelecionado',
            'escalas'
        ));
    }

    /**
     * Retorna as escalas de um funcionário via API
     */
    public function getEscalasFuncionario(Request $request, Funcionario $funcionario)
    {
        // Determinar escola_id baseado no usuário e sessão (seguindo padrão do sistema)
        $escolaId = null;
        if (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte')) {
            $escolaId = session('escola_atual') ?: auth()->user()->escola_id;
        } else {
            $escolaId = auth()->user()->escola_id;
        }

        // Verificar se o funcionário pertence à escola do usuário
        if ($escolaId && $funcionario->escola_id !== $escolaId) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }

        $user = auth()->user();
        if ($user && $user->isProfessor()) {
            $meuFuncionarioId = $user->funcionario->id ?? null;
            if (!$meuFuncionarioId || $meuFuncionarioId !== $funcionario->id) {
                return response()->json(['error' => 'Acesso negado'], 403);
            }
        }

        $mes = $request->get('mes', now()->format('Y-m'));
        $dataInicio = Carbon::createFromFormat('Y-m', $mes)->startOfMonth();
        $dataFim = $dataInicio->copy()->endOfMonth();

        $grade = GradeAula::with(['turma', 'disciplina', 'sala', 'tempoSlot'])
            ->where('funcionario_id', $funcionario->id)
            ->whereHas('turma', function($query) use ($escolaId) {
                if ($escolaId) {
                    $query->where('escola_id', $escolaId);
                }
            })
            ->where('ativo', true)
            ->where(function($q) use ($dataInicio, $dataFim) {
                $q->whereNull('data_inicio')
                  ->orWhere(function($sub) use ($dataInicio, $dataFim) {
                      $sub->where('data_inicio', '<=', $dataFim)
                          ->where('data_fim', '>=', $dataInicio);
                  });
            })
            ->get();
        $normalizarDiaSemana = function ($s) {
            if ($s === null) return null;
            $s = mb_strtolower($s, 'UTF-8');
            $s = iconv('UTF-8', 'ASCII//TRANSLIT', $s);
            $s = preg_replace('/[^a-z]/', '', $s);
            $map = [
                'domingo' => 0, 'dom' => 0,
                'segunda' => 1, 'seg' => 1,
                'terca' => 2, 'ter' => 2,
                'quarta' => 3, 'qua' => 3,
                'quinta' => 4, 'qui' => 4,
                'sexta' => 5, 'sex' => 5,
                'sabado' => 6, 'sab' => 6,
            ];
            return $map[$s] ?? null;
        };

        $items = [];
        foreach ($grade as $g) {
            $dow = $normalizarDiaSemana($g->dia_semana);
            if ($dow === null) {
                continue;
            }
            $inicioGlobal = $dataInicio->copy();
            $fimGlobal = $dataFim->copy();
            $inicioReg = $g->data_inicio ? $g->data_inicio->copy() : null;
            $fimReg = $g->data_fim ? $g->data_fim->copy() : null;
            $inicio = $inicioReg && $inicioReg->gt($inicioGlobal) ? $inicioReg : $inicioGlobal;
            $fim = $fimReg && $fimReg->lt($fimGlobal) ? $fimReg : $fimGlobal;
            $cursor = $inicio->copy();
            while ($cursor->lte($fim)) {
                if ($cursor->dayOfWeek === $dow) {
                    $horaInicio = optional($g->tempoSlot)->hora_inicio;
                    $horaFim = optional($g->tempoSlot)->hora_fim;
                    if ($horaInicio && $horaFim) {
                        $items[] = [
                            'id' => $g->id,
                            'data' => $cursor->format('Y-m-d'),
                            'dia_semana' => $cursor->dayOfWeek,
                            'horario_inicio' => $horaInicio,
                            'horario_fim' => $horaFim,
                            'tipo_atividade' => 'em_sala',
                            'tipo_escala' => 'Aula',
                            'observacoes' => $g->observacoes,
                            'data_formatada' => $cursor->format('d/m/Y'),
                            'dia_semana_nome' => $cursor->locale('pt_BR')->dayName,
                            'periodo' => $this->determinarPeriodo($horaInicio),
                            'sala_nome' => optional($g->sala)->nome,
                            'turma_nome' => optional($g->turma)->nome,
                            'disciplina_nome' => optional($g->disciplina)->nome,
                        ];
                    }
                }
                $cursor->addDay();
            }
        }

        $escalas = collect($items)->groupBy('data');
        
        return response()->json([
            'success' => true,
            'escalas' => $escalas,
            'funcionario' => [
                'id' => $funcionario->id,
                'nome' => $funcionario->nome
            ],
            'periodo' => [
                'inicio' => $dataInicio->format('Y-m-d'),
                'fim' => $dataFim->format('Y-m-d'),
                'mes_ano' => $dataInicio->format('m/Y')
            ]
        ]);
    }

    /**
     * Determina o período baseado no horário de início
     */
    private function determinarPeriodo($horaInicio)
    {
        if (!$horaInicio) {
            return 'manha';
        }

        $hora = (int) substr($horaInicio, 0, 2);

        if ($hora >= 6 && $hora < 12) {
            return 'manha';
        } elseif ($hora >= 12 && $hora < 18) {
            return 'tarde';
        } elseif ($hora >= 18 && $hora < 24) {
            return 'noite';
        } else {
            return 'madrugada';
        }
    }
}
