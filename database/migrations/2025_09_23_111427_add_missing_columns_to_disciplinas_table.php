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
        Schema::table('disciplinas', function (Blueprint $table) {
            $table->string('area_conhecimento')->nullable()->after('codigo'); // Linguagens, Matemática, Ciências da Natureza, Ciências Humanas
            $table->string('cor_hex', 7)->nullable()->after('descricao'); // Para identificação visual (#FF5733)
            $table->boolean('obrigatoria')->default(true)->after('cor_hex'); // Se é obrigatória na modalidade
            $table->integer('ordem')->default(0)->after('obrigatoria'); // Para ordenação
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('disciplinas', function (Blueprint $table) {
            $table->dropColumn(['area_conhecimento', 'cor_hex', 'obrigatoria', 'ordem']);
        });
    }
};
