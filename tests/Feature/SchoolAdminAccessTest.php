<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Escola;
use App\Models\Plan;
use App\Models\Module;
use App\Models\User;
use App\Models\Cargo;
use App\Models\Permissao;
use App\Models\ModalidadeEnsino;
use Illuminate\Support\Facades\Auth;

class SchoolAdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_school_admin_can_see_global_cargo_and_permissions()
    {
        // 1. Setup global data
        $cargoGlobal = Cargo::create([
            'nome' => 'Administrador de Escola',
            'escola_id' => null,
            'ativo' => true
        ]);

        $perm = Permissao::create([
            'nome' => 'dashboard.ver',
            'modulo' => 'Dashboard',
            'ativo' => true
        ]);

        $cargoGlobal->permissoes()->attach($perm->id);

        $modalidadeGlobal = ModalidadeEnsino::create([
            'nome' => 'Ensino Fundamental I',
            'codigo' => 'EF1',
            'escola_id' => null,
            'ativo' => true
        ]);

        $plan = Plan::create([
            'name' => 'Plano Teste',
            'slug' => 'teste',
            'price' => 100.00,
            'max_users' => 10,
            'max_students' => 100,
            'is_active' => true
        ]);

        // 2. Create school and admin
        $escola = Escola::create(['nome' => 'Escola Teste', 'plano_id' => $plan->id]);
        $user = User::create([
            'name' => 'Admin Teste',
            'email' => 'admin@teste.com',
            'password' => bcrypt('password'),
            'escola_id' => $escola->id
        ]);
        $user->refresh();

        $user->cargos()->attach($cargoGlobal->id);

        // 3. Act: Login and access dashboard (triggers middleware)
        $response = $this->actingAs($user)->get('/dashboard');

        // 4. Assert
        if ($response->status() !== 200) {
            fwrite(STDERR, "Response status: " . $response->status() . "\n");
            fwrite(STDERR, "Content: " . substr($response->getContent(), 0, 500) . "...\n");
        }
        $response->assertStatus(200);

        // Use a closure to verify if the scope is working as expected
        // In the context of the request, the user should have the permission
        // which implies the Cargo was found.
        $this->assertTrue($user->temPermissao('dashboard.ver'));
        $this->assertTrue(ModalidadeEnsino::where('nome', 'Ensino Fundamental I')->exists());
    }
}
