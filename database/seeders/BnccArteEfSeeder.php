<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\CampoExperiencia;
use App\Models\SaberConhecimento;
use App\Models\ObjetivoAprendizagem;

class BnccArteEfSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $nivel = 'EF_anos_iniciais';
            $etapa = 'EF_anos_iniciais';

            $unidades = [
                [
                    'titulo' => 'Arte — Artes Visuais',
                    'descricao' => 'Linguagem de artes visuais nos Anos Iniciais: criação, apreciação e reflexão.',
                    'objetos' => [
                        'Elementos visuais (ponto, linha, forma, cor, textura, luz).',
                        'Materiais e técnicas de desenho, pintura, colagem, escultura e modelagem.',
                        'Produções artísticas do cotidiano, da cultura popular e da arte erudita.',
                        'Artistas e obras de diferentes tempos e lugares.',
                    ],
                    'habilidades' => [
                        ['codigo' => 'EF15AR01', 'descricao' => 'Experimentar diferentes materiais, instrumentos e suportes nas produções visuais.', 'ano' => null],
                        ['codigo' => 'EF15AR02', 'descricao' => 'Explorar elementos visuais (cor, forma, linha, textura) em suas criações.', 'ano' => null],
                        ['codigo' => 'EF15AR03', 'descricao' => 'Observar e apreciar obras de arte, reconhecendo diferentes estilos e contextos.', 'ano' => null],
                        ['codigo' => 'EF15AR04', 'descricao' => 'Compartilhar e valorizar suas produções e as dos colegas, respeitando diferentes expressões artísticas.', 'ano' => null],
                        ['codigo' => 'EF15AR05', 'descricao' => 'Reconhecer manifestações artísticas presentes no cotidiano (muralismo, design, arte urbana).', 'ano' => null],
                    ],
                ],
                [
                    'titulo' => 'Arte — Dança',
                    'descricao' => 'Linguagem da dança: movimento, ritmo, criação e identidade cultural.',
                    'objetos' => [
                        'Movimento corporal, ritmo e expressão.',
                        'Danças populares, tradicionais e contemporâneas.',
                        'Criação coreográfica e improvisação.',
                        'A dança como forma de comunicação e identidade cultural.',
                    ],
                    'habilidades' => [
                        ['codigo' => 'EF15AR06', 'descricao' => 'Experimentar movimentos e gestos corporais em danças e brincadeiras rítmicas.', 'ano' => null],
                        ['codigo' => 'EF15AR07', 'descricao' => 'Reconhecer e valorizar danças de diferentes culturas e regiões.', 'ano' => null],
                        ['codigo' => 'EF15AR08', 'descricao' => 'Criar pequenas composições coreográficas com colegas.', 'ano' => null],
                        ['codigo' => 'EF15AR09', 'descricao' => 'Apreciar apresentações de dança e comentar sobre movimentos, ritmos e intenções.', 'ano' => null],
                        ['codigo' => 'EF15AR10', 'descricao' => 'Relacionar dança e expressão corporal com o respeito à diversidade cultural.', 'ano' => null],
                    ],
                ],
                [
                    'titulo' => 'Arte — Música',
                    'descricao' => 'Linguagem musical: som, silêncio, ritmo, melodia, prática e criação.',
                    'objetos' => [
                        'Som, silêncio, ritmo, melodia e harmonia.',
                        'Canto, escuta e execução instrumental.',
                        'Repertório musical popular, regional e erudito.',
                        'A música como expressão cultural e histórica.',
                    ],
                    'habilidades' => [
                        ['codigo' => 'EF15AR11', 'descricao' => 'Explorar sons do corpo, da voz, de objetos e instrumentos.', 'ano' => null],
                        ['codigo' => 'EF15AR12', 'descricao' => 'Identificar e reproduzir diferentes ritmos e melodias.', 'ano' => null],
                        ['codigo' => 'EF15AR13', 'descricao' => 'Participar de práticas musicais coletivas (canto, percussão, jogos sonoros).', 'ano' => null],
                        ['codigo' => 'EF15AR14', 'descricao' => 'Reconhecer diferentes estilos musicais e suas origens culturais.', 'ano' => null],
                        ['codigo' => 'EF15AR15', 'descricao' => 'Criar pequenas composições musicais, utilizando sons do ambiente e instrumentos simples.', 'ano' => null],
                    ],
                ],
                [
                    'titulo' => 'Arte — Teatro',
                    'descricao' => 'Linguagem teatral: jogo, dramatização, expressão corporal e elementos cênicos.',
                    'objetos' => [
                        'Jogo teatral e dramatização.',
                        'Corpo, gesto, voz e expressão.',
                        'Personagens, enredos e narrativas.',
                        'Espaço cênico e elementos da encenação.',
                    ],
                    'habilidades' => [
                        ['codigo' => 'EF15AR16', 'descricao' => 'Participar de jogos teatrais e dramatizações do cotidiano.', 'ano' => null],
                        ['codigo' => 'EF15AR17', 'descricao' => 'Explorar gestos, expressões e falas em pequenas encenações.', 'ano' => null],
                        ['codigo' => 'EF15AR18', 'descricao' => 'Criar coletivamente cenas teatrais baseadas em histórias, músicas ou temas estudados.', 'ano' => null],
                        ['codigo' => 'EF15AR19', 'descricao' => 'Reconhecer o teatro como manifestação artística presente em diferentes culturas.', 'ano' => null],
                        ['codigo' => 'EF15AR20', 'descricao' => 'Apreciar apresentações teatrais, respeitando os colegas e expressando opiniões sobre as cenas.', 'ano' => null],
                    ],
                ],
                [
                    'titulo' => 'Arte — Criação, Apreciação e Reflexão',
                    'descricao' => 'Unidade transversal que integra processos criativos, apreciação e reflexão.',
                    'objetos' => [
                        'Processo criativo e autoria.',
                        'Leitura e análise de produções artísticas.',
                        'Valorização da arte como patrimônio cultural.',
                    ],
                    'habilidades' => [
                        ['codigo' => 'EF15AR21', 'descricao' => 'Refletir sobre suas produções e as dos colegas, desenvolvendo autocrítica e escuta sensível.', 'ano' => null],
                        ['codigo' => 'EF15AR22', 'descricao' => 'Reconhecer o papel da arte na construção da identidade e da cultura local.', 'ano' => null],
                        ['codigo' => 'EF15AR23', 'descricao' => 'Estabelecer relações entre diferentes linguagens artísticas (música e dança, teatro e artes visuais).', 'ano' => null],
                        ['codigo' => 'EF15AR24', 'descricao' => 'Valorizar a arte como meio de expressão, comunicação e transformação social.', 'ano' => null],
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
                    $objetivo->ano = $ano; // EF15: transversal nos anos iniciais (mantido como null)
                    $primeiroObjeto = reset($objetos);
                    if ($primeiroObjeto) {
                        $objetivo->saber_conhecimento_id = $primeiroObjeto->id;
                    }
                    $objetivo->save();
                }
            }

            if (method_exists($this, 'command') && $this->command) {
                $this->command->info('BNCC Arte EF (Anos Iniciais) populado com sucesso.');
            }
        });
    }
}