<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Responsavel;
use App\Models\Aluno;
use Illuminate\Support\Facades\Validator;

class ResponsavelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Responsavel::query();

        // Para super admins e suporte, filtrar pela escola da sessão se definida
        if (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte')) {
            if (session('escola_atual')) {
                $query->where('escola_id', session('escola_atual'));
            }
        } else {
            // Para usuários normais, filtrar por sua escola
            if (auth()->user()->escola_id) {
                $query->where('escola_id', auth()->user()->escola_id);
            }
        }

        // Por padrão, mostrar apenas responsáveis ativos
        if (!$request->has('mostrar_inativos')) {
            $query->ativos();
        }

        // Filtros usando scopes
        if ($request->filled('nome')) {
            $query->buscarPorNome($request->nome);
        }

        if ($request->filled('ativo')) {
            if ($request->ativo == 'true') {
                $query->ativos();
            } else {
                $query->where('ativo', false);
            }
        }

        if ($request->has('cpf')) {
            $query->where('cpf', 'like', '%' . $request->cpf . '%');
        }

        // Ordenação dinâmica via query string
        $allowedSorts = ['id', 'nome', 'parentesco', 'ativo'];
        $sort = in_array($request->get('sort'), $allowedSorts) ? $request->get('sort') : 'nome';
        $direction = in_array($request->get('direction'), ['asc', 'desc']) ? $request->get('direction') : 'asc';

        $responsaveis = $query->orderBy($sort, $direction)->paginate(15)->withQueryString();

        return view('responsaveis.index', compact('responsaveis'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $alunos = Aluno::where('ativo', true)->orderBy('nome')->get();
        return view('responsaveis.create', compact('alunos'));
    }

    /**
     * Store a newly created resource in storage.
     */
    /**
     * Converte data do formato brasileiro (dd/mm/yyyy) para formato do banco (Y-m-d)
     */
    private function convertDateFormat($date)
    {
        if (!$date)
            return null;

        // Se já está no formato Y-m-d, retorna como está
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $date;
        }

        // Se está no formato dd/mm/yyyy, converte
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $date, $matches)) {
            return $matches[3] . '-' . $matches[2] . '-' . $matches[1];
        }

        return null;
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nome' => 'required|string|max:100',
            'sobrenome' => 'required|string|max:100',
            'cpf' => 'required|string|max:14|unique:responsaveis,cpf',
            'rg' => 'nullable|string|max:20',
            'telefone_principal' => 'required|string|max:20',
            'telefone_secundario' => 'nullable|string|max:15',
            'email' => 'nullable|email|max:100',
            'endereco' => 'required|string|max:255',
            'cidade' => 'required|string|max:100',
            'estado' => 'required|string|max:2',
            'cep' => 'required|string|max:10',
            'parentesco' => 'required|string|max:50',
            'autorizado_buscar' => 'boolean',
            'contato_emergencia' => 'boolean',
            'observacoes' => 'nullable|string',
            'consolidate_billing' => 'nullable|boolean',
            'alunos' => 'nullable|array',
            'alunos.*' => 'exists:alunos,id',
            'alunos_principal' => 'nullable|array',
            'alunos_principal.*' => 'boolean',
        ]);


        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $responsavel = Responsavel::create([
                'escola_id' => session('escola_atual') ?: auth()->user()->escola_id,
                'nome' => $request->nome,
                'sobrenome' => $request->sobrenome,
                'data_nascimento' => $this->convertDateFormat($request->data_nascimento),
                'genero' => $request->genero,
                'cpf' => $request->cpf,
                'rg' => $request->rg,
                'telefone_principal' => $request->telefone_principal,
                'telefone_secundario' => $request->telefone_secundario,
                'email' => $request->email,
                'endereco' => $request->endereco,
                'cidade' => $request->cidade,
                'estado' => $request->estado,
                'cep' => $request->cep,
                'parentesco' => $request->parentesco,
                'autorizado_buscar' => $request->autorizado_buscar ?? false,
                'contato_emergencia' => $request->contato_emergencia ?? false,
                'observacoes' => $request->observacoes,
                'consolidate_billing' => $request->has('consolidate_billing') || $request->input('consolidate_billing') === true,
            ]);

            // Associar alunos se houver
            if ($request->has('alunos') && is_array($request->alunos)) {
                foreach ($request->alunos as $index => $alunoId) {
                    $principal = isset($request->alunos_principal[$index]) && $request->alunos_principal[$index];
                    $responsavel->alunos()->attach($alunoId, ['responsavel_principal' => $principal]);
                }
            }

            return redirect()->route('responsaveis.index')
                ->with('success', 'Responsável cadastrado com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erro ao cadastrar responsável: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $responsavel = Responsavel::with('alunos')->findOrFail($id);

        // Escola para contexto financeiro
        $schoolId = $responsavel->escola_id ?? (optional(auth()->user())->escola_id ?? optional(auth()->user())->school_id ?? session('escola_atual'));
        $schoolId = $schoolId ? (int) $schoolId : null;

        // Métodos de cobrança ativos da escola
        $chargeMethods = [];
        if ($schoolId) {
            $chargeMethods = \App\Models\Finance\ChargeMethod::where('school_id', $schoolId)
                ->where('active', true)
                ->orderBy('gateway_alias')
                ->orderBy('method')
                ->get();
        }

        $billingPlans = [];
        if ($schoolId) {
            $billingPlans = \App\Models\Finance\BillingPlan::where('school_id', $schoolId)
                ->where('active', true)
                ->orderBy('name')
                ->get();
        }

        return view('responsaveis.show', compact('responsavel', 'chargeMethods', 'schoolId', 'billingPlans'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $responsavel = Responsavel::with('alunos')->findOrFail($id);
        $alunos = Aluno::where('ativo', true)->orderBy('nome')->get();
        $alunosResponsavel = $responsavel->alunos->pluck('id')->toArray();
        $alunosPrincipais = $responsavel->alunos()
            ->wherePivot('responsavel_principal', true)
            ->pluck('alunos.id')
            ->toArray();

        return view('responsaveis.edit', compact(
            'responsavel',
            'alunos',
            'alunosResponsavel',
            'alunosPrincipais'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'nome' => 'required|string|max:100',
            'sobrenome' => 'required|string|max:100',
            'cpf' => 'required|string|max:14|unique:responsaveis,cpf,' . $id,
            'rg' => 'nullable|string|max:20',
            'telefone_principal' => 'required|string|max:15',
            'telefone_secundario' => 'nullable|string|max:15',
            'email' => 'nullable|email|max:100',
            'endereco' => 'nullable|string|max:255',
            'cidade' => 'nullable|string|max:100',
            'estado' => 'nullable|string|max:2',
            'cep' => 'nullable|string|max:10',
            'parentesco' => 'nullable|string|max:50',
            'autorizado_buscar' => 'boolean',
            'contato_emergencia' => 'boolean',
            'observacoes' => 'nullable|string',
            'consolidate_billing' => 'nullable|boolean',
            'alunos' => 'nullable|array',
            'alunos.*' => 'exists:alunos,id',
            'alunos_principal' => 'nullable|array',
            'alunos_principal.*' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $responsavel = Responsavel::findOrFail($id);

            $responsavel->update([
                'nome' => $request->nome,
                'sobrenome' => $request->sobrenome,
                'data_nascimento' => $this->convertDateFormat($request->data_nascimento),
                'genero' => $request->genero,
                'cpf' => $request->cpf,
                'rg' => $request->rg,
                'telefone_principal' => $request->telefone_principal,
                'telefone_secundario' => $request->telefone_secundario,
                'email' => $request->email,
                'endereco' => $request->endereco,
                'cidade' => $request->cidade,
                'estado' => $request->estado,
                'cep' => $request->cep,
                'parentesco' => $request->parentesco,
                'autorizado_buscar' => $request->autorizado_buscar ?? false,
                'contato_emergencia' => $request->contato_emergencia ?? false,
                'observacoes' => $request->observacoes,
                'consolidate_billing' => $request->has('consolidate_billing') || $request->input('consolidate_billing') === true,
            ]);

            // Atualizar alunos
            $responsavel->alunos()->detach();

            if ($request->has('alunos') && is_array($request->alunos)) {
                foreach ($request->alunos as $index => $alunoId) {
                    $principal = isset($request->alunos_principal[$index]) && $request->alunos_principal[$index];
                    $responsavel->alunos()->attach($alunoId, ['responsavel_principal' => $principal]);
                }
            }

            return redirect()->route('responsaveis.index')
                ->with('success', 'Responsável atualizado com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erro ao atualizar responsável: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Toggle the active status of the specified resource.
     */
    public function toggleStatus(Responsavel $responsavel)
    {
        // Verificar se o usuário pode alterar o status deste responsável
        if (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte')) {
            if (session('escola_atual') && $responsavel->escola_id !== session('escola_atual')) {
                abort(404);
            }
        } else {
            if (auth()->user()->escola_id && $responsavel->escola_id !== auth()->user()->escola_id) {
                abort(404);
            }
        }

        $statusAnterior = $responsavel->ativo;
        $responsavel->update(['ativo' => !$responsavel->ativo]);

        $status = $responsavel->ativo ? 'ativado' : 'inativado';

        // Registrar no histórico
        \App\Models\Historico::registrar(
            $responsavel->ativo ? 'ativado' : 'inativado',
            'Responsavel',
            $responsavel->id,
            ['ativo' => $statusAnterior],
            ['ativo' => $responsavel->ativo],
            "Responsável {$status} com sucesso"
        );

        \App\Services\AlertService::success("Responsável {$status} com sucesso!");
        return redirect()->route('responsaveis.index');
    }

    public function toggleConsolidation(Responsavel $responsavel)
    {
        // Verificar permissões
        if (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte')) {
            if (session('escola_atual') && $responsavel->escola_id !== session('escola_atual')) {
                abort(404);
            }
        } else {
            if (auth()->user()->escola_id && $responsavel->escola_id !== auth()->user()->escola_id) {
                abort(404);
            }
        }

        $responsavel->update(['consolidate_billing' => !$responsavel->consolidate_billing]);

        $status = $responsavel->consolidate_billing ? 'Habilitada' : 'Desabilitada';

        // Registrar no histórico
        \App\Models\Historico::registrar(
            'configuracao_alterada',
            'Responsavel',
            $responsavel->id,
            null,
            ['consolidate_billing' => $responsavel->consolidate_billing],
            "Consolidação de faturas {$status} com sucesso"
        );

        if (request()->expectsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Consolidação de faturas {$status} com sucesso",
                'consolidate_billing' => $responsavel->consolidate_billing
            ]);
        }

        \App\Services\AlertService::success("Consolidação de faturas {$status} com sucesso!");
        return redirect()->back();
    }
}
