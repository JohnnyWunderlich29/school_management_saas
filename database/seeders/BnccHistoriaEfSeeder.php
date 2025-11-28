<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\CampoExperiencia;
use App\Models\SaberConhecimento;
use App\Models\ObjetivoAprendizagem;

class BnccHistoriaEfSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $nivel = 'EF_anos_iniciais';
            $etapa = 'EF_anos_iniciais';

            $unidades = [
                [
                    'titulo' => 'O sujeito e seu lugar no mundo',
                    'descricao' => 'Desenvolve o sentido de pertencimento e identidade nos espaços de convivência.',
                    'objetos' => [
                        'A noção de identidade pessoal e coletiva.',
                        'Família, escola e comunidade.',
                        'Relações de pertencimento e convivência.',
                        'Cotidiano, regras e papéis sociais.',
                    ],
                    'habilidades' => [
                        ['codigo' => 'EF01HI01', 'descricao' => 'Identificar e descrever lugares de convivência (casa, escola, bairro) e reconhecer sua importância.', 'ano' => 1],
                        ['codigo' => 'EF01HI02', 'descricao' => 'Reconhecer semelhanças e diferenças entre as pessoas do convívio social.', 'ano' => 1],
                        ['codigo' => 'EF01HI03', 'descricao' => 'Compreender a importância das regras de convivência e do respeito ao outro.', 'ano' => 1],
                        ['codigo' => 'EF02HI04', 'descricao' => 'Relacionar a própria história e a da família com o lugar onde vivem.', 'ano' => 2],
                        ['codigo' => 'EF02HI05', 'descricao' => 'Representar graficamente espaços conhecidos (casa, escola, bairro).', 'ano' => 2],
                        ['codigo' => 'EF03HI06', 'descricao' => 'Identificar formas de registro da história pessoal e coletiva (fotografias, objetos, relatos).', 'ano' => 3],
                        ['codigo' => 'EF03HI07', 'descricao' => 'Reconhecer mudanças e permanências nos modos de viver da comunidade.', 'ano' => 3],
                    ],
                ],
                [
                    'titulo' => 'Conexões e transformações no tempo',
                    'descricao' => 'Aprofunda noções de tempo e a leitura de mudanças e permanências.',
                    'objetos' => [
                        'Noções de tempo: ontem, hoje, amanhã.',
                        'Mudanças e permanências na vida pessoal, familiar e comunitária.',
                        'Fontes históricas: objetos, imagens, documentos, testemunhos orais.',
                        'Passagem do tempo e organização de eventos históricos.',
                    ],
                    'habilidades' => [
                        ['codigo' => 'EF01HI08', 'descricao' => 'Identificar acontecimentos importantes da própria vida e de sua família em ordem cronológica.', 'ano' => 1],
                        ['codigo' => 'EF02HI09', 'descricao' => 'Distinguir passado e presente em diferentes contextos (tecnologias, meios de transporte, hábitos).', 'ano' => 2],
                        ['codigo' => 'EF03HI10', 'descricao' => 'Comparar modos de vida do passado e do presente, valorizando o patrimônio cultural.', 'ano' => 3],
                        ['codigo' => 'EF03HI11', 'descricao' => 'Reconhecer que as transformações históricas ocorrem em diferentes ritmos e lugares.', 'ano' => 3],
                        ['codigo' => 'EF04HI12', 'descricao' => 'Analisar mudanças nas formas de trabalho, transporte, comunicação e moradia.', 'ano' => 4],
                        ['codigo' => 'EF05HI13', 'descricao' => 'Localizar no tempo e no espaço eventos e processos significativos da história do Brasil.', 'ano' => 5],
                    ],
                ],
                [
                    'titulo' => 'As pessoas e os grupos que formam as sociedades',
                    'descricao' => 'Explora diversidade cultural, organização social e direitos humanos.',
                    'objetos' => [
                        'Povos e culturas que formam o Brasil: indígenas, africanas, europeias e outras.',
                        'Diversidade cultural e religiosa.',
                        'Lutas, resistências e direitos humanos.',
                        'Organização política e social.',
                    ],
                    'habilidades' => [
                        ['codigo' => 'EF02HI14', 'descricao' => 'Reconhecer a diversidade cultural nas festas, comidas e costumes da comunidade.', 'ano' => 2],
                        ['codigo' => 'EF03HI15', 'descricao' => 'Identificar grupos que contribuíram para a formação da população brasileira.', 'ano' => 3],
                        ['codigo' => 'EF04HI16', 'descricao' => 'Conhecer aspectos da cultura indígena, africana e europeia e suas contribuições.', 'ano' => 4],
                        ['codigo' => 'EF04HI17', 'descricao' => 'Valorizar o respeito às diferenças culturais e religiosas.', 'ano' => 4],
                        ['codigo' => 'EF05HI18', 'descricao' => 'Analisar a formação do povo brasileiro e compreender a importância da miscigenação.', 'ano' => 5],
                        ['codigo' => 'EF05HI19', 'descricao' => 'Reconhecer lutas e conquistas de diferentes grupos sociais na história do Brasil.', 'ano' => 5],
                    ],
                ],
            ];

            foreach ($unidades as $ordemUnidade => $unidade) {
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
                foreach ($unidade['objetos'] as $idxObj => $objTitulo) {
                    $obj = SaberConhecimento::firstOrCreate(
                        ['campo_experiencia_id' => $campo->id, 'titulo' => $objTitulo],
                        [
                            'descricao' => $objTitulo,
                            'ordem' => $idxObj + 1,
                            'ativo' => true,
                        ]
                    );
                    $objetos[] = $obj;
                }

                $alvo = $objetos[0] ?? null;
                foreach ($unidade['habilidades'] as $idxHab => $hab) {
                    if (!$alvo) {
                        $alvo = SaberConhecimento::firstOrCreate(
                            ['campo_experiencia_id' => $campo->id, 'titulo' => 'Objetos de conhecimento'],
                            [
                                'descricao' => 'Objetos de conhecimento associados à unidade temática.',
                                'ordem' => 1,
                                'ativo' => true,
                            ]
                        );
                    }

                    $objetivo = ObjetivoAprendizagem::firstOrCreate(
                        ['codigo' => $hab['codigo']],
                        [
                            'campo_experiencia_id' => $campo->id,
                            'saber_conhecimento_id' => null,
                            'descricao' => $hab['descricao'],
                            'ativo' => true,
                        ]
                    );

                    $objetivo->campo_experiencia_id = $campo->id;
                    $objetivo->etapa = $etapa;
                    $objetivo->ano = $hab['ano'];
                    if ($alvo) {
                        $objetivo->saber_conhecimento_id = $alvo->id;
                    }
                    $objetivo->save();
                }
            }
        });
    }
}