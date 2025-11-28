<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\CampoExperiencia;
use App\Models\SaberConhecimento;
use App\Models\ObjetivoAprendizagem;

class BnccCienciasEfSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $nivel = 'EF_anos_iniciais';
            $etapa = 'EF_anos_iniciais';

            $unidades = [
                [
                    'titulo' => 'Matéria e Energia',
                    'descricao' => 'Unidade Temática de Ciências – Matéria e Energia (EF – anos iniciais).',
                    'objetos' => [
                        'Características e propriedades dos materiais.',
                        'Estados físicos e transformações da matéria.',
                        'Fontes e usos de energia no cotidiano.',
                        'Luz, som, calor e eletricidade como formas de energia.',
                    ],
                    'habilidades' => [
                        ['codigo' => 'EF01CI01', 'descricao' => 'Reconhecer diferentes materiais presentes no ambiente e seus usos.', 'ano' => 1],
                        ['codigo' => 'EF02CI02', 'descricao' => 'Observar e comparar propriedades dos materiais (cor, textura, dureza, flexibilidade).', 'ano' => 2],
                        ['codigo' => 'EF03CI03', 'descricao' => 'Identificar transformações reversíveis e irreversíveis em materiais (dissolver, derreter, queimar etc.).', 'ano' => 3],
                        ['codigo' => 'EF04CI04', 'descricao' => 'Compreender que o calor, a luz e o som são formas de energia presentes no cotidiano.', 'ano' => 4],
                        ['codigo' => 'EF04CI05', 'descricao' => 'Reconhecer fontes de energia (solar, elétrica, eólica) e seus usos.', 'ano' => 4],
                        ['codigo' => 'EF05CI06', 'descricao' => 'Investigar o consumo de energia elétrica e propor atitudes de uso consciente.', 'ano' => 5],
                    ],
                ],
                [
                    'titulo' => 'Vida e Evolução',
                    'descricao' => 'Unidade Temática de Ciências – Vida e Evolução (EF – anos iniciais).',
                    'objetos' => [
                        'Seres vivos e ambiente.',
                        'Características, necessidades e ciclos de vida dos seres vivos.',
                        'Corpo humano e saúde.',
                        'Alimentação, higiene e cuidados com o corpo.',
                        'Relações entre os seres vivos e o meio ambiente.',
                    ],
                    'habilidades' => [
                        ['codigo' => 'EF01CI07', 'descricao' => 'Identificar partes do corpo humano e suas funções básicas.', 'ano' => 1],
                        ['codigo' => 'EF01CI08', 'descricao' => 'Reconhecer hábitos de higiene, alimentação e autocuidado.', 'ano' => 1],
                        ['codigo' => 'EF02CI09', 'descricao' => 'Comparar diferentes seres vivos quanto a características externas e ambientes de vida.', 'ano' => 2],
                        ['codigo' => 'EF02CI10', 'descricao' => 'Descrever semelhanças e diferenças entre plantas e animais.', 'ano' => 2],
                        ['codigo' => 'EF03CI11', 'descricao' => 'Identificar fases do ciclo de vida de plantas e animais.', 'ano' => 3],
                        ['codigo' => 'EF03CI12', 'descricao' => 'Reconhecer que o ser humano modifica o ambiente e deve cuidar dele.', 'ano' => 3],
                        ['codigo' => 'EF04CI13', 'descricao' => 'Compreender a importância da alimentação equilibrada e do exercício físico.', 'ano' => 4],
                        ['codigo' => 'EF04CI14', 'descricao' => 'Identificar órgãos e sistemas do corpo humano e suas funções.', 'ano' => 4],
                        ['codigo' => 'EF05CI15', 'descricao' => 'Analisar as relações entre os seres vivos em ecossistemas (cadeias alimentares, equilíbrio ambiental).', 'ano' => 5],
                        ['codigo' => 'EF05CI16', 'descricao' => 'Discutir ações humanas que impactam o ambiente e propor atitudes sustentáveis.', 'ano' => 5],
                    ],
                ],
                [
                    'titulo' => 'Terra e Universo',
                    'descricao' => 'Unidade Temática de Ciências – Terra e Universo (EF – anos iniciais).',
                    'objetos' => [
                        'Elementos da natureza (solo, água, ar).',
                        'Movimentos da Terra e observação do céu.',
                        'Fenômenos naturais e mudanças climáticas.',
                        'Sustentabilidade e uso responsável dos recursos naturais.',
                    ],
                    'habilidades' => [
                        ['codigo' => 'EF01CI17', 'descricao' => 'Observar o céu, o Sol, a Lua e as nuvens, registrando variações ao longo do tempo.', 'ano' => 1],
                        ['codigo' => 'EF02CI18', 'descricao' => 'Reconhecer que o dia e a noite resultam do movimento aparente do Sol.', 'ano' => 2],
                        ['codigo' => 'EF02CI19', 'descricao' => 'Identificar elementos naturais do ambiente (água, solo, ar, plantas, animais).', 'ano' => 2],
                        ['codigo' => 'EF03CI20', 'descricao' => 'Relacionar condições do tempo (chuva, vento, calor) com atividades cotidianas.', 'ano' => 3],
                        ['codigo' => 'EF04CI21', 'descricao' => 'Reconhecer os principais componentes do Sistema Solar e suas características.', 'ano' => 4],
                        ['codigo' => 'EF04CI22', 'descricao' => 'Explicar a importância do Sol para a vida na Terra.', 'ano' => 4],
                        ['codigo' => 'EF05CI23', 'descricao' => 'Investigar o ciclo da água e sua importância para os seres vivos.', 'ano' => 5],
                        ['codigo' => 'EF05CI24', 'descricao' => 'Discutir formas de preservação dos recursos naturais e descarte adequado de resíduos.', 'ano' => 5],
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
                        // Garante um objeto de conhecimento para vínculo
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