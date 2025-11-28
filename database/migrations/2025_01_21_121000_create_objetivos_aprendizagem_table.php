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
        Schema::create('objetivos_aprendizagem', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campo_experiencia_id')->constrained('campos_experiencia')->onDelete('cascade');
            $table->string('codigo'); // Ex: EI02EO01, EI03CG02
            $table->text('descricao');
            $table->enum('faixa_etaria', ['bebes', 'criancas_bem_pequenas', 'criancas_pequenas']);
            $table->boolean('ativo')->default(true);
            $table->timestamps();
            
            $table->index(['campo_experiencia_id', 'faixa_etaria']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('objetivos_aprendizagem');
    }
};