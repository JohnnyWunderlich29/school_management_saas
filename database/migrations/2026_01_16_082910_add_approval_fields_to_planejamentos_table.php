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
        Schema::table('planejamentos', function (Blueprint $table) {
            $table->unsignedBigInteger('aprovado_por')->nullable()->after('status');
            $table->timestamp('aprovado_em')->nullable()->after('aprovado_por');
            $table->unsignedBigInteger('rejeitado_por')->nullable()->after('aprovado_em');
            $table->timestamp('rejeitado_em')->nullable()->after('rejeitado_por');
            $table->text('observacoes_rejeicao')->nullable()->after('rejeitado_em');
            $table->integer('rejeicoes_count')->default(0)->after('observacoes_rejeicao');

            $table->foreign('aprovado_por')->references('id')->on('users')->onDelete('set null');
            $table->foreign('rejeitado_por')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('planejamentos', function (Blueprint $table) {
            $table->dropForeign(['aprovado_por']);
            $table->dropForeign(['rejeitado_por']);
            $table->dropColumn(['aprovado_por', 'aprovado_em', 'rejeitado_por', 'rejeitado_em', 'observacoes_rejeicao', 'rejeicoes_count']);
        });
    }
};
