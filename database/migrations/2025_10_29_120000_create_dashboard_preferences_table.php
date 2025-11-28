<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('dashboard_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            // Referencia a tabela 'escolas' (campo id) mas guarda como 'school_id' para consistência do domínio
            $table->foreignId('school_id')->nullable()->constrained('escolas')->onDelete('cascade');
            $table->json('state');
            $table->timestamps();

            $table->unique(['user_id', 'school_id']);
            $table->index(['school_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dashboard_preferences');
    }
};