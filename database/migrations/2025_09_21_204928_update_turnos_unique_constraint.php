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
        $isPgsql = DB::getDriverName() === 'pgsql';
        // Remover a restrição de unicidade existente no campo codigo usando PostgreSQL
        if ($isPgsql) {
            try {
                DB::statement('ALTER TABLE turnos DROP CONSTRAINT IF EXISTS turnos_codigo_unique');
            } catch (\Exception $e) {
                // Se a constraint não existir, continua
            }
        }

        // Adicionar uma nova restrição de unicidade composta por codigo e escola_id
        Schema::table('turnos', function (Blueprint $table) {
            $table->unique(['codigo', 'escola_id'], 'turnos_codigo_escola_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $isPgsql = DB::getDriverName() === 'pgsql';
        // Reverter para a restrição original
        if ($isPgsql) {
            try {
                DB::statement('ALTER TABLE turnos DROP CONSTRAINT IF EXISTS turnos_codigo_escola_id_unique');
            } catch (\Exception $e) {
                // Se a constraint não existir, continua
            }
        }
        
        // Restaurar a restrição original
        Schema::table('turnos', function (Blueprint $table) {
            $table->unique('codigo', 'turnos_codigo_unique');
        });
    }
};
