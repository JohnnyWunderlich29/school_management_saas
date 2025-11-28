@props([
    'module',
    'status' => 'not_contracted',
    'statusColor' => 'gray',
    'statusDescription' => 'Não contratado'
])

<div class="bg-white rounded-lg shadow-md border border-gray-200 hover:shadow-lg transition-shadow duration-200 overflow-hidden">
    <!-- Header do Card -->
    <div class="p-4 border-b border-gray-100">
        <div class="flex items-start justify-between">
            <div class="flex items-center space-x-3">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center text-white text-xl" 
                         style="background-color: {{ $module['color'] ?? '#6B7280' }}">
                        <i class="{{ $module['icon'] ?? 'fas fa-puzzle-piece' }}"></i>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-lg font-semibold text-gray-900 truncate">
                        {{ $module['display_name'] }}
                    </h3>
                    <p class="text-sm text-gray-500 mt-1">
                        {{ $module['category_display'] ?? 'Geral' }}
                    </p>
                </div>
            </div>
            
            <!-- Status Badge -->
            <div class="flex-shrink-0">
                @if($status === 'active')
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        <span class="w-1.5 h-1.5 bg-green-400 rounded-full mr-1.5"></span>
                        {{ $statusDescription }}
                    </span>
                @elseif($status === 'inactive')
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                        <span class="w-1.5 h-1.5 bg-gray-400 rounded-full mr-1.5"></span>
                        {{ $statusDescription }}
                    </span>
                @elseif($status === 'expired')
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                        <span class="w-1.5 h-1.5 bg-red-400 rounded-full mr-1.5"></span>
                        {{ $statusDescription }}
                    </span>
                @elseif($status === 'expiring_soon')
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                        <span class="w-1.5 h-1.5 bg-yellow-400 rounded-full mr-1.5"></span>
                        {{ $statusDescription }}
                    </span>
                @else
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        Disponível
                    </span>
                @endif
            </div>
        </div>
    </div>

    <!-- Conteúdo do Card -->
    <div class="p-4">
        <!-- Descrição -->
        <p class="text-sm text-gray-600 mb-4 line-clamp-2">
            {{ $module['description'] }}
        </p>

        <!-- Features -->
        @if(!empty($module['features']))
            <div class="mb-4">
                <h4 class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Recursos inclusos</h4>
                <ul class="space-y-1">
                    @foreach(array_slice($module['features'], 0, 3) as $feature)
                        <li class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-check text-green-500 text-xs mr-2"></i>
                            {{ $feature }}
                        </li>
                    @endforeach
                    @if(count($module['features']) > 3)
                        <li class="text-xs text-gray-500 italic">
                            +{{ count($module['features']) - 3 }} recursos adicionais
                        </li>
                    @endif
                </ul>
            </div>
        @endif

        <!-- Preço -->
        <div class="flex items-center justify-between mb-4">
            <div>
                <span class="text-2xl font-bold text-gray-900">
                    {{ $module['formatted_price'] }}
                </span>
                <span class="text-sm text-gray-500">/mês</span>
            </div>
            @if($module['is_core'] ?? false)
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                    <i class="fas fa-star text-xs mr-1"></i>
                    Essencial
                </span>
            @endif
        </div>

        <!-- Ações -->
        <div class="space-y-2">
            @if(!($module['is_contracted'] ?? false))
                <!-- Módulo não contratado -->
                <button type="button" 
                        onclick="contractModule({{ $module['id'] }})"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition-colors duration-200 flex items-center justify-center">
                    <i class="fas fa-plus mr-2"></i>
                    Contratar Módulo
                </button>
            @else
                <!-- Módulo contratado -->
                <div class="flex space-x-2">
                    @if($module['is_active'] ?? false)
                        @if(!($module['is_core'] ?? false))
                            <button type="button" 
                                    onclick="toggleModule({{ $module['id'] }})"
                                    class="flex-1 bg-yellow-600 hover:bg-yellow-700 text-white font-medium py-2 px-4 rounded-md transition-colors duration-200 flex items-center justify-center">
                                <i class="fas fa-pause mr-2"></i>
                                Desativar
                            </button>
                        @endif
                        @if(!($module['is_core'] ?? false))
                            <button type="button" 
                                    onclick="cancelModule({{ $module['id'] }})"
                                    class="flex-1 bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-md transition-colors duration-200 flex items-center justify-center">
                                <i class="fas fa-times mr-2"></i>
                                Cancelar
                            </button>
                        @endif
                    @else
                        <button type="button" 
                                onclick="toggleModule({{ $module['id'] }})"
                                class="flex-1 bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-md transition-colors duration-200 flex items-center justify-center">
                            <i class="fas fa-play mr-2"></i>
                            Ativar
                        </button>
                        @if(!($module['is_core'] ?? false))
                            <button type="button" 
                                    onclick="cancelModule({{ $module['id'] }})"
                                    class="flex-1 bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-md transition-colors duration-200 flex items-center justify-center">
                                <i class="fas fa-times mr-2"></i>
                                Cancelar
                            </button>
                        @endif
                    @endif
                </div>
            @endif
        </div>
    </div>

    <!-- Footer com informações adicionais -->
    @if(($module['is_contracted'] ?? false) && isset($module['contracted_at']))
        <div class="px-4 py-3 bg-gray-50 border-t border-gray-100">
            <div class="flex items-center justify-between text-xs text-gray-500">
                <span>Contratado em: {{ \Carbon\Carbon::parse($module['contracted_at'])->format('d/m/Y') }}</span>
                @if(isset($module['expires_at']) && $module['expires_at'])
                    <span>Expira em: {{ \Carbon\Carbon::parse($module['expires_at'])->format('d/m/Y') }}</span>
                @else
                    <span>Sem expiração</span>
                @endif
            </div>
        </div>
    @endif
</div>