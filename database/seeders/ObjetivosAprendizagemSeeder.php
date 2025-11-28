<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ObjetivosAprendizagemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buscar os IDs dos campos de experiência
        $camposExperiencia = DB::table('campos_experiencia')->pluck('id', 'nome');

        $objetivos = [
            // 1. O eu, o outro e o nós
            [
                'campo_experiencia_id' => $camposExperiencia['O eu, o outro e o nós'],
                'codigo' => 'EI01EO01',
                'descricao' => 'Demonstrar interesse ao perceber que suas ações têm efeitos nas outras crianças e nos adultos.',
                'faixa_etaria' => 'bebes',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'campo_experiencia_id' => $camposExperiencia['O eu, o outro e o nós'],
                'codigo' => 'EI01EO02',
                'descricao' => 'Perceber possibilidades e limites de seu corpo nas brincadeiras e interações.',
                'faixa_etaria' => 'bebes',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'campo_experiencia_id' => $camposExperiencia['O eu, o outro e o nós'],
                'codigo' => 'EI01EO03',
                'descricao' => 'Interagir com crianças da mesma faixa etária e adultos ao explorar espaços, materiais, objetos e brinquedos.',
                'faixa_etaria' => 'bebes',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'campo_experiencia_id' => $camposExperiencia['O eu, o outro e o nós'],
                'codigo' => 'EI01EO04',
                'descricao' => 'Comunicar necessidades, desejos e emoções, utilizando gestos, balbucios, palavras.',
                'faixa_etaria' => 'bebes',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'campo_experiencia_id' => $camposExperiencia['O eu, o outro e o nós'],
                'codigo' => 'EI01EO05',
                'descricao' => 'Reconhecer seu corpo e expressar sensações em momentos de alimentação, higiene, brincadeira e descanso.',
                'faixa_etaria' => 'bebes',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'campo_experiencia_id' => $camposExperiencia['O eu, o outro e o nós'],
                'codigo' => 'EI01EO06',
                'descricao' => 'Adaptar-se ao convívio social, interagindo com diferentes pessoas em diferentes espaços.',
                'faixa_etaria' => 'bebes',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // 2. Corpo, gestos e movimentos
            [
                'campo_experiencia_id' => $camposExperiencia['Corpo, gestos e movimentos'],
                'codigo' => 'EI01CG01',
                'descricao' => 'Explorar sons produzidos por objetos e instrumentos musicais.',
                'faixa_etaria' => 'bebes',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'campo_experiencia_id' => $camposExperiencia['Corpo, gestos e movimentos'],
                'codigo' => 'EI01CG02',
                'descricao' => 'Utilizar o corpo para se expressar e interagir com pessoas e objetos.',
                'faixa_etaria' => 'bebes',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'campo_experiencia_id' => $camposExperiencia['Corpo, gestos e movimentos'],
                'codigo' => 'EI01CG03',
                'descricao' => 'Experimentar diferentes posturas, gestos e deslocamentos no espaço.',
                'faixa_etaria' => 'bebes',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'campo_experiencia_id' => $camposExperiencia['Corpo, gestos e movimentos'],
                'codigo' => 'EI01CG04',
                'descricao' => 'Manipular objetos diversos para explorar suas características.',
                'faixa_etaria' => 'bebes',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // 3. Traços, sons, cores e formas
            [
                'campo_experiencia_id' => $camposExperiencia['Traços, sons, cores e formas'],
                'codigo' => 'EI01TS01',
                'descricao' => 'Explorar sons com o corpo, objetos e instrumentos.',
                'faixa_etaria' => 'bebes',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'campo_experiencia_id' => $camposExperiencia['Traços, sons, cores e formas'],
                'codigo' => 'EI01TS02',
                'descricao' => 'Experimentar movimentos e gestos em brincadeiras, danças e jogos.',
                'faixa_etaria' => 'bebes',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'campo_experiencia_id' => $camposExperiencia['Traços, sons, cores e formas'],
                'codigo' => 'EI01TS03',
                'descricao' => 'Produzir rabiscos e marcas gráficas com diferentes materiais.',
                'faixa_etaria' => 'bebes',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'campo_experiencia_id' => $camposExperiencia['Traços, sons, cores e formas'],
                'codigo' => 'EI01TS04',
                'descricao' => 'Explorar diferentes formas, cores e texturas em objetos e materiais do ambiente.',
                'faixa_etaria' => 'bebes',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // 4. Escuta, fala, pensamento e imaginação
            [
                'campo_experiencia_id' => $camposExperiencia['Escuta, fala, pensamento e imaginação'],
                'codigo' => 'EI01EF01',
                'descricao' => 'Reconhecer e reproduzir sons da fala em interações e jogos verbais.',
                'faixa_etaria' => 'bebes',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'campo_experiencia_id' => $camposExperiencia['Escuta, fala, pensamento e imaginação'],
                'codigo' => 'EI01EF02',
                'descricao' => 'Escutar, atentar-se e reagir a histórias, canções, rimas e parlendas.',
                'faixa_etaria' => 'bebes',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'campo_experiencia_id' => $camposExperiencia['Escuta, fala, pensamento e imaginação'],
                'codigo' => 'EI01EF03',
                'descricao' => 'Expressar-se por meio de gestos, balbucios, palavras e frases simples.',
                'faixa_etaria' => 'bebes',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'campo_experiencia_id' => $camposExperiencia['Escuta, fala, pensamento e imaginação'],
                'codigo' => 'EI01EF04',
                'descricao' => 'Participar de brincadeiras de faz-de-conta simples, imitando situações cotidianas.',
                'faixa_etaria' => 'bebes',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // 5. Espaços, tempos, quantidades, relações e transformações
            [
                'campo_experiencia_id' => $camposExperiencia['Espaços, tempos, quantidades, relações e transformações'],
                'codigo' => 'EI01ET01',
                'descricao' => 'Explorar diferentes objetos, estabelecendo relações de causa e efeito.',
                'faixa_etaria' => 'bebes',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'campo_experiencia_id' => $camposExperiencia['Espaços, tempos, quantidades, relações e transformações'],
                'codigo' => 'EI01ET02',
                'descricao' => 'Reconhecer e experimentar noções simples de espaço (dentro/fora, em cima/embaixo).',
                'faixa_etaria' => 'bebes',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'campo_experiencia_id' => $camposExperiencia['Espaços, tempos, quantidades, relações e transformações'],
                'codigo' => 'EI01ET03',
                'descricao' => 'Vivenciar a rotina (alimentação, sono, higiene, brincadeira), percebendo a sequência de acontecimentos.',
                'faixa_etaria' => 'bebes',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'campo_experiencia_id' => $camposExperiencia['Espaços, tempos, quantidades, relações e transformações'],
                'codigo' => 'EI01ET04',
                'descricao' => 'Manipular objetos para perceber suas transformações (montar, desmontar, empilhar, espalhar).',
                'faixa_etaria' => 'bebes',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('objetivos_aprendizagem')->insert($objetivos);
    }
}