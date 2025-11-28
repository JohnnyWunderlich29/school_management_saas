<?php

return [
    // Permite controlar a exibição do badge de contexto (SuperAdmin/Suporte)
    'show_context_badge' => env('ONBOARDING_SHOW_CONTEXT_BADGE', true),

    'steps' => [
        [
            'slug' => 'dados-da-escola',
            'label' => 'Dados da escola',
            'description' => 'CNPJ, endereço, contatos e identidade visual.',
            'route' => 'profile.escola',
            'required' => true,
        ],
        [
            'slug' => 'ano-letivo',
            'label' => 'Ano letivo',
            'description' => 'Defina o período e calendário escolar.',
            // Ajuste conforme sua tela real de calendário escolar
            // Exemplo temporário: configurações gerais
            'route' => 'settings.index',
            'required' => true,
        ],
        [
            'slug' => 'series-e-turmas',
            'label' => 'Séries e turmas',
            'description' => 'Cadastre séries, turmas e suas capacidades.',
            'route' => 'turmas.index',
            'required' => true,
        ],
        [
            'slug' => 'disciplinas',
            'label' => 'Disciplinas',
            'description' => 'Organize as disciplinas e suas cargas horárias.',
            'route' => 'disciplinas.index',
            'required' => true,
        ],
        [
            'slug' => 'usuarios-e-permissoes',
            'label' => 'Usuários e permissões',
            'description' => 'Convide gestores, professores e defina perfis de acesso.',
            'route' => 'usuarios.index',
            'required' => true,
        ],
        [
            'slug' => 'horarios',
            'label' => 'Horários',
            'description' => 'Monte a grade de aulas e horários.',
            'route' => 'grade-aulas.index',
            'required' => false,
        ],
        [
            'slug' => 'integracoes',
            'label' => 'Integrações',
            'description' => 'Configure e-mails, mensageria e serviços externos (opcional).',
            // Ajuste quando houver tela específica de integrações
            'route' => 'settings.index',
            'required' => false,
        ],
    ],
];