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
        // Evitar SQL específico de PostgreSQL em bancos que não são pgsql (ex.: SQLite em testes)
        $isPgsql = DB::getDriverName() === 'pgsql';
        $constraintExists = false;
        if ($isPgsql) {
            $constraintExists = !empty(DB::select("SELECT 1 FROM pg_constraint WHERE conname = 'funcionarios_user_id_foreign'"));
        }

        Schema::table('funcionarios', function (Blueprint $table) use ($isPgsql, $constraintExists) {
            // Remover a constraint de foreign key somente em PostgreSQL quando existir
            if ($isPgsql && $constraintExists) {
                $table->dropForeign(['user_id']);
            }

            // Verificar se a coluna user_id existe
            if (Schema::hasColumn('funcionarios', 'user_id')) {
                // Tornar o campo nullable
                $table->foreignId('user_id')->nullable()->change();

                // Recriar a constraint de foreign key como nullable
                // Em bancos não-PG, o onDelete('set null') será traduzido conforme suporte
                $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            } else {
                // Se a coluna não existe, criá-la como nullable já com FK
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('funcionarios', function (Blueprint $table) {
            // Remover a constraint nullable
            $table->dropForeign(['user_id']);
            
            // Tornar o campo obrigatório novamente
            $table->foreignId('user_id')->change();
            
            // Recriar a constraint original
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};
