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
        // Atualizar registros existentes com escola_id baseado na sala
        DB::statement('
            UPDATE user_salas 
            SET escola_id = (
                SELECT salas.escola_id 
                FROM salas 
                WHERE salas.id = user_salas.sala_id
            )
        ');
        
        // Tornar o campo obrigatório após popular os dados
        Schema::table('user_salas', function (Blueprint $table) {
            $table->foreignId('escola_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tornar o campo nullable novamente
        Schema::table('user_salas', function (Blueprint $table) {
            $table->foreignId('escola_id')->nullable()->change();
        });
    }
};
