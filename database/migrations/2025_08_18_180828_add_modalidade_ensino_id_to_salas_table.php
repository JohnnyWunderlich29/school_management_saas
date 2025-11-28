<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $isSqlite = DB::getDriverName() === 'sqlite';
        Schema::table('salas', function (Blueprint $table) use ($isSqlite) {
            $table->foreignId('modalidade_ensino_id')->nullable()->constrained('modalidades_ensino')->onDelete('set null');
            // Em SQLite, dropar coluna pode quebrar FKs durante reconstrução da tabela; evitar em ambiente de teste
            if (!$isSqlite) {
                $table->dropColumn('modalidade_ensino');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salas', function (Blueprint $table) {
            $table->dropForeign(['modalidade_ensino_id']);
            $table->dropColumn('modalidade_ensino_id');
            $table->string('modalidade_ensino')->nullable();
        });
    }
};
