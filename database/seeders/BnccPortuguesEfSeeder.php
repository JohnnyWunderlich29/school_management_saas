<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\CampoExperiencia;
use App\Models\SaberConhecimento;
use App\Models\ObjetivoAprendizagem;

class BnccPortuguesEfSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $nivelEf = 'EF_anos_iniciais';

            // Definição dos Campos, Objetos e Habilidades (simplificado conforme documentação)
            $mapa = [
                [
                    'campo' => [
                        'nome' => 'Vida Cotidiana',
                        'descricao' => 'Práticas de linguagem relacionadas ao convívio social, à escola e à vida cotidiana.',
                    ],
                    'objetos' => [
                        'Troca de experiências e saberes',
                        'Conversas e relatos orais',
                        'Bilhetes, avisos, recados e instruções',
                        'Leitura e escrita de textos de uso diário',
                    ],
                    'habilidades' => [
                        ['codigo' => 'EF01LP01', 'descricao' => 'Ouvir, compreender e reproduzir oralmente textos da vida cotidiana (bilhetes, avisos, recados).', 'ano' => 1],
                        ['codigo' => 'EF02LP02', 'descricao' => 'Planejar e produzir textos de uso social com ajuda do professor.', 'ano' => 2],
                        ['codigo' => 'EF03LP03', 'descricao' => 'Reescrever textos, ajustando linguagem e ortografia.', 'ano' => 3],
                        ['codigo' => 'EF04LP04', 'descricao' => 'Identificar propósitos e contextos de textos cotidianos.', 'ano' => 4],
                        ['codigo' => 'EF05LP05', 'descricao' => 'Produzir textos com clareza e adequação à situação comunicativa.', 'ano' => 5],
                    ],
                ],
                [
                    'campo' => [
                        'nome' => 'Artístico-Literário',
                        'descricao' => 'Contato com diferentes manifestações culturais e literárias (contos, poemas, canções, etc.).',
                    ],
                    'objetos' => [
                        'Leitura e escuta de textos literários',
                        'Fruição estética e apreciação da literatura',
                        'Produção de textos literários orais e escritos',
                    ],
                    'habilidades' => [
                        ['codigo' => 'EF01LP06', 'descricao' => 'Escutar e compreender narrativas curtas e poemas.', 'ano' => 1],
                        ['codigo' => 'EF02LP07', 'descricao' => 'Identificar personagens, enredo e espaço em histórias.', 'ano' => 2],
                        ['codigo' => 'EF03LP08', 'descricao' => 'Recontar textos literários com coerência e entonação adequada.', 'ano' => 3],
                        ['codigo' => 'EF04LP09', 'descricao' => 'Produzir textos narrativos curtos, com estrutura básica (início, meio e fim).', 'ano' => 4],
                        ['codigo' => 'EF05LP10', 'descricao' => 'Ler e interpretar textos poéticos, narrativos e dramáticos, reconhecendo efeitos de sentido.', 'ano' => 5],
                    ],
                ],
                [
                    'campo' => [
                        'nome' => 'Práticas de Estudo e Pesquisa',
                        'descricao' => 'Uso da linguagem para buscar, registrar e organizar informações.',
                    ],
                    'objetos' => [
                        'Leitura e escrita de textos informativos',
                        'Estratégias de busca e seleção de informações',
                        'Organização de anotações e registros',
                    ],
                    'habilidades' => [
                        ['codigo' => 'EF02LP11', 'descricao' => 'Localizar informações explícitas em textos informativos.', 'ano' => 2],
                        ['codigo' => 'EF03LP12', 'descricao' => 'Registrar informações com apoio de imagens, legendas e listas.', 'ano' => 3],
                        ['codigo' => 'EF04LP13', 'descricao' => 'Produzir pequenos textos expositivos baseados em pesquisa.', 'ano' => 4],
                        ['codigo' => 'EF05LP14', 'descricao' => 'Planejar e escrever textos informativos com base em fontes confiáveis.', 'ano' => 5],
                    ],
                ],
                [
                    'campo' => [
                        'nome' => 'Jornalístico-Midiático',
                        'descricao' => 'Divulgação de informações, notícias e opinião; leitura crítica dos meios de comunicação.',
                    ],
                    'objetos' => [
                        'Notícia, reportagem e entrevista',
                        'Elementos gráficos e visuais dos textos jornalísticos',
                        'Linguagem oral e escrita no contexto midiático',
                    ],
                    'habilidades' => [
                        ['codigo' => 'EF03LP15', 'descricao' => 'Reconhecer a finalidade de notícias e reportagens.', 'ano' => 3],
                        ['codigo' => 'EF04LP16', 'descricao' => 'Identificar manchete, título, imagem e legenda em textos jornalísticos.', 'ano' => 4],
                        ['codigo' => 'EF05LP17', 'descricao' => 'Produzir pequenas reportagens ou entrevistas, com apoio do professor.', 'ano' => 5],
                    ],
                ],
                [
                    'campo' => [
                        'nome' => 'Vida Pública',
                        'descricao' => 'Práticas de linguagem voltadas à convivência social, cidadania e expressão de opinião.',
                    ],
                    'objetos' => [
                        'Textos de opinião, debates e cartas de leitor',
                        'Argumentação e escuta respeitosa',
                        'Produção oral e escrita voltada à participação social',
                    ],
                    'habilidades' => [
                        ['codigo' => 'EF03LP18', 'descricao' => 'Participar de conversas e discussões respeitando turnos de fala.', 'ano' => 3],
                        ['codigo' => 'EF04LP19', 'descricao' => 'Expressar opiniões com justificativas sobre temas de interesse da turma.', 'ano' => 4],
                        ['codigo' => 'EF05LP20', 'descricao' => 'Produzir textos argumentativos simples, considerando interlocutor e objetivo comunicativo.', 'ano' => 5],
                    ],
                ],
                // Transversais: Leitura e Escrita
                [
                    'campo' => [
                        'nome' => 'Habilidades Gerais — Leitura e Escrita (Transversal)',
                        'descricao' => 'Habilidades gerais de leitura e escrita aplicáveis em múltiplos campos.',
                    ],
                    'objetos' => [
                        'Leitura',
                        'Escrita',
                    ],
                    'habilidades' => [
                        ['codigo' => 'EF01LP21', 'descricao' => 'Reconhecer o alfabeto e utilizar estratégias de decodificação.', 'ano' => 1],
                        ['codigo' => 'EF02LP22', 'descricao' => 'Ler palavras e frases com fluência e compreensão.', 'ano' => 2],
                        ['codigo' => 'EF03LP23', 'descricao' => 'Ler textos curtos com autonomia, identificando ideia principal.', 'ano' => 3],
                        ['codigo' => 'EF04LP24', 'descricao' => 'Ler textos de diferentes gêneros, inferindo informações implícitas.', 'ano' => 4],
                        ['codigo' => 'EF05LP25', 'descricao' => 'Desenvolver leitura crítica, relacionando textos com o contexto social.', 'ano' => 5],
                        ['codigo' => 'EF01LP26', 'descricao' => 'Escrever palavras e frases legíveis.', 'ano' => 1],
                        ['codigo' => 'EF02LP27', 'descricao' => 'Produzir pequenos textos com coerência e pontuação básica.', 'ano' => 2],
                        ['codigo' => 'EF03LP28', 'descricao' => 'Revisar e reescrever textos, corrigindo ortografia e estrutura.', 'ano' => 3],
                        ['codigo' => 'EF04LP29', 'descricao' => 'Produzir textos narrativos, descritivos e informativos, revisando coletivamente.', 'ano' => 4],
                        ['codigo' => 'EF05LP30', 'descricao' => 'Produzir textos de diferentes gêneros com clareza e correção gramatical.', 'ano' => 5],
                    ],
                ],
                // Transversal: Oralidade
                [
                    'campo' => [
                        'nome' => 'Oralidade (Transversal)',
                        'descricao' => 'Desenvolvimento da oralidade em situações diversas e públicas de fala.',
                    ],
                    'objetos' => [
                        'Oralidade',
                    ],
                    'habilidades' => [
                        ['codigo' => 'EF01LP31', 'descricao' => 'Ouvir e compreender mensagens orais.', 'ano' => 1],
                        ['codigo' => 'EF02LP32', 'descricao' => 'Participar de conversas e apresentações curtas.', 'ano' => 2],
                        ['codigo' => 'EF03LP33', 'descricao' => 'Apresentar oralmente textos memorizados (poemas, parlendas).', 'ano' => 3],
                        ['codigo' => 'EF04LP34', 'descricao' => 'Planejar e realizar pequenas apresentações orais.', 'ano' => 4],
                        ['codigo' => 'EF05LP35', 'descricao' => 'Argumentar e defender ideias com clareza em situações públicas de fala.', 'ano' => 5],
                    ],
                ],
            ];

            foreach ($mapa as $grupo) {
                // Campo
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

                // Objetos de conhecimento
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

                // Habilidades (BNCC)
                foreach ($grupo['habilidades'] as $hab) {
                    $codigo = $hab['codigo'];
                    $descricao = $hab['descricao'];
                    $ano = $hab['ano'];

                    $objetivo = ObjetivoAprendizagem::firstOrCreate(
                        ['codigo' => $codigo],
                        [
                            'campo_experiencia_id' => $campo->id,
                            'saber_conhecimento_id' => null, // vínculo opcional
                            'descricao' => $descricao,
                            'ativo' => true,
                        ]
                    );

                    // Garantir vínculos e metadados
                    $objetivo->campo_experiencia_id = $campo->id;
                    $objetivo->etapa = $nivelEf;
                    $objetivo->ano = $ano;
                    // Opcionalmente, vincular ao primeiro objeto do grupo
                    $primeiroObjeto = reset($objetos);
                    if ($primeiroObjeto) {
                        $objetivo->saber_conhecimento_id = $primeiroObjeto->id;
                    }
                    $objetivo->save();
                }
            }

            if (method_exists($this, 'command') && $this->command) {
                $this->command->info('BNCC Português EF (Anos Iniciais) populado com sucesso.');
            }
        });
    }
}