<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CampoExperiencia extends Model
{
    protected $table = 'campos_experiencia';
    
    protected $fillable = [
        'nome',
        'descricao',
        'nivel',
        'ativo'
    ];
    
    protected $casts = [
        'ativo' => 'boolean'
    ];
    
    /**
     * Relacionamento com objetivos de aprendizagem
     */
    public function objetivosAprendizagem(): HasMany
    {
        return $this->hasMany(ObjetivoAprendizagem::class);
    }
    
    /**
     * Scope para filtrar por modalidade (nível)
     */
    public function scopePorModalidade($query, ?array $modalidades)
    {
        if (empty($modalidades)) {
            return $query;
        }

        return $query->where(function ($q) use ($modalidades) {
            // Se Educação Infantil (EI) estiver presente, inclui também registros sem nível (padrão BNCC)
            if (in_array('EI', $modalidades)) {
                $q->whereNull('nivel')->orWhere('nivel', 'EI')->orWhere('nivel', 'like', 'EI_%');
            }

            // Mapeamento de códigos de modalidade para valores na coluna 'nivel'
            $mapeamento = [
                'EF'  => ['EF', 'EF_anos_iniciais', 'EF_anos_finais'],
                'EF1' => ['EF', 'EF_anos_iniciais'],
                'EF2' => ['EF', 'EF_anos_finais'],
                'EM'  => ['EM'],
                'EJA' => ['EJA'],
                'EP'  => ['EP'],
            ];

            $valoresParaFiltrar = [];
            foreach ($modalidades as $mod) {
                if (isset($mapeamento[$mod])) {
                    $valoresParaFiltrar = array_merge($valoresParaFiltrar, $mapeamento[$mod]);
                } else {
                    $valoresParaFiltrar[] = $mod;
                }
            }

            $valoresParaFiltrar = array_unique($valoresParaFiltrar);
            $valoresParaFiltrar = array_diff($valoresParaFiltrar, ['EI']); // EI já tratado acima

            if (!empty($valoresParaFiltrar)) {
                $q->orWhereIn('nivel', $valoresParaFiltrar);
                
                // Suporte para prefixos caso alguém use string direta
                foreach ($valoresParaFiltrar as $val) {
                    $q->orWhere('nivel', 'like', $val . '_%');
                }
            }
        });
    }

    /**
     * Scope para campos ativos
     */
    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }
}
