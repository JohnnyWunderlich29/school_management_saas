<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ModalidadeEnsino;
use App\Models\Turno;
use Illuminate\Support\Facades\DB;

class ModalidadeEnsinoTurnoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buscar modalidades e turnos
        $modalidades = ModalidadeEnsino::all();
        $turnos = Turno::all();

        // Relacionamentos entre modalidades e turnos
        $relacionamentos = [
            'EI' => ['MAT', 'VES', 'INT'], // Educação Infantil: Matutino, Vespertino, Integral
            'EF1' => ['MAT', 'VES', 'INT'], // Ensino Fundamental I: Matutino, Vespertino, Integral
            'EF2' => ['MAT', 'VES', 'INT'], // Ensino Fundamental II: Matutino, Vespertino, Integral
            'EM' => ['MAT', 'VES', 'NOT'], // Ensino Médio: Matutino, Vespertino, Noturno
            'EJA' => ['VES', 'NOT'], // EJA: Vespertino, Noturno
            'EE' => ['MAT', 'VES', 'INT'], // Educação Especial: Matutino, Vespertino, Integral
            'EP' => ['MAT', 'VES', 'NOT'] // Educação Profissional: Matutino, Vespertino, Noturno
        ];

        foreach ($modalidades as $modalidade) {
            if (isset($relacionamentos[$modalidade->codigo])) {
                $turnosParaModalidade = $relacionamentos[$modalidade->codigo];
                
                foreach ($turnosParaModalidade as $codigoTurno) {
                    $turno = $turnos->where('codigo', $codigoTurno)->first();
                    
                    if ($turno) {
                        // Verificar se o relacionamento já existe
                        $existe = DB::table('modalidade_ensino_turno')
                            ->where('modalidade_ensino_id', $modalidade->id)
                            ->where('turno_id', $turno->id)
                            ->exists();
                        
                        if (!$existe) {
                            DB::table('modalidade_ensino_turno')->insert([
                                'modalidade_ensino_id' => $modalidade->id,
                                'turno_id' => $turno->id,
                                'created_at' => now(),
                                'updated_at' => now()
                            ]);
                        }
                    }
                }
            }
        }
    }
}
