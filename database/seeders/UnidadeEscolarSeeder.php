<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\UnidadeEscolar;
use App\Models\User;

class UnidadeEscolarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Criar unidades escolares
        $unidades = [
            [
                'nome' => 'Escola Municipal Infantil Pequenos Sonhos',
                'ativo' => true
            ],
            [
                'nome' => 'Centro de Educação Infantil Arco-Íris',
                'ativo' => true
            ],
            [
                'nome' => 'Creche Municipal Girassol',
                'ativo' => true
            ]
        ];

        foreach ($unidades as $unidade) {
            UnidadeEscolar::firstOrCreate(
                ['nome' => $unidade['nome']],
                $unidade
            );
        }

        // Associar o usuário administrador à primeira unidade escolar
        $adminUser = User::where('email', 'admin@escola.com')->first();
        $primeiraUnidade = UnidadeEscolar::where('nome', 'Escola Municipal Infantil Pequenos Sonhos')->first();
        
        if ($adminUser && $primeiraUnidade && !$adminUser->unidade_escolar_id) {
            $adminUser->update(['unidade_escolar_id' => $primeiraUnidade->id]);
        }

        // Associar outros usuários às unidades escolares
        $usuarios = User::whereNull('unidade_escolar_id')->get();
        $unidadesEscolares = UnidadeEscolar::where('ativo', true)->get();
        
        foreach ($usuarios as $usuario) {
            if ($unidadesEscolares->isNotEmpty()) {
                $unidadeAleatoria = $unidadesEscolares->random();
                $usuario->update(['unidade_escolar_id' => $unidadeAleatoria->id]);
            }
        }

        $this->command->info('Unidades escolares criadas e usuários associados com sucesso!');
    }
}