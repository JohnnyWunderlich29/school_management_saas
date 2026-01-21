<?php

namespace App\Http\Controllers;

use App\Models\Disciplina;
use App\Models\Funcionario;
use App\Models\GradeAula;
use App\Models\Prova;
use App\Models\Questao;
use App\Models\QuestaoAlternativa;
use App\Models\Turma;
use App\Services\AlertService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProvaController extends Controller
{
    public function index(Request $request)
    {
        $query = Prova::with(['turma', 'disciplina', 'professor']);

        // Filtro por escola
        $escolaId = auth()->user()->escola_id ?: session('escola_atual');
        if ($escolaId) {
            $query->where('escola_id', $escolaId);
        }

        // Filtros Adicionais
        if ($request->filled('titulo')) {
            $query->where('titulo', 'like', '%' . $request->titulo . '%');
        }

        if ($request->filled('turma_id')) {
            $query->where('turma_id', $request->turma_id);
        }

        if ($request->filled('disciplina_id')) {
            $query->where('disciplina_id', $request->disciplina_id);
        }

        if ($request->filled('status') && $request->status !== 'todos') {
            $query->where('status', $request->status);
        }

        // Se for professor, ver apenas as suas
        if (auth()->user()->temCargo('Professor')) {
            $funcionario = Funcionario::where('user_id', auth()->id())->first();
            if ($funcionario) {
                $query->where('funcionario_id', $funcionario->id);
            }
        }

        $provas = $query->latest()->paginate(10);

        // Dados para os filtros
        $turmas = Turma::where('escola_id', $escolaId)->ativas()->orderBy('nome')->get();
        $disciplinas = Disciplina::ativas()->orderBy('nome')->get();

        if ($request->ajax()) {
            return view('provas.index', compact('provas', 'turmas', 'disciplinas'))->render();
        }

        return view('provas.index', compact('provas', 'turmas', 'disciplinas'));
    }

    public function create()
    {
        $escolaId = auth()->user()->escola_id ?: session('escola_atual');

        $turmas = Turma::where('escola_id', $escolaId)->ativas()->get();
        $disciplinas = Disciplina::ativas()->get();

        // Se for professor, filtrar turmas/disciplinas da sua grade
        if (auth()->user()->temCargo('Professor')) {
            $funcionario = Funcionario::where('user_id', auth()->id())->first();
            if ($funcionario) {
                $grade = GradeAula::where('funcionario_id', $funcionario->id)
                    ->with(['turma', 'disciplina'])
                    ->get();

                $turmas = $grade->pluck('turma')->unique('id');
                $disciplinas = $grade->pluck('disciplina')->unique('id');
            }
        }

        $questoesJson = "[]";

        return view('provas.editor', compact('turmas', 'disciplinas', 'questoesJson'));
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $escolaId = auth()->user()->escola_id ?: session('escola_atual');
            $funcionario = Funcionario::where('user_id', auth()->id())->first();
            $funcionarioId = $funcionario ? $funcionario->id : null;

            // Se não encontrou pelo usuário logado, tenta pegar da GradeAula
            if (!$funcionarioId && $request->grade_aula_id) {
                $grade = GradeAula::find($request->grade_aula_id);
                if ($grade) {
                    $funcionarioId = $grade->funcionario_id;
                }
            }

            $prova = Prova::create([
                'escola_id' => $escolaId,
                'turma_id' => $request->turma_id,
                'disciplina_id' => $request->disciplina_id,
                'funcionario_id' => $funcionarioId,
                'grade_aula_id' => $request->grade_aula_id,
                'titulo' => $request->titulo,
                'descricao' => $request->descricao,
                'data_aplicacao' => $request->data_aplicacao,
                'status' => $request->status ?? 'rascunho',
            ]);

            if ($request->has('questoes')) {
                foreach ($request->questoes as $index => $qData) {
                    $imagemPath = null;
                    if (isset($qData['imagem']) && $qData['imagem'] instanceof \Illuminate\Http\UploadedFile) {
                        $imagemPath = $qData['imagem']->store('provas/questoes', 'public');
                    }

                    $questao = $prova->questoes()->create([
                        'tipo' => $qData['tipo'],
                        'enunciado' => $qData['enunciado'],
                        'imagem_path' => $imagemPath,
                        'ordem' => $index,
                        'valor' => $qData['valor'] ?? 0,
                    ]);

                    if ($qData['tipo'] === 'multipla_escolha' && isset($qData['alternativas'])) {
                        foreach ($qData['alternativas'] as $altIndex => $altData) {
                            $questao->alternativas()->create([
                                'texto' => $altData['texto'],
                                'correta' => isset($altData['correta']) && $altData['correta'] == "1",
                                'ordem' => $altIndex,
                            ]);
                        }
                    }
                }
            }

            DB::commit();
            AlertService::success('Prova salva com sucesso!');
            return redirect()->route('provas.index');

        } catch (Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Erro ao salvar prova: ' . $e->getMessage());
        }
    }

    public function edit(Prova $prova)
    {
        $prova->load(['questoes.alternativas']);
        $escolaId = auth()->user()->escola_id ?: session('escola_atual');

        $turmas = Turma::where('escola_id', $escolaId)->ativas()->get();
        $disciplinas = Disciplina::ativas()->get();

        $questoesJson = $prova->questoes->map(function ($q) {
            return [
                'uid' => uniqid(),
                'tipo' => $q->tipo,
                'enunciado' => $q->enunciado,
                'valor' => $q->valor,
                'alternativas' => $q->alternativas->map(function ($a) {
                    return ['texto' => $a->texto, 'correta' => $a->correta];
                })
            ];
        })->toJson();

        return view('provas.editor', compact('prova', 'turmas', 'disciplinas', 'questoesJson'));
    }

    public function update(Request $request, Prova $prova)
    {
        try {
            DB::beginTransaction();

            $funcionario = Funcionario::where('user_id', auth()->id())->first();
            $funcionarioId = $funcionario ? $funcionario->id : $prova->funcionario_id;

            // Se mudou a grade, tenta pegar o novo funcionário
            if ($request->grade_aula_id && $request->grade_aula_id != $prova->grade_aula_id) {
                $grade = GradeAula::find($request->grade_aula_id);
                if ($grade) {
                    $funcionarioId = $grade->funcionario_id;
                }
            }

            $prova->update([
                'turma_id' => $request->turma_id,
                'disciplina_id' => $request->disciplina_id,
                'funcionario_id' => $funcionarioId,
                'grade_aula_id' => $request->grade_aula_id,
                'titulo' => $request->titulo,
                'descricao' => $request->descricao,
                'data_aplicacao' => $request->data_aplicacao,
                'status' => $request->status ?? 'rascunho',
            ]);

            // Para simplificar a atualização, vamos remover as questões e alternativas antigas e recriar
            // Em um sistema real, poderíamos comparar IDs para manter o histórico, mas aqui recriar é mais seguro por causa da ordem e IDs novos vindos do JS.
            // Primeiro, deletar imagens físicas (opcional, mas recomendado)
            foreach ($prova->questoes as $q) {
                if ($q->imagem_path) {
                    Storage::disk('public')->delete($q->imagem_path);
                }
                $q->alternativas()->delete();
            }
            $prova->questoes()->delete();

            if ($request->has('questoes')) {
                foreach ($request->questoes as $index => $qData) {
                    $imagemPath = null;
                    if (isset($qData['imagem']) && $qData['imagem'] instanceof \Illuminate\Http\UploadedFile) {
                        $imagemPath = $qData['imagem']->store('provas/questoes', 'public');
                    }

                    $questao = $prova->questoes()->create([
                        'tipo' => $qData['tipo'],
                        'enunciado' => $qData['enunciado'],
                        'imagem_path' => $imagemPath,
                        'ordem' => $index,
                        'valor' => $qData['valor'] ?? 0,
                    ]);

                    if ($qData['tipo'] === 'multipla_escolha' && isset($qData['alternativas'])) {
                        foreach ($qData['alternativas'] as $altIndex => $altData) {
                            $questao->alternativas()->create([
                                'texto' => $altData['texto'],
                                'correta' => isset($altData['correta']) && $altData['correta'] == "1",
                                'ordem' => $altIndex,
                            ]);
                        }
                    }
                }
            }

            DB::commit();
            AlertService::success('Prova atualizada com sucesso!');
            return redirect()->route('provas.index');

        } catch (Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Erro ao atualizar prova: ' . $e->getMessage());
        }
    }

    public function destroy(Prova $prova)
    {
        $prova->delete();
        return redirect()->route('provas.index')->with('success', 'Prova excluída.');
    }

    /**
     * Exporta a prova para PDF
     */
    public function exportPdf(Prova $prova)
    {
        $prova->load(['questoes.alternativas', 'turma', 'disciplina', 'professor', 'escola']);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('provas.pdf', compact('prova'));

        return $pdf->download('prova-' . Str::slug($prova->titulo) . '.pdf');
    }

    /**
     * Ajax para carregar slots de aula baseados em turma e disciplina
     */
    public function getSlots(Request $request)
    {
        $funcionario = Funcionario::where('user_id', auth()->id())->first();

        $query = GradeAula::where('turma_id', $request->turma_id)
            ->where('disciplina_id', $request->disciplina_id);

        if ($funcionario) {
            $query->where('funcionario_id', $funcionario->id);
        }

        $slots = $query->with('tempoSlot')->get();

        return response()->json($slots);
    }
}
