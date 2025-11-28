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
        $isSqlite = $driver === 'sqlite';
        $hasCapacidade = Schema::hasColumn('salas', 'capacidade');
        $hasTipo = Schema::hasColumn('salas', 'tipo');
        
        Schema::table('salas', function (Blueprint $table) use ($isSqlite, $hasCapacidade, $hasTipo) {
            // Verificar novamente dentro do closure para evitar inconsistências de introspecção em SQLite
            if (!$hasCapacidade && !Schema::hasColumn('salas', 'capacidade')) {
                $table->integer('capacidade')->nullable()->after('descricao');
            }
            if (!$hasTipo && !Schema::hasColumn('salas', 'tipo')) {
                if ($isSqlite) {
                    // Em SQLite, usar string para evitar enum
                    $table->string('tipo', 100)->nullable()->after('capacidade');
                } else {
                    $table->enum('tipo', ['sala_aula', 'laboratorio', 'biblioteca', 'auditorio', 'quadra', 'outro'])
                          ->default('sala_aula')->after('capacidade');
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $isSqlite = DB::getDriverName() === 'sqlite';
        Schema::table('salas', function (Blueprint $table) use ($isSqlite) {
            // Evitar dropar colunas em SQLite durante testes
            if (!$isSqlite) {
                $table->dropColumn(['capacidade', 'tipo']);
            }
        });
    }
};
