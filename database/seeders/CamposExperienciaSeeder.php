<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CamposExperienciaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $campos = [
            [
                'nome' => 'O eu, o outro e o nós',
                'descricao' => 'É na interação com os pares e com adultos que as crianças vão constituindo um modo próprio de agir, sentir e pensar e vão descobrindo que existem outros modos de vida, pessoas diferentes, com outros pontos de vista.',
                'ativo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nome' => 'Corpo, gestos e movimentos',
                'descricao' => 'Com o corpo (por meio dos sentidos, gestos, movimentos impulsivos ou intencionais, coordenados ou espontâneos), as crianças, desde cedo, exploram o mundo, o espaço e os objetos do seu entorno.',
                'ativo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nome' => 'Traços, sons, cores e formas',
                'descricao' => 'Conviver com diferentes manifestações artísticas, culturais e científicas, locais e universais, no cotidiano da instituição de Educação Infantil, possibilita às crianças, por meio de experiências diversificadas, vivenciar diversas formas de expressão e linguagens.',
                'ativo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nome' => 'Escuta, fala, pensamento e imaginação',
                'descricao' => 'Desde o nascimento, as crianças participam de situações comunicativas cotidianas com as pessoas com as quais interagem. As primeiras formas de interação do bebê são os movimentos do seu corpo, o olhar, a postura corporal, o sorriso, o choro e outros recursos vocais.',
                'ativo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nome' => 'Espaços, tempos, quantidades, relações e transformações',
                'descricao' => 'As crianças vivem inseridas em espaços e tempos de diferentes dimensões, em um mundo constituído de fenômenos naturais e sociais. Desde muito pequenas, elas procuram se situar em diversos espaços (rua, bairro, cidade etc.) e tempos (dia e noite; hoje, ontem e amanhã etc.).',
                'ativo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('campos_experiencia')->insert($campos);
    }
}