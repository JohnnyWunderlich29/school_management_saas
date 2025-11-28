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
        Schema::table('escolas', function (Blueprint $table) {
            // Contato e endereço
            if (!Schema::hasColumn('escolas', 'celular')) {
                $table->string('celular', 20)->nullable()->after('telefone');
            }
            if (!Schema::hasColumn('escolas', 'cep')) {
                $table->string('cep', 9)->nullable()->after('email');
            }
            if (!Schema::hasColumn('escolas', 'numero')) {
                $table->string('numero', 10)->nullable()->after('endereco');
            }
            if (!Schema::hasColumn('escolas', 'complemento')) {
                $table->string('complemento')->nullable()->after('numero');
            }
            if (!Schema::hasColumn('escolas', 'bairro')) {
                $table->string('bairro')->nullable()->after('complemento');
            }
            if (!Schema::hasColumn('escolas', 'cidade')) {
                $table->string('cidade')->nullable()->after('bairro');
            }
            if (!Schema::hasColumn('escolas', 'estado')) {
                $table->string('estado', 2)->nullable()->after('cidade');
            }

            // Configurações e mídia
            if (!Schema::hasColumn('escolas', 'configuracoes')) {
                $table->json('configuracoes')->nullable();
            }
            if (!Schema::hasColumn('escolas', 'logo')) {
                $table->string('logo')->nullable();
            }
            if (!Schema::hasColumn('escolas', 'descricao')) {
                $table->text('descricao')->nullable();
            }

            // Limites e plano
            if (!Schema::hasColumn('escolas', 'max_usuarios')) {
                $table->unsignedInteger('max_usuarios')->nullable();
            }
            if (!Schema::hasColumn('escolas', 'max_alunos')) {
                $table->unsignedInteger('max_alunos')->nullable();
            }
            if (!Schema::hasColumn('escolas', 'plano')) {
                $table->string('plano')->nullable();
            }
            if (!Schema::hasColumn('escolas', 'data_vencimento')) {
                $table->date('data_vencimento')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('escolas', function (Blueprint $table) {
            // Remover colunas adicionadas — usar checks para evitar erros
            if (Schema::hasColumn('escolas', 'celular')) {
                $table->dropColumn('celular');
            }
            if (Schema::hasColumn('escolas', 'cep')) {
                $table->dropColumn('cep');
            }
            if (Schema::hasColumn('escolas', 'numero')) {
                $table->dropColumn('numero');
            }
            if (Schema::hasColumn('escolas', 'complemento')) {
                $table->dropColumn('complemento');
            }
            if (Schema::hasColumn('escolas', 'bairro')) {
                $table->dropColumn('bairro');
            }
            if (Schema::hasColumn('escolas', 'cidade')) {
                $table->dropColumn('cidade');
            }
            if (Schema::hasColumn('escolas', 'estado')) {
                $table->dropColumn('estado');
            }
            if (Schema::hasColumn('escolas', 'configuracoes')) {
                $table->dropColumn('configuracoes');
            }
            if (Schema::hasColumn('escolas', 'logo')) {
                $table->dropColumn('logo');
            }
            if (Schema::hasColumn('escolas', 'descricao')) {
                $table->dropColumn('descricao');
            }
            if (Schema::hasColumn('escolas', 'max_usuarios')) {
                $table->dropColumn('max_usuarios');
            }
            if (Schema::hasColumn('escolas', 'max_alunos')) {
                $table->dropColumn('max_alunos');
            }
            if (Schema::hasColumn('escolas', 'plano')) {
                $table->dropColumn('plano');
            }
            if (Schema::hasColumn('escolas', 'data_vencimento')) {
                $table->dropColumn('data_vencimento');
            }
        });
    }
};