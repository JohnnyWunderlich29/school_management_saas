<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\CampoExperiencia;
use App\Models\SaberConhecimento;
use App\Models\ObjetivoAprendizagem;

class BnccEducacaoFisicaEfSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $nivel = 'EF_anos_iniciais';
            $etapa = 'EF_anos_iniciais';

            $unidades = [
                [
                    'titulo' => 'Educação Física — Brincadeiras e Jogos',
                    'descricao' => 'Práticas corporais lúdicas, jogos de regras, cooperação e respeito.',
                    'objetos' => [
                        'Brincadeiras tradicionais e populares.',
                        'Jogos simbólicos e de regras.',
                        'Jogos cooperativos e competitivos.',
                        'Respeito às diferenças e à convivência.',
                    ],
                    'habilidades' => [
                        ['codigo' => 'EF01EF01', 'descricao' => 'Experimentar e fruir brincadeiras e jogos, valorizando a convivência e o respeito mútuo.', 'ano' => 1],
                        ['codigo' => 'EF02EF02', 'descricao' => 'Identificar brincadeiras e jogos de diferentes culturas e lugares.', 'ano' => 2],
                        ['codigo' => 'EF03EF03', 'descricao' => 'Adaptar regras e materiais de brincadeiras, reconhecendo possibilidades criativas.', 'ano' => 3],
                        ['codigo' => 'EF04EF04', 'descricao' => 'Compreender e aplicar regras de convivência e de jogos, respeitando adversários.', 'ano' => 4],
                        ['codigo' => 'EF05EF05', 'descricao' => 'Comparar brincadeiras e jogos do passado e do presente, identificando mudanças e permanências.', 'ano' => 5],
                    ],
                ],
                [
                    'titulo' => 'Educação Física — Esportes',
                    'descricao' => 'Fundamentos básicos, regras, papéis, cooperação e ética esportiva.',
                    'objetos' => [
                        'Esportes individuais, coletivos e de rede/parede.',
                        'Regras básicas e papéis nos jogos.',
                        'Estratégias de cooperação e competição.',
                        'Respeito e ética esportiva.',
                    ],
                    'habilidades' => [
                        ['codigo' => 'EF02EF06', 'descricao' => 'Experimentar esportes adaptados à faixa etária, valorizando o brincar e a cooperação.', 'ano' => 2],
                        ['codigo' => 'EF03EF07', 'descricao' => 'Reconhecer regras simples e papéis nos esportes.', 'ano' => 3],
                        ['codigo' => 'EF04EF08', 'descricao' => 'Aplicar fundamentos básicos de esportes coletivos (lançar, chutar, arremessar).', 'ano' => 4],
                        ['codigo' => 'EF05EF09', 'descricao' => 'Valorizar o trabalho em equipe e a ética nas práticas esportivas.', 'ano' => 5],
                    ],
                ],
                [
                    'titulo' => 'Educação Física — Danças',
                    'descricao' => 'Danças populares, regionais, urbanas e folclóricas; ritmo e expressão.',
                    'objetos' => [
                        'Danças populares, regionais, urbanas e folclóricas.',
                        'Ritmo, coordenação e expressão corporal.',
                        'Significados culturais das danças.',
                        'Apreciação e criação coreográfica.',
                    ],
                    'habilidades' => [
                        ['codigo' => 'EF01EF10', 'descricao' => 'Participar de danças e jogos rítmicos, explorando movimentos corporais.', 'ano' => 1],
                        ['codigo' => 'EF02EF11', 'descricao' => 'Reconhecer danças de diferentes regiões e culturas.', 'ano' => 2],
                        ['codigo' => 'EF03EF12', 'descricao' => 'Criar pequenas coreografias coletivas, valorizando a expressão individual.', 'ano' => 3],
                        ['codigo' => 'EF04EF13', 'descricao' => 'Apreciar apresentações de dança, reconhecendo diversidade cultural.', 'ano' => 4],
                        ['codigo' => 'EF05EF14', 'descricao' => 'Compor apresentações com base em danças regionais e populares.', 'ano' => 5],
                    ],
                ],
                [
                    'titulo' => 'Educação Física — Lutas',
                    'descricao' => 'Princípios das lutas, regras de segurança, respeito, cultura e diferenciações.',
                    'objetos' => [
                        'Movimentos e princípios das lutas.',
                        'Regras e valores de respeito e segurança.',
                        'Lutas do cotidiano e da cultura brasileira.',
                        'Diferença entre luta, briga e violência.',
                    ],
                    'habilidades' => [
                        ['codigo' => 'EF02EF15', 'descricao' => 'Reconhecer movimentos corporais presentes em brincadeiras de luta.', 'ano' => 2],
                        ['codigo' => 'EF03EF16', 'descricao' => 'Identificar regras de segurança e respeito nas práticas de luta.', 'ano' => 3],
                        ['codigo' => 'EF04EF17', 'descricao' => 'Experimentar lutas de forma cooperativa e controlada.', 'ano' => 4],
                        ['codigo' => 'EF05EF18', 'descricao' => 'Valorizar o respeito mútuo e a disciplina nas práticas corporais de luta.', 'ano' => 5],
                    ],
                ],
                [
                    'titulo' => 'Educação Física — Ginásticas',
                    'descricao' => 'Condicionamento físico, consciência corporal, equilíbrio, saúde e bem-estar.',
                    'objetos' => [
                        'Ginásticas de condicionamento físico, de conscientização corporal e de equilíbrio.',
                        'Expressões corporais e controle postural.',
                        'Cuidados com o corpo e saúde.',
                        'Atividades rítmicas e acrobáticas.',
                    ],
                    'habilidades' => [
                        ['codigo' => 'EF01EF19', 'descricao' => 'Experimentar movimentos de equilíbrio, força e flexibilidade.', 'ano' => 1],
                        ['codigo' => 'EF02EF20', 'descricao' => 'Reconhecer a importância dos cuidados corporais e posturais.', 'ano' => 2],
                        ['codigo' => 'EF03EF21', 'descricao' => 'Participar de circuitos e desafios de movimento com segurança.', 'ano' => 3],
                        ['codigo' => 'EF04EF22', 'descricao' => 'Identificar relações entre atividade física, saúde e bem-estar.', 'ano' => 4],
                        ['codigo' => 'EF05EF23', 'descricao' => 'Criar sequências de movimentos corporais com equilíbrio e coordenação.', 'ano' => 5],
                    ],
                ],
                [
                    'titulo' => 'Educação Física — Práticas Corporais de Aventura',
                    'descricao' => 'Atividades de exploração, contato com a natureza, cooperação e segurança.',
                    'objetos' => [
                        'Atividades de exploração, desafio e superação.',
                        'Relação entre corpo e natureza.',
                        'Cooperação e segurança em práticas ao ar livre.',
                        'Jogos e brincadeiras de aventura.',
                    ],
                    'habilidades' => [
                        ['codigo' => 'EF03EF24', 'descricao' => 'Participar de jogos e brincadeiras que envolvem desafios corporais.', 'ano' => 3],
                        ['codigo' => 'EF04EF25', 'descricao' => 'Identificar atitudes de segurança e cuidado em práticas corporais.', 'ano' => 4],
                        ['codigo' => 'EF05EF26', 'descricao' => 'Valorizar o contato com a natureza e o trabalho em grupo em atividades de aventura.', 'ano' => 5],
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
                $this->command->info('BNCC Educação Física EF (Anos Iniciais) populado com sucesso.');
            }
        });
    }
}