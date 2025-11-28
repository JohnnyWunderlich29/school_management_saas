<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ModalidadeEnsino;

class ModalidadeEnsinoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modalidades = [
            // Modalidades Regulares da Educação Básica
            [
                'codigo' => 'EI',
                'nome' => 'Educação Infantil',
                'nivel' => 1,
                'descricao' => 'Primeira etapa da educação básica (0 a 5 anos)',
                'ativo' => true
            ],
            [
                'codigo' => 'EF1',
                'nome' => 'Ensino Fundamental - Anos Iniciais',
                'nivel' => 2,
                'descricao' => 'Anos iniciais do ensino fundamental (1º ao 5º ano)',
                'ativo' => true
            ],
            [
                'codigo' => 'EF2',
                'nome' => 'Ensino Fundamental - Anos Finais',
                'nivel' => 3,
                'descricao' => 'Anos finais do ensino fundamental (6º ao 9º ano)',
                'ativo' => true
            ],
            [
                'codigo' => 'EM',
                'nome' => 'Ensino Médio',
                'nivel' => 4,
                'descricao' => 'Etapa final da educação básica (1ª à 3ª série)',
                'ativo' => true
            ],
            
            // Modalidades Específicas e Transversais
            [
                'codigo' => 'EJA',
                'nome' => 'Educação de Jovens e Adultos',
                'nivel' => 5,
                'descricao' => 'Modalidade destinada a jovens e adultos que não tiveram acesso ou continuidade de estudos',
                'ativo' => true
            ],
            [
                'codigo' => 'EE',
                'nome' => 'Educação Especial',
                'nivel' => 6,
                'descricao' => 'Modalidade transversal para estudantes com deficiência, transtornos globais do desenvolvimento e altas habilidades',
                'ativo' => true
            ],
            [
                'codigo' => 'EP',
                'nome' => 'Educação Profissional',
                'nivel' => 7,
                'descricao' => 'Modalidade que integra diferentes níveis e modalidades de educação às dimensões do trabalho',
                'ativo' => true
            ],
            [
                'codigo' => 'EC',
                'nome' => 'Educação do Campo',
                'nivel' => 8,
                'descricao' => 'Modalidade destinada ao atendimento das populações rurais em suas mais variadas formas de produção da vida',
                'ativo' => true
            ],
            [
                'codigo' => 'EEI',
                'nome' => 'Educação Escolar Indígena',
                'nivel' => 9,
                'descricao' => 'Modalidade que garante aos povos indígenas o uso de suas línguas maternas e processos próprios de aprendizagem',
                'ativo' => true
            ],
            [
                'codigo' => 'EEQ',
                'nome' => 'Educação Escolar Quilombola',
                'nivel' => 10,
                'descricao' => 'Modalidade desenvolvida em unidades educacionais inscritas em suas terras e cultura',
                'ativo' => true
            ],
            [
                'codigo' => 'EAD',
                'nome' => 'Educação a Distância',
                'nivel' => 11,
                'descricao' => 'Modalidade educacional na qual a mediação didático-pedagógica nos processos de ensino e aprendizagem ocorre com a utilização de meios e tecnologias de informação e comunicação',
                'ativo' => true
            ]
        ];

        foreach ($modalidades as $modalidade) {
            ModalidadeEnsino::updateOrCreate(
                ['codigo' => $modalidade['codigo']],
                $modalidade
            );
        }
    }
}
