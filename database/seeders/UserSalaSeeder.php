<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Sala;
use Faker\Factory as Faker;

class UserSalaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        
        // Limpar relacionamentos existentes
        DB::table('user_salas')->delete();
        
        // Buscar todos os professores (usuários com cargo Professor)
        $professores = User::whereHas('cargos', function($query) {
            $query->where('nome', 'Professor');
        })->get();
        
        // Buscar todas as salas
        $salas = Sala::all();
        
        if ($professores->isEmpty()) {
            $this->command->info('Não há professores para vincular às salas.');
            return;
        }
        
        if ($salas->isEmpty()) {
            $this->command->info('Não há salas para vincular aos professores.');
            return;
        }
        
        $relacionamentos = [];
        $totalVinculos = 0;
        
        foreach ($professores as $professor) {
            // Cada professor será vinculado a 3-8 salas aleatórias
            $quantidadeSalas = $faker->numberBetween(3, min(8, $salas->count()));
            $salasEscolhidas = $salas->random($quantidadeSalas);
            
            foreach ($salasEscolhidas as $sala) {
                $relacionamentos[] = [
                    'user_id' => $professor->id,
                    'sala_id' => $sala->id,
                    'escola_id' => $sala->escola_id,
                    'ativo' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $totalVinculos++;
            }
            
            $this->command->info("Professor {$professor->name} vinculado a {$quantidadeSalas} salas");
        }
        
        // Inserir todos os relacionamentos
        if (!empty($relacionamentos)) {
            // Inserir em lotes para melhor performance
            $chunks = array_chunk($relacionamentos, 100);
            foreach ($chunks as $chunk) {
                DB::table('user_salas')->insert($chunk);
            }
        }
        
        $this->command->info("{$totalVinculos} relacionamentos usuário-sala criados com sucesso!");
        $this->command->info("{$professores->count()} professores vinculados às salas");
        
        // Mostrar estatísticas por sala
        foreach ($salas as $sala) {
            $professoresNaSala = DB::table('user_salas')
                ->where('sala_id', $sala->id)
                ->where('ativo', true)
                ->count();
            $this->command->info("Sala {$sala->codigo}: {$professoresNaSala} professores");
        }
    }
}
