<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class VerificarTabelasCommand extends Command
{
    protected $signature = 'debug:verificar-tabelas';
    protected $description = 'Verifica a estrutura das tabelas e dados';

    public function handle()
    {
        $this->info('=== VERIFICAÇÃO DE TABELAS ===');
        
        // Verificar se as tabelas existem
        $tabelas = ['alunos', 'responsaveis', 'aluno_responsavel'];
        foreach ($tabelas as $tabela) {
            if (Schema::hasTable($tabela)) {
                $this->info("✓ Tabela '{$tabela}' existe");
            } else {
                $this->error("✗ Tabela '{$tabela}' NÃO existe");
            }
        }
        
        // Verificar dados nas tabelas
        $this->info('\n=== CONTAGEM DE REGISTROS ===');
        try {
            $countAlunos = DB::table('alunos')->count();
            $this->info("Alunos: {$countAlunos}");
            
            $countResponsaveis = DB::table('responsaveis')->count();
            $this->info("Responsáveis: {$countResponsaveis}");
            
            $countVinculos = DB::table('aluno_responsavel')->count();
            $this->info("Vínculos: {$countVinculos}");
        } catch (\Exception $e) {
            $this->error('Erro ao contar registros: ' . $e->getMessage());
        }
        
        // Verificar alguns IDs
        $this->info('\n=== VERIFICAÇÃO DE IDs ===');
        try {
            $primeiroAluno = DB::table('alunos')->first();
            if ($primeiroAluno) {
                $this->info("Primeiro aluno ID: {$primeiroAluno->id}");
            }
            
            $primeiroResponsavel = DB::table('responsaveis')->first();
            if ($primeiroResponsavel) {
                $this->info("Primeiro responsável ID: {$primeiroResponsavel->id}");
            }
        } catch (\Exception $e) {
            $this->error('Erro ao verificar IDs: ' . $e->getMessage());
        }
        
        // Tentar inserção simples sem constraints
        $this->info('\n=== TESTE DE INSERÇÃO SIMPLES ===');
        try {
            // Primeiro, vamos tentar desabilitar as foreign keys temporariamente
            DB::statement('PRAGMA foreign_keys=OFF;');
            
            $resultado = DB::table('aluno_responsavel')->insert([
                'aluno_id' => 1,
                'responsavel_id' => 1,
                'responsavel_principal' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            if ($resultado) {
                $this->info('✓ Inserção simples funcionou (sem FK)');
                
                // Verificar se foi inserido
                $vinculo = DB::table('aluno_responsavel')->where('aluno_id', 1)->where('responsavel_id', 1)->first();
                if ($vinculo) {
                    $this->info('✓ Registro encontrado na tabela');
                } else {
                    $this->error('✗ Registro não encontrado após inserção');
                }
            } else {
                $this->error('✗ Inserção simples falhou');
            }
            
            // Reabilitar foreign keys
            DB::statement('PRAGMA foreign_keys=ON;');
            
        } catch (\Exception $e) {
            $this->error('Erro na inserção simples: ' . $e->getMessage());
            DB::statement('PRAGMA foreign_keys=ON;'); // Garantir que seja reabilitado
        }
        
        // Verificar contagem final
        $countFinal = DB::table('aluno_responsavel')->count();
        $this->info("\nTotal de vínculos após teste: {$countFinal}");
        
        return 0;
    }
}