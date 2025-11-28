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
        Schema::create('finance_gateways', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->string('alias'); // ex.: asaas, gerencianet
            $table->string('name')->nullable();
            $table->text('credentials_encrypted')->nullable();
            $table->text('webhook_secret_encrypted')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['school_id', 'alias']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('finance_gateways');
    }
};