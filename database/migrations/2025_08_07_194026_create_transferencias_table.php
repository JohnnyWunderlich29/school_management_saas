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
        Schema::create('transferencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aluno_id')->constrained('alunos')->onDelete('cascade');
            $table->foreignId('sala_origem_id')->nullable()->constrained('salas')->onDelete('set null');
            $table->foreignId('sala_destino_id')->constrained('salas')->onDelete('cascade');
            $table->foreignId('solicitante_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('aprovador_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('status', ['pendente', 'aprovada', 'rejeitada', 'cancelada'])->default('pendente');
            $table->text('motivo')->nullable();
            $table->text('observacoes_aprovador')->nullable();
            $table->timestamp('data_solicitacao');
            $table->timestamp('data_aprovacao')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transferencias');
    }
};
