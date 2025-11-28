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
        Schema::table('funcionarios', function (Blueprint $table) {
            // Adicionar apenas as colunas que não existem
            if (!Schema::hasColumn('funcionarios', 'cpf')) {
                $table->string('cpf', 14)->nullable()->after('sobrenome');
            }
            if (!Schema::hasColumn('funcionarios', 'rg')) {
                $table->string('rg', 20)->nullable()->after('cpf');
            }
            if (!Schema::hasColumn('funcionarios', 'data_nascimento')) {
                $table->date('data_nascimento')->nullable()->after('rg');
            }
            if (!Schema::hasColumn('funcionarios', 'cargo')) {
                $table->string('cargo')->nullable()->after('email');
            }
            if (!Schema::hasColumn('funcionarios', 'departamento')) {
                $table->string('departamento')->nullable()->after('cargo');
            }
            if (!Schema::hasColumn('funcionarios', 'data_contratacao')) {
                $table->date('data_contratacao')->nullable()->after('departamento');
            }
            if (!Schema::hasColumn('funcionarios', 'data_demissao')) {
                $table->date('data_demissao')->nullable()->after('data_contratacao');
            }
            if (!Schema::hasColumn('funcionarios', 'salario')) {
                $table->decimal('salario', 10, 2)->nullable()->after('data_demissao');
            }
            if (!Schema::hasColumn('funcionarios', 'observacoes')) {
                $table->text('observacoes')->nullable()->after('salario');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('funcionarios', function (Blueprint $table) {
            $table->dropColumn([
                'cpf', 'rg', 'data_nascimento', 'endereco', 'cidade', 
                'estado', 'cep', 'cargo', 'departamento', 'data_contratacao', 
                'data_demissao', 'salario', 'observacoes'
            ]);
        });
    }
};
