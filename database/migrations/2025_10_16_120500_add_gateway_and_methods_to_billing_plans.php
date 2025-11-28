<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('billing_plans', function (Blueprint $table) {
            $table->string('gateway_alias')->nullable()->after('currency');
            $table->json('allowed_payment_methods')->nullable()->after('gateway_alias');
            $table->json('penalty_policy_by_method')->nullable()->after('penalty_policy');
            $table->index(['school_id', 'gateway_alias'], 'bp_school_gateway_idx');
        });
    }

    public function down(): void
    {
        Schema::table('billing_plans', function (Blueprint $table) {
            $table->dropIndex('bp_school_gateway_idx');
            $table->dropColumn('gateway_alias');
            $table->dropColumn('allowed_payment_methods');
            $table->dropColumn('penalty_policy_by_method');
        });
    }
};