<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('recorrencia_despesas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('escola_id')->index();
            $table->string('descricao');
            $table->string('categoria')->nullable();
            $table->decimal('valor', 12, 2);
            $table->string('frequencia'); // mensal, semanal, anual
            $table->integer('dia_vencimento')->nullable(); // Para mensal
            $table->date('data_inicio');
            $table->date('data_fim')->nullable();
            $table->date('proxima_geracao')->index();
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->foreign('escola_id')->references('id')->on('escolas')->onDelete('cascade');
        });

        Schema::table('despesas', function (Blueprint $table) {
            $table->unsignedBigInteger('recorrencia_id')->nullable()->after('escola_id')->index();
            $table->foreign('recorrencia_id')->references('id')->on('recorrencia_despesas')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('despesas', function (Blueprint $table) {
            $table->dropForeign(['recorrencia_id']);
            $table->dropColumn('recorrencia_id');
        });
        Schema::dropIfExists('recorrencia_despesas');
    }
};
