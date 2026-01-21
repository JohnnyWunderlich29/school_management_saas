<?php

namespace App\Actions\Planejamento;

use App\Models\Planejamento;
use App\Models\Notification;
use App\Models\User;
use App\Services\AlertService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UpdatePlanejamentoAction
{
    /**
     * @param Planejamento $planejamento
     * @param array $data
     * @return Planejamento
     * @throws ValidationException|\Exception
     */
    public function execute(Planejamento $planejamento, array $data): Planejamento
    {
        // 1. Validação
        $this->validate($data);

        // 2. Cálculo de datas
        $dataInicio = Carbon::parse($data['data_inicio']);
        $dataFim = $dataInicio->copy()->addDays($data['numero_dias'] - 1);

        // 3. Atualização
        $planejamento->update([
            'modalidade' => $data['modalidade'],
            'turno' => $data['turno'],
            'tipo_professor' => $data['tipo_professor'],
            'turma_id' => $data['turma_id'],
            'numero_dias' => $data['numero_dias'],
            'data_inicio' => $data['data_inicio'],
            'data_fim' => $dataFim,
            'titulo' => $data['titulo'] ?? null,
            'objetivo_geral' => $data['objetivo_geral'] ?? null,
            'objetivos_especificos' => $data['objetivos_especificos'] ?? null,
            'competencias_bncc' => $data['competencias_bncc'] ?? null,
            'habilidades_bncc' => $data['habilidades_bncc'] ?? null,
            'metodologia' => $data['metodologia'] ?? null,
            'recursos_didaticos' => $data['recursos_didaticos'] ?? null,
            'avaliacao' => $data['avaliacao'] ?? null,
            'status' => $data['status']
        ]);

        // 4. Notificações
        if ($data['status'] === 'finalizado') {
            $this->notificarCoordenadoresFinalizacao($planejamento);
        }

        return $planejamento;
    }

    private function validate(array $data)
    {
        $validator = Validator::make($data, [
            'modalidade' => 'required|in:' . implode(',', array_keys(Planejamento::getModalidadesOptions())),
            'turno' => 'required|in:' . implode(',', array_keys(Planejamento::getTurnosOptions())),
            'tipo_professor' => 'required|in:' . implode(',', array_keys(Planejamento::getTiposProfessorOptions())),
            'turma_id' => 'required|exists:turmas,id',
            'numero_dias' => 'required|integer|min:1|max:20',
            'data_inicio' => 'required|date',
            'titulo' => 'nullable|string|max:255',
            'objetivo_geral' => 'nullable|string',
            'objetivos_especificos' => 'nullable|array',
            'competencias_bncc' => 'nullable|array',
            'habilidades_bncc' => 'nullable|array',
            'metodologia' => 'nullable|string',
            'recursos_didaticos' => 'nullable|string',
            'avaliacao' => 'nullable|string',
            'status' => 'required|in:rascunho,finalizado,aprovado'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * Notifica coordenadores quando um professor finaliza um planejamento
     */
    private function notificarCoordenadoresFinalizacao(Planejamento $planejamento)
    {
        try {
            $turma = $planejamento->turma;
            if (!$turma || !$turma->sala) {
                return;
            }

            $sala = $turma->sala;
            $coordenadores = collect();

            if ($sala->coordenador_id) {
                $coordenador = User::find($sala->coordenador_id);
                if ($coordenador) {
                    $coordenadores->push($coordenador);
                }
            }

            $escolaId = $planejamento->escola_id ?: auth()->user()->escola_id;
            $coordenadoresGerais = User::whereHas('cargos', function ($query) use ($escolaId) {
                $query->where(function ($q) {
                    $q->where('tipo_cargo', 'coordenador')
                        ->orWhere('nome', 'like', '%coordenador%');
                })
                ->where('ativo', true)
                ->where(function ($subQuery) use ($escolaId) {
                    $subQuery->whereNull('escola_id')
                        ->orWhere('escola_id', $escolaId);
                });
            })->get();

            $coordenadores = $coordenadores->merge($coordenadoresGerais)->unique('id');

            foreach ($coordenadores as $coordenador) {
                Notification::createForUser(
                    $coordenador->id,
                    'info',
                    'Planejamento Finalizado',
                    'O professor ' . $planejamento->user->name . ' finalizou um planejamento para a turma ' . $turma->nome . ' (' . $sala->codigo . '). O planejamento está aguardando aprovação.',
                    [
                        'planejamento_id' => $planejamento->id,
                        'professor_id' => $planejamento->user_id,
                        'turma_id' => $turma->id,
                        'sala_id' => $sala->id
                    ],
                    route('planejamentos.show', $planejamento),
                    'Revisar Planejamento'
                );
            }
        } catch (\Exception $e) {
            Log::error('Erro ao notificar coordenadores sobre finalização (Action): ' . $e->getMessage());
        }
    }
}
