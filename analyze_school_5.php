<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\ModalidadeEnsino;
use App\Models\NivelEnsino;

$escolaId = 5;
$modalidadeCodigo = 'EF1';

echo "Escola ID: $escolaId\n";
echo "Procurando Modalidade: $modalidadeCodigo\n";

$modalidade = ModalidadeEnsino::where('codigo', $modalidadeCodigo)->first();
if (!$modalidade) {
    echo "Modalidade EF1 não encontrada!\n";
    exit;
}
echo "Modalidade ID: {$modalidade->id} | Nome: {$modalidade->nome}\n";

// 1. escola_niveis_config
$niveisConfig = DB::table('escola_niveis_config')
    ->where('escola_id', $escolaId)
    ->pluck('nivel_ensino_id')
    ->toArray();

echo "Níveis configurados em escola_niveis_config: " . count($niveisConfig) . " [" . implode(',', $niveisConfig) . "]\n";

// 2. Níveis em uso por turmas com grade_aulas
$niveisEmUso = DB::table('turmas')
    ->join('grade_aulas', 'turmas.id', '=', 'grade_aulas.turma_id')
    ->where('turmas.escola_id', $escolaId)
    ->where('turmas.ativo', true)
    ->where('grade_aulas.ativo', true)
    ->whereNotNull('turmas.nivel_ensino_id')
    ->distinct()
    ->pluck('turmas.nivel_ensino_id')
    ->toArray();

echo "Níveis em uso (turmas + grade_aulas): " . count($niveisEmUso) . " [" . implode(',', $niveisEmUso) . "]\n";

// 3. Detalhes dos níveis configurados
if (!empty($niveisConfig)) {
    $niveis = NivelEnsino::whereIn('id', $niveisConfig)->get();
    echo "\nDetalhes dos níveis configurados:\n";
    foreach ($niveis as $nivel) {
        $inUse = in_array($nivel->id, $niveisEmUso) ? "SIM" : "NÃO";
        $compativeis = json_encode($nivel->modalidades_compativeis);
        $isCompat = (is_array($nivel->modalidades_compativeis) && in_array($modalidade->codigo, $nivel->modalidades_compativeis)) ? "SIM" : "NÃO";
        
        echo "ID: {$nivel->id} | Nome: {$nivel->nome} | Ativo: {$nivel->ativo} | Em Uso: $inUse | Compatível com $modalidadeCodigo: $isCompat | Mods: $compativeis\n";
    }
} else {
    echo "Nenhum nível configurado para a escola $escolaId.\n";
}

// 4. Se não há nada em uso, vamos ver se há turmas SEM grade_aulas
$turmasSemGrade = DB::table('turmas')
    ->where('escola_id', $escolaId)
    ->where('ativo', true)
    ->whereNotNull('turmas.nivel_ensino_id')
    ->distinct()
    ->pluck('turmas.nivel_ensino_id')
    ->toArray();

echo "\nNíveis vinculados a turmas ativas (com ou sem grade): [" . implode(',', $turmasSemGrade) . "]\n";
