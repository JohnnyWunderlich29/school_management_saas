<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('presencas', function (Blueprint $table) {
            if (!Schema::hasColumn('presencas', 'sala_id')) {
                $table->unsignedBigInteger('sala_id')->nullable()->after('aluno_id');
                $table->foreign('sala_id')->references('id')->on('salas')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('presencas', function (Blueprint $table) {
            if (Schema::hasColumn('presencas', 'sala_id')) {
                $table->dropForeign(['sala_id']);
                $table->dropColumn('sala_id');
            }
        });
    }
};
