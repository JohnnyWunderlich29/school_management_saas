<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Turma;
use App\Models\Sala;

class TurmasSalasSeedingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function cria_salas_e_turmas_para_escola_5()
    {
        // Preparar dependências mínimas
        $this->seed(\Database\Seeders\EscolaSeeder::class);
        $this->seed(\Database\Seeders\ModalidadeEnsinoSeeder::class);
        $this->seed(\Database\Seeders\NiveisEnsinoSeeder::class);

        // Executar seeders específicos
        $this->seed(\Database\Seeders\SalasEscola5Seeder::class);
        $this->seed(\Database\Seeders\TurmasEscola5Seeder::class);

        // Validações básicas
        $salas5 = Sala::where('escola_id', 5)->count();
        $this->assertGreaterThanOrEqual(10, $salas5, 'Deve criar pelo menos 10 salas para escola 5');

        $turmas5 = Turma::where('escola_id', 5)->count();
        $this->assertGreaterThan(0, $turmas5, 'Deve criar turmas para escola 5');

        // Conferir se há turmas com turno definido
        $turmasComTurno = Turma::where('escola_id', 5)->whereNotNull('turno_id')->count();
        $this->assertGreaterThan(0, $turmasComTurno, 'Algumas turmas devem ter turno associado');
    }

    /** @test */
    public function reexecutar_seeders_nao_duplica_salas_ou_turmas()
    {
        $this->seed(\Database\Seeders\EscolaSeeder::class);
        $this->seed(\Database\Seeders\ModalidadeEnsinoSeeder::class);
        $this->seed(\Database\Seeders\NiveisEnsinoSeeder::class);
        $this->seed(\Database\Seeders\SalasEscola5Seeder::class);
        $this->seed(\Database\Seeders\TurmasEscola5Seeder::class);

        $salasInicial = Sala::where('escola_id', 5)->count();
        $turmasInicial = Turma::where('escola_id', 5)->count();

        // Reexecuta
        $this->seed(\Database\Seeders\SalasEscola5Seeder::class);
        $this->seed(\Database\Seeders\TurmasEscola5Seeder::class);

        $this->assertEquals($salasInicial, Sala::where('escola_id', 5)->count(), 'Salas não devem duplicar ao reexecutar seeder');
        $this->assertEquals($turmasInicial, Turma::where('escola_id', 5)->count(), 'Turmas não devem duplicar ao reexecutar seeder');
    }
}