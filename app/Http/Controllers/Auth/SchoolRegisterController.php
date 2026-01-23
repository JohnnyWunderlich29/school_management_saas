<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Escola;
use App\Models\User;
use App\Models\Module;
use App\Models\Plan;
use App\Models\Cargo;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Permissao;

class SchoolRegisterController extends Controller
{
    public function showForm()
    {
        $essentialModules = Module::query()
            ->where('is_active', true)
            ->where('is_core', true)
            ->orderBy('sort_order')
            ->get();

        $optionalModules = Module::query()
            ->where('is_active', true)
            ->where('is_core', false)
            ->orderBy('sort_order')
            ->get();

        // Buscar planos ativos do banco
        $plans = Plan::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('auth.register_school', compact('essentialModules', 'optionalModules', 'plans'));
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'escola_nome' => 'required|string|max:255',
            'cnpj' => 'nullable|string|max:32|unique:escolas,cnpj',
            'escola_email' => 'nullable|email|max:255|unique:escolas,email',
            // Contato e endereço
            'telefone' => 'nullable|string|max:20',
            'celular' => 'nullable|string|max:20',
            'cep' => 'nullable|string|max:9',
            'endereco' => 'nullable|string|max:255',
            'numero' => 'nullable|string|max:10',
            'complemento' => 'nullable|string|max:100',
            'bairro' => 'nullable|string|max:100',
            'cidade' => 'nullable|string|max:100',
            'estado' => 'nullable|string|size:2',
            'plan_id' => 'required|integer|exists:plans,id',
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'modules' => 'array',
            'modules.*' => 'integer|exists:modules,id',
        ]);

        return DB::transaction(function () use ($validated) {
            $plan = Plan::query()->where('id', $validated['plan_id'])->where('is_active', true)->firstOrFail();
            $escola = new Escola();
            $escola->nome = $validated['escola_nome'];
            $escola->cnpj = $validated['cnpj'] ?? null;
            $escola->email = $validated['escola_email'] ?? null;
            // Contato e endereço
            $escola->telefone = $validated['telefone'] ?? null;
            $escola->celular = $validated['celular'] ?? null;
            $escola->cep = $validated['cep'] ?? null;
            $escola->endereco = $validated['endereco'] ?? null;
            $escola->numero = $validated['numero'] ?? null;
            $escola->complemento = $validated['complemento'] ?? null;
            $escola->bairro = $validated['bairro'] ?? null;
            $escola->cidade = $validated['cidade'] ?? null;
            $escola->estado = $validated['estado'] ?? null;
            $escola->plan_id = $plan->id;
            $escola->plano = $plan->slug; // manter compatibilidade com lógica existente
            $escola->ativo = true;
            $escola->em_dia = true;

            // Ajustar limites conforme plano
            $escola->max_usuarios = $plan->max_users;
            $escola->max_alunos = $plan->max_students;

            // Definir valor mensal inicial (será recalculado após módulos se não for trial)
            $escola->valor_mensalidade = $plan->is_trial ? 0.0 : (float) $plan->price;

            // Se plano for trial, definir data de vencimento
            if ($plan->is_trial) {
                $trialDays = $plan->trial_days ?? 7;
                $escola->data_vencimento = now()->addDays($trialDays);
            }

            $escola->save();

            $user = new User();
            $user->name = $validated['admin_name'];
            $user->email = $validated['admin_email'];
            $user->password = Hash::make($validated['password']);
            $user->escola_id = $escola->id;
            $user->save();

            // Garantir que o cargo "Administrador de Escola" exista e possua permissões ativas
            $this->ensureAdminSchoolRole();

            // Vincular cargo de Administrador de Escola
            $cargo = Cargo::query()
                ->where('nome', 'Administrador de Escola')
                ->where(function ($q) use ($escola) {
                    $q->whereNull('escola_id')->orWhere('escola_id', $escola->id);
                })
                ->first();

            if (!$cargo) {
                $cargo = Cargo::query()
                    ->where('nome', 'Administrador')
                    ->first();
            }

            if ($cargo) {
                // Pivot user_cargos não possui coluna escola_id; o vínculo à escola
                // é feito pelo próprio cargo (cargos.escola_id) e pelo users.escola_id
                $user->cargos()->attach($cargo->id);
            }

            Auth::login($user);

            // Contratar módulos conforme plano + essenciais + opcionais selecionados
            $essential = Module::query()->where('is_active', true)->where('is_core', true)->get();
            $optionalSelectedIds = collect($validated['modules'] ?? [])->unique()->values()->all();
            $optionalSelected = Module::query()->whereIn('id', $optionalSelectedIds)->where('is_active', true)->get();
            $includedByPlan = $plan->modules()->wherePivot('included', true)->where('is_active', true)->get();

            $toContract = $includedByPlan->merge($essential)->merge($optionalSelected)->unique('id');

            foreach ($toContract as $module) {
                // Preço 0 para módulos incluídos no plano ou durante trial
                $isIncluded = $includedByPlan->contains('id', $module->id);
                $monthlyPrice = ($plan->is_trial || $isIncluded) ? 0.0 : ($module->price ?? 0.0);
                $em = $escola->contractModule($module, $monthlyPrice);
                if ($plan->is_trial) {
                    $em->expires_at = $escola->data_vencimento;
                    $em->save();
                }
            }

            // Contexto da escola atual
            session(['escola_atual' => $escola->id]);

            // Recalcular a mensalidade considerando módulos (trial mantém mensalidade 0)
            if ($plan->is_trial) {
                $escola->valor_mensalidade = 0.0;
            } else {
                $escola->valor_mensalidade = (string) $escola->getTotalMonthlyValue();
            }
            $escola->save();

            // Limpar cache de módulos para garantir que apareçam no primeiro acesso
            cache()->forget("school_modules_{$escola->id}");

            return redirect()->route('dashboard')->with('status', 'Escola registrada com sucesso! Bem-vindo.');
        });
    }

    /**
     * Garante que o cargo "Administrador de Escola" exista com permissões válidas
     * compatíveis com as rotas atuais (ex.: dashboard.ver, alunos.ver, etc.).
     */
    private function ensureAdminSchoolRole(): void
    {
        // Criar permissão mínima se estiver faltando (compatível com middleware das rotas)
        $this->ensurePermission('dashboard.ver', 'Dashboard', 'Visualizar dashboard');

        // Criar/obter cargo
        $cargo = Cargo::updateOrCreate(
            ['nome' => 'Administrador de Escola', 'escola_id' => null],
            [
                'descricao' => 'Administrador com acesso completo à escola',
                'ativo' => true,
            ]
        );

        // Anexar todas as permissões ativas existentes para garantir acesso
        $permissoesAtivas = Permissao::where('ativo', true)->pluck('id');
        if ($permissoesAtivas->isNotEmpty()) {
            $cargo->permissoes()->syncWithoutDetaching($permissoesAtivas->all());
        }
    }

    /**
     * Garante que uma permissão exista e esteja ativa
     */
    private function ensurePermission(string $nome, string $modulo, string $descricao = null): void
    {
        Permissao::updateOrCreate(
            ['nome' => $nome],
            [
                'modulo' => $modulo,
                'descricao' => $descricao ?? ('Permissão para ' . str_replace('.', ' ', $nome)),
                'ativo' => true,
            ]
        );
    }
}