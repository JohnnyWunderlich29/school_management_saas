@extends('layouts.app')

@section('content')
<div class="w-full mx-auto">
    <x-card>
        <div class="flex flex-col justify-between items-center mb-6 md:flex-row">
            <h2 class="text-2xl font-bold text-gray-900">Detalhes do Funcionário</h2>
            <div class="flex mt-6 pt-4 border-t flex flex-col w-full sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-3">
                <x-button href="{{ route('funcionarios.index') }}" color="secondary" class="w-full sm:w-auto">
                    <i class="fas fa-arrow-left mr-1"></i>
                    <span class="hidden sm:inline">Voltar</span><span class="sm:hidden">Voltar</span>
                </x-button>
                <x-button href="{{ route('funcionarios.edit', $funcionario->id) }}" color="warning" class="w-full sm:w-auto">
                    <i class="fas fa-edit mr-1"></i>
                    <span class="hidden sm:inline">Editar Funcionário</span><span class="sm:hidden">Editar</span>
                </x-button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Informações Pessoais -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                    <i class="fas fa-user mr-2"></i>Informações Pessoais
                </h3>
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Nome Completo</label>
                        <p class="text-gray-900">{{ $funcionario->nome }} {{ $funcionario->sobrenome }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">CPF</label>
                        <p class="text-gray-900">{{ $funcionario->cpf ?? 'Não informado' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">RG</label>
                        <p class="text-gray-900">{{ $funcionario->rg ?? 'Não informado' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Data de Nascimento</label>
                        <p class="text-gray-900">{{ $funcionario->data_nascimento ? $funcionario->data_nascimento->format('d/m/Y') : 'Não informado' }}</p>
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
                        <label class="block text-sm font-medium text-gray-600">Telefone</label>
                        <p class="text-gray-900">{{ $funcionario->telefone ?? 'Não informado' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">E-mail</label>
                        <p class="text-gray-900">{{ $funcionario->email ?? 'Não informado' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Endereço</label>
                        <p class="text-gray-900">{{ $funcionario->endereco ?? 'Não informado' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Cidade/Estado</label>
                        <p class="text-gray-900">{{ $funcionario->cidade ?? 'Não informado' }}{{ $funcionario->estado ? '/' . $funcionario->estado : '' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">CEP</label>
                        <p class="text-gray-900">{{ $funcionario->cep ?? 'Não informado' }}</p>
                    </div>
                </div>
            </div>

            <!-- Informações Profissionais -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                    <i class="fas fa-briefcase mr-2"></i>Informações Profissionais
                </h3>
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Cargo</label>
                        <p class="text-gray-900">{{ $funcionario->cargo ?? 'Não informado' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Departamento</label>
                        <p class="text-gray-900">{{ $funcionario->departamento ?? 'Não informado' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Data de Contratação</label>
                        <p class="text-gray-900">{{ $funcionario->data_contratacao ? $funcionario->data_contratacao->format('d/m/Y') : 'Não informado' }}</p>
                    </div>
                    @if($funcionario->data_demissao)
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Data de Demissão</label>
                        <p class="text-gray-900">{{ $funcionario->data_demissao->format('d/m/Y') }}</p>
                    </div>
                    @endif
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Salário</label>
                        <p class="text-gray-900">{{ $funcionario->salario ? 'R$ ' . number_format($funcionario->salario, 2, ',', '.') : 'Não informado' }}</p>
                    </div>
                </div>
            </div>

            <!-- Disciplinas -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                    <i class="fas fa-book mr-2"></i>Disciplinas
                </h3>
                <div class="space-y-3">
                    @if($funcionario->disciplinas->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                            @foreach($funcionario->disciplinas as $disciplina)
                                <div class="bg-white p-2 rounded border flex items-center">
                                    @if($disciplina->cor_hex)
                                        <div class="w-4 h-4 rounded mr-2" style="background-color: {{ $disciplina->cor_hex }}"></div>
                                    @endif
                                    <span>{{ $disciplina->nome }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-900">Nenhuma disciplina associada</p>
                    @endif
                </div>
            </div>

            <!-- Status e Observações -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                    <i class="fas fa-info-circle mr-2"></i>Status e Observações
                </h3>
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Status</label>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $funcionario->ativo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            <i class="fas {{ $funcionario->ativo ? 'fa-check' : 'fa-times' }} mr-1"></i>
                            {{ $funcionario->ativo ? 'Ativo' : 'Inativo' }}
                        </span>
                    </div>
                    @if($funcionario->observacoes)
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Observações</label>
                        <p class="text-gray-900">{{ $funcionario->observacoes }}</p>
                    </div>
                    @endif
                    @if($funcionario->user)
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Acesso ao Sistema</label>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            <i class="fas fa-user-check mr-1"></i>
                            Possui acesso
                        </span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </x-card>
</div>
@endsection