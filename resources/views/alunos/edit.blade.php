@extends('layouts.app')

@section('content')
<x-breadcrumbs :items="[
    ['title' => 'Alunos', 'url' => route('alunos.index')],
    ['title' => 'Editar Aluno']
]" />

<div class="bg-white rounded-lg shadow-sm p-4 sm:p-6">
            <form action="{{ route('alunos.update', $aluno->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Informações Pessoais -->
                <div>
                    <h4 class="text-lg sm:text-xl font-semibold text-gray-800 mb-4">Informações Pessoais</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input
                                type="text"
                                name="nome"
                                label="Nome *"
                                :value="old('nome') ?? $aluno->nome"
                                required
                                placeholder="Nome do aluno"
                            />
                        </div>

                        <div>
                            <x-input
                                type="text"
                                name="sobrenome"
                                label="Sobrenome *"
                                :value="old('sobrenome') ?? $aluno->sobrenome"
                                required
                                placeholder="Sobrenome do aluno"
                            />
                        </div>

                        <div>
                            <x-input
                                type="date"
                                name="data_nascimento"
                                label="Data de Nascimento *"
                                :value="old('data_nascimento') ?? ($aluno->data_nascimento ? $aluno->data_nascimento->format('Y-m-d') : '')"
                                required
                            />
                        </div>

                        <div>
                            <x-select
                                name="genero"
                                label="Gênero"
                            >
                                <option value="">Selecione o gênero</option>
                                <option value="Masculino" {{ (old('genero') ?? $aluno->genero) == 'Masculino' ? 'selected' : '' }}>Masculino</option>
                                <option value="Feminino" {{ (old('genero') ?? $aluno->genero) == 'Feminino' ? 'selected' : '' }}>Feminino</option>
                                <option value="Outro" {{ (old('genero') ?? $aluno->genero) == 'Outro' ? 'selected' : '' }}>Outro</option>
                            </x-select>
                        </div>

                        <div>
                            <x-input
                                type="text"
                                name="cpf"
                                label="CPF"
                                :value="old('cpf') ?? $aluno->cpf"
                                placeholder="000.000.000-00"
                                id="cpf"
                            />
                        </div>

                        <div>
                            <x-input
                                type="text"
                                name="rg"
                                label="RG"
                                :value="old('rg') ?? $aluno->rg"
                                placeholder="00.000.000-0"
                            />
                        </div>
                    </div>
                </div>

                <!-- Informações Acadêmicas -->
                <div>
                    <h4 class="text-lg sm:text-xl font-semibold text-gray-800 mb-4">Informações Acadêmicas</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input
                                type="text"
                                name="matricula"
                                label="Matrícula"
                                :value="old('matricula') ?? $aluno->matricula"
                                placeholder="Número da matrícula"
                            />
                        </div>
                        
                        <div>
                            <x-select
                                name="sala_id"
                                label="Sala"
                            >
                                <option value="">Selecione uma sala</option>
                                @foreach($salas as $sala)
                                    <option value="{{ $sala->id }}" 
                                        {{ (old('sala_id') ?? $aluno->sala_id) == $sala->id ? 'selected' : '' }}>
                                        {{ $sala->codigo }} - {{ $sala->nome }} ({{ $sala->alunos->count() }}/{{ $sala->capacidade }})
                                    </option>
                                @endforeach
                            </x-select>
                            <small class="text-gray-500">Sala onde o aluno está matriculado</small>
                        </div>
                    </div>
                </div>

                <!-- Informações de Contato -->
                <div>
                    <h4 class="text-lg sm:text-xl font-semibold text-gray-800 mb-4">Informações de Contato</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input
                                type="tel"
                                name="telefone"
                                label="Telefone"
                                :value="old('telefone') ?? $aluno->telefone"
                                placeholder="(00) 00000-0000"
                                id="telefone_aluno"
                            />
                        </div>

                        <div>
                            <x-input
                                type="email"
                                name="email"
                                label="E-mail"
                                :value="old('email') ?? $aluno->email"
                                placeholder="email@exemplo.com"
                            />
                        </div>
                    </div>
                </div>

                <!-- Informações de Endereço -->
                <div>
                    <h4 class="text-lg sm:text-xl font-semibold text-gray-800 mb-4">Informações de Endereço</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <!-- CEP primeiro, com lupa -->
                        <div>
                            <x-input
                                type="text"
                                name="cep"
                                label="CEP"
                                :value="old('cep') ?? $aluno->cep"
                                placeholder="00000-000"
                                id="cep"
                                maxlength="9"
                                class="pr-12"
                                help="Digite o CEP e clique na lupa para buscar."
                            >
                                <button type="button" id="btn-buscar-cep-aluno-edit" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-indigo-600" title="Buscar CEP" aria-label="Buscar CEP">
                                    <svg id="icon-cep-aluno-edit" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m1.1-4.4a7.5 7.5 0 11-15 0 7.5 7.5 0 0115 0z" />
                                    </svg>
                                </button>
                            </x-input>
                        </div>

                        <div class="sm:col-span-2 lg:col-span-2">
                            <x-input
                                type="text"
                                name="endereco"
                                label="Endereço"
                                :value="old('endereco') ?? $aluno->endereco"
                                placeholder="Rua, número, complemento"
                                id="endereco"
                            />
                        </div>

                        <div>
                            <x-input
                                type="text"
                                name="cidade"
                                label="Cidade"
                                :value="old('cidade') ?? $aluno->cidade"
                                placeholder="Nome da cidade"
                                id="cidade"
                            />
                        </div>

                        <div>
                            <x-input
                                type="text"
                                name="estado"
                                label="Estado"
                                :value="old('estado') ?? $aluno->estado"
                                placeholder="UF"
                                maxlength="2"
                                id="estado"
                            />
                        </div>
                    </div>
                </div>

                <!-- Informações Médicas -->
                <div>
                    <h4 class="text-lg sm:text-xl font-semibold text-gray-800 mb-4">Informações Médicas</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-select
                                name="tipo_sanguineo"
                                label="Tipo Sanguíneo"
                            >
                                <option value="">Selecione o tipo sanguíneo</option>
                                <option value="A+" {{ (old('tipo_sanguineo') ?? $aluno->tipo_sanguineo) == 'A+' ? 'selected' : '' }}>A+</option>
                                <option value="A-" {{ (old('tipo_sanguineo') ?? $aluno->tipo_sanguineo) == 'A-' ? 'selected' : '' }}>A-</option>
                                <option value="B+" {{ (old('tipo_sanguineo') ?? $aluno->tipo_sanguineo) == 'B+' ? 'selected' : '' }}>B+</option>
                                <option value="B-" {{ (old('tipo_sanguineo') ?? $aluno->tipo_sanguineo) == 'B-' ? 'selected' : '' }}>B-</option>
                                <option value="AB+" {{ (old('tipo_sanguineo') ?? $aluno->tipo_sanguineo) == 'AB+' ? 'selected' : '' }}>AB+</option>
                                <option value="AB-" {{ (old('tipo_sanguineo') ?? $aluno->tipo_sanguineo) == 'AB-' ? 'selected' : '' }}>AB-</option>
                                <option value="O+" {{ (old('tipo_sanguineo') ?? $aluno->tipo_sanguineo) == 'O+' ? 'selected' : '' }}>O+</option>
                                <option value="O-" {{ (old('tipo_sanguineo') ?? $aluno->tipo_sanguineo) == 'O-' ? 'selected' : '' }}>O-</option>
                            </x-select>
                        </div>

                        <div>
                            <x-input
                                type="text"
                                name="alergias"
                                label="Alergias"
                                :value="old('alergias') ?? $aluno->alergias"
                                placeholder="Alergias conhecidas"
                            />
                        </div>

                        <div class="md:col-span-2">
                            <x-textarea
                                name="medicamentos"
                                label="Medicamentos"
                                :value="old('medicamentos') ?? $aluno->medicamentos"
                                placeholder="Medicamentos em uso"
                                rows="2"
                            />
                        </div>
                    </div>
                </div>

                <!-- Responsáveis -->
                <div>
                    <h4 class="text-lg sm:text-xl font-semibold text-gray-800 mb-4">Responsáveis</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-select
                                name="responsaveis[]"
                                label="Responsáveis"
                                multiple
                            >
                                @foreach($responsaveis as $responsavel)
                                    <option value="{{ $responsavel->id }}" 
                                        {{ in_array($responsavel->id, old('responsaveis', $aluno->responsaveis->pluck('id')->toArray())) ? 'selected' : '' }}>
                                        {{ $responsavel->nome }} {{ $responsavel->sobrenome }} - {{ $responsavel->parentesco }}
                                    </option>
                                @endforeach
                            </x-select>
                            <small class="text-gray-500">Segure Ctrl para selecionar múltiplos responsáveis</small>
                        </div>

                        <div>
                            @php
                                $responsavelPrincipalId = optional($aluno->responsaveis->firstWhere('pivot.responsavel_principal', true))->id;
                            @endphp
                            <x-select
                                name="responsavel_principal"
                                label="Responsável Principal"
                            >
                                <option value="">Selecione o responsável principal</option>
                                @foreach($responsaveis as $responsavel)
                                    <option value="{{ $responsavel->id }}" 
                                        {{ (string) (old('responsavel_principal') ?? $responsavelPrincipalId) === (string) $responsavel->id ? 'selected' : '' }}>
                                        {{ $responsavel->nome }} {{ $responsavel->sobrenome }} - {{ $responsavel->parentesco }}
                                    </option>
                                @endforeach
                            </x-select>
                            <small class="text-gray-500">Escolha um dos responsáveis selecionados acima como principal</small>
                        </div>
                    </div>
                </div>

                <!-- Documentos -->
                <div>
                    <h4 class="text-md font-semibold text-gray-800 mb-4">Documentos</h4>
                    
                    <!-- Documentos Existentes -->
                    @if($aluno->documentos && $aluno->documentos->count() > 0)
                        <div class="mb-4">
                            <h5 class="text-sm font-medium text-gray-700 mb-3">Documentos Atuais</h5>
                            <div class="space-y-2" id="documentos-existentes">
                                @foreach($aluno->documentos as $documento)
                                    <div class="flex items-center justify-between bg-gray-50 border border-gray-200 rounded-md p-3" data-documento-id="{{ $documento->id }}">
                                        <div class="flex items-center space-x-3">
                                            <i class="fas fa-file-pdf text-red-500"></i>
                                            <div>
                                                <span class="text-sm text-gray-700">{{ $documento->nome_original }}</span>
                                                <span class="text-xs text-gray-500 ml-2">({{ $documento->tamanho_formatado }})</span>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <a href="{{ $documento->url }}" target="_blank" class="text-blue-500 hover:text-blue-700" title="Visualizar documento">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button type="button" class="text-red-500 hover:text-red-700 remover-documento" data-documento-id="{{ $documento->id }}" title="Remover documento">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    
                    <!-- Adicionar Novos Documentos -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Adicionar Novos Documentos</label>
                        
                        <!-- Upload Area -->
                        <div id="upload-area" class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-gray-400 transition-colors cursor-pointer">
                            <div class="space-y-4">
                                <div class="mx-auto w-12 h-12 text-gray-400">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 48 48" aria-hidden="true">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">
                                        <span class="font-medium text-blue-600 hover:text-blue-500">Clique para fazer upload</span>
                                        ou arraste e solte
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        PDF, DOC, DOCX, JPG, JPEG, PNG até 10MB cada
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <input type="file" name="documentos[]" id="documentos" multiple 
                               accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" class="hidden">
                        
                        @error('documentos')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                        @error('documentos.*')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                        
                        <!-- Success Notification -->
                        <div id="upload-success" class="hidden mt-3 p-3 bg-green-50 border border-green-200 rounded-md">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-green-800">Arquivos selecionados com sucesso!</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Preview dos Novos Documentos -->
                    <div id="preview-documentos" class="hidden mt-4">
                        <h5 class="text-sm font-medium text-gray-700 mb-3">Novos Documentos Selecionados</h5>
                        <div id="lista-documentos" class="space-y-2"></div>
                    </div>
                    
                    <!-- Input hidden para documentos a serem removidos -->
                    <input type="hidden" name="documentos_remover" id="documentos-remover" value="">
                </div>
                
                <!-- Observações -->
                <div>
                    <h4 class="text-lg sm:text-xl font-semibold text-gray-800 mb-4">Observações</h4>
                    <div>
                        <x-textarea
                            name="observacoes"
                            label="Observações"
                            :value="old('observacoes') ?? $aluno->observacoes"
                            placeholder="Observações adicionais sobre o aluno"
                            rows="3"
                        />
                    </div>
                </div>

                <!-- Status -->
                <div>
                    <h4 class="text-lg sm:text-xl font-semibold text-gray-800 mb-4">Status</h4>
                    <div class="flex items-center">
                        <input type="hidden" name="ativo" value="0">
                        <input type="checkbox" name="ativo" value="1" id="ativo" 
                               {{ (old('ativo') ?? $aluno->ativo) ? 'checked' : '' }}
                               class="mr-2 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="ativo" class="text-sm font-medium text-gray-700">Aluno Ativo</label>
                    </div>
                </div>

                <!-- Botões -->
                <div class="flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-3 pt-4 border-t">
                    <x-button href="{{ route('alunos.index') }}" color="secondary" class="w-full sm:w-auto sm:justify-center">
                        <span class="hidden sm:inline">Cancelar</span><span class="sm:hidden">Cancelar</span>
                    </x-button>
                    <x-button type="submit" color="primary" class="w-full sm:w-auto sm:justify-center">
                        <span class="hidden sm:inline">Atualizar Aluno</span><span class="sm:hidden">Atualizar</span>
                    </x-button>
                </div>
            </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    
    // Validação e debug do formulário
    form.addEventListener('submit', function(e) {
        console.log('Formulário sendo submetido');
        console.log('Arquivos selecionados:', document.getElementById('documentos').files.length);
        
        // Verificar responsáveis selecionados (via select múltiplo)
        const selectResponsaveis = document.querySelector('select[name="responsaveis[]"]');
        const responsaveisSelecionados = Array.from(selectResponsaveis?.options || []).filter(opt => opt.selected).map(opt => opt.value);
        if (!responsaveisSelecionados.length) {
            e.preventDefault();
            alert('Selecione pelo menos um responsável.');
            return;
        }
        
        // Verificar responsável principal selecionado e coerência
        const responsavelPrincipalSelect = document.querySelector('select[name="responsavel_principal"]');
        const principalVal = responsavelPrincipalSelect?.value || '';
        if (!principalVal) {
            e.preventDefault();
            alert('Selecione um responsável principal.');
            return;
        }
        if (!responsaveisSelecionados.includes(principalVal)) {
            e.preventDefault();
            alert('O responsável principal deve estar entre os responsáveis selecionados.');
            return;
        }
    });
    // Array para armazenar IDs dos documentos a serem removidos
    let documentosRemover = [];
    
    // Preview de novos documentos e drag & drop
    const documentosInput = document.getElementById('documentos');
    const previewDocumentos = document.getElementById('preview-documentos');
    const listaDocumentos = document.getElementById('lista-documentos');
    const documentosRemoverInput = document.getElementById('documentos-remover');
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
        
        // Criar um novo FileList a partir dos arquivos arrastados
        const dataTransfer = new DataTransfer();
        for (let i = 0; i < files.length; i++) {
            dataTransfer.items.add(files[i]);
        }
        
        documentosInput.files = dataTransfer.files;
        documentosInput.dispatchEvent(new Event('change'));
    }

    // Click to upload
    uploadArea.addEventListener('click', () => {
        documentosInput.click();
    });
    
    // Gerenciar remoção de documentos existentes
    document.querySelectorAll('.remover-documento').forEach(button => {
        button.addEventListener('click', function() {
            const documentoId = this.getAttribute('data-documento-id');
            const documentoDiv = this.closest('[data-documento-id]');
            
            if (confirm('Tem certeza que deseja remover este documento?')) {
                // Adicionar ID à lista de remoção
                documentosRemover.push(documentoId);
                documentosRemoverInput.value = documentosRemover.join(',');
                
                // Remover visualmente
                documentoDiv.style.opacity = '0.5';
                documentoDiv.style.pointerEvents = 'none';
                
                // Adicionar indicador de remoção
                const indicator = document.createElement('span');
                indicator.className = 'text-red-500 text-xs font-medium';
                indicator.textContent = ' (Será removido)';
                documentoDiv.querySelector('.text-sm').appendChild(indicator);
            }
        });
    });
    
    // Preview de novos documentos
    documentosInput.addEventListener('change', function() {
        console.log('Arquivos selecionados (edit):', this.files.length);
        console.log('Files object:', this.files);
        for (let i = 0; i < this.files.length; i++) {
            console.log('Arquivo ' + i + ':', this.files[i].name, this.files[i].size, 'bytes');
        }
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
                fileDiv.className = 'flex items-center justify-between bg-gray-50 border border-gray-200 rounded-md p-3';
                
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
    // Busca de CEP (ViaCEP) para auto-preencher endereço
    const cepInput = document.getElementById('cep');
    const btnBuscarCepAluno = document.getElementById('btn-buscar-cep-aluno-edit');
    const iconCepAluno = document.getElementById('icon-cep-aluno-edit');
    const enderecoInput = document.getElementById('endereco');
    const bairroInput = document.getElementById('bairro');
    const cidadeInput = document.getElementById('cidade');
    const estadoSelect = document.getElementById('estado');
    const numeroInput = document.getElementById('numero');

    function sanitizeCep(v){ return (v || '').toString().replace(/\D/g,'').slice(0,8); }
    function maskCEP(v){ const s = sanitizeCep(v); return s.length <= 5 ? s : s.slice(0,5)+'-'+s.slice(5,8); }

    function setCepLoading(loading){
        if(btnBuscarCepAluno){ btnBuscarCepAluno.disabled = !!loading; btnBuscarCepAluno.classList.toggle('opacity-50', !!loading); }
        if(iconCepAluno){ iconCepAluno.classList.toggle('animate-spin', !!loading); }
        [enderecoInput, cidadeInput].forEach(el => {
            if(!el) return;
            el.readOnly = !!loading;
            el.classList.toggle('bg-gray-50', !!loading);
        });
    }

    function ensureCepAlertContainer(){
        const cepWrapper = cepInput?.closest('.mb-4');
        if(!cepWrapper) return null;
        let box = document.getElementById('cep-alert-box-aluno-edit');
        if(!box){
            box = document.createElement('div');
            box.id = 'cep-alert-box-aluno-edit';
            box.className = 'mt-2 hidden';
            cepWrapper.appendChild(box);
        }
        return box;
    }

    function showCepAlert(type, msg){
        const box = ensureCepAlertContainer();
        if(!box) return;
        if(!msg){ box.classList.add('hidden'); box.innerHTML=''; cepInput?.removeAttribute('aria-invalid'); return; }
        const styles = type === 'error' ? 'bg-red-50 p-3 border border-red-200 text-red-800' : 'bg-yellow-50 p-3 border border-yellow-200 text-yellow-800';
        box.className = 'mt-2 rounded-md ' + styles;
        box.setAttribute('role','alert');
        box.setAttribute('aria-live','assertive');
        box.innerHTML = `<div class="text-sm">${msg}</div>`;
        box.classList.remove('hidden');
        if(type === 'error' && cepInput){ cepInput.setAttribute('aria-invalid','true'); }
    }

    async function buscarCepAluno(){
        if(!cepInput) return;
        const cep = sanitizeCep(cepInput.value);
        if(cep.length !== 8){
            showCepAlert('error','CEP inválido. Digite 8 dígitos (ex.: 12345-678).');
            cepInput.focus();
            return;
        }
        showCepAlert(null,'');
        setCepLoading(true);
        try{
            const resp = await fetch(`https://viacep.com.br/ws/${cep}/json/`, { headers: { 'Accept': 'application/json' } });
            if(!resp.ok) throw new Error('Falha ao consultar o CEP');
            const data = await resp.json();
            if(data.erro){
                showCepAlert('error','CEP não encontrado. Verifique e tente novamente.');
                return;
            }
            if(enderecoInput){
                enderecoInput.value = data.logradouro || '';
                if(!data.logradouro){
                    showCepAlert('warn','Logradouro não informado pelo CEP. Preencha manualmente.');
                    enderecoInput.focus();
                }
            }
            if(bairroInput) bairroInput.value = data.bairro || '';
            if(cidadeInput) cidadeInput.value = data.localidade || '';
            if(estadoSelect) estadoSelect.value = data.uf || '';
            if(numeroInput){ numeroInput.focus(); }
        } catch(e){
            console.error(e);
            showCepAlert('error','Não foi possível buscar o CEP agora. Tente novamente.');
        } finally {
            setCepLoading(false);
        }
    }

    if(btnBuscarCepAluno){ btnBuscarCepAluno.addEventListener('click', buscarCepAluno); }
    if(cepInput){
        cepInput.addEventListener('keydown', (ev) => {
            if(ev.key === 'Enter'){
                ev.preventDefault();
                buscarCepAluno();
            }
        });
        cepInput.addEventListener('blur', () => {
            const v = sanitizeCep(cepInput.value);
            if(v.length === 8){ buscarCepAluno(); }
        });
        cepInput.addEventListener('input', () => {
            cepInput.value = maskCEP(cepInput.value);
        });
    }
});
</script>

@endsection