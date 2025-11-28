<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('gateway_status', 64)->nullable()->after('gateway_alias');
            $table->string('gateway_error_code', 128)->nullable()->after('gateway_status');
            $table->text('gateway_error')->nullable()->after('gateway_error_code');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('gateway_status');
            $table->dropColumn('gateway_error_code');
            $table->dropColumn('gateway_error');
        });
    }
};