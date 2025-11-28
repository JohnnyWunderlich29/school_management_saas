@extends('corporativo.layout')

@section('title', 'Detalhes da Escola - ' . $escola->nome)
@section('page-title', 'Detalhes da Escola - ' . $escola->nome)

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <nav class="flex" aria-label="Breadcrumb">
                        <ol class="flex items-center space-x-4">
                            <li>
                                <a href="{{ route('corporativo.escolas') }}" class="text-gray-400 hover:text-gray-500">
                                    <span class="sr-only">Escolas</span>
                                    Escolas
                                </a>
                            </li>
                            <li>
                                <div class="flex items-center">
                                    <svg class="flex-shrink-0 h-5 w-5 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                    </svg>
                                    <span class="ml-4 text-sm font-medium text-gray-500">{{ $escola->nome }}</span>
                                </div>
                            </li>
                        </ol>
                    </nav>
                    <h1 class="text-3xl font-bold text-gray-900 mt-2">{{ $escola->nome }}</h1>
                    <p class="mt-2 text-gray-600">{{ $escola->razao_social }}</p>
                </div>
                <div class="flex space-x-3">
                    <button onclick="editEscola()" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                        Editar Escola
                    </button>
                    <button onclick="toggleStatus()" class="bg-{{ $escola->ativo ? 'red' : 'green' }}-600 hover:bg-{{ $escola->ativo ? 'red' : 'green' }}-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                        {{ $escola->ativo ? 'Desativar' : 'Ativar' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Status Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-purple-100 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Usuários</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $escola->users_count }}</p>
                        <p class="text-xs text-gray-500">Limite: {{ $escola->max_usuarios }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Funcionários</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $escola->funcionarios_count }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Salas</p>
                        @php
                            $salasCount = \App\Models\Sala::where('escola_id', $escola->id)->count();
                        @endphp
                        <p class="text-2xl font-bold text-gray-900">{{ $salasCount }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-{{ $escola->em_dia ? 'green' : 'red' }}-100 rounded-lg">
                        <svg class="w-6 h-6 text-{{ $escola->em_dia ? 'green' : 'red' }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Status Pagamento</p>
                        <p class="text-lg font-bold text-{{ $escola->em_dia ? 'green' : 'red' }}-600">{{ $escola->em_dia ? 'Em dia' : 'Inadimplente' }}</p>
                        @if($escola->valor_mensalidade)
                        <p class="text-xs text-gray-500">R$ {{ number_format($escola->valor_mensalidade, 2, ',', '.') }}/mês</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Informações Básicas -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Informações da Escola</h3>
                    </div>
                    <div class="p-6">
                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Nome</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $escola->nome }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">CNPJ</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $escola->cnpj }}</dd>
                            </div>
                            <div class="md:col-span-2">
                                <dt class="text-sm font-medium text-gray-500">Razão Social</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $escola->razao_social }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Email</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $escola->email ?: 'Não informado' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Telefone</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $escola->telefone ?: 'Não informado' }}</dd>
                            </div>
                            @if($escola->getEnderecoCompleto())
                            <div class="md:col-span-2">
                                <dt class="text-sm font-medium text-gray-500">Endereço</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $escola->getEnderecoCompleto() }}</dd>
                            </div>
                            @endif
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Plano</dt>
                                <dd class="mt-1">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                        @if($escola->plano === 'enterprise') bg-purple-100 text-purple-800
                                        @elseif($escola->plano === 'premium') bg-blue-100 text-blue-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        {{ ucfirst($escola->plano) }}
                                    </span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Status</dt>
                                <dd class="mt-1">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                        @if($escola->ativo) bg-green-100 text-green-800
                                        @else bg-red-100 text-red-800
                                        @endif">
                                        {{ $escola->ativo ? 'Ativa' : 'Inativa' }}
                                    </span>
                                </dd>
                            </div>
                            @if($escola->descricao)
                            <div class="md:col-span-2">
                                <dt class="text-sm font-medium text-gray-500">Descrição</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $escola->descricao }}</dd>
                            </div>
                            @endif
                        </dl>
                    </div>
                </div>

                <!-- Usuários Recentes -->
                <div class="bg-white rounded-lg shadow mt-6">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Usuários Recentes</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cargos</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Criado em</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($escola->users->take(5) as $user)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $user->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $user->email }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @if($user->cargos->count() > 0)
                                            {{ $user->cargos->pluck('nome')->join(', ') }}
                                        @else
                                            Sem cargo
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $user->created_at->format('d/m/Y') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">Nenhum usuário encontrado</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Ações Rápidas -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Ações Rápidas</h3>
                    <div class="space-y-3">
                        <button onclick="exportData()" class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Exportar Dados
                        </button>
                        
                        <button onclick="viewReports()" class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            Ver Relatórios
                        </button>
                        
                        <button onclick="clearCache()" class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Limpar Cache
                        </button>
                    </div>
                </div>

                <!-- Módulos Ativos -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Módulos Licenciados</h3>
                        <button onclick="manageModules()" class="text-purple-600 hover:text-purple-800 text-sm font-medium">
                            Gerenciar
                        </button>
                    </div>
                    <div class="space-y-2">
                        @php
                            $licenseService = app('App\Services\LicenseService');
                            $availableModules = $licenseService->getAvailableModules($escola);
                            $allModules = config('features.modules', []);
                        @endphp
                        
                        @foreach($allModules as $module => $enabled)
                            @php
                                $isLicensed = in_array($module, $availableModules);
                                $moduleNames = [
                                    'comunicacao' => 'Sistema de Comunicação',
                                    'relatorios' => 'Relatórios Avançados',
                                    'financeiro' => 'Módulo Financeiro',
                                    'biblioteca' => 'Sistema de Biblioteca',
                                    'transporte' => 'Controle de Transporte'
                                ];
                                $displayName = $moduleNames[$module] ?? ucfirst($module);
                            @endphp
                            <div class="flex items-center justify-between py-2 px-3 rounded-lg {{ $isLicensed ? 'bg-green-50' : 'bg-gray-50' }}">
                                <div class="flex items-center">
                                    <div class="w-3 h-3 rounded-full {{ $isLicensed ? 'bg-green-500' : 'bg-gray-300' }} mr-3"></div>
                                    <span class="text-sm font-medium {{ $isLicensed ? 'text-green-900' : 'text-gray-500' }}">{{ $displayName }}</span>
                                </div>
                                <span class="text-xs px-2 py-1 rounded-full {{ $isLicensed ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                    {{ $isLicensed ? 'Ativo' : 'Inativo' }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Informações do Sistema -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Informações do Sistema</h3>
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Criada em</dt>
                            <dd class="text-sm text-gray-900">{{ $escola->created_at->format('d/m/Y H:i') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Última atualização</dt>
                            <dd class="text-sm text-gray-900">{{ $escola->updated_at->format('d/m/Y H:i') }}</dd>
                        </div>
                        @if($escola->data_vencimento)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Vencimento</dt>
                            <dd class="text-sm text-gray-900">{{ $escola->data_vencimento->format('d/m/Y') }}</dd>
                        </div>
                        @endif
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Limite de Usuários</dt>
                            <dd class="text-sm text-gray-900">{{ $escola->max_usuarios }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Limite de Alunos</dt>
                            <dd class="text-sm text-gray-900">{{ $escola->max_alunos }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function editEscola() {
    // Redirecionar para página de edição ou abrir modal
    window.location.href = '{{ route("corporativo.escolas") }}?edit={{ $escola->id }}';
}

function toggleStatus() {
    if (confirm('Tem certeza que deseja alterar o status desta escola?')) {
        fetch(`/api/escolas/{{ $escola->id }}/toggle-status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erro ao alterar status: ' + data.message);
            }
        });
    }
}

function exportData() {
    // Implementar exportação de dados
    alert('Funcionalidade de exportação em desenvolvimento');
}

function viewReports() {
    // Redirecionar para relatórios específicos da escola
    window.location.href = '{{ route("corporativo.relatorios") }}?escola={{ $escola->id }}';
}

function clearCache() {
    if (confirm('Tem certeza que deseja limpar o cache desta escola?')) {
        fetch(`/api/escolas/{{ $escola->id }}/clear-cache`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Cache limpo com sucesso!');
            } else {
                alert('Erro ao limpar cache: ' + data.message);
            }
        });
    }
}

function manageModules() {
    // Redirecionar para página de gerenciamento de módulos ou abrir modal
    window.location.href = '{{ route("corporativo.escolas") }}?edit={{ $escola->id }}#modules';
}
</script>
@endsection