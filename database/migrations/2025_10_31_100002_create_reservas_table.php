<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reservas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('escola_id');
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('usuario_id');
            $table->dateTime('data_reserva');
            $table->string('status')->default('ativa'); // ativa, cancelada, expirada
            $table->dateTime('expires_at')->nullable();
            $table->string('prioridade')->nullable(); // ex.: professor prioritário
            $table->timestamps();

            $table->foreign('escola_id')->references('id')->on('escolas')->onDelete('cascade');
            $table->foreign('item_id')->references('id')->on('item_biblioteca')->onDelete('cascade');
            $table->foreign('usuario_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservas');
    }
};