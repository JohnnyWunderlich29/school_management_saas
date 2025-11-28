@extends('layouts.app')

@section('content')
<x-breadcrumbs :items="[
    ['title' => 'Funcionários', 'url' => route('funcionarios.index')],
    ['title' => 'Novo Funcionário']
]" />

<div class="bg-white rounded-lg shadow-sm p-4 sm:p-6">
            <form action="{{ route('funcionarios.store') }}" method="POST" class="space-y-6">
                @csrf

                <!-- Informações Pessoais -->
                <div>
                    <h4 class="text-lg sm:text-xl font-semibold text-gray-800 mb-4">Informações Pessoais</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input
                                type="text"
                                name="nome"
                                label="Nome *"
                                :value="old('nome')"
                                required
                                placeholder="Digite o nome"
                            />
                        </div>

                        <div>
                            <x-input
                                type="text"
                                name="sobrenome"
                                label="Sobrenome *"
                                :value="old('sobrenome')"
                                required
                                placeholder="Digite o sobrenome"
                            />
                        </div>

                        <div>
                            <x-input
                                type="date"
                                name="data_nascimento"
                                label="Data de Nascimento *"
                                :value="old('data_nascimento')"
                                required
                            />
                        </div>

                        <div>
                            <x-input
                                type="text"
                                name="cpf"
                                label="CPF *"
                                :value="old('cpf')"
                                placeholder="000.000.000-00"
                                id="cpf"
                                required
                            />
                        </div>

                        <div>
                            <x-input
                                type="text"
                                name="rg"
                                label="RG"
                                :value="old('rg')"
                                placeholder="Digite o RG"
                            />
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
                                label="Telefone *"
                                :value="old('telefone')"
                                required
                                placeholder="(00) 00000-0000"
                                id="telefone_funcionario"
                            />
                        </div>

                        <div>
                            <x-input
                                type="email"
                                name="email"
                                label="E-mail *"
                                :value="old('email')"
                                required
                                placeholder="email@exemplo.com"
                            />
                        </div>
                    </div>
                </div>

                <!-- Endereço -->
                <div>
                    <h4 class="text-lg sm:text-xl font-semibold text-gray-800 mb-4">Endereço</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div class="sm:col-span-2 lg:col-span-2">
                            <x-input
                                type="text"
                                name="endereco"
                                label="Endereço"
                                :value="old('endereco')"
                                placeholder="Rua, número, complemento"
                                id="endereco"
                            />
                        </div>

                        <!-- Bairro -->
                        <div>
                            <x-input
                                type="text"
                                name="bairro"
                                label="Bairro"
                                :value="old('bairro')"
                                placeholder="Digite o bairro"
                                id="bairro"
                            />
                        </div>

                        <div>
                            <x-input
                                type="text"
                                name="cidade"
                                label="Cidade"
                                :value="old('cidade')"
                                placeholder="Digite a cidade"
                                id="cidade"
                            />
                        </div>

                        <div>
                            <x-input
                                type="text"
                                name="estado"
                                label="Estado"
                                :value="old('estado')"
                                placeholder="UF"
                                maxlength="2"
                                id="estado"
                            />
                        </div>

                        <!-- Número -->
                        <div>
                            <x-input
                                type="text"
                                name="numero"
                                label="Número"
                                :value="old('numero')"
                                placeholder="Número"
                                id="numero"
                            />
                        </div>

                        <div>
                            <x-input
                                type="text"
                                name="cep"
                                label="CEP"
                                :value="old('cep')"
                                placeholder="00000-000"
                                id="cep"
                                maxlength="9"
                                class="pr-12"
                                help="Digite o CEP e clique na lupa para buscar."
                            >
                                <button type="button" id="btn-buscar-cep-func-create" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-indigo-600" title="Buscar CEP" aria-label="Buscar CEP">
                                    <svg id="icon-cep-func-create" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m1.1-4.4a7.5 7.5 0 11-15 0 7.5 7.5 0 0115 0z" />
                                    </svg>
                                </button>
                            </x-input>
                        </div>
                    </div>
                </div>

                <!-- Informações Profissionais -->
                <div>
                    <h4 class="text-lg sm:text-xl font-semibold text-gray-800 mb-4">Informações Profissionais</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="cargo" class="block text-sm font-medium text-gray-700 mb-1">Cargo *</label>
                            <x-select name="cargo" id="cargo" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                                <option value="">Selecione um cargo</option>
                                @foreach($cargos as $cargo)
                                    <option value="{{ $cargo->nome }}" {{ old('cargo') == $cargo->nome ? 'selected' : '' }}>
                                        {{ $cargo->nome }}
                                    </option>
                                @endforeach
                            </x-select>
                            @error('cargo')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <x-input
                                type="text"
                                name="departamento"
                                label="Departamento"
                                :value="old('departamento')"
                                placeholder="Digite o departamento"
                            />
                        </div>

                        <div>
                            <x-input
                                type="date"
                                name="data_contratacao"
                                label="Data de Contratação *"
                                :value="old('data_contratacao')"
                                required
                            />
                        </div>

                        <div>
                            <x-input
                                type="number"
                                name="salario"
                                label="Salário"
                                :value="old('salario')"
                                placeholder="0.00"
                                step="0.01"
                            />
                        </div>
                    </div>
                </div>
                
                <!-- Formações e Disciplinas (para professores) -->
                <div id="professor_fields" class="space-y-4">
                    <h4 class="text-lg sm:text-xl font-semibold text-gray-800 mb-4">Disciplinas</h4>
                    
                    <!-- Disciplinas -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Disciplinas</label>
                        <div class="border border-gray-300 rounded-md p-3 space-y-2">
                            @foreach(\App\Models\Disciplina::where('ativo', true)->orderBy('nome')->get() as $disciplina)
                                <div class="flex items-center">
                                    <input type="checkbox" name="disciplinas[]" id="disciplina_{{ $disciplina->id }}" 
                                        value="{{ $disciplina->id }}" class="mr-2"
                                        {{ (old('disciplinas') && in_array($disciplina->id, old('disciplinas'))) ? 'checked' : '' }}>
                                    <label for="disciplina_{{ $disciplina->id }}" class="text-sm text-gray-700">
                                        {{ $disciplina->nome }} 
                                        <span class="text-xs text-gray-500">
                                            ({{ $disciplina->modalidadeEnsino->nome ?? 'Sem modalidade' }})
                                        </span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Criação de Usuário -->
                <div>
                    <h4 class="text-lg sm:text-xl font-semibold text-gray-800 mb-4">Acesso ao Sistema</h4>
                    <div class="space-y-4">
                        <div class="flex items-center">
                            <input type="checkbox" name="criar_usuario" id="criar_usuario" class="mr-2" value="1" {{ old('criar_usuario') ? 'checked' : '' }}>
                            <label for="criar_usuario" class="text-sm text-gray-700">Criar usuário para acesso ao sistema</label>
                        </div>

                        <div id="usuario_fields" class="grid grid-cols-1 sm:grid-cols-2 gap-4" style="display: {{ old('criar_usuario') ? 'grid' : 'none' }};">
                            <div>
                                <x-input
                                    type="password"
                                    name="password"
                                    label="Senha"
                                    placeholder="Digite a senha"
                                />
                            </div>

                            <div>
                                <x-input
                                    type="password"
                                    name="password_confirmation"
                                    label="Confirmar Senha"
                                    placeholder="Confirme a senha"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Observações -->
                <div>
                    <h4 class="text-lg sm:text-xl font-semibold text-gray-800 mb-4">Observações</h4>
                    <div>
                        <x-textarea
                            name="observacoes"
                            label="Observações"
                            :value="old('observacoes')"
                            placeholder="Observações adicionais sobre o funcionário"
                            rows="3"
                        />
                    </div>
                </div>

                <!-- Botões -->
                <div class="flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-3 pt-4 border-t">
                    <x-button href="{{ route('funcionarios.index') }}" color="secondary" class="w-full sm:w-auto">
                        <i class="fas fa-times mr-1"></i> <span class="hidden sm:inline">Cancelar</span><span class="sm:hidden">Cancelar</span>
                    </x-button>
                    <x-button type="submit" color="primary" class="w-full sm:w-auto">
                        <i class="fas fa-save mr-1"></i> <span class="hidden sm:inline">Salvar Funcionário</span><span class="sm:hidden">Salvar</span>
                    </x-button>
                </div>
            </form>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Controle de exibição dos campos de usuário
        const criarUsuarioCheckbox = document.querySelector('input[name="criar_usuario"]');
        const usuarioFields = document.getElementById('usuario_fields');
        
        if (criarUsuarioCheckbox && usuarioFields) {
            criarUsuarioCheckbox.addEventListener('change', function() {
                usuarioFields.style.display = this.checked ? 'grid' : 'none';
            });
        }
    });
</script>
</div>
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
    const cepInput = document.getElementById('cep');
    const btnBuscar = document.getElementById('btn-buscar-cep-func-create');
    const iconCep = document.getElementById('icon-cep-func-create');
    const enderecoInput = document.getElementById('endereco');
    const bairroInput = document.getElementById('bairro');
    const cidadeInput = document.getElementById('cidade');
    const estadoInput = document.getElementById('estado');
    const numeroInput = document.getElementById('numero');

    function sanitizeCep(v){ return (v||'').toString().replace(/\D/g,'').slice(0,8); }
    function maskCEP(v){ const s = sanitizeCep(v); return s.length <= 5 ? s : s.slice(0,5)+'-'+s.slice(5,8); }

    function setCepLoading(loading){
        if(btnBuscar){ btnBuscar.disabled = !!loading; btnBuscar.classList.toggle('opacity-50', !!loading); }
        if(iconCep){ iconCep.classList.toggle('animate-spin', !!loading); }
        [enderecoInput, cidadeInput].forEach(el => { if(!el) return; el.readOnly = !!loading; el.classList.toggle('bg-gray-50', !!loading); });
    }

    function ensureCepAlertContainer(){
        const wrapper = cepInput?.closest('.mb-4');
        if(!wrapper) return null;
        let alertDiv = wrapper.querySelector('.cep-alert');
        if(!alertDiv){ alertDiv = document.createElement('div'); alertDiv.className = 'cep-alert mt-2 text-sm'; wrapper.appendChild(alertDiv); }
        return alertDiv;
    }
    function showCepAlert(type, message){
        const alertDiv = ensureCepAlertContainer(); if(!alertDiv) return;
        alertDiv.textContent = message || '';
        alertDiv.classList.remove('text-red-600','text-yellow-600','text-green-600');
        if(type === 'error') alertDiv.classList.add('text-red-600');
        else if(type === 'warn') alertDiv.classList.add('text-yellow-600');
        else if(type === 'ok') alertDiv.classList.add('text-green-600');
    }

    async function buscarCep(){
        if(!cepInput) return;
        const cep = sanitizeCep(cepInput.value);
        if(cep.length !== 8){ showCepAlert('error','CEP inválido. Digite 8 dígitos (ex.: 12345-678).'); cepInput.focus(); return; }
        showCepAlert(null,''); setCepLoading(true);
        try{
            const resp = await fetch(`https://viacep.com.br/ws/${cep}/json/`, { headers: { 'Accept': 'application/json' } });
            if(!resp.ok) throw new Error('Falha ao consultar o CEP');
            const data = await resp.json();
            if(data.erro){ showCepAlert('error','CEP não encontrado. Verifique e tente novamente.'); return; }
            if(enderecoInput){
                enderecoInput.value = data.logradouro || '';
                if(!data.logradouro){ showCepAlert('warn','Logradouro não informado pelo CEP. Preencha manualmente.'); }
            }
            if(bairroInput) bairroInput.value = data.bairro || '';
            if(cidadeInput) cidadeInput.value = data.localidade || '';
            if(estadoInput) estadoInput.value = data.uf || '';
            if(numeroInput){ numeroInput.focus(); }
        } catch(e){ console.error(e); showCepAlert('error','Não foi possível buscar o CEP agora. Tente novamente.'); }
        finally { setCepLoading(false); }
    }

    if(btnBuscar){ btnBuscar.addEventListener('click', buscarCep); }
    if(cepInput){
        cepInput.addEventListener('keydown', (ev) => { if(ev.key === 'Enter'){ ev.preventDefault(); buscarCep(); } });
        cepInput.addEventListener('blur', () => { const v = sanitizeCep(cepInput.value); if(v.length === 8){ buscarCep(); } });
        cepInput.addEventListener('input', () => { cepInput.value = maskCEP(cepInput.value); });
    }
});
</script>
@endpush