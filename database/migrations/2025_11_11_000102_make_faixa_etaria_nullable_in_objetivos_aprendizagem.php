<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Esta migration foi escrita para PostgreSQL. Ignorar em outros drivers (ex.: sqlite nos testes, mysql).
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }
        if (Schema::hasTable('objetivos_aprendizagem') && Schema::hasColumn('objetivos_aprendizagem', 'faixa_etaria')) {
            // Postgres-safe: tornar coluna opcional para suportar EF (anos iniciais)
            DB::statement('ALTER TABLE objetivos_aprendizagem ALTER COLUMN faixa_etaria DROP NOT NULL');
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }
        if (Schema::hasTable('objetivos_aprendizagem') && Schema::hasColumn('objetivos_aprendizagem', 'faixa_etaria')) {
            // Reverter para NOT NULL
            DB::statement('ALTER TABLE objetivos_aprendizagem ALTER COLUMN faixa_etaria SET NOT NULL');
        }
    }
};