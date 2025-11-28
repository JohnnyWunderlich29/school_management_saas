<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Escola;
use App\Models\Turno;
use App\Models\ModalidadeEnsino;
use App\Models\NivelEnsino;
use App\Models\Grupo;
use App\Models\Turma;
use App\Models\Disciplina;
use App\Models\Planejamento;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;

class PlanejamentosWizardFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected Escola $escola;
    protected User $superAdmin;
    protected Turno $turno;
    protected ModalidadeEnsino $modalidade;
    protected NivelEnsino $nivel;
    protected Grupo $grupo;
    protected Turma $turma;
    protected Disciplina $disciplina;

    protected function setUp(): void
    {
        parent::setUp();

        // Escola mínima
        $this->escola = Escola::create([
            'nome' => 'Escola Wizard',
            'cnpj' => '10.000.000/0001-00',
            'razao_social' => 'Escola Wizard LTDA',
            'email' => 'wizard@escola.com',
            'telefone' => '(11) 1111-1111',
            'endereco' => 'Rua Wizard',
            'cidade' => 'São Paulo',
            'estado' => 'SP',
            'cep' => '01000-000',
            'plano' => 'basico',
            'valor_mensalidade' => 199.90,
            'data_vencimento' => now()->addMonth(),
            'ativo' => true,
            'em_dia' => true,
            'configuracoes' => json_encode(['max_usuarios' => 50])
        ]);

        // Super Admin (bypass permission and license checks)
        $this->superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@wizard.com',
            'password' => bcrypt('password'),
            'escola_id' => null,
            'ativo' => true,
        ]);
        $cargoSuperAdmin = \App\Models\Cargo::create([
            'nome' => 'Super Administrador',
            'descricao' => 'Administrador do sistema',
            'ativo' => true,
        ]);
        $this->superAdmin->cargos()->attach($cargoSuperAdmin->id);

        // Turno vinculado à escola
        $this->turno = Turno::create([
            'nome' => 'Matutino',
            'codigo' => 'MAT',
            'hora_inicio' => '07:00',
            'hora_fim' => '12:00',
            'descricao' => 'Turno matutino',
            'ativo' => true,
            'ordem' => 1,
            'escola_id' => $this->escola->id,
        ]);

        // Nível de Ensino (global)
        $this->nivel = NivelEnsino::create([
            'nome' => 'Fundamental 1',
            'codigo' => 'EF1',
            'descricao' => 'Ensino Fundamental I',
            'capacidade_padrao' => 30,
            'ativo' => true,
            'turno_matutino' => true,
            'turno_vespertino' => true,
            'turno_noturno' => false,
            'turno_integral' => false,
            'modalidades_compativeis' => ['EF']
        ]);

        // Modalidade vinculada à escola (cria config via boot)
        $this->modalidade = ModalidadeEnsino::create([
            'codigo' => 'EF',
            'nome' => 'Ensino Fundamental',
            'nivel' => 'basico',
            'descricao' => 'Modalidade EF',
            'ativo' => true,
            'escola_id' => $this->escola->id,
        ]);

        // Grupo vinculado à modalidade e escola
        $this->grupo = Grupo::create([
            'nome' => 'Grupo A',
            'codigo' => 'GA',
            'modalidade_ensino_id' => $this->modalidade->id,
            'escola_id' => $this->escola->id,
        ]);

        // Disciplina vinculada à escola
        Model::unguard();
        $this->disciplina = Disciplina::create([
            'nome' => 'Matemática',
            'codigo' => 'MAT',
            'area_conhecimento' => 'Exatas',
            'descricao' => 'Disciplina de Matemática',
            'cor_hex' => '#3366FF',
            'obrigatoria' => true,
            'ativo' => true,
            'ordem' => 1,
            'escola_id' => $this->escola->id,
        ]);
        Model::reguard();
        // Vincular disciplina ao nível (para buscas/consistência)
        \App\Models\DisciplinaNivelEnsino::create([
            'disciplina_id' => $this->disciplina->id,
            'nivel_ensino_id' => $this->nivel->id,
            'carga_horaria_semanal' => 5,
            'carga_horaria_anual' => 200,
            'obrigatoria' => true,
            'ordem' => 1,
        ]);

        // Turma da escola e nível/turno/grupo
        $this->turma = Turma::create([
            'escola_id' => $this->escola->id,
            'nome' => 'Turma 5A',
            'codigo' => 'T5A',
            'descricao' => 'Turma do 5º ano',
            'capacidade' => 30,
            'ativo' => true,
            'turno_matutino' => true,
            'turno_vespertino' => false,
            'turno_noturno' => false,
            'turno_integral' => false,
            'ano_letivo' => (int) now()->year,
            'turno_id' => $this->turno->id,
            'grupo_id' => $this->grupo->id,
            'nivel_ensino_id' => $this->nivel->id,
        ]);
    }

    public function test_wizard_store_finaliza_com_payload_valido(): void
    {
        $this->actingAs($this->superAdmin)
            ->withSession(['escola_atual' => $this->escola->id]);

        $numeroDias = 3;
        $dataInicio = now()->toDateString();
        $diarios = [];
        for ($i = 0; $i < $numeroDias; $i++) {
            $diarios[] = [
                'data' => Carbon::parse($dataInicio)->addDays($i)->toDateString(),
                'planejado' => true,
                'campos_experiencia' => ['Raciocínio lógico'],
                'objetivos_aprendizagem' => ['OBJ' . ($i + 1)],
                'saberes_conhecimentos' => 'Conteúdos do dia ' . ($i + 1)
            ];
        }
        $payload = [
            'save_as_draft' => false,
            'modalidade_ensino_id' => $this->modalidade->id,
            'nivel_ensino_id' => $this->nivel->id,
            'turno_id' => $this->turno->id,
            'escola_id' => $this->escola->id,
            'turma_id' => $this->turma->id,
            'disciplina_id' => $this->disciplina->id,
            'professor_id' => $this->superAdmin->id,
            'data_inicio' => $dataInicio,
            'numero_dias' => $numeroDias,
            'carga_horaria_aula' => 1.0,
            'aulas_por_semana' => 5,
            'titulo' => 'Planejamento Matemática Semanal',
            'metodologia' => 'Método investigativo com resolução de problemas.',
            'tipo_periodo' => 'semanal',
            'campos_experiencia' => ['Raciocínio lógico'],
            'aceita_termos' => true,
            'aceitar_termos' => true,
            'planejamentos_diarios' => $diarios,
        ];

        $resp = $this->postJson('/planejamentos/wizard/store', $payload);
        $resp->assertStatus(200)->assertJson(['success' => true]);

        $pl = Planejamento::first();
        $this->assertNotNull($pl, 'Planejamento deve ser criado');
        $this->assertSame('finalizado', $pl->status, 'Status deve estar finalizado com payload completo');
        $this->assertSame($this->turma->id, $pl->turma_id);
        $this->assertSame($this->disciplina->id, $pl->disciplina_id);
        $this->assertSame($this->superAdmin->id, $pl->professor_id);
        $this->assertSame($this->escola->id, $pl->escola_id, 'Escola deve ser setada pelo contexto');
    }

    public function test_validate_step_2_rejeita_escola_diferente_do_contexto(): void
    {
        $this->actingAs($this->superAdmin)
            ->withSession(['escola_atual' => $this->escola->id]);

        $outraEscola = Escola::create([
            'nome' => 'Outra Escola',
            'cnpj' => '20.000.000/0001-00',
            'razao_social' => 'Outra Escola LTDA',
            'email' => 'outra@escola.com',
            'telefone' => '(11) 2222-2222',
            'endereco' => 'Rua Outra',
            'cidade' => 'São Paulo',
            'estado' => 'SP',
            'cep' => '02000-000',
            'plano' => 'basico',
            'valor_mensalidade' => 199.90,
            'data_vencimento' => now()->addMonth(),
            'ativo' => true,
            'em_dia' => true,
            'configuracoes' => json_encode(['max_usuarios' => 50])
        ]);

        $payload = [
            'step' => '2',
            'data' => [
                'turno_id' => $this->turno->id,
                'escola_id' => $outraEscola->id, // diferente do contexto, deve falhar
            ]
        ];

        $resp = $this->postJson('/planejamentos/wizard/validate-step', $payload);
        $resp->assertStatus(422);
        $resp->assertJson(['success' => false]);
    }

    public function test_store_rascunho_atribui_professor_ao_user_logado_quando_ausente(): void
    {
        $this->actingAs($this->superAdmin)
            ->withSession(['escola_atual' => $this->escola->id]);

        $payloadDraft = [
            'save_as_draft' => true,
            'modalidade_ensino_id' => $this->modalidade->id,
            'nivel_ensino_id' => $this->nivel->id,
            'turno_id' => $this->turno->id,
            'escola_id' => $this->escola->id,
            'turma_id' => $this->turma->id,
            'disciplina_id' => $this->disciplina->id,
            'data_inicio' => now()->toDateString(),
            'numero_dias' => 1,
            'carga_horaria_aula' => 1.0,
            'aulas_por_semana' => 5,
            'titulo' => 'Rascunho Sem Professor',
            'metodologia' => 'Abordagem exploratória.',
            'campos_experiencia' => ['Raciocínio lógico'],
        ];

        $resp = $this->postJson('/planejamentos/wizard/store', $payloadDraft);
        $resp->assertStatus(200)->assertJson(['success' => true]);

        $pl = Planejamento::first();
        $this->assertNotNull($pl, 'Planejamento rascunho deve ser criado');
        $this->assertSame('rascunho', $pl->status);
        $this->assertSame($this->superAdmin->id, $pl->professor_id, 'Professor deve ser atribuído ao usuário logado');
    }

    public function test_transicao_de_status_para_finalizado_respeita_politica_e_diarios(): void
    {
        $this->actingAs($this->superAdmin)
            ->withSession(['escola_atual' => $this->escola->id]);

        // Criar rascunho inicial
        $draftPayload = [
            'save_as_draft' => true,
            'modalidade_ensino_id' => $this->modalidade->id,
            'nivel_ensino_id' => $this->nivel->id,
            'turno_id' => $this->turno->id,
            'escola_id' => $this->escola->id,
            'turma_id' => $this->turma->id,
            'disciplina_id' => $this->disciplina->id,
            'data_inicio' => now()->toDateString(),
            'numero_dias' => 2,
            'carga_horaria_aula' => 1.0,
            'aulas_por_semana' => 5,
            'titulo' => 'Planejamento Draft',
            'metodologia' => 'Abordagem por projetos.',
            'campos_experiencia' => ['Raciocínio lógico'],
        ];
        $this->postJson('/planejamentos/wizard/store', $draftPayload)->assertStatus(200);

        $pl = Planejamento::firstOrFail();

        // Tentar finalizar com diários incompletos (deve falhar)
        $finalPayloadIncompleto = [
            'save_as_draft' => false,
            'planejamento_id' => $pl->id,
            'modalidade_ensino_id' => $this->modalidade->id,
            'nivel_ensino_id' => $this->nivel->id,
            'turno_id' => $this->turno->id,
            'escola_id' => $this->escola->id,
            'turma_id' => $this->turma->id,
            'disciplina_id' => $this->disciplina->id,
            'professor_id' => $this->superAdmin->id,
            'data_inicio' => now()->toDateString(),
            'numero_dias' => 2,
            'carga_horaria_aula' => 1.0,
            'aulas_por_semana' => 5,
            'status' => 'finalizado',
            // Campos exigidos na finalização
            'tipo_periodo' => 'bimestral',
            'metodologia' => 'Metodologia detalhada com estratégias e avaliações.',
            'campos_experiencia' => ['Raciocínio lógico'],
            'aceita_termos' => true,
            'aceitar_termos' => true,
            'planejamentos_diarios' => [
                [
                    'data' => now()->toDateString(),
                    'planejado' => true,
                    'campos_experiencia' => ['Raciocínio lógico'],
                    'objetivos_aprendizagem' => ['OBJ1'],
                    'saberes_conhecimentos' => 'Conteúdos do dia 1',
                    'objetivos' => 'Dia 1'
                ]
            ],
        ];
        $this->postJson('/planejamentos/wizard/store', $finalPayloadIncompleto)
            ->assertStatus(422);

        // Finalizar com diários completos
        $finalPayloadCompleto = $finalPayloadIncompleto;
        $finalPayloadCompleto['planejamentos_diarios'][] = [
            'data' => now()->addDay()->toDateString(),
            'planejado' => true,
            'campos_experiencia' => ['Raciocínio lógico'],
            'objetivos_aprendizagem' => ['OBJ2'],
            'saberes_conhecimentos' => 'Conteúdos do dia 2',
            'objetivos' => 'Dia 2'
        ];

        $this->postJson('/planejamentos/wizard/store', $finalPayloadCompleto)
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        $pl->refresh();
        $this->assertSame('finalizado', $pl->status, 'Deve transicionar para finalizado com diários completos');
    }
}