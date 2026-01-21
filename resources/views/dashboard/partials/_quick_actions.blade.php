<x-card class="mb-6 md:mb-8">
    <x-slot name="title">
        <div class="flex items-center">
            <i class="fas fa-bolt text-indigo-600 mr-2"></i>
            Ações Rápidas
        </div>
    </x-slot>

    <x-slot name="subtitle">
        Acesso rápido às principais funcionalidades do sistema
    </x-slot>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        <!-- Configuração Educacional -->
        @php
            $escolaId = session('escola_atual') ?: auth()->user()->escola_id;
        @endphp
        @if ($escolaId)
            <a href="{{ route('admin.configuracao-educacional.show', ['escola' => $escolaId]) }}"
                title="{{ auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte') ? 'Configuração da escola selecionada (ID: ' . $escolaId . ')' : 'Configuração da sua escola' }}"
            @else <a href="#"
                onclick="alert('{{ auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte') ? 'Selecione uma escola no seletor acima para acessar as configurações' : 'Usuário não possui escola associada' }}')"
                title="Nenhuma escola selecionada" @endif
                class="group flex items-center p-4 bg-gradient-to-r from-indigo-50 to-purple-50 border border-indigo-200 rounded-lg hover:from-indigo-100 hover:to-purple-100 hover:border-indigo-300 transition-all duration-200 hover:shadow-md">
                <div
                    class="flex-shrink-0 w-10 h-10 bg-indigo-500 rounded-lg flex items-center justify-center group-hover:bg-indigo-600 transition-colors">
                    <i class="fas fa-cogs text-white text-lg"></i>
                </div>
                <div class="ml-4 min-w-0 flex-1">
                    <p class="text-sm font-semibold text-gray-900 group-hover:text-indigo-700 transition-colors">
                        Configuração Educacional
                    </p>
                    <p class="text-xs text-gray-600 group-hover:text-indigo-600 transition-colors">
                        Modalidades e níveis de ensino
                    </p>
                </div>
                <div class="flex-shrink-0 ml-2">
                    <i
                        class="fas fa-arrow-right text-gray-400 group-hover:text-indigo-500 group-hover:translate-x-1 transition-all"></i>
                </div>
            </a>

            <!-- Placeholder para futuras ações rápidas -->
            <div class="flex items-center p-4 bg-gray-50 border border-gray-200 rounded-lg opacity-50">
                <div class="flex-shrink-0 w-10 h-10 bg-gray-300 rounded-lg flex items-center justify-center">
                    <i class="fas fa-plus text-gray-500 text-lg"></i>
                </div>
                <div class="ml-4 min-w-0 flex-1">
                    <p class="text-sm font-medium text-gray-500">
                        Mais ações em breve
                    </p>
                    <p class="text-xs text-gray-400">
                        Funcionalidades adicionais
                    </p>
                </div>
            </div>
    </div>
</x-card>
