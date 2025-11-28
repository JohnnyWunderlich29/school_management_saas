<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Executar seeders na ordem correta
        $this->call([
            // Seeders básicos
            EscolaSeeder::class,
            UserSeeder::class,
            PermissaoSeeder::class,
            UnidadeEscolarSeeder::class,
            ModalidadeEnsinoSeeder::class,
            TurnoSeeder::class,
            GrupoSeeder::class,
            DisciplinaSeeder::class,
            NiveisEnsinoSeeder::class,
            CargosAdministrativosSeeder::class,
            TipoProfessorSeeder::class,
            
            // Seeders BNCC
            CamposExperienciaSeeder::class,
            SaberesConhecimentosEOSeeder::class,
            SaberesConhecimentosCGSeeder::class,
            SaberesConhecimentosTSSeeder::class,
            SaberesConhecimentosEFSeeder::class,
            SaberesConhecimentosETSeeder::class,
            ObjetivosAprendizagemSeeder::class,
            ObjetivosAprendizagemEI02Seeder::class,
            TemplatesBnccSeeder::class,
            // BNCC EF — Língua Portuguesa
            BnccPortuguesEfSeeder::class,
            // BNCC EF — Matemática
            BnccMatematicaEfSeeder::class,
            // BNCC EF — Ciências
            BnccCienciasEfSeeder::class,
            // BNCC EF — História
            BnccHistoriaEfSeeder::class,
            // BNCC EF — Geografia
            BnccGeografiaEfSeeder::class,
            // BNCC EF — Arte
            BnccArteEfSeeder::class,
            // BNCC EF — Educação Física
            BnccEducacaoFisicaEfSeeder::class,
            // BNCC EF — Ensino Religioso
            BnccEnsinoReligiosoEfSeeder::class,
            
            // Seeders de dados
            FuncionarioSeeder::class,
            TurmasSeeder::class,
            SalaSeeder::class,
            AlunoSeeder::class,
            AlunoSalaSeeder::class,
            ResponsavelSeeder::class,
            UserSalaSeeder::class,

            // Seeders específicos por escola
            SalasEscola5Seeder::class,
            TurmasEscola5Seeder::class,
            
            // Seeders de relacionamentos
            EscalaSeeder::class,
            PresencaSeeder::class,
            
            // Seeders de módulos específicos
            BibliotecaPermissoesSeeder::class,
            BibliotecaPoliticasSeeder::class,
            BibliotecaCargosSeeder::class,

            // Seeders de módulos (catálogo e ativações)
            ModulesSeeder::class,
            RelatoriosModuleSeeder::class,
            FinanceiroModuleActivationSeeder::class,
            FinanceiroPermissoesSeeder::class,
        ]);
    }
}
