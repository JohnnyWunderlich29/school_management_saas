<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RecorrenciaDespesa;
use App\Models\Despesa;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GerarDespesasRecorrentes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'finance:gerar-despesas-recorrentes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $signature_description = 'Gera registros de despesas individuais a partir de templates recorrentes.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $hoje = Carbon::today();

        $recorrencias = RecorrenciaDespesa::where('ativo', true)
            ->whereDate('proxima_geracao', '<=', $hoje)
            ->get();

        if ($recorrencias->isEmpty()) {
            $this->info('Nenhuma despesa recorrente para gerar hoje.');
            return;
        }

        $this->info("Processando {$recorrencias->count()} recorrências...");

        foreach ($recorrencias as $recorrencia) {
            DB::beginTransaction();
            try {
                // Criar a despesa
                Despesa::create([
                    'escola_id' => $recorrencia->escola_id,
                    'recorrencia_id' => $recorrencia->id,
                    'descricao' => $recorrencia->descricao,
                    'categoria' => $recorrencia->categoria,
                    'valor' => $recorrencia->valor,
                    'data' => $recorrencia->proxima_geracao,
                    'status' => 'pendente',
                ]);

                // Calcular próxima data
                $proxima = $this->calcularProximaData($recorrencia->proxima_geracao, $recorrencia->frequencia);

                // Verificar data fim
                if ($recorrencia->data_fim && $proxima->gt($recorrencia->data_fim)) {
                    $recorrencia->ativo = false;
                }

                $recorrencia->proxima_geracao = $proxima;
                $recorrencia->save();

                DB::commit();
                $this->info("Gerada despesa para: {$recorrencia->descricao}");
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Erro ao gerar despesa recorrente', [
                    'recorrencia_id' => $recorrencia->id,
                    'error' => $e->getMessage()
                ]);
                $this->error("Erro na recorrência {$recorrencia->id}: {$e->getMessage()}");
            }
        }

        $this->info('Processamento concluído.');
    }

    private function calcularProximaData($atual, $frequencia)
    {
        $data = Carbon::parse($atual);

        return match ($frequencia) {
            'semanal' => $data->addWeek(),
            'mensal' => $data->addMonthNoOverflow()->startOfMonth(),
            'anual' => $data->addYear(),
            default => $data->addMonthNoOverflow()->startOfMonth(),
        };
    }
}
