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
        Schema::create('escola_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('escola_id')->constrained('escolas')->onDelete('cascade');
            $table->foreignId('module_id')->constrained('modules')->onDelete('cascade');
            $table->boolean('is_active')->default(true); // Se o módulo está ativo para a escola
            $table->decimal('monthly_price', 10, 2); // Preço mensal acordado (pode ser diferente do preço padrão)
            $table->date('contracted_at'); // Data de contratação
            $table->date('expires_at')->nullable(); // Data de expiração (null = sem expiração)
            $table->foreignId('contracted_by')->nullable()->constrained('users')->onDelete('set null'); // Usuário que contratou
            $table->text('notes')->nullable(); // Observações sobre a contratação
            $table->json('settings')->nullable(); // Configurações específicas do módulo para a escola
            $table->timestamps();
            
            // Índices para performance
            $table->index(['escola_id', 'is_active']);
            $table->index(['module_id', 'is_active']);
            $table->index(['expires_at']);
            
            // Constraint para evitar módulos duplicados por escola
            $table->unique(['escola_id', 'module_id'], 'unique_escola_module');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('escola_modules');
    }
};