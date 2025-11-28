<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('item_biblioteca', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('escola_id');
            $table->string('titulo');
            $table->string('autores')->nullable();
            $table->string('editora')->nullable();
            $table->smallInteger('ano')->nullable();
            $table->string('isbn')->nullable();
            $table->string('tipo'); // livro, revista, digital, audio, video
            $table->json('categorias')->nullable();
            $table->json('palavras_chave')->nullable();
            $table->string('status')->default('ativo');
            $table->boolean('habilitado_emprestimo')->default(true);
            $table->integer('quantidade_fisica')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('escola_id')->references('id')->on('escolas')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_biblioteca');
    }
};