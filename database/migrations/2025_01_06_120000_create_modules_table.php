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
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Nome técnico do módulo (ex: 'comunicacao_module')
            $table->string('display_name'); // Nome amigável (ex: 'Comunicação Escolar')
            $table->text('description')->nullable(); // Descrição do módulo
            $table->string('icon')->nullable(); // Ícone do módulo (classe CSS ou SVG)
            $table->string('color')->default('#3B82F6'); // Cor do card do módulo
            $table->decimal('price', 10, 2)->default(0); // Preço mensal do módulo
            $table->boolean('is_active')->default(true); // Se o módulo está disponível para compra
            $table->boolean('is_core')->default(false); // Se é um módulo essencial (não pode ser desativado)
            $table->json('features')->nullable(); // Lista de funcionalidades do módulo
            $table->string('category')->default('general'); // Categoria do módulo (academic, administrative, communication, etc.)
            $table->integer('sort_order')->default(0); // Ordem de exibição
            $table->timestamps();
            
            // Índices
            $table->index(['is_active']);
            $table->index(['category']);
            $table->index(['sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};