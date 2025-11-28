@extends('layouts.app')

@section('content')
<x-card>
    <div class="flex flex-col mb-6 space-y-4 md:flex-row justify-between md:space-y-0 md:items-center">
        <div>
            <h1 class="text-lg md:text-2xl font-semibold text-gray-900">Biblioteca</h1>
            <p class="mt-1 text-sm text-gray-600">Gerenciamento de itens da biblioteca</p>
        </div>
        <div class="flex flex-col gap-2 sm:flex-row">
            <x-button type="button" color="primary" onclick="showModal('create-item-modal')">
                <i class="fas fa-plus mr-1"></i>
                Novo Item
            </x-button>
        </div>
    </div>

    <x-collapsible-filter 
        title="Filtros de Itens" 
        :action="route('biblioteca.index')" 
        :clear-route="route('biblioteca.index')"
        target="biblioteca-list-wrapper"
    >
        <x-filter-field name="titulo" label="Título" type="text" placeholder="Buscar por título..." />
        <x-filter-field name="autores" label="Autores" type="text" placeholder="Buscar por autores..." />
        <x-filter-field 
            name="tipo" 
            label="Tipo" 
            type="select"
            empty-option="Todos"
            :options="[
                'livro' => 'Livro',
                'revista' => 'Revista',
                'digital' => 'Digital',
                'audio' => 'Áudio',
                'video' => 'Vídeo'
            ]"
        />
        <x-filter-field 
            name="habilitado_emprestimo" 
            label="Habilitado para empréstimo" 
            type="select"
            empty-option="Todos"
            :options="[
                '1' => 'Sim',
                '0' => 'Não'
            ]"
        />
    </x-collapsible-filter>

    <div id="biblioteca-list-wrapper" class="relative">
        <x-loading-overlay message="Atualizando itens..." />
        <div data-ajax-content>
            <div class="hidden md:block" id="items-desktop">
                <x-table 
                    :headers="[
                        ['label' => 'ID', 'sort' => 'id'],
                        ['label' => 'Título', 'sort' => 'titulo'],
                        ['label' => 'Autores', 'sort' => 'autores'],
                        ['label' => 'Tipo', 'sort' => 'tipo'],
                        'Status',
                        ['label' => 'Criado em', 'sort' => 'created_at'],
                        'Uploads realizados',

                    ]"
                    :actions="true"
                    striped
                    hover
                    responsive
                    sortable
                    :currentSort="request('sort')"
                    :currentDirection="request('direction', 'asc')"
                >
                    @forelse($items as $index => $item)
                        <x-table-row :striped="true" :index="$index">
                            <x-table-cell>#{{ $item->id }}</x-table-cell>
                            <x-table-cell>
                                <div class="flex items-center">
                                    @php $capa = ($item->arquivosDigitais ?? collect())->firstWhere('tipo', 'capa'); @endphp
                                    <div class="w-10 h-14 mr-3 overflow-hidden rounded bg-gray-100 flex items-center justify-center">
                                        @if($capa)
                                            <img id="cover-img-{{ $item->id }}" src="{{ route('biblioteca.cover', ['digitalId' => $capa->id]) }}" alt="Capa" class="w-full h-full object-cover" data-default-cover-url="{{ asset('images/book-cover-default.svg') }}">
                                        @else
                                            <img id="cover-img-{{ $item->id }}" src="{{ asset('images/book-cover-default.svg') }}" alt="Sem capa" class="w-full h-full object-cover" data-default-cover-url="{{ asset('images/book-cover-default.svg') }}">
                                        @endif
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-900">{{ $item->titulo }}</div>
                                    </div>
                                </div>
                            </x-table-cell>
                            <x-table-cell>
                                @if ($item->autores)
                                    <div class="text-sm text-gray-500">{{ $item->autores }}</div>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </x-table-cell>
                            <x-table-cell>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    {{ ucfirst($item->tipo) }}
                                </span>
                            </x-table-cell>
                            <x-table-cell>
                                @php
                                    $status = strtolower($item->status ?? 'ativo');
                                    $statusClasses = [
                                        'ativo' => 'bg-green-100 text-green-800',
                                        'disponivel' => 'bg-green-100 text-green-800',
                                        'inativo' => 'bg-gray-100 text-gray-800',
                                        'indisponivel' => 'bg-red-100 text-red-800',
                                    ];
                                    $badgeClass = $statusClasses[$status] ?? 'bg-gray-100 text-gray-800';
                                @endphp
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badgeClass }}">
                                        {{ ucfirst($status) }}
                                    </span>
                                    @if(!$item->habilitado_emprestimo)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            Não habilitado
                                        </span>
                                    @endif
                                </div>
                            </x-table-cell>
                            <x-table-cell>
                                <span class="text-xs text-gray-500">{{ $item->created_at?->format('d/m/Y H:i') }}</span>
                            </x-table-cell>
                            <!-- Coluna de uploads realizados -->
                            <x-table-cell>
                                @php $digitais = $item->arquivosDigitais ?? collect(); @endphp
                                @if($digitais->count() > 0)
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="text-sm text-gray-600"><span id="uploads-count-{{ $item->id }}">{{ $digitais->count() }}</span> arquivo(s)</span>
                                        <div class="flex items-center gap-2">
                                            @foreach($digitais as $digital)
                                                <a href="{{ route('biblioteca.preview', ['digitalId' => $digital->id]) }}" class="inline-flex items-center gap-1 px-2 py-0.5 rounded border text-xs text-indigo-700 border-indigo-200 hover:bg-indigo-50" title="Preview {{ strtoupper($digital->tipo) }}">
                                                    <i class="fas fa-file-{{ $digital->tipo === 'pdf' ? 'pdf' : ($digital->tipo === 'epub' ? 'alt' : ($digital->tipo === 'mp3' ? 'audio' : 'video')) }}"></i>
                                                    <span class="capitalize">{{ $digital->tipo }}</span>
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </x-table-cell>
                            <x-table-cell align="right">
                                <div class="flex items-center justify-end gap-3 flex-nowrap">
                                    <!-- Botões Ver/Editar -->
                                    <div class="flex items-center gap-2">
                                        <x-button type="button" color="secondary" size="sm"
                                            onclick="openViewItem(this)"
                                            data-id="{{ $item->id }}"
                                            data-titulo="{{ e($item->titulo) }}"
                                            data-autores="{{ e($item->autores) }}"
                                            data-editora="{{ e($item->editora) }}"
                                            data-ano="{{ e($item->ano) }}"
                                            data-isbn="{{ e($item->isbn) }}"
                                            data-tipo="{{ e($item->tipo) }}"
                                            data-quantidade_fisica="{{ e($item->quantidade_fisica) }}"
                                            data-status="{{ e($item->status) }}"
                                            data-created_at="{{ optional($item->created_at)->format('d/m/Y H:i') }}"
                                            data-digitais_count="{{ $item->arquivosDigitais->count() }}"
                                            data-digitais_types="{{ implode(',', $item->arquivosDigitais->pluck('tipo')->unique()->toArray()) }}"
                                        >
                                            <i class="fas fa-eye mr-1"></i>
                                            Ver
                                        </x-button>

                                        <x-button type="button" color="primary" size="sm"
                                            onclick="openEditItem(this)"
                                            data-id="{{ $item->id }}"
                                            data-update-url="{{ route('biblioteca.update', ['item' => $item->id]) }}"
                                            data-titulo="{{ e($item->titulo) }}"
                                            data-autores="{{ e($item->autores) }}"
                                            data-editora="{{ e($item->editora) }}"
                                            data-ano="{{ e($item->ano) }}"
                                            data-isbn="{{ e($item->isbn) }}"
                                            data-tipo="{{ e($item->tipo) }}"
                                            data-quantidade_fisica="{{ e($item->quantidade_fisica) }}"
                                            data-status="{{ e($item->status) }}"
                                            data-habilitado_emprestimo="{{ $item->habilitado_emprestimo ? '1' : '0' }}"
                                        >
                                            <i class="fas fa-edit mr-1"></i>
                                            Editar
                                        </x-button>
                                    </div>

                                    <!-- Abrir modal de anexos (livro e capa) -->
                                    <div class="flex items-center gap-2">
                                        @php
                                            $digitais = $item->arquivosDigitais ?? collect();
                                            $coverDigital = $digitais->firstWhere('tipo', 'capa');
                                            $bookDigital = $digitais->first(function($d){ return in_array($d->tipo, ['pdf','epub','mp3','mp4']); });
                                        @endphp
                                        <x-button type="button" color="secondary" size="sm"
                                            onclick="openAttachModal({{ $item->id }}, '{{ route('biblioteca.upload', ['itemId' => $item->id]) }}', {{ $bookDigital ? 'true' : 'false' }}, {{ $coverDigital ? 'true' : 'false' }}, {{ $bookDigital->id ?? 'null' }}, {{ $coverDigital->id ?? 'null' }})">
                                            <i class="fas fa-paperclip mr-1"></i>
                                            Anexar
                                        </x-button>
                                    </div>
                                </div>
                            </x-table-cell>
                        </x-table-row>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                Nenhum item encontrado.
                            </td>
                        </tr>
                    @endforelse
                </x-table>
            </div>

            <!-- Layout mobile com cards -->
            <div class="md:hidden space-y-4" id="items-mobile">
                @forelse($items as $item)
                    <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                            <div class="flex items-start mb-3">
                            @php $capa = ($item->arquivosDigitais ?? collect())->firstWhere('tipo', 'capa'); @endphp
                            <div class="w-12 h-16 mr-3 overflow-hidden rounded bg-gray-100 flex items-center justify-center">
                                @if($capa)
                                    <img id="cover-img-{{ $item->id }}" src="{{ route('biblioteca.cover', ['digitalId' => $capa->id]) }}" alt="Capa" class="w-full h-full object-cover">
                                @else
                                    <img id="cover-img-{{ $item->id }}" src="{{ asset('images/book-cover-default.svg') }}" alt="Sem capa" class="w-full h-full object-cover">
                                @endif
                            </div>
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-900 text-base">{{ $item->titulo }}</h3>
                                @if ($item->autores)
                                    <p class="text-sm text-gray-500">{{ $item->autores }}</p>
                                @endif
                                @if ($item->created_at)
                                    <p class="text-xs text-gray-500 mt-1">Criado em: {{ $item->created_at->format('d/m/Y H:i') }}</p>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center gap-2 mt-2">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                {{ ucfirst($item->tipo) }}
                            </span>
                            @php
                                $status = strtolower($item->status ?? 'ativo');
                                $statusClasses = [
                                    'ativo' => 'bg-green-100 text-green-800',
                                    'disponivel' => 'bg-green-100 text-green-800',
                                    'inativo' => 'bg-gray-100 text-gray-800',
                                    'indisponivel' => 'bg-red-100 text-red-800',
                                ];
                                $badgeClass = $statusClasses[$status] ?? 'bg-gray-100 text-gray-800';
                            @endphp
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $badgeClass }}">
                                    {{ ucfirst($status) }}
                                </span>
                                @if(!$item->habilitado_emprestimo)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        Não habilitado
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Uploads realizados (mobile) -->
                        @php $digitais = $item->arquivosDigitais ?? collect(); @endphp
                        <div class="mt-2">
                            @if($digitais->count() > 0)
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-sm text-gray-600"><span id="uploads-count-{{ $item->id }}">{{ $digitais->count() }}</span> arquivo(s)</span>
                                    <div class="flex items-center gap-2">
                                        @foreach($digitais as $digital)
                                            <a href="{{ route('biblioteca.preview', ['digitalId' => $digital->id]) }}" class="inline-flex items-center gap-1 px-2 py-0.5 rounded border text-xs text-indigo-700 border-indigo-200 hover:bg-indigo-50" title="Preview {{ strtoupper($digital->tipo) }}">
                                                <i class="fas fa-file-{{ $digital->tipo === 'pdf' ? 'pdf' : ($digital->tipo === 'epub' ? 'alt' : ($digital->tipo === 'mp3' ? 'audio' : 'video')) }}"></i>
                                                <span class="capitalize">{{ $digital->tipo }}</span>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <span class="text-gray-400 text-sm">Nenhum upload</span>
                            @endif
                        </div>

                        <div class="flex flex-col gap-2 mt-2">
                            <div class="flex gap-2 w-full">
                                <x-button type="button" color="secondary" size="sm" class="flex-1"
                                    onclick="openViewItem(this)"
                                    data-id="{{ $item->id }}"
                                    data-titulo="{{ e($item->titulo) }}"
                                    data-autores="{{ e($item->autores) }}"
                                    data-editora="{{ e($item->editora) }}"
                                    data-ano="{{ e($item->ano) }}"
                                    data-isbn="{{ e($item->isbn) }}"
                                    data-tipo="{{ e($item->tipo) }}"
                                    data-quantidade_fisica="{{ e($item->quantidade_fisica) }}"
                                    data-status="{{ e($item->status) }}"
                                    data-created_at="{{ optional($item->created_at)->format('d/m/Y H:i') }}"
                                    data-digitais_count="{{ $item->arquivosDigitais->count() }}"
                                    data-digitais_types="{{ implode(',', $item->arquivosDigitais->pluck('tipo')->unique()->toArray()) }}"
                                >
                                    <i class="fas fa-eye mr-1"></i>
                                    Ver
                                </x-button>
                                <x-button type="button" color="primary" size="sm" class="flex-1"
                                    onclick="openEditItem(this)"
                                    data-id="{{ $item->id }}"
                                    data-update-url="{{ route('biblioteca.update', ['item' => $item->id]) }}"
                                    data-titulo="{{ e($item->titulo) }}"
                                    data-autores="{{ e($item->autores) }}"
                                    data-editora="{{ e($item->editora) }}"
                                    data-ano="{{ e($item->ano) }}"
                                    data-isbn="{{ e($item->isbn) }}"
                                    data-tipo="{{ e($item->tipo) }}"
                                    data-quantidade_fisica="{{ e($item->quantidade_fisica) }}"
                                    data-status="{{ e($item->status) }}"
                                    data-habilitado_emprestimo="{{ $item->habilitado_emprestimo ? '1' : '0' }}"
                                >
                                    <i class="fas fa-edit mr-1"></i>
                                    Editar
                                </x-button>
                            </div>
                            <!-- Abrir modal de anexos (mobile) -->
                            <div class="flex flex-col gap-2">
                                @php
                                    $digitais = $item->arquivosDigitais ?? collect();
                                    $coverDigital = $digitais->firstWhere('tipo', 'capa');
                                    $bookDigital = $digitais->first(function($d){ return in_array($d->tipo, ['pdf','epub','mp3','mp4']); });
                                @endphp
                                <x-button type="button" color="secondary" size="sm" class="flex-none"
                                    onclick="openAttachModal({{ $item->id }}, '{{ route('biblioteca.upload', ['itemId' => $item->id]) }}', {{ $bookDigital ? 'true' : 'false' }}, {{ $coverDigital ? 'true' : 'false' }}, {{ $bookDigital->id ?? 'null' }}, {{ $coverDigital->id ?? 'null' }})">
                                    <i class="fas fa-paperclip mr-1"></i>
                                    Anexar
                                </x-button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8">
                        <div class="w-16 h-16 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
                            <i class="fas fa-book text-2xl text-gray-400"></i>
                        </div>
                        <p class="text-gray-500">Nenhum item encontrado.</p>
                    </div>
                @endforelse
            </div>

            <div class="mt-4">
                {{ $items->links('components.pagination') }}
            </div>
        </div>
    </div>
</x-card>

<!-- Modal de anexos (livro e capa) -->
<x-modal name="attach-item-modal" title="Anexar Arquivos" maxWidth="w-11/12 md:w-3/4 lg:w-1/2">
    <form id="attach-form" action="#" method="POST" class="space-y-4" enctype="multipart/form-data">
        @csrf
        <input type="hidden" id="attach-upload-url" value="">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Arquivo do livro</label>
                <input type="file" id="attach-livro" accept=".pdf,.epub,.mp3,.mp4" class="w-full border rounded px-3 py-2">
                <p class="text-xs text-gray-500 mt-1">Tipos aceitos: pdf, epub, mp3, mp4</p>
                <div class="mt-2 hidden w-full h-2 bg-gray-200 rounded overflow-hidden" id="attach-livro-progress"><div class="h-2 bg-indigo-600" style="width:0%"></div></div>
                <span id="attach-livro-status" class="text-xs text-gray-600"></span>
                <div id="attach-livro-existing" class="mt-2 hidden text-xs text-yellow-700">
                    Já existe um arquivo de livro anexado. Exclua para enviar outro.
                    <x-button type="button" size="xs" color="warning" onclick="deleteExisting('livro')">
                        <i class="fas fa-trash mr-1"></i> Excluir atual
                    </x-button>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Capa do livro (imagem)</label>
                <input type="file" id="attach-capa" accept="image/*" class="w-full border rounded px-3 py-2">
                <p class="text-xs text-gray-500 mt-1">Será redimensionada para 400x600</p>
                <div class="mt-2 hidden w-full h-2 bg-gray-200 rounded overflow-hidden" id="attach-capa-progress"><div class="h-2 bg-indigo-600" style="width:0%"></div></div>
                <span id="attach-capa-status" class="text-xs text-gray-600"></span>
                <div id="attach-capa-existing" class="mt-2 hidden text-xs text-yellow-700">
                    Já existe uma capa anexada. Exclua para enviar outra.
                    <x-button type="button" size="xs" color="warning" onclick="deleteExisting('capa')">
                        <i class="fas fa-trash mr-1"></i> Excluir capa
                    </x-button>
                </div>
            </div>
        </div>
        <div class="flex justify-end gap-2 pt-2">
            <x-button type="button" color="secondary" onclick="closeModal('attach-item-modal')">Cancelar</x-button>
            <x-button id="attach-send-btn" type="button" color="primary" onclick="submitAttachForm()">
                <i class="fas fa-upload mr-1"></i>
                Enviar
            </x-button>
        </div>
    </form>
    </x-modal>

<!-- Modal de cadastro de novo item -->
<x-modal name="create-item-modal" title="Cadastrar Item" maxWidth="w-11/12 md:w-3/4 lg:w-1/2">
    <form id="create-item-form" action="{{ route('biblioteca.store') }}" method="POST" class="space-y-4" onsubmit="submitCreateItem(event)">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Título</label>
                <input type="text" name="titulo" class="w-full border rounded px-3 py-2" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Autores</label>
                <input type="text" name="autores" class="w-full border rounded px-3 py-2">
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">ISBN</label>
                <input type="text" name="isbn" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Editora</label>
                <input type="text" name="editora" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Ano</label>
                <input type="number" name="ano" class="w-full border rounded px-3 py-2">
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                <select name="tipo" class="w-full border rounded px-3 py-2" required>
                    <option value="livro">Livro</option>
                    <option value="revista">Revista</option>
                    <option value="digital">Digital</option>
                    <option value="audio">Áudio</option>
                    <option value="video">Vídeo</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Quantidade física</label>
                <input type="number" name="quantidade_fisica" class="w-full border rounded px-3 py-2" value="0">
            </div>
            <div class="flex items-center mt-6">
                <input type="checkbox" id="create-habilitado_emprestimo" name="habilitado_emprestimo" value="1" class="mr-2">
                <label for="create-habilitado_emprestimo" class="text-sm text-gray-700">Habilitado para empréstimo</label>
            </div>
        </div>
        <div class="flex justify-end gap-2 pt-2">
            <x-button type="button" color="secondary" onclick="closeModal('create-item-modal')">Cancelar</x-button>
            <x-button type="submit" color="primary">
                <i class="fas fa-save mr-1"></i>
                Salvar
            </x-button>
        </div>
    </form>
</x-modal>

<!-- Modal de visualização de item -->
<x-modal name="view-item-modal" title="Detalhes do Item" maxWidth="w-11/12 md:w-3/4 lg:w-1/2">
    <!-- Header com título/autores, data e badges -->
    <div class="flex flex-col gap-2 md:flex-row md:items-start md:justify-between">
        <div class="flex items-start gap-3">
            <div class="flex-shrink-0 mt-0.5 text-indigo-600">
                <i class="fas fa-book text-xl"></i>
            </div>
            <div class="w-12 h-16 overflow-hidden rounded bg-gray-100 flex items-center justify-center">
                <img id="view-cover-img" src="{{ asset('images/book-cover-default.svg') }}" alt="Capa" class="w-full h-full object-cover" data-default-cover-url="{{ asset('images/book-cover-default.svg') }}">
            </div>
            <div>
                <h3 id="view-titulo" class="text-lg font-semibold text-gray-900"></h3>
                <p id="view-autores" class="text-sm text-gray-600"></p>
                <p id="view-created_at" class="text-xs text-gray-500 mt-1"></p>
            </div>
        </div>
        <div class="flex items-center gap-2 mt-2 md:mt-0">
            <span id="view-tipo-badge" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800"></span>
            <span id="view-status-badge" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800"></span>
        </div>
    </div>

    <!-- Metadados principais -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">ISBN</label>
            <p id="view-isbn" class="text-gray-900"></p>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Editora</label>
            <p id="view-editora" class="text-gray-900"></p>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Ano</label>
            <p id="view-ano" class="text-gray-900"></p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Quantidade física</label>
            <p id="view-quantidade_fisica" class="text-gray-900"></p>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <p id="view-status" class="text-gray-900"></p>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
            <p id="view-tipo" class="text-gray-900"></p>
        </div>
    </div>

    <!-- Uploads realizados -->
    <div class="mt-4">
        <div class="flex items-center justify-between">
            <label class="block text-sm font-medium text-gray-700">Uploads realizados</label>
            <span id="view-uploads-count" class="text-xs text-gray-500"></span>
        </div>
        <div id="view-attachments" class="mt-2 flex flex-wrap gap-2"></div>
    </div>

    <div class="flex justify-end gap-2 pt-4">
        <x-button type="button" color="secondary" onclick="closeModal('view-item-modal')">Fechar</x-button>
    </div>
    </x-modal>

<!-- Modal de edição de item -->
<x-modal name="edit-item-modal" title="Editar Item" maxWidth="w-11/12 md:w-3/4 lg:w-1/2">
    <form id="edit-item-form" action="#" method="POST" class="space-y-4">
        @csrf
        @method('PATCH')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Título</label>
                <input id="edit-titulo" type="text" name="titulo" class="w-full border rounded px-3 py-2" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Autores</label>
                <input id="edit-autores" type="text" name="autores" class="w-full border rounded px-3 py-2">
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">ISBN</label>
                <input id="edit-isbn" type="text" name="isbn" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Editora</label>
                <input id="edit-editora" type="text" name="editora" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Ano</label>
                <input id="edit-ano" type="number" name="ano" class="w-full border rounded px-3 py-2">
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                <select id="edit-tipo" name="tipo" class="w-full border rounded px-3 py-2" required>
                    <option value="livro">Livro</option>
                    <option value="revista">Revista</option>
                    <option value="digital">Digital</option>
                    <option value="audio">Áudio</option>
                    <option value="video">Vídeo</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Quantidade física</label>
                <input id="edit-quantidade_fisica" type="number" name="quantidade_fisica" class="w-full border rounded px-3 py-2" value="0">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select id="edit-status" name="status" class="w-full border rounded px-3 py-2">
                    <option value="ativo">Ativo</option>
                    <option value="inativo">Inativo</option>
                    <option value="disponivel">Disponível</option>
                    <option value="indisponivel">Indisponível</option>
                </select>
            </div>
            <div class="flex items-center mt-6">
                <input type="checkbox" id="edit-habilitado_emprestimo" name="habilitado_emprestimo" value="1" class="mr-2">
                <label for="edit-habilitado_emprestimo" class="text-sm text-gray-700">Habilitado para empréstimo</label>
            </div>
        </div>
        <div class="flex justify-end gap-2 pt-2">
            <x-button type="button" color="secondary" onclick="closeModal('edit-item-modal')">Cancelar</x-button>
            <x-button type="submit" color="primary">
                <i class="fas fa-save mr-1"></i>
                Salvar
            </x-button>
        </div>
    </form>
    </x-modal>

<script>
    // AJAX bindings para ordenação e paginação na lista da biblioteca
    function showBibliotecaLoading() {
        const wrapper = document.getElementById('biblioteca-list-wrapper');
        if (!wrapper) return;
        const overlay = wrapper.querySelector('[data-loading-overlay]') || wrapper.querySelector('.loading-overlay');
        if (overlay) overlay.classList.remove('hidden');
        wrapper.style.pointerEvents = 'none';
    }

    function hideBibliotecaLoading() {
        const wrapper = document.getElementById('biblioteca-list-wrapper');
        if (!wrapper) return;
        const overlay = wrapper.querySelector('[data-loading-overlay]') || wrapper.querySelector('.loading-overlay');
        if (overlay) overlay.classList.add('hidden');
        wrapper.style.pointerEvents = '';
    }

    function updateBibliotecaContainer(url, pushState = true) {
        const wrapper = document.getElementById('biblioteca-list-wrapper');
        if (!wrapper) { window.location.href = url; return; }
        const ajaxArea = wrapper.querySelector('[data-ajax-content]');
        if (!ajaxArea) { window.location.href = url; return; }

        showBibliotecaLoading();
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(resp => resp.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newWrapper = doc.querySelector('#biblioteca-list-wrapper');
                const newAjaxArea = newWrapper ? newWrapper.querySelector('[data-ajax-content]') : null;
                if (newAjaxArea) {
                    ajaxArea.innerHTML = newAjaxArea.innerHTML;
                    if (pushState) window.history.pushState(null, '', url);
                    initBibliotecaAjaxBindings();
                } else {
                    window.location.href = url;
                }
            })
            .catch(() => { window.location.href = url; })
            .finally(() => { hideBibliotecaLoading(); });
    }

    function initBibliotecaAjaxBindings() {
        const wrapper = document.getElementById('biblioteca-list-wrapper');
        if (!wrapper) return;
        const ajaxArea = wrapper.querySelector('[data-ajax-content]');
        if (!ajaxArea) return;

        // Interceptar ordenação nos cabeçalhos da tabela
        const sortLinks = ajaxArea.querySelectorAll('thead a[href]');
        sortLinks.forEach(link => {
            if (link.dataset.ajaxBound === '1') return;
            link.dataset.ajaxBound = '1';
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const url = this.href;
                updateBibliotecaContainer(url);
            });
        });

        // Interceptar paginação
        const paginationLinks = ajaxArea.querySelectorAll('nav[aria-label="Pagination Navigation"] a[href]');
        paginationLinks.forEach(link => {
            if (link.dataset.ajaxBound === '1') return;
            link.dataset.ajaxBound = '1';
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const url = this.href;
                updateBibliotecaContainer(url);
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        initBibliotecaAjaxBindings();
        window.addEventListener('popstate', function() {
            updateBibliotecaContainer(window.location.href, false);
        });
    });

    // Modais de Ver/Editar
    function setText(id, value) {
        const el = document.getElementById(id);
        if (el) el.textContent = value || '';
    }

    window.openViewItem = function(button) {
        const d = button.dataset;
        const capitalize = (v) => v ? v.charAt(0).toUpperCase() + v.slice(1) : '';

        // Texto principal
        setText('view-titulo', d.titulo);
        setText('view-autores', d.autores);
        setText('view-created_at', d.created_at ? `Criado em ${d.created_at}` : '');

        // Metadados
        setText('view-isbn', d.isbn);
        setText('view-editora', d.editora);
        setText('view-ano', d.ano);
        setText('view-quantidade_fisica', d.quantidade_fisica);
        setText('view-tipo', capitalize(d.tipo));
        setText('view-status', capitalize(d.status));

        // Badges
        const tipoBadge = document.getElementById('view-tipo-badge');
        const statusBadge = document.getElementById('view-status-badge');
        if (tipoBadge) tipoBadge.textContent = capitalize(d.tipo);
        if (statusBadge) statusBadge.textContent = capitalize(d.status);

        // Reset uploads e capa
        const cont = document.getElementById('view-attachments');
        const countEl = document.getElementById('view-uploads-count');
        const coverImg = document.getElementById('view-cover-img');
        if (cont) cont.innerHTML = '';
        if (countEl) countEl.textContent = '';
        if (coverImg) {
            const def = coverImg.getAttribute('data-default-cover-url') || coverImg.src;
            coverImg.src = def;
        }

        // Buscar anexos completos via AJAX
        const itemId = d.id;
        const xhr = new XMLHttpRequest();
        xhr.open('GET', '/biblioteca/item/' + itemId + '/uploads', true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        const tokenMeta = document.querySelector('meta[name="csrf-token"]');
        if (tokenMeta) xhr.setRequestHeader('X-CSRF-TOKEN', tokenMeta.getAttribute('content'));
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                if (xhr.status >= 200 && xhr.status < 300) {
                    try {
                        const resp = JSON.parse(xhr.responseText);
                        if (resp && Array.isArray(resp.digitais)) {
                            // Contagem
                            if (countEl) countEl.textContent = resp.digitais.length ? `${resp.digitais.length} arquivo(s) digital(is)` : '';

                            // Renderizar lista de previews
                            if (cont) {
                                resp.digitais.forEach(digital => {
                                    const iconClass = digital.tipo === 'pdf' ? 'file-pdf' : 
                                                     digital.tipo === 'epub' ? 'file-alt' : 
                                                     digital.tipo === 'mp3' ? 'file-audio' : 
                                                     digital.tipo === 'capa' ? 'image' : 'file-video';
                                    const a = document.createElement('a');
                                    a.href = `/biblioteca/digital/${digital.id}/preview`;
                                    a.className = 'inline-flex items-center gap-1 px-2 py-0.5 rounded border text-xs text-indigo-700 border-indigo-200 hover:bg-indigo-50';
                                    a.title = `Preview ${digital.tipo.toUpperCase()}`;
                                    const i = document.createElement('i');
                                    i.className = `fas fa-${iconClass}`;
                                    const span = document.createElement('span');
                                    span.className = 'capitalize';
                                    span.textContent = digital.tipo;
                                    a.appendChild(i);
                                    a.appendChild(span);
                                    cont.appendChild(a);

                                    // Atualizar capa se existir
                                    if (digital.tipo === 'capa' && coverImg) {
                                        coverImg.src = `/biblioteca/digital/${digital.id}/cover?t=${Date.now()}`;
                                    }
                                });
                            }
                        }
                    } catch (e) {
                        // Ignorar erros de parse
                    }
                } else {
                    // Em erro, manter default sem anexos
                }
            }
        };
        xhr.send();

        showModal('view-item-modal');
    }

    window.openEditItem = function(button) {
        const d = button.dataset;
        const form = document.getElementById('edit-item-form');
        if (form && d.updateUrl) form.action = d.updateUrl;
        const setVal = (id, val) => { const el = document.getElementById(id); if (el) el.value = val || ''; };
        setVal('edit-titulo', d.titulo);
        setVal('edit-autores', d.autores);
        setVal('edit-isbn', d.isbn);
        setVal('edit-editora', d.editora);
        setVal('edit-ano', d.ano);
        setVal('edit-tipo', d.tipo);
        setVal('edit-quantidade_fisica', d.quantidade_fisica);
        setVal('edit-status', d.status || 'ativo');
        const chk = document.getElementById('edit-habilitado_emprestimo');
        if (chk) chk.checked = (d.habilitado_emprestimo === '1' || d.habilitado_emprestimo === 'true');
        showModal('edit-item-modal');
    }

    // Upload com progresso
    function getTipoFromFile(file) {
        const ext = file.name.split('.').pop().toLowerCase();
        const map = { pdf: 'pdf', epub: 'epub', mp3: 'mp3', mp4: 'mp4' };
        return map[ext] || null;
    }

    function uploadDigitalFile(itemId, file, uploadUrl, isMobile = false) {
        const tipo = getTipoFromFile(file);
        const progressEl = document.getElementById(isMobile ? `upload-progress-mobile-${itemId}` : `upload-progress-${itemId}`);
        const barEl = progressEl ? progressEl.querySelector('div') : null;
        const statusEl = document.getElementById(isMobile ? `upload-status-mobile-${itemId}` : `upload-status-${itemId}`);

        if (!tipo) {
            if (statusEl) statusEl.textContent = 'Tipo inválido. Use pdf/epub/mp3/mp4.';
            return;
        }

        if (progressEl) progressEl.classList.remove('hidden');
        if (statusEl) statusEl.textContent = 'Iniciando...';

        const xhr = new XMLHttpRequest();
        xhr.open('POST', uploadUrl, true);

        // CSRF
        const tokenMeta = document.querySelector('meta[name="csrf-token"]');
        if (tokenMeta) xhr.setRequestHeader('X-CSRF-TOKEN', tokenMeta.getAttribute('content'));

        xhr.upload.onprogress = function(e) {
            if (e.lengthComputable && barEl) {
                const percent = Math.round((e.loaded / e.total) * 100);
                barEl.style.width = percent + '%';
                if (statusEl) statusEl.textContent = percent + '%';
            }
        };

        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                if (xhr.status >= 200 && xhr.status < 300) {
                    if (statusEl) statusEl.textContent = 'Concluído';
                    // atualizar listagem para refletir anexos
                    updateBibliotecaContainer(window.location.href);
                } else {
                    if (statusEl) statusEl.textContent = 'Falha no upload';
                }
            }
        };

        const formData = new FormData();
        formData.append('file', file);
        formData.append('tipo', tipo);
        xhr.send(formData);
    }

    // Modal de anexos
    // Estado do modal
    let attachItemId = null;
    let pendingUploads = 0;
    let uploadedSomething = false;
    let uploadedCoverDigitalId = null;
    let latestDigitaisCount = null;

    window.openAttachModal = function(itemId, uploadUrl, hasBook = false, hasCover = false, bookDigitalId = null, coverDigitalId = null) {
        const urlInput = document.getElementById('attach-upload-url');
        if (urlInput) urlInput.value = uploadUrl;
        attachItemId = itemId;
        // Guardar itemId e IDs existentes
        const form = document.getElementById('attach-form');
        if (form) {
            form.dataset.itemId = itemId;
            form.dataset.bookId = bookDigitalId || '';
            form.dataset.coverId = coverDigitalId || '';
        }
        // reset estados
        uploadedSomething = false;
        uploadedCoverDigitalId = null;
        latestDigitaisCount = null;
        pendingUploads = 0;
        ['attach-livro-status','attach-capa-status'].forEach(id => { const el = document.getElementById(id); if (el) el.textContent=''; });
        ['attach-livro-progress','attach-capa-progress'].forEach(id => { const el=document.getElementById(id); if (el) { el.classList.add('hidden'); const bar=el.querySelector('div'); if (bar) bar.style.width='0%'; } });
        const sendBtn = document.getElementById('attach-send-btn');
        if (sendBtn) sendBtn.setAttribute('disabled','disabled');
        // Bind immediate upload
        const livroInput = document.getElementById('attach-livro');
        const capaInput = document.getElementById('attach-capa');
        const livroExisting = document.getElementById('attach-livro-existing');
        const capaExisting = document.getElementById('attach-capa-existing');

        // Bloquear inputs se já existem
        if (hasBook) {
            if (livroInput) livroInput.setAttribute('disabled', 'disabled');
            if (livroExisting) livroExisting.classList.remove('hidden');
        } else {
            if (livroInput) livroInput.removeAttribute('disabled');
            if (livroExisting) livroExisting.classList.add('hidden');
        }
        if (hasCover) {
            if (capaInput) capaInput.setAttribute('disabled', 'disabled');
            if (capaExisting) capaExisting.classList.remove('hidden');
        } else {
            if (capaInput) capaInput.removeAttribute('disabled');
            if (capaExisting) capaExisting.classList.add('hidden');
        }

        if (livroInput) {
            livroInput.onchange = function() {
                const file = livroInput.files && livroInput.files[0];
                if (!file) return;
                // iniciar upload
                const progressEl = document.getElementById('attach-livro-progress');
                if (progressEl) progressEl.classList.remove('hidden');
                pendingUploads++;
                uploadModalFile(uploadUrl, file, guessTipoFromName(file.name), 'attach-livro-progress', 'attach-livro-status');
            };
        }
        if (capaInput) {
            capaInput.onchange = async function() {
                const file = capaInput.files && capaInput.files[0];
                if (!file) return;
                const progressEl = document.getElementById('attach-capa-progress');
                if (progressEl) progressEl.classList.remove('hidden');
                const statusEl = document.getElementById('attach-capa-status');
                if (statusEl) statusEl.textContent = 'Processando imagem...';
                try {
                    pendingUploads++;
                    const blob = await resizeCoverToBlob(file);
                    uploadModalFile(uploadUrl, blob, 'capa', 'attach-capa-progress', 'attach-capa-status');
                } catch (e) {
                    if (statusEl) statusEl.textContent = 'Erro ao processar a capa';
                }
            };
        }
        showModal('attach-item-modal');
    }

    function setAttachSendEnabledIfReady() {
        const sendBtn = document.getElementById('attach-send-btn');
        if (!sendBtn) return;
        // Habilita o botão quando não há uploads pendentes
        if (pendingUploads === 0) {
            sendBtn.removeAttribute('disabled');
        } else {
            sendBtn.setAttribute('disabled','disabled');
        }
    }

    function guessTipoFromName(name) {
        const lower = (name || '').toLowerCase();
        if (lower.endsWith('.pdf')) return 'pdf';
        if (lower.endsWith('.epub')) return 'epub';
        if (lower.endsWith('.mp3')) return 'mp3';
        if (lower.endsWith('.mp4')) return 'mp4';
        return 'pdf';
    }

    function resizeCoverToBlob(file, targetW=400, targetH=600) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = () => {
                const img = new Image();
                img.onload = () => {
                    const origW = img.width, origH = img.height;
                    const targetRatio = targetW / targetH;
                    const origRatio = origW / origH;
                    let srcX=0, srcY=0, newW=origW, newH=origH;
                    if (origRatio > targetRatio) {
                        newW = Math.floor(origH * targetRatio);
                        newH = origH;
                        srcX = Math.floor((origW - newW) / 2);
                        srcY = 0;
                    } else {
                        newW = origW;
                        newH = Math.floor(origW / targetRatio);
                        srcX = 0;
                        srcY = Math.floor((origH - newH) / 2);
                    }
                    const canvas = document.createElement('canvas');
                    canvas.width = targetW;
                    canvas.height = targetH;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, srcX, srcY, newW, newH, 0, 0, targetW, targetH);
                    canvas.toBlob((blob) => {
                        if (blob) resolve(blob); else reject(new Error('Falha ao gerar imagem'));
                    }, 'image/jpeg', 0.85);
                };
                img.onerror = () => reject(new Error('Imagem inválida'));
                img.src = reader.result;
            };
            reader.onerror = () => reject(new Error('Falha ao ler arquivo'));
            reader.readAsDataURL(file);
        });
    }

    function uploadModalFile(uploadUrl, fileOrBlob, tipo, progressId, statusId) {
        const progressWrap = document.getElementById(progressId);
        const barEl = progressWrap ? progressWrap.querySelector('div') : null;
        const statusEl = document.getElementById(statusId);
        const xhr = new XMLHttpRequest();
        xhr.open('POST', uploadUrl, true);
        const tokenMeta = document.querySelector('meta[name="csrf-token"]');
        if (tokenMeta) xhr.setRequestHeader('X-CSRF-TOKEN', tokenMeta.getAttribute('content'));
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.upload.onprogress = function(e) {
            if (e.lengthComputable && barEl) {
                const percent = Math.round((e.loaded / e.total) * 100);
                barEl.style.width = percent + '%';
                if (statusEl) statusEl.textContent = percent + '%';
            }
        };
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                pendingUploads = Math.max(0, pendingUploads - 1);
                if (xhr.status >= 200 && xhr.status < 300) {
                    try {
                        const resp = JSON.parse(xhr.responseText);
                        uploadedSomething = true;
                        latestDigitaisCount = resp.digitais_count ?? latestDigitaisCount;
                        if (resp.tipo === 'capa' && resp.digital_id) uploadedCoverDigitalId = resp.digital_id;
                        if (statusEl) statusEl.textContent = 'Concluído';
                        if (barEl) barEl.style.width = '100%';

                        // Atualizar dataset de IDs para permitir exclusão subsequente
                        const form = document.getElementById('attach-form');
                        if (form && resp.digital_id) {
                            if (resp.tipo === 'capa') {
                                form.dataset.coverId = String(resp.digital_id);
                                // Desabilitar imediatamente a entrada de capa e mostrar mensagem de existente
                                const capaInput = document.getElementById('attach-capa');
                                const capaExisting = document.getElementById('attach-capa-existing');
                                if (capaInput) capaInput.setAttribute('disabled', 'disabled');
                                if (capaExisting) capaExisting.classList.remove('hidden');
                            } else {
                                form.dataset.bookId = String(resp.digital_id);
                                // Desabilitar imediatamente a entrada de livro e mostrar mensagem de existente
                                const livroInput = document.getElementById('attach-livro');
                                const livroExisting = document.getElementById('attach-livro-existing');
                                if (livroInput) livroInput.setAttribute('disabled', 'disabled');
                                if (livroExisting) livroExisting.classList.remove('hidden');
                            }
                        }

                        // Atualizar contagem na célula e coluna "Upload Realizado"
                        if (form) {
                            const itemId = parseInt(form.dataset.itemId || '0', 10);
                            if (!Number.isNaN(itemId)) {
                                const cnt = document.getElementById('uploads-count-' + itemId);
                                if (cnt && resp.digitais_count !== undefined) {
                                    cnt.textContent = String(resp.digitais_count);
                                }
                                // Se for capa, atualizar imagem de capa imediatamente
                                if (resp.tipo === 'capa') {
                                    const coverEl = document.getElementById('cover-img-' + itemId);
                                    const srcUrl = resp.cover_url ? (resp.cover_url + '?t=' + Date.now()) : ('/biblioteca/digital/' + resp.digital_id + '/cover?t=' + Date.now());
                                    if (coverEl) {
                                        coverEl.src = srcUrl;
                                        coverEl.classList.remove('hidden');
                                    }
                                }
                                // Atualizar coluna de uploads
                                updateUploadRealizadoColumn(itemId);
                            }
                        }
                    } catch (e) {
                        if (statusEl) statusEl.textContent = 'Concluído';
                    }
                } else {
                    // Mostrar mensagem detalhada do backend, quando disponível
                    let msg = 'Falha no upload';
                    try {
                        const resp = JSON.parse(xhr.responseText);
                        if (resp && resp.error) msg = resp.error;
                        else if (resp && resp.message) msg = resp.message;
                        else if (resp && resp.errors) msg = Object.values(resp.errors).flat().join('\n');
                    } catch (_) {}
                    if (statusEl) statusEl.textContent = msg;
                    alert(msg);
                }
                setAttachSendEnabledIfReady();
            }
        };
        const formData = new FormData();
        formData.append('file', fileOrBlob);
        formData.append('tipo', tipo);
        xhr.send(formData);
    }

    window.deleteExisting = function(tipo) {
        const form = document.getElementById('attach-form');
        if (!form) return;
        const itemId = parseInt(form.dataset.itemId || '0', 10);
        const digitalId = tipo === 'capa' ? (form.dataset.coverId || '') : (form.dataset.bookId || '');
        if (!digitalId) return;
        const xhr = new XMLHttpRequest();
        xhr.open('DELETE', '/biblioteca/digital/' + digitalId, true);
        const tokenMeta = document.querySelector('meta[name="csrf-token"]');
        if (tokenMeta) xhr.setRequestHeader('X-CSRF-TOKEN', tokenMeta.getAttribute('content'));
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                if (xhr.status >= 200 && xhr.status < 300) {
                    try {
                        const resp = JSON.parse(xhr.responseText);
                        // Atualiza contagem
                        if (resp.digitais_count !== undefined) {
                            const cnt = document.getElementById('uploads-count-' + itemId);
                            if (cnt) cnt.textContent = String(resp.digitais_count);
                            latestDigitaisCount = resp.digitais_count;
                        }
                    } catch (e) {}
                    if (tipo === 'capa') {
                        const coverEl = document.getElementById('cover-img-' + itemId);
                        if (coverEl) {
                            const def = coverEl.dataset && coverEl.dataset.defaultCoverUrl ? coverEl.dataset.defaultCoverUrl : coverEl.getAttribute('data-default-cover-url');
                            if (def) coverEl.src = def;
                            coverEl.classList.remove('hidden');
                        }
                        form.dataset.coverId = '';
                        const capaInput = document.getElementById('attach-capa');
                        const capaExisting = document.getElementById('attach-capa-existing');
                        if (capaInput) capaInput.removeAttribute('disabled');
                        if (capaExisting) capaExisting.classList.add('hidden');
                        // Reset uploaded cover ID
                        uploadedCoverDigitalId = null;
                    } else {
                        form.dataset.bookId = '';
                        const livroInput = document.getElementById('attach-livro');
                        const livroExisting = document.getElementById('attach-livro-existing');
                        if (livroInput) livroInput.removeAttribute('disabled');
                        if (livroExisting) livroExisting.classList.add('hidden');
                        // Reset uploaded book ID
                        uploadedBookDigitalId = null;
                    }
                    
                    // Atualiza coluna "Upload Realizado"
                    updateUploadRealizadoColumn(itemId);
                    
                    // Reabilita o botão "Enviar" se necessário
                    setAttachSendEnabledIfReady();
                } else {
                    alert('Falha ao excluir o arquivo existente.');
                }
            }
        };
        xhr.send();
    }

    // Mantida função antiga se ainda usada em outros lugares
    function uploadCoverFile(itemId, file, uploadUrl, progressId, statusId) {
        const progressEl = document.getElementById(progressId);
        if (progressEl) progressEl.classList.remove('hidden');
        const statusEl = document.getElementById(statusId);
        if (statusEl) statusEl.textContent = 'Processando imagem...';
        resizeCoverToBlob(file).then(blob => {
            pendingUploads++;
            uploadModalFile(uploadUrl, blob, 'capa', progressId, statusId);
        }).catch(() => {
            if (statusEl) statusEl.textContent = 'Erro ao processar a capa';
        });
    }

    window.submitAttachForm = function() {
        const form = document.getElementById('attach-form');
        if (!form) return;
        const itemId = parseInt(form.dataset.itemId || '0', 10);

        // Se ainda há uploads em andamento, aguarde
        if (pendingUploads > 0) {
            alert('Aguarde a conclusão do upload antes de enviar.');
            return;
        }

        // Atualiza capa no card/tabela se houve upload de capa
        if (uploadedCoverDigitalId) {
            const el1 = document.getElementById('cover-img-' + itemId);
            if (el1) el1.src = '/biblioteca/digital/' + uploadedCoverDigitalId + '/cover?t=' + Date.now();
        }

        // Atualiza contagem
        if (latestDigitaisCount !== null) {
            const cnt = document.getElementById('uploads-count-' + itemId);
            if (cnt) cnt.textContent = String(latestDigitaisCount);
        }

        // Atualiza coluna "Upload Realizado"
        updateUploadRealizadoColumn(itemId);

        // Fechar modal
        closeModal('attach-item-modal');
    }

    window.submitCreateItem = function(event) {
        if (event) event.preventDefault();
        const form = document.getElementById('create-item-form');
        if (!form) return;
        const action = form.getAttribute('action');
        const formData = new FormData(form);
        const xhr = new XMLHttpRequest();
        xhr.open('POST', action, true);
        const tokenMeta = document.querySelector('meta[name="csrf-token"]');
        if (tokenMeta) xhr.setRequestHeader('X-CSRF-TOKEN', tokenMeta.getAttribute('content'));
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                if (xhr.status >= 200 && xhr.status < 300) {
                    try {
                        const resp = JSON.parse(xhr.responseText);
                        if (resp && resp.success && resp.item) {
                            // Inserir novo item na UI sem reload
                            addItemToUI(resp.item);
                            // Fechar modal e limpar formulário
                            closeModal('create-item-modal');
                            form.reset();
                            return;
                        }
                    } catch (e) {}
                    // Fallback em sucesso sem JSON: apenas fecha o modal
                    closeModal('create-item-modal');
                } else if (xhr.status === 422) {
                    // Mostrar mensagens simples de erro de validação
                    let msg = 'Erro de validação ao criar item.';
                    try {
                        const resp = JSON.parse(xhr.responseText);
                        if (resp && resp.errors) {
                            msg = Object.values(resp.errors).flat().join('\n');
                        } else if (resp && resp.message) {
                            msg = resp.message;
                        }
                    } catch (e) {}
                    alert(msg);
                } else {
                    alert('Falha ao criar o item.');
                }
            }
        };
        xhr.send(formData);
    }

    // Formata ISO date em "dd/mm/aaaa HH:MM"
    function formatDateTime(iso) {
        if (!iso) return '';
        try {
            const d = new Date(iso);
            const pad = (n) => String(n).padStart(2, '0');
            const dia = pad(d.getDate());
            const mes = pad(d.getMonth() + 1);
            const ano = d.getFullYear();
            const hora = pad(d.getHours());
            const min = pad(d.getMinutes());
            return `${dia}/${mes}/${ano} ${hora}:${min}`;
        } catch (_) { return ''; }
    }

    // Insere novo item na tabela desktop e card mobile
    function addItemToUI(item) {
        addItemToDesktop(item);
        addItemToMobile(item);
    }

    function addItemToDesktop(item) {
        const table = document.querySelector('#items-desktop table') || document.querySelector('table');
        if (!table) return;
        const tbody = table.querySelector('tbody') || table;
        const proto = tbody.querySelector('tr');
        if (!proto) return;
        const row = proto.cloneNode(true);

        // ID
        const idCell = row.querySelector('td');
        if (idCell) idCell.textContent = `#${item.id}`;

        // Capa e título
        const coverImg = row.querySelector(`[id^="cover-img-"]`);
        if (coverImg) {
            coverImg.id = `cover-img-${item.id}`;
            const defaultUrl = coverImg.getAttribute('data-default-cover-url') || '/images/book-cover-default.svg';
            coverImg.src = defaultUrl;
        }
        const titleEl = row.querySelector('.font-medium.text-gray-900');
        if (titleEl) titleEl.textContent = item.titulo || '';

        // Autores
        const authorsCell = row.querySelectorAll('td')[2];
        if (authorsCell) {
            const authorsText = authorsCell.querySelector('.text-sm.text-gray-500');
            if (item.autores) {
                if (authorsText) authorsText.textContent = item.autores;
                else authorsCell.innerHTML = `<div class=\"text-sm text-gray-500\">${item.autores}</div>`;
            } else {
                authorsCell.innerHTML = '<span class=\"text-gray-400\">-</span>';
            }
        }

        // Tipo
        const tipoBadge = row.querySelector('span.bg-gray-100.text-gray-800');
        if (tipoBadge) tipoBadge.textContent = (item.tipo || 'livro').charAt(0).toUpperCase() + (item.tipo || 'livro').slice(1);

        // Status
        const statusCell = row.querySelectorAll('td')[4];
        if (statusCell) {
            const status = (item.status || 'ativo').toLowerCase();
            const classesMap = {
                'ativo': 'bg-green-100 text-green-800',
                'disponivel': 'bg-green-100 text-green-800',
                'inativo': 'bg-gray-100 text-gray-800',
                'indisponivel': 'bg-red-100 text-red-800',
            };
            const badgeClass = classesMap[status] || 'bg-gray-100 text-gray-800';
            statusCell.innerHTML = `<div class=\"flex items-center gap-2 flex-wrap\"><span class=\"inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${badgeClass}\">${status.charAt(0).toUpperCase() + status.slice(1)}</span>${item.habilitado_emprestimo ? '' : '<span class=\"inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800\">Não habilitado</span>'}</div>`;
        }

        // Criado em
        const createdCell = row.querySelectorAll('td')[5];
        if (createdCell) {
            const span = createdCell.querySelector('span');
            const formatted = formatDateTime(item.created_at || item.created_at_formatted);
            if (span) span.textContent = formatted; else createdCell.innerHTML = `<span class=\"text-xs text-gray-500\">${formatted}</span>`;
        }

        // Uploads realizados (reset para "-")
        const uploadsCell = row.querySelectorAll('td')[6];
        if (uploadsCell) uploadsCell.innerHTML = '<span class=\"text-gray-400\">-</span>';

        // Botões Ver/Editar
        const viewBtn = row.querySelector('button[onclick^=\"openViewItem\"]');
        if (viewBtn) {
            viewBtn.dataset.id = item.id;
            viewBtn.dataset.titulo = item.titulo || '';
            viewBtn.dataset.autores = item.autores || '';
            viewBtn.dataset.editora = item.editora || '';
            viewBtn.dataset.ano = item.ano || '';
            viewBtn.dataset.isbn = item.isbn || '';
            viewBtn.dataset.tipo = item.tipo || 'livro';
            viewBtn.dataset.quantidade_fisica = item.quantidade_fisica || 0;
            viewBtn.dataset.status = item.status || 'ativo';
            viewBtn.dataset.created_at = formatDateTime(item.created_at || item.created_at_formatted);
            viewBtn.dataset.digitais_count = 0;
            viewBtn.dataset.digitais_types = '';
        }

        const editBtn = row.querySelector('button[onclick^=\"openEditItem\"]');
        if (editBtn) {
            editBtn.dataset.id = item.id;
            editBtn.dataset.updateUrl = `/biblioteca/item/${item.id}`;
            editBtn.dataset.titulo = item.titulo || '';
            editBtn.dataset.autores = item.autores || '';
            editBtn.dataset.editora = item.editora || '';
            editBtn.dataset.ano = item.ano || '';
            editBtn.dataset.isbn = item.isbn || '';
            editBtn.dataset.tipo = item.tipo || 'livro';
            editBtn.dataset.quantidade_fisica = item.quantidade_fisica || 0;
            editBtn.dataset.status = item.status || 'ativo';
            editBtn.dataset.habilitado_emprestimo = item.habilitado_emprestimo ? '1' : '0';
        }

        // Botão Anexar
        const attachBtn = row.querySelector('button[onclick^=\"openAttachModal\"]');
        if (attachBtn) {
            const uploadUrl = `/biblioteca/item/${item.id}/upload`;
            attachBtn.setAttribute('onclick', `openAttachModal(${item.id}, '${uploadUrl}', false, false, null, null)`);
        }

        // Inserir no topo
        if (tbody.firstChild) tbody.insertBefore(row, tbody.firstChild); else tbody.appendChild(row);
    }

    function addItemToMobile(item) {
        const container = document.getElementById('items-mobile') || document.querySelector('div.md\\:hidden.space-y-4');
        if (!container) return;
        const proto = container.querySelector('div.bg-white');
        if (!proto) return;
        const card = proto.cloneNode(true);

        // Capa
        const coverImg = card.querySelector(`[id^=\"cover-img-\"]`);
        if (coverImg) {
            coverImg.id = `cover-img-${item.id}`;
            coverImg.src = '/images/book-cover-default.svg';
        }

        // Título e autores
        const titleEl = card.querySelector('h3.font-semibold');
        if (titleEl) titleEl.textContent = item.titulo || '';
        const authorsP = Array.from(card.querySelectorAll('p')).find(p => p.classList.contains('text-sm') && p.classList.contains('text-gray-500'));
        if (authorsP) {
            if (item.autores) authorsP.textContent = item.autores; else authorsP.remove();
        }

        // Criado em
        const createdP = Array.from(card.querySelectorAll('p')).find(p => p.textContent.trim().startsWith('Criado em:'));
        const formatted = formatDateTime(item.created_at || item.created_at_formatted);
        if (createdP) createdP.textContent = formatted ? `Criado em: ${formatted}` : '';

        // Tipo badge
        const tipoBadge = Array.from(card.querySelectorAll('span')).find(s => s.classList.contains('bg-gray-100') && s.classList.contains('text-gray-800'));
        if (tipoBadge) tipoBadge.textContent = (item.tipo || 'livro').charAt(0).toUpperCase() + (item.tipo || 'livro').slice(1);

        // Status badges
        const statusWrap = Array.from(card.querySelectorAll('div')).find(d => d.classList.contains('flex') && d.classList.contains('items-center') && d.classList.contains('gap-2'));
        if (statusWrap) {
            const status = (item.status || 'ativo').toLowerCase();
            const classesMap = {
                'ativo': 'bg-green-100 text-green-800',
                'disponivel': 'bg-green-100 text-green-800',
                'inativo': 'bg-gray-100 text-gray-800',
                'indisponivel': 'bg-red-100 text-red-800',
            };
            statusWrap.innerHTML = `<span class=\"inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ${classesMap[status] || 'bg-gray-100 text-gray-800'}\">${status.charAt(0).toUpperCase() + status.slice(1)}</span>${item.habilitado_emprestimo ? '' : '<span class=\"inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800\">Não habilitado</span>'}`;
        }

        // Inserir no topo
        if (container.firstChild) container.insertBefore(card, container.firstChild); else container.appendChild(card);
    }

    // Função para atualizar a coluna "Upload Realizado"
    window.updateUploadRealizadoColumn = function(itemId) {
        if (!itemId) return;
        
        // Buscar informações atualizadas do item via AJAX
        const xhr = new XMLHttpRequest();
        xhr.open('GET', '/biblioteca/item/' + itemId + '/uploads', true);
        const tokenMeta = document.querySelector('meta[name="csrf-token"]');
        if (tokenMeta) xhr.setRequestHeader('X-CSRF-TOKEN', tokenMeta.getAttribute('content'));
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status >= 200 && xhr.status < 300) {
                try {
                    const resp = JSON.parse(xhr.responseText);
                    if (resp && resp.digitais) {
                        // Atualizar a coluna de uploads na tabela
                        const uploadCell = document.querySelector(`#uploads-count-${itemId}`);
                        if (uploadCell) {
                            const parentCell = uploadCell.closest('td');
                            if (parentCell && resp.digitais.length > 0) {
                                // Construir o HTML da coluna de uploads
                                let html = `<div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-sm text-gray-600"><span id="uploads-count-${itemId}">${resp.digitais.length}</span> arquivo(s)</span>
                                    <div class="flex items-center gap-2">`;
                                
                                resp.digitais.forEach(digital => {
                                    const iconClass = digital.tipo === 'pdf' ? 'file-pdf' : 
                                                    digital.tipo === 'epub' ? 'file-alt' : 
                                                    digital.tipo === 'mp3' ? 'file-audio' : 
                                                    digital.tipo === 'capa' ? 'image' : 'file-video';
                                    
                                    html += `<a href="/biblioteca/digital/${digital.id}/preview" class="inline-flex items-center gap-1 px-2 py-0.5 rounded border text-xs text-indigo-700 border-indigo-200 hover:bg-indigo-50" title="Preview ${digital.tipo.toUpperCase()}">
                                        <i class="fas fa-${iconClass}"></i>
                                        <span class="capitalize">${digital.tipo}</span>
                                    </a>`;
                                });
                                
                                html += '</div></div>';
                                parentCell.innerHTML = html;
                            } else if (parentCell && resp.digitais.length === 0) {
                                parentCell.innerHTML = '<span class="text-gray-400">-</span>';
                            }
                        }
                    }
                } catch (e) {
                    console.error('Erro ao atualizar coluna de uploads:', e);
                }
            }
        };
        
        xhr.send();
    }
</script>
@endsection