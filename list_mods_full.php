<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ModalidadeEnsino;

$mods = ModalidadeEnsino::take(50)->get();
foreach ($mods as $m) {
    echo "ID: {$m->id} | Nome: {$m->nome} | Codigo: {$m->codigo} | Nivel: " . ($m->nivel ?? 'NULL') . "\n";
}
