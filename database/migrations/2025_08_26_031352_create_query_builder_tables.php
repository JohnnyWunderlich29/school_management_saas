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
        // Tabela para histórico de consultas
        Schema::create('query_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('escola_id')->constrained('escolas')->onDelete('cascade');
            $table->text('query');
            $table->text('description')->nullable();
            $table->integer('execution_time_ms')->nullable();
            $table->integer('rows_returned')->nullable();
            $table->boolean('has_error')->default(false);
            $table->text('error_message')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'created_at']);
            $table->index(['escola_id', 'created_at']);
        });
        
        // Tabela para consultas favoritas
        Schema::create('query_favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('escola_id')->constrained('escolas')->onDelete('cascade');
            $table->string('name');
            $table->text('query');
            $table->text('description')->nullable();
            $table->json('tags')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'name']);
            $table->index(['escola_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('query_favorites');
        Schema::dropIfExists('query_history');
    }
};
