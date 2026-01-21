<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TemplateBncc;

$templates = TemplateBncc::take(10)->get();
foreach ($templates as $t) {
    echo "ID: {$t->id} | Nome: {$t->nome} | Categoria: {$t->categoria} | Sub: {$t->subcategoria} | Mods: " . json_encode($t->modalidades_compativeis) . "\n";
}
