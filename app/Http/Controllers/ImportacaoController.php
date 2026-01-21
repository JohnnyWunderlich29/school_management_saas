<?php

namespace App\Http\Controllers;

use App\Models\Aluno;
use App\Models\Responsavel;
use App\Models\Despesa;
use App\Models\RecorrenciaDespesa;
use App\Models\Sala;
use App\Models\Turma;
use App\Models\Historico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ImportacaoController extends Controller
{
    /**
     * Download CSV template for specific type
     */
    public function downloadTemplate($type)
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ];

        if ($type === 'alunos') {
            $headers['Content-Disposition'] = 'attachment; filename="modelo_importacao_alunos.csv"';
            $columns = [
                'nome',
                'sobrenome',
                'data_nascimento',
                'matricula',
                'email',
                'telefone',
                'cpf',
                'rg',
                'endereco',
                'cidade',
                'estado',
                'cep',
                'genero',
                'tipo_sanguineo',
                'alergias',
                'medicamentos',
                'observacoes',
                'sala_codigo_ou_nome',
                'turma_nome_ou_codigo'
            ];
            $example = [
                'João',
                'Silva',
                '20/05/2015',
                '2024001',
                'joao.silva@email.com',
                '11999999999',
                '123.456.789-00',
                '12345678',
                'Rua Exemplo, 123',
                'São Paulo',
                'SP',
                '01001-000',
                'Masculino',
                'O+',
                'Nenhuma',
                'Nenhum',
                'Aluno novo',
                'Sala 01',
                '1º Ano A'
            ];
        } elseif ($type === 'responsaveis') {
            $headers['Content-Disposition'] = 'attachment; filename="modelo_importacao_responsaveis.csv"';
            $columns = [
                'nome',
                'sobrenome',
                'data_nascimento',
                'cpf',
                'rg',
                'telefone_principal',
                'telefone_secundario',
                'email',
                'endereco',
                'cidade',
                'estado',
                'cep',
                'parentesco',
                'genero',
                'autorizado_buscar',
                'contato_emergencia',
                'observacoes'
            ];
            $example = [
                'Maria',
                'Oliveira',
                '15/08/1985',
                '987.654.321-00',
                '87654321',
                '11988888888',
                '1133333333',
                'maria@email.com',
                'Av. Brasil, 500',
                'Curitiba',
                'PR',
                '80000-000',
                'Mãe',
                'Feminino',
                'Sim',
                'Sim',
                'Responsável principal'
            ];
        } elseif ($type === 'despesas') {
            $headers['Content-Disposition'] = 'attachment; filename="modelo_importacao_despesas.csv"';
            $columns = [
                'descricao',
                'categoria',
                'data',
                'valor',
                'is_recorrente',
                'frequencia',
                'data_fim'
            ];
            $example = [
                'Aluguel da Unidade',
                'Gastos Fixos',
                '10/02/2026',
                '1500.00',
                'Sim',
                'mensal',
                '10/02/2027'
            ];
        } else {
            abort(404);
        }

        $callback = function () use ($columns, $example) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM UTF-8
            fputcsv($file, $columns, ';');
            fputcsv($file, $example, ';');
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Preview students from uploaded CSV
     */
    public function alunoPreview(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $file = $request->file('file');
        $path = $file->getRealPath();
        $data = [];

        if (($handle = fopen($path, 'r')) !== FALSE) {
            // Check BOM
            $bom = fread($handle, 3);
            if ($bom !== chr(0xEF) . chr(0xBB) . chr(0xBF)) {
                rewind($handle);
            }

            $headers = fgetcsv($handle, 1000, ';');

            // Expected columns check
            $expected = ['nome', 'sobrenome', 'data_nascimento'];
            foreach ($expected as $col) {
                if (!in_array($col, $headers)) {
                    return response()->json([
                        'success' => false,
                        'message' => "Coluna obrigatória ausente no CSV: {$col}"
                    ], 422);
                }
            }

            while (($row = fgetcsv($handle, 1000, ';')) !== FALSE) {
                if (count($headers) !== count($row))
                    continue;
                $data[] = array_combine($headers, $row);
            }
            fclose($handle);
        }

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Final import process
     */
    public function alunoImport(Request $request)
    {
        Log::info('ImportacaoController: Iniciando importação de alunos', [
            'usuario_id' => auth()->id(),
            'escola_sessao' => session('escola_atual'),
            'escola_usuario' => auth()->user()->escola_id ?? null,
            'total_recebido' => count($request->input('students', []))
        ]);

        $escolaId = session('escola_atual') ?: auth()->user()->escola_id;

        if (!$escolaId) {
            Log::error('ImportacaoController: Escola não identificada no contexto.');
            return response()->json([
                'success' => false,
                'message' => 'Não foi possível identificar a escola para importação. Por favor, selecione uma escola ou recarregue a página.'
            ], 422);
        }

        try {
            $rules = [
                'students' => 'required|array',
                'students.*.nome' => 'required|string|max:100',
                'students.*.sobrenome' => 'required|string|max:100',
                'students.*.data_nascimento' => 'required|string|max:20', // Validar como string primeiro para conversão
                'students.*.email' => 'nullable|email|max:100',
                'students.*.cpf' => [
                    'nullable',
                    'string',
                    'max:14',
                    \Illuminate\Validation\Rule::unique('alunos', 'cpf')->where(fn($q) => $q->where('escola_id', $escolaId))
                ],
                'students.*.matricula' => [
                    'nullable',
                    'string',
                    'max:50',
                    \Illuminate\Validation\Rule::unique('alunos', 'matricula')->where(fn($q) => $q->where('escola_id', $escolaId))
                ],
            ];

            $messages = [
                'students.*.nome.required' => 'Linha :index: O nome é obrigatório.',
                'students.*.sobrenome.required' => 'Linha :index: O sobrenome é obrigatório.',
                'students.*.data_nascimento.required' => 'Linha :index: A data de nascimento é obrigatória.',
                'students.*.email.email' => 'Linha :index: O e-mail informado é inválido.',
                'students.*.cpf.unique' => 'Linha :index: Este CPF já está cadastrado nesta escola.',
                'students.*.matricula.unique' => 'Linha :index: Esta matrícula já está em uso nesta escola.',
            ];

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                $formattedErrors = [];
                foreach ($validator->errors()->toArray() as $field => $messages) {
                    // Converter students.0.nome para "Linha 1"
                    if (preg_match('/students\.(\d+)\.(.+)/', $field, $matches)) {
                        $index = $matches[1] + 1;
                        foreach ($messages as $msg) {
                            $formattedErrors[] = str_replace(':index', $index, $msg);
                        }
                    } else {
                        foreach ($messages as $msg) {
                            $formattedErrors[] = $msg;
                        }
                    }
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Existem erros de validação nos dados do arquivo.',
                    'errors' => $formattedErrors
                ], 422);
            }

            // Pré-processar datas após validação básica
            $studentsData = $request->input('students');
            foreach ($studentsData as $idx => &$student) {
                $originalDate = $student['data_nascimento'];
                $converted = $this->convertDate($originalDate);

                if (!$converted) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Existem erros de formatação de data.',
                        'errors' => ["Linha " . ($idx + 1) . ": A data '{$originalDate}' é inválida. Use o formato DD/MM/YYYY."]
                    ], 422);
                }
                $student['data_nascimento'] = $converted;
            }
            $students = $studentsData;

        } catch (\Exception $e) {
            Log::error('ImportacaoController: Erro durante validação/conversão', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Erro ao processar os dados: ' . $e->getMessage()
            ], 500);
        }
        $escolaId = session('escola_atual') ?: auth()->user()->escola_id;

        if (!$escolaId) {
            Log::error('ImportacaoController: Escola não identificada no contexto.');
            return response()->json([
                'success' => false,
                'message' => 'Não foi possível identificar a escola para importação. Por favor, selecione uma escola ou recarregue a página.'
            ], 422);
        }

        $importedCount = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($students as $index => $data) {
                try {
                    Log::debug("Processando aluno index {$index}", ['nome' => $data['nome'] ?? 'N/A']);

                    // Find Sala if provided
                    $salaId = null;
                    if (!empty($data['sala_codigo_ou_nome'])) {
                        $sala = Sala::where('escola_id', $escolaId)
                            ->where(function ($q) use ($data) {
                                $q->where('codigo', $data['sala_codigo_ou_nome'])
                                    ->orWhere('nome', $data['sala_codigo_ou_nome']);
                            })->first();
                        $salaId = $sala?->id;
                    }

                    // Find Turma if provided
                    $turmaId = null;
                    if (!empty($data['turma_nome_ou_codigo'])) {
                        $turma = Turma::where('escola_id', $escolaId)
                            ->where(function ($q) use ($data) {
                                $q->where('nome', $data['turma_nome_ou_codigo'])
                                    ->orWhere('codigo', $data['turma_nome_ou_codigo']);
                            })->first();
                        $turmaId = $turma?->id;
                    }

                    $aluno = Aluno::create([
                        'escola_id' => $escolaId,
                        'nome' => $data['nome'],
                        'sobrenome' => $data['sobrenome'],
                        'matricula' => $data['matricula'] ?? null,
                        'data_nascimento' => $data['data_nascimento'],
                        'email' => $data['email'] ?? null,
                        'telefone' => $data['telefone'] ?? null,
                        'cpf' => $data['cpf'] ?? null,
                        'rg' => $data['rg'] ?? null,
                        'endereco' => $data['endereco'] ?? 'Não informado',
                        'cidade' => $data['cidade'] ?? 'Não informado',
                        'estado' => $data['estado'] ?? 'XX',
                        'cep' => $data['cep'] ?? '00000-000',
                        'genero' => $data['genero'] ?? null,
                        'tipo_sanguineo' => $data['tipo_sanguineo'] ?? null,
                        'alergias' => $data['alergias'] ?? null,
                        'medicamentos' => $data['medicamentos'] ?? null,
                        'observacoes' => $data['observacoes'] ?? null,
                        'sala_id' => $salaId,
                        'turma_id' => $turmaId,
                        'ativo' => true,
                    ]);

                    Historico::registrar(
                        'importado',
                        'Aluno',
                        (int) $aluno->id,
                        null,
                        $aluno->toArray(),
                        'Aluno importado via CSV'
                    );

                    $importedCount++;
                } catch (\Exception $e) {
                    Log::error("Erro na linha " . ($index + 1), ['message' => $e->getMessage()]);
                    $errors[] = "Linha " . ($index + 1) . " ({$data['nome']}): " . $e->getMessage();
                }
            }

            if (count($errors) > 0 && $importedCount === 0) {
                DB::rollBack();
                Log::warning('Importação falhou completamente', ['errors' => $errors]);
                return response()->json([
                    'success' => false,
                    'message' => 'Nenhum aluno foi importado devido a erros.',
                    'errors' => $errors
                ], 422);
            }

            DB::commit();
            Log::info("Importação concluída: {$importedCount} alunos");
            return response()->json([
                'success' => true,
                'message' => "{$importedCount} alunos importados com sucesso!",
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::critical('Erro fatal na importação: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Erro interno ao processar importação: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper para converter data BR para Banco
     */
    private function convertDate($date)
    {
        if (!$date)
            return null;
        // dd/mm/yyyy -> yyyy-mm-dd
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $date, $matches)) {
            return "{$matches[3]}-{$matches[2]}-{$matches[1]}";
        }
        // yyyy-mm-dd (já no formato)
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $date;
        }
        return null;
    }

    public function responsavelPreview(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt'
        ]);

        $file = $request->file('file');
        $data = [];

        if (($handle = fopen($file->getRealPath(), "r")) !== FALSE) {
            // Check for BOM and skip it
            $bom = fread($handle, 3);
            if ($bom !== "\xEF\xBB\xBF") {
                rewind($handle);
            }

            $headers = fgetcsv($handle, 1000, ";");
            if ($headers) {
                // Ensure headers are trimmed and lowercase for mapping
                $headers = array_map(function ($h) {
                    return strtolower(trim(str_replace(['"', "'"], '', $h)));
                }, $headers);

                while (($row = fgetcsv($handle, 1000, ";")) !== FALSE) {
                    if (count($headers) == count($row)) {
                        $data[] = array_combine($headers, $row);
                    }
                }
            }
            fclose($handle);
        }

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function responsavelImport(Request $request)
    {
        $escolaId = session('escola_atual') ?: auth()->user()->escola_id;

        if (!$escolaId) {
            return response()->json([
                'success' => false,
                'message' => 'Nenhuma escola selecionada para importação.'
            ], 403);
        }

        $responsaveis = $request->input('responsaveis');
        if (empty($responsaveis)) {
            return response()->json([
                'success' => false,
                'message' => 'Nenhum dado de responsável enviado.'
            ], 422);
        }

        try {
            $rules = [
                'responsaveis' => 'required|array',
                'responsaveis.*.nome' => 'required|string|max:100',
                'responsaveis.*.sobrenome' => 'required|string|max:100',
                'responsaveis.*.cpf' => [
                    'required',
                    'string',
                    'max:14',
                    \Illuminate\Validation\Rule::unique('responsaveis', 'cpf')->where(fn($q) => $q->where('escola_id', $escolaId))
                ],
                'responsaveis.*.telefone_principal' => 'required|string|max:20',
                'responsaveis.*.email' => 'nullable|email|max:100',
                'responsaveis.*.parentesco' => 'required|string|max:50',
            ];

            $messages = [
                'responsaveis.*.nome.required' => 'Linha :index: O nome é obrigatório.',
                'responsaveis.*.sobrenome.required' => 'Linha :index: O sobrenome é obrigatório.',
                'responsaveis.*.cpf.required' => 'Linha :index: O CPF é obrigatório.',
                'responsaveis.*.cpf.unique' => 'Linha :index: Este CPF já está cadastrado nesta escola.',
                'responsaveis.*.telefone_principal.required' => 'Linha :index: O telefone principal é obrigatório.',
                'responsaveis.*.parentesco.required' => 'Linha :index: O parentesco é obrigatório.',
            ];

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                $formattedErrors = [];
                foreach ($validator->errors()->toArray() as $field => $messages) {
                    // Converter responsaveis.0.nome para "Linha 1"
                    if (preg_match('/responsaveis\.(\d+)\.(.+)/', $field, $matches)) {
                        $index = $matches[1] + 1;
                        foreach ($messages as $msg) {
                            $formattedErrors[] = str_replace(':index', $index, $msg);
                        }
                    } else {
                        foreach ($messages as $msg) {
                            $formattedErrors[] = $msg;
                        }
                    }
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Existem erros de validação nos dados do arquivo.',
                    'errors' => $formattedErrors
                ], 422);
            }

            // Pré-processar datas após validação básica
            foreach ($responsaveis as $idx => &$resp) {
                if (!empty($resp['data_nascimento'])) {
                    $originalDate = $resp['data_nascimento'];
                    $converted = $this->convertDate($originalDate);

                    if (!$converted) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Existem erros de formatação de data.',
                            'errors' => ["Linha " . ($idx + 1) . ": A data '{$originalDate}' é inválida. Use o formato DD/MM/YYYY."]
                        ], 422);
                    }
                    $resp['data_nascimento'] = $converted;
                }
            }

        } catch (\Exception $e) {
            Log::error('ImportacaoController: Erro durante validação/conversão Responsável', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Erro ao processar os dados: ' . $e->getMessage()
            ], 500);
        }

        $importedCount = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($responsaveis as $index => $data) {
                try {
                    $responsavel = Responsavel::create([
                        'escola_id' => $escolaId,
                        'nome' => $data['nome'],
                        'sobrenome' => $data['sobrenome'],
                        'data_nascimento' => $data['data_nascimento'] ?? null,
                        'cpf' => $data['cpf'],
                        'rg' => $data['rg'] ?? null,
                        'telefone_principal' => $data['telefone_principal'],
                        'telefone_secundario' => $data['telefone_secundario'] ?? null,
                        'email' => $data['email'] ?? null,
                        'endereco' => $data['endereco'] ?? 'Não informado',
                        'cidade' => $data['cidade'] ?? 'Não informado',
                        'estado' => $data['estado'] ?? 'XX',
                        'cep' => $data['cep'] ?? '00000-000',
                        'parentesco' => $data['parentesco'],
                        'genero' => $data['genero'] ?? null,
                        'autorizado_buscar' => (isset($data['autorizado_buscar']) && (strtolower($data['autorizado_buscar']) == 'sim' || $data['autorizado_buscar'] == '1')),
                        'contato_emergencia' => (isset($data['contato_emergencia']) && (strtolower($data['contato_emergencia']) == 'sim' || $data['contato_emergencia'] == '1')),
                        'observacoes' => $data['observacoes'] ?? null,
                        'ativo' => true,
                    ]);

                    Historico::registrar(
                        'importado',
                        'Responsavel',
                        (int) $responsavel->id,
                        null,
                        $responsavel->toArray(),
                        'Responsável importado via CSV'
                    );

                    $importedCount++;
                } catch (\Exception $e) {
                    Log::error("Erro na linha " . ($index + 1), ['message' => $e->getMessage()]);
                    $errors[] = "Linha " . ($index + 1) . " ({$data['nome']}): " . $e->getMessage();
                }
            }

            if (count($errors) > 0 && $importedCount === 0) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Nenhum responsável foi importado devido a erros.',
                    'errors' => $errors
                ], 422);
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => "{$importedCount} responsáveis importados com sucesso!",
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::critical('Erro fatal na importação de responsáveis: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro interno ao processar importação: ' . $e->getMessage()
            ], 500);
        }
    }

    public function despesaPreview(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt'
        ]);

        $file = $request->file('file');
        $data = [];

        if (($handle = fopen($file->getRealPath(), "r")) !== FALSE) {
            $bom = fread($handle, 3);
            if ($bom !== "\xEF\xBB\xBF") {
                rewind($handle);
            }

            $headers = fgetcsv($handle, 1000, ";");
            if ($headers) {
                $headers = array_map(function ($h) {
                    return strtolower(trim(str_replace(['"', "'"], '', $h)));
                }, $headers);

                while (($row = fgetcsv($handle, 1000, ";")) !== FALSE) {
                    if (count($headers) == count($row)) {
                        $data[] = array_combine($headers, $row);
                    }
                }
            }
            fclose($handle);
        }

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function despesaImport(Request $request)
    {
        $escolaId = session('escola_atual') ?: auth()->user()->escola_id;

        if (!$escolaId) {
            return response()->json([
                'success' => false,
                'message' => 'Nenhuma escola selecionada para importação.'
            ], 403);
        }

        $despesas = $request->input('despesas');
        if (empty($despesas)) {
            return response()->json([
                'success' => false,
                'message' => 'Nenhum dado de despesa enviado.'
            ], 422);
        }

        try {
            $rules = [
                'despesas' => 'required|array',
                'despesas.*.descricao' => 'required|string|max:255',
                'despesas.*.valor' => 'required|numeric|min:0',
                'despesas.*.data' => 'required|string',
                'despesas.*.is_recorrente' => 'nullable|string',
                'despesas.*.frequencia' => 'required_if:despesas.*.is_recorrente,Sim|nullable|in:semanal,mensal,anual',
            ];

            $messages = [
                'despesas.*.descricao.required' => 'Linha :index: A descrição é obrigatória.',
                'despesas.*.valor.required' => 'Linha :index: O valor é obrigatório.',
                'despesas.*.data.required' => 'Linha :index: A data é obrigatória.',
                'despesas.*.frequencia.required_if' => 'Linha :index: A frequência é obrigatória para despesas recorrentes.',
            ];

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                $formattedErrors = [];
                foreach ($validator->errors()->toArray() as $field => $messages) {
                    if (preg_match('/despesas\.(\d+)\.(.+)/', $field, $matches)) {
                        $index = $matches[1] + 1;
                        foreach ($messages as $msg) {
                            $formattedErrors[] = str_replace(':index', $index, $msg);
                        }
                    } else {
                        foreach ($messages as $msg) {
                            $formattedErrors[] = $msg;
                        }
                    }
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Existem erros de validação nos dados do arquivo.',
                    'errors' => $formattedErrors
                ], 422);
            }

            // Pré-processar datas
            foreach ($despesas as $idx => &$despesa) {
                $originalDate = $despesa['data'];
                $converted = $this->convertDate($originalDate);
                if (!$converted) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Existem erros de formatação de data.',
                        'errors' => ["Linha " . ($idx + 1) . ": A data '{$originalDate}' é inválida. Use o formato DD/MM/YYYY."]
                    ], 422);
                }
                $despesa['data'] = $converted;

                if (!empty($despesa['data_fim'])) {
                    $dateFim = $this->convertDate($despesa['data_fim']);
                    if (!$dateFim) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Existem erros de formatação na data de término.',
                            'errors' => ["Linha " . ($idx + 1) . ": A data de término '{$despesa['data_fim']}' é inválida."]
                        ], 422);
                    }
                    $despesa['data_fim'] = $dateFim;
                }
            }

        } catch (\Exception $e) {
            Log::error('ImportacaoController: Erro durante validação/conversão Despesa', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Erro ao processar os dados: ' . $e->getMessage()
            ], 500);
        }

        $importedCount = 0;
        $recurrentCount = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($despesas as $index => $data) {
                try {
                    $isRecorrente = (isset($data['is_recorrente']) && strtolower($data['is_recorrente']) == 'sim');

                    $recorrenciaId = null;
                    if ($isRecorrente) {
                        $frequencia = $data['frequencia'];
                        $atual = \Carbon\Carbon::parse($data['data']);
                        $proxima = match ($frequencia) {
                            'semanal' => $atual->copy()->addWeek(),
                            'mensal' => $atual->copy()->addMonthNoOverflow()->startOfMonth(),
                            'anual' => $atual->copy()->addYear(),
                            default => $atual->copy()->addMonthNoOverflow()->startOfMonth(),
                        };

                        $recorrencia = RecorrenciaDespesa::create([
                            'escola_id' => $escolaId,
                            'descricao' => $data['descricao'],
                            'categoria' => $data['categoria'] ?? null,
                            'valor' => $data['valor'],
                            'frequencia' => $frequencia,
                            'data_inicio' => $data['data'],
                            'data_fim' => $data['data_fim'] ?? null,
                            'proxima_geracao' => $proxima,
                            'ativo' => true,
                        ]);
                        $recorrenciaId = $recorrencia->id;
                        $recurrentCount++;

                        // Desativar se a próxima já passar do fim
                        if (!empty($data['data_fim']) && $proxima->gt($data['data_fim'])) {
                            $recorrencia->update(['ativo' => false]);
                        }
                    }

                    $despesa = Despesa::create([
                        'escola_id' => $escolaId,
                        'recorrencia_id' => $recorrenciaId,
                        'descricao' => $data['descricao'],
                        'categoria' => $data['categoria'] ?? null,
                        'valor' => $data['valor'],
                        'data' => $data['data'],
                        'status' => 'pendente',
                    ]);

                    Historico::registrar(
                        'importado',
                        'Despesa',
                        (int) $despesa->id,
                        null,
                        $despesa->toArray(),
                        'Despesa importada via CSV' . ($isRecorrente ? ' (com recorrência)' : '')
                    );

                    $importedCount++;
                } catch (\Exception $e) {
                    Log::error("Erro na linha " . ($index + 1), ['message' => $e->getMessage()]);
                    $errors[] = "Linha " . ($index + 1) . " ({$data['descricao']}): " . $e->getMessage();
                }
            }

            if (count($errors) > 0 && $importedCount === 0) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Nenhuma despesa foi importada devido a erros.',
                    'errors' => $errors
                ], 422);
            }

            DB::commit();
            $msg = "{$importedCount} despesas importadas com sucesso!";
            if ($recurrentCount > 0)
                $msg .= " ({$recurrentCount} recorrências configuradas)";

            return response()->json([
                'success' => true,
                'message' => $msg,
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::critical('Erro fatal na importação de despesas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro interno ao processar importação: ' . $e->getMessage()
            ], 500);
        }
    }
}
