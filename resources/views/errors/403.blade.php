@extends('layouts.app')

@section('content')
    <x-breadcrumbs :items="[['title' => 'Erro 403', 'url' => '#']]" />

    <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div class="text-center">
                <div class="mx-auto h-24 w-24 text-red-500">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z">
                        </path>
                    </svg>
                </div>
                <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                    Acesso Negado
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    {{ $message ?? 'Você não tem permissão para acessar esta página.' }}
                </p>
            </div>

            <div class="mt-8 space-y-4">
                <a href="{{ route('dashboard') }}"
                    class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Voltar ao Dashboard
                </a>

                <a href="javascript:history.back()"
                    class="group relative w-full flex justify-center py-2 px-4 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Voltar à Página Anterior
                </a>
            </div>

            <div class="mt-6 text-center">
                <p class="text-xs text-gray-500">
                    Se você acredita que deveria ter acesso a esta funcionalidade, entre em contato com o administrador do
                    sistema.
                </p>

                @auth
                    <div class="mt-4">
                        <button type="button" onclick="solicitarAcesso()" id="btn-solicitar"
                            class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-500">
                            <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122" />
                            </svg>
                            Solicitar acesso a este módulo
                        </button>
                        <p id="solicitacao-msg" class="text-sm mt-2 hidden"></p>
                    </div>

                    <script>
                        function solicitarAcesso() {
                            const btn = document.getElementById('btn-solicitar');
                            const msg = document.getElementById('solicitacao-msg');
                            const originalText = btn.innerHTML;

                            // Disable button
                            btn.disabled = true;
                            btn.innerHTML =
                                '<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Enviando...';

                            fetch('{{ route('solicitar.acesso') }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                    },
                                    body: JSON.stringify({
                                        url: window.location.href
                                    })
                                })
                                .then(response => response.json())
                                .then(data => {
                                    msg.classList.remove('hidden');
                                    if (data.success) {
                                        msg.textContent = 'Solicitação enviada com sucesso! O administrador foi notificado.';
                                        msg.className = 'text-sm mt-2 text-green-600 font-medium';
                                        btn.innerHTML =
                                            '<svg class="h-4 w-4 mr-1 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg> Solicitação Enviada';
                                    } else {
                                        msg.textContent = data.message || 'Erro ao enviar solicitação.';
                                        msg.className = 'text-sm mt-2 text-red-600 font-medium';
                                        btn.disabled = false;
                                        btn.innerHTML = originalText;
                                    }
                                })
                                .catch(error => {
                                    console.error('Erro:', error);
                                    msg.classList.remove('hidden');
                                    msg.textContent = 'Erro de conexão. Tente novamente.';
                                    msg.className = 'text-sm mt-2 text-red-600 font-medium';
                                    btn.disabled = false;
                                    btn.innerHTML = originalText;
                                });
                        }
                    </script>
                @endauth
            </div>
        </div>
    </div>
@endsection
