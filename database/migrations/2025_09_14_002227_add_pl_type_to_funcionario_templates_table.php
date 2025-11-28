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
        // Usar string ao invés de enum para compatibilidade com PostgreSQL
        Schema::table('funcionario_templates', function (Blueprint $table) {
            $dias = ['segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado', 'domingo'];
            
            foreach ($dias as $dia) {
                // Alterar colunas existentes para string
                $table->string("{$dia}_tipo", 50)->nullable()->change();
                $table->string("{$dia}_manha2_tipo", 50)->nullable()->change();
                $table->string("{$dia}_tarde_tipo", 50)->nullable()->change();
                $table->string("{$dia}_tarde2_tipo", 50)->nullable()->change();
            }
        });
        
        // Adicionar constraints de check para PostgreSQL (apenas se não existirem)
        if ($isPgsql) {
            $dias = ['segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado', 'domingo'];
            foreach ($dias as $dia) {
                // Remover constraints existentes primeiro
                DB::statement("ALTER TABLE funcionario_templates DROP CONSTRAINT IF EXISTS funcionario_templates_{$dia}_tipo_check");
                DB::statement("ALTER TABLE funcionario_templates DROP CONSTRAINT IF EXISTS funcionario_templates_{$dia}_manha2_tipo_check");
                DB::statement("ALTER TABLE funcionario_templates DROP CONSTRAINT IF EXISTS funcionario_templates_{$dia}_tarde_tipo_check");
                DB::statement("ALTER TABLE funcionario_templates DROP CONSTRAINT IF EXISTS funcionario_templates_{$dia}_tarde2_tipo_check");
                
                // Adicionar novas constraints
                DB::statement("ALTER TABLE funcionario_templates ADD CONSTRAINT funcionario_templates_{$dia}_tipo_check CHECK ({$dia}_tipo IN ('Normal', 'Extra', 'Substituição', 'PL') OR {$dia}_tipo IS NULL)");
                DB::statement("ALTER TABLE funcionario_templates ADD CONSTRAINT funcionario_templates_{$dia}_manha2_tipo_check CHECK ({$dia}_manha2_tipo IN ('Normal', 'Extra', 'Substituição', 'PL') OR {$dia}_manha2_tipo IS NULL)");
                DB::statement("ALTER TABLE funcionario_templates ADD CONSTRAINT funcionario_templates_{$dia}_tarde_tipo_check CHECK ({$dia}_tarde_tipo IN ('Normal', 'Extra', 'Substituição', 'PL') OR {$dia}_tarde_tipo IS NULL)");
                DB::statement("ALTER TABLE funcionario_templates ADD CONSTRAINT funcionario_templates_{$dia}_tarde2_tipo_check CHECK ({$dia}_tarde2_tipo IN ('Normal', 'Extra', 'Substituição', 'PL') OR {$dia}_tarde2_tipo IS NULL)");
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $isPgsql = DB::getDriverName() === 'pgsql';
        // Remover constraints de check
        if ($isPgsql) {
            $dias = ['segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado', 'domingo'];
            foreach ($dias as $dia) {
                DB::statement("ALTER TABLE funcionario_templates DROP CONSTRAINT IF EXISTS funcionario_templates_{$dia}_tipo_check");
                DB::statement("ALTER TABLE funcionario_templates DROP CONSTRAINT IF EXISTS funcionario_templates_{$dia}_manha2_tipo_check");
                DB::statement("ALTER TABLE funcionario_templates DROP CONSTRAINT IF EXISTS funcionario_templates_{$dia}_tarde_tipo_check");
                DB::statement("ALTER TABLE funcionario_templates DROP CONSTRAINT IF EXISTS funcionario_templates_{$dia}_tarde2_tipo_check");
            }
        }
        
        Schema::table('funcionario_templates', function (Blueprint $table) {
            $dias = ['segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado', 'domingo'];
            
            foreach ($dias as $dia) {
                // Reverter para string sem restrições
                $table->string("{$dia}_tipo", 50)->nullable()->change();
                $table->string("{$dia}_manha2_tipo", 50)->nullable()->change();
                $table->string("{$dia}_tarde_tipo", 50)->nullable()->change();
                $table->string("{$dia}_tarde2_tipo", 50)->nullable()->change();
            }
        });
    }
};
