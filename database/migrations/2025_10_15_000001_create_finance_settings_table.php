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
        Schema::create('finance_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id')->index();
            $table->string('default_gateway_alias')->nullable();
            $table->json('repasse_bank_account')->nullable();
            $table->json('penalty_policy')->nullable(); // { multa_percent, juros_dia }
            $table->json('dunning_schedule')->nullable(); // ex.: [1,3,7,14]
            $table->json('allowed_payment_methods')->nullable(); // ['boleto','pix']
            $table->json('invoice_numbering')->nullable();
            $table->json('legal_texts')->nullable();
            $table->string('timezone')->nullable();
            $table->string('currency')->default('BRL');
            $table->timestamps();
            $table->unique('school_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('finance_settings');
    }
};