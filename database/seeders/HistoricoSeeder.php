<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Historico;
use App\Models\User;
use Carbon\Carbon;

class HistoricoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $usuario = User::first();
        
        if (!$usuario) {
            $this->command->info('Nenhum usuário encontrado. Criando registros sem usuário.');
        }
        
        // Exemplo de criação de escala
        Historico::create([
            'acao' => 'criado',
            'modelo' => 'Escala',
            'modelo_id' => 1,
            'usuario_id' => $usuario?->id,
            'dados_novos' => [
                'funcionario_id' => 1,
                'sala_id' => 1,
                'data' => '2025-08-08',
                'hora_inicio' => '07:00:00',
                'hora_fim' => '13:00:00',
                'tipo_atividade' => 'em_sala'
            ],
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            'created_at' => Carbon::now()->subHours(2)
        ]);
        
        // Exemplo de atualização de escala
        Historico::create([
            'acao' => 'atualizado',
            'modelo' => 'Escala',
            'modelo_id' => 1,
            'usuario_id' => $usuario?->id,
            'dados_antigos' => [
                'funcionario_id' => 1,
                'sala_id' => 1,
                'data' => '2025-08-08',
                'hora_inicio' => '07:00:00',
                'hora_fim' => '13:00:00',
                'tipo_atividade' => 'em_sala'
            ],
            'dados_novos' => [
                'funcionario_id' => 1,
                'sala_id' => 1,
                'data' => '2025-08-08',
                'hora_inicio' => '08:00:00',
                'hora_fim' => '14:00:00',
                'tipo_atividade' => 'em_sala'
            ],
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            'created_at' => Carbon::now()->subHour()
        ]);
        
        // Exemplo de exclusão de escala
        Historico::create([
            'acao' => 'excluido',
            'modelo' => 'Escala',
            'modelo_id' => 2,
            'usuario_id' => $usuario?->id,
            'dados_antigos' => [
                'funcionario_id' => 2,
                'sala_id' => 2,
                'data' => '2025-08-07',
                'hora_inicio' => '14:00:00',
                'hora_fim' => '18:00:00',
                'tipo_atividade' => 'pl'
            ],
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            'created_at' => Carbon::now()->subMinutes(30)
        ]);
        
        // Exemplo de histórico de aluno
        Historico::create([
            'acao' => 'criado',
            'modelo' => 'Aluno',
            'modelo_id' => 1,
            'usuario_id' => $usuario?->id,
            'dados_novos' => [
                'nome' => 'João',
                'sobrenome' => 'Silva',
                'data_nascimento' => '2010-05-15',
                'cpf' => '12345678901'
            ],
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            'created_at' => Carbon::now()->subDays(1)
        ]);
        
        $this->command->info('Registros de histórico criados com sucesso!');
    }
}
