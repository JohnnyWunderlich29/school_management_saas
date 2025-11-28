<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('escolas', function (Blueprint $table) {
            if (!Schema::hasColumn('escolas', 'plan_id')) {
                $table->unsignedBigInteger('plan_id')->nullable()->after('plano');
                $table->foreign('plan_id')->references('id')->on('plans')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('escolas', function (Blueprint $table) {
            if (Schema::hasColumn('escolas', 'plan_id')) {
                $table->dropForeign(['plan_id']);
                $table->dropColumn('plan_id');
            }
        });
    }
};