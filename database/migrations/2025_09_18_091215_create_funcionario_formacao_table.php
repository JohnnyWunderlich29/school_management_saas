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
        if (!Schema::hasTable('funcionario_formacao')) {
            Schema::create('funcionario_formacao', function (Blueprint $table) {
                $table->foreignId('funcionario_id')->constrained()->onDelete('cascade');
                $table->foreignId('formacao_id')->constrained('formacoes')->onDelete('cascade');
                $table->primary(['funcionario_id', 'formacao_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('funcionario_formacao');
    }
};
