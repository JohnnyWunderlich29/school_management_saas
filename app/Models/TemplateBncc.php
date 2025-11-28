<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class TemplateBncc extends Model
{
    protected $table = 'templates_bncc';

    protected $fillable = [
        'categoria',
        'subcategoria',
        'nome',
        'codigo',
        'descricao',
        'idade_minima',
        'idade_maxima',
        'capacidade_padrao',
        'capacidade_minima',
        'capacidade_maxima',
        'carga_horaria_semanal',
        'numero_aulas_dia',
        'duracao_aula_minutos',
        'turno_matutino',
        'turno_vespertino',
        'turno_noturno',
        'turno_integral',
        'modalidades_compativeis',
        'observacoes',
        'ativo',
        'ordem'
    ];

    protected $casts = [
        'modalidades_compativeis' => 'array',
        'turno_matutino' => 'boolean',
        'turno_vespertino' => 'boolean',
        'turno_noturno' => 'boolean',
        'turno_integral' => 'boolean',
        'ativo' => 'boolean',
        'idade_minima' => 'integer',
        'idade_maxima' => 'integer',
        'capacidade_padrao' => 'integer',
        'capacidade_minima' => 'integer',
        'capacidade_maxima' => 'integer',
        'carga_horaria_semanal' => 'integer',
        'numero_aulas_dia' => 'integer',
        'duracao_aula_minutos' => 'integer',
        'ordem' => 'integer'
    ];

    /**
     * Scope para filtrar apenas templates ativos
     */
    public function scopeAtivos(Builder $query): Builder
    {
        return $query->where('ativo', true);
    }

    /**
     * Scope para filtrar por categoria
     */
    public function scopePorCategoria(Builder $query, string $categoria): Builder
    {
        return $query->where('categoria', $categoria);
    }

    /**
     * Scope para filtrar por subcategoria
     */
    public function scopePorSubcategoria(Builder $query, string $subcategoria): Builder
    {
        return $query->where('subcategoria', $subcategoria);
    }

    /**
     * Scope para ordenar por ordem e nome
     */
    public function scopeOrdenado(Builder $query): Builder
    {
        return $query->orderBy('ordem')->orderBy('nome');
    }

    /**
     * Retorna os templates organizados por categoria e subcategoria
     */
    public static function getTemplatesOrganizados(): array
    {
        $templates = self::ativos()->ordenado()->get();
        
        $organizados = [];
        
        foreach ($templates as $template) {
            $categoria = $template->categoria;
            $subcategoria = $template->subcategoria ?? 'Geral';
            
            if (!isset($organizados[$categoria])) {
                $organizados[$categoria] = [];
            }
            
            if (!isset($organizados[$categoria][$subcategoria])) {
                $organizados[$categoria][$subcategoria] = [];
            }
            
            $organizados[$categoria][$subcategoria][] = $template;
        }
        
        return $organizados;
    }

    /**
     * Verifica se um template é compatível com uma modalidade
     */
    public function isCompativelComModalidade(int $modalidadeId): bool
    {
        if (empty($this->modalidades_compativeis)) {
            return true; // Se não há restrições, é compatível com todas
        }
        
        return in_array($modalidadeId, $this->modalidades_compativeis);
    }

    /**
     * Retorna a idade em formato legível
     */
    public function getIdadeFormatada(): string
    {
        $idadeMinAno = floor($this->idade_minima / 12);
        $idadeMaxAno = floor($this->idade_maxima / 12);
        
        if ($idadeMinAno == $idadeMaxAno) {
            return "{$idadeMinAno} anos";
        }
        
        return "{$idadeMinAno} a {$idadeMaxAno} anos";
    }

    /**
     * Retorna os turnos permitidos
     */
    public function getTurnosPermitidos(): array
    {
        $turnos = [];
        
        if ($this->turno_matutino) $turnos[] = 'Matutino';
        if ($this->turno_vespertino) $turnos[] = 'Vespertino';
        if ($this->turno_noturno) $turnos[] = 'Noturno';
        if ($this->turno_integral) $turnos[] = 'Integral';
        
        return $turnos;
    }
}
