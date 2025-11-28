<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            // Em PostgreSQL, o enum do Laravel vira VARCHAR com CHECK constraint.
            // Ajustar a constraint para incluir o novo status 'revisao'.
            DB::statement('ALTER TABLE planejamentos_detalhados DROP CONSTRAINT IF EXISTS planejamentos_detalhados_status_check');
            DB::statement("ALTER TABLE planejamentos_detalhados ADD CONSTRAINT planejamentos_detalhados_status_check CHECK (status IN ('rascunho','revisao','finalizado','aprovado','reprovado'))");
        } elseif ($driver === 'mysql') {
            // Em MySQL, alterar o enum diretamente para incluir 'revisao'.
            DB::statement("ALTER TABLE planejamentos_detalhados MODIFY COLUMN status ENUM('rascunho','revisao','finalizado','aprovado','reprovado') NOT NULL DEFAULT 'rascunho'");
        } else {
            // Outros drivers: manter como string sem restrição explícita (se aplicável).
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            // Reverter para a lista original sem 'revisao'.
            DB::statement('ALTER TABLE planejamentos_detalhados DROP CONSTRAINT IF EXISTS planejamentos_detalhados_status_check');
            DB::statement("ALTER TABLE planejamentos_detalhados ADD CONSTRAINT planejamentos_detalhados_status_check CHECK (status IN ('rascunho','finalizado','aprovado','reprovado'))");
        } elseif ($driver === 'mysql') {
            DB::statement("ALTER TABLE planejamentos_detalhados MODIFY COLUMN status ENUM('rascunho','finalizado','aprovado','reprovado') NOT NULL DEFAULT 'rascunho'");
        } else {
            // Sem ação para outros drivers.
        }
    }
};