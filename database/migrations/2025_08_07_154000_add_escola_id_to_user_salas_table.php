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
        Schema::table('user_salas', function (Blueprint $table) {
            $table->foreignId('escola_id')->nullable()->after('sala_id')->constrained('escolas')->onDelete('cascade');
            $table->index('escola_id');
        });
        
        // Nota: A população dos dados será feita em uma migração posterior
        // após a coluna escola_id ser adicionada à tabela salas
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_salas', function (Blueprint $table) {
            $table->dropForeign(['escola_id']);
            $table->dropIndex(['escola_id']);
            $table->dropColumn('escola_id');
        });
    }
};