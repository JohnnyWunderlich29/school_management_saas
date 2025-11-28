<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('billing_plans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->string('name');
            $table->unsignedInteger('amount_cents');
            $table->string('currency', 8)->default('BRL');
            $table->string('periodicity', 16)->default('monthly'); // monthly
            $table->unsignedTinyInteger('day_of_month')->default(5); // dia padrão de cobrança
            $table->unsignedTinyInteger('grace_days')->default(0);
            $table->json('penalty_policy')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['school_id', 'active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_plans');
    }
};