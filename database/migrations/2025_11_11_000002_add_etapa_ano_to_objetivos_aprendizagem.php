<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('objetivos_aprendizagem', function (Blueprint $table) {
            if (!Schema::hasColumn('objetivos_aprendizagem', 'etapa')) {
                $table->string('etapa', 50)->nullable()->index();
            }
            if (!Schema::hasColumn('objetivos_aprendizagem', 'ano')) {
                $table->unsignedTinyInteger('ano')->nullable()->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('objetivos_aprendizagem', function (Blueprint $table) {
            if (Schema::hasColumn('objetivos_aprendizagem', 'etapa')) {
                $table->dropIndex(['etapa']);
                $table->dropColumn('etapa');
            }
            if (Schema::hasColumn('objetivos_aprendizagem', 'ano')) {
                $table->dropIndex(['ano']);
                $table->dropColumn('ano');
            }
        });
    }
};