<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Escola;
use App\Models\Plan;
use App\Models\Module;
use App\Models\User;

class SchoolCodeGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_school_generates_unique_code_on_creation()
    {
        $escola1 = Escola::create(['nome' => 'Escola Teste 1']);
        $escola2 = Escola::create(['nome' => 'Escola Teste 2']);

        $this->assertNotNull($escola1->codigo);
        $this->assertNotNull($escola2->codigo);
        $this->assertEquals(8, strlen($escola1->codigo));
        $this->assertEquals(8, strlen($escola2->codigo));
        $this->assertNotEquals($escola1->codigo, $escola2->codigo);
    }

    public function test_school_registration_flow_assigns_code()
    {
        // Setup initial data
        $plan = Plan::create([
            'name' => 'Plano Teste',
            'slug' => 'teste',
            'price' => 100.00,
            'max_users' => 10,
            'max_students' => 100,
            'is_active' => true
        ]);

        $coreModule = Module::create([
            'name' => 'administracao_module',
            'display_name' => 'Administração',
            'is_core' => true,
            'is_active' => true,
            'price' => 0.00
        ]);

        $optionalModule = Module::create([
            'name' => 'alunos_module',
            'display_name' => 'Alunos',
            'is_core' => false,
            'is_active' => true,
            'price' => 20.00
        ]);

        $response = $this->post(route('register.escola.submit'), [
            'escola_nome' => 'Escola Nova',
            'cnpj' => '12.345.678/0001-90',
            'escola_email' => 'financeiro@escola.com',
            'celular' => '(11) 99999-9999',
            'cep' => '01001-000',
            'plan_id' => $plan->id,
            'admin_name' => 'Admin User',
            'admin_email' => 'admin@escola.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(302);

        $escola = Escola::where('nome', 'Escola Nova')->first();
        $this->assertNotNull($escola);
        $this->assertNotNull($escola->codigo);
        $this->assertEquals(8, strlen($escola->codigo));

        // Verificar se módulos foram ativados
        // Se houver módulos "core" no banco, eles devem ser ativados automaticamente
        $this->assertTrue($escola->escolaModules()->where('is_active', true)->exists());

        $user = User::where('email', 'admin@escola.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals($escola->id, $user->escola_id);
    }
}
