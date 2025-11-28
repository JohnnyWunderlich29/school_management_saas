<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('multa_regras', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('escola_id');
            $table->decimal('taxa_por_dia', 8, 2)->default(0);
            $table->decimal('valor_maximo', 8, 2)->nullable();
            $table->json('excecoes')->nullable();
            $table->timestamps();

            $table->foreign('escola_id')->references('id')->on('escolas')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('multa_regras');
    }
};