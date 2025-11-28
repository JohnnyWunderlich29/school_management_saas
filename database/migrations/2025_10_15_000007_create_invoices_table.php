<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('subscription_id');
            $table->string('number')->nullable();
            $table->date('due_date');
            $table->unsignedInteger('total_cents');
            $table->string('currency', 8)->default('BRL');
            $table->string('status', 16)->default('pending'); // pending, paid, overdue, canceled
            $table->string('gateway_alias')->nullable();
            $table->string('charge_id')->nullable();
            $table->text('boleto_url')->nullable();
            $table->string('barcode')->nullable();
            $table->string('linha_digitavel')->nullable();
            $table->text('pix_qr_code')->nullable();
            $table->text('pix_code')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'subscription_id']);
            $table->index(['school_id', 'status']);
            $table->index(['school_id', 'due_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};