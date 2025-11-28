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
        Schema::table('salas', function (Blueprint $table) {
            $table->foreignId('grupo_id')->nullable()->constrained('grupos');
            $table->foreignId('turno_id')->nullable()->constrained('turnos');
            $table->string('turma', 10)->nullable(); // A, B, C, D, etc.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salas', function (Blueprint $table) {
            $table->dropForeign(['grupo_id']);
            $table->dropForeign(['turno_id']);
            $table->dropColumn(['grupo_id', 'turno_id', 'turma']);
        });
    }
};
