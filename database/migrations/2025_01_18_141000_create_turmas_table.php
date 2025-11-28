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
        Schema::create('turmas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nivel_ensino_id')->constrained('niveis_ensino')->onDelete('cascade');
            $table->string('nome'); // Ex: A, B, C, 1º Ano A, 2º Ano B
            $table->string('codigo'); // Ex: A, B, C
            $table->text('descricao')->nullable();
            $table->integer('capacidade'); // Capacidade específica desta turma
            $table->boolean('ativo')->default(true);
            
            // Turnos específicos desta turma (herda do nível, mas pode ser mais restritivo)
            $table->boolean('turno_matutino')->default(true);
            $table->boolean('turno_vespertino')->default(true);
            $table->boolean('turno_noturno')->default(false);
            $table->boolean('turno_integral')->default(false);
            
            // Ano letivo
            $table->year('ano_letivo')->default(date('Y'));
            
            $table->timestamps();
            
            // Índices
            $table->unique(['nivel_ensino_id', 'codigo', 'ano_letivo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('turmas');
    }
};