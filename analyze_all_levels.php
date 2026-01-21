<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\NivelEnsino;
use Illuminate\Support\Facades\DB;

$niveis = NivelEnsino::all();

echo "ID | Nome | Codigo | Ativo | Modalidades Compativeis\n";
echo "------------------------------------------------------\n";
foreach ($niveis as $nivel) {
    $mods = is_array($nivel->modalidades_compativeis) ? implode(',', $nivel->modalidades_compativeis) : var_export($nivel->modalidades_compativeis, true);
    echo "{$nivel->id} | {$nivel->nome} | {$nivel->codigo} | {$nivel->ativo} | $mods\n";
}

echo "\n--- Contagem por Escola (Turmas) ---\n";
$turmasCount = DB::table('turmas')
    ->select('escola_id', DB::raw('count(*) as total'))
    ->groupBy('escola_id')
    ->get();

foreach ($turmasCount as $count) {
    echo "Escola ID: {$count->escola_id} | Total Turmas: {$count->total}\n";
}

echo "\n--- Grade Aulas Ativas por Escola ---\n";
$gradeCount = DB::table('grade_aulas')
    ->select('escola_id', DB::raw('count(*) as total'))
    ->where('ativo', true)
    ->groupBy('escola_id')
    ->get();

foreach ($gradeCount as $count) {
    echo "Escola ID: {$count->escola_id} | Total Grades Ativas: {$count->total}\n";
}
