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
            if (!Schema::hasColumn('planejamentos', 'user_id')) {
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->index(['user_id']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('planejamentos', function (Blueprint $table) {
            if (Schema::hasColumn('planejamentos', 'user_id')) {
                // Remover FK e índice antes da coluna, com guards para diferentes bancos
                try { $table->dropForeign(['user_id']); } catch (\Throwable $e) { /* ignore */ }
                try { $table->dropIndex(['user_id']); } catch (\Throwable $e) { /* ignore */ }
                $table->dropColumn('user_id');
            }
        });
    }
};
