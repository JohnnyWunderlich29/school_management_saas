<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Escola;
use App\Models\ModalidadeEnsino;
use App\Models\NivelEnsino;
use App\Models\EscolaModalidadeConfig;
use App\Models\EscolaNivelConfig;
use App\Models\TemplateBncc;
use App\Http\Middleware\EscolaContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ConfiguracaoEducacionalController extends Controller
{
    /**
     * Verificar se o usuário pode acessar uma escola específica
     */
    private function verificarAcessoEscola($escolaId)
    {
        $user = Auth::user();
        
        // Super admins e suporte podem acessar qualquer escola
        if ($user->isSuperAdmin() || $user->temCargo('Suporte')) {
            return true;
        }
        
        // Usuários normais só podem acessar sua própria escola
        if ($user->escola_id != $escolaId) {
            abort(403, 'Acesso negado. Você só pode visualizar configurações de sua própria escola.');
        }
        
        return true;
    }





    public function show(Escola $escola)
    {
        // Verificar acesso à escola
        $this->verificarAcessoEscola($escola->id);
        
        $escola->load([
            'modalidadeConfigs.modalidadeEnsino',
            'nivelConfigs.nivelEnsino'
        ]);

        // Obter IDs das modalidades já configuradas para esta escola
        $modalidadesConfiguradas = $escola->modalidadeConfigs()
            ->pluck('modalidade_ensino_id')
            ->toArray();

        // Separar modalidades padrão (BNCC) das personalizadas
        // Excluir modalidades BNCC que já estão configuradas para evitar duplicidade
        $modalidadesPadrao = ModalidadeEnsino::whereNull('escola_id')
            ->whereNotIn('id', $modalidadesConfiguradas)
            ->orderBy('nome')
            ->get();
            
        $modalidadesPersonalizadas = ModalidadeEnsino::where('escola_id', $escola->id)
            ->whereNotIn('id', $modalidadesConfiguradas)
            ->orderBy('nome')
            ->get();

        // Obter IDs dos níveis já configurados para esta escola
        $niveisConfigurados = $escola->nivelConfigs()
            ->pluck('nivel_ensino_id')
            ->toArray();
            
        // Filtrar níveis disponíveis excluindo os já configurados
        $niveisDisponiveis = NivelEnsino::whereNotIn('id', $niveisConfigurados)
            ->orderBy('nome')
            ->get();

        return view('admin.configuracao-educacional.show', compact(
            'escola',
            'modalidadesPadrao',
            'modalidadesPersonalizadas',
            'niveisDisponiveis'
        ));
    }

    public function storeModalidade(Request $request, Escola $escola)
    {
        // Verificar acesso à escola
        $this->verificarAcessoEscola($escola->id);
        
        $request->validate([
            'modalidade_ensino_id' => 'required|exists:modalidades_ensino,id',
            'ativo' => 'boolean',
            'capacidade_minima_turma' => 'nullable|integer|min:1',
            'capacidade_maxima_turma' => 'nullable|integer|min:1',
            'turno_matutino' => 'boolean',
            'turno_vespertino' => 'boolean',
            'turno_noturno' => 'boolean',
            'turno_integral' => 'boolean',
            'observacoes' => 'nullable|string|max:1000',
        ]);

        $config = EscolaModalidadeConfig::updateOrCreate(
            [
                'escola_id' => $escola->id,
                'modalidade_ensino_id' => $request->modalidade_ensino_id,
            ],
            [
                'ativo' => $request->boolean('ativo', true),
                'capacidade_minima_turma' => $request->capacidade_minima_turma ?: 1,
                'capacidade_maxima_turma' => $request->capacidade_maxima_turma ?: 30,
                'permite_turno_matutino' => $request->boolean('turno_matutino'),
                'permite_turno_vespertino' => $request->boolean('turno_vespertino'),
                'permite_turno_noturno' => $request->boolean('turno_noturno'),
                'permite_turno_integral' => $request->boolean('turno_integral'),
                'observacoes' => $request->observacoes,
                'data_ativacao' => $request->boolean('ativo', true) ? now() : null,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]
        );

        return redirect()
            ->route('admin.configuracao-educacional.show', $escola)
            ->with('success', 'Configuração de modalidade salva com sucesso!');
    }

    public function storeNivel(Request $request, Escola $escola)
    {
        // Verificar acesso à escola
        $this->verificarAcessoEscola($escola->id);
        
        $request->validate([
            'nivel_ensino_id' => 'required|exists:niveis_ensino,id',
            'ativo' => 'boolean',
            'capacidade_minima_turma' => 'nullable|integer|min:1',
            'capacidade_maxima_turma' => 'nullable|integer|min:1',
            'turno_matutino' => 'boolean',
            'turno_vespertino' => 'boolean',
            'turno_noturno' => 'boolean',
            'turno_integral' => 'boolean',
            'carga_horaria_semanal' => 'nullable|integer|min:1',
            'numero_aulas_dia' => 'nullable|integer|min:1',
            'duracao_aula_minutos' => 'nullable|integer|min:30',
            'idade_minima' => 'nullable|integer|min:0',
            'idade_maxima' => 'nullable|integer|min:0',
            'observacoes' => 'nullable|string|max:1000',
        ]);

        $config = EscolaNivelConfig::updateOrCreate(
            [
                'escola_id' => $escola->id,
                'nivel_ensino_id' => $request->nivel_ensino_id,
            ],
            [
                'ativo' => $request->boolean('ativo', true),
                'capacidade_minima_turma' => $request->capacidade_minima_turma ?: 1,
                'capacidade_maxima_turma' => $request->capacidade_maxima_turma ?: 30,
                'permite_turno_matutino' => $request->boolean('turno_matutino'),
                'permite_turno_vespertino' => $request->boolean('turno_vespertino'),
                'permite_turno_noturno' => $request->boolean('turno_noturno'),
                'permite_turno_integral' => $request->boolean('turno_integral'),
                'carga_horaria_semanal' => $request->carga_horaria_semanal,
                'numero_aulas_dia' => $request->numero_aulas_dia,
                'duracao_aula_minutos' => $request->duracao_aula_minutos,
                'idade_minima' => $request->idade_minima,
                'idade_maxima' => $request->idade_maxima,
                'observacoes' => $request->observacoes,
                'data_ativacao' => $request->boolean('ativo', true) ? now() : null,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]
        );

        return redirect()
            ->route('admin.configuracao-educacional.show', $escola)
            ->with('success', 'Configuração de nível salva com sucesso!');
    }

    public function destroyModalidade(Escola $escola, EscolaModalidadeConfig $config)
    {
        // Verificar acesso à escola
        $this->verificarAcessoEscola($escola->id);
        
        if ($config->escola_id !== $escola->id) {
            abort(404);
        }

        $config->delete();

        return redirect()
            ->route('admin.configuracao-educacional.show', $escola)
            ->with('success', 'Configuração de modalidade removida com sucesso!');
    }

    public function destroyNivel(Escola $escola, EscolaNivelConfig $config)
    {
        // Verificar acesso à escola
        $this->verificarAcessoEscola($escola->id);
        
        if ($config->escola_id !== $escola->id) {
            abort(404);
        }

        $config->delete();

        return redirect()
            ->route('admin.configuracao-educacional.show', $escola)
            ->with('success', 'Configuração de nível removida com sucesso!');
    }

    /**
     * Buscar templates BNCC disponíveis
     */
    public function getTemplatesBncc(Escola $escola)
    {
        // Verificar acesso à escola
        $this->verificarAcessoEscola($escola->id);

        try {
            $templates = TemplateBncc::getTemplatesOrganizados();
            
            // Verificar quais templates já estão configurados para esta escola
            $niveisConfigurados = EscolaNivelConfig::where('escola_id', $escola->id)
                ->with('nivelEnsino')
                ->get()
                ->pluck('nivelEnsino.codigo')
                ->filter()
                ->toArray();

            // Marcar templates já configurados
            foreach ($templates as $categoria => &$subcategorias) {
                foreach ($subcategorias as $subcategoria => &$items) {
                    foreach ($items as &$template) {
                        $template['ja_configurado'] = in_array($template['codigo'], $niveisConfigurados);
                    }
                }
            }

            return response()->json([
                'success' => true,
                'templates' => $templates
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar templates: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Aplicar templates BNCC selecionados
     */
    public function aplicarTemplatesBncc(Request $request, Escola $escola)
    {
        // Verificar acesso à escola
        $this->verificarAcessoEscola($escola->id);

        $request->validate([
            'templates' => 'required|array|min:1',
            'templates.*' => 'required|integer|exists:templates_bncc,id'
        ]);

        try {
            DB::beginTransaction();

            $templatesSelecionados = TemplateBncc::whereIn('id', $request->templates)
                ->where('ativo', true)
                ->get();

            $resultados = [
                'criados' => [],
                'ja_existentes' => [],
                'erros' => []
            ];

            foreach ($templatesSelecionados as $template) {
                try {
                    // Verificar se já existe um nível com este código
                    $nivelExistente = NivelEnsino::where('codigo', $template->codigo)->first();
                    
                    if (!$nivelExistente) {
                        // Criar novo nível de ensino baseado no template
                        $nivelExistente = NivelEnsino::create([
                            'nome' => $template->nome,
                            'codigo' => $template->codigo,
                            'descricao' => $template->descricao,
                            'categoria' => $template->categoria,
                            'subcategoria' => $template->subcategoria,
                            'ativo' => true,
                            'created_by' => Auth::id(),
                            'updated_by' => Auth::id(),
                        ]);
                    }

                    // Criar modalidade de ensino automaticamente baseada no template
                    if (!empty($template->modalidades_compativeis)) {
                        foreach ($template->modalidades_compativeis as $codigoModalidade) {
                            $this->criarModalidadeSeNaoExistir($escola, $codigoModalidade);
                        }
                    }

                    // Verificar se já existe configuração para esta escola
                    $configExistente = EscolaNivelConfig::where('escola_id', $escola->id)
                        ->where('nivel_ensino_id', $nivelExistente->id)
                        ->first();

                    if ($configExistente) {
                        $resultados['ja_existentes'][] = $template->nome;
                    } else {
                        // Criar configuração para a escola
                        EscolaNivelConfig::create([
                            'escola_id' => $escola->id,
                            'nivel_ensino_id' => $nivelExistente->id,
                            'ativo' => true,
                            'capacidade_minima_turma' => $template->capacidade_minima,
                            'capacidade_maxima_turma' => $template->capacidade_maxima,
                            'permite_turno_matutino' => $template->turno_matutino,
                            'permite_turno_vespertino' => $template->turno_vespertino,
                            'permite_turno_noturno' => $template->turno_noturno,
                            'permite_turno_integral' => $template->turno_integral,
                            'carga_horaria_semanal' => $template->carga_horaria_semanal,
                            'numero_aulas_dia' => $template->numero_aulas_dia,
                            'duracao_aula_minutos' => $template->duracao_aula_minutos,
                            'idade_minima' => $template->idade_minima,
                            'idade_maxima' => $template->idade_maxima,
                            'observacoes' => $template->observacoes,
                            'data_ativacao' => now(),
                            'created_by' => Auth::id(),
                            'updated_by' => Auth::id(),
                        ]);

                        $resultados['criados'][] = $template->nome;
                    }

                } catch (\Exception $e) {
                    $resultados['erros'][] = "Erro ao processar {$template->nome}: " . $e->getMessage();
                }
            }

            DB::commit();

            // Preparar mensagem de retorno
            $mensagens = [];
            
            if (!empty($resultados['criados'])) {
                $mensagens[] = 'Configurados com sucesso: ' . implode(', ', $resultados['criados']);
            }
            
            if (!empty($resultados['ja_existentes'])) {
                $mensagens[] = 'Já configurados: ' . implode(', ', $resultados['ja_existentes']);
            }
            
            if (!empty($resultados['erros'])) {
                $mensagens[] = 'Erros: ' . implode('; ', $resultados['erros']);
            }

            return response()->json([
                'success' => true,
                'message' => implode(' | ', $mensagens),
                'resultados' => $resultados
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao aplicar templates: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Criar modalidade de ensino se não existir para a escola
     * Agora usa modalidades BNCC padrão ao invés de criar duplicatas
     */
    private function criarModalidadeSeNaoExistir(Escola $escola, string $codigoModalidade)
    {
        // Mapear códigos para modalidades BNCC padrão
        $codigosModalidadesBncc = [
            'EI' => 'EI',
            'EF1' => 'EF1', 
            'EF2' => 'EF2',
            'EM' => 'EM',
            'EJA' => 'EJA'
        ];

        if (!isset($codigosModalidadesBncc[$codigoModalidade])) {
            return;
        }

        // Buscar a modalidade BNCC padrão (escola_id = NULL)
        $modalidadeBncc = ModalidadeEnsino::whereNull('escola_id')
            ->where('codigo', $codigosModalidadesBncc[$codigoModalidade])
            ->first();

        if (!$modalidadeBncc) {
            // Se não encontrar a modalidade BNCC padrão, criar uma personalizada como fallback
            $modalidades = [
                'EI' => [
                    'codigo' => 'EI',
                    'nome' => 'Educação Infantil',
                    'nivel' => 'Educação Básica',
                    'descricao' => 'Educação Infantil - Creche e Pré-escola'
                ],
                'EF1' => [
                    'codigo' => 'EF1',
                    'nome' => 'Ensino Fundamental - Anos Iniciais',
                    'nivel' => 'Educação Básica',
                    'descricao' => 'Ensino Fundamental do 1º ao 5º ano'
                ],
                'EF2' => [
                    'codigo' => 'EF2',
                    'nome' => 'Ensino Fundamental - Anos Finais',
                    'nivel' => 'Educação Básica',
                    'descricao' => 'Ensino Fundamental do 6º ao 9º ano'
                ],
                'EM' => [
                    'codigo' => 'EM',
                    'nome' => 'Ensino Médio',
                    'nivel' => 'Educação Básica',
                    'descricao' => 'Ensino Médio - 1ª, 2ª e 3ª séries'
                ],
                'EJA' => [
                    'codigo' => 'EJA',
                    'nome' => 'Educação de Jovens e Adultos',
                    'nivel' => 'Educação Básica',
                    'descricao' => 'Educação de Jovens e Adultos - EJA'
                ]
            ];

            $dadosModalidade = $modalidades[$codigoModalidade];
            $modalidadeBncc = ModalidadeEnsino::create([
                'escola_id' => $escola->id,
                'codigo' => $dadosModalidade['codigo'],
                'nome' => $dadosModalidade['nome'],
                'nivel' => $dadosModalidade['nivel'],
                'descricao' => $dadosModalidade['descricao'],
                'ativo' => true
            ]);
        }

        // Verificar se já existe configuração de modalidade para esta escola
        $configExistente = EscolaModalidadeConfig::where('escola_id', $escola->id)
            ->where('modalidade_ensino_id', $modalidadeBncc->id)
            ->first();

        if (!$configExistente) {
            // Criar configuração padrão para a modalidade BNCC
            EscolaModalidadeConfig::create([
                'escola_id' => $escola->id,
                'modalidade_ensino_id' => $modalidadeBncc->id,
                'ativo' => true,
                'capacidade_minima_turma' => 15,
                'capacidade_maxima_turma' => 35,
                'permite_turno_matutino' => true,
                'permite_turno_vespertino' => true,
                'permite_turno_noturno' => false,
                'permite_turno_integral' => false,
                'carga_horaria_semanal' => 25,
                'numero_aulas_dia' => 5,
                'duracao_aula_minutos' => 50,
                'data_ativacao' => now(),
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);
        }
    }

    /**
     * Obter disciplinas da escola para a aba de configuração
     */
    public function getDisciplinas(Escola $escola)
    {
        // Verificar acesso à escola
        $this->verificarAcessoEscola($escola->id);

        $disciplinas = \App\Models\Disciplina::with(['disciplinaNiveis.nivelEnsino'])
            ->orderBy('area_conhecimento')
            ->orderBy('ordem')
            ->orderBy('nome')
            ->get();

        // Buscar níveis de ensino da escola
        $niveis = \App\Models\NivelEnsino::whereHas('escolaNiveisConfig', function($query) use ($escola) {
            $query->where('escola_id', $escola->id)->where('ativo', true);
        })->orderBy('nome')->get();

        return response()->json([
            'success' => true,
            'disciplinas' => $disciplinas,
            'niveis' => $niveis
        ]);
    }

    /**
     * Atualizar dados básicos de uma disciplina
     */
    public function updateDisciplina(Request $request, Escola $escola)
    {
        // Verificar acesso à escola
        $this->verificarAcessoEscola($escola->id);

        $request->validate([
            'disciplina_id' => 'required|exists:disciplinas,id',
            'nome' => 'required|string|max:255',
            'codigo' => 'nullable|string|max:50',
            'cor_hex' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'area_conhecimento' => 'nullable|string|max:255',
            'ordem' => 'nullable|integer|min:0',
        ]);

        $disciplina = \App\Models\Disciplina::findOrFail($request->disciplina_id);

        // Disciplinas são padronizadas e não pertencem a uma escola específica

        $disciplina->update([
            'nome' => $request->nome,
            'codigo' => $request->codigo,
            'cor_hex' => $request->cor_hex,
            'area_conhecimento' => $request->area_conhecimento,
            'ordem' => $request->ordem ?? $disciplina->ordem,
            'updated_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Disciplina atualizada com sucesso!',
            'disciplina' => $disciplina
        ]);
    }

    /**
     * Atualizar carga horária de uma disciplina por nível
     */
    public function updateDisciplinaNivel(Request $request, Escola $escola)
    {
        // Verificar acesso à escola
        $this->verificarAcessoEscola($escola->id);

        $request->validate([
            'disciplina_id' => 'required|exists:disciplinas,id',
            'nivel_ensino_id' => 'required|exists:niveis_ensino,id',
            'carga_horaria_semanal' => 'required|numeric|min:0|max:40',
            'carga_horaria_anual' => 'nullable|numeric|min:0',
            'obrigatoria' => 'boolean',
            'ordem' => 'nullable|integer|min:0',
        ]);

        $disciplina = \App\Models\Disciplina::findOrFail($request->disciplina_id);

        // Disciplinas são padronizadas e não pertencem a uma escola específica

        // Buscar ou criar o relacionamento disciplina-nível
        $disciplinaNivel = \App\Models\DisciplinaNivelEnsino::updateOrCreate(
            [
                'disciplina_id' => $request->disciplina_id,
                'nivel_ensino_id' => $request->nivel_ensino_id,
            ],
            [
                'carga_horaria_semanal' => $request->carga_horaria_semanal,
                'carga_horaria_anual' => $request->carga_horaria_anual ?? ($request->carga_horaria_semanal * 40), // 40 semanas letivas
                'obrigatoria' => $request->boolean('obrigatoria'),
                'ordem' => $request->ordem ?? 0,
                'updated_by' => Auth::id(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Carga horária atualizada com sucesso!',
            'disciplinaNivel' => $disciplinaNivel->load('disciplina', 'nivelEnsino')
        ]);
    }
}
