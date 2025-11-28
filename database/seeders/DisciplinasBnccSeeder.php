<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DisciplinasBnccSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $escolaId = 5; // ID da escola

        // ========================================
        // EDUCAÇÃO INFANTIL - CAMPOS DE EXPERIÊNCIA
        // ========================================
        $camposExperiencia = [
            [
                'nome' => 'O eu, o outro e o nós',
                'codigo' => 'EI_EU_OUTRO_NOS',
                'descricao' => 'Identidade, alteridade, convivência social e ética',
                'area_conhecimento' => 'Campos de Experiência',
                'cor_hex' => '#FF6B6B',
                'ordem' => 1
            ],
            [
                'nome' => 'Corpo, gestos e movimentos',
                'codigo' => 'EI_CORPO_GESTOS',
                'descricao' => 'Exploração corporal, motricidade e expressão',
                'area_conhecimento' => 'Campos de Experiência',
                'cor_hex' => '#4ECDC4',
                'ordem' => 2
            ],
            [
                'nome' => 'Traços, sons, cores e formas',
                'codigo' => 'EI_TRACOS_SONS',
                'descricao' => 'Linguagens artísticas e sensoriais (visuais, sonoras)',
                'area_conhecimento' => 'Campos de Experiência',
                'cor_hex' => '#45B7D1',
                'ordem' => 3
            ],
            [
                'nome' => 'Escuta, fala, pensamento e imaginação',
                'codigo' => 'EI_ESCUTA_FALA',
                'descricao' => 'Desenvolvimento linguístico, narrativo e cognitivo',
                'area_conhecimento' => 'Campos de Experiência',
                'cor_hex' => '#96CEB4',
                'ordem' => 4
            ],
            [
                'nome' => 'Espaços, tempos, quantidades, relações e transformações',
                'codigo' => 'EI_ESPACOS_TEMPOS',
                'descricao' => 'Noções matemáticas, espaciais e temporais básicas',
                'area_conhecimento' => 'Campos de Experiência',
                'cor_hex' => '#FFEAA7',
                'ordem' => 5
            ]
        ];

        // ========================================
        // ENSINO FUNDAMENTAL I - ANOS INICIAIS
        // ========================================
        $ef1Disciplinas = [
            // Linguagens
            [
                'nome' => 'Língua Portuguesa',
                'codigo' => 'EF1_LP',
                'descricao' => 'Leitura, escrita, oralidade e análise linguística',
                'area_conhecimento' => 'Linguagens',
                'cor_hex' => '#E74C3C',
                'ordem' => 1
            ],
            [
                'nome' => 'Arte',
                'codigo' => 'EF1_ART',
                'descricao' => 'Artes visuais, música, dança e teatro',
                'area_conhecimento' => 'Linguagens',
                'cor_hex' => '#9B59B6',
                'ordem' => 2
            ],
            [
                'nome' => 'Educação Física',
                'codigo' => 'EF1_EF',
                'descricao' => 'Jogos, brincadeiras e movimentos corporais',
                'area_conhecimento' => 'Linguagens',
                'cor_hex' => '#3498DB',
                'ordem' => 3
            ],
            // Matemática
            [
                'nome' => 'Matemática',
                'codigo' => 'EF1_MAT',
                'descricao' => 'Números, operações, geometria, medidas e estatística',
                'area_conhecimento' => 'Matemática',
                'cor_hex' => '#F39C12',
                'ordem' => 1
            ],
            // Ciências da Natureza
            [
                'nome' => 'Ciências',
                'codigo' => 'EF1_CIE',
                'descricao' => 'Matéria, energia, vida, evolução, terra e universo',
                'area_conhecimento' => 'Ciências da Natureza',
                'cor_hex' => '#27AE60',
                'ordem' => 1
            ],
            // Ciências Humanas
            [
                'nome' => 'História',
                'codigo' => 'EF1_HIS',
                'descricao' => 'Mundo pessoal, familiar e comunitário',
                'area_conhecimento' => 'Ciências Humanas',
                'cor_hex' => '#8E44AD',
                'ordem' => 1
            ],
            [
                'nome' => 'Geografia',
                'codigo' => 'EF1_GEO',
                'descricao' => 'Espaço vivido e representações cartográficas',
                'area_conhecimento' => 'Ciências Humanas',
                'cor_hex' => '#16A085',
                'ordem' => 2
            ],
            // Ensino Religioso
            [
                'nome' => 'Ensino Religioso',
                'codigo' => 'EF1_ER',
                'descricao' => 'Identidades, crenças e pluralismo religioso',
                'area_conhecimento' => 'Ensino Religioso',
                'cor_hex' => '#D35400',
                'ordem' => 1
            ]
        ];

        // ========================================
        // ENSINO FUNDAMENTAL II - ANOS FINAIS
        // ========================================
        $ef2Disciplinas = [
            // Linguagens
            [
                'nome' => 'Língua Portuguesa',
                'codigo' => 'EF2_LP',
                'descricao' => 'Análise textual avançada e gramática',
                'area_conhecimento' => 'Linguagens',
                'cor_hex' => '#E74C3C',
                'ordem' => 1
            ],
            [
                'nome' => 'Língua Inglesa',
                'codigo' => 'EF2_ING',
                'descricao' => 'Comunicação oral e escrita em inglês',
                'area_conhecimento' => 'Linguagens',
                'cor_hex' => '#2ECC71',
                'ordem' => 2
            ],
            [
                'nome' => 'Arte',
                'codigo' => 'EF2_ART',
                'descricao' => 'Artes integradas e expressão artística',
                'area_conhecimento' => 'Linguagens',
                'cor_hex' => '#9B59B6',
                'ordem' => 3
            ],
            [
                'nome' => 'Educação Física',
                'codigo' => 'EF2_EF',
                'descricao' => 'Práticas corporais e esportes',
                'area_conhecimento' => 'Linguagens',
                'cor_hex' => '#3498DB',
                'ordem' => 4
            ],
            // Matemática
            [
                'nome' => 'Matemática',
                'codigo' => 'EF2_MAT',
                'descricao' => 'Álgebra, geometria, probabilidade e estatística',
                'area_conhecimento' => 'Matemática',
                'cor_hex' => '#F39C12',
                'ordem' => 1
            ],
            // Ciências da Natureza
            [
                'nome' => 'Ciências',
                'codigo' => 'EF2_CIE',
                'descricao' => 'Misturas químicas, sistemas biológicos, terra e universo',
                'area_conhecimento' => 'Ciências da Natureza',
                'cor_hex' => '#27AE60',
                'ordem' => 1
            ],
            // Ciências Humanas
            [
                'nome' => 'História',
                'codigo' => 'EF2_HIS',
                'descricao' => 'Processos históricos e sociais',
                'area_conhecimento' => 'Ciências Humanas',
                'cor_hex' => '#8E44AD',
                'ordem' => 1
            ],
            [
                'nome' => 'Geografia',
                'codigo' => 'EF2_GEO',
                'descricao' => 'Territórios, globalização e cidadania',
                'area_conhecimento' => 'Ciências Humanas',
                'cor_hex' => '#16A085',
                'ordem' => 2
            ],
            // Ensino Religioso
            [
                'nome' => 'Ensino Religioso',
                'codigo' => 'EF2_ER',
                'descricao' => 'Manifestações religiosas, ética e diversidade',
                'area_conhecimento' => 'Ensino Religioso',
                'cor_hex' => '#D35400',
                'ordem' => 1
            ]
        ];

        // ========================================
        // ENSINO MÉDIO - FORMAÇÃO COMUM
        // ========================================
        $emFormacaoComum = [
            // Linguagens e suas Tecnologias
            [
                'nome' => 'Língua Portuguesa',
                'codigo' => 'EM_LP',
                'descricao' => 'Análise literária e produção textual avançada',
                'area_conhecimento' => 'Linguagens e suas Tecnologias',
                'cor_hex' => '#E74C3C',
                'ordem' => 1
            ],
            [
                'nome' => 'Língua Inglesa',
                'codigo' => 'EM_ING',
                'descricao' => 'Proficiência em inglês e culturas anglófonas',
                'area_conhecimento' => 'Linguagens e suas Tecnologias',
                'cor_hex' => '#2ECC71',
                'ordem' => 2
            ],
            [
                'nome' => 'Arte',
                'codigo' => 'EM_ART',
                'descricao' => 'História da arte e produção artística',
                'area_conhecimento' => 'Linguagens e suas Tecnologias',
                'cor_hex' => '#9B59B6',
                'ordem' => 3
            ],
            [
                'nome' => 'Educação Física',
                'codigo' => 'EM_EF',
                'descricao' => 'Cultura corporal e qualidade de vida',
                'area_conhecimento' => 'Linguagens e suas Tecnologias',
                'cor_hex' => '#3498DB',
                'ordem' => 4
            ],
            // Matemática e suas Tecnologias
            [
                'nome' => 'Matemática',
                'codigo' => 'EM_MAT',
                'descricao' => 'Matemática avançada e aplicações tecnológicas',
                'area_conhecimento' => 'Matemática e suas Tecnologias',
                'cor_hex' => '#F39C12',
                'ordem' => 1
            ],
            // Ciências da Natureza e suas Tecnologias
            [
                'nome' => 'Biologia',
                'codigo' => 'EM_BIO',
                'descricao' => 'Vida, evolução e biotecnologia',
                'area_conhecimento' => 'Ciências da Natureza e suas Tecnologias',
                'cor_hex' => '#27AE60',
                'ordem' => 1
            ],
            [
                'nome' => 'Física',
                'codigo' => 'EM_FIS',
                'descricao' => 'Matéria, energia e tecnologia',
                'area_conhecimento' => 'Ciências da Natureza e suas Tecnologias',
                'cor_hex' => '#34495E',
                'ordem' => 2
            ],
            [
                'nome' => 'Química',
                'codigo' => 'EM_QUI',
                'descricao' => 'Transformações químicas e materiais',
                'area_conhecimento' => 'Ciências da Natureza e suas Tecnologias',
                'cor_hex' => '#E67E22',
                'ordem' => 3
            ],
            // Ciências Humanas e Sociais Aplicadas
            [
                'nome' => 'História',
                'codigo' => 'EM_HIS',
                'descricao' => 'Processos históricos e contemporaneidade',
                'area_conhecimento' => 'Ciências Humanas e Sociais Aplicadas',
                'cor_hex' => '#8E44AD',
                'ordem' => 1
            ],
            [
                'nome' => 'Geografia',
                'codigo' => 'EM_GEO',
                'descricao' => 'Espaço geográfico e sustentabilidade',
                'area_conhecimento' => 'Ciências Humanas e Sociais Aplicadas',
                'cor_hex' => '#16A085',
                'ordem' => 2
            ],
            [
                'nome' => 'Sociologia',
                'codigo' => 'EM_SOC',
                'descricao' => 'Sociedade, cultura e cidadania',
                'area_conhecimento' => 'Ciências Humanas e Sociais Aplicadas',
                'cor_hex' => '#95A5A6',
                'ordem' => 3
            ],
            [
                'nome' => 'Filosofia',
                'codigo' => 'EM_FIL',
                'descricao' => 'Pensamento crítico e ética',
                'area_conhecimento' => 'Ciências Humanas e Sociais Aplicadas',
                'cor_hex' => '#7F8C8D',
                'ordem' => 4
            ],
            // Ensino Religioso
            [
                'nome' => 'Ensino Religioso',
                'codigo' => 'EM_ER',
                'descricao' => 'Diversidade religiosa e valores éticos',
                'area_conhecimento' => 'Ensino Religioso',
                'cor_hex' => '#D35400',
                'ordem' => 1
            ]
        ];

        // ========================================
        // ENSINO MÉDIO - ITINERÁRIOS FORMATIVOS
        // ========================================
        $itinerariosFormativos = [
            // Linguagens
            [
                'nome' => 'Aprofundamento em Linguagens',
                'codigo' => 'IF_LING',
                'descricao' => 'Mídias digitais, comunicação e cultura',
                'area_conhecimento' => 'Itinerários Formativos - Linguagens',
                'cor_hex' => '#E74C3C',
                'ordem' => 1
            ],
            // Matemática
            [
                'nome' => 'Aprofundamento em Matemática',
                'codigo' => 'IF_MAT',
                'descricao' => 'Raciocínio lógico e aplicações tecnológicas',
                'area_conhecimento' => 'Itinerários Formativos - Matemática',
                'cor_hex' => '#F39C12',
                'ordem' => 1
            ],
            // Ciências da Natureza
            [
                'nome' => 'Aprofundamento em Ciências da Natureza',
                'codigo' => 'IF_CN',
                'descricao' => 'Investigação científica e sustentabilidade',
                'area_conhecimento' => 'Itinerários Formativos - Ciências da Natureza',
                'cor_hex' => '#27AE60',
                'ordem' => 1
            ],
            // Ciências Humanas
            [
                'nome' => 'Aprofundamento em Ciências Humanas',
                'codigo' => 'IF_CH',
                'descricao' => 'Cidadania, diversidade e análise social',
                'area_conhecimento' => 'Itinerários Formativos - Ciências Humanas',
                'cor_hex' => '#8E44AD',
                'ordem' => 1
            ],
            // Formação Técnica e Profissional
            [
                'nome' => 'Formação Técnica e Profissional',
                'codigo' => 'FTP_GERAL',
                'descricao' => 'Cursos profissionalizantes integrados',
                'area_conhecimento' => 'Formação Técnica e Profissional',
                'cor_hex' => '#34495E',
                'ordem' => 1
            ]
        ];

        // ========================================
        // EJA - ENSINO FUNDAMENTAL
        // ========================================
        $ejaFundamental = [
            [
                'nome' => 'Língua Portuguesa',
                'codigo' => 'EJA_F_LP',
                'descricao' => 'Alfabetização e letramento para jovens e adultos',
                'area_conhecimento' => 'Linguagens',
                'cor_hex' => '#E74C3C',
                'ordem' => 1
            ],
            [
                'nome' => 'Matemática',
                'codigo' => 'EJA_F_MAT',
                'descricao' => 'Matemática básica aplicada ao cotidiano',
                'area_conhecimento' => 'Matemática',
                'cor_hex' => '#F39C12',
                'ordem' => 1
            ],
            [
                'nome' => 'Ciências',
                'codigo' => 'EJA_F_CIE',
                'descricao' => 'Ciências naturais e saúde',
                'area_conhecimento' => 'Ciências da Natureza',
                'cor_hex' => '#27AE60',
                'ordem' => 1
            ],
            [
                'nome' => 'História',
                'codigo' => 'EJA_F_HIS',
                'descricao' => 'História do Brasil e cidadania',
                'area_conhecimento' => 'Ciências Humanas',
                'cor_hex' => '#8E44AD',
                'ordem' => 1
            ],
            [
                'nome' => 'Geografia',
                'codigo' => 'EJA_F_GEO',
                'descricao' => 'Geografia do Brasil e mundo do trabalho',
                'area_conhecimento' => 'Ciências Humanas',
                'cor_hex' => '#16A085',
                'ordem' => 2
            ]
        ];

        // ========================================
        // EJA - ENSINO MÉDIO
        // ========================================
        $ejaMedio = [
            [
                'nome' => 'Língua Portuguesa',
                'codigo' => 'EJA_M_LP',
                'descricao' => 'Literatura e produção textual para jovens e adultos',
                'area_conhecimento' => 'Linguagens',
                'cor_hex' => '#E74C3C',
                'ordem' => 1
            ],
            [
                'nome' => 'Matemática',
                'codigo' => 'EJA_M_MAT',
                'descricao' => 'Matemática aplicada e financeira',
                'area_conhecimento' => 'Matemática',
                'cor_hex' => '#F39C12',
                'ordem' => 1
            ],
            [
                'nome' => 'Biologia',
                'codigo' => 'EJA_M_BIO',
                'descricao' => 'Biologia e qualidade de vida',
                'area_conhecimento' => 'Ciências da Natureza',
                'cor_hex' => '#27AE60',
                'ordem' => 1
            ],
            [
                'nome' => 'Física',
                'codigo' => 'EJA_M_FIS',
                'descricao' => 'Física aplicada ao cotidiano',
                'area_conhecimento' => 'Ciências da Natureza',
                'cor_hex' => '#34495E',
                'ordem' => 2
            ],
            [
                'nome' => 'Química',
                'codigo' => 'EJA_M_QUI',
                'descricao' => 'Química e meio ambiente',
                'area_conhecimento' => 'Ciências da Natureza',
                'cor_hex' => '#E67E22',
                'ordem' => 3
            ],
            [
                'nome' => 'História',
                'codigo' => 'EJA_M_HIS',
                'descricao' => 'História contemporânea e direitos humanos',
                'area_conhecimento' => 'Ciências Humanas',
                'cor_hex' => '#8E44AD',
                'ordem' => 1
            ],
            [
                'nome' => 'Geografia',
                'codigo' => 'EJA_M_GEO',
                'descricao' => 'Geografia econômica e globalização',
                'area_conhecimento' => 'Ciências Humanas',
                'cor_hex' => '#16A085',
                'ordem' => 2
            ],
            [
                'nome' => 'Sociologia',
                'codigo' => 'EJA_M_SOC',
                'descricao' => 'Sociedade e mundo do trabalho',
                'area_conhecimento' => 'Ciências Humanas',
                'cor_hex' => '#95A5A6',
                'ordem' => 3
            ],
            [
                'nome' => 'Filosofia',
                'codigo' => 'EJA_M_FIL',
                'descricao' => 'Ética e cidadania',
                'area_conhecimento' => 'Ciências Humanas',
                'cor_hex' => '#7F8C8D',
                'ordem' => 4
            ]
        ];

        // Inserir todas as disciplinas
        $todasDisciplinas = array_merge(
            $camposExperiencia,
            $ef1Disciplinas,
            $ef2Disciplinas,
            $emFormacaoComum,
            $itinerariosFormativos,
            $ejaFundamental,
            $ejaMedio
        );

        foreach ($todasDisciplinas as $disciplina) {
            DB::table('disciplinas')->updateOrInsert(
                [
                    'codigo' => $disciplina['codigo'],
                    'escola_id' => $escolaId
                ],
                [
                    'nome' => $disciplina['nome'],
                    'descricao' => $disciplina['descricao'],
                    'area_conhecimento' => $disciplina['area_conhecimento'],
                    'cor_hex' => $disciplina['cor_hex'],
                    'obrigatoria' => true,
                    'ordem' => $disciplina['ordem'],
                    'ativo' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        }

        $this->command->info('Disciplinas BNCC inseridas com sucesso!');
    }
}
