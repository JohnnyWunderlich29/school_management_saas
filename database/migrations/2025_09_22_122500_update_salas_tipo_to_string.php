<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();
        $isPgsql = $driver === 'pgsql';
        $isSqlite = $driver === 'sqlite';
        
        // Em SQLite, evitar alterações de coluna que exigem reconstrução de tabela com FKs
        if ($isSqlite) {
            return;
        }
        // Primeiro, remover a constraint de check existente
        if ($isPgsql) {
            DB::statement('ALTER TABLE salas DROP CONSTRAINT IF EXISTS salas_tipo_check');
        }
        
        // Alterar o campo tipo de enum para string
        Schema::table('salas', function (Blueprint $table) {
            $table->string('tipo', 100)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $isSqlite = DB::getDriverName() === 'sqlite';
        if ($isSqlite) {
            return;
        }
        // Reverter para enum (apenas se necessário)
        Schema::table('salas', function (Blueprint $table) {
            $table->enum('tipo', ['sala_aula', 'laboratorio', 'biblioteca', 'auditorio', 'quadra', 'outro'])
                  ->default('sala_aula')->change();
        });
    }
};