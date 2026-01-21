<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$cats = DB::table('templates_bncc')->select('categoria', 'subcategoria')->distinct()->get();
foreach ($cats as $c) {
    echo "Cat: {$c->categoria} | Sub: {$c->subcategoria}\n";
}
