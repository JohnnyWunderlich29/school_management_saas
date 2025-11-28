<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanejamentoDiario extends Model
{
    use HasFactory;

    protected $table = 'planejamento_diarios';

    protected $fillable = [
        'planejamento_id',
        'data',
        'dia_semana',
        'planejado',
        'campos_experiencia',
        'saberes_conhecimentos',
        'objetivos_especificos',
        'objetivos_aprendizagem',
        'metodologia',
        'recursos_predefinidos',
        'recursos_personalizados',
    ];

    protected $casts = [
        'data' => 'date:Y-m-d',
        'planejado' => 'boolean',
        'campos_experiencia' => 'array',
        'saberes_conhecimentos' => 'array',
        'objetivos_aprendizagem' => 'array',
        'recursos_predefinidos' => 'array',
    ];

    public function planejamento()
    {
        return $this->belongsTo(Planejamento::class);
    }
}