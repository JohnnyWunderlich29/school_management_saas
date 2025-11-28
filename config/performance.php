<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Configurações de cache para melhorar a performance do sistema
    |
    */
    'cache' => [
        // Cache de contadores do dashboard (em minutos)
        'dashboard_stats_ttl' => 5,
        
        // Cache de listas de seleção (em minutos)
        'select_lists_ttl' => 30,
        
        // Cache de permissões (em minutos)
        'permissions_ttl' => 60,
        
        // Cache de salas ativas (em minutos)
        'active_rooms_ttl' => 15,
        
        // Cache de funcionários ativos (em minutos)
        'active_employees_ttl' => 30,
    ],

    /*
    |--------------------------------------------------------------------------
    | Query Optimization
    |--------------------------------------------------------------------------
    |
    | Configurações para otimização de queries
    |
    */
    'query' => [
        // Número máximo de registros por página
        'max_per_page' => 50,
        
        // Número padrão de registros por página
        'default_per_page' => 15,
        
        // Limite de registros para queries sem paginação
        'max_records_without_pagination' => 1000,
        
        // Timeout para queries longas (em segundos)
        'long_query_timeout' => 30,
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Optimization
    |--------------------------------------------------------------------------
    |
    | Configurações para otimização do banco de dados
    |
    */
    'database' => [
        // Usar índices compostos para queries frequentes
        'use_composite_indexes' => true,
        
        // Usar eager loading por padrão
        'default_eager_loading' => true,
        
        // Limitar campos selecionados por padrão
        'limit_selected_fields' => true,
        
        // Usar soft deletes com índices
        'optimize_soft_deletes' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | View Optimization
    |--------------------------------------------------------------------------
    |
    | Configurações para otimização das views
    |
    */
    'view' => [
        // Usar cache de views compiladas
        'cache_compiled_views' => true,
        
        // Comprimir output HTML
        'compress_html' => false,
        
        // Lazy loading de imagens
        'lazy_load_images' => true,
        
        // Minificar CSS e JS em produção
        'minify_assets' => env('APP_ENV') === 'production',
    ],

    /*
    |--------------------------------------------------------------------------
    | Memory Optimization
    |--------------------------------------------------------------------------
    |
    | Configurações para otimização de memória
    |
    */
    'memory' => [
        // Limite de memória para processamento em lote
        'batch_processing_limit' => '256M',
        
        // Usar chunking para grandes datasets
        'use_chunking' => true,
        
        // Tamanho do chunk para processamento
        'chunk_size' => 1000,
        
        // Limpar cache automaticamente
        'auto_clear_cache' => true,
    ],
];