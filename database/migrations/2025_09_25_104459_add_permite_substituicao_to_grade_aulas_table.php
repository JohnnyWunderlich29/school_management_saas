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
            $table->boolean('permite_substituicao')->default(false)->after('observacoes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grade_aulas', function (Blueprint $table) {
            $table->dropColumn('permite_substituicao');
        });
    }
};
