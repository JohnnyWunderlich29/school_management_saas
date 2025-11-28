@extends('layouts.app')

@section('content')
    <x-breadcrumbs :items="[['title' => 'Administração', 'url' => '#'], ['title' => 'Configurações', 'url' => '#']]" />
    <x-card>
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-gray-900">Configurações do Sistema</h1>
                <p class="mt-1 text-sm text-gray-600">Gerencie modalidades, grupos, turnos e disciplinas</p>
            </div>

            <!-- Tabs Navigation -->
            <div class="border-b border-gray-200 mb-6">
                <nav class="-mb-px flex space-x-8 overflow-x-auto" aria-label="Tabs">
                    <a href="{{ route('admin.configuracoes.index', ['tab' => 'modalidades']) }}"
                        class="{{ $activeTab === 'modalidades' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm flex items-center">
                        <i class="fas fa-graduation-cap mr-2"></i>
                        Modalidades de Ensino
                    </a>
                    <a href="{{ route('admin.configuracoes.index', ['tab' => 'grupos']) }}"
                        class="{{ $activeTab === 'grupos' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm flex items-center">
                        <i class="fas fa-users mr-2"></i>
                        Grupos Educacionais
                    </a>
                    <a href="{{ route('admin.configuracoes.index', ['tab' => 'turnos']) }}"
                        class="{{ $activeTab === 'turnos' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm flex items-center">
                        <i class="fas fa-clock mr-2"></i>
                        Turnos
                    </a>
                    <a href="{{ route('admin.configuracoes.index', ['tab' => 'disciplinas']) }}"
                        class="{{ $activeTab === 'disciplinas' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm flex items-center">
                        <i class="fas fa-book mr-2"></i>
                        Disciplinas
                    </a>
                </nav>
            </div>

            <!-- Tab Content -->
            <div class="tab-content">
                @if ($activeTab === 'modalidades')
                    @include('admin.configuracoes.tabs.modalidades', ['modalidades' => $modalidades])
                @elseif($activeTab === 'grupos')
                    @include('admin.configuracoes.tabs.grupos', ['grupos' => $grupos])
                @elseif($activeTab === 'turnos')
                    @include('admin.configuracoes.tabs.turnos', ['turnos' => $turnos])
                @elseif($activeTab === 'disciplinas')
                    @include('admin.configuracoes.tabs.disciplinas', ['disciplinas' => $disciplinas])
                @endif
            </div>
        </div>
    </x-card>
@endsection

@push('scripts')
    <script>
        // Auto-refresh content when switching tabs
        document.addEventListener('DOMContentLoaded', function() {
            const tabLinks = document.querySelectorAll('nav a[href*="tab="]');

            tabLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    // Show loading state
                    const content = document.querySelector('.tab-content');
                    if (content) {
                        content.style.opacity = '0.5';
                    }
                });
            });

            // Carregar modalidades de ensino quando a aba de grupos estiver ativa
            if (window.location.href.includes('tab=grupos')) {
                loadModalidadesEnsino();
            }

            // Configurar formulário de criação de grupo
            const createForm = document.getElementById('create-grupo-form');
            if (createForm) {
                createForm.addEventListener('submit', handleCreateGrupoSubmit);
            }
        });

        // Função para abrir o modal de criação
        function openCreateGrupoModal() {
            const modal = document.getElementById('create-grupo-modal');
            if (modal) {
                modal.classList.remove('hidden');
                loadModalidadesEnsino();
                clearErrors();
            }
        }

        // Função para fechar o modal de criação
        function closeCreateGrupoModal() {
            const modal = document.getElementById('create-grupo-modal');
            if (modal) {
                modal.classList.add('hidden');
                document.getElementById('create-grupo-form').reset();
                clearErrors();
            }
        }

        // Função para carregar modalidades de ensino
        function loadModalidadesEnsino() {
            const select = document.getElementById('modalidade_ensino_id');
            if (!select) return;

            fetch('/admin/grupos/modalidades-ensino', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                credentials: 'same-origin'
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                select.innerHTML = '<option value="">Selecione uma modalidade...</option>';
                
                // Verificar se data é um array
                if (Array.isArray(data)) {
                    data.forEach(modalidade => {
                        const option = document.createElement('option');
                        option.value = modalidade.id;
                        option.textContent = modalidade.nome;
                        select.appendChild(option);
                    });
                } else {
                    console.error('Resposta não é um array:', data);
                    select.innerHTML = '<option value="">Erro: formato de resposta inválido</option>';
                }
            })
            .catch(error => {
                console.error('Erro ao carregar modalidades:', error);
                select.innerHTML = '<option value="">Erro ao carregar modalidades</option>';
            });
        }

        // Função para tratar o envio do formulário
        function handleCreateGrupoSubmit(e) {
            e.preventDefault();
            
            const form = e.target;
            const submitButton = document.getElementById('submit-create-grupo');
            const formData = new FormData(form);

            // Limpar erros anteriores
            clearErrors();

            // Desabilitar botão de envio
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Criando...';

            fetch('/admin/grupos', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeCreateGrupoModal();
                    // Recarregar a página para mostrar o novo grupo
                    window.location.reload();
                } else {
                    // Mostrar erros de validação
                    if (data.errors) {
                        showErrors(data.errors);
                    }
                }
            })
            .catch(error => {
                console.error('Erro ao criar grupo:', error);
                alert('Erro ao criar grupo. Tente novamente.');
            })
            .finally(() => {
                // Reabilitar botão de envio
                submitButton.disabled = false;
                submitButton.innerHTML = '<i class="fas fa-save mr-1"></i> Criar Grupo';
            });
        }

        // Função para limpar erros
        function clearErrors() {
            const errorElements = document.querySelectorAll('[id^="error-"]');
            errorElements.forEach(element => {
                element.classList.add('hidden');
                element.textContent = '';
            });
        }

        // Função para mostrar erros
        function showErrors(errors) {
            Object.keys(errors).forEach(field => {
                const errorElement = document.getElementById(`error-${field}`);
                if (errorElement) {
                    errorElement.textContent = errors[field][0];
                    errorElement.classList.remove('hidden');
                }
            });
        }

        // Fechar modal ao clicar fora dele
        document.addEventListener('click', function(e) {
            const modal = document.getElementById('create-grupo-modal');
            if (e.target === modal) {
                closeCreateGrupoModal();
            }
        });
    </script>
@endpush
