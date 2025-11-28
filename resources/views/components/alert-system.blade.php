@php
    use App\Services\AlertService;
    $alerts = AlertService::getAlerts();
    AlertService::clear(); // Limpa os alertas após obter
@endphp

<!-- Container de Alertas -->
<div id="alert-container" class="fixed top-4 right-4 z-50 space-y-2 max-w-sm">
    @if($alerts)
        @foreach($alerts as $alert)
            <div 
                id="{{ $alert['id'] }}"
                class="alert-item transform translate-x-full transition-all duration-300 ease-in-out"
                data-timeout="{{ $alert['timeout'] }}"
                data-dismissible="{{ $alert['dismissible'] ? 'true' : 'false' }}"
            >
                <div class="
                    @switch($alert['type'])
                        @case('success')
                            bg-green-50 border-l-4 border-green-400 text-green-800
                            @break
                        @case('error')
                            bg-red-50 border-l-4 border-red-400 text-red-800
                            @break
                        @case('warning')
                            bg-yellow-50 border-l-4 border-yellow-400 text-yellow-800
                            @break
                        @default
                            bg-blue-50 border-l-4 border-blue-400 text-blue-800
                    @endswitch
                    rounded-lg shadow-lg p-4 relative
                ">
                    <!-- Ícone do tipo de alerta -->
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            @switch($alert['type'])
                                @case('success')
                                    <i class="fas fa-check-circle text-green-400 text-lg"></i>
                                    @break
                                @case('error')
                                    <i class="fas fa-exclamation-circle text-red-400 text-lg"></i>
                                    @break
                                @case('warning')
                                    <i class="fas fa-exclamation-triangle text-yellow-400 text-lg"></i>
                                    @break
                                @default
                                    <i class="fas fa-info-circle text-blue-400 text-lg"></i>
                            @endswitch
                        </div>
                        
                        <div class="ml-3 flex-1">
                            <!-- Mensagem principal -->
                            <p class="text-sm font-medium">{{ $alert['message'] }}</p>
                            
                            <!-- Lista de erros (para validação) -->
                            @if(isset($alert['errors']) && is_array($alert['errors']))
                                <ul class="mt-2 text-xs list-disc list-inside space-y-1">
                                    @foreach($alert['errors'] as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            @endif
                            
                            <!-- Ações personalizadas -->
                            @if(isset($alert['actions']) && is_array($alert['actions']) && count($alert['actions']) > 0)
                                <div class="mt-3 flex flex-wrap gap-2">
                                    @foreach($alert['actions'] as $action)
                                        <button 
                                            class="px-3 py-1 text-xs rounded-md font-medium transition-colors duration-200 {{ $action['class'] ?? 'bg-gray-600 hover:bg-gray-700 text-white' }}"
                                            onclick="handleAlertAction('{{ $action['action'] }}', '{{ $action['url'] ?? '' }}', '{{ $alert['id'] }}')"
                                        >
                                            {{ $action['label'] }}
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        
                        <!-- Botão de fechar -->
                        @if($alert['dismissible'])
                            <div class="ml-3 flex-shrink-0">
                                <button 
                                    class="inline-flex text-gray-400 hover:text-gray-600 focus:outline-none transition-colors duration-200"
                                    onclick="dismissAlert('{{ $alert['id'] }}')"
                                >
                                    <i class="fas fa-times text-sm"></i>
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    @endif
</div>

<!-- Inclui o arquivo JavaScript do sistema de alertas -->
<script src="{{ asset('js/alert-system.js') }}"></script>
<script src="{{ asset('js/validation-handler.js') }}"></script>

<script>
    // Configurações específicas da aplicação
    document.addEventListener('DOMContentLoaded', function() {
        // Configurações globais do sistema de alertas
        if (window.alertSystem) {
            // Personalizar URLs de ação se necessário
            window.alertSystem.defaultUrls = {
                login: '{{ route("login") }}',
                home: '{{ route("dashboard") }}'
            };
        }
    });
</script>