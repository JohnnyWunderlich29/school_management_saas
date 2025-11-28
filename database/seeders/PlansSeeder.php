<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Plan;
use App\Models\Module;

class PlansSeeder extends Seeder
{
    public function run(): void
    {
        // Create or update plans
        $plans = [
            [
                'name' => 'Trial',
                'slug' => 'trial',
                'description' => 'Plano de testes gratuito por 7 dias',
                'price' => 0.00,
                'max_users' => 15,
                'max_students' => 50,
                'is_active' => true,
                'is_trial' => true,
                'trial_days' => 7,
                'sort_order' => 0,
            ],
            [
                'name' => 'Básico',
                'slug' => 'basico',
                'description' => 'Plano básico para escolas pequenas',
                'price' => 99.90,
                'max_users' => 50,
                'max_students' => 200,
                'is_active' => true,
                'is_trial' => false,
                'trial_days' => null,
                'sort_order' => 1,
            ],
            [
                'name' => 'Premium',
                'slug' => 'premium',
                'description' => 'Plano premium para escolas médias',
                'price' => 199.90,
                'max_users' => 150,
                'max_students' => 800,
                'is_active' => true,
                'is_trial' => false,
                'trial_days' => null,
                'sort_order' => 2,
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'description' => 'Plano enterprise para escolas grandes',
                'price' => 399.90,
                'max_users' => 500,
                'max_students' => 2000,
                'is_active' => true,
                'is_trial' => false,
                'trial_days' => null,
                'sort_order' => 3,
            ],
        ];

        $plansBySlug = [];
        foreach ($plans as $planData) {
            $plan = Plan::updateOrCreate(
                ['slug' => $planData['slug']],
                array_merge($planData, [
                    'updated_at' => Carbon::now(),
                ])
            );
            $plansBySlug[$plan->slug] = $plan;
        }

        // Fetch modules by name
        $moduleNames = [
            'alunos_module',
            'administracao_module',
            'comunicacao_module',
            'academico_module',
            'funcionarios_module',
            'financeiro_module',
            'biblioteca_module',
            'eventos_module',
        ];
        $modules = Module::whereIn('name', $moduleNames)->get()->keyBy('name');

        // Define included modules per plan
        $includedByPlan = [
            'trial' => [
                'alunos_module',
                'administracao_module',
            ],
            'basico' => [
                'alunos_module',
                'administracao_module',
                'comunicacao_module',
            ],
            'premium' => [
                'alunos_module',
                'administracao_module',
                'comunicacao_module',
                'academico_module',
            ],
            'enterprise' => [
                'alunos_module',
                'administracao_module',
                'comunicacao_module',
                'academico_module',
                'funcionarios_module',
                'financeiro_module',
                'biblioteca_module',
                'eventos_module',
            ],
        ];

        // Link modules to plans
        foreach ($includedByPlan as $slug => $moduleList) {
            if (!isset($plansBySlug[$slug])) {
                continue;
            }
            $plan = $plansBySlug[$slug];
            $moduleIds = collect($moduleList)
                ->map(fn($name) => $modules[$name]->id ?? null)
                ->filter()
                ->values()
                ->all();

            // Sync while keeping 'included' = true
            $syncData = [];
            foreach ($moduleIds as $mid) {
                $syncData[$mid] = ['included' => true];
            }
            $plan->modules()->sync($syncData);
        }

        $this->command->info('Planos e módulos vinculados criados com sucesso!');
    }
}