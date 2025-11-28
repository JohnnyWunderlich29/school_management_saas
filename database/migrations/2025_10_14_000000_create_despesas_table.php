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
        Schema::create('despesas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('escola_id')->index();
            $table->string('descricao');
            $table->string('categoria')->nullable();
            $table->date('data');
            $table->decimal('valor', 12, 2)->default(0);
            $table->string('status')->default('pendente'); // pendente, liquidada, cancelada
            $table->text('cancelamento_motivo')->nullable();
            $table->unsignedBigInteger('cancelado_por')->nullable();
            $table->timestamp('cancelado_em')->nullable();
            $table->timestamps();

            // Índices úteis
            $table->index(['escola_id', 'data']);
            $table->index(['escola_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('despesas');
    }
};