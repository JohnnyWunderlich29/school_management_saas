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
        Schema::create('historicos', function (Blueprint $table) {
            $table->id();
            $table->string('acao'); // create, update, delete
            $table->string('modelo'); // nome do modelo (Escala, Funcionario, etc)
            $table->unsignedBigInteger('modelo_id'); // ID do registro afetado
            $table->unsignedBigInteger('usuario_id')->nullable(); // ID do usuário que fez a ação
            $table->json('dados_antigos')->nullable(); // dados antes da alteração
            $table->json('dados_novos')->nullable(); // dados após a alteração
            $table->string('ip_address')->nullable(); // IP do usuário
            $table->string('user_agent')->nullable(); // navegador/dispositivo
            $table->text('observacoes')->nullable(); // observações adicionais
            $table->timestamps();
            
            $table->index(['modelo', 'modelo_id']);
            $table->index(['usuario_id']);
            $table->index(['acao']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historicos');
    }
};
