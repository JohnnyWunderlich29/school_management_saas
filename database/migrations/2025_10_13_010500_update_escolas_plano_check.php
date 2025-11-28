<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $isPgsql = DB::getDriverName() === 'pgsql';
        // Em PostgreSQL, o enum do Laravel é um VARCHAR com CHECK constraint.
        // Removemos a constraint para permitir slugs dinâmicos (ex.: trial) oriundos da tabela plans.
        if ($isPgsql) {
            DB::statement('ALTER TABLE escolas DROP CONSTRAINT IF EXISTS escolas_plano_check');
        }

        // Opcional: garantir que a coluna seja VARCHAR sem restrições adicionais
        // (normalmente já é), mantendo os valores existentes.
        // DB::statement('ALTER TABLE escolas ALTER COLUMN plano TYPE VARCHAR(50) USING plano::text');
    }

    public function down(): void
    {
        $isPgsql = DB::getDriverName() === 'pgsql';
        // Restaurar a constraint original com valores conhecidos
        // Caso você prefira manter uma lista, inclua também "trial".
        // Aqui reintroduzimos a constraint com os valores básicos originais.
        if ($isPgsql) {
            try {
                DB::statement("ALTER TABLE escolas ADD CONSTRAINT escolas_plano_check CHECK (plano IN ('basico','premium','enterprise'))");
            } catch (\Exception $e) {
                // Ignorar erros se a constraint já existir ou se o tipo da coluna não permitir
            }
        }
    }
};