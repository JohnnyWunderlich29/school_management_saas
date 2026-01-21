<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    DB::beginTransaction();

    $campos = DB::table('campos_experiencia')->get();
    
    $grouped = [];
    foreach ($campos as $campo) {
        $key = $campo->nome . '|' . ($campo->nivel ?? 'NULL');
        $grouped[$key][] = $campo;
    }
    
    $deletedTotal = 0;
    $updatedTotal = 0;

    foreach ($grouped as $key => $items) {
        if (count($items) > 1) {
            echo "Processing duplicates for: $key\n";
            $keep = $items[0];
            
            for ($i = 1; $i < count($items); $i++) {
                $duplicate = $items[$i];
                $dupId = $duplicate->id;
                
                // Reassign Objetivos
                $updatedObj = DB::table('objetivos_aprendizagem')
                    ->where('campo_experiencia_id', $dupId)
                    ->update(['campo_experiencia_id' => $keep->id]);
                
                // Reassign Saberes
                $updatedSab = DB::table('saberes_conhecimentos')
                    ->where('campo_experiencia_id', $dupId)
                    ->update(['campo_experiencia_id' => $keep->id]);
                
                $updatedTotal += ($updatedObj + $updatedSab);
                
                // Delete duplicate
                DB::table('campos_experiencia')->where('id', $dupId)->delete();
                $deletedTotal++;
                
                echo "  Deleted ID $dupId, kept ID {$keep->id}. (Updated relations: " . ($updatedObj + $updatedSab) . ")\n";
            }
        }
    }
    
    DB::commit();
    echo "\nCleanup completed successfully.\n";
    echo "Total duplicates deleted: $deletedTotal\n";
    echo "Total related records reassigned: $updatedTotal\n";

} catch (\Exception $e) {
    DB::rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
}
