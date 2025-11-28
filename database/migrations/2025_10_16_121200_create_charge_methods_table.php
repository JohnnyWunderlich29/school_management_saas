<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('charge_methods', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->string('gateway_alias');
            $table->string('method', 32); // credit_card, debit_card, pix, boleto
            $table->json('penalty_policy')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->unique(['school_id', 'gateway_alias', 'method'], 'cm_school_gateway_method_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('charge_methods');
    }
};