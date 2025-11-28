<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('historicos', function (Blueprint $table) {
            $table->unsignedBigInteger('escola_id')->nullable()->after('usuario_id');
            $table->index(['escola_id', 'modelo', 'modelo_id']);
        });
    }

    public function down(): void
    {
        Schema::table('historicos', function (Blueprint $table) {
            $table->dropIndex(['escola_id', 'modelo', 'modelo_id']);
            $table->dropColumn('escola_id');
        });
    }
};

