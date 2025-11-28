<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('arquivos_digitais', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('escola_id');
            $table->unsignedBigInteger('item_id');
            $table->string('tipo'); // pdf, epub, mp3, mp4
            $table->string('storage_path');
            $table->unsignedBigInteger('tamanho')->nullable();
            $table->string('hash')->nullable();
            $table->json('watermark')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->timestamps();

            $table->foreign('escola_id')->references('id')->on('escolas')->onDelete('cascade');
            $table->foreign('item_id')->references('id')->on('item_biblioteca')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('arquivos_digitais');
    }
};