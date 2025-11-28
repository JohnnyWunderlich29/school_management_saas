<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DisciplinaNivelEnsinoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $escolaId = 5;

        // ========================================
        // EDUCAÇÃO INFANTIL - CAMPOS DE EXPERIÊNCIA
        // ========================================
        // IDs dos níveis de Educação Infantil: 1,2,3,4,18,19,38
        $niveisEducacaoInfantil = [1, 2, 3, 4, 18, 19, 38];
        
        $disciplinasCamposExperiencia = DB::table('disciplinas')
            ->where('area_conhecimento', 'Campos de Experiência')
            ->where('escola_id', $escolaId)
            ->get();

        foreach ($niveisEducacaoInfantil as $nivelId) {
            foreach ($disciplinasCamposExperiencia as $disciplina) {
                DB::table('disciplina_nivel_ensino')->updateOrInsert(
                    [
                        'disciplina_id' => $disciplina->id,
                        'nivel_ensino_id' => $nivelId
                    ],
                    [
                        'carga_horaria_semanal' => 10,
                        'carga_horaria_anual' => 400,
                        'obrigatoria' => true,
                        'ordem' => $disciplina->ordem,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]
                );
            }
        }

        // ========================================
        // ENSINO FUNDAMENTAL I - ANOS INICIAIS
        // ========================================
        // IDs dos níveis EF1: 5,6,7,8,9,20,21,22,23,24,39
        $niveisEF1 = [5, 6, 7, 8, 9, 20, 21, 22, 23, 24, 39];
        
        $disciplinasEF1 = DB::table('disciplinas')
            ->where('codigo', 'LIKE', 'EF1_%')
            ->where('escola_id', $escolaId)
            ->get();

        foreach ($niveisEF1 as $nivelId) {
            foreach ($disciplinasEF1 as $disciplina) {
                $cargaHorariaSemanal = match($disciplina->codigo) {
                    'EF1_LP' => 5,    // Língua Portuguesa
                    'EF1_MAT' => 5,   // Matemática
                    'EF1_CIE' => 2,   // Ciências
                    'EF1_HIS' => 2,   // História
                    'EF1_GEO' => 2,   // Geografia
                    'EF1_ART' => 2,   // Arte
                    'EF1_EF' => 2,    // Educação Física
                    'EF1_ER' => 1,    // Ensino Religioso
                    default => 2
                };

                DB::table('disciplina_nivel_ensino')->updateOrInsert(
                    [
                        'disciplina_id' => $disciplina->id,
                        'nivel_ensino_id' => $nivelId
                    ],
                    [
                        'carga_horaria_semanal' => $cargaHorariaSemanal,
                        'carga_horaria_anual' => $cargaHorariaSemanal * 40,
                        'obrigatoria' => true,
                        'ordem' => $disciplina->ordem,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]
                );
            }
        }

        // ========================================
        // ENSINO FUNDAMENTAL II - ANOS FINAIS
        // ========================================
        // IDs dos níveis EF2: 10,11,12,13,25,26,27,28,40
        $niveisEF2 = [10, 11, 12, 13, 25, 26, 27, 28, 40];
        
        $disciplinasEF2 = DB::table('disciplinas')
            ->where('codigo', 'LIKE', 'EF2_%')
            ->where('escola_id', $escolaId)
            ->get();

        foreach ($niveisEF2 as $nivelId) {
            foreach ($disciplinasEF2 as $disciplina) {
                $cargaHorariaSemanal = match($disciplina->codigo) {
                    'EF2_LP' => 4,    // Língua Portuguesa
                    'EF2_MAT' => 4,   // Matemática
                    'EF2_ING' => 2,   // Inglês
                    'EF2_CIE' => 3,   // Ciências
                    'EF2_HIS' => 2,   // História
                    'EF2_GEO' => 2,   // Geografia
                    'EF2_ART' => 2,   // Arte
                    'EF2_EF' => 2,    // Educação Física
                    'EF2_ER' => 1,    // Ensino Religioso
                    default => 2
                };

                DB::table('disciplina_nivel_ensino')->updateOrInsert(
                    [
                        'disciplina_id' => $disciplina->id,
                        'nivel_ensino_id' => $nivelId
                    ],
                    [
                        'carga_horaria_semanal' => $cargaHorariaSemanal,
                        'carga_horaria_anual' => $cargaHorariaSemanal * 40,
                        'obrigatoria' => true,
                        'ordem' => $disciplina->ordem,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]
                );
            }
        }

        // ========================================
        // ENSINO MÉDIO - FORMAÇÃO COMUM
        // ========================================
        // IDs dos níveis EM: 14,15,16,29,30,31,41
        $niveisEM = [14, 15, 16, 29, 30, 31, 41];
        
        $disciplinasEMComum = DB::table('disciplinas')
            ->where('codigo', 'LIKE', 'EM_%')
            ->where('escola_id', $escolaId)
            ->get();

        foreach ($niveisEM as $nivelId) {
            foreach ($disciplinasEMComum as $disciplina) {
                $cargaHorariaSemanal = match($disciplina->codigo) {
                    'EM_LP' => 3,     // Língua Portuguesa
                    'EM_MAT' => 3,    // Matemática
                    'EM_ING' => 2,    // Inglês
                    'EM_BIO' => 2,    // Biologia
                    'EM_FIS' => 2,    // Física
                    'EM_QUI' => 2,    // Química
                    'EM_HIS' => 2,    // História
                    'EM_GEO' => 2,    // Geografia
                    'EM_SOC' => 2,    // Sociologia
                    'EM_FIL' => 2,    // Filosofia
                    'EM_ART' => 1,    // Arte
                    'EM_EF' => 2,     // Educação Física
                    'EM_ER' => 1,     // Ensino Religioso
                    default => 2
                };

                DB::table('disciplina_nivel_ensino')->updateOrInsert(
                    [
                        'disciplina_id' => $disciplina->id,
                        'nivel_ensino_id' => $nivelId
                    ],
                    [
                        'carga_horaria_semanal' => $cargaHorariaSemanal,
                        'carga_horaria_anual' => $cargaHorariaSemanal * 40,
                        'obrigatoria' => true,
                        'ordem' => $disciplina->ordem,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]
                );
            }
        }

        // ========================================
        // ENSINO MÉDIO - ITINERÁRIOS FORMATIVOS
        // ========================================
        $disciplinasItinerarios = DB::table('disciplinas')
            ->where(function($query) {
                $query->where('codigo', 'LIKE', 'IF_%')
                      ->orWhere('codigo', 'LIKE', 'FTP_%');
            })
            ->where('escola_id', $escolaId)
            ->get();

        foreach ($niveisEM as $nivelId) {
            foreach ($disciplinasItinerarios as $disciplina) {
                DB::table('disciplina_nivel_ensino')->updateOrInsert(
                    [
                        'disciplina_id' => $disciplina->id,
                        'nivel_ensino_id' => $nivelId
                    ],
                    [
                        'carga_horaria_semanal' => 2,
                        'carga_horaria_anual' => 80,
                        'obrigatoria' => false, // Itinerários são eletivos
                        'ordem' => $disciplina->ordem,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]
                );
            }
        }

        // ========================================
        // EJA FUNDAMENTAL
        // ========================================
        // IDs dos níveis EJA Fundamental: 32,33,42
        $niveisEJAFund = [32, 33, 42];
        
        $disciplinasEJAFund = DB::table('disciplinas')
            ->where('codigo', 'LIKE', 'EJA_F_%')
            ->where('escola_id', $escolaId)
            ->get();

        foreach ($niveisEJAFund as $nivelId) {
            foreach ($disciplinasEJAFund as $disciplina) {
                $cargaHorariaSemanal = match($disciplina->codigo) {
                    'EJA_F_LP' => 4,   // Língua Portuguesa
                    'EJA_F_MAT' => 4,  // Matemática
                    'EJA_F_CIE' => 3,  // Ciências
                    'EJA_F_HIS' => 2,  // História
                    'EJA_F_GEO' => 2,  // Geografia
                    default => 2
                };

                DB::table('disciplina_nivel_ensino')->updateOrInsert(
                    [
                        'disciplina_id' => $disciplina->id,
                        'nivel_ensino_id' => $nivelId
                    ],
                    [
                        'carga_horaria_semanal' => $cargaHorariaSemanal,
                        'carga_horaria_anual' => $cargaHorariaSemanal * 40,
                        'obrigatoria' => true,
                        'ordem' => $disciplina->ordem,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]
                );
            }
        }

        // ========================================
        // EJA ENSINO MÉDIO
        // ========================================
        // ID do nível EJA Médio: 34
        $disciplinasEJAMedio = DB::table('disciplinas')
            ->where('codigo', 'LIKE', 'EJA_M_%')
            ->where('escola_id', $escolaId)
            ->get();

        foreach ($disciplinasEJAMedio as $disciplina) {
            $cargaHorariaSemanal = match($disciplina->codigo) {
                'EJA_M_LP' => 3,   // Língua Portuguesa
                'EJA_M_MAT' => 3,  // Matemática
                'EJA_M_BIO' => 2,  // Biologia
                'EJA_M_FIS' => 2,  // Física
                'EJA_M_QUI' => 2,  // Química
                'EJA_M_HIS' => 2,  // História
                'EJA_M_GEO' => 2,  // Geografia
                'EJA_M_SOC' => 1,  // Sociologia
                'EJA_M_FIL' => 1,  // Filosofia
                default => 2
            };

            DB::table('disciplina_nivel_ensino')->updateOrInsert(
                [
                    'disciplina_id' => $disciplina->id,
                    'nivel_ensino_id' => 34
                ],
                [
                    'carga_horaria_semanal' => $cargaHorariaSemanal,
                    'carga_horaria_anual' => $cargaHorariaSemanal * 40,
                    'obrigatoria' => true,
                    'ordem' => $disciplina->ordem,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        }

        $this->command->info('Relacionamentos disciplina-nível de ensino criados com sucesso!');
    }
}
