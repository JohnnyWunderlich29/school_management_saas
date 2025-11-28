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
            // Adicionar campos para definir quais turnos a sala atende
            $table->boolean('turno_matutino')->default(true)->after('capacidade');
            $table->boolean('turno_vespertino')->default(true)->after('turno_matutino');
            $table->boolean('turno_noturno')->default(false)->after('turno_vespertino');
            $table->boolean('turno_integral')->default(false)->after('turno_noturno');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salas', function (Blueprint $table) {
            $table->dropColumn([
                'turno_matutino',
                'turno_vespertino', 
                'turno_noturno',
                'turno_integral'
            ]);
        });
    }
};