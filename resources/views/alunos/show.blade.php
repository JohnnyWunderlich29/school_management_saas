@extends('layouts.app')

@section('content')
    <div class="w-full mx-auto">
        <x-card>
            <div class="flex flex-col justify-between items-center mb-6 md:flex-row">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Detalhes do Aluno</h2>
                    <div class="flex flex-wrap items-center gap-2 mt-2">
                        @if ($aluno->sala)
                            <a href="{{ route('salas.show', $aluno->sala) }}"
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 hover:bg-green-200 transition">
                                <i class="fas fa-door-open mr-1"></i>{{ $aluno->sala->nome_completo ?? $aluno->sala->nome }}
                                <i class="fas fa-external-link-alt ml-1"></i>
                            </a>
                        @endif
                        @if ($aluno->matricula)
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                <i class="fas fa-id-card mr-1"></i>{{ $aluno->matricula }}
                            </span>
                        @endif
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                            <i
                                class="fas fa-calendar mr-1"></i>{{ $aluno->data_nascimento ? \Carbon\Carbon::parse($aluno->data_nascimento)->age : 'N/A' }}
                            anos
                        </span>
                    </div>
                </div>
                <div class="flex mt-6 pt-4 border-t flex flex-col w-full sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-3">
                    <x-button href="{{ route('alunos.index') }}" color="secondary" class="w-full sm:w-auto">
                        <i class="fas fa-arrow-left mr-1"></i> <span class="hidden sm:inline">Voltar</span><span
                            class="sm:hidden">Voltar</span>
                    </x-button>
                    <x-button href="{{ route('alunos.edit', $aluno->id) }}" color="warning" class="w-full sm:w-auto">
                        <i class="fas fa-edit mr-1"></i> <span class="hidden sm:inline">Editar Aluno</span><span
                            class="sm:hidden">Editar</span>
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
                            <p class="text-gray-900">{{ $aluno->nome }} {{ $aluno->sobrenome }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600">Sala Atual</label>
                            @if ($aluno->sala)
                                <p class="text-gray-900">
                                    <a href="{{ route('salas.show', $aluno->sala) }}"
                                        class="text-indigo-600 hover:text-indigo-800">
                                        <i
                                            class="fas fa-door-open mr-1"></i>{{ $aluno->sala->nome_completo ?? $aluno->sala->nome }}
                                    </a>
                                </p>
                            @else
                                <p class="text-gray-900">Não vinculado a uma sala</p>
                            @endif
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600">CPF</label>
                            <p class="text-gray-900">{{ $aluno->cpf ?? 'Não informado' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600">RG</label>
                            <p class="text-gray-900">{{ $aluno->rg ?? 'Não informado' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600">Data de Nascimento</label>
                            <p class="text-gray-900">
                                {{ $aluno->data_nascimento ? $aluno->data_nascimento->format('d/m/Y') : 'Não informado' }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600">Gênero</label>
                            <p class="text-gray-900">{{ $aluno->genero ?? 'Não informado' }}</p>
                        </div>
                        @if ($aluno->matricula)
                            <div>
                                <label class="block text-sm font-medium text-gray-600">Matrícula</label>
                                <p class="text-gray-900">{{ $aluno->matricula }}</p>
                            </div>
                        @endif
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
                            <p class="text-gray-900">{{ $aluno->telefone ?? 'Não informado' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600">E-mail</label>
                            <p class="text-gray-900 break-all">{{ $aluno->email ?? 'Não informado' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600">Endereço</label>
                            <p class="text-gray-900">{{ $aluno->endereco ?? 'Não informado' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600">Cidade/Estado</label>
                            <p class="text-gray-900">
                                {{ $aluno->cidade ?? 'Não informado' }}{{ $aluno->estado ? '/' . $aluno->estado : '' }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600">CEP</label>
                            <p class="text-gray-900">{{ $aluno->cep ?? 'Não informado' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Informações Médicas -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">
                        <i class="fas fa-heartbeat mr-2"></i>Informações Médicas
                    </h3>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-600">Tipo Sanguíneo</label>
                            <p class="text-gray-900">{{ $aluno->tipo_sanguineo ?? 'Não informado' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600">Alergias</label>
                            <p class="text-gray-900">{{ $aluno->alergias ?? 'Nenhuma alergia informada' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600">Medicamentos</label>
                            <p class="text-gray-900">{{ $aluno->medicamentos ?? 'Nenhum medicamento informado' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Responsáveis -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">
                        <i class="fas fa-users mr-2"></i>Responsáveis
                    </h3>
                    <div class="space-y-3">
                        @if ($responsavelPrincipal)
                            <div>
                                <label class="block text-sm font-medium text-gray-600">Responsável Principal</label>
                                <p class="text-gray-900">{{ $responsavelPrincipal->nome }}
                                    {{ $responsavelPrincipal->sobrenome }}</p>
                                @if ($responsavelPrincipal->telefone_principal)
                                    <p class="text-sm text-gray-600">{{ $responsavelPrincipal->telefone_principal }}</p>
                                @endif
                            </div>
                        @endif

                        @foreach ($aluno->responsaveis as $responsavel)
                            @if (!$responsavel->pivot->responsavel_principal)
                                <div>
                                    <label class="block text-sm font-medium text-gray-600">Responsável</label>
                                    <p class="text-gray-900">{{ $responsavel->nome }} {{ $responsavel->sobrenome }}</p>
                                    @if ($responsavel->telefone_principal)
                                        <p class="text-sm text-gray-600">{{ $responsavel->telefone_principal }}</p>
                                    @endif
                                </div>
                            @endif
                        @endforeach

                        @if ($aluno->responsaveis->count() == 0)
                            <p class="text-gray-900">Nenhum responsável cadastrado</p>
                        @endif
                    </div>
                </div>

                <!-- Documentos -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">
                        <i class="fas fa-file-alt mr-2"></i>Documentos
                    </h3>
                    <div class="space-y-3">
                        @if ($aluno->documentos && $aluno->documentos->count() > 0)
                            @foreach ($aluno->documentos as $documento)
                                <div
                                    class="flex items-center flex-wrap gap-2 justify-between p-3 bg-white rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors">
                                    <div class="flex items-center space-x-3">
                                        <div class="flex-shrink-0">
                                            @if (in_array($documento->tipo_mime, ['application/pdf']))
                                                <i class="fas fa-file-pdf text-red-500 text-lg"></i>
                                            @elseif(in_array($documento->tipo_mime, [
                                                    'application/msword',
                                                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                                ]))
                                                <i class="fas fa-file-word text-blue-500 text-lg"></i>
                                            @elseif(in_array($documento->tipo_mime, ['image/jpeg', 'image/jpg', 'image/png']))
                                                <i class="fas fa-file-image text-green-500 text-lg"></i>
                                            @else
                                                <i class="fas fa-file text-gray-500 text-lg"></i>
                                            @endif
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900 truncate">
                                                {{ $documento->nome_original }}</p>
                                            <p class="text-xs text-gray-500">{{ $documento->tamanho_formatado }} •
                                                {{ $documento->created_at->format('d/m/Y H:i') }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ $documento->url }}" target="_blank"
                                            class="inline-flex items-center px-2 py-1 text-xs font-medium text-blue-600 bg-blue-100 rounded hover:bg-blue-200 transition-colors">
                                            <i class="fas fa-eye mr-1"></i>
                                            Ver
                                        </a>
                                        <a href="{{ $documento->url }}" download="{{ $documento->nome_original }}"
                                            class="inline-flex items-center px-2 py-1 text-xs font-medium text-green-600 bg-green-100 rounded hover:bg-green-200 transition-colors">
                                            <i class="fas fa-download mr-1"></i>
                                            Download
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center py-6">
                                <i class="fas fa-file-alt text-gray-300 text-3xl mb-2"></i>
                                <p class="text-gray-500 text-sm">Nenhum documento anexado</p>
                            </div>
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
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $aluno->ativo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                <i class="fas {{ $aluno->ativo ? 'fa-check' : 'fa-times' }} mr-1"></i>
                                {{ $aluno->ativo ? 'Ativo' : 'Inativo' }}
                            </span>
                        </div>
                        @if ($aluno->observacoes)
                            <div>
                                <label class="block text-sm font-medium text-gray-600">Observações</label>
                                <p class="text-gray-900">{{ $aluno->observacoes }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </x-card>
    </div>

    </div>
@endsection
