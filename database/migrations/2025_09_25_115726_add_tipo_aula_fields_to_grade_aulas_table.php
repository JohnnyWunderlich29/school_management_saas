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
        Schema::table('grade_aulas', function (Blueprint $table) {
            $table->enum('tipo_aula', ['anual', 'periodo'])->default('anual')->after('dia_semana');
            $table->enum('tipo_periodo', ['curso_intensivo', 'substituicao', 'reforco', 'recuperacao', 'outro'])->nullable()->after('tipo_aula');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grade_aulas', function (Blueprint $table) {
            $table->dropColumn(['tipo_aula', 'tipo_periodo']);
        });
    }
};
