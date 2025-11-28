<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('funcionario_templates', function (Blueprint $table) {
            // Adicionar campos de tipo para manhã opcional (manha2)
            $table->string('segunda_manha2_tipo')->nullable()->after('segunda_manha2_fim');
            $table->string('terca_manha2_tipo')->nullable()->after('terca_manha2_fim');
            $table->string('quarta_manha2_tipo')->nullable()->after('quarta_manha2_fim');
            $table->string('quinta_manha2_tipo')->nullable()->after('quinta_manha2_fim');
            $table->string('sexta_manha2_tipo')->nullable()->after('sexta_manha2_fim');
            $table->string('sabado_manha2_tipo')->nullable()->after('sabado_manha2_fim');
            $table->string('domingo_manha2_tipo')->nullable()->after('domingo_manha2_fim');
            
            // Adicionar campos para período tarde
            $table->time('segunda_tarde_inicio')->nullable()->after('domingo_manha2_tipo');
            $table->time('segunda_tarde_fim')->nullable()->after('segunda_tarde_inicio');
            $table->string('segunda_tarde_tipo')->nullable()->after('segunda_tarde_fim');
            
            $table->time('terca_tarde_inicio')->nullable()->after('segunda_tarde_tipo');
            $table->time('terca_tarde_fim')->nullable()->after('terca_tarde_inicio');
            $table->string('terca_tarde_tipo')->nullable()->after('terca_tarde_fim');
            
            $table->time('quarta_tarde_inicio')->nullable()->after('terca_tarde_tipo');
            $table->time('quarta_tarde_fim')->nullable()->after('quarta_tarde_inicio');
            $table->string('quarta_tarde_tipo')->nullable()->after('quarta_tarde_fim');
            
            $table->time('quinta_tarde_inicio')->nullable()->after('quarta_tarde_tipo');
            $table->time('quinta_tarde_fim')->nullable()->after('quinta_tarde_inicio');
            $table->string('quinta_tarde_tipo')->nullable()->after('quinta_tarde_fim');
            
            $table->time('sexta_tarde_inicio')->nullable()->after('quinta_tarde_tipo');
            $table->time('sexta_tarde_fim')->nullable()->after('sexta_tarde_inicio');
            $table->string('sexta_tarde_tipo')->nullable()->after('sexta_tarde_fim');
            
            $table->time('sabado_tarde_inicio')->nullable()->after('sexta_tarde_tipo');
            $table->time('sabado_tarde_fim')->nullable()->after('sabado_tarde_inicio');
            $table->string('sabado_tarde_tipo')->nullable()->after('sabado_tarde_fim');
            
            $table->time('domingo_tarde_inicio')->nullable()->after('sabado_tarde_tipo');
            $table->time('domingo_tarde_fim')->nullable()->after('domingo_tarde_inicio');
            $table->string('domingo_tarde_tipo')->nullable()->after('domingo_tarde_fim');
            
            // Adicionar campos de tipo para tarde opcional (tarde2)
            $table->string('segunda_tarde2_tipo')->nullable()->after('segunda_tarde2_fim');
            $table->string('terca_tarde2_tipo')->nullable()->after('terca_tarde2_fim');
            $table->string('quarta_tarde2_tipo')->nullable()->after('quarta_tarde2_fim');
            $table->string('quinta_tarde2_tipo')->nullable()->after('quinta_tarde2_fim');
            $table->string('sexta_tarde2_tipo')->nullable()->after('sexta_tarde2_fim');
            $table->string('sabado_tarde2_tipo')->nullable()->after('sabado_tarde2_fim');
            $table->string('domingo_tarde2_tipo')->nullable()->after('domingo_tarde2_fim');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('funcionario_templates', function (Blueprint $table) {
            $table->dropColumn([
                // Tipos manhã opcional
                'segunda_manha2_tipo', 'terca_manha2_tipo', 'quarta_manha2_tipo', 'quinta_manha2_tipo',
                'sexta_manha2_tipo', 'sabado_manha2_tipo', 'domingo_manha2_tipo',
                
                // Período tarde
                'segunda_tarde_inicio', 'segunda_tarde_fim', 'segunda_tarde_tipo',
                'terca_tarde_inicio', 'terca_tarde_fim', 'terca_tarde_tipo',
                'quarta_tarde_inicio', 'quarta_tarde_fim', 'quarta_tarde_tipo',
                'quinta_tarde_inicio', 'quinta_tarde_fim', 'quinta_tarde_tipo',
                'sexta_tarde_inicio', 'sexta_tarde_fim', 'sexta_tarde_tipo',
                'sabado_tarde_inicio', 'sabado_tarde_fim', 'sabado_tarde_tipo',
                'domingo_tarde_inicio', 'domingo_tarde_fim', 'domingo_tarde_tipo',
                
                // Tipos tarde opcional
                'segunda_tarde2_tipo', 'terca_tarde2_tipo', 'quarta_tarde2_tipo', 'quinta_tarde2_tipo',
                'sexta_tarde2_tipo', 'sabado_tarde2_tipo', 'domingo_tarde2_tipo'
            ]);
        });
    }
};
