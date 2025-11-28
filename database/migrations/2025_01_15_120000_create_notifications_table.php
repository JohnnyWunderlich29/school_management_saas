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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // tipo da notificação (info, warning, success, error)
            $table->string('title'); // título da notificação
            $table->text('message'); // mensagem da notificação
            $table->json('data')->nullable(); // dados adicionais em JSON
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade'); // usuário destinatário
            $table->timestamp('read_at')->nullable(); // quando foi lida
            $table->boolean('is_global')->default(false); // se é uma notificação global
            $table->string('action_url')->nullable(); // URL de ação (opcional)
            $table->string('action_text')->nullable(); // texto do botão de ação (opcional)
            $table->timestamps();
            
            // Índices para performance
            $table->index(['user_id', 'read_at']);
            $table->index(['created_at']);
            $table->index(['is_global']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};