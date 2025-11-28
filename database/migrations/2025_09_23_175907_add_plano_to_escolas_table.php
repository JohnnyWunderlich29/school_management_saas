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
        Schema::table('escolas', function (Blueprint $table) {
            // Evitar depender da coluna 'ativo' para ordenação
            $table->enum('plano', ['basico', 'premium', 'enterprise'])->default('basico');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('escolas', function (Blueprint $table) {
            if (Schema::hasColumn('escolas', 'plano')) {
                $table->dropColumn('plano');
            }
        });
    }
};
