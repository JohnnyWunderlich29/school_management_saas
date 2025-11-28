<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CorporativoPlansController extends Controller
{
    public function index()
    {
        $plans = Plan::withCount('modules')->orderBy('sort_order')->orderBy('name')->get();
        return view('corporativo.plans.index', compact('plans'));
    }

    public function create()
    {
        $modules = Module::orderBy('display_name')->get();
        return view('corporativo.plans.create', compact('modules'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:plans,slug'],
            'description' => ['nullable', 'string'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'max_users' => ['nullable', 'integer', 'min:0'],
            'max_students' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'is_trial' => ['sometimes', 'boolean'],
            'trial_days' => ['nullable', 'integer', 'min:0'],
            'sort_order' => ['nullable', 'integer'],
            'modules' => ['nullable', 'array'],
            'modules.*' => ['integer', 'exists:modules,id'],
        ]);

        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
        $data['is_active'] = $request->boolean('is_active');
        $data['is_trial'] = $request->boolean('is_trial');
        if (!isset($data['sort_order'])) {
            $data['sort_order'] = (Plan::max('sort_order') ?? 0) + 1;
        }

        $plan = Plan::create($data);

        $modules = $data['modules'] ?? [];
        if (!empty($modules)) {
            $plan->modules()->sync($modules);
        }

        return redirect()->route('corporativo.plans.index')->with('success', 'Plano criado com sucesso.');
    }

    public function edit(Plan $plan)
    {
        $modules = Module::orderBy('display_name')->get();
        $selectedModules = $plan->modules()->pluck('modules.id')->toArray();
        return view('corporativo.plans.edit', compact('plan', 'modules', 'selectedModules'));
    }

    public function update(Request $request, Plan $plan)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:plans,slug,' . $plan->id],
            'description' => ['nullable', 'string'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'max_users' => ['nullable', 'integer', 'min:0'],
            'max_students' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'is_trial' => ['sometimes', 'boolean'],
            'trial_days' => ['nullable', 'integer', 'min:0'],
            'sort_order' => ['nullable', 'integer'],
            'modules' => ['nullable', 'array'],
            'modules.*' => ['integer', 'exists:modules,id'],
        ]);

        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
        $data['is_active'] = $request->boolean('is_active');
        $data['is_trial'] = $request->boolean('is_trial');

        $plan->update($data);

        $modules = $data['modules'] ?? [];
        $plan->modules()->sync($modules);

        return redirect()->route('corporativo.plans.index')->with('success', 'Plano atualizado com sucesso.');
    }

    public function toggle(Plan $plan)
    {
        $plan->is_active = !$plan->is_active;
        $plan->save();
        return redirect()->route('corporativo.plans.index')->with('success', 'Status do plano atualizado.');
    }

    /**
     * Endpoint JSON para listar planos (usado no select de escolas)
     */
    public function api(Request $request)
    {
        $onlyActive = $request->boolean('active', true);

        $plansQuery = Plan::query();
        if ($onlyActive) {
            $plansQuery->where('is_active', true);
        }

        $plans = $plansQuery
            ->orderBy('sort_order')
            ->orderBy('price')
            ->get()
            ->map(function (Plan $plan) {
                return [
                    'id' => $plan->id,
                    'name' => $plan->name,
                    'slug' => $plan->slug,
                    'price' => (float) $plan->price,
                    'is_active' => (bool) $plan->is_active,
                    'is_trial' => (bool) $plan->is_trial,
                    'trial_days' => (int) ($plan->trial_days ?? 0),
                ];
            });

        return response()->json([
            'success' => true,
            'plans' => $plans,
        ]);
    }
}

