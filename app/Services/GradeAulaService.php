<?php

namespace App\Services;

use App\Models\GradeAula;
use App\Models\Sala;
use App\Models\Funcionario;
use App\Models\TempoSlot;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class GradeAulaService
{
    /**
     * Verifica se uma sala está disponível em um horário específico
     */
    public function salaEstaDisponivel(int $salaId, int $tempoSlotId, string $diaSemana, ?Carbon $dataInicio = null, ?Carbon $dataFim = null, ?int $gradeAulaId = null): bool
    {
        $query = GradeAula::where('sala_id', $salaId)
            ->where('tempo_slot_id', $tempoSlotId)
            ->where('dia_semana', $diaSemana)
            ->ativas();

        // Aplicar filtro de escola
        $user = auth()->user();
        $escolaId = null;
        
        if ($user && ($user->isSuperAdmin() || $user->temCargo('Suporte'))) {
            $escolaId = session('escola_atual') ?: $user->escola_id;
        } else if ($user) {
            $escolaId = $user->escola_id;
        }
        
        // Filtrar por escola via relacionamento com turma
        if ($escolaId) {
            $query->whereHas('turma', function ($q) use ($escolaId) {
                $q->where('escola_id', $escolaId);
            });
        }

        // Se estiver editando uma grade existente, excluir ela da verificação
        if ($gradeAulaId) {
            $query->where('id', '!=', $gradeAulaId);
        }

        // Verificar conflitos de período
        if ($dataInicio && $dataFim) {
            $query->where(function ($q) use ($dataInicio, $dataFim) {
                // Verificar conflitos com aulas que têm período definido
                $q->where(function ($subQ) use ($dataInicio, $dataFim) {
                    $subQ->whereNotNull('data_inicio')
                         ->whereNotNull('data_fim')
                         ->where(function ($periodQ) use ($dataInicio, $dataFim) {
                             $periodQ->where(function ($innerQ) use ($dataInicio, $dataFim) {
                                 // Período da nova aula está dentro de um período existente
                                 $innerQ->where('data_inicio', '<=', $dataInicio)
                                        ->where('data_fim', '>=', $dataFim);
                             })->orWhere(function ($innerQ) use ($dataInicio, $dataFim) {
                                 // Período existente está dentro do período da nova aula
                                 $innerQ->where('data_inicio', '>=', $dataInicio)
                                        ->where('data_fim', '<=', $dataFim);
                             })->orWhere(function ($innerQ) use ($dataInicio, $dataFim) {
                                 // Há sobreposição de períodos
                                 $innerQ->where('data_inicio', '<', $dataFim)
                                        ->where('data_fim', '>', $dataInicio);
                             });
                         });
                })->orWhere(function ($subQ) {
                    // Verificar conflitos com aulas sem período definido (aulas permanentes)
                    $subQ->whereNull('data_inicio')
                         ->whereNull('data_fim');
                });
            });
        } else {
            // Se não há período definido para a nova aula, verificar conflitos com todas as aulas
            // (tanto as com período quanto as permanentes)
            // Não adicionar filtro adicional - verificar todos os conflitos básicos
        }

        return !$query->exists();
    }

    /**
     * Verifica se um professor está disponível em um horário específico
     */
    public function professorEstaDisponivel(int $funcionarioId, int $tempoSlotId, string $diaSemana, ?Carbon $dataInicio = null, ?Carbon $dataFim = null, ?int $gradeAulaId = null): bool
    {
        $query = GradeAula::where('funcionario_id', $funcionarioId)
            ->where('tempo_slot_id', $tempoSlotId)
            ->where('dia_semana', $diaSemana)
            ->ativas();

        // Aplicar filtro de escola
        $user = auth()->user();
        $escolaId = null;
        
        if ($user && ($user->isSuperAdmin() || $user->temCargo('Suporte'))) {
            $escolaId = session('escola_atual') ?: $user->escola_id;
        } else if ($user) {
            $escolaId = $user->escola_id;
        }
        
        // Filtrar por escola via relacionamento com turma
        if ($escolaId) {
            $query->whereHas('turma', function ($q) use ($escolaId) {
                $q->where('escola_id', $escolaId);
            });
        }

        // Se estiver editando uma grade existente, excluir ela da verificação
        if ($gradeAulaId) {
            $query->where('id', '!=', $gradeAulaId);
        }

        // Verificar conflitos de período
        if ($dataInicio && $dataFim) {
            $query->where(function ($q) use ($dataInicio, $dataFim) {
                // Verificar conflitos com aulas que têm período definido
                $q->where(function ($subQ) use ($dataInicio, $dataFim) {
                    $subQ->whereNotNull('data_inicio')
                         ->whereNotNull('data_fim')
                         ->where(function ($periodQ) use ($dataInicio, $dataFim) {
                             $periodQ->where(function ($innerQ) use ($dataInicio, $dataFim) {
                                 // Período da nova aula está dentro de um período existente
                                 $innerQ->where('data_inicio', '<=', $dataInicio)
                                        ->where('data_fim', '>=', $dataFim);
                             })->orWhere(function ($innerQ) use ($dataInicio, $dataFim) {
                                 // Período existente está dentro do período da nova aula
                                 $innerQ->where('data_inicio', '>=', $dataInicio)
                                        ->where('data_fim', '<=', $dataFim);
                             })->orWhere(function ($innerQ) use ($dataInicio, $dataFim) {
                                 // Há sobreposição de períodos
                                 $innerQ->where('data_inicio', '<', $dataFim)
                                        ->where('data_fim', '>', $dataInicio);
                             });
                         });
                })->orWhere(function ($subQ) {
                    // Verificar conflitos com aulas sem período definido (aulas permanentes)
                    $subQ->whereNull('data_inicio')
                         ->whereNull('data_fim');
                });
            });
        } else {
            // Se não há período definido para a nova aula, verificar conflitos com todas as aulas
            // (tanto as com período quanto as permanentes)
            // Não adicionar filtro adicional - verificar todos os conflitos básicos
        }

        return !$query->exists();
    }

    /**
     * Retorna todas as salas disponíveis para um horário específico
     */
    public function getSalasDisponiveis(int $tempoSlotId, string $diaSemana, ?Carbon $dataInicio = null, ?Carbon $dataFim = null, ?int $gradeAulaId = null): Collection
    {
        // Determinar escola_id para filtros
        $user = auth()->user();
        if ($user->isSuperAdmin() || $user->temCargo('Suporte')) {
            $escolaId = session('escola_atual') ?: $user->escola_id;
        } else {
            $escolaId = $user->escola_id;
        }

        $salasOcupadas = GradeAula::where('tempo_slot_id', $tempoSlotId)
            ->where('dia_semana', $diaSemana)
            ->ativas();

        // Se estiver editando uma grade existente, excluir ela da verificação
        if ($gradeAulaId) {
            $salasOcupadas->where('id', '!=', $gradeAulaId);
        }

        // Verificar conflitos de período
        if ($dataInicio && $dataFim) {
            $salasOcupadas->where(function ($q) use ($dataInicio, $dataFim) {
                $q->where(function ($subQ) use ($dataInicio, $dataFim) {
                    $subQ->where('data_inicio', '<=', $dataInicio)
                         ->where('data_fim', '>=', $dataFim);
                })->orWhere(function ($subQ) use ($dataInicio, $dataFim) {
                    $subQ->where('data_inicio', '>=', $dataInicio)
                         ->where('data_fim', '<=', $dataFim);
                })->orWhere(function ($subQ) use ($dataInicio, $dataFim) {
                    $subQ->where('data_inicio', '<', $dataFim)
                         ->where('data_fim', '>', $dataInicio);
                });
            });
        }

        $idsOcupadas = $salasOcupadas->pluck('sala_id');

        $query = Sala::whereNotIn('id', $idsOcupadas)
            ->where('ativo', true);

        // Filtrar por escola se definida
        if ($escolaId) {
            $query->where('escola_id', $escolaId);
        }

        return $query->orderBy('nome')->get();
    }

    /**
     * Retorna todos os professores disponíveis para um horário específico
     */
    public function getProfessoresDisponiveis(int $tempoSlotId, string $diaSemana, ?Carbon $dataInicio = null, ?Carbon $dataFim = null, ?int $gradeAulaId = null): Collection
    {
        // Determinar escola_id para filtros
        $user = auth()->user();
        if ($user->isSuperAdmin() || $user->temCargo('Suporte')) {
            $escolaId = session('escola_atual') ?: $user->escola_id;
        } else {
            $escolaId = $user->escola_id;
        }

        $professoresOcupados = GradeAula::where('tempo_slot_id', $tempoSlotId)
            ->where('dia_semana', $diaSemana)
            ->ativas();

        // Se estiver editando uma grade existente, excluir ela da verificação
        if ($gradeAulaId) {
            $professoresOcupados->where('id', '!=', $gradeAulaId);
        }

        // Verificar conflitos de período
        if ($dataInicio && $dataFim) {
            $professoresOcupados->where(function ($q) use ($dataInicio, $dataFim) {
                $q->where(function ($subQ) use ($dataInicio, $dataFim) {
                    $subQ->where('data_inicio', '<=', $dataInicio)
                         ->where('data_fim', '>=', $dataFim);
                })->orWhere(function ($subQ) use ($dataInicio, $dataFim) {
                    $subQ->where('data_inicio', '>=', $dataInicio)
                         ->where('data_fim', '<=', $dataFim);
                })->orWhere(function ($subQ) use ($dataInicio, $dataFim) {
                    $subQ->where('data_inicio', '<', $dataFim)
                         ->where('data_fim', '>', $dataInicio);
                });
            });
        }

        $idsOcupados = $professoresOcupados->pluck('funcionario_id');

        $query = Funcionario::whereNotIn('id', $idsOcupados)
            ->where('ativo', true)
            ->whereHas('user.cargos', function($query) {
                $query->where('tipo_cargo', 'professor')
                      ->orWhere('nome', 'like', '%professor%');
            }); // Apenas funcionários que são professores

        // Filtrar por escola se definida
        if ($escolaId) {
            $query->where('escola_id', $escolaId);
        }

        return $query->orderBy('nome')->get();
    }

    /**
     * Cria uma nova aula na grade
     */
    public function criarGradeAula(array $dados): GradeAula
    {
        // Determinar datas baseado no tipo de aula
        if ($dados['tipo_aula'] === 'anual') {
            // Para aulas anuais, definir período do ano letivo (fevereiro a dezembro)
            $anoAtual = Carbon::now()->year;
            $dataInicio = Carbon::create($anoAtual, 2, 1); // 1º de fevereiro
            $dataFim = Carbon::create($anoAtual, 12, 20);  // 20 de dezembro
            
            $dados['data_inicio'] = $dataInicio->format('Y-m-d');
            $dados['data_fim'] = $dataFim->format('Y-m-d');
            $dados['tipo_periodo'] = null; // Aulas anuais não têm tipo específico
        } else {
            // Para aulas com período específico, usar as datas fornecidas
            $dataInicio = isset($dados['data_inicio']) ? Carbon::parse($dados['data_inicio']) : null;
            $dataFim = isset($dados['data_fim']) ? Carbon::parse($dados['data_fim']) : null;
        }

        // Validar disponibilidade antes de criar
        if (!$this->salaEstaDisponivel(
            $dados['sala_id'],
            $dados['tempo_slot_id'],
            $dados['dia_semana'],
            $dataInicio,
            $dataFim
        )) {
            throw new \Exception('Sala não está disponível neste horário.');
        }

        if (!$this->professorEstaDisponivel(
            $dados['funcionario_id'],
            $dados['tempo_slot_id'],
            $dados['dia_semana'],
            $dataInicio,
            $dataFim
        )) {
            throw new \Exception('Professor não está disponível neste horário.');
        }

        try {
            return GradeAula::create($dados);
        } catch (\Illuminate\Database\QueryException $e) {
            // Capturar erro de violação de unicidade
            if (str_contains($e->getMessage(), 'uk_professor_horario')) {
                // Buscar informações do professor para mensagem mais clara
                $professor = Funcionario::find($dados['funcionario_id']);
                $tempoSlot = TempoSlot::find($dados['tempo_slot_id']);
                
                $professorNome = $professor ? $professor->nome : 'Professor';
                $horario = $tempoSlot ? $tempoSlot->hora_inicio . ' - ' . $tempoSlot->hora_fim : 'horário selecionado';
                $diaSemana = ucfirst($dados['dia_semana']);
                
                throw new \Exception("O professor {$professorNome} já possui uma aula agendada para {$diaSemana} no {$horario} no período selecionado.");
            }
            
            // Re-lançar outros erros de banco de dados
            throw $e;
        }
    }

    /**
     * Atualiza uma aula na grade
     */
    public function atualizarGradeAula(int $id, array $dados): GradeAula
    {
        $gradeAula = GradeAula::findOrFail($id);

        // Definir datas baseado no tipo de aula
        if (isset($dados['tipo_aula'])) {
            if ($dados['tipo_aula'] === 'anual') {
                // Para aulas anuais, definir período do ano letivo
                $anoAtual = Carbon::now()->year;
                $dataInicio = Carbon::create($anoAtual, 2, 1); // 1º de fevereiro
                $dataFim = Carbon::create($anoAtual, 12, 20);  // 20 de dezembro
                $dados['data_inicio'] = $dataInicio->format('Y-m-d');
                $dados['data_fim'] = $dataFim->format('Y-m-d');
                $dados['tipo_periodo'] = null; // Limpar tipo_periodo para aulas anuais
            } else {
                // Para aulas de período específico, usar as datas fornecidas
                $dataInicio = isset($dados['data_inicio']) ? Carbon::parse($dados['data_inicio']) : $gradeAula->data_inicio;
                $dataFim = isset($dados['data_fim']) ? Carbon::parse($dados['data_fim']) : $gradeAula->data_fim;
            }
        } else {
            $dataInicio = isset($dados['data_inicio']) ? Carbon::parse($dados['data_inicio']) : $gradeAula->data_inicio;
            $dataFim = isset($dados['data_fim']) ? Carbon::parse($dados['data_fim']) : $gradeAula->data_fim;
        }

        // Validar disponibilidade antes de atualizar
        if (isset($dados['sala_id']) && !$this->salaEstaDisponivel(
            $dados['sala_id'],
            $dados['tempo_slot_id'] ?? $gradeAula->tempo_slot_id,
            $dados['dia_semana'] ?? $gradeAula->dia_semana,
            $dataInicio,
            $dataFim,
            $id
        )) {
            throw new \Exception('Sala não está disponível neste horário.');
        }

        if (isset($dados['funcionario_id']) && !$this->professorEstaDisponivel(
            $dados['funcionario_id'],
            $dados['tempo_slot_id'] ?? $gradeAula->tempo_slot_id,
            $dados['dia_semana'] ?? $gradeAula->dia_semana,
            $dataInicio,
            $dataFim,
            $id
        )) {
            throw new \Exception('Professor não está disponível neste horário.');
        }

        $gradeAula->update($dados);
        return $gradeAula;
    }

    /**
     * Retorna a grade de uma turma específica
     */
    public function getGradeTurma(int $turmaId): Collection
    {
        return GradeAula::with(['disciplina', 'funcionario', 'sala', 'tempoSlot'])
            ->where('turma_id', $turmaId)
            ->ativas()
            ->orderBy('dia_semana')
            ->orderBy('tempo_slot_id')
            ->get();
    }

    /**
     * Retorna a ocupação de uma sala específica
     */
    public function getOcupacaoSala(int $salaId, ?Carbon $dataInicio = null, ?Carbon $dataFim = null): Collection
    {
        $query = GradeAula::with(['turma', 'disciplina', 'funcionario', 'tempoSlot'])
            ->where('sala_id', $salaId)
            ->ativas();

        if ($dataInicio && $dataFim) {
            $query->where(function ($q) use ($dataInicio, $dataFim) {
                $q->where('data_inicio', '<=', $dataFim)
                  ->where('data_fim', '>=', $dataInicio);
            });
        }

        return $query->orderBy('dia_semana')
            ->orderBy('tempo_slot_id')
            ->get();
    }

    /**
     * Obter sugestões de horários disponíveis para um professor e sala
     */
    public function obterSugestoesHorarios($funcionarioId, $salaId, $diaSemana, $dataInicio = null, $dataFim = null, $turmaId = null)
    {
        // Determinar escola_id para filtros
        $user = auth()->user();
        if ($user && ($user->isSuperAdmin() || $user->temCargo('Suporte'))) {
            $escolaId = session('escola_atual') ?: $user->escola_id;
        } else if ($user) {
            $escolaId = $user->escola_id;
        } else {
            $escolaId = null;
        }

        // Filtrar slots pelo turno da turma quando informado e pela escola
        $tempoSlotsQuery = TempoSlot::query()->orderBy('hora_inicio');
        if ($escolaId) {
            $tempoSlotsQuery->where('escola_id', $escolaId);
        }
        if ($turmaId) {
            $turma = \App\Models\Turma::find($turmaId);
            if ($turma && $turma->turno_id) {
                $tempoSlotsQuery->where('turno_id', $turma->turno_id);
            }
        }

        // Buscar e deduplicar por janela de tempo (hora_inicio + hora_fim)
        $tempoSlots = $tempoSlotsQuery->get()
            ->unique(function ($slot) {
                return $slot->hora_inicio . '|' . $slot->hora_fim;
            })
            ->values();
        $sugestoes = [];

        foreach ($tempoSlots as $slot) {
            $professorDisponivel = $this->professorEstaDisponivel(
                $funcionarioId,
                $slot->id,
                $diaSemana,
                $dataInicio,
                $dataFim
            );

            $salaDisponivel = $this->salaEstaDisponivel(
                $salaId,
                $slot->id,
                $diaSemana,
                $dataInicio,
                $dataFim
            );

            $status = 'disponivel';
            $motivo = null;

            if (!$professorDisponivel && !$salaDisponivel) {
                $status = 'indisponivel';
                $motivo = 'Professor e sala ocupados';
            } elseif (!$professorDisponivel) {
                $status = 'professor_ocupado';
                $motivo = 'Professor ocupado';
            } elseif (!$salaDisponivel) {
                $status = 'sala_ocupada';
                $motivo = 'Sala ocupada';
            }

            $sugestoes[] = [
                'tempo_slot_id' => $slot->id,
                'hora_inicio' => $slot->hora_inicio,
                'hora_fim' => $slot->hora_fim,
                'status' => $status,
                'motivo' => $motivo,
                'professor_disponivel' => $professorDisponivel,
                'sala_disponivel' => $salaDisponivel
            ];
        }

        return $sugestoes;
    }

    /**
     * Obter sugestões de salas disponíveis para um horário específico
     */
    public function obterSugestoesSalas($funcionarioId, $tempoSlotId, $diaSemana, $dataInicio = null, $dataFim = null)
    {
        // Determinar escola_id para filtros
        $user = auth()->user();
        if ($user->isSuperAdmin() || $user->temCargo('Suporte')) {
            $escolaId = session('escola_atual') ?: $user->escola_id;
        } else {
            $escolaId = $user->escola_id;
        }

        $query = Sala::where('ativo', true);
        
        // Filtrar por escola se definida
        if ($escolaId) {
            $query->where('escola_id', $escolaId);
        }
        
        $salas = $query->orderBy('nome')->get();
        $sugestoes = [];

        foreach ($salas as $sala) {
            $professorDisponivel = $this->professorEstaDisponivel(
                $funcionarioId,
                $tempoSlotId,
                $diaSemana,
                $dataInicio,
                $dataFim
            );

            $salaDisponivel = $this->salaEstaDisponivel(
                $sala->id,
                $tempoSlotId,
                $diaSemana,
                $dataInicio,
                $dataFim
            );

            $status = 'disponivel';
            $motivo = null;

            if (!$professorDisponivel && !$salaDisponivel) {
                $status = 'indisponivel';
                $motivo = 'Professor e sala ocupados';
            } elseif (!$professorDisponivel) {
                $status = 'professor_ocupado';
                $motivo = 'Professor ocupado';
            } elseif (!$salaDisponivel) {
                $status = 'sala_ocupada';
                $motivo = 'Sala ocupada';
            }

            $sugestoes[] = [
                'sala_id' => $sala->id,
                'sala_nome' => $sala->nome,
                'capacidade' => $sala->capacidade,
                'status' => $status,
                'motivo' => $motivo,
                'professor_disponivel' => $professorDisponivel,
                'sala_disponivel' => $salaDisponivel
            ];
        }

        return $sugestoes;
    }

    /**
     * Obter professores alternativos que podem assumir um horário/sala
     */
    public function obterProfessoresAlternativos($salaId, $tempoSlotId, $diaSemana, $disciplinaId, $dataInicio = null, $dataFim = null)
    {
        // Determinar escola_id para filtros
        $user = auth()->user();
        if ($user->isSuperAdmin() || $user->temCargo('Suporte')) {
            $escolaId = session('escola_atual') ?: $user->escola_id;
        } else {
            $escolaId = $user->escola_id;
        }

        // Buscar professores que podem lecionar a disciplina
        $query = Funcionario::whereHas('disciplinas', function ($query) use ($disciplinaId) {
            $query->where('disciplinas.id', $disciplinaId);
        })
        ->where('ativo', true)
        ->where('tipo', 'professor');

        // Filtrar por escola se definida
        if ($escolaId) {
            $query->where('escola_id', $escolaId);
        }

        $professores = $query->orderBy('nome')->get();

        $alternativas = [];

        foreach ($professores as $professor) {
            $professorDisponivel = $this->professorEstaDisponivel(
                $professor->id,
                $tempoSlotId,
                $diaSemana,
                $dataInicio,
                $dataFim
            );

            $salaDisponivel = $this->salaEstaDisponivel(
                $salaId,
                $tempoSlotId,
                $diaSemana,
                $dataInicio,
                $dataFim
            );

            $status = 'disponivel';
            $motivo = null;

            if (!$professorDisponivel && !$salaDisponivel) {
                $status = 'indisponivel';
                $motivo = 'Professor e sala ocupados';
            } elseif (!$professorDisponivel) {
                $status = 'professor_ocupado';
                $motivo = 'Professor ocupado';
            } elseif (!$salaDisponivel) {
                $status = 'sala_ocupada';
                $motivo = 'Sala ocupada';
            }

            // Calcular carga horária atual do professor
            $cargaAtual = GradeAula::where('funcionario_id', $professor->id)
                ->where('dia_semana', $diaSemana)
                ->ativas()
                ->count();

            $alternativas[] = [
                'funcionario_id' => $professor->id,
                'nome' => $professor->nome,
                'status' => $status,
                'motivo' => $motivo,
                'carga_atual' => $cargaAtual,
                'professor_disponivel' => $professorDisponivel,
                'sala_disponivel' => $salaDisponivel
            ];
        }

        // Ordenar por disponibilidade e depois por carga horária
        usort($alternativas, function ($a, $b) {
            if ($a['status'] === 'disponivel' && $b['status'] !== 'disponivel') {
                return -1;
            }
            if ($a['status'] !== 'disponivel' && $b['status'] === 'disponivel') {
                return 1;
            }
            return $a['carga_atual'] <=> $b['carga_atual'];
        });

        return $alternativas;
    }

    public function __construct()
    {
        //
    }
}

