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
        Schema::table('cargos', function (Blueprint $table) {
            $table->enum('tipo_cargo', ['professor', 'coordenador', 'administrador', 'secretario', 'diretor', 'funcionario', 'outro'])
                  ->nullable()
                  ->after('nome')
                  ->comment('Tipo/categoria do cargo para identificação automática');
            
            $table->index(['tipo_cargo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cargos', function (Blueprint $table) {
            $table->dropIndex(['tipo_cargo']);
            $table->dropColumn('tipo_cargo');
        });
    }
};