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
            // Adicionar campos para segundo período da manhã
            $table->time('segunda_manha2_inicio')->nullable()->after('segunda_tipo');
            $table->time('segunda_manha2_fim')->nullable()->after('segunda_manha2_inicio');
            $table->time('segunda_tarde2_inicio')->nullable()->after('segunda_manha2_fim');
            $table->time('segunda_tarde2_fim')->nullable()->after('segunda_tarde2_inicio');
            
            $table->time('terca_manha2_inicio')->nullable()->after('terca_tipo');
            $table->time('terca_manha2_fim')->nullable()->after('terca_manha2_inicio');
            $table->time('terca_tarde2_inicio')->nullable()->after('terca_manha2_fim');
            $table->time('terca_tarde2_fim')->nullable()->after('terca_tarde2_inicio');
            
            $table->time('quarta_manha2_inicio')->nullable()->after('quarta_tipo');
            $table->time('quarta_manha2_fim')->nullable()->after('quarta_manha2_inicio');
            $table->time('quarta_tarde2_inicio')->nullable()->after('quarta_manha2_fim');
            $table->time('quarta_tarde2_fim')->nullable()->after('quarta_tarde2_inicio');
            
            $table->time('quinta_manha2_inicio')->nullable()->after('quinta_tipo');
            $table->time('quinta_manha2_fim')->nullable()->after('quinta_manha2_inicio');
            $table->time('quinta_tarde2_inicio')->nullable()->after('quinta_manha2_fim');
            $table->time('quinta_tarde2_fim')->nullable()->after('quinta_tarde2_inicio');
            
            $table->time('sexta_manha2_inicio')->nullable()->after('sexta_tipo');
            $table->time('sexta_manha2_fim')->nullable()->after('sexta_manha2_inicio');
            $table->time('sexta_tarde2_inicio')->nullable()->after('sexta_manha2_fim');
            $table->time('sexta_tarde2_fim')->nullable()->after('sexta_tarde2_inicio');
            
            $table->time('sabado_manha2_inicio')->nullable()->after('sabado_tipo');
            $table->time('sabado_manha2_fim')->nullable()->after('sabado_manha2_inicio');
            $table->time('sabado_tarde2_inicio')->nullable()->after('sabado_manha2_fim');
            $table->time('sabado_tarde2_fim')->nullable()->after('sabado_tarde2_inicio');
            
            $table->time('domingo_manha2_inicio')->nullable()->after('domingo_tipo');
            $table->time('domingo_manha2_fim')->nullable()->after('domingo_manha2_inicio');
            $table->time('domingo_tarde2_inicio')->nullable()->after('domingo_manha2_fim');
            $table->time('domingo_tarde2_fim')->nullable()->after('domingo_tarde2_inicio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('funcionario_templates', function (Blueprint $table) {
            $table->dropColumn([
                'segunda_manha2_inicio', 'segunda_manha2_fim', 'segunda_tarde2_inicio', 'segunda_tarde2_fim',
                'terca_manha2_inicio', 'terca_manha2_fim', 'terca_tarde2_inicio', 'terca_tarde2_fim',
                'quarta_manha2_inicio', 'quarta_manha2_fim', 'quarta_tarde2_inicio', 'quarta_tarde2_fim',
                'quinta_manha2_inicio', 'quinta_manha2_fim', 'quinta_tarde2_inicio', 'quinta_tarde2_fim',
                'sexta_manha2_inicio', 'sexta_manha2_fim', 'sexta_tarde2_inicio', 'sexta_tarde2_fim',
                'sabado_manha2_inicio', 'sabado_manha2_fim', 'sabado_tarde2_inicio', 'sabado_tarde2_fim',
                'domingo_manha2_inicio', 'domingo_manha2_fim', 'domingo_tarde2_inicio', 'domingo_tarde2_fim'
            ]);
        });
    }
};
