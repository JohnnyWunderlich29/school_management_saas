@extends('layouts.app')

@section('content')
<x-breadcrumbs :items="[
    ['title' => 'Transferências', 'url' => route('transferencias.index')],
    ['title' => $transferencia->aluno->nome, 'url' => '#']
]" />
<x-card>
    <!-- Header Responsivo -->
    <div class="flex flex-col space-y-4 mb-6 md:flex-row md:justify-between md:items-center md:space-y-0">
        <div class="flex-1">
            <h1 class="text-lg md:text-2xl font-bold text-gray-900">Transferência #{{ $transferencia->id }}</h1>
            <p class="mt-1 text-sm text-gray-600">Detalhes da solicitação de transferência</p>
        </div>
        <div class="flex-shrink-0 flex items-center gap-2">
            <x-button href="{{ route('transferencias.index') }}" color="secondary" class="w-full md:w-auto">
                <i class="fas fa-arrow-left mr-1"></i> 
                <span class="hidden md:inline">Voltar</span>
                <span class="md:hidden">Voltar</span>
            </x-button>
            @if($transferencia->status === 'pendente')
                <x-button href="{{ route('transferencias.show-aprovar', $transferencia->id) }}" color="primary" class="bg-green-600 hover:bg-green-700 text-white hidden md:inline">
                    <i class="fas fa-check mr-1"></i>
                    Aprovar
                </x-button>
                <x-button href="{{ route('transferencias.show-rejeitar', $transferencia->id) }}" color="primary" class="bg-red-600 hover:bg-red-700 text-white hidden md:inline">
                    <i class="fas fa-times mr-1"></i>
                    Rejeitar
                </x-button>
                <x-button type="button" color="secondary" variant="outline" class="border-red-600 text-red-700 hover:bg-red-50 hidden md:inline" onclick="excluirTransferencia({{ $transferencia->id }})">
                    <i class="fas fa-trash mr-1"></i>
                    Excluir
                </x-button>
            @endif
        </div>
    </div>

    <!-- Layout Desktop - Card único com Informações + Detalhes -->
    <div class="hidden md:block">
        <x-card title="Informações e Detalhes">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Informações do Aluno -->
                <div class="space-y-4">
                    <div class="flex items-center">
                        <div class="w-12 h-12 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-500 mr-4">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <div>
                            <div class="font-medium text-gray-900">{{ $transferencia->aluno->nome_completo }}</div>
                            <div class="text-sm text-gray-500">{{ $transferencia->aluno->cpf }}</div>
                        </div>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-500">Data de Nascimento:</span>
                        <p class="text-gray-900">{{ $transferencia->aluno->data_nascimento->format('d/m/Y') }}</p>
                    </div>
                    @php
                        $responsavelPrincipal = $transferencia->aluno->responsavelPrincipal();
                    @endphp
                    @if($responsavelPrincipal)
                        <div>
                            <span class="text-sm font-medium text-gray-500">Responsável:</span>
                            <p class="text-gray-900">{{ $responsavelPrincipal->nome_completo }}</p>
                        </div>
                    @endif
                </div>

                <!-- Detalhes da Transferência -->
                <div class="space-y-4">
                    <div>
                        <span class="text-sm font-medium text-gray-500">Status:</span>
                        <div class="mt-1">
                            @if($transferencia->status === 'pendente')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Pendente</span>
                            @elseif($transferencia->status === 'aprovada')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Aprovada</span>
                            @elseif($transferencia->status === 'rejeitada')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Rejeitada</span>
                            @elseif($transferencia->status === 'cancelada')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Cancelada</span>
                            @endif
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <span class="text-sm font-medium text-gray-500">Solicitação:</span>
                            <p class="text-gray-900">{{ $transferencia->data_solicitacao->format('d/m/Y H:i') }}</p>
                        </div>
                        @if($transferencia->data_aprovacao)
                        <div>
                            <span class="text-sm font-medium text-gray-500">{{ $transferencia->status === 'aprovada' ? 'Aprovação' : 'Rejeição' }}:</span>
                            <p class="text-gray-900">{{ $transferencia->data_aprovacao->format('d/m/Y H:i') }}</p>
                        </div>
                        @endif
                        <div>
                            <span class="text-sm font-medium text-gray-500">Solicitante:</span>
                            <p class="text-gray-900">{{ $transferencia->solicitante->name }}</p>
                        </div>
                        @if($transferencia->aprovador)
                        <div>
                            <span class="text-sm font-medium text-gray-500">{{ $transferencia->status === 'aprovada' ? 'Aprovado por' : 'Rejeitado por' }}:</span>
                            <p class="text-gray-900">{{ $transferencia->aprovador->name }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Motivo da Transferência (reposicionado dentro do card principal) -->
            <div class="mt-6">
                <div class="flex items-start">
                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 mr-3 mt-1">
                        <i class="fas fa-comment"></i>
                    </div>
                    <div class="flex-1">
                        <div class="text-sm font-medium text-gray-500 mb-1">Motivo da Transferência</div>
                        <p class="text-gray-700 leading-relaxed">{{ $transferencia->motivo ?: 'Não informado' }}</p>
                    </div>
                </div>
            </div>
        </x-card>
    </div>

    <div class="hidden md:block mt-6">
        <x-card title="Turmas (Origem → Destino)">
            <div class="grid grid-cols-7 gap-6 items-start">
                <!-- Origem -->
                <div class="col-span-3">
                    <div class="flex items-center mb-3">
                        <div class="w-10 h-10 rounded-full bg-yellow-100 flex items-center justify-center text-yellow-600 mr-3">
                            <i class="fas fa-door-open"></i>
                        </div>
                        <div>
                            <div class="font-medium text-gray-900">Turma de Origem</div>
                            <div class="text-xs text-gray-500">Dados da turma atual</div>
                        </div>
                    </div>

                    @if($transferencia->turmaOrigem)
                        <div class="space-y-3">
                            <div>
                                <div class="font-medium text-gray-900">{{ $transferencia->turmaOrigem->codigo }}</div>
                                <div class="text-sm text-gray-500">{{ $transferencia->turmaOrigem->nome }}</div>
                                @php
                                    $anoOrigem = $transferencia->turmaOrigem->ano_letivo ?? null;
                                    $anoOrigemFmt = null;
                                    if (!empty($anoOrigem)) {
                                        if (preg_match('/^\\d{4}$/', (string) $anoOrigem)) {
                                            $anoOrigemFmt = $anoOrigem . '/' . ((int) $anoOrigem + 1);
                                        } else {
                                            $anoOrigemFmt = $anoOrigem;
                                        }
                                    }
                                @endphp
                                @if(!empty($transferencia->turmaOrigem->turno) || !empty($anoOrigemFmt))
                                    <div class="text-xs text-gray-500 mt-1">
                                        @if(!empty($transferencia->turmaOrigem->turno))
                                            Turno: {{ $transferencia->turmaOrigem->turno->nome }}
                                        @endif
                                        @if(!empty($anoOrigemFmt))
                                            <span class="mx-1">—</span>
                                            Ano letivo: {{ $anoOrigemFmt }}
                                        @endif
                                    </div>
                                @endif
                            </div>
                            <div class="grid grid-cols-3 gap-4 text-center">
                                <div class="bg-gray-50 p-3 rounded-lg">
                                    <div class="text-lg font-semibold text-gray-900">{{ $transferencia->turmaOrigem->capacidade }}</div>
                                    <div class="text-xs text-gray-500">Capacidade</div>
                                </div>
                                <div class="bg-blue-50 p-3 rounded-lg">
                                    <div class="text-lg font-semibold text-blue-600">{{ $transferencia->turmaOrigem->alunos()->count() }}</div>
                                    <div class="text-xs text-gray-500">Ocupação</div>
                                </div>
                                <div class="bg-green-50 p-3 rounded-lg">
                                    <div class="text-lg font-semibold text-green-600">{{ $transferencia->turmaOrigem->capacidade - $transferencia->turmaOrigem->alunos()->count() }}</div>
                                    <div class="text-xs text-gray-500">Disponíveis</div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center text-gray-400 mx-auto mb-3">
                                <i class="fas fa-ban"></i>
                            </div>
                            <p class="text-gray-500">Sem turma de origem</p>
                        </div>
                    @endif
                </div>

                <!-- Arrow -->
                <div class="col-span-1 flex items-center justify-center">
                    <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center">
                        <i class="fas fa-arrow-right text-blue-600"></i>
                    </div>
                </div>

                <!-- Destino -->
                <div class="col-span-3">
                    <div class="flex items-center mb-3">
                        <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center text-green-600 mr-3">
                            <i class="fas fa-door-closed"></i>
                        </div>
                        <div>
                            <div class="font-medium text-gray-900">Turma de Destino</div>
                            <div class="text-xs text-gray-500">Dados da turma de destino</div>
                        </div>
                    </div>

                    @if($transferencia->turmaDestino)
                        <div class="space-y-3">
                            <div>
                                <div class="font-medium text-gray-900">{{ $transferencia->turmaDestino->codigo }}</div>
                                <div class="text-sm text-gray-500">{{ $transferencia->turmaDestino->nome }}</div>
                                @php
                                    $anoDestino = $transferencia->turmaDestino->ano_letivo ?? null;
                                    $anoDestinoFmt = null;
                                    if (!empty($anoDestino)) {
                                        if (preg_match('/^\\d{4}$/', (string) $anoDestino)) {
                                            $anoDestinoFmt = $anoDestino . '/' . ((int) $anoDestino + 1);
                                        } else {
                                            $anoDestinoFmt = $anoDestino;
                                        }
                                    }
                                @endphp
                                @if(!empty($transferencia->turmaDestino->turno) || !empty($anoDestinoFmt))
                                    <div class="text-xs text-gray-500 mt-1">
                                        @if(!empty($transferencia->turmaDestino->turno))
                                            Turno: {{ $transferencia->turmaDestino->turno->nome }}
                                        @endif
                                        @if(!empty($anoDestinoFmt))
                                            <span class="mx-1">—</span>
                                            Ano letivo: {{ $anoDestinoFmt }}
                                        @endif
                                    </div>
                                @endif
                            </div>
                            <div class="grid grid-cols-3 gap-4 text-center">
                                <div class="bg-gray-50 p-3 rounded-lg">
                                    <div class="text-lg font-semibold text-gray-900">{{ $transferencia->turmaDestino->capacidade }}</div>
                                    <div class="text-xs text-gray-500">Capacidade</div>
                                </div>
                                <div class="bg-blue-50 p-3 rounded-lg">
                                    <div class="text-lg font-semibold text-blue-600">{{ $transferencia->turmaDestino->alunos()->count() }}</div>
                                    <div class="text-xs text-gray-500">Ocupação</div>
                                </div>
                                <div class="bg-green-50 p-3 rounded-lg">
                                    @php
                                        $vagasDisponiveis = $transferencia->turmaDestino->capacidade - $transferencia->turmaDestino->alunos()->count();
                                    @endphp
                                    <div class="text-lg font-semibold {{ $vagasDisponiveis > 0 ? 'text-green-600' : 'text-red-600' }}">{{ $vagasDisponiveis }}</div>
                                    <div class="text-xs text-gray-500">Disponíveis</div>
                                </div>
                            </div>

                            @if($vagasDisponiveis <= 0)
                                @php
                                    $ocupacaoDestino = $transferencia->turmaDestino->alunos()->count();
                                    $capacidadeDestino = $transferencia->turmaDestino->capacidade;
                                @endphp
                                <div class="bg-red-50 border border-red-300 rounded-md p-3">
                                    <div class="flex items-start">
                                        <i class="fas fa-circle-exclamation text-red-600 mr-2 mt-0.5"></i>
                                        <div>
                                            <div class="text-sm font-semibold text-red-800">Sem vagas disponíveis</div>
                                            <div class="text-xs text-red-700">Ocupação {{ $ocupacaoDestino }}/{{ $capacidadeDestino }} — ajuste necessário para aprovar a transferência.</div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="text-center py-8">
                            <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center text-gray-400 mx-auto mb-3">
                                <i class="fas fa-ban"></i>
                            </div>
                            <p class="text-gray-500">Sem turma de destino</p>
                        </div>
                    @endif
                </div>
            </div>
        </x-card>
    </div>
</x-card>
    <!-- Observações do Aprovador (se houver) -->
    @if($transferencia->observacoes_aprovador)
    <div class="hidden md:block mt-6">
        <x-card title="Observações do Aprovador">
            <div class="flex items-start">
                <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center text-purple-600 mr-3 mt-1">
                    <i class="fas fa-sticky-note"></i>
                </div>
                <div class="flex-1">
                    <p class="text-gray-700 leading-relaxed">{{ $transferencia->observacoes_aprovador }}</p>
                </div>
            </div>
        </x-card>
    </div>
    @endif

    <!-- Ações Desktop -->
    <!-- Ações movidas para o cabeçalho quando pendente -->

    <!-- Layout Mobile com Cards -->
    <div class="md:hidden mt-6 space-y-4">
        <!-- Card único: Status + Informações do Aluno (Mobile) -->
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
            <div class="flex items-center mb-3">
                <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-500 mr-3">
                    <i class="fas fa-user-graduate text-lg"></i>
                </div>
                <h3 class="text-base font-semibold text-gray-900">Informações e Detalhes</h3>
                <div class="ml-auto">
                    @if($transferencia->status === 'pendente')
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Pendente</span>
                    @elseif($transferencia->status === 'aprovada')
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">Aprovada</span>
                    @elseif($transferencia->status === 'rejeitada')
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">Rejeitada</span>
                    @elseif($transferencia->status === 'cancelada')
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Cancelada</span>
                    @endif
                </div>
            </div>
            <div class="space-y-4">
                <div>
                    <span class="text-gray-500 block text-sm">Nome:</span>
                    <span class="text-gray-900 text-sm font-medium">{{ $transferencia->aluno->nome_completo }}</span>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <span class="text-gray-500 block text-sm">CPF:</span>
                        <span class="text-gray-900 text-sm font-medium">{{ $transferencia->aluno->cpf }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 block text-sm">Nascimento:</span>
                        <span class="text-gray-900 text-sm font-medium">{{ $transferencia->aluno->data_nascimento->format('d/m/Y') }}</span>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <span class="text-gray-500 block text-sm">Solicitação:</span>
                        <span class="text-gray-900 text-sm font-medium">{{ $transferencia->data_solicitacao->format('d/m/Y H:i') }}</span>
                    </div>
                    @if($transferencia->data_aprovacao)
                    <div>
                        <span class="text-gray-500 block text-sm">{{ $transferencia->status === 'aprovada' ? 'Aprovação' : 'Rejeição' }}:</span>
                        <span class="text-gray-900 text-sm font-medium">{{ $transferencia->data_aprovacao->format('d/m/Y H:i') }}</span>
                    </div>
                    @endif
                </div>
                <div>
                    <span class="text-gray-500 block text-sm">Solicitante:</span>
                    <span class="text-gray-900 text-sm font-medium">{{ $transferencia->solicitante->name }}</span>
                </div>
                @php
                    $responsavelPrincipal = $transferencia->aluno->responsavelPrincipal();
                @endphp
                @if($responsavelPrincipal)
                <div>
                    <span class="text-gray-500 block text-sm">Responsável:</span>
                    <span class="text-gray-900 text-sm font-medium">{{ $responsavelPrincipal->nome_completo }}</span>
                </div>
                @endif

                <!-- Motivo (mobile) -->
                <div class="hidden md:inline bg-gray-50 rounded p-3">
                    <div class="text-xs text-gray-600 mb-1">Motivo da Transferência</div>
                    <div class="text-sm text-gray-900">{{ $transferencia->motivo ?: 'Não informado' }}</div>
                </div>
            </div>
        </div>

        <!-- Card Turmas (Mobile) -->
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
            <div class="flex items-center mb-3">
                <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center text-green-500 mr-3">
                    <i class="fas fa-door-open text-lg"></i>
                </div>
                <h3 class="text-base font-semibold text-gray-900">Transferência de Turmas</h3>
            </div>

            <div class="flex flex-col items-center gap-4 items-start">
                <!-- Origem -->
                <div class="col-span-2">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-gray-500 text-sm font-medium">Origem</span>
                        <div class="w-6 h-6 rounded-full bg-yellow-100 flex items-center justify-center">
                            <i class="fas fa-door-open text-yellow-600 text-xs"></i>
                        </div>
                    </div>
                    @if($transferencia->turmaOrigem)
                        <div class="bg-gray-50 rounded-lg p-3">
                            <div class="font-medium text-gray-900 text-sm">{{ $transferencia->turmaOrigem->codigo }}</div>
                            <div class="text-xs text-gray-500 mb-2">{{ $transferencia->turmaOrigem->nome }}</div>
                            <div class="grid grid-cols-3 gap-2 text-center">
                                <div class="bg-white p-2 rounded">
                                    <div class="text-xs font-semibold text-gray-900">{{ $transferencia->turmaOrigem->capacidade }}</div>
                                    <div class="text-[10px] text-gray-500">Capacidade</div>
                                </div>
                                <div class="bg-blue-50 p-2 rounded">
                                    <div class="text-xs font-semibold text-blue-600">{{ $transferencia->turmaOrigem->alunos()->count() }}</div>
                                    <div class="text-[10px] text-gray-500">Ocupação</div>
                                </div>
                                <div class="bg-green-50 p-2 rounded">
                                    <div class="text-xs font-semibold text-green-600">{{ $transferencia->turmaOrigem->capacidade - $transferencia->turmaOrigem->alunos()->count() }}</div>
                                    <div class="text-[10px] text-gray-500">Disponíveis</div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="bg-gray-50 rounded-lg p-3 text-center">
                            <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-400 mx-auto mb-2">
                                <i class="fas fa-ban text-sm"></i>
                            </div>
                            <p class="text-gray-500 text-xs">Sem turma de origem</p>
                        </div>
                    @endif
                </div>

                <!-- Arrow -->
                <div class="col-span-1 flex items-center justify-center">
                    <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                        <i class="fas fa-arrow-right text-blue-600"></i>
                    </div>
                </div>

                <!-- Destino -->
                <div class="col-span-2">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-gray-500 text-sm font-medium">Destino</span>
                        <div class="w-6 h-6 rounded-full bg-green-100 flex items-center justify-center">
                            <i class="fas fa-door-closed text-green-600 text-xs"></i>
                        </div>
                    </div>
                    @if($transferencia->turmaDestino)
                        <div class="bg-gray-50 rounded-lg p-3">
                            <div class="font-medium text-gray-900 text-sm">{{ $transferencia->turmaDestino->codigo }}</div>
                            <div class="text-xs text-gray-500 mb-2">{{ $transferencia->turmaDestino->nome }}</div>
                            <div class="grid grid-cols-3 gap-2 text-center">
                                <div class="bg-white p-2 rounded">
                                    <div class="text-xs font-semibold text-gray-900">{{ $transferencia->turmaDestino->capacidade }}</div>
                                    <div class="text-[10px] text-gray-500">Capacidade</div>
                                </div>
                                <div class="bg-blue-50 p-2 rounded">
                                    <div class="text-xs font-semibold text-blue-600">{{ $transferencia->turmaDestino->alunos()->count() }}</div>
                                    <div class="text-[10px] text-gray-500">Ocupação</div>
                                </div>
                                <div class="bg-green-50 p-2 rounded">
                                    @php
                                        $vagasDisponiveis = $transferencia->turmaDestino->capacidade - $transferencia->turmaDestino->alunos()->count();
                                    @endphp
                                    <div class="text-xs font-semibold {{ $vagasDisponiveis > 0 ? 'text-green-600' : 'text-red-600' }}">{{ $vagasDisponiveis }}</div>
                                    <div class="text-[10px] text-gray-500">Disponíveis</div>
                                </div>
                            </div>

                            @if($vagasDisponiveis <= 0)
                                <div class="bg-red-50 border border-red-200 rounded-md p-2 mt-2">
                                    <div class="flex items-center">
                                        <i class="fas fa-exclamation-triangle text-red-500 mr-2 text-xs"></i>
                                        <span class="text-xs font-medium text-red-800">Turma lotada!</span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="bg-gray-50 rounded-lg p-3 text-center">
                            <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-400 mx-auto mb-2">
                                <i class="fas fa-ban text-sm"></i>
                            </div>
                            <p class="text-gray-500 text-xs">Sem turma de destino</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Card Motivo -->
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
            <div class="flex items-center mb-3">
                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-500 mr-3">
                    <i class="fas fa-comment text-lg"></i>
                </div>
                <h3 class="text-base font-semibold text-gray-900">Motivo da Transferência</h3>
            </div>
            <div class="bg-gray-50 rounded-lg p-3">
                <p class="text-gray-700 text-sm leading-relaxed">{{ $transferencia->motivo ?: 'Não informado' }}</p>
            </div>
        </div>

        @if($transferencia->observacoes_aprovador)
        <!-- Card Observações do Aprovador -->
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
            <div class="flex items-center mb-3">
                <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center text-purple-500 mr-3">
                    <i class="fas fa-sticky-note text-lg"></i>
                </div>
                <h3 class="text-base font-semibold text-gray-900">Observações do Aprovador</h3>
            </div>
            <div class="bg-gray-50 rounded-lg p-3">
                <p class="text-gray-700 text-sm leading-relaxed">{{ $transferencia->observacoes_aprovador }}</p>
            </div>
        </div>
        @endif

        <!-- Ações Mobile -->
        @if($transferencia->status === 'pendente')
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
            <div class="flex items-center mb-3">
                <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 mr-3">
                    <i class="fas fa-cogs text-lg"></i>
                </div>
                <h3 class="text-base font-semibold text-gray-900">Ações</h3>
            </div>
            <div class="space-y-3">
                <a href="{{ route('transferencias.show-aprovar', $transferencia->id) }}" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-lg text-sm flex items-center justify-center">
                    <i class="fas fa-check mr-2"></i>
                    Aprovar Transferência
                </a>
                <a href="{{ route('transferencias.show-rejeitar', $transferencia->id) }}" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-4 rounded-lg text-sm flex items-center justify-center">
                    <i class="fas fa-times mr-2"></i>
                    Rejeitar Transferência
                </a>
                <button type="button" onclick="excluirTransferencia({{ $transferencia->id }})" class="w-full border border-red-300 text-red-700 hover:bg-red-50 font-bold py-3 px-4 rounded-lg text-sm flex items-center justify-center">
                    <i class="fas fa-trash mr-2"></i>
                    Excluir Solicitação
                </button>
            </div>
        </div>
        @endif
    </div>

@endsection

@push('scripts')
<script>
function excluirTransferencia(id) {
    if (confirm('Tem certeza que deseja excluir esta transferência?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/transferencias/${id}`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        
        form.appendChild(csrfToken);
        form.appendChild(methodInput);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endpush