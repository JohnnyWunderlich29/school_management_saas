<!-- Etapa 2: Unidade e Turno -->
<form id="step-2-form">
<div class="space-y-6">
    <div class="border-b border-gray-200 pb-4">
        <h3 class="text-lg font-medium text-gray-900 flex items-center">
            <i class="fas fa-school text-blue-600 mr-2"></i>
            Unidade e Turno
        </h3>
        <p class="text-gray-600 mt-1">Selecione a unidade escolar e o turno para este planejamento</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Unidade Escolar -->
        <div>
            <label for="escola_nome" class="block text-sm font-medium text-gray-700 mb-2">
                Unidade Escolar
            </label>
            @if($escola)
                <input type="text" id="escola_nome" value="{{ $escola->nome }}" readonly
                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-50 text-gray-700 cursor-not-allowed">
                <input type="hidden" name="escola_id" id="escola_id" value="{{ $escola->id }}">
                <p class="text-xs text-gray-500 mt-1">Esta é a escola à qual você está vinculado</p>
            @else
                <div class="w-full px-3 py-2 border border-red-300 rounded-md shadow-sm bg-red-50 text-red-700">
                    Nenhuma escola vinculada ao seu perfil
                </div>
            @endif
        </div>

        <!-- Turno -->
        <div>
            <label for="turno_id" class="block text-sm font-medium text-gray-700 mb-2">
                Turno <span class="text-red-500">*</span>
            </label>
            <select name="turno_id" id="turno_id" required 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                <option value="">Selecione um turno</option>
                @foreach($turnos as $turno)
                    <option value="{{ $turno->id }}" 
                            {{ (old('turno_id', $planejamento->turno_id ?? '') == $turno->id) ? 'selected' : '' }}>
                        {{ $turno->nome }} ({{ substr($turno->hora_inicio, 0, 5) }} - {{ substr($turno->hora_fim, 0, 5) }})
                    </option>
                @endforeach
            </select>
            @error('turno_id')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <!-- Informações da Unidade Selecionada -->
    <div id="escola-info" class="hidden bg-gray-50 border border-gray-200 rounded-lg p-4">
        <h4 class="text-sm font-medium text-gray-900 mb-2">Informações da Unidade</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600">
            <div>
                <span class="font-medium">Endereço:</span>
                <span id="escola-endereco">-</span>
            </div>
            <div>
                <span class="font-medium">Telefone:</span>
                <span id="escola-telefone">-</span>
            </div>
            <div>
                <span class="font-medium">Diretor(a):</span>
                <span id="escola-diretor">-</span>
            </div>
            <div>
                <span class="font-medium">Modalidades:</span>
                <span id="escola-modalidades">-</span>
            </div>
        </div>
    </div>

    <!-- Informações do Turno Selecionado -->
    <div id="turno-info" class="hidden bg-gray-50 border border-gray-200 rounded-lg p-4">
        <h4 class="text-sm font-medium text-gray-900 mb-2">Informações do Turno</h4>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm text-gray-600">
            <div>
                <span class="font-medium">Horário:</span>
                <span id="turno-horario">-</span>
            </div>
            <div>
                <span class="font-medium">Duração:</span>
                <span id="turno-duracao">-</span>
            </div>
            <div>
                <span class="font-medium">Intervalos:</span>
                <span id="turno-intervalos">-</span>
            </div>
        </div>
    </div>

    <!-- Verificação de Compatibilidade -->
    <div id="compatibilidade-check" class="hidden">
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-yellow-600 mt-0.5"></i>
                </div>
                <div class="ml-3">
                    <h4 class="text-sm font-medium text-yellow-800">Verificação de Compatibilidade</h4>
                    <div id="compatibilidade-messages" class="text-sm text-yellow-700 mt-1">
                        <!-- Mensagens de compatibilidade serão inseridas aqui -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Dicas -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <i class="fas fa-lightbulb text-blue-600 mt-0.5"></i>
            </div>
            <div class="ml-3">
                <h4 class="text-sm font-medium text-blue-800">Dicas para esta etapa:</h4>
                <ul class="text-sm text-blue-700 mt-1 space-y-1">
                    <li>• A unidade escolar determina quais turmas estarão disponíveis</li>
                    <li>• O turno define os horários disponíveis para as aulas</li>
                    <li>• Verifique se a modalidade selecionada é oferecida na unidade escolhida</li>
                </ul>
            </div>
        </div>
    </div>
</div>
</form>

<script>
(function() {
    console.log('🚀 STEP-2: Script executado diretamente!');
    
    const escolaSelect = document.getElementById('escola_id');
    const turnoSelect = document.getElementById('turno_id');
    const compatibilidadeCheck = document.getElementById('compatibilidade-check');
    const escolaInfo = document.getElementById('escola-info');
    const turnoInfo = document.getElementById('turno-info');
    // Garantir stores globais
    window.planejamentoWizard = window.planejamentoWizard || { formData: {} };
    window.planejamentoWizard.formData[2] = window.planejamentoWizard.formData[2] || {};
    window.wizardData = window.wizardData || {};
    window.wizardData.step2 = window.wizardData.step2 || {};
    
    console.log('🔍 STEP-2: Elementos encontrados:', {
        escolaSelect: !!escolaSelect,
        turnoSelect: !!turnoSelect,
        compatibilidadeCheck: !!compatibilidadeCheck
    });

    // Função para carregar turnos baseado na modalidade e nível de ensino da etapa 1
    function loadTurnosFiltered() {
        console.log('=== INICIANDO loadTurnosFiltered ===');
        
        // Obter dados da etapa 1 do wizard
        const wizard = window.planejamentoWizard;
        console.log('Wizard object:', wizard);
        
        if (!wizard || !wizard.formData || !wizard.formData[1]) {
            console.log('❌ Dados da etapa 1 não encontrados');
            console.log('wizard exists:', !!wizard);
            console.log('wizard.formData exists:', !!(wizard && wizard.formData));
            console.log('wizard.formData[1] exists:', !!(wizard && wizard.formData && wizard.formData[1]));
            return;
        }

        const step1Data = wizard.formData[1];
        console.log('Step 1 data:', step1Data);
        
        const modalidadeId = step1Data.modalidade_ensino_id;
        const nivelEnsinoId = step1Data.nivel_ensino_id;
        
        console.log('Modalidade ID:', modalidadeId);
        console.log('Nível Ensino ID:', nivelEnsinoId);

        if (!modalidadeId || !nivelEnsinoId) {
            console.log('❌ Modalidade ou nível de ensino não selecionados na etapa 1');
            return;
        }

        // Limpar opções atuais (exceto a primeira)
        turnoSelect.innerHTML = '<option value="">Selecione um turno</option>';
        
        // Mostrar loading
        turnoSelect.disabled = true;
        turnoSelect.innerHTML = '<option value="">Carregando turnos...</option>';

        const apiUrl = `{{ route('api.planejamentos.turnos') }}?modalidade_id=${modalidadeId}&nivel_ensino_id=${nivelEnsinoId}`;
        console.log('🌐 Fazendo requisição para:', apiUrl);

        // Fazer requisição para buscar turnos filtrados
        fetch(apiUrl, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            console.log('📡 Response status:', response.status);
            console.log('📡 Response ok:', response.ok);
            return response.json();
        })
        .then(data => {
            console.log('📦 Dados recebidos da API:', data);
            console.log('📦 Tipo dos dados:', typeof data);
            console.log('📦 É array?', Array.isArray(data));
            console.log('📦 Quantidade de itens:', data.length);
            
            turnoSelect.innerHTML = '<option value="">Selecione um turno</option>';
            
            if (data.error) {
                console.error('❌ Erro ao carregar turnos:', data.error);
                turnoSelect.innerHTML = '<option value="">Erro ao carregar turnos</option>';
                return;
            }

            // Adicionar turnos filtrados
            console.log('🔄 Iniciando forEach para popular select...');
            data.forEach((turno, index) => {
                console.log(`📝 Processando turno ${index + 1}:`, turno);
                
                const option = document.createElement('option');
                option.value = turno.id;
                option.textContent = `${turno.nome}`;
                if (turno.hora_inicio && turno.hora_fim) {
                    option.textContent += ` (${turno.hora_inicio} - ${turno.hora_fim})`;
                }
                
                console.log(`📝 Opção criada:`, {
                    value: option.value,
                    text: option.textContent
                });
                
                turnoSelect.appendChild(option);
            });

            console.log('✅ Select populado com sucesso!');
            console.log('📋 Opções no select:', turnoSelect.options.length);

            // Reabilitar select
            turnoSelect.disabled = false;

            // Se havia um valor selecionado anteriormente, tentar restaurar
            const savedTurnoId = step1Data.turno_id || (wizard.formData[2] && wizard.formData[2].turno_id);
            if (savedTurnoId) {
                console.log('🔄 Restaurando valor selecionado:', savedTurnoId);
                turnoSelect.value = savedTurnoId;
                turnoSelect.dispatchEvent(new Event('change'));
            }
        })
        .catch(error => {
            console.error('❌ Erro ao carregar turnos:', error);
            turnoSelect.innerHTML = '<option value="">Erro ao carregar turnos</option>';
            turnoSelect.disabled = false;
        });
    }

    // Carregar turnos quando a etapa for carregada
    console.log('🕐 STEP-2: Tentando carregar turnos...');
    
    let turnosCarregados = false;
    
    // Função para tentar carregar turnos apenas uma vez
    function tentarCarregarTurnos() {
        if (turnosCarregados) {
            console.log('🔄 STEP-2: Turnos já foram carregados, ignorando...');
            return;
        }
        
        if (window.planejamentoWizard && window.planejamentoWizard.formData && window.planejamentoWizard.formData[1]) {
            console.log('🎯 STEP-2: Wizard encontrado, carregando turnos...');
            turnosCarregados = true;
            loadTurnosFiltered();
        }
    }
    
    // Tentar carregar imediatamente
    tentarCarregarTurnos();
    
    // Se não funcionou, tentar novamente após um delay
    if (!turnosCarregados) {
        setTimeout(() => {
            console.log('🕐 STEP-2: Tentando carregar turnos após delay...');
            tentarCarregarTurnos();
        }, 1000);
    }

    // Carregar informações da escola
    escolaSelect.addEventListener('change', function() {
        const escolaId = this.value;
        // Persistir em stores
        try {
            window.planejamentoWizard.formData[2].escola_id = escolaId || '';
            window.wizardData.step2.escola_id = escolaId || '';
        } catch (e) { console.warn('STEP-2: falha ao persistir escola_id', e); }
        
        if (escolaId) {
            fetch(`/api/escolas/${escolaId}`)
                .then(response => response.json())
                .then(escola => {
                    document.getElementById('escola-endereco').textContent = escola.endereco || '-';
                    document.getElementById('escola-telefone').textContent = escola.telefone || '-';
                    document.getElementById('escola-diretor').textContent = escola.diretor || '-';
                    document.getElementById('escola-modalidades').textContent = escola.modalidades?.join(', ') || '-';
                    
                    escolaInfo.classList.remove('hidden');
                    verificarCompatibilidade();
                })
                .catch(error => {
                    console.error('Erro ao carregar informações da escola:', error);
                    escolaInfo.classList.add('hidden');
                });
        } else {
            escolaInfo.classList.add('hidden');
        }
    });

    // Carregar informações do turno
    turnoSelect.addEventListener('change', function() {
        const turnoId = this.value;
        // Persistir em stores
        try {
            window.planejamentoWizard.formData[2].turno_id = turnoId || '';
            window.wizardData.step2.turno_id = turnoId || '';
        } catch (e) { console.warn('STEP-2: falha ao persistir turno_id', e); }
        
        if (turnoId) {
            fetch(`/api/turnos/${turnoId}`)
                .then(response => response.json())
                .then(turno => {
                    document.getElementById('turno-horario').textContent = 
                        `${turno.hora_inicio} - ${turno.hora_fim}`;
                    document.getElementById('turno-duracao').textContent = turno.duracao || '-';
                    document.getElementById('turno-intervalos').textContent = turno.intervalos || '-';
                    
                    turnoInfo.classList.remove('hidden');
                    verificarCompatibilidade();
                })
                .catch(error => {
                    console.error('Erro ao carregar informações do turno:', error);
                    turnoInfo.classList.add('hidden');
                });
        } else {
            turnoInfo.classList.add('hidden');
        }
    });

    function verificarCompatibilidade() {
        const escolaId = escolaSelect.value;
        const turnoId = turnoSelect.value;
        const modalidadeId = document.getElementById('modalidade_ensino_id')?.value;

        if (escolaId && turnoId && modalidadeId) {
            fetch('/api/verificar-compatibilidade', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    escola_id: escolaId,
                    turno_id: turnoId,
                    modalidade_id: modalidadeId
                })
            })
            .then(response => response.json())
            .then(data => {
                const messagesDiv = document.getElementById('compatibilidade-messages');
                messagesDiv.innerHTML = '';

                if (data.warnings && data.warnings.length > 0) {
                    data.warnings.forEach(warning => {
                        const p = document.createElement('p');
                        p.textContent = '• ' + warning;
                        messagesDiv.appendChild(p);
                    });
                    compatibilidadeCheck.classList.remove('hidden');
                } else {
                    compatibilidadeCheck.classList.add('hidden');
                }
            })
            .catch(error => {
                console.error('Erro ao verificar compatibilidade:', error);
            });
        }
    }

    // Verificar compatibilidade se os campos já estão preenchidos
    if (escolaSelect.value) {
        escolaSelect.dispatchEvent(new Event('change'));
    }
    if (turnoSelect.value) {
        turnoSelect.dispatchEvent(new Event('change'));
    }

    // Persistência inicial ao carregar etapa (se valores já existem)
    try {
        if (escolaSelect && escolaSelect.value) {
            window.planejamentoWizard.formData[2].escola_id = escolaSelect.value;
            window.wizardData.step2.escola_id = escolaSelect.value;
        }
        if (turnoSelect && turnoSelect.value) {
            window.planejamentoWizard.formData[2].turno_id = turnoSelect.value;
            window.wizardData.step2.turno_id = turnoSelect.value;
        }
    } catch (e) { console.warn('STEP-2: falha na persistência inicial', e); }
})();
</script>