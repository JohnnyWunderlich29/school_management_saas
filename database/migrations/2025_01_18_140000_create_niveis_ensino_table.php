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
        Schema::create('niveis_ensino', function (Blueprint $table) {
            $table->id();
            $table->string('nome'); // Ex: Berçário, Educação Infantil, Ensino Fundamental
            $table->string('codigo')->unique(); // Ex: BER, EI, EF
            $table->text('descricao')->nullable();
            $table->integer('capacidade_padrao')->default(20); // Capacidade padrão para turmas deste nível
            $table->boolean('ativo')->default(true);
            
            // Turnos disponíveis para este nível
            $table->boolean('turno_matutino')->default(true);
            $table->boolean('turno_vespertino')->default(true);
            $table->boolean('turno_noturno')->default(false);
            $table->boolean('turno_integral')->default(false);
            
            // Modalidades compatíveis
            $table->json('modalidades_compativeis')->nullable(); // Array de modalidades que podem usar este nível
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('niveis_ensino');
    }
};