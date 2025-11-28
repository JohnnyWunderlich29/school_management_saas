<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->unsignedInteger('amount_cents')->after('billing_plan_id');
            $table->string('currency', 8)->default('BRL')->after('amount_cents');
            $table->unsignedBigInteger('charge_method_id')->after('currency');
            $table->unsignedTinyInteger('day_of_month')->nullable()->after('charge_method_id');

            $table->index(['school_id', 'charge_method_id'], 'subs_school_charge_method_idx');
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropIndex('subs_school_charge_method_idx');
            $table->dropColumn('amount_cents');
            $table->dropColumn('currency');
            $table->dropColumn('charge_method_id');
            $table->dropColumn('day_of_month');
        });
    }
};