@extends('layouts.app')

@section('content')
<breadcrumb :items="[
    ['label' => 'Início', 'url' => route('dashboard.index')],
    ['label' => 'Responsáveis', 'url' => route('responsaveis.index')],
    ['label' => 'Detalhes do Responsável'],
]"></breadcrumb>
<div class="grid grid-cols-1 md:grid-cols-7 gap-4">
    <div class="container md:col-span-2 gap-4 mx-auto w-full px-2">
        <x-card class="flex flex-col">
            <!-- Header responsivo -->
            <div class="flex flex-col justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl md:text-2xl font-bold text-gray-900">Detalhes do Responsável</h1>
                </div>
                <!-- Botões desktop -->
                <div class="flex flex-col w-full mt-6 pt-4 border-t md:flex-row">
                    <div class="flex flex-col sm:flex-row gap-3">
                        <x-button href="{{ route('responsaveis.index') }}" color="secondary" class="w-full sm:w-auto">
                            <i class="fas fa-arrow-left mr-2"></i> Voltar
                        </x-button>
                        <x-button href="{{ route('responsaveis.edit', $responsavel->id) }}" color="primary" class="w-full sm:w-auto">
                            <i class="fas fa-edit mr-2"></i> Editar
                        </x-button>
                    </div>
                </div>
            </div>

            <!-- Layout Desktop -->
            <div class="hidden md:block">
                <div class="grid grid-cols-1 gap-2 flex flex-col">
                    <!-- Informações Pessoais -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">
                            <i class="fas fa-user mr-2"></i>Informações Pessoais
                        </h3>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-600">Nome Completo</label>
                                <p class="text-gray-900">{{ $responsavel->nome }} {{ $responsavel->sobrenome }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600">Data de Nascimento</label>
                                <p class="text-gray-900">
                                    {{ $responsavel->data_nascimento ? $responsavel->data_nascimento->format('d/m/Y') : 'Não informado' }}
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600">CPF</label>
                                <p class="text-gray-900">{{ $responsavel->cpf ?? 'Não informado' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600">RG</label>
                                <p class="text-gray-900">{{ $responsavel->rg ?? 'Não informado' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600">Gênero</label>
                                <p class="text-gray-900">{{ $responsavel->genero ?? 'Não informado' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Informações de Contato -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">
                            <i class="fas fa-phone mr-2"></i>Contato
                        </h3>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-600">Telefone Principal</label>
                                <p class="text-gray-900">{{ $responsavel->telefone_principal ?? 'Não informado' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600">Telefone Secundário</label>
                                <p class="text-gray-900">{{ $responsavel->telefone_secundario ?? 'Não informado' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600">E-mail</label>
                                <p class="text-gray-900">{{ $responsavel->email ?? 'Não informado' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600">Endereço</label>
                                <p class="text-gray-900">{{ $responsavel->endereco ?? 'Não informado' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600">Cidade/Estado</label>
                                <p class="text-gray-900">
                                    {{ $responsavel->cidade ?? 'Não informado' }}{{ $responsavel->estado ? '/' . $responsavel->estado : '' }}
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600">CEP</label>
                                <p class="text-gray-900">{{ $responsavel->cep ?? 'Não informado' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Alunos Vinculados -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">
                            <i class="fas fa-user-graduate mr-2"></i>Alunos Vinculados
                        </h3>
                        <div class="space-y-3">
                            @if ($responsavel->alunos->count() > 0)
                                @foreach ($responsavel->alunos as $aluno)
                                    <div class="flex items-center justify-between p-3 bg-white rounded border">
                                        <div>
                                            <p class="font-medium text-gray-900">{{ $aluno->nome }}
                                                {{ $aluno->sobrenome }}</p>
                                            @if ($aluno->pivot->responsavel_principal)
                                                <span
                                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    <i class="fas fa-star mr-1"></i>
                                                    Responsável Principal
                                                </span>
                                            @endif
                                        </div>
                                        <x-button href="{{ route('alunos.show', $aluno->id) }}" color="primary"
                                            size="sm">
                                            <i class="fas fa-eye"></i>
                                        </x-button>
                                    </div>
                                @endforeach
                            @else
                                <p class="text-gray-500 italic">Nenhum aluno vinculado</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Layout Mobile com Cards -->
            <div class="md:hidden space-y-4">
                <!-- Card Informações Pessoais -->
                <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                    <div class="flex items-center mb-3">
                        <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-500 mr-3">
                            <i class="fas fa-user text-lg"></i>
                        </div>
                        <h3 class="text-mobile-title text-gray-900">Informações Pessoais</h3>
                    </div>
                    <div class="space-y-3">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <span class="text-gray-500 block text-mobile-body">Nome:</span>
                                <span class="font-medium text-mobile-body">{{ $responsavel->nome }}
                                    {{ $responsavel->sobrenome }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500 block text-mobile-body">Nascimento:</span>
                                <span
                                    class="font-medium text-mobile-body">{{ $responsavel->data_nascimento ? $responsavel->data_nascimento->format('d/m/Y') : 'Não informado' }}</span>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <span class="text-gray-500 block text-mobile-body">CPF:</span>
                                <span
                                    class="font-medium text-mobile-body">{{ $responsavel->cpf ?? 'Não informado' }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500 block text-mobile-body">RG:</span>
                                <span class="font-medium text-mobile-body">{{ $responsavel->rg ?? 'Não informado' }}</span>
                            </div>
                        </div>
                        <div>
                            <span class="text-gray-500 block text-mobile-body">Gênero:</span>
                            <span class="font-medium text-mobile-body">{{ $responsavel->genero ?? 'Não informado' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Card Informações de Contato -->
                <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                    <div class="flex items-center mb-3">
                        <div
                            class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center text-green-500 mr-3">
                            <i class="fas fa-phone text-lg"></i>
                        </div>
                        <h3 class="text-mobile-title text-gray-900">Contato</h3>
                    </div>
                    <div class="space-y-3">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <span class="text-gray-500 block text-mobile-body">Tel. Principal:</span>
                                <span
                                    class="font-medium text-mobile-body">{{ $responsavel->telefone_principal ?? 'Não informado' }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500 block text-mobile-body">Tel. Secundário:</span>
                                <span
                                    class="font-medium text-mobile-body">{{ $responsavel->telefone_secundario ?? 'Não informado' }}</span>
                            </div>
                        </div>
                        <div>
                            <span class="text-gray-500 block text-mobile-body">E-mail:</span>
                            <span class="font-medium text-mobile-body">{{ $responsavel->email ?? 'Não informado' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500 block text-mobile-body">Endereço:</span>
                            <span
                                class="font-medium text-mobile-body">{{ $responsavel->endereco ?? 'Não informado' }}</span>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <span class="text-gray-500 block text-mobile-body">Cidade/Estado:</span>
                                <span
                                    class="font-medium text-mobile-body">{{ $responsavel->cidade ?? 'Não informado' }}{{ $responsavel->estado ? '/' . $responsavel->estado : '' }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500 block text-mobile-body">CEP:</span>
                                <span
                                    class="font-medium text-mobile-body">{{ $responsavel->cep ?? 'Não informado' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card Informações Profissionais -->
                <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                    <div class="flex items-center mb-3">
                        <div
                            class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center text-purple-500 mr-3">
                            <i class="fas fa-briefcase text-lg"></i>
                        </div>
                        <h3 class="text-mobile-title text-gray-900">Informações Profissionais</h3>
                    </div>
                    <div class="space-y-3">
                        <div>
                            <span class="text-gray-500 block text-mobile-body">Profissão:</span>
                            <span
                                class="font-medium text-mobile-body">{{ $responsavel->profissao ?? 'Não informado' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500 block text-mobile-body">Local de Trabalho:</span>
                            <span
                                class="font-medium text-mobile-body">{{ $responsavel->local_trabalho ?? 'Não informado' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500 block text-mobile-body">Tel. do Trabalho:</span>
                            <span
                                class="font-medium text-mobile-body">{{ $responsavel->telefone_trabalho ?? 'Não informado' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Card Alunos Vinculados -->
                <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                    <div class="flex items-center mb-3">
                        <div
                            class="w-10 h-10 rounded-full bg-orange-100 flex items-center justify-center text-orange-500 mr-3">
                            <i class="fas fa-user-graduate text-lg"></i>
                        </div>
                        <h3 class="text-mobile-title text-gray-900">Alunos Vinculados</h3>
                    </div>
                    <div class="space-y-3">
                        @if ($responsavel->alunos->count() > 0)
                            @foreach ($responsavel->alunos as $aluno)
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded border">
                                    <div class="flex-1">
                                        <p class="font-medium text-mobile-body text-gray-900">{{ $aluno->nome }}
                                            {{ $aluno->sobrenome }}</p>
                                        @if ($aluno->pivot->responsavel_principal)
                                            <span
                                                class="inline-flex items-center px-2 py-1 rounded-full text-mobile-badge bg-blue-100 text-blue-800 mt-1">
                                                <i class="fas fa-star mr-1"></i>
                                                Principal
                                            </span>
                                        @endif
                                    </div>
                                    <a href="{{ route('alunos.show', $aluno->id) }}"
                                        class="bg-blue-600 hover:bg-blue-800 text-white font-bold py-2 px-3 rounded text-mobile-button flex items-center">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center py-4 text-gray-500">
                                <i class="fas fa-user-graduate text-2xl mb-2"></i>
                                <p class="text-mobile-body">Nenhum aluno vinculado</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Status e Observações Desktop -->
            <div class="hidden md:block mt-6 bg-gray-50 p-4 rounded-lg">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                    <i class="fas fa-info-circle mr-2"></i>Status e Observações
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Status</label>
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $responsavel->ativo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            <i class="fas {{ $responsavel->ativo ? 'fa-check' : 'fa-times' }} mr-1"></i>
                            {{ $responsavel->ativo ? 'Ativo' : 'Inativo' }}
                        </span>
                    </div>
                    @if ($responsavel->observacoes)
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-600">Observações</label>
                            <p class="text-gray-900">{{ $responsavel->observacoes }}</p>
                        </div>
                    @endif
                </div>
            </div>

                <!-- Card Status e Observações Mobile -->
                <div class="md:hidden mt-4">
                    <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                        <div class="flex items-center mb-3">
                            <div
                                class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 mr-3">
                                <i class="fas fa-info-circle text-lg"></i>
                            </div>
                            <h3 class="text-mobile-title text-gray-900">Status e Observações</h3>
                        </div>
                        <div class="space-y-3">
                        <div>
                            <span class="text-gray-500 block text-mobile-body">Status:</span>
                            <span
                                class="inline-flex items-center px-2 py-1 rounded-full text-mobile-badge {{ $responsavel->ativo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                <i class="fas {{ $responsavel->ativo ? 'fa-check' : 'fa-times' }} mr-1"></i>
                                {{ $responsavel->ativo ? 'Ativo' : 'Inativo' }}
                            </span>
                        </div>
                        @if ($responsavel->observacoes)
                            <div>
                                <span class="text-gray-500 block text-mobile-body">Observações:</span>
                                <p class="text-mobile-body text-gray-900 mt-1">{{ $responsavel->observacoes }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </x-card>
    </div>
    <div class="md:col-span-5 w-full px-2">
        <div class="overflow-x-auto">
            <!-- Mensalidade e Cobranças -->
            @include('responsaveis.partials.mensalidade-cobrancas', [
                'responsavel' => $responsavel,
                'alunos' => $responsavel->alunos,
                'schoolId' => $schoolId,
                'chargeMethods' => $chargeMethods,
                'billingPlans' => $billingPlans,
            ])
        </div>
    </div>
</div>
@endsection
