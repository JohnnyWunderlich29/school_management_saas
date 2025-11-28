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
        Schema::table('tempo_slots', function (Blueprint $table) {
            $table->foreignId('escola_id')->nullable()->after('id')->constrained('escolas')->onDelete('cascade');
            $table->index(['escola_id', 'turno_id', 'ativo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tempo_slots', function (Blueprint $table) {
            $table->dropForeign(['escola_id']);
            $table->dropIndex(['escola_id', 'turno_id', 'ativo']);
            $table->dropColumn('escola_id');
        });
    }
};
