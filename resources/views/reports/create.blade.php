@extends('layouts.app')

@section('title', 'Novo Relatório')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Novo Relatório</h1>
            <p class="text-gray-600 mt-1">Configure e gere um novo relatório</p>
        </div>
        <x-button href="{{ route('reports.index') }}" color="secondary">
            <i class="fas fa-arrow-left mr-1"></i> Voltar
        </x-button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
        <div class="lg:col-span-3">
            <!-- Formulário de Criação -->
            <x-card>
                <x-slot name="header">
                    <h3 class="text-lg font-semibold text-gray-900">Configurações do Relatório</h3>
                </x-slot>
                <form id="reportForm" class="space-y-6">
                    @csrf
                    
                    <!-- Informações Básicas -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input 
                                type="text" 
                                name="name" 
                                label="Nome do Relatório" 
                                required 
                                placeholder="Digite o nome do relatório"
                            />
                            <div class="invalid-feedback"></div>
                        </div>
                        
                        <div>
                            <x-select 
                                name="type" 
                                label="Tipo de Relatório" 
                                required
                                placeholder="Selecione o tipo"
                            >
                                <option value="attendance">Relatório de Presenças</option>
                                <option value="schedule">Relatório de Escalas</option>
                                <option value="performance">Relatório de Performance</option>
                                <option value="financial">Relatório Financeiro</option>
                            </x-select>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                    <x-textarea 
                        name="description" 
                        label="Descrição" 
                        rows="3" 
                        placeholder="Descrição opcional do relatório"
                    />

                    <!-- Período -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input 
                                type="date" 
                                name="date_from" 
                                label="Data Inicial" 
                                required
                            />
                            <div class="invalid-feedback"></div>
                        </div>
                        
                        <div>
                            <x-input 
                                type="date" 
                                name="date_to" 
                                label="Data Final" 
                                required
                            />
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                    <!-- Filtros Específicos -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-select 
                            name="sala_id" 
                            label="Sala (Opcional)"
                            placeholder="Todas as salas"
                        >
                            @foreach($salas as $sala)
                                <option value="{{ $sala->id }}">{{ $sala->nome_completo }}</option>
                            @endforeach
                        </x-select>
                        
                        <x-select 
                            name="funcionario_id" 
                            label="Funcionário (Opcional)"
                            placeholder="Todos os funcionários"
                        >
                            @foreach($funcionarios as $funcionario)
                                <option value="{{ $funcionario->id }}">{{ $funcionario->nome }} {{ $funcionario->sobrenome }}</option>
                            @endforeach
                        </x-select>
                    </div>

                    <!-- Formato de Saída -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Formato de Saída
                            <span class="text-red-600">*</span>
                        </label>
                        <div class="flex flex-wrap gap-4">
                            <label class="flex items-center space-x-2 cursor-pointer">
                                <input type="radio" name="format" value="pdf" checked class="text-indigo-600 focus:ring-indigo-500">
                                <span class="flex items-center space-x-1">
                                    <i class="fas fa-file-pdf text-red-500"></i>
                                    <span>PDF</span>
                                </span>
                            </label>
                            <label class="flex items-center space-x-2 cursor-pointer">
                                <input type="radio" name="format" value="excel" class="text-indigo-600 focus:ring-indigo-500">
                                <span class="flex items-center space-x-1">
                                    <i class="fas fa-file-excel text-green-500"></i>
                                    <span>Excel</span>
                                </span>
                            </label>
                            <label class="flex items-center space-x-2 cursor-pointer">
                                <input type="radio" name="format" value="csv" class="text-indigo-600 focus:ring-indigo-500">
                                <span class="flex items-center space-x-1">
                                    <i class="fas fa-file-csv text-blue-500"></i>
                                    <span>CSV</span>
                                </span>
                            </label>
                        </div>
                    </div>

                    <!-- Botões -->
                    <div class="flex justify-end space-x-4 pt-4 border-t">
                        <x-button type="button" color="secondary" onclick="window.location.href='{{ route('reports.index') }}'">
                            <i class="fas fa-times mr-1"></i> Cancelar
                        </x-button>
                        <x-button type="submit" color="primary" id="submitBtn">
                            <i class="fas fa-chart-bar mr-1"></i> Gerar Relatório
                        </x-button>
                    </div>
                </form>
            </x-card>
        </div>

        <div class="lg:col-span-1">
            <!-- Informações sobre Tipos de Relatório -->
            <x-card>
                <x-slot name="header">
                    <h3 class="text-lg font-semibold text-gray-900">Tipos de Relatório</h3>
                </x-slot>
                
                <div class="space-y-4">
                    <div>
                        <h4 class="font-semibold text-gray-900 flex items-center space-x-2">
                            <i class="fas fa-user-check text-blue-500"></i>
                            <span>Relatório de Presenças</span>
                        </h4>
                        <p class="text-sm text-gray-600">Dados detalhados sobre presenças e ausências dos alunos, incluindo horários de entrada e saída.</p>
                    </div>
                    
                    <div>
                        <h4 class="font-semibold text-gray-900 flex items-center space-x-2">
                            <i class="fas fa-calendar-alt text-yellow-500"></i>
                            <span>Relatório de Escalas</span>
                        </h4>
                        <p class="text-sm text-gray-600">Informações sobre escalas de trabalho dos funcionários, horas trabalhadas e distribuição por salas.</p>
                    </div>
                    
                    <div>
                        <h4 class="font-semibold text-gray-900 flex items-center space-x-2">
                            <i class="fas fa-chart-line text-green-500"></i>
                            <span>Relatório de Performance</span>
                        </h4>
                        <p class="text-sm text-gray-600">Análise de performance geral do sistema, incluindo estatísticas e indicadores de produtividade.</p>
                    </div>
                    
                    <div>
                        <h4 class="font-semibold text-gray-900 flex items-center space-x-2">
                            <i class="fas fa-dollar-sign text-purple-500"></i>
                            <span>Relatório Financeiro</span>
                        </h4>
                        <p class="text-sm text-gray-600">Estimativas de custos operacionais baseadas em horas trabalhadas e recursos utilizados.</p>
                    </div>
                </div>
            </x-card>

            <!-- Dicas -->
            <x-card class="mt-6">
                <x-slot name="header">
                    <h3 class="text-lg font-semibold text-gray-900">Dicas</h3>
                </x-slot>
                
                <div class="space-y-3">
                    <div class="flex items-start space-x-2">
                        <i class="fas fa-lightbulb text-yellow-500 mt-0.5"></i>
                        <p class="text-sm text-gray-600">Use nomes descritivos para facilitar a identificação posterior.</p>
                    </div>
                    <div class="flex items-start space-x-2">
                        <i class="fas fa-lightbulb text-yellow-500 mt-0.5"></i>
                        <p class="text-sm text-gray-600">Períodos menores geram relatórios mais rápidos.</p>
                    </div>
                    <div class="flex items-start space-x-2">
                        <i class="fas fa-lightbulb text-yellow-500 mt-0.5"></i>
                        <p class="text-sm text-gray-600">Use filtros para relatórios mais específicos.</p>
                    </div>
                    <div class="flex items-start space-x-2">
                        <i class="fas fa-lightbulb text-yellow-500 mt-0.5"></i>
                        <p class="text-sm text-gray-600">Você será notificado quando o relatório estiver pronto.</p>
                    </div>
                </div>
            </x-card>
        </div>
    </div>
</div>

<!-- Notificação de Sucesso -->
<div id="successNotification" class="fixed top-4 right-4 z-50 hidden">
    <div class="bg-green-50 border border-green-200 rounded-lg p-4 shadow-lg max-w-md">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <i class="fas fa-check-circle text-green-400 text-xl"></i>
            </div>
            <div class="ml-3 flex-1">
                <h3 class="text-sm font-medium text-green-800">Relatório Gerado com Sucesso!</h3>
                <p class="mt-1 text-sm text-green-700">Seu relatório está pronto para download.</p>
                <div class="mt-3 flex space-x-2">
                    <button id="downloadBtn" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        <i class="fas fa-download mr-1"></i>
                        Baixar Agora
                    </button>
                    <a href="{{ route('reports.index') }}" class="inline-flex items-center px-3 py-1.5 border border-green-300 text-xs font-medium rounded text-green-700 bg-white hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        <i class="fas fa-list mr-1"></i>
                        Ver Todos
                    </a>
                </div>
            </div>
            <div class="ml-4 flex-shrink-0">
                <button id="closeNotification" class="inline-flex text-green-400 hover:text-green-600 focus:outline-none focus:text-green-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Definir data padrão (último mês)
    const today = new Date();
    const lastMonth = new Date(today.getFullYear(), today.getMonth() - 1, today.getDate());
    
    $('#date_from').val(lastMonth.toISOString().split('T')[0]);
    $('#date_to').val(today.toISOString().split('T')[0]);
    
    // Validação de datas
    $('#date_from, #date_to').change(function() {
        const dateFrom = new Date($('#date_from').val());
        const dateTo = new Date($('#date_to').val());
        
        if (dateFrom > dateTo) {
            $('#date_to').val($('#date_from').val());
        }
    });
    
    // Submissão do formulário
    $('#reportForm').submit(function(e) {
        e.preventDefault();
        
        const submitBtn = $('#submitBtn');
        const originalText = submitBtn.html();
        
        // Desabilitar botão e mostrar loading
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Gerando...');
        
        // Limpar erros anteriores
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        
        $.ajax({
            url: '{{ route("reports.store") }}',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    showSuccessNotification();
                    
                    // Auto-hide notification after 10 seconds
                    setTimeout(function() {
                        hideSuccessNotification();
                    }, 10000);
                } else {
                    alert('Erro ao criar relatório: ' + (response.message || 'Erro desconhecido'));
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    // Erros de validação
                    const errors = xhr.responseJSON.errors;
                    
                    Object.keys(errors).forEach(function(field) {
                        const input = $(`[name="${field}"]`);
                        input.addClass('is-invalid');
                        input.siblings('.invalid-feedback').text(errors[field][0]);
                    });
                } else {
                    alert('Erro ao criar relatório. Tente novamente.');
                }
            },
            complete: function() {
                // Reabilitar botão
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });
    
    // Atualizar descrição baseada no tipo
    $('#type').change(function() {
        const type = $(this).val();
        const descriptions = {
            'attendance': 'Relatório detalhado de presenças e ausências dos alunos',
            'schedule': 'Relatório de escalas e horários dos funcionários',
            'performance': 'Análise de performance e indicadores do sistema',
            'financial': 'Relatório de custos operacionais estimados'
        };
        
        if (descriptions[type]) {
            $('#description').val(descriptions[type]);
        }
    });
    
    // Função para mostrar notificação de sucesso
    function showSuccessNotification() {
        $('#successNotification').removeClass('hidden').addClass('animate-pulse');
        setTimeout(function() {
            $('#successNotification').removeClass('animate-pulse');
        }, 500);
    }
    
    // Função para esconder notificação
    function hideSuccessNotification() {
        $('#successNotification').addClass('hidden');
    }
    
    // Event listener para fechar notificação
    $('#closeNotification').click(function() {
        hideSuccessNotification();
    });
    
    // Event listener para o botão de download
     $('#downloadBtn').click(function() {
         // Aqui você pode adicionar a lógica de download
         alert('Iniciando download do relatório...');
         hideSuccessNotification();
     });
});
</script>
@endpush