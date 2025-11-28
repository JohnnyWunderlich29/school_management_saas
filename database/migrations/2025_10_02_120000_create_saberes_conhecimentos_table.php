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
        Schema::create('saberes_conhecimentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campo_experiencia_id')->constrained('campos_experiencia')->onDelete('cascade');
            $table->string('titulo');
            $table->text('descricao')->nullable();
            $table->unsignedSmallInteger('ordem')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->unique(['campo_experiencia_id', 'titulo']);
            $table->index(['campo_experiencia_id', 'ativo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saberes_conhecimentos');
    }
};