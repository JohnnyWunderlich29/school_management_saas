<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Feature Toggles
    |--------------------------------------------------------------------------
    |
    | Este arquivo controla quais módulos estão disponíveis globalmente.
    | Cada feature pode ser ativada/desativada via variáveis de ambiente.
    |
    */

    'modules' => [
        'comunicacao_module' => env('FEATURE_COMUNICACAO_MODULE', true),
        'alunos_module' => env('FEATURE_ALUNOS_MODULE', true),
        'funcionarios_module' => env('FEATURE_FUNCIONARIOS_MODULE', true),
        'academico_module' => env('FEATURE_ACADEMICO_MODULE', true),
        'administracao_module' => env('FEATURE_ADMINISTRACAO_MODULE', true),
        'relatorios_module' => env('FEATURE_RELATORIOS_MODULE', true),
        'financeiro_module' => env('FEATURE_FINANCEIRO_MODULE', true),
        'biblioteca_module' => env('FEATURE_BIBLIOTECA_MODULE', true),
        'transporte_module' => env('FEATURE_TRANSPORTE_MODULE', true),
        'eventos_module' => env('FEATURE_EVENTOS_MODULE', true),
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Configurações Avançadas
    |--------------------------------------------------------------------------
    */
    
    'license_check_enabled' => env('LICENSE_CHECK_ENABLED', true),
    'default_license_duration' => env('DEFAULT_LICENSE_DURATION', 365), // dias
    
    // Ambiente financeiro atual (homolog|production)
    'finance_env' => env('FINANCE_ENV', 'production'),
];