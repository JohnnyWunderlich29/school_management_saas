<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CheckTableStructure extends Command
{
    protected $signature = 'app:check-table-structure {table}';
    protected $description = 'Verifica a estrutura de uma tabela';

    public function handle()
    {
        $table = $this->argument('table');
        
        if (!Schema::hasTable($table)) {
            $this->error("Tabela '{$table}' não existe.");
            return 1;
        }
        
        $this->info("Estrutura da tabela '{$table}':");
        
        $columns = Schema::getColumnListing($table);
        $this->info("Colunas: " . implode(', ', $columns));
        
        // Verificar se escola_id existe
        if (in_array('escola_id', $columns)) {
            $this->info("✓ Coluna 'escola_id' existe na tabela.");
        } else {
            $this->error("✗ Coluna 'escola_id' NÃO existe na tabela.");
        }
        
        // Mostrar detalhes das colunas
        $columnDetails = DB::select("PRAGMA table_info({$table})");
        $this->info("\nDetalhes das colunas:");
        foreach ($columnDetails as $column) {
            $this->line("- {$column->name} ({$column->type}) - Nullable: " . ($column->notnull ? 'No' : 'Yes'));
        }
        
        return 0;
    }
}