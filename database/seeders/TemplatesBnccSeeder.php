<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TemplateBncc;

class TemplatesBnccSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            // EDUCAÇÃO INFANTIL - CRECHE
            [
                'categoria' => 'Educação Infantil',
                'subcategoria' => 'Creche',
                'nome' => 'Grupo 1 (Bebês)',
                'codigo' => 'EI_CRECHE_G1',
                'descricao' => 'Grupo 1: 4 meses a 1 ano e meio (bebês)',
                'idade_minima' => 4, // 4 meses
                'idade_maxima' => 18, // 1 ano e meio
                'capacidade_padrao' => 8,
                'capacidade_minima' => 6,
                'capacidade_maxima' => 10,
                'carga_horaria_semanal' => 25,
                'numero_aulas_dia' => 5,
                'duracao_aula_minutos' => 60,
                'turno_matutino' => true,
                'turno_vespertino' => true,
                'turno_noturno' => false,
                'turno_integral' => true,
                'modalidades_compativeis' => ['EI'],
                'observacoes' => 'Foco no cuidado, desenvolvimento motor e sensorial',
                'ativo' => true,
                'ordem' => 1
            ],
            [
                'categoria' => 'Educação Infantil',
                'subcategoria' => 'Creche',
                'nome' => 'Grupo 2 (Crianças bem pequenas)',
                'codigo' => 'EI_CRECHE_G2',
                'descricao' => 'Grupo 2: 1 ano e meio a 3 anos (crianças bem pequenas)',
                'idade_minima' => 18, // 1 ano e meio
                'idade_maxima' => 36, // 3 anos
                'capacidade_padrao' => 12,
                'capacidade_minima' => 8,
                'capacidade_maxima' => 15,
                'carga_horaria_semanal' => 25,
                'numero_aulas_dia' => 5,
                'duracao_aula_minutos' => 60,
                'turno_matutino' => true,
                'turno_vespertino' => true,
                'turno_noturno' => false,
                'turno_integral' => true,
                'modalidades_compativeis' => ['EI'],
                'observacoes' => 'Desenvolvimento da linguagem, autonomia e socialização',
                'ativo' => true,
                'ordem' => 2
            ],

            // EDUCAÇÃO INFANTIL - PRÉ-ESCOLA
            [
                'categoria' => 'Educação Infantil',
                'subcategoria' => 'Pré-escola',
                'nome' => 'Grupo 3 (4 anos)',
                'codigo' => 'EI_PRE_G3',
                'descricao' => 'Grupo 3: 4 anos',
                'idade_minima' => 48, // 4 anos
                'idade_maxima' => 59, // 4 anos e 11 meses
                'capacidade_padrao' => 20,
                'capacidade_minima' => 15,
                'capacidade_maxima' => 25,
                'carga_horaria_semanal' => 20,
                'numero_aulas_dia' => 4,
                'duracao_aula_minutos' => 60,
                'turno_matutino' => true,
                'turno_vespertino' => true,
                'turno_noturno' => false,
                'turno_integral' => true,
                'modalidades_compativeis' => ['EI'],
                'observacoes' => 'Preparação para alfabetização, desenvolvimento cognitivo',
                'ativo' => true,
                'ordem' => 3
            ],
            [
                'categoria' => 'Educação Infantil',
                'subcategoria' => 'Pré-escola',
                'nome' => 'Grupo 4 (5 anos)',
                'codigo' => 'EI_PRE_G4',
                'descricao' => 'Grupo 4: 5 anos',
                'idade_minima' => 60, // 5 anos
                'idade_maxima' => 71, // 5 anos e 11 meses
                'capacidade_padrao' => 20,
                'capacidade_minima' => 15,
                'capacidade_maxima' => 25,
                'carga_horaria_semanal' => 20,
                'numero_aulas_dia' => 4,
                'duracao_aula_minutos' => 60,
                'turno_matutino' => true,
                'turno_vespertino' => true,
                'turno_noturno' => false,
                'turno_integral' => true,
                'modalidades_compativeis' => ['EI'],
                'observacoes' => 'Preparação direta para o Ensino Fundamental',
                'ativo' => true,
                'ordem' => 4
            ],

            // ENSINO FUNDAMENTAL - ANOS INICIAIS
            [
                'categoria' => 'Ensino Fundamental',
                'subcategoria' => 'Anos Iniciais',
                'nome' => '1º ano',
                'codigo' => 'EF_AI_1ANO',
                'descricao' => '1º ano (6 anos)',
                'idade_minima' => 72, // 6 anos
                'idade_maxima' => 83, // 6 anos e 11 meses
                'capacidade_padrao' => 25,
                'capacidade_minima' => 20,
                'capacidade_maxima' => 30,
                'carga_horaria_semanal' => 20,
                'numero_aulas_dia' => 4,
                'duracao_aula_minutos' => 50,
                'turno_matutino' => true,
                'turno_vespertino' => true,
                'turno_noturno' => false,
                'turno_integral' => true,
                'modalidades_compativeis' => ['EF1'],
                'observacoes' => 'Alfabetização e letramento',
                'ativo' => true,
                'ordem' => 5
            ],
            [
                'categoria' => 'Ensino Fundamental',
                'subcategoria' => 'Anos Iniciais',
                'nome' => '2º ano',
                'codigo' => 'EF_AI_2ANO',
                'descricao' => '2º ano (7 anos)',
                'idade_minima' => 84, // 7 anos
                'idade_maxima' => 95, // 7 anos e 11 meses
                'capacidade_padrao' => 25,
                'capacidade_minima' => 20,
                'capacidade_maxima' => 30,
                'carga_horaria_semanal' => 20,
                'numero_aulas_dia' => 4,
                'duracao_aula_minutos' => 50,
                'turno_matutino' => true,
                'turno_vespertino' => true,
                'turno_noturno' => false,
                'turno_integral' => true,
                'modalidades_compativeis' => ['EF1'],
                'observacoes' => 'Consolidação da alfabetização',
                'ativo' => true,
                'ordem' => 6
            ],
            [
                'categoria' => 'Ensino Fundamental',
                'subcategoria' => 'Anos Iniciais',
                'nome' => '3º ano',
                'codigo' => 'EF_AI_3ANO',
                'descricao' => '3º ano (8 anos)',
                'idade_minima' => 96, // 8 anos
                'idade_maxima' => 107, // 8 anos e 11 meses
                'capacidade_padrao' => 25,
                'capacidade_minima' => 20,
                'capacidade_maxima' => 30,
                'carga_horaria_semanal' => 25,
                'numero_aulas_dia' => 5,
                'duracao_aula_minutos' => 50,
                'turno_matutino' => true,
                'turno_vespertino' => true,
                'turno_noturno' => false,
                'turno_integral' => true,
                'modalidades_compativeis' => ['EF1'],
                'observacoes' => 'Desenvolvimento da fluência leitora',
                'ativo' => true,
                'ordem' => 7
            ],
            [
                'categoria' => 'Ensino Fundamental',
                'subcategoria' => 'Anos Iniciais',
                'nome' => '4º ano',
                'codigo' => 'EF_AI_4ANO',
                'descricao' => '4º ano (9 anos)',
                'idade_minima' => 108, // 9 anos
                'idade_maxima' => 119, // 9 anos e 11 meses
                'capacidade_padrao' => 25,
                'capacidade_minima' => 20,
                'capacidade_maxima' => 30,
                'carga_horaria_semanal' => 25,
                'numero_aulas_dia' => 5,
                'duracao_aula_minutos' => 50,
                'turno_matutino' => true,
                'turno_vespertino' => true,
                'turno_noturno' => false,
                'turno_integral' => true,
                'modalidades_compativeis' => ['EF1'],
                'observacoes' => 'Aprofundamento em todas as áreas do conhecimento',
                'ativo' => true,
                'ordem' => 8
            ],
            [
                'categoria' => 'Ensino Fundamental',
                'subcategoria' => 'Anos Iniciais',
                'nome' => '5º ano',
                'codigo' => 'EF_AI_5ANO',
                'descricao' => '5º ano (10 anos)',
                'idade_minima' => 120, // 10 anos
                'idade_maxima' => 131, // 10 anos e 11 meses
                'capacidade_padrao' => 25,
                'capacidade_minima' => 20,
                'capacidade_maxima' => 30,
                'carga_horaria_semanal' => 25,
                'numero_aulas_dia' => 5,
                'duracao_aula_minutos' => 50,
                'turno_matutino' => true,
                'turno_vespertino' => true,
                'turno_noturno' => false,
                'turno_integral' => true,
                'modalidades_compativeis' => ['EF1'],
                'observacoes' => 'Preparação para os Anos Finais',
                'ativo' => true,
                'ordem' => 9
            ],

            // ENSINO FUNDAMENTAL - ANOS FINAIS
            [
                'categoria' => 'Ensino Fundamental',
                'subcategoria' => 'Anos Finais',
                'nome' => '6º ano',
                'codigo' => 'EF_AF_6ANO',
                'descricao' => '6º ano (11 anos)',
                'idade_minima' => 132, // 11 anos
                'idade_maxima' => 143, // 11 anos e 11 meses
                'capacidade_padrao' => 30,
                'capacidade_minima' => 25,
                'capacidade_maxima' => 35,
                'carga_horaria_semanal' => 30,
                'numero_aulas_dia' => 6,
                'duracao_aula_minutos' => 50,
                'turno_matutino' => true,
                'turno_vespertino' => true,
                'turno_noturno' => false,
                'turno_integral' => true,
                'modalidades_compativeis' => ['EF2'],
                'observacoes' => 'Transição para o sistema de disciplinas',
                'ativo' => true,
                'ordem' => 10
            ],
            [
                'categoria' => 'Ensino Fundamental',
                'subcategoria' => 'Anos Finais',
                'nome' => '7º ano',
                'codigo' => 'EF_AF_7ANO',
                'descricao' => '7º ano (12 anos)',
                'idade_minima' => 144, // 12 anos
                'idade_maxima' => 155, // 12 anos e 11 meses
                'capacidade_padrao' => 30,
                'capacidade_minima' => 25,
                'capacidade_maxima' => 35,
                'carga_horaria_semanal' => 30,
                'numero_aulas_dia' => 6,
                'duracao_aula_minutos' => 50,
                'turno_matutino' => true,
                'turno_vespertino' => true,
                'turno_noturno' => false,
                'turno_integral' => true,
                'modalidades_compativeis' => ['EF2'],
                'observacoes' => 'Aprofundamento das competências',
                'ativo' => true,
                'ordem' => 11
            ],
            [
                'categoria' => 'Ensino Fundamental',
                'subcategoria' => 'Anos Finais',
                'nome' => '8º ano',
                'codigo' => 'EF_AF_8ANO',
                'descricao' => '8º ano (13 anos)',
                'idade_minima' => 156, // 13 anos
                'idade_maxima' => 167, // 13 anos e 11 meses
                'capacidade_padrao' => 30,
                'capacidade_minima' => 25,
                'capacidade_maxima' => 35,
                'carga_horaria_semanal' => 30,
                'numero_aulas_dia' => 6,
                'duracao_aula_minutos' => 50,
                'turno_matutino' => true,
                'turno_vespertino' => true,
                'turno_noturno' => false,
                'turno_integral' => true,
                'modalidades_compativeis' => ['EF2'],
                'observacoes' => 'Desenvolvimento do pensamento crítico',
                'ativo' => true,
                'ordem' => 12
            ],
            [
                'categoria' => 'Ensino Fundamental',
                'subcategoria' => 'Anos Finais',
                'nome' => '9º ano',
                'codigo' => 'EF_AF_9ANO',
                'descricao' => '9º ano (14 anos)',
                'idade_minima' => 168, // 14 anos
                'idade_maxima' => 179, // 14 anos e 11 meses
                'capacidade_padrao' => 30,
                'capacidade_minima' => 25,
                'capacidade_maxima' => 35,
                'carga_horaria_semanal' => 30,
                'numero_aulas_dia' => 6,
                'duracao_aula_minutos' => 50,
                'turno_matutino' => true,
                'turno_vespertino' => true,
                'turno_noturno' => false,
                'turno_integral' => true,
                'modalidades_compativeis' => ['EF2'],
                'observacoes' => 'Preparação para o Ensino Médio',
                'ativo' => true,
                'ordem' => 13
            ],

            // ENSINO MÉDIO
            [
                'categoria' => 'Ensino Médio',
                'subcategoria' => null,
                'nome' => '1ª série',
                'codigo' => 'EM_1SERIE',
                'descricao' => '1ª série (15 anos)',
                'idade_minima' => 180, // 15 anos
                'idade_maxima' => 191, // 15 anos e 11 meses
                'capacidade_padrao' => 35,
                'capacidade_minima' => 30,
                'capacidade_maxima' => 40,
                'carga_horaria_semanal' => 30,
                'numero_aulas_dia' => 6,
                'duracao_aula_minutos' => 50,
                'turno_matutino' => true,
                'turno_vespertino' => true,
                'turno_noturno' => true,
                'turno_integral' => false,
                'modalidades_compativeis' => ['EM'],
                'observacoes' => 'Formação geral básica e itinerários formativos',
                'ativo' => true,
                'ordem' => 14
            ],
            [
                'categoria' => 'Ensino Médio',
                'subcategoria' => null,
                'nome' => '2ª série',
                'codigo' => 'EM_2SERIE',
                'descricao' => '2ª série (16 anos)',
                'idade_minima' => 192, // 16 anos
                'idade_maxima' => 203, // 16 anos e 11 meses
                'capacidade_padrao' => 35,
                'capacidade_minima' => 30,
                'capacidade_maxima' => 40,
                'carga_horaria_semanal' => 30,
                'numero_aulas_dia' => 6,
                'duracao_aula_minutos' => 50,
                'turno_matutino' => true,
                'turno_vespertino' => true,
                'turno_noturno' => true,
                'turno_integral' => false,
                'modalidades_compativeis' => ['EM'],
                'observacoes' => 'Aprofundamento nos itinerários formativos',
                'ativo' => true,
                'ordem' => 15
            ],
            [
                'categoria' => 'Ensino Médio',
                'subcategoria' => null,
                'nome' => '3ª série',
                'codigo' => 'EM_3SERIE',
                'descricao' => '3ª série (17 anos)',
                'idade_minima' => 204, // 17 anos
                'idade_maxima' => 215, // 17 anos e 11 meses
                'capacidade_padrao' => 35,
                'capacidade_minima' => 30,
                'capacidade_maxima' => 40,
                'carga_horaria_semanal' => 30,
                'numero_aulas_dia' => 6,
                'duracao_aula_minutos' => 50,
                'turno_matutino' => true,
                'turno_vespertino' => true,
                'turno_noturno' => true,
                'turno_integral' => false,
                'modalidades_compativeis' => ['EM'],
                'observacoes' => 'Preparação para o ensino superior e mundo do trabalho',
                'ativo' => true,
                'ordem' => 16
            ],

            // EJA - EDUCAÇÃO DE JOVENS E ADULTOS
            [
                'categoria' => 'EJA',
                'subcategoria' => 'Ensino Fundamental',
                'nome' => 'EJA - Anos Iniciais (1º ao 5º ano)',
                'codigo' => 'EJA_EF_AI',
                'descricao' => 'EJA Ensino Fundamental - Anos Iniciais (1º ao 5º ano)',
                'idade_minima' => 180, // 15 anos (idade mínima para EJA)
                'idade_maxima' => 1200, // Sem limite máximo
                'capacidade_padrao' => 25,
                'capacidade_minima' => 15,
                'capacidade_maxima' => 30,
                'carga_horaria_semanal' => 20,
                'numero_aulas_dia' => 4,
                'duracao_aula_minutos' => 50,
                'turno_matutino' => true,
                'turno_vespertino' => true,
                'turno_noturno' => true,
                'turno_integral' => false,
                'modalidades_compativeis' => ['EJA'],
                'observacoes' => 'Alfabetização e letramento para jovens e adultos',
                'ativo' => true,
                'ordem' => 17
            ],
            [
                'categoria' => 'EJA',
                'subcategoria' => 'Ensino Fundamental',
                'nome' => 'EJA - Anos Finais (6º ao 9º ano)',
                'codigo' => 'EJA_EF_AF',
                'descricao' => 'EJA Ensino Fundamental - Anos Finais (6º ao 9º ano)',
                'idade_minima' => 180, // 15 anos
                'idade_maxima' => 1200, // Sem limite máximo
                'capacidade_padrao' => 30,
                'capacidade_minima' => 20,
                'capacidade_maxima' => 35,
                'carga_horaria_semanal' => 25,
                'numero_aulas_dia' => 5,
                'duracao_aula_minutos' => 50,
                'turno_matutino' => true,
                'turno_vespertino' => true,
                'turno_noturno' => true,
                'turno_integral' => false,
                'modalidades_compativeis' => ['EJA'],
                'observacoes' => 'Conclusão do Ensino Fundamental para jovens e adultos',
                'ativo' => true,
                'ordem' => 18
            ],
            [
                'categoria' => 'EJA',
                'subcategoria' => 'Ensino Médio',
                'nome' => 'EJA - Ensino Médio',
                'codigo' => 'EJA_EM',
                'descricao' => 'EJA Ensino Médio (1ª, 2ª e 3ª séries)',
                'idade_minima' => 216, // 18 anos (idade mínima para EJA Ensino Médio)
                'idade_maxima' => 1200, // Sem limite máximo
                'capacidade_padrao' => 35,
                'capacidade_minima' => 25,
                'capacidade_maxima' => 40,
                'carga_horaria_semanal' => 25,
                'numero_aulas_dia' => 5,
                'duracao_aula_minutos' => 50,
                'turno_matutino' => true,
                'turno_vespertino' => true,
                'turno_noturno' => true,
                'turno_integral' => false,
                'modalidades_compativeis' => ['EJA'],
                'observacoes' => 'Conclusão do Ensino Médio para jovens e adultos',
                'ativo' => true,
                'ordem' => 19
            ]
        ];

        foreach ($templates as $template) {
            TemplateBncc::updateOrCreate(
                ['codigo' => $template['codigo']],
                $template
            );
        }
    }
}
