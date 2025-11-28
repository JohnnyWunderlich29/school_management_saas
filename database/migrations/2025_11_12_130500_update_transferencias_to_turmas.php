<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transferencias', function (Blueprint $table) {
            // Add new turma columns
            $table->foreignId('turma_id')->nullable()->after('aluno_id')->constrained('turmas')->nullOnDelete();
            $table->foreignId('turma_destino_id')->nullable()->after('turma_id')->constrained('turmas')->cascadeOnDelete();
        });

        // Backfill turma_id and turma_destino_id from existing sala relations (PostgreSQL syntax)
        // 1) turma_id from aluno.turma_id (mais confiável no contexto atual)
        DB::statement("UPDATE transferencias t
            SET turma_id = a.turma_id
            FROM alunos a
            WHERE a.id = t.aluno_id AND t.turma_id IS NULL");

        // 2) turma_destino_id: não há mapeamento confiável sem vínculo direto sala->turma
        // Mantemos NULL para registros antigos; novos registros usarão turma_destino_id diretamente.

        Schema::table('transferencias', function (Blueprint $table) {
            // Remove old foreign keys and columns
            if (Schema::hasColumn('transferencias', 'sala_origem_id')) {
                $table->dropForeign(['sala_origem_id']);
                $table->dropColumn('sala_origem_id');
            }
            if (Schema::hasColumn('transferencias', 'sala_destino_id')) {
                $table->dropForeign(['sala_destino_id']);
                $table->dropColumn('sala_destino_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('transferencias', function (Blueprint $table) {
            // Recreate sala columns
            $table->foreignId('sala_origem_id')->nullable()->constrained('salas')->nullOnDelete();
            $table->foreignId('sala_destino_id')->nullable()->constrained('salas')->nullOnDelete();
        });

        // Optionally try to backfill sala columns from turmas via salas.turma_id (PostgreSQL syntax)
        // This may not be perfect if multiple salas share a turma; it will pick an arbitrary matching sala.
        // 1) sala_origem_id from turma_id
        DB::statement("UPDATE transferencias t
            SET sala_origem_id = so.id
            FROM salas so
            WHERE so.turma_id = t.turma_id AND t.sala_origem_id IS NULL");

        // 2) sala_destino_id from turma_destino_id
        DB::statement("UPDATE transferencias t
            SET sala_destino_id = sd.id
            FROM salas sd
            WHERE sd.turma_id = t.turma_destino_id AND t.sala_destino_id IS NULL");

        Schema::table('transferencias', function (Blueprint $table) {
            // Remove turma columns
            if (Schema::hasColumn('transferencias', 'turma_destino_id')) {
                $table->dropForeign(['turma_destino_id']);
                $table->dropColumn('turma_destino_id');
            }
            if (Schema::hasColumn('transferencias', 'turma_id')) {
                $table->dropForeign(['turma_id']);
                $table->dropColumn('turma_id');
            }
        });
    }
};