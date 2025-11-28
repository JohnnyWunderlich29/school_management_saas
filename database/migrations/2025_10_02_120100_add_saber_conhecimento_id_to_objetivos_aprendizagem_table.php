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
        Schema::table('objetivos_aprendizagem', function (Blueprint $table) {
            if (!Schema::hasColumn('objetivos_aprendizagem', 'saber_conhecimento_id')) {
                $table->foreignId('saber_conhecimento_id')->nullable()->constrained('saberes_conhecimentos')->onDelete('set null');
                $table->index(['saber_conhecimento_id']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('objetivos_aprendizagem', function (Blueprint $table) {
            if (Schema::hasColumn('objetivos_aprendizagem', 'saber_conhecimento_id')) {
                $table->dropForeign(['saber_conhecimento_id']);
                $table->dropIndex(['saber_conhecimento_id']);
                $table->dropColumn('saber_conhecimento_id');
            }
        });
    }
};