<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('emprestimos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('escola_id');
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('usuario_id');
            $table->dateTime('data_emprestimo');
            $table->dateTime('data_prevista');
            $table->dateTime('data_devolucao')->nullable();
            $table->string('status')->default('ativo'); // ativo, devolvido, atrasado
            $table->decimal('multa_calculada', 8, 2)->default(0);
            $table->timestamps();

            $table->foreign('escola_id')->references('id')->on('escolas')->onDelete('cascade');
            $table->foreign('item_id')->references('id')->on('item_biblioteca')->onDelete('cascade');
            $table->foreign('usuario_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emprestimos');
    }
};