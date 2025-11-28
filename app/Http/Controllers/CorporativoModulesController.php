<?php

namespace App\Http\Controllers;

use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CorporativoModulesController extends Controller
{
    public function index(Request $request): View
    {
        $query = Module::query();

        // Filtros
        if ($request->filled('search')) {
            $search = strtolower($request->string('search'));
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]) 
                  ->orWhereRaw('LOWER(display_name) LIKE ?', ["%{$search}%"]) 
                  ->orWhereRaw('LOWER(description) LIKE ?', ["%{$search}%"]) 
                  ->orWhereRaw('LOWER(category) LIKE ?', ["%{$search}%"]);
            });
        }

        if ($request->filled('status')) {
            if ($request->string('status') === 'ativo') {
                $query->where('is_active', true);
            } elseif ($request->string('status') === 'inativo') {
                $query->where('is_active', false);
            }
        }

        if ($request->filled('category')) {
            $query->where('category', $request->string('category'));
        }

        $modules = $query->orderBy('sort_order')->orderBy('display_name')->paginate(15);
        $categories = Module::select('category')->whereNotNull('category')->distinct()->orderBy('category')->pluck('category');

        $stats = [
            'total' => Module::count(),
            'ativos' => Module::where('is_active', true)->count(),
            'inativos' => Module::where('is_active', false)->count(),
            'cores' => Module::where('is_core', true)->count(),
            'nao_cores' => Module::where('is_core', false)->count(),
        ];

        return view('corporativo.modules.index', compact('modules', 'categories', 'stats'));
    }

    public function create(Request $request)
    {
        $this->authorizeActions();
        $categories = Module::select('category')->whereNotNull('category')->distinct()->orderBy('category')->pluck('category');

        if ($request->ajax() || $request->boolean('partial') || $request->boolean('modal')) {
            return response()->view('corporativo.modules._form', compact('categories'));
        }

        return view('corporativo.modules.create', compact('categories'));
    }

    public function edit(Request $request, Module $module)
    {
        $this->authorizeActions();
        $categories = Module::select('category')->whereNotNull('category')->distinct()->orderBy('category')->pluck('category');

        if ($request->ajax() || $request->boolean('partial') || $request->boolean('modal')) {
            return response()->view('corporativo.modules._form', compact('module', 'categories'));
        }

        return view('corporativo.modules.edit', compact('module', 'categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeActions();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'display_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:20'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'is_core' => ['sometimes', 'boolean'],
            'features' => ['nullable', 'array'],
            'category' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['is_core'] = $request->boolean('is_core');

        // Suporte a features via JSON no formulário
        if ($request->has('features_json') && $request->filled('features_json')) {
            $json = json_decode($request->string('features_json'), true);
            if (is_array($json)) {
                $data['features'] = $json;
            }
        }

        $module = Module::create($data);

        if ($request->ajax() || $request->wantsJson()) {
            return redirect()->route('corporativo.modules.index')->with('success', 'Módulo criado com sucesso.');
        }

        return redirect()->route('corporativo.modules.index')->with('success', 'Módulo criado com sucesso.');
    }

    public function update(Request $request, Module $module): RedirectResponse
    {
        $this->authorizeActions();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'display_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:20'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'is_core' => ['sometimes', 'boolean'],
            'features' => ['nullable', 'array'],
            'category' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['is_core'] = $request->boolean('is_core');

        if ($request->has('features_json')) {
            $json = json_decode($request->string('features_json'), true);
            $data['features'] = is_array($json) ? $json : null;
        }

        $module->update($data);

        if ($request->ajax() || $request->wantsJson()) {
            return redirect()->route('corporativo.modules.index')->with('success', 'Módulo atualizado com sucesso.');
        }

        return redirect()->route('corporativo.modules.index')->with('success', 'Módulo atualizado com sucesso.');
    }

    public function deactivate(Request $request, Module $module): RedirectResponse
    {
        $this->authorizeActions();
        $module->update(['is_active' => false]);

        if ($request->ajax() || $request->wantsJson()) {
            return redirect()->route('corporativo.modules.index')->with('success', 'Módulo inativado com sucesso.');
        }

        return redirect()->route('corporativo.modules.index')->with('success', 'Módulo inativado com sucesso.');
    }

    private function authorizeActions(): void
    {
        // Permitir apenas Super Administradores criarem/editar/inativar
        if (!(auth()->user() && auth()->user()->isSuperAdmin())) {
            abort(403);
        }
    }
}