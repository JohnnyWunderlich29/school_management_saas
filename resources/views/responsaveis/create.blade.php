@extends('layouts.app')

@section('content')
    <x-breadcrumbs :items="[['title' => 'Responsáveis', 'url' => route('responsaveis.index')], ['title' => 'Novo Responsável']]" />

    <div class="bg-white rounded-lg shadow-sm p-6">
        <form action="{{ route('responsaveis.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Informações Pessoais -->
            <div>
                <h4 class="text-md sm:text-lg font-semibold text-gray-800 mb-4">Informações Pessoais</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <x-input type="text" name="nome" label="Nome *" :value="old('nome')" required
                            placeholder="Nome do responsável" />
                    </div>

                    <div>
                        <x-input type="text" name="sobrenome" label="Sobrenome *" :value="old('sobrenome')" required
                            placeholder="Sobrenome do responsável" />
                    </div>

                    <div>
                        <x-input type="text" name="cpf" label="CPF *" :value="old('cpf')" required
                            placeholder="000.000.000-00" id="cpf" />
                    </div>

                    <div>
                        <x-input type="text" name="rg" label="RG" :value="old('rg')"
                            placeholder="00.000.000-0" />
                    </div>

                    <div>
                        <x-input type="date" name="data_nascimento" label="Data de Nascimento" :value="old('data_nascimento')" />
                    </div>

                    <div>
                        <x-select name="genero" label="Gênero">
                            <option value="">Selecione o gênero</option>
                            <option value="Masculino" {{ old('genero') == 'Masculino' ? 'selected' : '' }}>Masculino
                            </option>
                            <option value="Feminino" {{ old('genero') == 'Feminino' ? 'selected' : '' }}>Feminino</option>
                            <option value="Outro" {{ old('genero') == 'Outro' ? 'selected' : '' }}>Outro</option>
                        </x-select>
                    </div>
                </div>
            </div>

            <!-- Informações de Contato -->
            <div>
                <h4 class="text-md sm:text-lg font-semibold text-gray-800 mb-4">Informações de Contato</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <x-input type="tel" name="telefone_principal" label="Telefone *" :value="old('telefone_principal')" required
                            placeholder="(00) 00000-0000" id="telefone_principal" />
                    </div>

                    <div>
                        <x-input type="tel" name="telefone_secundario" label="Telefone Secundário" :value="old('telefone_secundario')"
                            placeholder="(00) 00000-0000" />
                    </div>

                    <div>
                        <x-input type="email" name="email" label="E-mail" :value="old('email')"
                            placeholder="email@exemplo.com" />
                    </div>
                </div>
            </div>

            <!-- Informações de Endereço -->
            <div>
                <h4 class="text-md sm:text-lg font-semibold text-gray-800 mb-4">Informações de Endereço</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- CEP primeiro, com lupa -->
                    <div>
                        <x-input type="text" name="cep" label="CEP *" :value="old('cep')" required
                            placeholder="00000-000" id="cep" maxlength="9" class="pr-12" help="Digite o CEP e clique na lupa para buscar.">
                            <button type="button" id="btn-buscar-cep-resp-create" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-indigo-600" title="Buscar CEP" aria-label="Buscar CEP">
                                <svg id="icon-cep-resp-create" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m1.1-4.4a7.5 7.5 0 11-15 0 7.5 7.5 0 0115 0z" />
                                </svg>
                            </button>
                        </x-input>
                    </div>

                    <div>
                        <x-input type="text" name="endereco" label="Endereço *" :value="old('endereco')" required
                            placeholder="Rua, número, complemento" id="endereco" />
                    </div>

                    <div>
                        <x-input type="text" name="cidade" label="Cidade *" :value="old('cidade')" required
                            placeholder="Nome da cidade" id="cidade" />
                    </div>

                    <div>
                        <x-input type="text" name="estado" label="Estado *" :value="old('estado')" required placeholder="UF"
                            maxlength="2" id="estado" />
                    </div>
                </div>
            </div>

            <!-- Informações Profissionais -->
            <div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                    <div>
                        <x-select name="parentesco" label="Parentesco *" required>
                            <option value="">Selecione o parentesco</option>
                            <option value="Pai" {{ old('parentesco') == 'Pai' ? 'selected' : '' }}>Pai</option>
                            <option value="Mãe" {{ old('parentesco') == 'Mãe' ? 'selected' : '' }}>Mãe</option>
                            <option value="Avô" {{ old('parentesco') == 'Avô' ? 'selected' : '' }}>Avô</option>
                            <option value="Avó" {{ old('parentesco') == 'Avó' ? 'selected' : '' }}>Avó</option>
                            <option value="Tio" {{ old('parentesco') == 'Tio' ? 'selected' : '' }}>Tio</option>
                            <option value="Tia" {{ old('parentesco') == 'Tia' ? 'selected' : '' }}>Tia</option>
                            <option value="Responsável Legal"
                                {{ old('parentesco') == 'Responsável Legal' ? 'selected' : '' }}>Responsável Legal</option>
                            <option value="Outro" {{ old('parentesco') == 'Outro' ? 'selected' : '' }}>Outro</option>
                        </x-select>
                    </div>
                </div>
            </div>

            <!--Contato de emergencia-->
            <div>
                <h4 class="text-md sm:text-lg font-semibold text-gray-800 mb-4">Contato de Emergência</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="flex flex-col gap-2 items-start justify-between">
                        <div>
                            <h6 class="block text-sm font-medium text-gray-700 mb-1">É o principal contato de emergência?</h6>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="contato_emergencia" value="0" />
                            <input type="checkbox" name="contato_emergencia" value="1" checked class="sr-only peer" />
                            <div
                                class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-3 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                            </div>
                        </label>
                        <div>
                            <h6 class="block text-sm font-medium text-gray-700 mb-1">Principal contato para buscar aluno?</h6>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="autorizado_buscar" value="0" />
                            <input type="checkbox" name="autorizado_buscar" value="1" checked class="sr-only peer" />
                            <div
                                class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-3 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Botões -->
            <div class="flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-3 pt-4 border-t">
                <x-button href="{{ route('responsaveis.index') }}" color="secondary" class="w-full sm:w-auto">
                    <i class="fas fa-times mr-1"></i> 
                    <span class="hidden sm:inline">Cancelar</span>
                    <span class="sm:hidden">Cancelar</span>
                </x-button>
                <x-button type="submit" color="primary" class="w-full sm:w-auto">
                    <i class="fas fa-save mr-1"></i> 
                    <span class="hidden sm:inline">Salvar Responsável</span>
                    <span class="sm:hidden">Salvar</span>
                </x-button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
    // ViaCEP - busca de CEP e preenchimento automático
    const cepInput = document.getElementById('cep');
    const btnBuscar = document.getElementById('btn-buscar-cep-resp-create');
    const iconCep = document.getElementById('icon-cep-resp-create');
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
        if(!alertDiv){
            alertDiv = document.createElement('div');
            alertDiv.className = 'cep-alert mt-2 text-sm';
            wrapper.appendChild(alertDiv);
        }
        return alertDiv;
    }

    function showCepAlert(type, message){
        const alertDiv = ensureCepAlertContainer();
        if(!alertDiv) return;
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
