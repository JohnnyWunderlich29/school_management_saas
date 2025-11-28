<?php

namespace App\Http\Controllers;

use App\Models\TempoSlot;
use App\Models\Turno;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TempoSlotController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Turno $turno)
    {
        // Verificar se o usuário pode acessar este turno
        if (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte')) {
            if (session('escola_atual') && $turno->escola_id !== session('escola_atual')) {
                abort(404);
            }
        } else {
            if (auth()->user()->escola_id && $turno->escola_id !== auth()->user()->escola_id) {
                abort(404);
            }
        }

        $tempoSlots = $turno->tempoSlots()->ordenados()->get();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'turno' => [
                    'id' => $turno->id,
                    'nome' => $turno->nome,
                ],
                'slots' => $tempoSlots->map(function ($s) {
                    return [
                        'id' => $s->id,
                        'nome' => $s->nome,
                        'tipo' => $s->tipo,
                        'tipo_formatado' => $s->tipo_formatado,
                        'hora_inicio' => $s->hora_inicio ? substr($s->hora_inicio, 0, 5) : null,
                        'hora_fim' => $s->hora_fim ? substr($s->hora_fim, 0, 5) : null,
                        'horario_formatado' => $s->horario_formatado,
                        'duracao_minutos' => $s->duracao_minutos,
                        'ordem' => $s->ordem,
                        'descricao' => $s->descricao,
                        'ativo' => (bool) $s->ativo,
                        'show_url' => route('admin.turnos.tempo-slots.show', [$s->turno_id, $s->id]),
                        'edit_url' => route('admin.turnos.tempo-slots.edit', [$s->turno_id, $s->id]),
                        'update_url' => route('admin.turnos.tempo-slots.update', [$s->turno_id, $s->id]),
                        'delete_url' => route('admin.turnos.tempo-slots.destroy', [$s->turno_id, $s->id]),
                    ];
                }),
            ]);
        }

        return view('admin.tempo-slots.index', compact('turno', 'tempoSlots'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Turno $turno)
    {
        // Verificar se o usuário pode criar tempo slots para este turno
        if (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte')) {
            if (session('escola_atual') && $turno->escola_id !== session('escola_atual')) {
                abort(404);
            }
        } else {
            if (auth()->user()->escola_id && $turno->escola_id !== auth()->user()->escola_id) {
                abort(404);
            }
        }

        $tipos = TempoSlot::getTiposOptions();
        
        return view('admin.tempo-slots.create', compact('turno', 'tipos'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Turno $turno)
    {
        // Verificar se o usuário pode criar tempo slots para este turno
        if (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte')) {
            if (session('escola_atual') && $turno->escola_id !== session('escola_atual')) {
                abort(404);
            }
        } else {
            if (auth()->user()->escola_id && $turno->escola_id !== auth()->user()->escola_id) {
                abort(404);
            }
        }

        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'tipo' => ['required', Rule::in(array_keys(TempoSlot::TIPOS))],
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fim' => 'required|date_format:H:i|after:hora_inicio',
            'ordem' => 'required|integer|min:1',
            'duracao_minutos' => 'nullable|integer|min:1',
            'descricao' => 'nullable|string|max:500',
            'ativo' => 'boolean'
        ]);

        // Calcular duração em minutos se não fornecida
        if (!$validated['duracao_minutos']) {
            $inicio = \Carbon\Carbon::createFromFormat('H:i', $validated['hora_inicio']);
            $fim = \Carbon\Carbon::createFromFormat('H:i', $validated['hora_fim']);
            $validated['duracao_minutos'] = $fim->diffInMinutes($inicio);
        }

        $validated['turno_id'] = $turno->id;
        $validated['escola_id'] = $turno->escola_id;
        $validated['ativo'] = $request->has('ativo');

        $slot = TempoSlot::create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Tempo slot criado com sucesso!',
                'data' => [
                    'id' => $slot->id,
                    'nome' => $slot->nome,
                    'tipo' => $slot->tipo,
                    'tipo_formatado' => $slot->tipo_formatado,
                    'hora_inicio' => substr($slot->hora_inicio, 0, 5),
                    'hora_fim' => substr($slot->hora_fim, 0, 5),
                    'horario_formatado' => $slot->horario_formatado,
                    'duracao_minutos' => $slot->duracao_minutos,
                    'ordem' => $slot->ordem,
                    'descricao' => $slot->descricao,
                    'ativo' => (bool) $slot->ativo,
                ],
            ], 201);
        }

        return redirect()
            ->route('admin.turnos.tempo-slots.index', $turno)
            ->with('success', 'Tempo slot criado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Turno $turno, TempoSlot $tempoSlot)
    {
        // Verificar se o tempo slot pertence ao turno
        if ($tempoSlot->turno_id !== $turno->id) {
            abort(404);
        }

        // Verificar se o usuário pode acessar este turno
        if (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte')) {
            if (session('escola_atual') && $turno->escola_id !== session('escola_atual')) {
                abort(404);
            }
        } else {
            if (auth()->user()->escola_id && $turno->escola_id !== auth()->user()->escola_id) {
                abort(404);
            }
        }

        // Buscar tempo slots anterior e próximo
        $tempoSlotAnterior = $tempoSlot->getTempoSlotAnterior();
        $proximoTempoSlot = $tempoSlot->getProximoTempoSlot();

        return view('admin.tempo-slots.show', compact('turno', 'tempoSlot', 'tempoSlotAnterior', 'proximoTempoSlot'));
    }

    /**
     * Display the specified resource for modal view (AJAX).
     */
    public function showModal(Turno $turno, TempoSlot $tempoSlot)
    {
        // Verificar se o tempo slot pertence ao turno
        if ($tempoSlot->turno_id !== $turno->id) {
            abort(404);
        }

        // Verificar se o usuário pode acessar este turno
        if (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte')) {
            if (session('escola_atual') && $turno->escola_id !== session('escola_atual')) {
                abort(404);
            }
        } else {
            if (auth()->user()->escola_id && $turno->escola_id !== auth()->user()->escola_id) {
                abort(404);
            }
        }

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $tempoSlot->id,
                    'nome' => $tempoSlot->nome,
                    'tipo' => $tempoSlot->tipo_formatado,
                    'horario' => $tempoSlot->horario_formatado,
                    'duracao' => $tempoSlot->duracao_minutos . ' minutos',
                    'ordem' => $tempoSlot->ordem,
                    'descricao' => $tempoSlot->descricao ?: 'Nenhuma descrição',
                    'ativo' => $tempoSlot->ativo ? 'Sim' : 'Não',
                    'turno' => $turno->nome
                ]
            ]);
        }

        return redirect()->route('admin.turnos.tempo-slots.show', [$turno, $tempoSlot]);
    }

    /**
     * Show the form for editing the specified resource for modal (AJAX).
     */
    public function editModal(Turno $turno, TempoSlot $tempoSlot)
    {
        // Verificar se o tempo slot pertence ao turno
        if ($tempoSlot->turno_id !== $turno->id) {
            abort(404);
        }

        // Verificar se o usuário pode editar este turno
        if (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte')) {
            if (session('escola_atual') && $turno->escola_id !== session('escola_atual')) {
                abort(404);
            }
        } else {
            if (auth()->user()->escola_id && $turno->escola_id !== auth()->user()->escola_id) {
                abort(404);
            }
        }

        if (request()->ajax()) {
            $tipos = TempoSlot::getTiposOptions();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $tempoSlot->id,
                    'nome' => $tempoSlot->nome,
                    'tipo' => $tempoSlot->tipo,
                    'hora_inicio' => $tempoSlot->hora_inicio ? substr($tempoSlot->hora_inicio, 0, 5) : null,
                'hora_fim' => $tempoSlot->hora_fim ? substr($tempoSlot->hora_fim, 0, 5) : null,
                    'ordem' => $tempoSlot->ordem,
                    'duracao_minutos' => $tempoSlot->duracao_minutos,
                    'descricao' => $tempoSlot->descricao,
                    'ativo' => $tempoSlot->ativo,
                    'tipos' => $tipos,
                    'update_url' => route('admin.turnos.tempo-slots.update', [$turno, $tempoSlot])
                ]
            ]);
        }

        return redirect()->route('admin.turnos.tempo-slots.edit', [$turno, $tempoSlot]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Turno $turno, TempoSlot $tempoSlot)
    {
        // Verificar se o tempo slot pertence ao turno
        if ($tempoSlot->turno_id !== $turno->id) {
            abort(404);
        }

        // Verificar se o usuário pode editar este turno
        if (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte')) {
            if (session('escola_atual') && $turno->escola_id !== session('escola_atual')) {
                abort(404);
            }
        } else {
            if (auth()->user()->escola_id && $turno->escola_id !== auth()->user()->escola_id) {
                abort(404);
            }
        }

        $tipos = TempoSlot::getTiposOptions();
        
        return view('admin.tempo-slots.edit', compact('turno', 'tempoSlot', 'tipos'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Turno $turno, TempoSlot $tempoSlot)
    {
        // Verificar se o tempo slot pertence ao turno
        if ($tempoSlot->turno_id !== $turno->id) {
            abort(404);
        }

        // Verificar se o usuário pode editar este turno
        if (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte')) {
            if (session('escola_atual') && $turno->escola_id !== session('escola_atual')) {
                abort(404);
            }
        } else {
            if (auth()->user()->escola_id && $turno->escola_id !== auth()->user()->escola_id) {
                abort(404);
            }
        }

        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'tipo' => ['required', Rule::in(array_keys(TempoSlot::TIPOS))],
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fim' => 'required|date_format:H:i|after:hora_inicio',
            'ordem' => 'required|integer|min:1',
            'duracao_minutos' => 'nullable|integer|min:1',
            'descricao' => 'nullable|string|max:500',
            'ativo' => 'boolean'
        ]);

        // Calcular duração em minutos se não fornecida
        if (!$validated['duracao_minutos']) {
            $inicio = \Carbon\Carbon::createFromFormat('H:i', $validated['hora_inicio']);
            $fim = \Carbon\Carbon::createFromFormat('H:i', $validated['hora_fim']);
            $validated['duracao_minutos'] = $fim->diffInMinutes($inicio);
        }

        $validated['ativo'] = $request->has('ativo');

        $tempoSlot->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Tempo slot atualizado com sucesso!',
                'data' => [
                    'id' => $tempoSlot->id,
                    'nome' => $tempoSlot->nome,
                    'tipo' => $tempoSlot->tipo,
                    'tipo_formatado' => $tempoSlot->tipo_formatado,
                    'hora_inicio' => substr($tempoSlot->hora_inicio, 0, 5),
                    'hora_fim' => substr($tempoSlot->hora_fim, 0, 5),
                    'horario_formatado' => $tempoSlot->horario_formatado,
                    'duracao_minutos' => $tempoSlot->duracao_minutos,
                    'ordem' => $tempoSlot->ordem,
                    'descricao' => $tempoSlot->descricao,
                    'ativo' => (bool) $tempoSlot->ativo,
                ],
            ]);
        }

        return redirect()
            ->route('admin.turnos.tempo-slots.index', $turno)
            ->with('success', 'Tempo slot atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Turno $turno, TempoSlot $tempoSlot)
    {
        // Verificar se o tempo slot pertence ao turno
        if ($tempoSlot->turno_id !== $turno->id) {
            abort(404);
        }

        // Verificar se o usuário pode excluir este turno
        if (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte')) {
            if (session('escola_atual') && $turno->escola_id !== session('escola_atual')) {
                abort(404);
            }
        } else {
            if (auth()->user()->escola_id && $turno->escola_id !== auth()->user()->escola_id) {
                abort(404);
            }
        }

        $tempoSlot->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Tempo slot excluído com sucesso!',
            ]);
        }

        return redirect()
            ->route('admin.turnos.tempo-slots.index', $turno)
            ->with('success', 'Tempo slot excluído com sucesso!');
    }
}
