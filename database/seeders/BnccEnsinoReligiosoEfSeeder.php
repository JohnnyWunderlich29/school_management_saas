<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\CampoExperiencia;
use App\Models\SaberConhecimento;
use App\Models\ObjetivoAprendizagem;

class BnccEnsinoReligiosoEfSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $nivel = 'EF_anos_iniciais';
            $etapa = 'EF_anos_iniciais';

            $unidades = [
                [
                    'titulo' => 'Ensino Religioso — Identidades e Alteridades',
                    'descricao' => 'Identidade pessoal e social, respeito ao outro, diversidade e diálogo.',
                    'objetos' => [
                        'Identidade pessoal, social e cultural.',
                        'Respeito às diferenças e ao outro.',
                        'Diversidade étnica, religiosa e cultural.',
                        'Diálogo e convivência entre diferentes grupos.',
                    ],
                    'habilidades' => [
                        ['codigo' => 'EF01ER01', 'descricao' => 'Reconhecer características de sua identidade e de outras pessoas.', 'ano' => 1],
                        ['codigo' => 'EF02ER02', 'descricao' => 'Valorizar o respeito nas relações com o outro, em diferentes contextos.', 'ano' => 2],
                        ['codigo' => 'EF03ER03', 'descricao' => 'Identificar manifestações culturais e religiosas de diferentes grupos sociais.', 'ano' => 3],
                        ['codigo' => 'EF04ER04', 'descricao' => 'Refletir sobre atitudes de respeito e solidariedade nas relações cotidianas.', 'ano' => 4],
                        ['codigo' => 'EF05ER05', 'descricao' => 'Compreender a importância da convivência harmoniosa entre pessoas de diferentes crenças e valores.', 'ano' => 5],
                    ],
                ],
                [
                    'titulo' => 'Ensino Religioso — Manifestações Religiosas',
                    'descricao' => 'Expressões religiosas, diversidade de tradições, formas de espiritualidade e patrimônio cultural.',
                    'objetos' => [
                        'Expressões religiosas (ritos, celebrações, símbolos, espaços, músicas, vestes).',
                        'Diversidade das tradições religiosas.',
                        'Formas de expressão da fé e espiritualidade.',
                        'Patrimônio cultural religioso.',
                    ],
                    'habilidades' => [
                        ['codigo' => 'EF01ER06', 'descricao' => 'Reconhecer símbolos e celebrações religiosas presentes na comunidade.', 'ano' => 1],
                        ['codigo' => 'EF02ER07', 'descricao' => 'Identificar diferentes espaços e práticas religiosas.', 'ano' => 2],
                        ['codigo' => 'EF03ER08', 'descricao' => 'Valorizar a diversidade das manifestações religiosas e culturais.', 'ano' => 3],
                        ['codigo' => 'EF04ER09', 'descricao' => 'Comparar expressões religiosas, percebendo semelhanças e diferenças.', 'ano' => 4],
                        ['codigo' => 'EF05ER10', 'descricao' => 'Compreender o papel das manifestações religiosas na formação da cultura e da história local.', 'ano' => 5],
                    ],
                ],
                [
                    'titulo' => 'Ensino Religioso — Crenças e Filosofias de Vida',
                    'descricao' => 'Sentidos da vida, crenças religiosas e não religiosas, sabedorias e filosofias.',
                    'objetos' => [
                        'Sentidos e significados da vida humana.',
                        'Crenças religiosas e não religiosas.',
                        'Sabedorias e tradições de diferentes povos.',
                        'Filosofias e modos de pensar a existência.',
                    ],
                    'habilidades' => [
                        ['codigo' => 'EF02ER11', 'descricao' => 'Identificar crenças e valores presentes em sua comunidade.', 'ano' => 2],
                        ['codigo' => 'EF03ER12', 'descricao' => 'Compreender diferentes modos de buscar sentido para a vida.', 'ano' => 3],
                        ['codigo' => 'EF04ER13', 'descricao' => 'Refletir sobre crenças e filosofias que orientam atitudes e comportamentos.', 'ano' => 4],
                        ['codigo' => 'EF05ER14', 'descricao' => 'Valorizar a diversidade de pensamentos e convicções como parte da vida social.', 'ano' => 5],
                    ],
                ],
                [
                    'titulo' => 'Ensino Religioso — Valores e Convivência',
                    'descricao' => 'Valores éticos e morais, solidariedade, justiça, paz, cuidado e responsabilidade.',
                    'objetos' => [
                        'Valores éticos, morais e espirituais.',
                        'Solidariedade, justiça, paz e cuidado com o outro.',
                        'Convivência em comunidade.',
                        'Ações de responsabilidade social.',
                    ],
                    'habilidades' => [
                        ['codigo' => 'EF01ER15', 'descricao' => 'Praticar atitudes de cuidado e solidariedade nas relações diárias.', 'ano' => 1],
                        ['codigo' => 'EF02ER16', 'descricao' => 'Identificar situações de convivência que envolvem escolhas e valores.', 'ano' => 2],
                        ['codigo' => 'EF03ER17', 'descricao' => 'Refletir sobre atitudes que promovem o bem comum e a harmonia social.', 'ano' => 3],
                        ['codigo' => 'EF04ER18', 'descricao' => 'Participar de ações coletivas que expressem valores de respeito e cooperação.', 'ano' => 4],
                        ['codigo' => 'EF05ER19', 'descricao' => 'Valorizar o diálogo, a empatia e o respeito como fundamentos da convivência.', 'ano' => 5],
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
                $this->command->info('BNCC Ensino Religioso EF (Anos Iniciais) populado com sucesso.');
            }
        });
    }
}