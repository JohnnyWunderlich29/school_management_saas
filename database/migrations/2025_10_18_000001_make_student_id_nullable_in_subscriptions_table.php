<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Skip for SQLite in tests (not supported)
        if (DB::getDriverName() === 'sqlite') {
            return;
        }
        $driver = DB::getDriverName();
        if ($driver === 'pgsql') {
            // Postgres: drop NOT NULL constraint on student_id
            DB::statement('ALTER TABLE subscriptions ALTER COLUMN student_id DROP NOT NULL');
        } elseif ($driver === 'mysql') {
            // MySQL/MariaDB: usar MODIFY para alterar nullability mantendo tipo
            DB::statement('ALTER TABLE `subscriptions` MODIFY `student_id` BIGINT UNSIGNED NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Skip for SQLite in tests (not supported)
        if (DB::getDriverName() === 'sqlite') {
            return;
        }
        $driver = DB::getDriverName();
        if ($driver === 'pgsql') {
            // Postgres: set NOT NULL constraint on student_id
            DB::statement('ALTER TABLE subscriptions ALTER COLUMN student_id SET NOT NULL');
        } elseif ($driver === 'mysql') {
            // MySQL/MariaDB: voltar a NOT NULL se necessário
            DB::statement('ALTER TABLE `subscriptions` MODIFY `student_id` BIGINT UNSIGNED NOT NULL');
        }
    }
};