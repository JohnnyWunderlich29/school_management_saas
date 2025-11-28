<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Funcionario;
use App\Models\User;
use App\Models\Cargo;
use App\Models\Historico;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Services\AlertService;

class FuncionarioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Debug: Log da escola atual ao listar funcionários
        \Log::info('DEBUG FUNCIONARIO - Listando funcionários', [
            'user_id' => auth()->user()->id,
            'user_email' => auth()->user()->email,
            'user_escola_id' => auth()->user()->escola_id,
            'session_escola_atual' => session('escola_atual'),
            'is_super_admin' => auth()->user()->isSuperAdmin(),
            'url' => request()->url()
        ]);
        
        $query = Funcionario::query();
        
        // Para super admins e suporte, filtrar pela escola da sessão se definida
        // NOTA: O middleware EscolaContext já aplica filtros globais para usuários normais
        if (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte')) {
            if (session('escola_atual')) {
                $query->where('escola_id', session('escola_atual'));
                \Log::info('DEBUG FUNCIONARIO - Aplicando filtro de escola (Super Admin/Suporte)', [
                    'escola_id_filtro' => session('escola_atual')
                ]);
            }
        }
        // REMOVIDO: Filtro manual para usuários normais pois o middleware EscolaContext
        // já aplica um filtro global que filtra automaticamente por escola_id
        
        // Filtros
        if ($request->has('nome')) {
            $query->where(function($q) use ($request) {
                $q->where('nome', 'like', '%' . $request->nome . '%')
                  ->orWhere('sobrenome', 'like', '%' . $request->nome . '%');
            });
        }
        
        if ($request->has('cargo')) {
            $query->where('cargo', 'like', '%' . $request->cargo . '%');
        }
        
        if ($request->has('ativo')) {
            $query->where('ativo', $request->ativo == 'true' ? true : false);
        }
        
        // Ordenação dinâmica via query string
        $allowedSorts = ['id', 'nome', 'cargo', 'ativo'];
        $sort = in_array($request->get('sort'), $allowedSorts) ? $request->get('sort') : 'nome';
        $direction = in_array($request->get('direction'), ['asc', 'desc']) ? $request->get('direction') : 'asc';

        $funcionarios = $query->orderBy($sort, $direction)->paginate(15)->withQueryString();
        
        return view('funcionarios.index', compact('funcionarios'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Buscar cargos globais (escola_id null) e cargos específicos da escola atual
        $cargos = Cargo::where('ativo', true)
            ->where(function($query) {
                $query->whereNull('escola_id') // Cargos globais/padrão do sistema
                      ->orWhere('escola_id', auth()->user()->escola_id); // Cargos específicos da escola
            })
            ->orderBy('nome')
            ->get();
        
        // Filtrar cargo 'Super Administrador' - só mostrar para super administradores
        if (!auth()->user()->isSuperAdmin()) {
            $cargos = $cargos->filter(function($cargo) {
                return $cargo->nome !== 'Super Administrador';
            });
        }
        
        return view('funcionarios.create', compact('cargos'));
    }

    /**
     * Converte data do formato brasileiro (dd/mm/yyyy) para formato do banco (Y-m-d)
     */
    private function convertDateFormat($date)
    {
        if (!$date) return null;
        
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

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nome' => 'required|string|max:100',
            'sobrenome' => 'required|string|max:100',
            'cpf' => 'nullable|string|max:14|unique:funcionarios,cpf',
            'rg' => 'nullable|string|max:20',
            'data_nascimento' => 'required|date',
            'telefone' => 'required|string|max:15',
            'email' => 'required|email|max:100|unique:funcionarios,email',
            'endereco' => 'nullable|string|max:255',
            'cidade' => 'nullable|string|max:100',
            'estado' => 'nullable|string|max:2',
            'cep' => 'nullable|string|max:10',
            'cargo' => 'required|string|max:100',
            'departamento' => 'nullable|string|max:100',
            'data_contratacao' => 'required|date',
            'data_demissao' => 'nullable|date',
            'salario' => 'nullable|numeric',
            'observacoes' => 'nullable|string',
            'criar_usuario' => 'boolean',
            'password' => 'required_if:criar_usuario,true|nullable|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        try {
            $userId = null;
            
            // Criar usuário se solicitado
            if ($request->criar_usuario) {
                $user = User::create([
                    'name' => $request->nome . ' ' . $request->sobrenome,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'escola_id' => session('escola_atual') ?: auth()->user()->escola_id,
                ]);
                
                $userId = $user->id;
                
                // Associar cargo ao usuário baseado no cargo do funcionário
                $cargoModel = Cargo::where('nome', $request->cargo)->where('ativo', true)->first();
                if ($cargoModel) {
                    $user->cargos()->attach($cargoModel->id);
                }
            }
            
            // Debug: Log da escola atual antes de criar funcionário
            \Log::info('DEBUG FUNCIONARIO - Criando funcionário', [
                'user_id' => auth()->user()->id,
                'user_email' => auth()->user()->email,
                'user_escola_id' => auth()->user()->escola_id,
                'session_escola_atual' => session('escola_atual'),
                'is_super_admin' => auth()->user()->isSuperAdmin(),
                'funcionario_nome' => $request->nome . ' ' . $request->sobrenome,
                'escola_id_para_funcionario' => auth()->user()->escola_id,
                'url' => request()->url()
            ]);
            
            $funcionario = Funcionario::create([
                'user_id' => $userId,
                'escola_id' => session('escola_atual') ?: auth()->user()->escola_id,
                'nome' => $request->nome,
                'sobrenome' => $request->sobrenome,
                'cpf' => $request->cpf,
                'rg' => $request->rg,
                'data_nascimento' => $this->convertDateFormat($request->data_nascimento),
                'telefone' => $request->telefone,
                'email' => $request->email,
                'endereco' => $request->endereco,
                'cidade' => $request->cidade,
                'estado' => $request->estado,
                'cep' => $request->cep,
                'cargo' => $request->cargo,
                'departamento' => $request->departamento,
                'data_contratacao' => $request->data_contratacao,
                'data_demissao' => $request->data_demissao,
                'salario' => $request->salario,
                'ativo' => true,
                'observacoes' => $request->observacoes,
            ]);
            
            // Associar disciplinas se fornecidas
            if ($request->has('disciplinas')) {
                $funcionario->disciplinas()->attach($request->disciplinas);
            }
            
            // Debug: Log após criar funcionário
            \Log::info('DEBUG FUNCIONARIO - Funcionário criado com sucesso', [
                'funcionario_id' => $funcionario->id,
                'funcionario_escola_id' => $funcionario->escola_id,
                'funcionario_nome' => $funcionario->nome . ' ' . $funcionario->sobrenome
            ]);

            // Registrar no histórico
            Historico::registrar(
                'criado',
                'Funcionario',
                $funcionario->id,
                null,
                $funcionario->toArray(),
                'Funcionário criado com sucesso'
            );

            DB::commit();
            AlertService::success('Funcionário cadastrado com sucesso!');
            return redirect()->route('funcionarios.index');
        } catch (\Exception $e) {
            DB::rollBack();
            AlertService::systemError('Erro ao cadastrar funcionário', $e);
            return redirect()->back()->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $funcionario = Funcionario::with('user')->findOrFail($id);
        return view('funcionarios.show', compact('funcionario'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $funcionario = Funcionario::with('user')->findOrFail($id);
        // Buscar cargos globais (escola_id null) e cargos específicos da escola atual
        $cargos = Cargo::where('ativo', true)
            ->where(function($query) {
                $query->whereNull('escola_id') // Cargos globais/padrão do sistema
                      ->orWhere('escola_id', auth()->user()->escola_id); // Cargos específicos da escola
            })
            ->orderBy('nome')
            ->get();
        
        // Filtrar cargo 'Super Administrador' - só mostrar para super administradores
        if (!auth()->user()->isSuperAdmin()) {
            $cargos = $cargos->filter(function($cargo) {
                return $cargo->nome !== 'Super Administrador';
            });
        }
        
        return view('funcionarios.edit', compact('funcionario', 'cargos'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $funcionario = Funcionario::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'nome' => 'required|string|max:100',
            'sobrenome' => 'required|string|max:100',
            'cpf' => 'nullable|string|max:14|unique:funcionarios,cpf,' . $id,
            'rg' => 'nullable|string|max:20',
            'data_nascimento' => 'required|date',
            'telefone' => 'required|string|max:15',
            'email' => 'required|email|max:100|unique:funcionarios,email,' . $id,
            'endereco' => 'nullable|string|max:255',
            'cidade' => 'nullable|string|max:100',
            'estado' => 'nullable|string|max:2',
            'cep' => 'nullable|string|max:10',
            'cargo' => 'required|string|max:100',
            'departamento' => 'nullable|string|max:100',
            'data_contratacao' => 'required|date',
            'data_demissao' => 'nullable|date',
            'salario' => 'nullable|numeric',
            'ativo' => 'boolean',
            'observacoes' => 'nullable|string',
            'atualizar_usuario' => 'boolean',
            'password' => 'nullable|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        try {
            $dadosAntigos = $funcionario->toArray();
            
            // Atualizar usuário se existir e for solicitado
            if ($funcionario->user_id && $request->atualizar_usuario && $request->filled('password')) {
                $user = User::find($funcionario->user_id);
                if ($user) {
                    $user->update([
                        'name' => $request->nome . ' ' . $request->sobrenome,
                        'email' => $request->email,
                        'password' => Hash::make($request->password),
                    ]);
                }
            } else if ($funcionario->user_id) {
                // Atualizar apenas nome e email
                $user = User::find($funcionario->user_id);
                if ($user) {
                    $user->update([
                        'name' => $request->nome . ' ' . $request->sobrenome,
                        'email' => $request->email,
                    ]);
                }
            }
            
            // Atualizar cargo do usuário se o cargo do funcionário mudou
            if ($funcionario->user_id && $funcionario->cargo !== $request->cargo) {
                $user = User::find($funcionario->user_id);
                if ($user) {
                    // Remover cargo anterior baseado no cargo antigo do funcionário
                    $cargoAntigo = Cargo::where('nome', $funcionario->cargo)->first();
                    if ($cargoAntigo) {
                        $user->cargos()->detach($cargoAntigo->id);
                    }
                    
                    // Adicionar novo cargo (verificando se já existe para evitar duplicação)
                    $cargoNovo = Cargo::where('nome', $request->cargo)->where('ativo', true)->first();
                    if ($cargoNovo && !$user->cargos()->where('cargo_id', $cargoNovo->id)->exists()) {
                        $user->cargos()->attach($cargoNovo->id);
                    }
                }
            }
            
            $funcionario->update([
                'nome' => $request->nome,
                'sobrenome' => $request->sobrenome,
                'cpf' => $request->cpf,
                'rg' => $request->rg,
                'data_nascimento' => $this->convertDateFormat($request->data_nascimento),
                'telefone' => $request->telefone,
                'email' => $request->email,
                'endereco' => $request->endereco,
                'cidade' => $request->cidade,
                'estado' => $request->estado,
                'cep' => $request->cep,
                'cargo' => $request->cargo,
                'departamento' => $request->departamento,
                'data_contratacao' => $this->convertDateFormat($request->data_contratacao),
                'data_demissao' => $this->convertDateFormat($request->data_demissao),
                'salario' => $request->salario,
                'ativo' => $request->ativo ?? false,
                'observacoes' => $request->observacoes,
            ]);
            
            // Atualizar disciplinas
            if ($request->has('disciplinas')) {
                $funcionario->disciplinas()->sync($request->disciplinas);
            } else {
                $funcionario->disciplinas()->detach();
            }

            // Registrar no histórico
            Historico::registrar(
                'atualizado',
                'Funcionario',
                $funcionario->id,
                $dadosAntigos,
                $funcionario->fresh()->toArray(),
                'Funcionário atualizado com sucesso'
            );

            DB::commit();
            AlertService::success('Funcionário atualizado com sucesso!');
            return redirect()->route('funcionarios.index');
        } catch (\Exception $e) {
            DB::rollBack();
            AlertService::systemError('Erro ao atualizar funcionário', $e);
            return redirect()->back()->withInput();
        }
    }

    /**
     * Toggle the status of the specified resource.
     */
    public function toggleStatus(string $id)
    {
        try {
            $funcionario = Funcionario::findOrFail($id);
            $dadosAntigos = $funcionario->toArray();
            
            // Alternar o status
            $funcionario->ativo = !$funcionario->ativo;
            $funcionario->save();
            
            // Se tiver usuário associado, também alterar o status do usuário
            if ($funcionario->user_id) {
                $user = User::find($funcionario->user_id);
                if ($user) {
                    // Aqui você pode decidir se quer desativar o usuário também
                    // Por enquanto, vamos manter o usuário ativo
                }
            }
            
            $acao = $funcionario->ativo ? 'ativado' : 'inativado';
            
            // Registrar no histórico
            Historico::registrar(
                $acao,
                'Funcionario',
                $funcionario->id,
                $dadosAntigos,
                $funcionario->fresh()->toArray(),
                "Funcionário {$acao} com sucesso"
            );
            
            $mensagem = $funcionario->ativo ? 'Funcionário ativado com sucesso!' : 'Funcionário inativado com sucesso!';
            AlertService::success($mensagem);
            
            return redirect()->route('funcionarios.index');
        } catch (\Exception $e) {
            AlertService::systemError('Erro ao alterar status do funcionário', $e);
            return redirect()->route('funcionarios.index');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $funcionario = Funcionario::findOrFail($id);
            
            // Verificar se tem escalas ou presenças associadas
            if ($funcionario->escalas()->count() > 0 || $funcionario->presencas()->count() > 0) {
                AlertService::error('Não é possível remover este funcionário pois possui escalas ou registros de presença associados.');
                return redirect()->route('funcionarios.index');
            }
            
            $dadosAntigos = $funcionario->toArray();
            
            // Se tiver usuário associado, remover também
            if ($funcionario->user_id) {
                $user = User::find($funcionario->user_id);
                if ($user) {
                    $user->delete();
                }
            }
            
            $funcionario->delete();
            
            // Registrar no histórico
            Historico::registrar(
                'excluído',
                'Funcionario',
                $id,
                $dadosAntigos,
                null,
                'Funcionário excluído com sucesso'
            );
            
            AlertService::success('Funcionário removido com sucesso!');
            return redirect()->route('funcionarios.index');
        } catch (\Exception $e) {
            AlertService::systemError('Erro ao remover funcionário', $e);
            return redirect()->route('funcionarios.index');
        }
    }
}
