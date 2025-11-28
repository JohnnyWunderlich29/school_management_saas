<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ObjetivosAprendizagemEI02Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $camposExperiencia = DB::table('campos_experiencia')->pluck('id', 'nome');

        $agora = now();
        $faixa = 'criancas_bem_pequenas'; // EI02

        $objetivos = [
            // O eu, o outro e o nós
            [
                'campo_experiencia_id' => $camposExperiencia['O eu, o outro e o nós'] ?? null,
                'codigo' => 'EI02EO01',
                'descricao' => 'Demonstrar atitudes de cuidado e solidariedade nas interações com crianças e adultos.',
                'faixa_etaria' => $faixa,
                'ativo' => true,
                'created_at' => $agora,
                'updated_at' => $agora,
            ],
            [
                'campo_experiencia_id' => $camposExperiencia['O eu, o outro e o nós'] ?? null,
                'codigo' => 'EI02EO02',
                'descricao' => 'Ampliar progressivamente a autonomia nas ações do cotidiano.',
                'faixa_etaria' => $faixa,
                'ativo' => true,
                'created_at' => $agora,
                'updated_at' => $agora,
            ],
            [
                'campo_experiencia_id' => $camposExperiencia['O eu, o outro e o nós'] ?? null,
                'codigo' => 'EI02EO03',
                'descricao' => 'Estabelecer relações de respeito com outras crianças e adultos.',
                'faixa_etaria' => $faixa,
                'ativo' => true,
                'created_at' => $agora,
                'updated_at' => $agora,
            ],
            [
                'campo_experiencia_id' => $camposExperiencia['O eu, o outro e o nós'] ?? null,
                'codigo' => 'EI02EO04',
                'descricao' => 'Reconhecer e valorizar características pessoais, respeitando diferenças entre as pessoas.',
                'faixa_etaria' => $faixa,
                'ativo' => true,
                'created_at' => $agora,
                'updated_at' => $agora,
            ],
            [
                'campo_experiencia_id' => $camposExperiencia['O eu, o outro e o nós'] ?? null,
                'codigo' => 'EI02EO05',
                'descricao' => 'Identificar situações de conflito e buscar a mediação do adulto para resolvê-las.',
                'faixa_etaria' => $faixa,
                'ativo' => true,
                'created_at' => $agora,
                'updated_at' => $agora,
            ],

            // Corpo, gestos e movimentos
            [
                'campo_experiencia_id' => $camposExperiencia['Corpo, gestos e movimentos'] ?? null,
                'codigo' => 'EI02CG01',
                'descricao' => 'Experimentar movimentos de correr, pular, subir, escorregar, arremessar, equilibrar-se, etc.',
                'faixa_etaria' => $faixa,
                'ativo' => true,
                'created_at' => $agora,
                'updated_at' => $agora,
            ],
            [
                'campo_experiencia_id' => $camposExperiencia['Corpo, gestos e movimentos'] ?? null,
                'codigo' => 'EI02CG02',
                'descricao' => 'Utilizar gestos e movimentos corporais em brincadeiras, danças e jogos.',
                'faixa_etaria' => $faixa,
                'ativo' => true,
                'created_at' => $agora,
                'updated_at' => $agora,
            ],
            [
                'campo_experiencia_id' => $camposExperiencia['Corpo, gestos e movimentos'] ?? null,
                'codigo' => 'EI02CG03',
                'descricao' => 'Manipular diferentes objetos, percebendo suas características e funções.',
                'faixa_etaria' => $faixa,
                'ativo' => true,
                'created_at' => $agora,
                'updated_at' => $agora,
            ],
            [
                'campo_experiencia_id' => $camposExperiencia['Corpo, gestos e movimentos'] ?? null,
                'codigo' => 'EI02CG04',
                'descricao' => 'Utilizar movimentos de coordenação motora fina em atividades de exploração de objetos, brinquedos e materiais.',
                'faixa_etaria' => $faixa,
                'ativo' => true,
                'created_at' => $agora,
                'updated_at' => $agora,
            ],

            // Traços, sons, cores e formas
            [
                'campo_experiencia_id' => $camposExperiencia['Traços, sons, cores e formas'] ?? null,
                'codigo' => 'EI02TS01',
                'descricao' => 'Produzir marcas gráficas, desenhos e construções utilizando diferentes materiais.',
                'faixa_etaria' => $faixa,
                'ativo' => true,
                'created_at' => $agora,
                'updated_at' => $agora,
            ],
            [
                'campo_experiencia_id' => $camposExperiencia['Traços, sons, cores e formas'] ?? null,
                'codigo' => 'EI02TS02',
                'descricao' => 'Explorar sons, ritmos, gestos e movimentos em brincadeiras, canções e jogos.',
                'faixa_etaria' => $faixa,
                'ativo' => true,
                'created_at' => $agora,
                'updated_at' => $agora,
            ],
            [
                'campo_experiencia_id' => $camposExperiencia['Traços, sons, cores e formas'] ?? null,
                'codigo' => 'EI02TS03',
                'descricao' => 'Utilizar diferentes instrumentos, objetos e materiais para produzir sons e ritmos.',
                'faixa_etaria' => $faixa,
                'ativo' => true,
                'created_at' => $agora,
                'updated_at' => $agora,
            ],
            [
                'campo_experiencia_id' => $camposExperiencia['Traços, sons, cores e formas'] ?? null,
                'codigo' => 'EI02TS04',
                'descricao' => 'Experimentar o uso de materiais artísticos em produções individuais e coletivas.',
                'faixa_etaria' => $faixa,
                'ativo' => true,
                'created_at' => $agora,
                'updated_at' => $agora,
            ],

            // Escuta, fala, pensamento e imaginação
            [
                'campo_experiencia_id' => $camposExperiencia['Escuta, fala, pensamento e imaginação'] ?? null,
                'codigo' => 'EI02EF01',
                'descricao' => 'Expressar desejos, necessidades, sentimentos e opiniões em interações com adultos e crianças.',
                'faixa_etaria' => $faixa,
                'ativo' => true,
                'created_at' => $agora,
                'updated_at' => $agora,
            ],
            [
                'campo_experiencia_id' => $camposExperiencia['Escuta, fala, pensamento e imaginação'] ?? null,
                'codigo' => 'EI02EF02',
                'descricao' => 'Participar de situações de leitura de histórias, manifestando preferência por personagens ou acontecimentos.',
                'faixa_etaria' => $faixa,
                'ativo' => true,
                'created_at' => $agora,
                'updated_at' => $agora,
            ],
            [
                'campo_experiencia_id' => $camposExperiencia['Escuta, fala, pensamento e imaginação'] ?? null,
                'codigo' => 'EI02EF03',
                'descricao' => 'Recontar, com apoio de imagens ou objetos, trechos de histórias ou acontecimentos.',
                'faixa_etaria' => $faixa,
                'ativo' => true,
                'created_at' => $agora,
                'updated_at' => $agora,
            ],
            [
                'campo_experiencia_id' => $camposExperiencia['Escuta, fala, pensamento e imaginação'] ?? null,
                'codigo' => 'EI02EF04',
                'descricao' => 'Utilizar a linguagem oral para interagir e ampliar a comunicação.',
                'faixa_etaria' => $faixa,
                'ativo' => true,
                'created_at' => $agora,
                'updated_at' => $agora,
            ],
            [
                'campo_experiencia_id' => $camposExperiencia['Escuta, fala, pensamento e imaginação'] ?? null,
                'codigo' => 'EI02EF05',
                'descricao' => 'Participar de brincadeiras de faz-de-conta, imitando papéis e situações do cotidiano.',
                'faixa_etaria' => $faixa,
                'ativo' => true,
                'created_at' => $agora,
                'updated_at' => $agora,
            ],

            // Espaços, tempos, quantidades, relações e transformações
            [
                'campo_experiencia_id' => $camposExperiencia['Espaços, tempos, quantidades, relações e transformações'] ?? null,
                'codigo' => 'EI02ET01',
                'descricao' => 'Estabelecer relações entre objetos, observando semelhanças e diferenças.',
                'faixa_etaria' => $faixa,
                'ativo' => true,
                'created_at' => $agora,
                'updated_at' => $agora,
            ],
            [
                'campo_experiencia_id' => $camposExperiencia['Espaços, tempos, quantidades, relações e transformações'] ?? null,
                'codigo' => 'EI02ET02',
                'descricao' => 'Reconhecer e utilizar noções de espaço em deslocamentos (dentro/fora, em cima/embaixo, perto/longe).',
                'faixa_etaria' => $faixa,
                'ativo' => true,
                'created_at' => $agora,
                'updated_at' => $agora,
            ],
            [
                'campo_experiencia_id' => $camposExperiencia['Espaços, tempos, quantidades, relações e transformações'] ?? null,
                'codigo' => 'EI02ET03',
                'descricao' => 'Vivenciar a sequência de atividades cotidianas, reconhecendo a noção de antes e depois.',
                'faixa_etaria' => $faixa,
                'ativo' => true,
                'created_at' => $agora,
                'updated_at' => $agora,
            ],
            [
                'campo_experiencia_id' => $camposExperiencia['Espaços, tempos, quantidades, relações e transformações'] ?? null,
                'codigo' => 'EI02ET04',
                'descricao' => 'Estabelecer relações de quantidade em situações do cotidiano (um, dois, muitos; pouco, muito).',
                'faixa_etaria' => $faixa,
                'ativo' => true,
                'created_at' => $agora,
                'updated_at' => $agora,
            ],
            [
                'campo_experiencia_id' => $camposExperiencia['Espaços, tempos, quantidades, relações e transformações'] ?? null,
                'codigo' => 'EI02ET05',
                'descricao' => 'Explorar transformações em objetos e materiais (misturar, empilhar, desmontar, etc.).',
                'faixa_etaria' => $faixa,
                'ativo' => true,
                'created_at' => $agora,
                'updated_at' => $agora,
            ],
        ];

        // Inserir garantindo idempotência por código (compatível sem índice único)
        foreach ($objetivos as $obj) {
            DB::table('objetivos_aprendizagem')->updateOrInsert(
                ['codigo' => $obj['codigo']],
                [
                    'campo_experiencia_id' => $obj['campo_experiencia_id'],
                    'descricao' => $obj['descricao'],
                    'faixa_etaria' => $obj['faixa_etaria'],
                    'ativo' => $obj['ativo'],
                    'created_at' => $obj['created_at'],
                    'updated_at' => $obj['updated_at'],
                ]
            );
        }

        // Vincular objetivos EI02EOxx aos saberes da área "O eu, o outro e o nós", se existirem
        $campoEO = $camposExperiencia['O eu, o outro e o nós'] ?? null;
        if ($campoEO) {
            $saberesEO = DB::table('saberes_conhecimentos')
                ->where('campo_experiencia_id', $campoEO)
                ->pluck('id', 'titulo');

            if ($saberesEO && $saberesEO->count()) {
                $map = [
                    'EI02EO01' => 'Expressão de emoções e sentimentos',
                    'EI02EO02' => 'Construção da identidade e reconhecimento de si',
                    'EI02EO03' => 'Relações de amizade com outras crianças',
                    'EI02EO04' => 'Construção da identidade e reconhecimento de si',
                    'EI02EO05' => 'Primeiras regras de convivência e partilha',
                ];

                foreach ($map as $codigo => $saberTitulo) {
                    $saberId = $saberesEO[$saberTitulo] ?? null;
                    if ($saberId) {
                        DB::table('objetivos_aprendizagem')
                            ->where('codigo', $codigo)
                            ->update(['saber_conhecimento_id' => $saberId, 'updated_at' => now()]);
                    }
                }
            }
        }

        // Vincular objetivos EI02CGxx aos saberes da área "Corpo, gestos e movimentos", se existirem
        $campoCG = $camposExperiencia['Corpo, gestos e movimentos'] ?? null;
        if ($campoCG) {
            $saberesCG = DB::table('saberes_conhecimentos')
                ->where('campo_experiencia_id', $campoCG)
                ->pluck('id', 'titulo');

            if ($saberesCG && $saberesCG->count()) {
                $mapCG = [
                    'EI02CG01' => 'Domínio progressivo da coordenação motora ampla (correr, pular, subir, descer)',
                    'EI02CG02' => 'Brincadeiras de movimento coletivo',
                    'EI02CG03' => 'Coordenação motora fina (pegar objetos pequenos, empilhar, encaixar)',
                    'EI02CG04' => 'Coordenação motora fina (pegar objetos pequenos, empilhar, encaixar)',
                ];

                foreach ($mapCG as $codigo => $saberTitulo) {
                    $saberId = $saberesCG[$saberTitulo] ?? null;
                    if ($saberId) {
                        DB::table('objetivos_aprendizagem')
                            ->where('codigo', $codigo)
                            ->update(['saber_conhecimento_id' => $saberId, 'updated_at' => now()]);
                    }
                }
            }
        }

        // Vincular objetivos EI02TSxx aos saberes da área "Traços, sons, cores e formas", se existirem
        $campoTS = $camposExperiencia['Traços, sons, cores e formas'] ?? null;
        if ($campoTS) {
            $saberesTS = DB::table('saberes_conhecimentos')
                ->where('campo_experiencia_id', $campoTS)
                ->pluck('id', 'titulo');

            if ($saberesTS && $saberesTS->count()) {
                $mapTS = [
                    'EI02TS01' => 'Produção de rabiscos intencionais e reconhecimento de marcas próprias.',
                    'EI02TS02' => 'Expressão musical e rítmica com corpo e instrumentos.',
                    'EI02TS03' => 'Expressão musical e rítmica com corpo e instrumentos.',
                    'EI02TS04' => 'Exploração de cores, formas e texturas.',
                ];

                foreach ($mapTS as $codigo => $saberTitulo) {
                    $saberId = $saberesTS[$saberTitulo] ?? null;
                    if ($saberId) {
                        DB::table('objetivos_aprendizagem')
                            ->where('codigo', $codigo)
                            ->update(['saber_conhecimento_id' => $saberId, 'updated_at' => now()]);
                    }
                }
            }
        }

        // Vincular objetivos EI02EFxx aos saberes da área "Escuta, fala, pensamento e imaginação", se existirem
        $campoEF = $camposExperiencia['Escuta, fala, pensamento e imaginação'] ?? null;
        if ($campoEF) {
            $saberesEF = DB::table('saberes_conhecimentos')
                ->where('campo_experiencia_id', $campoEF)
                ->pluck('id', 'titulo');

            if ($saberesEF && $saberesEF->count()) {
                $mapEF = [
                    'EI02EF01' => 'Participação em conversas simples.',
                    'EI02EF02' => 'Escuta de histórias, cantigas e rimas.',
                    'EI02EF03' => 'Escuta de histórias, cantigas e rimas.',
                    'EI02EF04' => 'Desenvolvimento da linguagem oral (palavras e frases curtas).',
                    'EI02EF05' => 'Brincadeiras de faz-de-conta mais elaboradas.',
                ];

                foreach ($mapEF as $codigo => $saberTitulo) {
                    $saberId = $saberesEF[$saberTitulo] ?? null;
                    if ($saberId) {
                        DB::table('objetivos_aprendizagem')
                            ->where('codigo', $codigo)
                            ->update(['saber_conhecimento_id' => $saberId, 'updated_at' => now()]);
                    }
                }
            }
        }

        // Vincular objetivos EI02ETxx aos saberes da área "Espaços, tempos, quantidades, relações e transformações", se existirem
        $campoET = $camposExperiencia['Espaços, tempos, quantidades, relações e transformações'] ?? null;
        if ($campoET) {
            $saberesET = DB::table('saberes_conhecimentos')
                ->where('campo_experiencia_id', $campoET)
                ->pluck('id', 'titulo');

            if ($saberesET && $saberesET->count()) {
                $mapET = [
                    'EI02ET01' => 'Descoberta de relações espaciais (perto/longe, em cima/embaixo, dentro/fora).',
                    'EI02ET02' => 'Descoberta de relações espaciais (perto/longe, em cima/embaixo, dentro/fora).',
                    'EI02ET03' => 'Noções de tempo (antes, depois, rotina diária).',
                    'EI02ET04' => 'Noções iniciais de quantidade (um, dois, muitos).',
                    'EI02ET05' => 'Observação de mudanças em objetos e ambientes.',
                ];

                foreach ($mapET as $codigo => $saberTitulo) {
                    $saberId = $saberesET[$saberTitulo] ?? null;
                    if ($saberId) {
                        DB::table('objetivos_aprendizagem')
                            ->where('codigo', $codigo)
                            ->update(['saber_conhecimento_id' => $saberId, 'updated_at' => now()]);
                    }
                }
            }
        }
    }
}