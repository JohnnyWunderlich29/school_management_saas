<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Aluno;
use App\Models\Responsavel;

class TestarVinculoCommand extends Command
{
    protected $signature = 'debug:testar-vinculo';
    protected $description = 'Testa a criação de vínculos entre aluno e responsável';

    public function handle()
    {
        $this->info('=== TESTE DE CRIAÇÃO DE VÍNCULO ===');
        
        try {
            // Pegar o primeiro aluno e primeiro responsável
            $aluno = Aluno::first();
            $responsavel = Responsavel::first();
            
            if (!$aluno || !$responsavel) {
                $this->error('Não há alunos ou responsáveis para testar.');
                return 1;
            }
            
            $this->info("Aluno: {$aluno->nome} {$aluno->sobrenome} (ID: {$aluno->id})");
            $this->info("Responsável: {$responsavel->nome} {$responsavel->sobrenome} (ID: {$responsavel->id})");
            
            // Verificar se já existe vínculo
            $vinculoExistente = DB::table('aluno_responsavel')
                ->where('aluno_id', $aluno->id)
                ->where('responsavel_id', $responsavel->id)
                ->first();
                
            if ($vinculoExistente) {
                $this->info('Vínculo já existe.');
            } else {
                $this->info('Criando vínculo...');
                
                // Tentar criar vínculo usando Eloquent
                $aluno->responsaveis()->attach($responsavel->id, [
                    'responsavel_principal' => true
                ]);
                
                $this->info('Vínculo criado com sucesso via Eloquent!');
                
                // Verificar se foi criado
                $vinculoCriado = DB::table('aluno_responsavel')
                    ->where('aluno_id', $aluno->id)
                    ->where('responsavel_id', $responsavel->id)
                    ->first();
                    
                if ($vinculoCriado) {
                    $this->info('Vínculo confirmado na tabela!');
                    $this->info('Dados: ' . json_encode($vinculoCriado));
                } else {
                    $this->error('Vínculo não foi encontrado na tabela após criação!');
                }
            }
            
            // Testar inserção direta
            $this->info('\nTestando inserção direta...');
            $resultado = DB::table('aluno_responsavel')->insert([
                'aluno_id' => $aluno->id,
                'responsavel_id' => $responsavel->id + 1, // Usar outro responsável
                'responsavel_principal' => false,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            if ($resultado) {
                $this->info('Inserção direta funcionou!');
            } else {
                $this->error('Inserção direta falhou!');
            }
            
            // Contar vínculos após teste
            $totalVinculos = DB::table('aluno_responsavel')->count();
            $this->info("\nTotal de vínculos após teste: {$totalVinculos}");
            
        } catch (\Exception $e) {
            $this->error('Erro durante o teste: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return 1;
        }
        
        return 0;
    }
}