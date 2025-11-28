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

        // Primeiro, atualizar os dados existentes para os novos valores
        DB::statement("UPDATE escalas SET tipo_escala = 'Normal' WHERE tipo_escala IN ('Regular', 'Manhã', 'Tarde', 'Noite', 'Integral')");
        DB::statement("UPDATE escalas SET tipo_escala = 'Extra' WHERE tipo_escala IN ('Plantão', 'Reforço')");
        DB::statement("UPDATE escalas SET tipo_escala = 'Substituição' WHERE tipo_escala = 'Substituição'");
        
        // Atualizar status para os novos valores
        DB::statement("UPDATE escalas SET status = 'Agendada' WHERE status IN ('Agendado', 'agendada')");
        DB::statement("UPDATE escalas SET status = 'Ativa' WHERE status IN ('Confirmado', 'ativa')");
        DB::statement("UPDATE escalas SET status = 'Concluída' WHERE status IN ('Realizado', 'Cancelado', 'concluída', 'cancelada')");
        
        // Modificar as colunas para usar varchar com validação
        Schema::table('escalas', function (Blueprint $table) {
            $table->string('tipo_escala', 50)->default('Normal')->change();
            $table->string('status', 50)->default('Agendada')->change();
        });
        
        // Adicionar constraints de check somente em PostgreSQL
        if ($isPgsql) {
            DB::statement('ALTER TABLE escalas ADD CONSTRAINT escalas_tipo_escala_check CHECK (tipo_escala IN (\'Normal\', \'Extra\', \'Substituição\'))');
            DB::statement('ALTER TABLE escalas ADD CONSTRAINT escalas_status_check CHECK (status IN (\'Agendada\', \'Ativa\', \'Concluída\'))');
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
            DB::statement('ALTER TABLE escalas DROP CONSTRAINT IF EXISTS escalas_tipo_escala_check');
            DB::statement('ALTER TABLE escalas DROP CONSTRAINT IF EXISTS escalas_status_check');
        }
        
        Schema::table('escalas', function (Blueprint $table) {
            // Reverter para os valores antigos
            $table->string('tipo_escala')->change();
            $table->string('status')->change();
        });
    }
};
