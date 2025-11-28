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
        Schema::create('multas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('escola_id')->nullable();
            $table->unsignedBigInteger('emprestimo_id');
            $table->unsignedBigInteger('usuario_id');
            $table->decimal('valor', 10, 2)->default(0);
            $table->string('status')->nullable(); // pendente, paga, cancelada
            $table->boolean('paga')->default(false);
            $table->dateTime('data_multa')->nullable();
            $table->timestamps();

            $table->foreign('emprestimo_id')->references('id')->on('emprestimos')->onDelete('cascade');
            $table->foreign('usuario_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('multas');
    }
};