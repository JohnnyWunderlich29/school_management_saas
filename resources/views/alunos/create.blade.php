@extends('layouts.app')

@section('content')
    <x-breadcrumbs :items="[['title' => 'Alunos', 'url' => route('alunos.index')], ['title' => 'Novo Aluno']]" />

    <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6">
        <form action="{{ route('alunos.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- Informações Pessoais -->
            <div>
                <h4 class="text-lg sm:text-xl font-semibold text-gray-800 mb-4">Informações Pessoais</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <x-input type="text" name="nome" label="Nome *" :value="old('nome')" required
                            placeholder="Digite o nome" />
                    </div>

                    <div>
                        <x-input type="text" name="sobrenome" label="Sobrenome *" :value="old('sobrenome')" required
                            placeholder="Digite o sobrenome" />
                    </div>

                    <div>
                        <x-input type="date" name="data_nascimento" label="Data de Nascimento *" :value="old('data_nascimento')"
                            required />
                    </div>

                    <div>
                        <x-select name="genero" label="Gênero" :options="[
                            '' => 'Selecione...',
                            'Masculino' => 'Masculino',
                            'Feminino' => 'Feminino',
                            'Outro' => 'Outro',
                        ]" :selected="old('genero')" />
                    </div>

                    <div>
                        <x-input type="text" name="cpf" label="CPF" :value="old('cpf')" placeholder="000.000.000-00"
                            maxlength="14" />
                    </div>

                    <div>
                        <x-input type="text" name="rg" label="RG" :value="old('rg')"
                            placeholder="00.000.000-0" />
                    </div>
                </div>
            </div>

            <!-- Informações Acadêmicas -->
            <div>
                <h4 class="text-lg sm:text-xl font-semibold text-gray-800 mb-4">Informações Acadêmicas</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <x-input type="text" name="matricula" label="Matrícula" :value="old('matricula')"
                            placeholder="Digite a matrícula do aluno" />
                    </div>

                    <div>
                        <label for="sala_id" class="block text-sm font-medium text-gray-700 mb-1">Sala</label>
                        <div class="relative" id="sala-select-container">
                            <input type="hidden" name="sala_id" id="sala_id" value="{{ old('sala_id') }}">
                            <div class="relative">
                                <input type="text" id="sala-search"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                    placeholder="Buscar sala..." autocomplete="off">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <i class="fas fa-chevron-down text-gray-400"></i>
                                </div>
                            </div>
                            <div id="sala-dropdown"
                                class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg hidden max-h-60 overflow-y-auto">
                                <div class="py-1">
                                    <div class="px-3 py-2 text-xs text-gray-500 bg-gray-50 border-b border-gray-200">
                                        Digite para filtrar as salas
                                    </div>
                                    @foreach ($salas as $index => $sala)
                                        <div class="sala-option px-3 py-2 cursor-pointer hover:bg-gray-100 {{ $index >= 8 ? 'hidden' : '' }}"
                                            data-value="{{ $sala->id }}"
                                            data-search="{{ strtolower($sala->codigo . ' ' . $sala->nome) }}">
                                            <div class="flex justify-between items-center">
                                                <span class="text-sm text-gray-900">{{ $sala->codigo }} -
                                                    {{ $sala->nome }}</span>
                                                <span
                                                    class="text-xs text-gray-500">({{ $sala->alunos_count }}/{{ $sala->capacidade }})</span>
                                            </div>
                                        </div>
                                    @endforeach
                                    <div id="no-results" class="px-3 py-2 text-sm text-gray-500 text-center hidden">
                                        Nenhuma sala encontrada
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contato -->
            <div>
                <h4 class="text-lg sm:text-xl font-semibold text-gray-800 mb-4">Contato</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <x-input type="tel" name="telefone" label="Telefone" :value="old('telefone')"
                            placeholder="(00) 00000-0000" id="telefone_aluno_create" />
                    </div>

                    <div>
                        <x-input type="email" name="email" label="Email" :value="old('email')"
                            placeholder="email@exemplo.com" />
                    </div>
                </div>
            </div>

            <!-- Endereço -->
            <div>
                <h4 class="text-lg sm:text-xl font-semibold text-gray-800 mb-4">Endereço</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div>
                        <x-input type="text" name="cep" id="cep" label="CEP" :value="old('cep')"
                            placeholder="00000-000" maxlength="9" class="pr-12"
                            help="Digite o CEP e clique na lupa para buscar.">
                            <button type="button" id="btn-buscar-cep-aluno"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-indigo-600"
                                title="Buscar CEP" aria-label="Buscar CEP">
                                <svg id="icon-cep-aluno" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-4.35-4.35m1.1-4.4a7.5 7.5 0 11-15 0 7.5 7.5 0 0115 0z" />
                                </svg>
                            </button>
                        </x-input>
                    </div>

                    <div class="sm:col-span-2 lg:col-span-2">
                        <x-input type="text" name="endereco" id="endereco" label="Endereço" :value="old('endereco')"
                            placeholder="Rua, número, complemento" />
                    </div>

                    <div>
                        <x-input type="text" name="cidade" id="cidade" label="Cidade" :value="old('cidade')"
                            placeholder="Nome da cidade" />
                    </div>

                    <div>
                        <x-select name="estado" id="estado" label="Estado" :options="[
                            '' => 'Selecione...',
                            'AC' => 'Acre',
                            'AL' => 'Alagoas',
                            'AP' => 'Amapá',
                            'AM' => 'Amazonas',
                            'BA' => 'Bahia',
                            'CE' => 'Ceará',
                            'DF' => 'Distrito Federal',
                            'ES' => 'Espírito Santo',
                            'GO' => 'Goiás',
                            'MA' => 'Maranhão',
                            'MT' => 'Mato Grosso',
                            'MS' => 'Mato Grosso do Sul',
                            'MG' => 'Minas Gerais',
                            'PA' => 'Pará',
                            'PB' => 'Paraíba',
                            'PR' => 'Paraná',
                            'PE' => 'Pernambuco',
                            'PI' => 'Piauí',
                            'RJ' => 'Rio de Janeiro',
                            'RN' => 'Rio Grande do Norte',
                            'RS' => 'Rio Grande do Sul',
                            'RO' => 'Rondônia',
                            'RR' => 'Roraima',
                            'SC' => 'Santa Catarina',
                            'SP' => 'São Paulo',
                            'SE' => 'Sergipe',
                            'TO' => 'Tocantins',
                        ]" :selected="old('estado')" />
                    </div>
                </div>
            </div>

            <!-- Informações Médicas -->
            <div>
                <h4 class="text-lg sm:text-xl font-semibold text-gray-800 mb-4">Informações Médicas</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <x-select name="tipo_sanguineo" label="Tipo Sanguíneo" :options="[
                            '' => 'Selecione...',
                            'A+' => 'A+',
                            'A-' => 'A-',
                            'B+' => 'B+',
                            'B-' => 'B-',
                            'AB+' => 'AB+',
                            'AB-' => 'AB-',
                            'O+' => 'O+',
                            'O-' => 'O-',
                        ]" :selected="old('tipo_sanguineo')" />
                    </div>

                    <div></div>

                    <div>
                        <x-textarea name="alergias" label="Alergias" :value="old('alergias')"
                            placeholder="Descreva alergias conhecidas" rows="3" />
                    </div>

                    <div>
                        <x-textarea name="medicamentos" label="Medicamentos" :value="old('medicamentos')"
                            placeholder="Medicamentos em uso" rows="3" />
                    </div>
                </div>
            </div>

            <!-- Responsáveis -->
            <div>
                <h4 class="text-lg sm:text-xl font-semibold text-gray-800 mb-4">Responsáveis *</h4>
                <div class="grid grid-cols-1 gap-4">
                    <!-- Buscar Responsáveis -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Buscar Responsáveis</label>
                        <input type="text" id="search-responsaveis"
                            placeholder="Digite o nome, sobrenome ou CPF do responsável..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">

                        <!-- Resultados da busca -->
                        <div id="search-results"
                            class="mt-2 hidden bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto">
                        </div>
                    </div>

                    <!-- Lista de responsáveis selecionados -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Responsáveis Selecionados</label>
                        <div id="responsaveis-selecionados"
                            class="min-h-[100px] border border-gray-300 rounded-md p-3 bg-gray-50">
                            <p class="text-sm text-gray-500 text-center">Nenhum responsável selecionado</p>
                        </div>
                        @error('responsaveis')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Responsável Principal -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Responsável Principal</label>
                        <select name="responsavel_principal" id="responsavel-principal"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Selecione um responsável principal...</option>
                        </select>
                        @error('responsavel_principal')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Documentos -->
            <div>
                <h4 class="text-lg sm:text-xl font-semibold text-gray-800 mb-4">Documentos</h4>
                <div class="space-y-4">
                    <!-- Upload Area -->
                    <div class="relative">
                        <div id="upload-area"
                            class="border-2 border-dashed border-gray-300 rounded-lg p-4 sm:p-8 text-center hover:border-indigo-400 transition-colors duration-200 cursor-pointer bg-gray-50 hover:bg-gray-100">
                            <div class="space-y-3 sm:space-y-4">
                                <div
                                    class="mx-auto w-12 h-12 sm:w-16 sm:h-16 bg-indigo-100 rounded-full flex items-center justify-center">
                                    <svg class="w-6 h-6 sm:w-8 sm:h-8 text-indigo-600" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12">
                                        </path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-base sm:text-lg font-medium text-gray-900">Clique para selecionar
                                        arquivos</p>
                                    <p class="text-sm text-gray-500 hidden sm:block">ou arraste e solte aqui</p>
                                </div>
                                <div class="text-xs text-gray-400">
                                    <p>Formatos aceitos: PDF, DOC, DOCX, JPG, JPEG, PNG</p>
                                    <p>Máximo 5MB por arquivo</p>
                                </div>
                            </div>
                        </div>
                        <input type="file" name="documentos[]" id="documentos" multiple
                            accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                    </div>

                    @error('documentos')
                        <div class="bg-red-50 border border-red-200 rounded-md p-3">
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        </div>
                    @enderror
                    @error('documentos.*')
                        <div class="bg-red-50 border border-red-200 rounded-md p-3">
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        </div>
                    @enderror

                    <!-- Preview dos arquivos selecionados -->
                    <div id="preview-documentos" class="hidden">
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <h5 class="text-sm font-medium text-blue-900 mb-3 flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                    </path>
                                </svg>
                                Arquivos Selecionados
                            </h5>
                            <div id="lista-documentos" class="space-y-2"></div>
                        </div>
                    </div>

                    <!-- Notificação de sucesso -->
                    <div id="upload-success" class="hidden bg-green-50 border border-green-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                                </path>
                            </svg>
                            <p class="text-sm font-medium text-green-800">Arquivos selecionados com sucesso!</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Observações -->
            <div>
                <x-textarea name="observacoes" label="Observações" :value="old('observacoes')"
                    placeholder="Informações adicionais sobre o aluno" rows="4" />
            </div>

            <!-- Botões de Ação -->
            <div class="flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-3 pt-4 border-t">
                <x-button href="{{ route('alunos.index') }}" color="secondary" class="w-full sm:w-auto">
                    <i class="fas fa-times mr-1"></i> <span class="hidden sm:inline">Cancelar</span><span
                        class="sm:hidden">Cancelar</span>
                </x-button>
                <x-button type="submit" color="primary" class="w-full sm:w-auto">
                    <i class="fas fa-save mr-1"></i> <span class="hidden sm:inline">Salvar Aluno</span><span
                        class="sm:hidden">Salvar</span>
                </x-button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        let responsaveisSelecionados = [];

        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('search-responsaveis');
            const searchResults = document.getElementById('search-results');
            const responsaveisSelecionadosDiv = document.getElementById('responsaveis-selecionados');
            const responsavelPrincipalSelect = document.getElementById('responsavel-principal');
            const form = document.querySelector('form');

            // Buscar responsáveis
            searchInput.addEventListener('input', function() {
                const query = this.value.trim();

                if (query.length < 2) {
                    searchResults.classList.add('hidden');
                    return;
                }

                fetch(`{{ route('alunos.search-responsaveis') }}?q=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        mostrarResultados(data);
                    });
            });

            function mostrarResultados(responsaveis) {
                searchResults.innerHTML = '';

                responsaveis.forEach(responsavel => {
                    const isSelected = responsaveisSelecionados.some(r => r.id === responsavel.id);

                    const item = document.createElement('div');
                    item.className = 'px-3 py-2 hover:bg-gray-100 cursor-pointer border-b';
                    item.innerHTML = `<span>${responsavel.text}</span>`;

                    if (!isSelected) {
                        item.addEventListener('click', () => {
                            responsaveisSelecionados.push(responsavel);
                            atualizarInterface();
                            searchResults.classList.add('hidden');
                            searchInput.value = '';
                        });
                    }

                    searchResults.appendChild(item);
                });

                searchResults.classList.remove('hidden');
            }

            window.removerResponsavel = function(responsavelId) {
                responsaveisSelecionados = responsaveisSelecionados.filter(r => r.id != responsavelId);
                atualizarInterface();
            }

            function atualizarInterface() {
                // Atualizar lista visual
                if (responsaveisSelecionados.length === 0) {
                    responsaveisSelecionadosDiv.innerHTML =
                        '<p class="text-sm text-gray-500 text-center">Nenhum responsável selecionado</p>';
                } else {
                    responsaveisSelecionadosDiv.innerHTML = responsaveisSelecionados.map(responsavel => `
                <div class="flex items-center justify-between bg-white border border-gray-200 rounded-md p-2 mb-2">
                    <span class="text-sm text-gray-700">${responsavel.nome_completo}</span>
                    <button type="button" onclick="removerResponsavel(${responsavel.id})" class="text-red-500 hover:text-red-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `).join('');
                }

                // Atualizar select do responsável principal
                responsavelPrincipalSelect.innerHTML =
                    '<option value="">Selecione um responsável principal...</option>';
                responsaveisSelecionados.forEach(responsavel => {
                    const option = document.createElement('option');
                    option.value = responsavel.id;
                    option.textContent = responsavel.nome_completo;
                    responsavelPrincipalSelect.appendChild(option);
                });

                if (responsaveisSelecionados.length === 1) {
                    responsavelPrincipalSelect.value = responsaveisSelecionados[0].id;
                }

                // Criar inputs hidden
                document.querySelectorAll('.responsavel-hidden-input').forEach(input => input.remove());

                responsaveisSelecionados.forEach(responsavel => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'responsaveis[]';
                    input.value = responsavel.id;
                    input.className = 'responsavel-hidden-input';
                    form.appendChild(input);
                });
            }

            // Validação simples do formulário
            form.addEventListener('submit', function(e) {
                // Comentado temporariamente para testar upload de documentos
                /*
                if (responsaveisSelecionados.length === 0) {
                    e.preventDefault();
                    alert('Selecione pelo menos um responsável.');
                    return;
                }

                if (!responsavelPrincipalSelect.value) {
                    e.preventDefault();
                    alert('Selecione um responsável principal.');
                    return;
                }
                */

                // Garantir que os inputs estão atualizados
                atualizarInterface();

                console.log('Formulário sendo submetido...');
                console.log('Arquivos selecionados:', documentosInput.files.length);
            });

            // Fechar resultados ao clicar fora
            document.addEventListener('click', function(e) {
                if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                    searchResults.classList.add('hidden');
                }
            });

            // Preview de documentos e drag & drop
            const documentosInput = document.getElementById('documentos');
            const previewDocumentos = document.getElementById('preview-documentos');
            const listaDocumentos = document.getElementById('lista-documentos');
            const uploadArea = document.getElementById('upload-area');
            const uploadSuccess = document.getElementById('upload-success');

            // Drag & Drop functionality
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                uploadArea.addEventListener(eventName, preventDefaults, false);
                document.body.addEventListener(eventName, preventDefaults, false);
            });

            ['dragenter', 'dragover'].forEach(eventName => {
                uploadArea.addEventListener(eventName, highlight, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                uploadArea.addEventListener(eventName, unhighlight, false);
            });

            uploadArea.addEventListener('drop', handleDrop, false);

            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }

            function highlight(e) {
                uploadArea.classList.add('border-blue-500', 'bg-blue-50');
            }

            function unhighlight(e) {
                uploadArea.classList.remove('border-blue-500', 'bg-blue-50');
            }

            function handleDrop(e) {
                const dt = e.dataTransfer;
                const files = dt.files;
                console.log('Arquivos arrastados:', files.length);
                documentosInput.files = files;
                documentosInput.dispatchEvent(new Event('change'));
            }

            // Click to upload
            uploadArea.addEventListener('click', () => {
                documentosInput.click();
            });

            documentosInput.addEventListener('change', function() {
                console.log('Arquivos selecionados:', this.files.length);
                const files = Array.from(this.files);

                if (files.length > 0) {
                    // Mostrar notificação de sucesso
                    uploadSuccess.classList.remove('hidden');
                    setTimeout(() => {
                        uploadSuccess.classList.add('hidden');
                    }, 3000);

                    previewDocumentos.classList.remove('hidden');
                    listaDocumentos.innerHTML = '';

                    files.forEach((file, index) => {
                        const fileDiv = document.createElement('div');
                        fileDiv.className =
                            'flex items-center justify-between bg-gray-50 border border-gray-200 rounded-md p-3';

                        const fileInfo = document.createElement('div');
                        fileInfo.className = 'flex items-center space-x-3';

                        const fileIcon = document.createElement('i');
                        const extension = file.name.split('.').pop().toLowerCase();
                        if (['pdf'].includes(extension)) {
                            fileIcon.className = 'fas fa-file-pdf text-red-500';
                        } else if (['doc', 'docx'].includes(extension)) {
                            fileIcon.className = 'fas fa-file-word text-blue-500';
                        } else if (['jpg', 'jpeg', 'png'].includes(extension)) {
                            fileIcon.className = 'fas fa-file-image text-green-500';
                        } else {
                            fileIcon.className = 'fas fa-file text-gray-500';
                        }

                        const fileName = document.createElement('span');
                        fileName.className = 'text-sm text-gray-700';
                        fileName.textContent = file.name;

                        const fileSize = document.createElement('span');
                        fileSize.className = 'text-xs text-gray-500';
                        fileSize.textContent = `(${(file.size / 1024 / 1024).toFixed(2)} MB)`;

                        fileInfo.appendChild(fileIcon);
                        fileInfo.appendChild(fileName);
                        fileInfo.appendChild(fileSize);

                        const removeBtn = document.createElement('button');
                        removeBtn.type = 'button';
                        removeBtn.className = 'text-red-500 hover:text-red-700';
                        removeBtn.innerHTML = '<i class="fas fa-times"></i>';
                        removeBtn.addEventListener('click', function() {
                            // Remover arquivo da lista
                            const dt = new DataTransfer();
                            const files = Array.from(documentosInput.files);
                            files.forEach((f, i) => {
                                if (i !== index) {
                                    dt.items.add(f);
                                }
                            });
                            documentosInput.files = dt.files;

                            // Atualizar preview
                            documentosInput.dispatchEvent(new Event('change'));
                        });

                        fileDiv.appendChild(fileInfo);
                        fileDiv.appendChild(removeBtn);
                        listaDocumentos.appendChild(fileDiv);
                    });
                } else {
                    previewDocumentos.classList.add('hidden');
                }
            });

            // Select customizado de sala com filtro
            const salaSearch = document.getElementById('sala-search');
            const salaDropdown = document.getElementById('sala-dropdown');
            const salaHidden = document.getElementById('sala_id');
            const salaOptions = document.querySelectorAll('.sala-option');
            const noResults = document.getElementById('no-results');
            let selectedSalaId = salaHidden.value;

            // Definir texto inicial se há valor selecionado
            if (selectedSalaId) {
                const selectedOption = document.querySelector(`[data-value="${selectedSalaId}"]`);
                if (selectedOption) {
                    salaSearch.value = selectedOption.textContent.trim();
                }
            }

            // Mostrar dropdown ao focar no input
            salaSearch.addEventListener('focus', function() {
                salaDropdown.classList.remove('hidden');
                filterSalas();
            });

            // Filtrar salas conforme digitação
            salaSearch.addEventListener('input', function() {
                filterSalas();
            });

            // Fechar dropdown ao clicar fora
            document.addEventListener('click', function(e) {
                if (!document.getElementById('sala-select-container').contains(e.target)) {
                    salaDropdown.classList.add('hidden');
                }
            });

            // Selecionar sala
            salaOptions.forEach(option => {
                option.addEventListener('click', function() {
                    const value = this.getAttribute('data-value');
                    const text = this.textContent.trim();

                    salaHidden.value = value;
                    salaSearch.value = text;
                    salaDropdown.classList.add('hidden');
                    selectedSalaId = value;
                });
            });

            function filterSalas() {
                const searchTerm = salaSearch.value.toLowerCase();
                let visibleCount = 0;
                let hasResults = false;

                salaOptions.forEach(option => {
                    const searchData = option.getAttribute('data-search');
                    const matches = searchData.includes(searchTerm);

                    if (matches && visibleCount < 8) {
                        option.classList.remove('hidden');
                        visibleCount++;
                        hasResults = true;
                    } else {
                        option.classList.add('hidden');
                    }
                });

                // Mostrar/ocultar mensagem de "nenhum resultado"
                if (hasResults) {
                    noResults.classList.add('hidden');
                } else {
                    noResults.classList.remove('hidden');
                }
            }

            // Busca de CEP (ViaCEP) para auto-preencher endereço
            const cepInput = document.getElementById('cep');
            const btnBuscarCepAluno = document.getElementById('btn-buscar-cep-aluno');
            const iconCepAluno = document.getElementById('icon-cep-aluno');
            const enderecoInput = document.getElementById('endereco');
            const bairroInput = document.getElementById('bairro');
            const cidadeInput = document.getElementById('cidade');
            const estadoSelect = document.getElementById('estado');
            const numeroInput = document.getElementById('numero');

            function sanitizeCep(v) {
                return (v || '').toString().replace(/\D/g, '').slice(0, 8);
            }

            function maskCEP(v) {
                const s = sanitizeCep(v);
                return s.length <= 5 ? s : s.slice(0, 5) + '-' + s.slice(5, 8);
            }

            function setCepLoading(loading) {
                if (btnBuscarCepAluno) {
                    btnBuscarCepAluno.disabled = !!loading;
                    btnBuscarCepAluno.classList.toggle('opacity-50', !!loading);
                }
                if (iconCepAluno) {
                    iconCepAluno.classList.toggle('animate-spin', !!loading);
                }
                [enderecoInput, cidadeInput].forEach(el => {
                    if (!el) return;
                    el.readOnly = !!loading;
                    el.classList.toggle('bg-gray-50', !!loading);
                });
            }

            function ensureCepAlertContainer() {
                const cepWrapper = cepInput?.closest('.mb-4');
                if (!cepWrapper) return null;
                let box = document.getElementById('cep-alert-box-aluno');
                if (!box) {
                    box = document.createElement('div');
                    box.id = 'cep-alert-box-aluno';
                    box.className = 'mt-2 hidden';
                    cepWrapper.appendChild(box);
                }
                return box;
            }

            function showCepAlert(type, msg) {
                const box = ensureCepAlertContainer();
                if (!box) return;
                if (!msg) {
                    box.classList.add('hidden');
                    box.innerHTML = '';
                    cepInput?.removeAttribute('aria-invalid');
                    return;
                }
                const styles = type === 'error' ? 'bg-red-50 p-3 border border-red-200 text-red-800' :
                    'bg-yellow-50 p-3 border border-yellow-200 text-yellow-800';
                box.className = 'mt-2 rounded-md ' + styles;
                box.setAttribute('role', 'alert');
                box.setAttribute('aria-live', 'assertive');
                box.innerHTML = `<div class="text-sm">${msg}</div>`;
                box.classList.remove('hidden');
                if (type === 'error' && cepInput) {
                    cepInput.setAttribute('aria-invalid', 'true');
                }
            }

            async function buscarCepAluno() {
                if (!cepInput) return;
                const cep = sanitizeCep(cepInput.value);
                if (cep.length !== 8) {
                    showCepAlert('error', 'CEP inválido. Digite 8 dígitos (ex.: 12345-678).');
                    cepInput.focus();
                    return;
                }
                showCepAlert(null, '');
                setCepLoading(true);
                try {
                    const resp = await fetch(`https://viacep.com.br/ws/${cep}/json/`, {
                        headers: {
                            'Accept': 'application/json'
                        }
                    });
                    if (!resp.ok) throw new Error('Falha ao consultar o CEP');
                    const data = await resp.json();
                    if (data.erro) {
                        showCepAlert('error', 'CEP não encontrado. Verifique e tente novamente.');
                        return;
                    }
                    if (enderecoInput) {
                        enderecoInput.value = data.logradouro || '';
                        if (!data.logradouro) {
                            showCepAlert('warn', 'Logradouro não informado pelo CEP. Preencha manualmente.');
                        }
                    }
                    if (bairroInput) bairroInput.value = data.bairro || '';
                    if (cidadeInput) cidadeInput.value = data.localidade || '';
                    if (estadoSelect) estadoSelect.value = data.uf || '';
                    if (numeroInput) {
                        numeroInput.focus();
                    }
                } catch (e) {
                    console.error(e);
                    showCepAlert('error', 'Não foi possível buscar o CEP agora. Tente novamente.');
                } finally {
                    setCepLoading(false);
                }
            }

            if (btnBuscarCepAluno) {
                btnBuscarCepAluno.addEventListener('click', buscarCepAluno);
            }
            if (cepInput) {
                cepInput.addEventListener('keydown', (ev) => {
                    if (ev.key === 'Enter') {
                        ev.preventDefault();
                        buscarCepAluno();
                    }
                });
                cepInput.addEventListener('blur', () => {
                    const v = sanitizeCep(cepInput.value);
                    if (v.length === 8) {
                        buscarCepAluno();
                    }
                });
                cepInput.addEventListener('input', () => {
                    cepInput.value = maskCEP(cepInput.value);
                });
            }
        });
    </script>
@endpush
