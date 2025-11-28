<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PoliticaAcesso;
use App\Models\MultaRegra;
use App\Models\Escola;

class BibliotecaPoliticasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Criando políticas de acesso padrão para bibliotecas...');

        // Buscar todas as escolas para criar políticas padrão
        $escolas = Escola::all();

        foreach ($escolas as $escola) {
            $this->criarPoliticasEscola($escola->id);
            $this->criarRegrasMultaEscola($escola->id);
        }

        $this->command->info('Políticas de acesso e regras de multa criadas com sucesso!');
    }

    /**
     * Criar políticas de acesso para uma escola
     */
    private function criarPoliticasEscola(int $escolaId): void
    {
        $politicas = [
            // Políticas para Professores
            [
                'escola_id' => $escolaId,
                'perfil' => 'professor',
                'tipo_item' => 'livro',
                'max_emprestimos' => 5,
                'prazo_dias' => 15,
                'max_reservas' => 3,
                'acesso_digital_perfil' => true,
                'janelas' => ['manha', 'tarde', 'noite'],
                'regras' => [
                    'renovacao_permitida' => true,
                    'max_renovacoes' => 2,
                    'multa_aplicavel' => true
                ]
            ],
            [
                'escola_id' => $escolaId,
                'perfil' => 'professor',
                'tipo_item' => 'revista',
                'max_emprestimos' => 3,
                'prazo_dias' => 7,
                'max_reservas' => 2,
                'acesso_digital_perfil' => true,
                'janelas' => ['manha', 'tarde', 'noite'],
                'regras' => [
                    'renovacao_permitida' => true,
                    'max_renovacoes' => 1,
                    'multa_aplicavel' => true
                ]
            ],
            [
                'escola_id' => $escolaId,
                'perfil' => 'professor',
                'tipo_item' => 'audiovisual',
                'max_emprestimos' => 2,
                'prazo_dias' => 7,
                'max_reservas' => 2,
                'acesso_digital_perfil' => true,
                'janelas' => ['manha', 'tarde', 'noite'],
                'regras' => [
                    'renovacao_permitida' => false,
                    'max_renovacoes' => 0,
                    'multa_aplicavel' => true
                ]
            ],

            // Políticas para Alunos
            [
                'escola_id' => $escolaId,
                'perfil' => 'aluno',
                'tipo_item' => 'livro',
                'max_emprestimos' => 3,
                'prazo_dias' => 7,
                'max_reservas' => 2,
                'acesso_digital_perfil' => true,
                'janelas' => ['manha', 'tarde'],
                'regras' => [
                    'renovacao_permitida' => true,
                    'max_renovacoes' => 1,
                    'multa_aplicavel' => true
                ]
            ],
            [
                'escola_id' => $escolaId,
                'perfil' => 'aluno',
                'tipo_item' => 'revista',
                'max_emprestimos' => 2,
                'prazo_dias' => 5,
                'max_reservas' => 1,
                'acesso_digital_perfil' => true,
                'janelas' => ['manha', 'tarde'],
                'regras' => [
                    'renovacao_permitida' => false,
                    'max_renovacoes' => 0,
                    'multa_aplicavel' => true
                ]
            ],
            [
                'escola_id' => $escolaId,
                'perfil' => 'aluno',
                'tipo_item' => 'audiovisual',
                'max_emprestimos' => 1,
                'prazo_dias' => 3,
                'max_reservas' => 1,
                'acesso_digital_perfil' => false,
                'janelas' => ['manha', 'tarde'],
                'regras' => [
                    'renovacao_permitida' => false,
                    'max_renovacoes' => 0,
                    'multa_aplicavel' => true
                ]
            ],

            // Políticas para Funcionários
            [
                'escola_id' => $escolaId,
                'perfil' => 'funcionario',
                'tipo_item' => 'livro',
                'max_emprestimos' => 4,
                'prazo_dias' => 10,
                'max_reservas' => 2,
                'acesso_digital_perfil' => true,
                'janelas' => ['manha', 'tarde', 'noite'],
                'regras' => [
                    'renovacao_permitida' => true,
                    'max_renovacoes' => 1,
                    'multa_aplicavel' => true
                ]
            ],
            [
                'escola_id' => $escolaId,
                'perfil' => 'funcionario',
                'tipo_item' => 'revista',
                'max_emprestimos' => 2,
                'prazo_dias' => 7,
                'max_reservas' => 1,
                'acesso_digital_perfil' => true,
                'janelas' => ['manha', 'tarde', 'noite'],
                'regras' => [
                    'renovacao_permitida' => true,
                    'max_renovacoes' => 1,
                    'multa_aplicavel' => true
                ]
            ],

            // Política geral (fallback)
            [
                'escola_id' => $escolaId,
                'perfil' => 'geral',
                'tipo_item' => 'geral',
                'max_emprestimos' => 2,
                'prazo_dias' => 7,
                'max_reservas' => 1,
                'acesso_digital_perfil' => false,
                'janelas' => ['manha', 'tarde'],
                'regras' => [
                    'renovacao_permitida' => true,
                    'max_renovacoes' => 1,
                    'multa_aplicavel' => true
                ]
            ]
        ];

        foreach ($politicas as $politica) {
            PoliticaAcesso::updateOrCreate(
                [
                    'escola_id' => $politica['escola_id'],
                    'perfil' => $politica['perfil'],
                    'tipo_item' => $politica['tipo_item']
                ],
                $politica
            );
        }
    }

    /**
     * Criar regras de multa para uma escola
     */
    private function criarRegrasMultaEscola(int $escolaId): void
    {
        MultaRegra::updateOrCreate(
            ['escola_id' => $escolaId],
            [
                'escola_id' => $escolaId,
                'taxa_por_dia' => 0.50, // R$ 0,50 por dia de atraso
                'valor_maximo' => 15.00, // Máximo de R$ 15,00
                'excecoes' => [
                    'feriados_nao_contam' => true,
                    'fins_semana_nao_contam' => false,
                    'primeira_vez_isento' => false,
                    'tipos_isentos' => ['audiovisual'], // Audiovisuais não geram multa
                    'perfis_com_desconto' => [
                        'professor' => 0.5, // 50% de desconto para professores
                        'funcionario' => 0.3 // 30% de desconto para funcionários
                    ]
                ]
            ]
        );
    }
}