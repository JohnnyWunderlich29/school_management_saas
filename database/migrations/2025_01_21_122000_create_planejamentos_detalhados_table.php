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
        Schema::create('planejamentos_detalhados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('planejamento_id')->constrained('planejamentos')->onDelete('cascade');
            $table->json('campos_experiencia_selecionados')->nullable(); // IDs dos campos selecionados
            $table->text('saberes_conhecimentos')->nullable();
            $table->json('objetivos_aprendizagem_selecionados')->nullable(); // IDs dos objetivos selecionados
            $table->text('encaminhamentos_metodologicos')->nullable();
            $table->text('recursos')->nullable();
            $table->text('registros_anotacoes')->nullable();
            $table->enum('status', ['rascunho', 'finalizado', 'aprovado', 'reprovado'])->default('rascunho');
            $table->timestamp('finalizado_em')->nullable();
            $table->timestamp('aprovado_em')->nullable();
            $table->foreignId('aprovado_por')->nullable()->constrained('users')->onDelete('set null');
            $table->text('observacoes_aprovacao')->nullable();
            $table->timestamps();
            
            $table->index(['planejamento_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planejamentos_detalhados');
    }
};