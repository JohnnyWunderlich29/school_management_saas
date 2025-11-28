<?php

namespace Database\Seeders;

use App\Models\Responsavel;
use App\Models\Aluno;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class ResponsavelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('pt_BR');
        $alunos = Aluno::all();
        
        if ($alunos->isEmpty()) {
            $this->command->info('Nenhum aluno encontrado. Execute o AlunoSeeder primeiro.');
            return;
        }
        
        $parentescos = ['Pai', 'Mãe', 'Avô', 'Avó', 'Tio', 'Tia', 'Padrasto', 'Madrasta', 'Responsável Legal', 'Tutor'];
        
        // Primeiro, criar responsáveis principais para cada aluno
        $responsaveisPrincipais = [];
        foreach ($alunos as $aluno) {
            $responsavel = Responsavel::create([
                'nome' => $faker->firstName,
                'sobrenome' => $faker->lastName,
                'cpf' => $faker->cpf(false),
                'rg' => $faker->rg(false),
                'telefone_principal' => $faker->cellphone(false),
                'telefone_secundario' => $faker->optional(0.6)->cellphone(false),
                'email' => $faker->unique()->safeEmail,
                'endereco' => $faker->streetAddress,
                'cidade' => $faker->city,
                'estado' => $faker->stateAbbr,
                'cep' => $faker->postcode,
                'parentesco' => $faker->randomElement(['Pai', 'Mãe']), // Responsável principal sempre pai ou mãe
                'autorizado_buscar' => true, // Responsável principal sempre autorizado
                'contato_emergencia' => true, // Responsável principal sempre contato de emergência
                'observacoes' => $faker->optional(0.3)->paragraph(1),
            ]);
            
            $responsaveisPrincipais[$aluno->id] = $responsavel->id;
        }
        
        // Criar responsáveis adicionais
        $responsaveisJaCriados = Responsavel::count();
        $responsaveisRestantes = 500 - $responsaveisJaCriados;
        
        $responsaveisSecundarios = [];
        for ($i = 1; $i <= $responsaveisRestantes; $i++) {
            $responsavel = Responsavel::create([
                'nome' => $faker->firstName,
                'sobrenome' => $faker->lastName,
                'cpf' => $faker->cpf(false),
                'rg' => $faker->rg(false),
                'telefone_principal' => $faker->cellphone(false),
                'telefone_secundario' => $faker->optional(0.6)->cellphone(false),
                'email' => $faker->unique()->safeEmail,
                'endereco' => $faker->streetAddress,
                'cidade' => $faker->city,
                'estado' => $faker->stateAbbr,
                'cep' => $faker->postcode,
                'parentesco' => $faker->randomElement($parentescos),
                'autorizado_buscar' => $faker->boolean(70),
                'contato_emergencia' => $faker->boolean(40),
                'observacoes' => $faker->optional(0.3)->paragraph(1),
            ]);
            
            $responsaveisSecundarios[] = $responsavel;
        }
        
        // Agora criar os vínculos após todos os responsáveis estarem criados
        // Vincular responsáveis principais
        foreach ($responsaveisPrincipais as $alunoId => $responsavelId) {
            \DB::table('aluno_responsavel')->insert([
                'aluno_id' => $alunoId,
                'responsavel_id' => $responsavelId,
                'responsavel_principal' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
        // Vincular responsáveis secundários
        foreach ($responsaveisSecundarios as $responsavel) {
            $quantidadeAlunos = $faker->numberBetween(1, 2);
            $alunosVinculados = $alunos->random($quantidadeAlunos);
            
            foreach ($alunosVinculados as $aluno) {
                // Verificar se já não está vinculado a este aluno
                $vinculoExiste = \DB::table('aluno_responsavel')
                    ->where('aluno_id', $aluno->id)
                    ->where('responsavel_id', $responsavel->id)
                    ->exists();
                    
                if (!$vinculoExiste) {
                    \DB::table('aluno_responsavel')->insert([
                        'aluno_id' => $aluno->id,
                        'responsavel_id' => $responsavel->id,
                        'responsavel_principal' => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
        
        $totalResponsaveis = Responsavel::count();
        $totalVinculos = \DB::table('aluno_responsavel')->count();
        
        $this->command->info("{$totalResponsaveis} responsáveis criados com sucesso!");
        $this->command->info("{$totalVinculos} vínculos aluno-responsável criados!");
    }
}