<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Finance\BillingPlan;

class BillingPlanTestSeeder extends Seeder
{
    public function run(): void
    {
        BillingPlan::updateOrCreate(
            [
                'school_id' => 5,
                'name' => 'Mensalidade Teste',
                'gateway_alias' => 'asaas',
                'periodicity' => 'monthly',
                'day_of_month' => 10,
            ],
            [
                'amount_cents' => 100000, // R$ 1000,00
                'currency' => 'BRL',
                'active' => true,
            ]
        );
    }
}