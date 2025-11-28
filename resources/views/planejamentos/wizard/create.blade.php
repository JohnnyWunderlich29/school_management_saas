@extends('layouts.app')

@section('content')
<x-breadcrumbs :items="[
    ['title' => 'Planejamentos', 'url' => route('planejamentos.index')],
    ['title' => isset($planejamento) ? 'Editar Planejamento' : 'Novo Planejamento', 'url' => '#']
]" />

<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">
            {{ isset($planejamento) ? 'Editar Planejamento' : 'Novo Planejamento' }}
        </h1>
        <p class="text-gray-600 mt-1">
            {{ isset($planejamento) ? 'Atualize as informações do planejamento' : 'Siga as etapas para criar um novo planejamento de aula' }}
        </p>
    </div>

    <!-- Stepper -->
    <div class="mb-8">
        <nav aria-label="Progress">
            <ol class="flex items-center">
                @php
                    $steps = [
                        1 => ['title' => 'Configuração Básica', 'icon' => 'fas fa-cog'],
                        2 => ['title' => 'Unidade e Turno', 'icon' => 'fas fa-school'],
                        3 => ['title' => 'Turma e Disciplina', 'icon' => 'fas fa-users'],
                        4 => ['title' => 'Período e Duração', 'icon' => 'fas fa-calendar'],
                        5 => ['title' => 'Conteúdo Pedagógico', 'icon' => 'fas fa-book'],
                        6 => ['title' => 'Revisão e Finalização', 'icon' => 'fas fa-check']
                    ];
                    $currentStep = request('step', 1);
                @endphp

                @foreach($steps as $stepNumber => $step)
                    <li class="relative {{ $loop->last ? '' : 'pr-8 sm:pr-20' }}">
                        @if(!$loop->last)
                            <div class="absolute inset-0 flex items-center" aria-hidden="true">
                                <div class="h-0.5 w-full {{ $stepNumber < $currentStep ? 'bg-blue-600' : 'bg-gray-200' }}"></div>
                            </div>
                        @endif
                        
                        <div class="relative flex items-center justify-center w-8 h-8 {{ $stepNumber <= $currentStep ? 'bg-blue-600' : 'bg-gray-200' }} rounded-full">
                            @if($stepNumber < $currentStep)
                                <i class="fas fa-check text-white text-sm"></i>
                            @elseif($stepNumber == $currentStep)
                                <i class="{{ $step['icon'] }} text-white text-sm"></i>
                            @else
                                <span class="text-gray-500 text-sm font-medium">{{ $stepNumber }}</span>
                            @endif
                        </div>
                        
                        <div class="mt-2">
                            <span class="text-xs font-medium {{ $stepNumber <= $currentStep ? 'text-blue-600' : 'text-gray-500' }}">
                                {{ $step['title'] }}
                            </span>
                        </div>
                    </li>
                @endforeach
            </ol>
        </nav>
    </div>

    <!-- Formulário -->
    <form method="POST" action="{{ isset($planejamento) ? route('planejamentos.update', $planejamento) : route('planejamentos.store') }}" id="planejamento-form">
        @csrf
        @if(isset($planejamento))
            @method('PUT')
        @endif
        
        <input type="hidden" name="step" value="{{ $currentStep }}" id="current-step">

        <x-card>
            <!-- Conteúdo da Etapa -->
            <div id="step-content">
                @include('planejamentos.wizard.steps.step-' . $currentStep, [
                    'planejamento' => $planejamento ?? null
                ])
            </div>

            <!-- Navegação -->
            <div class="border-t border-gray-200 pt-6 mt-8">
                <div class="flex justify-between">
                    <div>
                        @if($currentStep > 1)
                            <x-button type="button" color="secondary" onclick="previousStep()" class="inline-flex items-center">
                                <i class="fas fa-arrow-left mr-2"></i>
                                <span class="hidden md:block">Anterior</span>
                            </x-button>
                        @endif
                    </div>

                    <div class="flex gap-3">
                        <!-- Próximo/Finalizar -->
                        @if($currentStep < 6)
                            <x-button type="button" color="primary" onclick="nextStep()" class="inline-flex items-center">
                                Próximo
                                <i class="fas fa-arrow-right ml-2"></i>
                            </x-button>
                        @else
                            <x-button type="submit" color="success" class="inline-flex items-center">
                                <i class="fas fa-check mr-2"></i>
                                {{ isset($planejamento) ? 'Atualizar Planejamento' : 'Finalizar Planejamento' }}
                            </x-button>
                        @endif
                    </div>
                </div>
            </div>
        </x-card>
    </form>
</div>

@push('scripts')
<script>
let currentStep = {{ $currentStep }};
const maxSteps = 6;

function nextStep() {
    if (validateCurrentStep() && currentStep < maxSteps) {
        currentStep++;
        updateStep();
    }
}

function previousStep() {
    if (currentStep > 1) {
        currentStep--;
        updateStep();
    }
}

function updateStep() {
    document.getElementById('current-step').value = currentStep;
    
    // Atualizar URL sem recarregar a página
    const url = new URL(window.location);
    url.searchParams.set('step', currentStep);
    window.history.pushState({}, '', url);
    
    // Carregar conteúdo da nova etapa via AJAX
    loadStepContent();
}

function loadStepContent() {
    const formData = new FormData(document.getElementById('planejamento-form'));
    formData.append('ajax', '1');
    
    fetch(window.location.pathname + '?step=' + currentStep, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.text())
    .then(html => {
        document.getElementById('step-content').innerHTML = html;
        updateStepperUI();
    })
    .catch(error => {
        console.error('Erro ao carregar etapa:', error);
        // Fallback: recarregar a página
        window.location.reload();
    });
}

function updateStepperUI() {
    // Atualizar visual do stepper
    document.querySelectorAll('[data-step]').forEach(element => {
        const stepNumber = parseInt(element.dataset.step);
        const isActive = stepNumber <= currentStep;
        const isCurrent = stepNumber === currentStep;
        
        element.classList.toggle('active', isActive);
        element.classList.toggle('current', isCurrent);
    });
}

function validateCurrentStep() {
    const requiredFields = document.querySelectorAll('#step-content [required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('border-red-500');
            isValid = false;
        } else {
            field.classList.remove('border-red-500');
        }
    });
    
    if (!isValid) {
        window.AlertService.error('Por favor, preencha todos os campos obrigatórios antes de continuar.');
    }
    
    return isValid;
}

</script>
@endpush
@endsection