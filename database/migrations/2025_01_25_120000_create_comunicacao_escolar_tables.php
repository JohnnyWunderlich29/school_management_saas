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
        // Tabela de conversas
        Schema::create('conversas', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->enum('tipo', ['individual', 'grupo', 'turma', 'geral'])->default('individual');
            $table->text('descricao')->nullable();
            $table->foreignId('criador_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('turma_id')->nullable()->constrained('turmas')->onDelete('cascade');
            $table->boolean('ativo')->default(true);
            $table->timestamp('ultima_mensagem_at')->nullable();
            $table->timestamps();
            
            $table->index(['tipo', 'ativo']);
            $table->index(['turma_id']);
            $table->index(['criador_id']);
        });

        // Tabela de participantes das conversas
        Schema::create('conversa_participantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversa_id')->constrained('conversas')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('tipo_participante', ['professor', 'responsavel', 'coordenador', 'admin']);
            $table->timestamp('entrou_em');
            $table->timestamp('saiu_em')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();
            
            $table->unique(['conversa_id', 'user_id']);
            $table->index(['user_id', 'ativo']);
        });

        // Tabela de mensagens
        Schema::create('mensagens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversa_id')->constrained('conversas')->onDelete('cascade');
            $table->foreignId('remetente_id')->constrained('users')->onDelete('cascade');
            $table->text('conteudo');
            $table->enum('tipo', ['texto', 'arquivo', 'imagem', 'audio', 'video'])->default('texto');
            $table->string('arquivo_path')->nullable();
            $table->string('arquivo_nome')->nullable();
            $table->integer('arquivo_tamanho')->nullable();
            $table->boolean('importante')->default(false);
            $table->timestamp('editada_em')->nullable();
            $table->timestamps();
            
            $table->index(['conversa_id', 'created_at']);
            $table->index(['remetente_id']);
            $table->index(['importante']);
        });

        // Tabela de leitura de mensagens
        Schema::create('mensagem_leituras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mensagem_id')->constrained('mensagens')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('lida_em');
            $table->timestamps();
            
            $table->unique(['mensagem_id', 'user_id']);
            $table->index(['user_id', 'lida_em']);
        });

        // Tabela de comunicados gerais
        Schema::create('comunicados', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->text('conteudo');
            $table->enum('tipo', ['informativo', 'urgente', 'evento', 'reuniao', 'aviso']);
            $table->enum('destinatario_tipo', ['todos', 'pais', 'professores', 'turma_especifica']);
            $table->foreignId('turma_id')->nullable()->constrained('turmas')->onDelete('cascade');
            $table->foreignId('autor_id')->constrained('users')->onDelete('cascade');
            $table->boolean('requer_confirmacao')->default(false);
            $table->date('data_evento')->nullable();
            $table->time('hora_evento')->nullable();
            $table->string('local_evento')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamp('publicado_em')->nullable();
            $table->timestamps();
            
            $table->index(['tipo', 'ativo']);
            $table->index(['destinatario_tipo']);
            $table->index(['turma_id']);
            $table->index(['publicado_em']);
        });

        // Tabela de confirmações de comunicados
        Schema::create('comunicado_confirmacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comunicado_id')->constrained('comunicados')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('confirmado_em');
            $table->text('observacoes')->nullable();
            $table->timestamps();
            
            $table->unique(['comunicado_id', 'user_id']);
            $table->index(['user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comunicado_confirmacoes');
        Schema::dropIfExists('comunicados');
        Schema::dropIfExists('mensagem_leituras');
        Schema::dropIfExists('mensagens');
        Schema::dropIfExists('conversa_participantes');
        Schema::dropIfExists('conversas');
    }
};