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
        Schema::table('escolas', function (Blueprint $table) {
            $table->string('cnpj', 18)->nullable()->after('codigo')->comment('CNPJ da escola');
            $table->string('endereco')->nullable()->after('cnpj')->comment('Endereço da escola');
            $table->string('telefone', 20)->nullable()->after('endereco')->comment('Telefone da escola');
            $table->string('email')->nullable()->after('telefone')->comment('Email da escola');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('escolas', function (Blueprint $table) {
            $table->dropColumn(['cnpj', 'endereco', 'telefone', 'email']);
        });
    }
};
