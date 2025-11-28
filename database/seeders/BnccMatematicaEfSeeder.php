<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\CampoExperiencia;
use App\Models\SaberConhecimento;
use App\Models\ObjetivoAprendizagem;

class BnccMatematicaEfSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $nivelEf = 'EF_anos_iniciais';

            $mapa = [
                [
                    'campo' => [
                        'nome' => 'Números',
                        'descricao' => 'Contagem, sistema de numeração decimal e operações com números naturais e racionais.',
                    ],
                    'objetos' => [
                        'Contagem, leitura e escrita de números naturais',
                        'Valor posicional e sistema de numeração decimal',
                        'Operações (adição, subtração, multiplicação e divisão)',
                        'Problemas envolvendo as quatro operações',
                    ],
                    'habilidades' => [
                        ['codigo' => 'EF01MA01', 'descricao' => 'Utilizar contagens para comparar quantidades, estimar resultados e resolver problemas.', 'ano' => 1],
                        ['codigo' => 'EF01MA02', 'descricao' => 'Ler, escrever e ordenar números naturais até 100.', 'ano' => 1],
                        ['codigo' => 'EF02MA03', 'descricao' => 'Compreender o valor posicional e compor/decompor números até 1.000.', 'ano' => 2],
                        ['codigo' => 'EF02MA04', 'descricao' => 'Resolver e elaborar problemas de adição e subtração com números naturais.', 'ano' => 2],
                        ['codigo' => 'EF03MA05', 'descricao' => 'Utilizar fatos básicos da multiplicação e divisão em situações cotidianas.', 'ano' => 3],
                        ['codigo' => 'EF03MA06', 'descricao' => 'Resolver problemas que envolvam diferentes significados das operações.', 'ano' => 3],
                        ['codigo' => 'EF04MA07', 'descricao' => 'Compreender e usar o sistema de numeração decimal até 100.000.', 'ano' => 4],
                        ['codigo' => 'EF04MA08', 'descricao' => 'Resolver problemas com multiplicação e divisão, utilizando estratégias e algoritmos convencionais.', 'ano' => 4],
                        ['codigo' => 'EF05MA09', 'descricao' => 'Efetuar cálculos e resolver problemas com números naturais e racionais (decimais).', 'ano' => 5],
                        ['codigo' => 'EF05MA10', 'descricao' => 'Utilizar estimativas e verificações para avaliar a razoabilidade de resultados.', 'ano' => 5],
                    ],
                ],
                [
                    'campo' => [
                        'nome' => 'Álgebra',
                        'descricao' => 'Regularidades, padrões e relações em sequências numéricas e simbólicas.',
                    ],
                    'objetos' => [
                        'Regularidades, padrões e sequências',
                        'Propriedades das operações',
                        'Equivalência e desigualdade',
                    ],
                    'habilidades' => [
                        ['codigo' => 'EF01MA11', 'descricao' => 'Reconhecer, descrever e continuar padrões em sequências.', 'ano' => 1],
                        ['codigo' => 'EF02MA12', 'descricao' => 'Compreender e representar relações de igualdade e desigualdade.', 'ano' => 2],
                        ['codigo' => 'EF03MA13', 'descricao' => 'Utilizar regularidades para construir estratégias de cálculo mental.', 'ano' => 3],
                        ['codigo' => 'EF04MA14', 'descricao' => 'Generalizar padrões e expressar relações por meio de linguagem natural, símbolos e tabelas.', 'ano' => 4],
                        ['codigo' => 'EF05MA15', 'descricao' => 'Resolver e formular problemas que envolvam regularidades numéricas e simbólicas.', 'ano' => 5],
                    ],
                ],
                [
                    'campo' => [
                        'nome' => 'Geometria',
                        'descricao' => 'Estudo de formas planas e espaciais, localização e representações geométricas.',
                    ],
                    'objetos' => [
                        'Formas planas e espaciais',
                        'Localização e deslocamento no espaço',
                        'Simetrias e representações geométricas',
                    ],
                    'habilidades' => [
                        ['codigo' => 'EF01MA16', 'descricao' => 'Reconhecer figuras geométricas planas em objetos do cotidiano.', 'ano' => 1],
                        ['codigo' => 'EF02MA17', 'descricao' => 'Descrever a localização de pessoas e objetos no espaço, utilizando vocabulário adequado.', 'ano' => 2],
                        ['codigo' => 'EF03MA18', 'descricao' => 'Identificar, comparar e classificar figuras geométricas planas e espaciais.', 'ano' => 3],
                        ['codigo' => 'EF03MA19', 'descricao' => 'Desenhar figuras planas usando instrumentos de medição e apoio.', 'ano' => 3],
                        ['codigo' => 'EF04MA20', 'descricao' => 'Reconhecer ângulos, vértices e lados em figuras geométricas.', 'ano' => 4],
                        ['codigo' => 'EF04MA21', 'descricao' => 'Interpretar e construir representações espaciais simples (croquis, plantas, mapas).', 'ano' => 4],
                        ['codigo' => 'EF05MA22', 'descricao' => 'Identificar propriedades de triângulos, quadriláteros e outras figuras planas.', 'ano' => 5],
                        ['codigo' => 'EF05MA23', 'descricao' => 'Resolver problemas que envolvam deslocamentos e localização em mapas e representações.', 'ano' => 5],
                    ],
                ],
                [
                    'campo' => [
                        'nome' => 'Grandezas e Medidas',
                        'descricao' => 'Comparação, medição e conversão de unidades; perímetro e área.',
                    ],
                    'objetos' => [
                        'Comparação e medição de comprimentos, massas, tempos, capacidades e temperaturas',
                        'Unidades de medida padronizadas e não padronizadas',
                        'Perímetro e área de figuras planas',
                    ],
                    'habilidades' => [
                        ['codigo' => 'EF01MA24', 'descricao' => 'Comparar comprimentos, massas e capacidades utilizando instrumentos não padronizados.', 'ano' => 1],
                        ['codigo' => 'EF02MA25', 'descricao' => 'Utilizar unidades de medida convencionais para medir e estimar grandezas.', 'ano' => 2],
                        ['codigo' => 'EF03MA26', 'descricao' => 'Resolver problemas envolvendo medidas de tempo (dias, horas, minutos).', 'ano' => 3],
                        ['codigo' => 'EF04MA27', 'descricao' => 'Calcular o perímetro de figuras planas.', 'ano' => 4],
                        ['codigo' => 'EF04MA28', 'descricao' => 'Resolver problemas que envolvam diferentes unidades de medida.', 'ano' => 4],
                        ['codigo' => 'EF05MA29', 'descricao' => 'Calcular área de figuras planas, utilizando unidades de medida convencionais.', 'ano' => 5],
                        ['codigo' => 'EF05MA30', 'descricao' => 'Relacionar unidades de medida e converter entre elas em situações práticas.', 'ano' => 5],
                    ],
                ],
                [
                    'campo' => [
                        'nome' => 'Probabilidade e Estatística',
                        'descricao' => 'Coleta, organização e interpretação de dados; noções de chance e probabilidade.',
                    ],
                    'objetos' => [
                        'Coleta e organização de dados',
                        'Leitura e interpretação de gráficos e tabelas',
                        'Noções de chance e probabilidade em contextos cotidianos',
                    ],
                    'habilidades' => [
                        ['codigo' => 'EF01MA31', 'descricao' => 'Ler e organizar informações simples em listas e tabelas.', 'ano' => 1],
                        ['codigo' => 'EF02MA32', 'descricao' => 'Coletar dados e representá-los por meio de gráficos de colunas ou pictogramas.', 'ano' => 2],
                        ['codigo' => 'EF03MA33', 'descricao' => 'Interpretar gráficos e tabelas de dupla entrada.', 'ano' => 3],
                        ['codigo' => 'EF04MA34', 'descricao' => 'Produzir e interpretar gráficos e tabelas com base em dados obtidos em pesquisas.', 'ano' => 4],
                        ['codigo' => 'EF05MA35', 'descricao' => 'Avaliar e comunicar resultados de pesquisas, interpretando gráficos e discutindo probabilidades simples.', 'ano' => 5],
                    ],
                ],
            ];

            foreach ($mapa as $grupo) {
                $campo = CampoExperiencia::firstOrCreate(
                    [
                        'nome' => $grupo['campo']['nome'],
                        'nivel' => $nivelEf,
                    ],
                    [
                        'descricao' => $grupo['campo']['descricao'] ?? null,
                        'ativo' => true,
                    ]
                );

                $objetos = [];
                foreach ($grupo['objetos'] as $idx => $tituloObj) {
                    $obj = SaberConhecimento::firstOrCreate(
                        [
                            'campo_experiencia_id' => $campo->id,
                            'titulo' => $tituloObj,
                        ],
                        [
                            'descricao' => null,
                            'ordem' => $idx + 1,
                            'ativo' => true,
                        ]
                    );
                    $objetos[$tituloObj] = $obj;
                }

                foreach ($grupo['habilidades'] as $hab) {
                    $codigo = $hab['codigo'];
                    $descricao = $hab['descricao'];
                    $ano = $hab['ano'];

                    $objetivo = ObjetivoAprendizagem::firstOrCreate(
                        ['codigo' => $codigo],
                        [
                            'campo_experiencia_id' => $campo->id,
                            'saber_conhecimento_id' => null,
                            'descricao' => $descricao,
                            'ativo' => true,
                        ]
                    );

                    $objetivo->campo_experiencia_id = $campo->id;
                    $objetivo->etapa = $nivelEf;
                    $objetivo->ano = $ano;
                    $primeiroObjeto = reset($objetos);
                    if ($primeiroObjeto) {
                        $objetivo->saber_conhecimento_id = $primeiroObjeto->id;
                    }
                    $objetivo->save();
                }
            }

            if (method_exists($this, 'command') && $this->command) {
                $this->command->info('BNCC Matemática EF (Anos Iniciais) populado com sucesso.');
            }
        });
    }
}