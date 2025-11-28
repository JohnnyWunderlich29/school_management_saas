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
        Schema::table('query_favorites', function (Blueprint $table) {
            $table->dropForeign(['escola_id']);
            $table->unsignedBigInteger('escola_id')->nullable()->change();
            $table->foreign('escola_id')->references('id')->on('escolas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('query_favorites', function (Blueprint $table) {
            $table->dropForeign(['escola_id']);
            $table->unsignedBigInteger('escola_id')->nullable(false)->change();
            $table->foreign('escola_id')->references('id')->on('escolas')->onDelete('cascade');
        });
    }
};
