<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Cargo;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestProfessorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verificar se existe o cargo Professor
        $cargoProfessor = Cargo::where('nome', 'Professor')->first();
        
        if (!$cargoProfessor) {
            $this->command->info('Cargo Professor não encontrado. Criando...');
            $cargoProfessor = Cargo::create([
                'nome' => 'Professor',
                'descricao' => 'Professor da instituição',
                'ativo' => true
            ]);
        }
        
        // Criar um usuário professor de teste se não existir
        $professor = User::where('email', 'professor.teste@escola.com')->first();
        
        if (!$professor) {
            $this->command->info('Criando professor de teste...');
            $professor = User::create([
                'name' => 'Professor Teste',
                'email' => 'professor.teste@escola.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]);
            
            // Atribuir o cargo de Professor
            $professor->cargos()->attach($cargoProfessor->id);
            
            $this->command->info('Professor de teste criado com sucesso!');
        } else {
            $this->command->info('Professor de teste já existe.');
            
            // Verificar se tem o cargo
            if (!$professor->cargos()->where('nome', 'Professor')->exists()) {
                $professor->cargos()->attach($cargoProfessor->id);
                $this->command->info('Cargo Professor atribuído ao usuário existente.');
            }
        }
        
        // Mostrar estatísticas
        $totalProfessores = User::whereHas('cargos', function($query) {
            $query->where('nome', 'Professor');
        })->count();
        
        $this->command->info("Total de professores no sistema: {$totalProfessores}");
    }
}
