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
        if (!Schema::hasTable('item_biblioteca')) {
            // Se a tabela ainda não existe (ordem de migrations diferente), não faz nada
            return;
        }
        Schema::table('item_biblioteca', function (Blueprint $table) {
            if (!Schema::hasColumn('item_biblioteca', 'habilitado_emprestimo')) {
                $table->boolean('habilitado_emprestimo')->default(true)->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('item_biblioteca')) {
            return;
        }
        Schema::table('item_biblioteca', function (Blueprint $table) {
            if (Schema::hasColumn('item_biblioteca', 'habilitado_emprestimo')) {
                $table->dropColumn('habilitado_emprestimo');
            }
        });
    }
};