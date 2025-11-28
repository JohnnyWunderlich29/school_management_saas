<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('planejamentos', function (Blueprint $table) {
            if (!Schema::hasColumn('planejamentos', 'tipo_professor')) {
                $table->string('tipo_professor')->nullable()->after('escola_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('planejamentos', function (Blueprint $table) {
            if (Schema::hasColumn('planejamentos', 'tipo_professor')) {
                try { $table->dropColumn('tipo_professor'); } catch (\Throwable $e) { /* ignore */ }
            }
        });
    }
};