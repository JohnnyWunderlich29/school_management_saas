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
        // Adicionar escola_id na tabela users
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'escola_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('escola_id')->nullable()->constrained('escolas')->onDelete('cascade');
                $table->index(['escola_id']);
            });
        }

        // Adicionar escola_id na tabela funcionarios
        if (Schema::hasTable('funcionarios') && !Schema::hasColumn('funcionarios', 'escola_id')) {
            Schema::table('funcionarios', function (Blueprint $table) {
                $table->foreignId('escola_id')->nullable()->constrained('escolas')->onDelete('cascade');
                $table->index(['escola_id']);
            });
        }

        // Adicionar escola_id na tabela responsaveis
        if (Schema::hasTable('responsaveis') && !Schema::hasColumn('responsaveis', 'escola_id')) {
            Schema::table('responsaveis', function (Blueprint $table) {
                $table->foreignId('escola_id')->nullable()->constrained('escolas')->onDelete('cascade');
                $table->index(['escola_id']);
            });
        }

        // Adicionar escola_id na tabela cargos
        if (Schema::hasTable('cargos') && !Schema::hasColumn('cargos', 'escola_id')) {
            Schema::table('cargos', function (Blueprint $table) {
                $table->foreignId('escola_id')->nullable()->constrained('escolas')->onDelete('cascade');
                $table->index(['escola_id']);
            });
        }

        // Adicionar escola_id na tabela planejamentos
        if (Schema::hasTable('planejamentos') && !Schema::hasColumn('planejamentos', 'escola_id')) {
            Schema::table('planejamentos', function (Blueprint $table) {
                $table->foreignId('escola_id')->nullable()->constrained('escolas')->onDelete('cascade');
                $table->index(['escola_id']);
            });
        }

        // Adicionar escola_id na tabela presencas
        if (Schema::hasTable('presencas') && !Schema::hasColumn('presencas', 'escola_id')) {
            Schema::table('presencas', function (Blueprint $table) {
                $table->foreignId('escola_id')->nullable()->constrained('escolas')->onDelete('cascade');
                $table->index(['escola_id']);
            });
        }

        // Adicionar escola_id na tabela transferencias
        if (Schema::hasTable('transferencias') && !Schema::hasColumn('transferencias', 'escola_id')) {
            Schema::table('transferencias', function (Blueprint $table) {
                $table->foreignId('escola_id')->nullable()->constrained('escolas')->onDelete('cascade');
                $table->index(['escola_id']);
            });
        }

        // Adicionar escola_id na tabela modalidades_ensino
        if (Schema::hasTable('modalidades_ensino') && !Schema::hasColumn('modalidades_ensino', 'escola_id')) {
            Schema::table('modalidades_ensino', function (Blueprint $table) {
                $table->foreignId('escola_id')->nullable()->constrained('escolas')->onDelete('cascade');
                $table->index(['escola_id']);
            });
        }

        // Adicionar escola_id na tabela grupos_educacionais
        if (Schema::hasTable('grupos_educacionais') && !Schema::hasColumn('grupos_educacionais', 'escola_id')) {
            Schema::table('grupos_educacionais', function (Blueprint $table) {
                $table->foreignId('escola_id')->nullable()->constrained('escolas')->onDelete('cascade');
                $table->index(['escola_id']);
            });
        }

        // Adicionar escola_id na tabela turnos
        if (Schema::hasTable('turnos') && !Schema::hasColumn('turnos', 'escola_id')) {
            Schema::table('turnos', function (Blueprint $table) {
                $table->foreignId('escola_id')->nullable()->constrained('escolas')->onDelete('cascade');
                $table->index(['escola_id']);
            });
        }

        // Adicionar escola_id na tabela disciplinas
        if (Schema::hasTable('disciplinas') && !Schema::hasColumn('disciplinas', 'escola_id')) {
            Schema::table('disciplinas', function (Blueprint $table) {
                $table->foreignId('escola_id')->nullable()->constrained('escolas')->onDelete('cascade');
                $table->index(['escola_id']);
            });
        }

        // Adicionar escola_id na tabela relatorios
        if (Schema::hasTable('relatorios') && !Schema::hasColumn('relatorios', 'escola_id')) {
            Schema::table('relatorios', function (Blueprint $table) {
                $table->foreignId('escola_id')->nullable()->constrained('escolas')->onDelete('cascade');
                $table->index(['escola_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remover escola_id das tabelas
        $tables = [
            'users', 'funcionarios', 'responsaveis', 'salas', 'cargos',
            'planejamentos', 'presencas', 'transferencias', 'modalidades_ensino',
            'grupos_educacionais', 'turnos', 'disciplinas', 'relatorios'
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropForeign(['escola_id']);
                    $table->dropColumn('escola_id');
                });
            }
        }
    }
};