<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('campos_experiencia', function (Blueprint $table) {
            if (!Schema::hasColumn('campos_experiencia', 'nivel')) {
                $table->string('nivel', 50)->nullable()->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('campos_experiencia', function (Blueprint $table) {
            if (Schema::hasColumn('campos_experiencia', 'nivel')) {
                $table->dropIndex(['nivel']);
                $table->dropColumn('nivel');
            }
        });
    }
};