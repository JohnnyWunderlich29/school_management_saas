<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add columns to subscriptions
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->integer('early_discount_value')->nullable()->after('discount_percent');
            $table->integer('early_discount_days')->nullable()->after('early_discount_value');
            $table->boolean('early_discount_active')->default(false)->after('early_discount_days');
        });

        // Add columns to responsaveis
        Schema::table('responsaveis', function (Blueprint $table) {
            $table->boolean('consolidate_billing')->default(true)->after('ativo');
        });

        // Add columns to invoices
        Schema::table('invoices', function (Blueprint $table) {
            $table->boolean('is_consolidated')->default(false)->after('status');
        });

        // Create billing_automations table
        Schema::create('billing_automations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('escolas')->onDelete('cascade');
            $table->string('name');
            $table->integer('days_advance')->default(5);
            $table->boolean('consolidate_default')->default(true);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // Create invoice_items table
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade');
            $table->foreignId('subscription_id')->nullable()->constrained('subscriptions')->onDelete('set null');
            $table->string('description');
            $table->integer('amount_cents');
            $table->integer('qty')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('billing_automations');

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('is_consolidated');
        });

        Schema::table('responsaveis', function (Blueprint $table) {
            $table->dropColumn('consolidate_billing');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn(['early_discount_value', 'early_discount_days', 'early_discount_active']);
        });
    }
};
