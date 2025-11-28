<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('planejamento_diarios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('planejamento_id');
            $table->date('data');
            $table->unsignedTinyInteger('dia_semana')->nullable(); // 0-6 (domingo-sábado)
            $table->boolean('planejado')->default(true);

            // Conteúdo pedagógico diário
            $table->json('campos_experiencia')->nullable();
            $table->text('saberes_conhecimentos')->nullable();
            $table->text('objetivos_especificos')->nullable();
            $table->text('metodologia')->nullable();
            $table->json('recursos_predefinidos')->nullable();
            $table->text('recursos_personalizados')->nullable();

            $table->timestamps();

            $table->foreign('planejamento_id')
                ->references('id')
                ->on('planejamentos')
                ->onDelete('cascade');

            $table->unique(['planejamento_id', 'data']);
            $table->index(['planejamento_id']);
            $table->index(['data']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planejamento_diarios');
    }
};