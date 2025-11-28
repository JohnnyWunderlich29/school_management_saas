<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('planejamento_diarios', function (Blueprint $table) {
            // Postgres-friendly: avoid column order directives
            if (!Schema::hasColumn('planejamento_diarios', 'objetivos_aprendizagem')) {
                $table->json('objetivos_aprendizagem')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('planejamento_diarios', function (Blueprint $table) {
            if (Schema::hasColumn('planejamento_diarios', 'objetivos_aprendizagem')) {
                $table->dropColumn('objetivos_aprendizagem');
            }
        });
    }
};