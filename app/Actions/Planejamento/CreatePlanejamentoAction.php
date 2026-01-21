<?php

namespace App\Actions\Planejamento;

use App\Models\Planejamento;
use App\Models\Turma;
use App\Models\Notification;
use App\Models\User;
use App\Services\AlertService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CreatePlanejamentoAction
{
    /**
     * @param array $data
     * @return Planejamento
     * @throws ValidationException|\Exception
     */
    public function execute(array $data): Planejamento
    {
        // 1. Normalização de dados
        $data = $this->normalizeData($data);

        // 2. Validação básica
        $this->validate($data);

        $user = Auth::user();

        // 3. Verificação de permissão
        if (!$user->isSuperAdmin() && !$user->isProfessor() && !$user->isCoordenador()) {
            AlertService::accessDenied('Você não tem permissão para criar planejamentos.');
            throw new \Exception('Acesso negado para criação de planejamento.', 403);
        }

        $dataInicio = Carbon::parse($data['data_inicio']);
        $dataFim = $dataInicio->copy()->addDays($data['numero_dias'] - 1);

        // 4. Verificação de data sequencial
        $ultimoPlanejamento = $this->getUltimoPlanejamento($data, $user);
        
        if ($ultimoPlanejamento && !$user->isAdminOrCoordinator()) {
            if ($dataInicio->lte($ultimoPlanejamento->data_fim)) {
                $proximaDataDisponivel = $ultimoPlanejamento->data_fim->copy()->addDay();
                $message = 'A data de início deve ser posterior ao último planejamento da disciplina (' . $ultimoPlanejamento->data_fim->format('d/m/Y') . '). Próxima data disponível: ' . $proximaDataDisponivel->format('d/m/Y');
                AlertService::error($message);
                throw ValidationException::withMessages(['data_inicio' => [$message]]);
            }
        }

        // 5. Verificação de sobreposição/conflitos
        $this->checkConflicts($data, $dataInicio, $dataFim, $user);

        // 6. Criar Planejamento
        $planejamento = $this->createPlanejamento($data, $user, $dataFim);

        // 7. Notificações
        if ($ultimoPlanejamento && $user->isAdminOrCoordinator() && $dataInicio->lt($ultimoPlanejamento->data_fim)) {
            $this->notifyProfessor($ultimoPlanejamento, $planejamento);
        }

        return $planejamento;
    }

    private function normalizeData(array $data): array
    {
        if (isset($data['modalidade_id']) && !isset($data['modalidade'])) {
            $data['modalidade'] = $data['modalidade_id'];
        }

        if (isset($data['turno_id']) && !isset($data['turno'])) {
            $data['turno'] = $data['turno_id'];
        }

        if (isset($data['grupo']) && !isset($data['grupo_id'])) {
            $data['grupo_id'] = $data['grupo'];
        }

        if (isset($data['grupo_educacional_id']) && !isset($data['grupo_id'])) {
            $data['grupo_id'] = $data['grupo_educacional_id'];
        }

        return $data;
    }

    private function validate(array $data)
    {
        $validator = Validator::make($data, [
            'modalidade' => 'required|exists:modalidades_ensino,id',
            'escola_id' => 'nullable|exists:escolas,id',
            'unidade_escolar' => 'nullable|string',
            'turno_id' => 'nullable|exists:turnos,id',
            'grupo_id' => 'required|exists:grupos,id',
            'turma_id' => 'required|exists:turmas,id',
            'disciplina_id' => 'required|exists:disciplinas,id',
            'data_inicio' => 'required|date',
            'numero_dias' => 'required|integer|min:1|max:20',
        ]);

        if ($validator->fails()) {
            Log::error('Erro de validação ao criar planejamento (Action)', ['errors' => $validator->errors()->toArray()]);
            AlertService::validationErrors($validator->errors());
            throw new ValidationException($validator);
        }
    }

    private function getUltimoPlanejamento(array $data, $user)
    {
        return Planejamento::where('turma_id', $data['turma_id'])
            ->where('user_id', $user->id)
            ->where('disciplina_id', $data['disciplina_id'])
            ->whereIn('status', ['rascunho', 'aberto', 'finalizado', 'aprovado'])
            ->orderBy('data_fim', 'desc')
            ->first();
    }

    private function checkConflicts(array $data, Carbon $dataInicio, Carbon $dataFim, $user)
    {
        $planejamentoExistente = Planejamento::where('turma_id', $data['turma_id'])
            ->where('disciplina_id', $data['disciplina_id'])
            ->where('user_id', $user->id)
            ->whereIn('status', ['rascunho', 'aberto', 'finalizado', 'aprovado'])
            ->when(isset($data['id']), function ($query) use ($data) {
                return $query->where('id', '!=', $data['id']);
            })
            ->where(function ($query) use ($dataInicio, $dataFim) {
                $query->where('data_inicio', '<=', $dataFim->format('Y-m-d'))
                      ->where('data_fim', '>=', $dataInicio->format('Y-m-d'));
            })
            ->first();

        if ($planejamentoExistente) {
            $message = 'Já existe um planejamento para esta disciplina e turma no período de ' .
                $planejamentoExistente->data_inicio->format('d/m/Y') . ' a ' .
                $planejamentoExistente->data_fim->format('d/m/Y') . '. ' .
                'Escolha um período diferente.';
            AlertService::error($message);
            throw ValidationException::withMessages(['data_inicio' => [$message]]);
        }
    }

    private function createPlanejamento(array $data, $user, Carbon $dataFim): Planejamento
    {
        $planejamento = new Planejamento();
        $planejamento->user_id = Auth::id();

        if ($user->isAdminOrCoordinator() && isset($data['escola_id']) && !empty($data['escola_id'])) {
            $planejamento->escola_id = $data['escola_id'];
        } else {
            $planejamento->escola_id = $user->escola_id;
        }

        $planejamento->unidade_escolar = $data['unidade_escolar'] ?? null;
        $planejamento->modalidade = $data['modalidade'];

        if (isset($data['turno']) && !empty($data['turno'])) {
            $planejamento->turno = $data['turno'];
        } elseif (isset($data['turno_id']) && !empty($data['turno_id'])) {
            $planejamento->turno = $data['turno_id'];
        }

        $planejamento->turma_id = $data['turma_id'];
        $planejamento->disciplina_id = $data['disciplina_id'];
        $planejamento->data_inicio = $data['data_inicio'];
        $planejamento->numero_dias = $data['numero_dias'];
        $planejamento->data_fim = $dataFim;
        $planejamento->status = 'rascunho';
        $planejamento->save();

        return $planejamento;
    }

    private function notifyProfessor(Planejamento $ultimoPlanejamento, Planejamento $novoPlanejamento)
    {
        $professor = User::find($ultimoPlanejamento->user_id);
        if ($professor) {
            Notification::createForUser(
                $professor->id,
                'warning',
                'Planejamento Reaberto',
                'Um coordenador/diretor reabriu o período de planejamentos para a turma ' . ($novoPlanejamento->turma->nome ?? 'N/A') . '. Você pode criar novos planejamentos para datas anteriores.',
                ['planejamento_id' => $novoPlanejamento->id, 'turma_id' => $novoPlanejamento->turma_id],
                route('planejamentos.index'),
                'Ver Planejamentos'
            );
        }
    }
}
