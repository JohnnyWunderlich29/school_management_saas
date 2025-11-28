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
        Schema::create('funcionario_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('funcionario_id')->constrained('funcionarios')->onDelete('cascade');
            $table->string('nome_template')->nullable(); // Nome do template (ex: "Padrão Manhã", "Escala Noturna")
            $table->boolean('ativo')->default(true);
            
            // Horários para cada dia da semana
            $table->time('segunda_inicio')->nullable();
            $table->time('segunda_fim')->nullable();
            $table->enum('segunda_tipo', ['Normal', 'Extra', 'Substituição'])->nullable();
            
            $table->time('terca_inicio')->nullable();
            $table->time('terca_fim')->nullable();
            $table->enum('terca_tipo', ['Normal', 'Extra', 'Substituição'])->nullable();
            
            $table->time('quarta_inicio')->nullable();
            $table->time('quarta_fim')->nullable();
            $table->enum('quarta_tipo', ['Normal', 'Extra', 'Substituição'])->nullable();
            
            $table->time('quinta_inicio')->nullable();
            $table->time('quinta_fim')->nullable();
            $table->enum('quinta_tipo', ['Normal', 'Extra', 'Substituição'])->nullable();
            
            $table->time('sexta_inicio')->nullable();
            $table->time('sexta_fim')->nullable();
            $table->enum('sexta_tipo', ['Normal', 'Extra', 'Substituição'])->nullable();
            
            $table->time('sabado_inicio')->nullable();
            $table->time('sabado_fim')->nullable();
            $table->enum('sabado_tipo', ['Normal', 'Extra', 'Substituição'])->nullable();
            
            $table->time('domingo_inicio')->nullable();
            $table->time('domingo_fim')->nullable();
            $table->enum('domingo_tipo', ['Normal', 'Extra', 'Substituição'])->nullable();
            
            $table->timestamps();
            
            // Índices para performance
            $table->index(['funcionario_id', 'ativo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('funcionario_templates');
    }
};
