<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->unsignedInteger('amount_paid_cents');
            $table->dateTime('paid_at');
            $table->string('method', 16)->nullable(); // boleto, pix
            $table->unsignedInteger('gateway_fee_cents')->default(0);
            $table->unsignedInteger('net_amount_cents')->default(0);
            $table->string('currency', 8)->default('BRL');
            $table->string('gateway_payment_id')->nullable();
            $table->string('status', 16)->default('confirmed'); // confirmed, refunded, failed
            $table->dateTime('settled_at')->nullable();
            $table->string('settlement_ref')->nullable();
            $table->string('reconciliation_status', 16)->nullable();
            $table->timestamps();

            $table->index(['invoice_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};