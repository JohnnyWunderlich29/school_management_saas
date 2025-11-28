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
        // Índices para tabela alunos
        Schema::table('alunos', function (Blueprint $table) {
            $table->index(['ativo', 'nome']); // Para listagens de alunos ativos
            $table->index(['sala_id', 'ativo']); // Para buscar alunos por sala
            $table->index('created_at'); // Para ordenação por data de criação
            $table->index('email'); // Para busca por email
        });

        // Índices para tabela presencas
        Schema::table('presencas', function (Blueprint $table) {
            $table->index(['data', 'presente']); // Para relatórios de presença
            $table->index(['aluno_id', 'data']); // Para histórico de presença por aluno
            $table->index(['funcionario_id', 'data']); // Para presença por funcionário
            $table->index(['data', 'hora_entrada']); // Para relatórios de horários
        });

        // Índices para tabela escalas
        Schema::table('escalas', function (Blueprint $table) {
            $table->index(['funcionario_id', 'data']); // Para buscar escalas por funcionário e data
            $table->index(['data', 'hora_inicio', 'hora_fim']); // Para verificar conflitos de horário
            $table->index(['sala_id', 'data']); // Para escalas por sala
            $table->index(['status', 'data']); // Para filtrar por status
            $table->index(['tipo_atividade', 'data']); // Para filtrar por tipo de atividade
        });

        // Índices para tabela responsaveis
        Schema::table('responsaveis', function (Blueprint $table) {
            $table->index(['nome', 'sobrenome']); // Para busca por nome
            $table->index('telefone_principal'); // Para busca por telefone
            $table->index('email'); // Para busca por email
        });

        // Índices para tabela funcionarios
        Schema::table('funcionarios', function (Blueprint $table) {
            if (Schema::hasColumn('funcionarios', 'ativo')) {
                $table->index(['ativo', 'nome']); // Para listagens de funcionários ativos
            } else {
                $table->index('nome'); // Para busca por nome
            }
            $table->index('created_at'); // Para ordenação por data de criação
        });

        // Índices para tabela aluno_responsavel
        Schema::table('aluno_responsavel', function (Blueprint $table) {
            $table->index(['aluno_id', 'responsavel_principal']); // Para buscar responsável principal
            $table->index('responsavel_id'); // Para buscar alunos por responsável
        });

        // Índices para tabela transferencias
        Schema::table('transferencias', function (Blueprint $table) {
            $table->index(['status', 'data_solicitacao']); // Para filtrar transferências por status
            $table->index(['aluno_id', 'status']); // Para histórico de transferências por aluno
            $table->index(['solicitante_id', 'data_solicitacao']); // Para transferências por solicitante
            $table->index(['aprovador_id', 'data_aprovacao']); // Para transferências por aprovador
        });

        // Índices para tabela salas
        Schema::table('salas', function (Blueprint $table) {
            if (Schema::hasColumn('salas', 'ativo')) {
                $table->index(['ativo', 'codigo']); // Para listagens de salas ativas
            } else {
                $table->index('codigo'); // Para busca por código
            }
        });

        // Índices para tabela user_salas
        Schema::table('user_salas', function (Blueprint $table) {
            $table->index('user_id'); // Para buscar salas por usuário
            $table->index('sala_id'); // Para buscar usuários por sala
        });

        // Índices para tabela cargo_permissoes
        Schema::table('cargo_permissoes', function (Blueprint $table) {
            $table->index('cargo_id'); // Para buscar permissões por cargo
            $table->index('permissao_id'); // Para buscar cargos por permissão
        });

        // Índices para tabela user_cargos
        Schema::table('user_cargos', function (Blueprint $table) {
            $table->index('user_id'); // Para buscar cargos por usuário
            $table->index('cargo_id'); // Para buscar usuários por cargo
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remover índices da tabela alunos
        Schema::table('alunos', function (Blueprint $table) {
            $table->dropIndex(['ativo', 'nome']);
            $table->dropIndex(['sala_id', 'ativo']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['email']);
        });

        // Remover índices da tabela presencas
        Schema::table('presencas', function (Blueprint $table) {
            $table->dropIndex(['data', 'presente']);
            $table->dropIndex(['aluno_id', 'data']);
            $table->dropIndex(['funcionario_id', 'data']);
            $table->dropIndex(['data', 'hora_entrada']);
        });

        // Remover índices da tabela escalas
        Schema::table('escalas', function (Blueprint $table) {
            $table->dropIndex(['funcionario_id', 'data']);
            $table->dropIndex(['data', 'hora_inicio', 'hora_fim']);
            $table->dropIndex(['sala_id', 'data']);
            $table->dropIndex(['status', 'data']);
            $table->dropIndex(['tipo_atividade', 'data']);
        });

        // Remover índices da tabela responsaveis
        Schema::table('responsaveis', function (Blueprint $table) {
            $table->dropIndex(['nome', 'sobrenome']);
            $table->dropIndex(['telefone_principal']);
            $table->dropIndex(['email']);
        });

        // Remover índices da tabela funcionarios
        Schema::table('funcionarios', function (Blueprint $table) {
            $table->dropIndex(['ativo', 'nome']);
            $table->dropIndex(['created_at']);
        });

        // Remover índices da tabela aluno_responsavel
        Schema::table('aluno_responsavel', function (Blueprint $table) {
            $table->dropIndex(['aluno_id', 'responsavel_principal']);
            $table->dropIndex(['responsavel_id']);
        });

        // Remover índices da tabela transferencias
        Schema::table('transferencias', function (Blueprint $table) {
            $table->dropIndex(['status', 'data_solicitacao']);
            $table->dropIndex(['aluno_id', 'status']);
            $table->dropIndex(['solicitante_id', 'data_solicitacao']);
            $table->dropIndex(['aprovador_id', 'data_aprovacao']);
        });

        // Remover índices da tabela salas
        Schema::table('salas', function (Blueprint $table) {
            $table->dropIndex(['ativo', 'codigo']);
        });

        // Remover índices da tabela user_salas
        Schema::table('user_salas', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['sala_id']);
        });

        // Remover índices da tabela cargo_permissoes
        Schema::table('cargo_permissoes', function (Blueprint $table) {
            $table->dropIndex(['cargo_id']);
            $table->dropIndex(['permissao_id']);
        });

        // Remover índices da tabela user_cargos
        Schema::table('user_cargos', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['cargo_id']);
        });
    }
};