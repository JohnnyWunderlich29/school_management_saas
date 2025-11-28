<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sala;
use App\Models\Aluno;
use Carbon\Carbon;

class BaseHtmlController extends Controller
{
    /**
     * Exibe a página de demonstração de componentes e padrões de design
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Dados de exemplo para demonstração dos componentes
        $salasComEstatisticas = collect([
            (object) [
                'id' => 1,
                'nome_completo' => 'Sala A - Infantil',
                'codigo' => 'SA01',
                'total_alunos' => 25,
                'presentes' => 20,
                'ausentes' => 3,
                'nao_registrados' => 2
            ],
            (object) [
                'id' => 2,
                'nome_completo' => 'Sala B - Fundamental I',
                'codigo' => 'SB02',
                'total_alunos' => 30,
                'presentes' => 28,
                'ausentes' => 2,
                'nao_registrados' => 0
            ],
            (object) [
                'id' => 3,
                'nome_completo' => 'Sala C - Fundamental II',
                'codigo' => 'SC03',
                'total_alunos' => 22,
                'presentes' => 18,
                'ausentes' => 4,
                'nao_registrados' => 0
            ]
        ]);
        
        // Dados de exemplo para filtros
        $todasSalas = collect([
            (object) ['id' => 1, 'nome_completo' => 'Sala A - Infantil'],
            (object) ['id' => 2, 'nome_completo' => 'Sala B - Fundamental I'],
            (object) ['id' => 3, 'nome_completo' => 'Sala C - Fundamental II']
        ]);
        
        // Dados de exemplo para alunos
        $alunos = collect([
            (object) ['id' => 1, 'nome' => 'João', 'sobrenome' => 'Silva'],
            (object) ['id' => 2, 'nome' => 'Maria', 'sobrenome' => 'Santos'],
            (object) ['id' => 3, 'nome' => 'Pedro', 'sobrenome' => 'Oliveira']
        ]);
        
        $dataInicio = Carbon::today()->format('Y-m-d');
        $dataFim = Carbon::today()->format('Y-m-d');
        
        return view('base-html.index', compact(
            'salasComEstatisticas',
            'todasSalas', 
            'alunos',
            'dataInicio',
            'dataFim'
        ));
    }
}