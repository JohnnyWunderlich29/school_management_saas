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
        Schema::create('user_disciplinas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('disciplina_id')->constrained('disciplinas')->onDelete('cascade');
            $table->foreignId('escola_id')->nullable()->constrained('escolas')->onDelete('cascade');
            $table->boolean('ativo')->default(true);
            $table->timestamps();
            
            // Índices para melhorar a performance
            $table->index(['user_id', 'disciplina_id']);
            $table->index('escola_id');
            
            // Garantir que não haja duplicatas
            $table->unique(['user_id', 'disciplina_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_disciplinas');
    }
};
