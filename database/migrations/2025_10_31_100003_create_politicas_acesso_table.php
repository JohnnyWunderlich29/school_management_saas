<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('politicas_acesso', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('escola_id');
            $table->string('perfil'); // aluno, professor, bibliotecario
            $table->string('tipo_item'); // fisico, digital
            $table->integer('max_emprestimos')->default(3);
            $table->integer('prazo_dias')->default(7);
            $table->integer('max_reservas')->default(3);
            $table->boolean('acesso_digital_perfil')->default(true);
            $table->json('janelas')->nullable();
            $table->json('regras')->nullable();
            $table->timestamps();

            $table->foreign('escola_id')->references('id')->on('escolas')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('politicas_acesso');
    }
};