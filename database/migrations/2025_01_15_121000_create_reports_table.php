<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // nome do relatório
            $table->string('type'); // tipo: attendance, schedule, performance, financial
            $table->text('description')->nullable(); // descrição do relatório
            $table->json('filters'); // filtros aplicados (JSON)
            $table->json('data'); // dados do relatório (JSON)
            $table->string('format'); // formato: pdf, excel, csv
            $table->string('status')->default('pending'); // pending, processing, completed, failed
            $table->string('file_path')->nullable(); // caminho do arquivo gerado
            $table->integer('file_size')->nullable(); // tamanho do arquivo em bytes
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // usuário que gerou
            $table->timestamp('generated_at')->nullable(); // quando foi gerado
            $table->timestamp('expires_at')->nullable(); // quando expira
            $table->timestamps();
            
            // Índices para performance
            $table->index(['user_id', 'status']);
            $table->index(['type', 'created_at']);
            $table->index(['status']);
            $table->index(['expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};