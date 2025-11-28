@extends('layouts.app')

@section('title', 'Templates de Escalas')

@section('content')
    <x-breadcrumbs :items="[['title' => 'Funcionários', 'url' => route('funcionarios.index')], ['title' => 'Templates de Escalas', 'url' => '#']]" />
    <x-card>
        <!-- Header responsivo -->
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-6 space-y-3 sm:space-y-0">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Templates de Escalas</h1>
                <p class="text-gray-600 mt-1">Gerencie os templates de escalas semanais</p>
            </div>
            <div class="flex flex-col gap-2 space-y-2 sm:space-y-0 sm:space-x-2 md:flex-row">
                <x-button href="{{ route('funcionarios.templates.create', $funcionario->id) }}" color="primary" class="w-full sm:justify-center">
                    <i class="fas fa-plus mr-2"></i>
                    <span class="hidden md:inline">Novo Template</span>
                    <span class="md:hidden">Novo</span>
                </x-button>
            </div>
        </div>

        <!-- Mensagens de sucesso -->
         @if (session('success'))
             <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-md">
                 <div class="flex items-center">
                     <i class="fas fa-check-circle mr-2"></i>
                     {{ session('success') }}
                 </div>
             </div>
         @endif

         <!-- Mensagens de erro -->
         @if (session('error'))
             <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md">
                 <div class="flex items-center">
                     <i class="fas fa-exclamation-triangle mr-2"></i>
                     {{ session('error') }}
                 </div>
             </div>
         @endif

         <!-- Erros de validação -->
         @if ($errors->any())
             <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md">
                 <div class="flex items-center">
                     <i class="fas fa-exclamation-triangle mr-2"></i>
                     <div>
                         <ul class="list-disc list-inside">
                             @foreach ($errors->all() as $error)
                                 <li>{{ $error }}</li>
                             @endforeach
                         </ul>
                     </div>
                 </div>
             </div>
         @endif

         <!-- Header com estatísticas - responsivo -->
         <div class="mb-4">
             <h3 class="text-mobile-title text-gray-900 mb-3">Lista de Templates</h3>
             <!-- Desktop: horizontal -->
             <div class="hidden md:flex space-x-2">
                 <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                     <i class="fas fa-calendar-alt mr-1"></i>{{ $templates->total() }} Total
                 </span>
                 <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                     <i class="fas fa-check mr-1"></i>{{ $templates->where('ativo', true)->count() }} Ativos
                 </span>
                 <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                     <i class="fas fa-times mr-1"></i>{{ $templates->where('ativo', false)->count() }} Inativos
                 </span>
             </div>
             <!-- Mobile: grid 3 colunas -->
             <div class="md:hidden grid grid-cols-3 gap-2">
                 <span class="inline-flex items-center justify-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                     <i class="fas fa-calendar-alt mr-1"></i>{{ $templates->total() }}
                 </span>
                 <span class="inline-flex items-center justify-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                     <i class="fas fa-check mr-1"></i>{{ $templates->where('ativo', true)->count() }}
                 </span>
                 <span class="inline-flex items-center justify-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                     <i class="fas fa-times mr-1"></i>{{ $templates->where('ativo', false)->count() }}
                 </span>
             </div>
         </div>

         <!-- Filtros -->
         <x-collapsible-filter title="Filtros de Busca" action="{{ route('funcionarios.templates.index', $funcionario) }}" clear-route="{{ route('funcionarios.templates.index', $funcionario) }}">
             <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                 <x-filter-field name="search" label="Nome" type="text" placeholder="Digite o nome do template" value="{{ request('search') }}" />
                 <x-filter-field name="status" label="Status" type="select" :options="['1' => 'Ativo', '0' => 'Inativo']" empty-option="Todos os status" value="{{ request('status') }}" />
             </div>
         </x-collapsible-filter>

         <!-- Lista de Templates -->
         @if($templates->count() > 0)
             <!-- Desktop Layout (Table) -->
             <div class="hidden md:block">
                 <x-table :headers="['Nome do Template', 'Status', 'Dias Configurados', 'Criado em']" :actions="true">
                     @forelse($templates as $index => $template)
                         <x-table-row :striped="true" :index="$index">
                             <x-table-cell>
                                 <div class="flex items-center">
                                     <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-500 mr-3">
                                         <i class="fas fa-calendar-alt"></i>
                                     </div>
                                     <div>
                                         <div class="font-medium text-gray-900">{{ $template->nome_template }}</div>
                                         @if($template->ativo)
                                             <div class="text-green-500 text-xs">Template Ativo</div>
                                         @endif
                                     </div>
                                 </div>
                             </x-table-cell>
                             <x-table-cell>
                                 <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $template->ativo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                     {{ $template->ativo ? 'Ativo' : 'Inativo' }}
                                 </span>
                             </x-table-cell>
                             <x-table-cell>
                                 @php
                                     $diasConfigurados = $template->getDiasConfigurados();
                                 @endphp
                                 @if(count($diasConfigurados) > 0)
                                     <div class="flex flex-wrap gap-1">
                                         @foreach($diasConfigurados as $nomeDia => $dia)
                                             <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-blue-100 text-blue-800">{{ ucfirst($nomeDia) }}</span>
                                         @endforeach
                                     </div>
                                 @else
                                     <span class="text-gray-500 text-sm">Nenhum dia configurado</span>
                                 @endif
                             </x-table-cell>
                             <x-table-cell>{{ $template->created_at->format('d/m/Y H:i') }}</x-table-cell>
                             <x-table-cell align="right">
                                 <div class="flex justify-end space-x-2">
                                     <a href="{{ route('funcionarios.templates.show', [$funcionario, $template]) }}"
                                         class="text-indigo-600 hover:text-indigo-900" title="Visualizar">
                                         <i class="fas fa-eye"></i>
                                     </a>
                                     <a href="{{ route('funcionarios.templates.edit', [$funcionario, $template]) }}"
                                         class="text-yellow-600 hover:text-yellow-900" title="Editar">
                                         <i class="fas fa-edit"></i>
                                     </a>
                                     <button type="button" onclick="abrirModalCopiar({{ $template->id }}, '{{ $template->nome_template }}')"
                                         class="text-purple-600 hover:text-purple-900" title="Copiar">
                                         <i class="fas fa-copy"></i>
                                     </button>
                                     <form method="POST" action="{{ route('funcionarios.templates.toggle-ativo', [$funcionario, $template]) }}" class="inline">
                                         @csrf
                                         @method('PATCH')
                                         <button type="submit"
                                             class="{{ $template->ativo ? 'text-red-600 hover:text-red-900' : 'text-green-600 hover:text-green-900' }}"
                                             title="{{ $template->ativo ? 'Desativar' : 'Ativar' }}">
                                             <i class="fas {{ $template->ativo ? 'fa-ban' : 'fa-check' }}"></i>
                                         </button>
                                     </form>
                                     <button onclick="confirmDelete('{{ $template->id }}', '{{ $template->nome_template }}')"
                                         class="text-red-600 hover:text-red-900" title="Excluir">
                                         <i class="fas fa-trash"></i>
                                     </button>
                                 </div>
                             </x-table-cell>
                         </x-table-row>
                     @empty
                         <tr>
                             <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                 Nenhum template encontrado.
                             </td>
                         </tr>
                     @endforelse
                 </x-table>
             </div>

             <!-- Mobile Layout (Cards) -->
             <div class="block md:hidden space-y-4">
                 @forelse($templates as $template)
                     <x-card class="mobile-card-shadow rounded-xl border-0 overflow-hidden">
                         <!-- Card Header -->
                         <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-4 border-b border-gray-100">
                             <div class="flex items-center justify-between">
                                 <div class="flex items-center space-x-3">
                                     <!-- Avatar -->
                                     <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center flex-shrink-0">
                                         <i class="fas fa-calendar-alt text-white text-lg"></i>
                                     </div>
                                     <!-- Nome e Status -->
                                     <div class="min-w-0 flex-1">
                                         <h3 class="text-mobile-title font-semibold text-gray-900 truncate">{{ $template->nome_template }}</h3>
                                         <p class="text-mobile-subtitle text-gray-600 truncate">Template de Escala</p>
                                     </div>
                                 </div>
                                 <!-- Status Badge -->
                                 <span class="text-mobile-badge px-3 py-1.5 rounded-full font-medium flex-shrink-0 {{ $template->ativo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                     {{ $template->ativo ? 'Ativo' : 'Inativo' }}
                                 </span>
                             </div>
                         </div>

                         <!-- Card Body -->
                         <div class="p-4">
                             <!-- Informações em Grid -->
                             <div class="grid grid-cols-1 xs:grid-cols-2 gap-3 mb-4">
                                 <div class="flex items-center space-x-2">
                                     <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                         <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                         </svg>
                                     </div>
                                     <div class="min-w-0">
                                         <p class="text-mobile-caption text-gray-500 text-xs">Criado em</p>
                                         <p class="text-mobile-body text-gray-900 font-medium truncate">{{ $template->created_at->format('d/m/Y') }}</p>
                                     </div>
                                 </div>

                                 <div class="flex items-center space-x-2">
                                     <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                         <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                         </svg>
                                     </div>
                                     <div class="min-w-0">
                                         <p class="text-mobile-caption text-gray-500 text-xs">Dias</p>
                                         @php
                                             $diasConfigurados = $template->getDiasConfigurados();
                                         @endphp
                                         <p class="text-mobile-body text-gray-900 font-medium truncate">
                                             {{ count($diasConfigurados) > 0 ? count($diasConfigurados) . ' configurados' : 'Nenhum' }}
                                         </p>
                                     </div>
                                 </div>
                             </div>

                             <!-- Dias Configurados -->
                             @if(count($diasConfigurados) > 0)
                                 <div class="mb-4">
                                     <p class="text-mobile-caption text-gray-500 text-xs mb-2">Dias da Semana</p>
                                     <div class="flex flex-wrap gap-1">
                                         @foreach($diasConfigurados as $nomeDia => $dia)
                                             <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-blue-100 text-blue-800">{{ ucfirst($nomeDia) }}</span>
                                         @endforeach
                                     </div>
                                 </div>
                             @endif

                             <!-- Botões de Ação -->
                             <div class="grid grid-cols-2 gap-2 mb-3">
                                 <a href="{{ route('funcionarios.templates.show', [$funcionario, $template]) }}"
                                     class="touch-button bg-blue-500 hover:bg-blue-600 active:bg-blue-700 text-white text-center py-3 px-4 rounded-lg text-mobile-button font-medium transition-all duration-200 focus-ring flex items-center justify-center space-x-2">
                                     <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                     </svg>
                                     <span class="hidden xs:inline">Ver</span>
                                 </a>
                                 <a href="{{ route('funcionarios.templates.edit', [$funcionario, $template]) }}"
                                     class="touch-button bg-yellow-500 hover:bg-yellow-600 active:bg-yellow-700 text-white text-center py-3 px-4 rounded-lg text-mobile-button font-medium transition-all duration-200 focus-ring flex items-center justify-center space-x-2">
                                     <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                     </svg>
                                     <span class="hidden xs:inline">Editar</span>
                                 </a>
                             </div>
                             <div class="grid grid-cols-2 gap-2">
                                 <button type="button" onclick="abrirModalCopiar({{ $template->id }}, '{{ $template->nome_template }}')"
                                     class="touch-button bg-purple-500 hover:bg-purple-600 active:bg-purple-700 text-white py-3 px-4 rounded-lg text-mobile-button font-medium transition-all duration-200 focus-ring flex items-center justify-center space-x-2">
                                     <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                     </svg>
                                     <span class="hidden xs:inline">Copiar</span>
                                 </button>
                                 <form method="POST" action="{{ route('funcionarios.templates.toggle-ativo', [$funcionario, $template]) }}">
                                     @csrf
                                     @method('PATCH')
                                     <button type="submit"
                                         class="touch-button w-full {{ $template->ativo ? 'bg-red-500 hover:bg-red-600 active:bg-red-700' : 'bg-green-500 hover:bg-green-600 active:bg-green-700' }} text-white py-3 px-4 rounded-lg text-mobile-button font-medium transition-all duration-200 focus-ring flex items-center justify-center space-x-2">
                                         @if($template->ativo)
                                             <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728"></path>
                                             </svg>
                                             <span class="hidden xs:inline">Inativar</span>
                                         @else
                                             <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                             </svg>
                                             <span class="hidden xs:inline">Ativar</span>
                                         @endif
                                     </button>
                                 </form>
                             </div>
                         </div>
                     </x-card>
                 @empty
                     <!-- Estado Vazio Melhorado -->
                     <div class="bg-white rounded-xl border border-gray-200 p-8 text-center">
                         <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                             <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                             </svg>
                         </div>
                         <h3 class="text-lg font-semibold text-gray-900 mb-2">Nenhum template encontrado</h3>
                         <p class="text-gray-500 mb-6">Não há templates cadastrados ou que correspondam aos filtros aplicados. Tente ajustar os filtros ou adicione um novo template.</p>
                         <a href="{{ route('funcionarios.templates.create', $funcionario) }}"
                             class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors duration-200">
                             <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                             </svg>
                             Adicionar Template
                         </a>
                     </div>
                 @endforelse
             </div>

             <div class="mt-4">
                 {{ $templates->links('components.pagination') }}
             </div>
         @else
             <!-- Estado Vazio Desktop -->
             <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
                 <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                     <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                     </svg>
                 </div>
                 <h3 class="text-lg font-semibold text-gray-900 mb-2">Nenhum template encontrado</h3>
                 <p class="text-gray-500 mb-6">Não há templates cadastrados para este funcionário. Crie o primeiro template para começar.</p>
                 <a href="{{ route('funcionarios.templates.create', $funcionario) }}"
                     class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors duration-200">
                     <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                     </svg>
                     Criar Primeiro Template
                 </a>
             </div>
         @endif
     </x-card>


<!-- Modal para Copiar Template -->
<div id="modalCopiarTemplate" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Copiar Template</h3>
                <button type="button" onclick="fecharModalCopiar()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <form id="formCopiarTemplate" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Template:</label>
                    <p id="nomeTemplateOriginal" class="text-sm text-gray-600 bg-gray-50 p-2 rounded"></p>
                </div>
                
                <div class="mb-4">
                        <label for="funcionarios_destino" class="block text-sm font-medium text-gray-700 mb-2">Copiar para funcionários:</label>
                        <div class="border border-gray-300 rounded-md max-h-48 overflow-y-auto bg-white">
                            <div class="p-2">
                                <label class="flex items-center p-2 hover:bg-gray-50 cursor-pointer">
                                    <input type="checkbox" id="select_all" class="mr-2 rounded" onchange="toggleAllFuncionarios()">
                                    <span class="font-medium text-blue-600">Selecionar todos</span>
                                </label>
                                <hr class="my-1">
                                @foreach($funcionarios as $func)
                                    <label class="flex items-center p-2 hover:bg-gray-50 cursor-pointer">
                                        <input type="checkbox" name="funcionarios_destino[]" value="{{ $func->id }}" class="mr-2 rounded funcionario-checkbox">
                                        <span>{{ $func->nome }} {{ $func->sobrenome }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        <p class="text-sm text-gray-500 mt-1">Selecione um ou mais funcionários para copiar o template</p>
                    </div>
                
                <div class="mb-4">
                    <label for="nome_template_novo" class="block text-sm font-medium text-gray-700 mb-2">Nome do novo template:</label>
                    <input type="text" name="nome_template" id="nome_template_novo" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="fecharModalCopiar()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition-colors">
                        Cancelar
                    </button>
                    <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition-colors">
                        Copiar Template
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let templateIdParaCopiar = null;

function abrirModalCopiar(templateId, nomeTemplate) {
    templateIdParaCopiar = templateId;
    document.getElementById('nomeTemplateOriginal').textContent = nomeTemplate;
    document.getElementById('nome_template_novo').value = nomeTemplate + ' - Cópia';
    
    // Configurar a action do formulário com o template ID na URL
    const form = document.getElementById('formCopiarTemplate');
    form.action = `/funcionarios/{{ $funcionario->id }}/templates/${templateId}/copiar`;
    
    document.getElementById('modalCopiarTemplate').classList.remove('hidden');
}

function fecharModalCopiar() {
    document.getElementById('modalCopiarTemplate').classList.add('hidden');
    templateIdParaCopiar = null;
    // Reset checkboxes
    document.getElementById('select_all').checked = false;
    document.querySelectorAll('.funcionario-checkbox').forEach(checkbox => {
        checkbox.checked = false;
    });
}

function toggleAllFuncionarios() {
    const selectAll = document.getElementById('select_all');
    const funcionarioCheckboxes = document.querySelectorAll('.funcionario-checkbox');
    
    funcionarioCheckboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
}

// Validação do formulário
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('formCopiarTemplate').addEventListener('submit', function(e) {
        const checkboxes = document.querySelectorAll('.funcionario-checkbox:checked');
        if (checkboxes.length === 0) {
            e.preventDefault();
            alert('Por favor, selecione pelo menos um funcionário para copiar o template.');
            return false;
        }
    });
});

// Fechar modal ao clicar fora dele
document.getElementById('modalCopiarTemplate').addEventListener('click', function(e) {
    if (e.target === this) {
        fecharModalCopiar();
    }
});
</script>

@endsection

@push('scripts')
<script>
    function confirmDelete(templateId, templateName) {
        if (confirm(`Tem certeza que deseja excluir o template "${templateName}"? Esta ação não pode ser desfeita.`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `{{ route('funcionarios.templates.index', $funcionario) }}/${templateId}`;
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            
            const methodField = document.createElement('input');
            methodField.type = 'hidden';
            methodField.name = '_method';
            methodField.value = 'DELETE';
            
            form.appendChild(csrfToken);
            form.appendChild(methodField);
            document.body.appendChild(form);
            form.submit();
        }
    }
</script>
@endpush