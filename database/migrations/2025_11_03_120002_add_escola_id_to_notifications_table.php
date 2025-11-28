<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('notifications') && !Schema::hasColumn('notifications', 'escola_id')) {
            Schema::table('notifications', function (Blueprint $table) {
                $table->foreignId('escola_id')->nullable()->after('user_id')->constrained('escolas')->onDelete('cascade');
                $table->index(['escola_id']);
            });

            // Backfill: set escola_id for user-targeted notifications using the user's escola_id
            try {
                DB::statement('UPDATE notifications SET escola_id = (
                    SELECT users.escola_id FROM users WHERE users.id = notifications.user_id
                ) WHERE escola_id IS NULL AND user_id IS NOT NULL');
            } catch (\Throwable $e) {
                // Ignore backfill errors to keep migration forward-only
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('notifications') && Schema::hasColumn('notifications', 'escola_id')) {
            Schema::table('notifications', function (Blueprint $table) {
                try { $table->dropForeign(['escola_id']); } catch (\Throwable $e) { /* ignore */ }
                try { $table->dropIndex(['escola_id']); } catch (\Throwable $e) { /* ignore */ }
                $table->dropColumn('escola_id');
            });
        }
    }
};