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
        if (Schema::hasColumn('planejamentos', 'nivel_ensino_id')) {
            Schema::table('planejamentos', function (Blueprint $table) {
                // Tornar nivel_ensino_id nullable
                $table->foreignId('nivel_ensino_id')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('planejamentos', 'nivel_ensino_id')) {
            Schema::table('planejamentos', function (Blueprint $table) {
                // Reverter para not null
                $table->foreignId('nivel_ensino_id')->nullable(false)->change();
            });
        }
    }
};
