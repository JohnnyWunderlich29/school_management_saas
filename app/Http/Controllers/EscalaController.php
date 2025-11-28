<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Escala;
use App\Models\Funcionario;
use App\Models\Sala;
use App\Models\Historico;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class EscalaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Escala::select(
            'id', 'funcionario_id', 'data', 'hora_inicio', 'hora_fim', 
            'tipo_escala', 'status', 'tipo_atividade', 'sala_id', 'observacoes'
        )
        ->with([
            'funcionario:id,nome,sobrenome,cargo',
            'sala:id,codigo,nome'
        ]);
        
        // Filtros otimizados
        if ($request->filled('funcionario_id')) {
            $query->where('funcionario_id', $request->funcionario_id);
        }
        
        if ($request->filled('data_inicio')) {
            $query->where('data', '>=', $request->data_inicio);
        }
        
        if ($request->filled('data_fim')) {
            $query->where('data', '<=', $request->data_fim);
        }
        
        if ($request->filled('tipo_escala')) {
            $query->where('tipo_escala', $request->tipo_escala);
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('tipo_atividade')) {
            $query->where('tipo_atividade', $request->tipo_atividade);
        }
        
        if ($request->filled('sala_id')) {
            $query->where('sala_id', $request->sala_id);
        }
        
        // Buscar escalas sem paginação para agrupamento
        $escalasCollection = $query->orderBy('data', 'desc')
                                  ->orderBy('hora_inicio')
                                  ->get();

        // Agrupar escalas por funcionário e data
        $escalasAgrupadas = $escalasCollection->groupBy(function($escala) {
            return $escala->funcionario_id . '_' . $escala->data->format('Y-m-d');
        })->map(function($grupo) {
            $primeiraEscala = $grupo->first();
            
            // Calcular total de horas
            $totalHoras = $grupo->sum(function($escala) {
                $inicio = \Carbon\Carbon::parse($escala->hora_inicio);
                $fim = \Carbon\Carbon::parse($escala->hora_fim);
                return $fim->diffInMinutes($inicio) / 60;
            });
            
            return (object) [
                'funcionario' => $primeiraEscala->funcionario,
                'data' => $primeiraEscala->data,
                'escalas' => $grupo,
                'total_horas' => $totalHoras
            ];
        })->values();
        
        // Implementar paginação manual
        $perPage = 15;
        $currentPage = request()->get('page', 1);
        $offset = ($currentPage - 1) * $perPage;
        
        $escalasAgrupadas = new \Illuminate\Pagination\LengthAwarePaginator(
            $escalasAgrupadas->slice($offset, $perPage)->values(),
            $escalasAgrupadas->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );
        
        // Carregar dados para filtros usando scopes
        $funcionarios = Funcionario::ativos()
            ->select('id', 'nome', 'sobrenome')
            ->orderBy('nome')
            ->get();
            
        $salas = Sala::ativas()
            ->select('id', 'codigo', 'nome')
            ->orderBy('codigo')
            ->get();
        
        return view('escalas.index', compact('escalasAgrupadas', 'funcionarios', 'salas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $funcionarios = Funcionario::where('ativo', 1)->orderBy('nome')->get();
        
        // Aplicar filtro de escola nas salas
        $salasQuery = \App\Models\Sala::where('ativo', true);
        if (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte')) {
            if (session('escola_atual')) {
                $salasQuery->where('escola_id', session('escola_atual'));
            }
        } else {
            if (auth()->user()->escola_id) {
                $salasQuery->where('escola_id', auth()->user()->escola_id);
            }
        }
        $salas = $salasQuery->orderBy('codigo')->get();
        
        return view('escalas.create', compact('funcionarios', 'salas'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'funcionario_id' => 'required|exists:funcionarios,id',
            'sala_id' => 'nullable|exists:salas,id',
            'data' => 'required|date',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fim' => 'required|date_format:H:i|after:hora_inicio',
            'tipo_escala' => 'required|in:Normal,Extra,Substituição',
            'tipo_atividade' => 'required|in:em_sala,pl,ausente',
            'status' => 'required|in:Agendada,Ativa,Concluída',
            'observacoes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Verificar se já existe escala para o funcionário no mesmo horário
            $dataEscala = Carbon::parse($request->data);
            $horaInicio = Carbon::parse($request->hora_inicio);
            $horaFim = Carbon::parse($request->hora_fim);
            
            $conflito = Escala::where('funcionario_id', $request->funcionario_id)
                ->where('data', $dataEscala->format('Y-m-d'))
                ->where(function($query) use ($horaInicio, $horaFim) {
                    $query->whereBetween('hora_inicio', [$horaInicio->format('H:i'), $horaFim->format('H:i')])
                          ->orWhereBetween('hora_fim', [$horaInicio->format('H:i'), $horaFim->format('H:i')])
                          ->orWhere(function($q) use ($horaInicio, $horaFim) {
                              $q->where('hora_inicio', '<=', $horaInicio->format('H:i'))
                                ->where('hora_fim', '>=', $horaFim->format('H:i'));
                          });
                })
                ->exists();
                
            if ($conflito) {
                return redirect()->back()
                    ->with('error', 'Já existe uma escala para este funcionário neste horário.')
                    ->withInput();
            }
            
            $escala = Escala::create([
                'funcionario_id' => $request->funcionario_id,
                'sala_id' => $request->sala_id,
                'data' => $request->data,
                'hora_inicio' => $request->hora_inicio,
                'hora_fim' => $request->hora_fim,
                'tipo_escala' => $request->tipo_escala,
                'tipo_atividade' => $request->tipo_atividade,
                'status' => $request->status,
                'observacoes' => $request->observacoes,
            ]);

            // Registrar no histórico
            Historico::registrar(
                'criado',
                'Escala',
                $escala->id,
                null,
                $escala->toArray(),
                'Escala criada'
            );

            return redirect()->route('escalas.index')
                ->with('success', 'Escala cadastrada com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erro ao cadastrar escala: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $escala = Escala::with(['funcionario', 'sala'])->findOrFail($id);
        return view('escalas.show', compact('escala'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $escala = Escala::findOrFail($id);
        $funcionarios = Funcionario::where('ativo', 1)->orderBy('nome')->get();
        
        // Aplicar filtro de escola nas salas
        $salasQuery = Sala::where('ativo', 1);
        if (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte')) {
            if (session('escola_atual')) {
                $salasQuery->where('escola_id', session('escola_atual'));
            }
        } else {
            if (auth()->user()->escola_id) {
                $salasQuery->where('escola_id', auth()->user()->escola_id);
            }
        }
        $salas = $salasQuery->orderBy('codigo')->get();
        
        return view('escalas.edit', compact('escala', 'funcionarios', 'salas'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'funcionario_id' => 'required|exists:funcionarios,id',
            'data' => 'required|date',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fim' => 'required|date_format:H:i|after:hora_inicio',
            'tipo_escala' => 'required|in:Normal,Extra,Substituição',
            'sala_id' => 'nullable|exists:salas,id',
            'tipo_atividade' => 'required|in:em_sala,pl,ausente',
            'status' => 'required|in:Agendada,Ativa,Concluída',
            'observacoes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $escala = Escala::findOrFail($id);
            
            // Capturar dados antigos para o histórico
            $dadosAntigos = $escala->toArray();
            
            // Verificar se já existe escala para o funcionário no mesmo horário (excluindo a atual)
            $dataEscala = Carbon::parse($request->data);
            $horaInicio = Carbon::parse($request->hora_inicio);
            $horaFim = Carbon::parse($request->hora_fim);
            
            $conflito = Escala::where('funcionario_id', $request->funcionario_id)
                ->where('id', '!=', $id)
                ->where('data', $dataEscala->format('Y-m-d'))
                ->where(function($query) use ($horaInicio, $horaFim) {
                    $query->whereBetween('hora_inicio', [$horaInicio->format('H:i'), $horaFim->format('H:i')])
                          ->orWhereBetween('hora_fim', [$horaInicio->format('H:i'), $horaFim->format('H:i')])
                          ->orWhere(function($q) use ($horaInicio, $horaFim) {
                              $q->where('hora_inicio', '<=', $horaInicio->format('H:i'))
                                ->where('hora_fim', '>=', $horaFim->format('H:i'));
                          });
                })
                ->exists();
                
            if ($conflito) {
                return redirect()->back()
                    ->with('error', 'Já existe uma escala para este funcionário neste horário.')
                    ->withInput();
            }
            
            $escala->update([
                'funcionario_id' => $request->funcionario_id,
                'data' => $request->data,
                'hora_inicio' => $request->hora_inicio,
                'hora_fim' => $request->hora_fim,
                'tipo_escala' => $request->tipo_escala,
                'sala_id' => $request->sala_id,
                'tipo_atividade' => $request->tipo_atividade,
                'status' => $request->status,
                'observacoes' => $request->observacoes,
            ]);

            // Registrar no histórico
            Historico::registrar(
                'atualizado',
                'Escala',
                $escala->id,
                $dadosAntigos,
                $escala->fresh()->toArray(),
                'Escala atualizada'
            );

            return redirect()->route('escalas.index')
                ->with('success', 'Escala atualizada com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erro ao atualizar escala: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $escala = Escala::findOrFail($id);
            
            // Capturar dados para o histórico antes da exclusão
            $dadosAntigos = $escala->toArray();
            
            $escala->delete();
            
            // Registrar no histórico
            Historico::registrar(
                'excluido',
                'Escala',
                $id,
                $dadosAntigos,
                null,
                'Escala excluída'
            );
            
            return redirect()->route('escalas.index')
                ->with('success', 'Escala removida com sucesso!');
        } catch (\Exception $e) {
            return redirect()->route('escalas.index')
                ->with('error', 'Erro ao remover escala: ' . $e->getMessage());
        }
    }
}
