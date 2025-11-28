<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('biblioteca_politicas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('escola_id');
            $table->boolean('permitir_funcionarios')->default(true);
            $table->boolean('permitir_alunos')->default(true);
            $table->unsignedInteger('max_emprestimos_por_usuario')->nullable();
            $table->unsignedInteger('prazo_padrao_dias')->default(7);
            $table->boolean('bloquear_por_multas')->default(false);
            $table->timestamps();

            $table->unique('escola_id');
            // Ajuste de FK conforme o schema do projeto
            if (Schema::hasTable('escolas')) {
                $table->foreign('escola_id')->references('id')->on('escolas')->onDelete('cascade');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('biblioteca_politicas');
    }
};