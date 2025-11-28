<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class FuncionarioTemplate extends Model
{
    protected $fillable = [
        'funcionario_id',
        'nome_template',
        'ativo',
        'segunda_inicio',
        'segunda_fim',
        'segunda_tipo',
        'segunda_manha2_inicio',
        'segunda_manha2_fim',
        'segunda_manha2_tipo',
        'segunda_tarde_inicio',
        'segunda_tarde_fim',
        'segunda_tarde_tipo',
        'segunda_tarde2_inicio',
        'segunda_tarde2_fim',
        'segunda_tarde2_tipo',
        'terca_inicio',
        'terca_fim',
        'terca_tipo',
        'terca_manha2_inicio',
        'terca_manha2_fim',
        'terca_manha2_tipo',
        'terca_tarde_inicio',
        'terca_tarde_fim',
        'terca_tarde_tipo',
        'terca_tarde2_inicio',
        'terca_tarde2_fim',
        'terca_tarde2_tipo',
        'quarta_inicio',
        'quarta_fim',
        'quarta_tipo',
        'quarta_manha2_inicio',
        'quarta_manha2_fim',
        'quarta_manha2_tipo',
        'quarta_tarde_inicio',
        'quarta_tarde_fim',
        'quarta_tarde_tipo',
        'quarta_tarde2_inicio',
        'quarta_tarde2_fim',
        'quarta_tarde2_tipo',
        'quinta_inicio',
        'quinta_fim',
        'quinta_tipo',
        'quinta_manha2_inicio',
        'quinta_manha2_fim',
        'quinta_manha2_tipo',
        'quinta_tarde_inicio',
        'quinta_tarde_fim',
        'quinta_tarde_tipo',
        'quinta_tarde2_inicio',
        'quinta_tarde2_fim',
        'quinta_tarde2_tipo',
        'sexta_inicio',
        'sexta_fim',
        'sexta_tipo',
        'sexta_manha2_inicio',
        'sexta_manha2_fim',
        'sexta_manha2_tipo',
        'sexta_tarde_inicio',
        'sexta_tarde_fim',
        'sexta_tarde_tipo',
        'sexta_tarde2_inicio',
        'sexta_tarde2_fim',
        'sexta_tarde2_tipo',
        'sabado_inicio',
        'sabado_fim',
        'sabado_tipo',
        'sabado_manha2_inicio',
        'sabado_manha2_fim',
        'sabado_manha2_tipo',
        'sabado_tarde_inicio',
        'sabado_tarde_fim',
        'sabado_tarde_tipo',
        'sabado_tarde2_inicio',
        'sabado_tarde2_fim',
        'sabado_tarde2_tipo',
        'domingo_inicio',
        'domingo_fim',
        'domingo_tipo',
        'domingo_manha2_inicio',
        'domingo_manha2_fim',
        'domingo_manha2_tipo',
        'domingo_tarde_inicio',
        'domingo_tarde_fim',
        'domingo_tarde_tipo',
        'domingo_tarde2_inicio',
        'domingo_tarde2_fim',
        'domingo_tarde2_tipo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'segunda_inicio' => 'datetime:H:i',
        'segunda_fim' => 'datetime:H:i',
        'segunda_manha2_inicio' => 'datetime:H:i',
        'segunda_manha2_fim' => 'datetime:H:i',
        'segunda_tarde_inicio' => 'datetime:H:i',
        'segunda_tarde_fim' => 'datetime:H:i',
        'segunda_tarde2_inicio' => 'datetime:H:i',
        'segunda_tarde2_fim' => 'datetime:H:i',
        'terca_inicio' => 'datetime:H:i',
        'terca_fim' => 'datetime:H:i',
        'terca_manha2_inicio' => 'datetime:H:i',
        'terca_manha2_fim' => 'datetime:H:i',
        'terca_tarde_inicio' => 'datetime:H:i',
        'terca_tarde_fim' => 'datetime:H:i',
        'terca_tarde2_inicio' => 'datetime:H:i',
        'terca_tarde2_fim' => 'datetime:H:i',
        'quarta_inicio' => 'datetime:H:i',
        'quarta_fim' => 'datetime:H:i',
        'quarta_manha2_inicio' => 'datetime:H:i',
        'quarta_manha2_fim' => 'datetime:H:i',
        'quarta_tarde_inicio' => 'datetime:H:i',
        'quarta_tarde_fim' => 'datetime:H:i',
        'quarta_tarde2_inicio' => 'datetime:H:i',
        'quarta_tarde2_fim' => 'datetime:H:i',
        'quinta_inicio' => 'datetime:H:i',
        'quinta_fim' => 'datetime:H:i',
        'quinta_manha2_inicio' => 'datetime:H:i',
        'quinta_manha2_fim' => 'datetime:H:i',
        'quinta_tarde_inicio' => 'datetime:H:i',
        'quinta_tarde_fim' => 'datetime:H:i',
        'quinta_tarde2_inicio' => 'datetime:H:i',
        'quinta_tarde2_fim' => 'datetime:H:i',
        'sexta_inicio' => 'datetime:H:i',
        'sexta_fim' => 'datetime:H:i',
        'sexta_manha2_inicio' => 'datetime:H:i',
        'sexta_manha2_fim' => 'datetime:H:i',
        'sexta_tarde_inicio' => 'datetime:H:i',
        'sexta_tarde_fim' => 'datetime:H:i',
        'sexta_tarde2_inicio' => 'datetime:H:i',
        'sexta_tarde2_fim' => 'datetime:H:i',
        'sabado_inicio' => 'datetime:H:i',
        'sabado_fim' => 'datetime:H:i',
        'sabado_manha2_inicio' => 'datetime:H:i',
        'sabado_manha2_fim' => 'datetime:H:i',
        'sabado_tarde_inicio' => 'datetime:H:i',
        'sabado_tarde_fim' => 'datetime:H:i',
        'sabado_tarde2_inicio' => 'datetime:H:i',
        'sabado_tarde2_fim' => 'datetime:H:i',
        'domingo_inicio' => 'datetime:H:i',
        'domingo_fim' => 'datetime:H:i',
        'domingo_manha2_inicio' => 'datetime:H:i',
        'domingo_manha2_fim' => 'datetime:H:i',
        'domingo_tarde_inicio' => 'datetime:H:i',
        'domingo_tarde_fim' => 'datetime:H:i',
        'domingo_tarde2_inicio' => 'datetime:H:i',
        'domingo_tarde2_fim' => 'datetime:H:i',
    ];

    /**
     * Relacionamento com Funcionario
     */
    public function funcionario(): BelongsTo
    {
        return $this->belongsTo(Funcionario::class);
    }

    /**
     * Retorna os dias da semana configurados no template
     */
    public function getDiasConfigurados(): array
    {
        $dias = [];
        $diasSemana = ['segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado', 'domingo'];
        
        foreach ($diasSemana as $dia) {
            $inicio = $this->{$dia . '_inicio'};
            $fim = $this->{$dia . '_fim'};
            $tipo = $this->{$dia . '_tipo'};
            
            if ($inicio && $fim) {
                $dias[$dia] = [
                    'inicio' => $inicio,
                    'fim' => $fim,
                    'tipo' => $tipo ?? 'Normal'
                ];
            }
        }
        
        return $dias;
    }

    /**
     * Gera escalas para um período específico baseado no template
     */
    public function gerarEscalas(Carbon $dataInicio, Carbon $dataFim): array
    {
        $escalas = [];
        $diasConfigurados = $this->getDiasConfigurados();
        
        $dataAtual = $dataInicio->copy();
        
        while ($dataAtual->lte($dataFim)) {
            $diaSemana = $this->getDiaSemanaPortugues($dataAtual->dayOfWeek);
            
            if (isset($diasConfigurados[$diaSemana])) {
                $config = $diasConfigurados[$diaSemana];
                
                $escalas[] = [
                    'funcionario_id' => $this->funcionario_id,
                    'data' => $dataAtual->format('Y-m-d'),
                    'hora_inicio' => $config['inicio'],
                    'hora_fim' => $config['fim'],
                    'tipo_escala' => $config['tipo'],
                    'status' => 'Agendada',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            
            $dataAtual->addDay();
        }
        
        return $escalas;
    }

    /**
     * Converte número do dia da semana para português
     */
    public function getDiaSemanaPortugues(int $dayOfWeek): string
    {
        $dias = [
            0 => 'domingo',
            1 => 'segunda',
            2 => 'terca',
            3 => 'quarta',
            4 => 'quinta',
            5 => 'sexta',
            6 => 'sabado'
        ];
        
        return $dias[$dayOfWeek];
    }

    /**
     * Scope para templates ativos
     */
    public function scopeAtivo($query)
    {
        return $query->where('ativo', true);
    }

    /**
     * Scope para buscar por funcionário
     */
    public function scopePorFuncionario($query, $funcionarioId)
    {
        return $query->where('funcionario_id', $funcionarioId);
    }
}
