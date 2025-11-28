<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\CampoExperiencia;
use App\Models\SaberConhecimento;
use App\Models\ObjetivoAprendizagem;

class BnccGeografiaEfSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $nivel = 'EF_anos_iniciais';
            $etapa = 'EF_anos_iniciais';

            $unidades = [
                [
                    'titulo' => 'Geografia — O sujeito e seu lugar no mundo',
                    'descricao' => 'Noção de lugar, pertencimento, convivência e ações humanas no espaço vivido.',
                    'objetos' => [
                        'Lugar de vivência e pertencimento.',
                        'Espaços de convivência: casa, escola, bairro, comunidade.',
                        'Identidade e relações sociais no espaço.',
                        'Ações humanas e transformações do lugar.',
                    ],
                    'habilidades' => [
                        ['codigo' => 'EF01GE01', 'descricao' => 'Reconhecer-se como parte integrante do lugar em que vive e identificar elementos que o compõem.', 'ano' => 1],
                        ['codigo' => 'EF01GE02', 'descricao' => 'Descrever características do lugar em que vive, valorizando o convívio e o respeito.', 'ano' => 1],
                        ['codigo' => 'EF02GE03', 'descricao' => 'Comparar diferentes lugares de vivência (rural, urbano, praiano etc.) e suas transformações.', 'ano' => 2],
                        ['codigo' => 'EF03GE04', 'descricao' => 'Identificar modificações realizadas pelas pessoas no lugar e suas consequências.', 'ano' => 3],
                        ['codigo' => 'EF04GE05', 'descricao' => 'Reconhecer o papel das ações humanas na transformação do espaço geográfico.', 'ano' => 4],
                        ['codigo' => 'EF05GE06', 'descricao' => 'Analisar interações entre sociedade e natureza na formação do espaço vivido.', 'ano' => 5],
                    ],
                ],
                [
                    'titulo' => 'Geografia — Conexões e escalas',
                    'descricao' => 'Compreensão de escalas espaciais, localização, representação e redes entre lugares.',
                    'objetos' => [
                        'Escalas espaciais (local, regional, nacional, global).',
                        'Localização e deslocamento no espaço.',
                        'Representação cartográfica.',
                        'Relações entre diferentes lugares.',
                    ],
                    'habilidades' => [
                        ['codigo' => 'EF02GE07', 'descricao' => 'Localizar pontos de referência no espaço de vivência.', 'ano' => 2],
                        ['codigo' => 'EF02GE08', 'descricao' => 'Identificar caminhos e trajetos cotidianos (de casa à escola, por exemplo).', 'ano' => 2],
                        ['codigo' => 'EF03GE09', 'descricao' => 'Usar representações gráficas simples (croquis, mapas mentais, plantas baixas).', 'ano' => 3],
                        ['codigo' => 'EF04GE10', 'descricao' => 'Compreender a noção de escala e representar diferentes lugares com proporção.', 'ano' => 4],
                        ['codigo' => 'EF05GE11', 'descricao' => 'Analisar conexões entre diferentes lugares e as redes que os ligam (transportes, comunicações, comércio).', 'ano' => 5],
                    ],
                ],
                [
                    'titulo' => 'Geografia — Mundo do trabalho',
                    'descricao' => 'Tipos de trabalho, formas de produção, consumo e relação com o espaço.',
                    'objetos' => [
                        'Tipos de trabalho e formas de produção.',
                        'Transformações no mundo do trabalho ao longo do tempo.',
                        'Relação entre o trabalho e o espaço.',
                        'Consumo e sustentabilidade.',
                    ],
                    'habilidades' => [
                        ['codigo' => 'EF02GE12', 'descricao' => 'Reconhecer diferentes tipos de trabalho na comunidade.', 'ano' => 2],
                        ['codigo' => 'EF03GE13', 'descricao' => 'Relacionar o trabalho às condições naturais e sociais de cada lugar.', 'ano' => 3],
                        ['codigo' => 'EF04GE14', 'descricao' => 'Identificar transformações nos modos de produção e consumo.', 'ano' => 4],
                        ['codigo' => 'EF05GE15', 'descricao' => 'Analisar o papel do trabalho na organização do espaço e na vida das pessoas.', 'ano' => 5],
                    ],
                ],
                [
                    'titulo' => 'Geografia — Formas de representação e pensamento espacial',
                    'descricao' => 'Linguagem cartográfica, símbolos, legendas, escalas e diferentes representações.',
                    'objetos' => [
                        'Linguagem cartográfica.',
                        'Símbolos, legendas e escalas.',
                        'Mapas, globos, plantas, croquis.',
                        'Localização e orientação.',
                    ],
                    'habilidades' => [
                        ['codigo' => 'EF01GE16', 'descricao' => 'Observar e descrever elementos do espaço e sua disposição.', 'ano' => 1],
                        ['codigo' => 'EF02GE17', 'descricao' => 'Reconhecer representações simples do espaço em imagens e mapas.', 'ano' => 2],
                        ['codigo' => 'EF03GE18', 'descricao' => 'Interpretar mapas, legendas e escalas simples.', 'ano' => 3],
                        ['codigo' => 'EF04GE19', 'descricao' => 'Produzir mapas e croquis do entorno, identificando pontos de referência.', 'ano' => 4],
                        ['codigo' => 'EF05GE20', 'descricao' => 'Utilizar diferentes representações cartográficas para compreender fenômenos espaciais.', 'ano' => 5],
                    ],
                ],
                [
                    'titulo' => 'Geografia — Natureza, ambientes e qualidade de vida',
                    'descricao' => 'Elementos naturais, relação sociedade-natureza, problemas ambientais e conservação.',
                    'objetos' => [
                        'Elementos naturais: relevo, vegetação, clima, hidrografia.',
                        'Relação entre sociedade e natureza.',
                        'Problemas ambientais e sustentabilidade.',
                        'Conservação e uso dos recursos naturais.',
                    ],
                    'habilidades' => [
                        ['codigo' => 'EF01GE21', 'descricao' => 'Identificar elementos naturais do lugar em que vive.', 'ano' => 1],
                        ['codigo' => 'EF02GE22', 'descricao' => 'Reconhecer as relações entre os elementos da natureza e a vida das pessoas.', 'ano' => 2],
                        ['codigo' => 'EF03GE23', 'descricao' => 'Analisar como as ações humanas modificam a natureza local.', 'ano' => 3],
                        ['codigo' => 'EF04GE24', 'descricao' => 'Relacionar problemas ambientais às atividades humanas.', 'ano' => 4],
                        ['codigo' => 'EF05GE25', 'descricao' => 'Propor ações de cuidado e conservação do ambiente para melhoria da qualidade de vida.', 'ano' => 5],
                    ],
                ],
            ];

            foreach ($unidades as $unidade) {
                $campo = CampoExperiencia::firstOrCreate(
                    [
                        'nome' => $unidade['titulo'],
                        'nivel' => $nivel,
                    ],
                    [
                        'descricao' => $unidade['descricao'] ?? null,
                        'ativo' => true,
                    ]
                );

                $objetos = [];
                foreach ($unidade['objetos'] as $idx => $tituloObj) {
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

                foreach ($unidade['habilidades'] as $hab) {
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
                    $objetivo->etapa = $etapa;
                    $objetivo->ano = $ano;
                    $primeiroObjeto = reset($objetos);
                    if ($primeiroObjeto) {
                        $objetivo->saber_conhecimento_id = $primeiroObjeto->id;
                    }
                    $objetivo->save();
                }
            }

            if (method_exists($this, 'command') && $this->command) {
                $this->command->info('BNCC Geografia EF (Anos Iniciais) populado com sucesso.');
            }
        });
    }
}