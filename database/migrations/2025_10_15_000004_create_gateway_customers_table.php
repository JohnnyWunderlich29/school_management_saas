<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('gateway_customers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('payer_id'); // Responsável/Pagador
            $table->string('gateway_alias');
            $table->string('external_customer_id');
            $table->string('status')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'payer_id']);
            $table->index(['school_id', 'gateway_alias']);
            $table->unique(['school_id', 'gateway_alias', 'payer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gateway_customers');
    }
};