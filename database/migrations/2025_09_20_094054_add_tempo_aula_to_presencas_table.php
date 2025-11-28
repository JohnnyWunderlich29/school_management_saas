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
        Schema::table('presencas', function (Blueprint $table) {
            $table->integer('tempo_aula')->nullable()->after('data');
            $table->index(['aluno_id', 'data', 'tempo_aula']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('presencas', function (Blueprint $table) {
            $table->dropIndex(['aluno_id', 'data', 'tempo_aula']);
            $table->dropColumn('tempo_aula');
        });
    }
};
