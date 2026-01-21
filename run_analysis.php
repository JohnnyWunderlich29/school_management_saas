<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- DATAS ---" . PHP_EOL;
$c25 = App\Models\CampoExperiencia::find(25);
if ($c25) {
    echo "Campo 25 | Nome: {$c25->nome} | Nivel: '" . ($c25->nivel ?? 'NULL') . "'" . PHP_EOL;
} else {
    echo "Campo 25 not found" . PHP_EOL;
}

$p53 = App\Models\Planejamento::with('nivelEnsino')->find(53);
if ($p53) {
    $mods = $p53->nivelEnsino->modalidades_compativeis ?? [];
    echo "Plan 53 | Nivel: " . ($p53->nivelEnsino ? $p53->nivelEnsino->nome : 'NULL') . " | Mods: " . json_encode($mods) . PHP_EOL;
    
    $query = App\Models\CampoExperiencia::ativos();
    $query->porModalidade($mods);
    echo "Filtered SQL: " . $query->toSql() . PHP_EOL;
    echo "Filtered Bindings: " . json_encode($query->getBindings()) . PHP_EOL;
    echo "Filtered Count: " . $query->count() . PHP_EOL;
    
    $ids = $query->pluck('id')->toArray();
    echo "Is 25 in results? " . (in_array(25, $ids) ? 'YES' : 'NO') . PHP_EOL;
} else {
    echo "Plan 53 not found" . PHP_EOL;
}
