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
        Schema::table('escalas', function (Blueprint $table) {
            $table->foreignId('sala_id')->nullable()->constrained('salas')->onDelete('set null');
            $table->enum('tipo_atividade', ['em_sala', 'pl', 'ausente'])->default('em_sala');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('escalas', function (Blueprint $table) {
            $table->dropForeign(['sala_id']);
            $table->dropColumn(['sala_id', 'tipo_atividade']);
        });
    }
};
