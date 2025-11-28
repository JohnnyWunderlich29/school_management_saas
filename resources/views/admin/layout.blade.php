<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="user-data" content="{{ json_encode([
        'id' => auth()->user()->id ?? null,
        'name' => auth()->user()->name ?? null,
        'email' => auth()->user()->email ?? null,
        'escola_id' => auth()->user()->escola_id ?? null,
        'is_super_admin' => auth()->user()->is_super_admin ?? false,
        'has_suporte' => auth()->user()->hasRole('suporte') ?? false,
        'session_escola' => session('escola_id'),
        'current_url' => request()->url()
    ]) }}">
    <title>@yield('title', 'Painel Administrativo') - {{ config('app.name') }}</title>
    
    <!-- Tailwind CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
</head>
<body class="bg-gray-100 font-sans antialiased">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <div class="w-64 bg-gray-900 text-white flex flex-col">
            <!-- Logo -->
            <div class="p-6 border-b border-gray-700">
                <h1 class="text-xl font-bold text-center">
                    <span class="text-blue-400">Admin</span> Panel
                </h1>
            </div>
            
            <!-- Navigation -->
            <nav class="flex-1 p-4">
                <ul class="space-y-2">
                    <li>
                        <a href="{{ route('corporativo.dashboard') }}" 
                           class="flex items-center px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors {{ request()->routeIs('corporativo.dashboard') ? 'bg-gray-700' : '' }}">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v6H8V5z"></path>
                            </svg>
                            Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('corporativo.users') }}" 
                           class="flex items-center px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors {{ request()->routeIs('corporativo.users') ? 'bg-gray-700' : '' }}">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                            </svg>
                            Usuários
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('corporativo.permissions') }}" 
                           class="flex items-center px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors {{ request()->routeIs('corporativo.permissions') ? 'bg-gray-700' : '' }}">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                            Permissões
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('corporativo.escolas') }}" 
                           class="flex items-center px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors {{ request()->routeIs('corporativo.escolas') || request()->routeIs('corporativo.escola.*') ? 'bg-gray-700' : '' }}">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            Escolas
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('corporativo.relatorios') }}" 
                           class="flex items-center px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors {{ request()->routeIs('corporativo.relatorios') ? 'bg-gray-700' : '' }}">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            Relatórios
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('corporativo.query.builder') }}" 
                           class="flex items-center px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors {{ request()->routeIs('corporativo.query.*') ? 'bg-gray-700' : '' }}">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            Query Builder
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('corporativo.system.info') }}" 
                           class="flex items-center px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors {{ request()->routeIs('corporativo.system.*') ? 'bg-gray-700' : '' }}">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Info do Sistema
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.database.relationships') }}" 
                           class="flex items-center px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors {{ request()->routeIs('admin.database.*') ? 'bg-gray-700' : '' }}">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                            Relacionamentos DB
                        </a>
                    </li>
                </ul>
            </nav>
            
            <!-- User Info -->
            <div class="p-4 border-t border-gray-700">
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                        <span class="text-sm font-medium">{{ substr(auth()->user()->name, 0, 1) }}</span>
                    </div>
                    <div class="ml-3 flex-1">
                        <p class="text-sm font-medium">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-gray-400">{{ auth()->user()->email }}</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('corporativo.logout') }}" class="mt-3">
                    @csrf
                    <button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-300 hover:text-white hover:bg-gray-700 rounded-lg transition-colors">
                        Sair
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="flex-1 flex flex-col">
            <!-- Header -->
            <header class="bg-white shadow-sm border-b border-gray-200">
                <div class="px-6 py-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-2xl font-semibold text-gray-800">
                            @yield('page-title', 'Dashboard')
                        </h2>
                        @if(Auth::user()->escolas)
                        <div class="flex items-center space-x-2 px-3 py-2 bg-blue-50 border border-blue-200 rounded-lg">
                            <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                            <span class="text-sm font-medium text-blue-700">{{ Auth::user()->escolas->pluck('nome')->implode(', ') }}</span>
                        </div>
                        @endif
                    </div>
                    @if(isset($breadcrumbs))
                        <nav class="mt-2">
                            <ol class="flex items-center space-x-2 text-sm text-gray-500">
                                @foreach($breadcrumbs as $breadcrumb)
                                    @if(!$loop->last)
                                        <li>
                                            <a href="{{ $breadcrumb['url'] }}" class="hover:text-gray-700">{{ $breadcrumb['title'] }}</a>
                                        </li>
                                        <li>
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                        </li>
                                    @else
                                        <li class="text-gray-900 font-medium">{{ $breadcrumb['title'] }}</li>
                                    @endif
                                @endforeach
                            </ol>
                        </nav>
                    @endif
                </div>
            </header>
            
            <!-- Content -->
            <main class="flex-1 p-6">
                @if(session('success'))
                    <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                        {{ session('success') }}
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                        {{ session('error') }}
                    </div>
                @endif
                
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
