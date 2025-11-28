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
        Schema::create('school_licenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('escola_id')->constrained('escolas')->onDelete('cascade');
            $table->string('module_name'); // Nome do módulo (ex: 'comunicacao_module')
            $table->boolean('is_active')->default(true);
            $table->datetime('expires_at');
            $table->integer('max_users')->nullable(); // Limite de usuários (futuro)
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('notes')->nullable(); // Observações sobre a licença
            $table->timestamps();
            
            // Índices para performance
            $table->index(['escola_id', 'module_name']);
            $table->index(['escola_id', 'is_active', 'expires_at']);
            
            // Constraint para evitar licenças duplicadas ativas
            $table->unique(['escola_id', 'module_name'], 'unique_active_license');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_licenses');
    }
};
