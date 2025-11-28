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
            if (!Schema::hasColumn('escolas', 'razao_social')) {
                $table->string('razao_social')->nullable()->after('nome');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('escolas', function (Blueprint $table) {
            if (Schema::hasColumn('escolas', 'razao_social')) {
                $table->dropColumn('razao_social');
            }
        });
    }
};