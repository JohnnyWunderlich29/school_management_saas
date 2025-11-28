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
        Schema::table('turmas', function (Blueprint $table) {
            $table->foreignId('turno_id')->nullable()->after('nivel_ensino_id')->constrained('turnos')->onDelete('set null');
            $table->foreignId('grupo_id')->nullable()->after('turno_id')->constrained('grupos')->onDelete('set null');
            $table->index(['turno_id']);
            $table->index(['grupo_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('turmas', function (Blueprint $table) {
            $table->dropForeign(['turno_id']);
            $table->dropForeign(['grupo_id']);
            $table->dropColumn(['turno_id', 'grupo_id']);
        });
    }
};
