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
        Schema::table('planejamentos', function (Blueprint $table) {
            if (!Schema::hasColumn('planejamentos', 'escola_id')) {
                $table->foreignId('escola_id')->nullable()->constrained('escolas')->onDelete('cascade');
                $table->index(['escola_id']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('planejamentos', function (Blueprint $table) {
            $table->dropForeign(['escola_id']);
            $table->dropIndex(['escola_id']);
            $table->dropColumn('escola_id');
        });
    }
};
