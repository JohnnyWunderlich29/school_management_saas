<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Escola;
use App\Models\ModalidadeEnsino;
use App\Models\Grupo;
use App\Models\Turno;
use App\Models\Disciplina;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TestConfiguracoesIsolation extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Criar escolas manualmente para evitar problemas com seeders
        $this->escola1 = Escola::create([
            'nome' => 'Escola Teste 1',
            'cnpj' => '11.111.111/0001-11',
            'razao_social' => 'Escola Teste 1 LTDA',
            'email' => 'teste1@escola.com',
            'telefone' => '(11) 1111-1111',
            'endereco' => 'Rua Teste 1',
            'cidade' => 'São Paulo',
            'estado' => 'SP',
            'cep' => '01111-111',
            'plano' => 'basico',
            'valor_mensalidade' => 299.90,
            'data_vencimento' => now()->addMonth(),
            'ativo' => true,
            'em_dia' => true,
            'configuracoes' => json_encode(['max_usuarios' => 50])
        ]);
        
        $this->escola2 = Escola::create([
            'nome' => 'Escola Teste 2',
            'cnpj' => '22.222.222/0002-22',
            'razao_social' => 'Escola Teste 2 LTDA',
            'email' => 'teste2@escola.com',
            'telefone' => '(11) 2222-2222',
            'endereco' => 'Rua Teste 2',
            'cidade' => 'São Paulo',
            'estado' => 'SP',
            'cep' => '02222-222',
            'plano' => 'basico',
            'valor_mensalidade' => 299.90,
            'data_vencimento' => now()->addMonth(),
            'ativo' => true,
            'em_dia' => true,
            'configuracoes' => json_encode(['max_usuarios' => 50])
        ]);
    }

    public function test_normal_user_sees_only_own_school_data()
    {
        // Arrange: Create a normal user for escola1
        $user = User::create([
            'name' => 'Professor Teste',
            'email' => 'professor@escola1.com',
            'password' => bcrypt('password'),
            'escola_id' => $this->escola1->id,
            'ativo' => true
        ]);
        
        // Create and assign Super Administrador role to access configurations
        $cargoSuperAdmin = \App\Models\Cargo::create([
            'nome' => 'Super Administrador',
            'descricao' => 'Super Administrador do sistema',
            'ativo' => true
        ]);
        
        // Attach role to user
        $user->cargos()->attach($cargoSuperAdmin->id);
        
        // Create some test data for both schools
        $modalidade1 = ModalidadeEnsino::create([
            'codigo' => 'EF',
            'nome' => 'Ensino Fundamental',
            'escola_id' => $this->escola1->id
        ]);
        
        $modalidade2 = ModalidadeEnsino::create([
            'codigo' => 'EM',
            'nome' => 'Ensino Médio',
            'escola_id' => $this->escola2->id
        ]);
        
        $grupo1 = Grupo::create([
            'nome' => 'Grupo A',
            'codigo' => 'GA',
            'modalidade_ensino_id' => $modalidade1->id,
            'escola_id' => $this->escola1->id
        ]);
        
        $grupo2 = Grupo::create([
            'nome' => 'Grupo B',
            'codigo' => 'GB',
            'modalidade_ensino_id' => $modalidade2->id,
            'escola_id' => $this->escola2->id
        ]);
        
        // Act & Assert: Test each tab
        $tabs = ['modalidades', 'grupos', 'turnos', 'disciplinas'];
        
        foreach ($tabs as $tab) {
            $response = $this->actingAs($user)
                ->get("/admin/configuracoes?tab={$tab}");
            
            $response->assertStatus(200);
            
            // Check that data is filtered by school
            $viewData = $response->original->getData();
            
            if (isset($viewData[$tab]) && $viewData[$tab]->count() > 0) {
                foreach ($viewData[$tab] as $item) {
                    $this->assertEquals($user->escola_id, $item->escola_id, 
                        "Item in {$tab} tab should belong to user's school");
                }
            }
        }
    }

    public function test_super_admin_with_escola_atual_sees_filtered_data()
    {
        // Arrange: Create a Super Admin
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@sistema.com',
            'password' => bcrypt('password'),
            'escola_id' => null, // Super admin não tem escola específica
            'ativo' => true
        ]);
        
        // Create cargo Super Administrador and assign to user
        $cargoSuperAdmin = \App\Models\Cargo::create([
            'nome' => 'Super Administrador',
            'descricao' => 'Administrador do sistema'
        ]);
        
        $superAdmin->cargos()->attach($cargoSuperAdmin->id);
        
        // Create test data for escola1
        $modalidade1 = ModalidadeEnsino::create([
            'codigo' => 'EFS',
            'nome' => 'Ensino Fundamental Super',
            'escola_id' => $this->escola1->id
        ]);
        
        $grupo1 = Grupo::create([
            'nome' => 'Grupo Super A',
            'codigo' => 'GSA',
            'modalidade_ensino_id' => $modalidade1->id,
            'escola_id' => $this->escola1->id
        ]);
        
        // Act & Assert: Test each tab with escola_atual in session
        $tabs = ['modalidades', 'grupos', 'turnos', 'disciplinas'];
        
        foreach ($tabs as $tab) {
            $response = $this->actingAs($superAdmin)
                ->withSession(['escola_atual' => $this->escola1->id])
                ->get("/admin/configuracoes?tab={$tab}");
            
            $response->assertStatus(200);
            
            // Check that data is filtered by escola_atual
            $viewData = $response->original->getData();
            
            if (isset($viewData[$tab]) && $viewData[$tab]->count() > 0) {
                foreach ($viewData[$tab] as $item) {
                    $this->assertEquals($this->escola1->id, $item->escola_id, 
                        "Item in {$tab} tab should belong to escola_atual");
                }
            }
        }
    }

    public function test_super_admin_without_escola_atual_sees_no_data()
    {
        // Arrange: Create a Super Admin without escola_id
        $superAdmin = User::create([
            'name' => 'Super Admin 2',
            'email' => 'admin2@sistema.com',
            'password' => bcrypt('password'),
            'escola_id' => null,
            'ativo' => true
        ]);
        
        // Create cargo Super Administrador and assign to user
        $cargoSuperAdmin = \App\Models\Cargo::create([
            'nome' => 'Super Administrador',
            'descricao' => 'Administrador do sistema'
        ]);
        
        $superAdmin->cargos()->attach($cargoSuperAdmin->id);
        
        // Act & Assert: Test each tab without escola_atual in session
        $tabs = ['modalidades', 'grupos', 'turnos', 'disciplinas'];
        
        foreach ($tabs as $tab) {
            $response = $this->actingAs($superAdmin)
                ->get("/admin/configuracoes?tab={$tab}");
            
            $response->assertStatus(200);
            
            // Check that no data is returned
            $viewData = $response->original->getData();
            
            if (isset($viewData[$tab])) {
                $this->assertEquals(0, $viewData[$tab]->count(), 
                    "Super Admin without escola_atual should see 0 items in {$tab} tab");
            }
        }
    }

    public function test_unauthenticated_user_redirected()
    {
        // Act & Assert: Test each tab without authentication
        $tabs = ['modalidades', 'grupos', 'turnos', 'disciplinas'];
        
        foreach ($tabs as $tab) {
            $response = $this->get("/admin/configuracoes?tab={$tab}");
            $response->assertRedirect('/login');
        }
    }
}