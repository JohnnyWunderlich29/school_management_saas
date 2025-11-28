<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Sistema de Gestão Escolar')</title>
    <meta name="description" content="Sistema de gestão escolar completo: matrículas, grade de aulas, financeiro, comunicação e mais.">
    <meta property="og:title" content="Sistema de Gestão Escolar">
    <meta property="og:description" content="Conheça as funcionalidades e solicite uma demonstração.">
    <meta property="og:type" content="website">
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="min-h-screen bg-white text-slate-800">
    <header class="border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
            <a href="{{ route('institucional.index') }}" class="text-xl font-semibold text-sky-700">Gestão Escolar</a>
            <nav class="hidden md:flex items-center gap-6">
                <a href="{{ route('institucional.funcionalidades') }}" class="text-slate-700 hover:text-sky-700">Funcionalidades</a>
                <a href="{{ route('institucional.contato') }}" class="text-slate-700 hover:text-sky-700">Contato</a>
                <a href="{{ route('login') }}" class="inline-flex items-center rounded-md bg-sky-600 px-4 py-2 text-white hover:bg-sky-700">Entrar</a>
            </nav>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    <footer class="mt-16 border-t border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 text-sm text-slate-600">
            <div class="flex items-center justify-between">
                <p>&copy; {{ date('Y') }} Sistema de Gestão Escolar</p>
                <div class="flex items-center gap-4">
                    <a href="{{ route('institucional.funcionalidades') }}" class="hover:text-sky-700">Funcionalidades</a>
                    <a href="{{ route('institucional.contato') }}" class="hover:text-sky-700">Contato</a>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>

