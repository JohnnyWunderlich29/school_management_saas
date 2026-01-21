<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    // Fetch all campos to group them in PHP to avoid SQL grouping issues with nulls if any
    $campos = DB::table('campos_experiencia')->get();
    
    $grouped = [];
    foreach ($campos as $campo) {
        $key = $campo->nome . '|' . ($campo->nivel ?? 'NULL');
        $grouped[$key][] = $campo;
    }
    
    $duplicatesFound = 0;
    foreach ($grouped as $key => $items) {
        if (count($items) > 1) {
            $duplicatesFound++;
            echo "Duplicates for: $key\n";
            $keep = $items[0]; // Logic: keep the first one
            echo "  KEEP ID: {$keep->id}\n";
            
            $deleteIds = [];
            for ($i = 1; $i < count($items); $i++) {
                $deleteIds[] = $items[$i]->id;
            }
            echo "  DELETE IDs: " . implode(', ', $deleteIds) . "\n";
            
            // Re-check related records
            foreach ($deleteIds as $id) {
                $objCount = DB::table('objetivos_aprendizagem')->where('campo_experiencia_id', $id)->count();
                $sabCount = DB::table('saberes_conhecimentos')->where('campo_experiencia_id', $id)->count();
                if ($objCount > 0 || $sabCount > 0) {
                    echo "    WARNING: ID $id has related records! (Obj: $objCount, Sab: $sabCount)\n";
                    echo "    Recommendation: UPDATE related records to use Keep ID {$keep->id} before deleting.\n";
                }
            }
        }
    }
    
    if ($duplicatesFound === 0) {
        echo "No duplicates found.\n";
    }

} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
