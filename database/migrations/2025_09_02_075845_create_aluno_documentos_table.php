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
        Schema::create('aluno_documentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aluno_id')->constrained('alunos')->onDelete('cascade');
            $table->string('nome_original'); // Nome original do arquivo
            $table->string('nome_arquivo'); // Nome do arquivo no storage
            $table->string('tipo_mime'); // Tipo MIME do arquivo
            $table->integer('tamanho'); // Tamanho do arquivo em bytes
            $table->string('caminho'); // Caminho do arquivo no storage
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aluno_documentos');
    }
};
