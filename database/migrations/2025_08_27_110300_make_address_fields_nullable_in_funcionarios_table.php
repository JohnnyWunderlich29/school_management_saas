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
        Schema::table('funcionarios', function (Blueprint $table) {
            if (Schema::hasColumn('funcionarios', 'endereco')) {
                $table->string('endereco')->nullable()->change();
            } else {
                $table->string('endereco')->nullable();
            }
            
            if (Schema::hasColumn('funcionarios', 'cidade')) {
                $table->string('cidade')->nullable()->change();
            } else {
                $table->string('cidade')->nullable();
            }
            
            if (Schema::hasColumn('funcionarios', 'estado')) {
                $table->string('estado')->nullable()->change();
            } else {
                $table->string('estado')->nullable();
            }
            
            if (Schema::hasColumn('funcionarios', 'cep')) {
                $table->string('cep')->nullable()->change();
            } else {
                $table->string('cep')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('funcionarios', function (Blueprint $table) {
            $table->string('endereco')->nullable(false)->change();
            $table->string('cidade')->nullable(false)->change();
            $table->string('estado')->nullable(false)->change();
            $table->string('cep')->nullable(false)->change();
        });
    }
};
