<!-- Etapa 4: Período e Duração -->
<form id="step-4-form">
<div class="space-y-6">
    <div class="border-b border-gray-200 pb-4">
        <h3 class="text-lg font-medium text-gray-900 flex items-center">
            <i class="fas fa-calendar text-blue-600 mr-2"></i>
            Período e Duração
        </h3>
        <p class="text-gray-600 mt-1">Defina o período de execução e duração do planejamento</p>
    </div>

    <!-- Seleção do Período -->
    <div class="mb-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Período do Planejamento</h3>
        
        <!-- Campo oculto para tipo_periodo fixo -->
        <input type="hidden" name="tipo_periodo" value="mensal">
        
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h4 class="text-sm font-medium text-blue-800">Planejamento Mensal</h4>
                    <p class="text-sm text-blue-700">
                        Selecione o período desejado. No mês atual, considera de hoje até o último dia.
                    </p>
                </div>
            </div>
        </div>

        <!-- Seleção de Período -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Período Atual -->
            <div class="relative">
                <input type="radio" name="periodo_selecionado" value="atual" id="periodo_atual" class="peer sr-only" checked>
                <label for="periodo_atual" class="flex cursor-pointer rounded-lg border border-gray-300 bg-white p-4 shadow-sm hover:border-blue-500 peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-focus:ring-2 peer-focus:ring-blue-500">
                    <div class="flex flex-1 flex-col">
                        <span class="block text-sm font-medium text-gray-900" id="periodo_atual_titulo">Período Atual</span>
                        <span class="mt-1 text-sm text-gray-500" id="periodo_atual_datas">Carregando...</span>
                        <span class="mt-2 text-xs text-blue-600" id="periodo_atual_dias">Carregando...</span>
                    </div>
                </label>
            </div>

            <!-- Próximo Período -->
            <div class="relative">
                <input type="radio" name="periodo_selecionado" value="proximo" id="periodo_proximo" class="peer sr-only">
                <label for="periodo_proximo" class="flex cursor-pointer rounded-lg border border-gray-300 bg-white p-4 shadow-sm hover:border-blue-500 peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-focus:ring-2 peer-focus:ring-blue-500">
                    <div class="flex flex-1 flex-col">
                        <span class="block text-sm font-medium text-gray-900" id="periodo_proximo_titulo">Próximo Período</span>
                        <span class="mt-1 text-sm text-gray-500" id="periodo_proximo_datas">Carregando...</span>
                        <span class="mt-2 text-xs text-blue-600" id="periodo_proximo_dias">Carregando...</span>
                    </div>
                </label>
            </div>
        </div>

        <!-- Campos ocultos para enviar ao backend -->
        <input type="hidden" name="data_inicio" id="data_inicio">
        <input type="hidden" name="data_fim" id="data_fim">
        <input type="hidden" name="numero_dias" id="numero_dias">
        <input type="hidden" name="aulas_por_semana" id="aulas_por_semana" value="5">
        <input type="hidden" name="carga_horaria_aula" id="carga_horaria_aula" value="0.75">
        <input type="hidden" name="ano_letivo" value="{{ date('Y') }}">
        <input type="hidden" name="bimestre" value="1">

        <!-- Resumo do Período Selecionado -->
        <div id="resumo-periodo" class="mt-6">
            <!-- O resumo será preenchido via JavaScript -->
        </div>
    </div>

    <!-- Resumo do Planejamento -->
    <!--
        <div class="border-t border-gray-200 pt-6">
        <h4 class="text-md font-medium text-gray-900 mb-4">Resumo do Planejamento</h4>
        
        <div class="bg-gray-50 p-4 rounded-lg">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <div>
                    <span class="font-medium text-gray-700">Total de Aulas:</span>
                    <span id="total-aulas" class="text-blue-600 font-semibold">-</span>
                </div>
                <div>
                    <span class="font-medium text-gray-700">Duração do Período:</span>
                    <span id="duracao-periodo" class="text-blue-600 font-semibold">-</span>
                </div>
                <div>
                    <span class="font-medium text-gray-700">Carga Horária Total:</span>
                    <span id="carga-total" class="text-blue-600 font-semibold">-</span>
                </div>
            </div>
        </div>
    </div>
    -->

    <!-- Dicas removidas conforme solicitação -->
</div>
</form>

@push('styles')
<style>
/* Estilos para radio buttons customizados */
.radio-card input[type="radio"]:checked + label {
    border-color: #3b82f6;
    background-color: #eff6ff;
    box-shadow: 0 0 0 1px #3b82f6;
}

.radio-card input[type="radio"]:checked + label .text-gray-900 {
    color: #1e40af;
}

.radio-card:hover label {
    border-color: #93c5fd;
}
</style>
@endpush

<script>
(function() {
    const dataInicioInput = document.getElementById('data_inicio');
    const dataFimInput = document.getElementById('data_fim');
    const numeroDiasInput = document.getElementById('numero_dias');
    const periodoAtualRadio = document.getElementById('periodo_atual');
    const periodoProximoRadio = document.getElementById('periodo_proximo');
    const aulasPorSemanaInput = document.getElementById('aulas_por_semana');
    const cargaHorariaAulaInput = document.getElementById('carga_horaria_aula');

    // Garantir stores globais
    window.planejamentoWizard = window.planejamentoWizard || { formData: {} };
    window.planejamentoWizard.formData[4] = window.planejamentoWizard.formData[4] || {};
    window.wizardData = window.wizardData || {};
    window.wizardData.step4 = window.wizardData.step4 || {};

    // Formata data no padrão YYYY-MM-DD sem efeitos de timezone
    function formatDateYMD(date) {
        const y = date.getFullYear();
        const m = String(date.getMonth() + 1).padStart(2, '0');
        const d = String(date.getDate()).padStart(2, '0');
        return `${y}-${m}-${d}`;
    }

    // Converte string YYYY-MM-DD para Date usando componentes locais
    function parseYMD(ymd) {
        const [y, m, d] = ymd.split('-').map(n => parseInt(n, 10));
        return new Date(y, m - 1, d);
    }

    // Função para obter o primeiro e último dia do mês
    function getPeriodoMensal(ano, mes) {
        // Garantir que a data de início seja o primeiro dia do mês selecionado
        const dataInicio = new Date(ano, mes, 1);
        // Garantir que a data de fim seja o último dia do mês selecionado
        const dataFim = new Date(ano, mes + 1, 0); // Último dia do mês
        
        return {
            inicio: dataInicio,
            fim: dataFim,
            inicioFormatado: formatDateYMD(dataInicio),
            fimFormatado: formatDateYMD(dataFim)
        };
    }

    // Função para calcular dias úteis no período
    function calcularDiasUteis(dataInicio, dataFim) {
        let diasUteis = 0;
        let dataAtual = new Date(dataInicio);
        
        while (dataAtual <= dataFim) {
            // Se não é sábado (6) nem domingo (0)
            if (dataAtual.getDay() !== 0 && dataAtual.getDay() !== 6) {
                diasUteis++;
            }
            dataAtual.setDate(dataAtual.getDate() + 1);
        }
        
        return diasUteis;
    }

    // Função para inicializar os períodos
    function inicializarPeriodos() {
        const hoje = new Date();
        const anoAtual = hoje.getFullYear();
        const mesAtual = hoje.getMonth();

        // Período atual (mês atual)
        const periodoAtual = getPeriodoMensal(anoAtual, mesAtual);
        // Para o mês atual, permitir planejamento a partir de hoje até o último dia
        const inicioAtualEfetivo = new Date(hoje.getFullYear(), hoje.getMonth(), hoje.getDate());
        const diasUteisAtual = calcularDiasUteis(inicioAtualEfetivo, periodoAtual.fim);

        // Próximo período (próximo mês)
        const proximoMes = mesAtual === 11 ? 0 : mesAtual + 1;
        const proximoAno = mesAtual === 11 ? anoAtual + 1 : anoAtual;
        const periodoProximo = getPeriodoMensal(proximoAno, proximoMes);
        const diasUteisProximo = calcularDiasUteis(periodoProximo.inicio, periodoProximo.fim);

        // Verificar se os elementos existem e atualizar interface do período atual
        const elemTituloAtual = document.getElementById('periodo_atual_titulo');
        const elemDatasAtual = document.getElementById('periodo_atual_datas');
        const elemDiasAtual = document.getElementById('periodo_atual_dias');

        if (elemTituloAtual && elemDatasAtual && elemDiasAtual) {
            elemTituloAtual.textContent = 
                `${periodoAtual.inicio.toLocaleDateString('pt-BR', { month: 'long', year: 'numeric' })}`;
            elemDatasAtual.textContent = 
                `${inicioAtualEfetivo.toLocaleDateString('pt-BR')} a ${periodoAtual.fim.toLocaleDateString('pt-BR')}`;
            elemDiasAtual.textContent = 
                `${diasUteisAtual} dias úteis`;
        }

        // Verificar elementos e atualizar interface do próximo período
        const elemTituloProximo = document.getElementById('periodo_proximo_titulo');
        const elemDatasProximo = document.getElementById('periodo_proximo_datas');
        const elemDiasProximo = document.getElementById('periodo_proximo_dias');

        if (elemTituloProximo && elemDatasProximo && elemDiasProximo) {
            elemTituloProximo.textContent = 
                `${periodoProximo.inicio.toLocaleDateString('pt-BR', { month: 'long', year: 'numeric' })}`;
            elemDatasProximo.textContent = 
                `${periodoProximo.inicio.toLocaleDateString('pt-BR')} a ${periodoProximo.fim.toLocaleDateString('pt-BR')}`;
            elemDiasProximo.textContent = 
                `${diasUteisProximo} dias úteis`;
        }

        // Armazenar dados para uso posterior
        if (periodoAtualRadio) {
            // Enviar para backend a partir de hoje (mês atual) até fim do mês
            periodoAtualRadio.dataset.dataInicio = formatDateYMD(inicioAtualEfetivo);
            periodoAtualRadio.dataset.dataFim = periodoAtual.fimFormatado;
            periodoAtualRadio.dataset.diasUteis = diasUteisAtual;
            periodoAtualRadio.checked = true; // Definir como selecionado por padrão
        }

        if (periodoProximoRadio) {
            periodoProximoRadio.dataset.dataInicio = periodoProximo.inicioFormatado;
            periodoProximoRadio.dataset.dataFim = periodoProximo.fimFormatado;
            periodoProximoRadio.dataset.diasUteis = diasUteisProximo;
        }

        // Atualizar campos ocultos com o período atual selecionado
        atualizarCamposOcultos();
    }

    // Função para atualizar campos ocultos baseado na seleção
    function atualizarCamposOcultos() {
        const periodoSelecionado = document.querySelector('input[name="periodo_selecionado"]:checked');
        
        console.log('DEBUG: atualizarCamposOcultos chamada');
        console.log('DEBUG: periodoSelecionado:', periodoSelecionado);
        
        if (periodoSelecionado) {
            console.log('DEBUG: dataset:', periodoSelecionado.dataset);
            
            dataInicioInput.value = periodoSelecionado.dataset.dataInicio;
            dataFimInput.value = periodoSelecionado.dataset.dataFim;
            numeroDiasInput.value = periodoSelecionado.dataset.diasUteis;

            // Persistir em stores (tipo_periodo mensal + campos calculados)
            try {
                const tipoPeriodo = 'mensal';
                const aulasPorSemana = parseInt(aulasPorSemanaInput?.value || 5, 10);
                const cargaAula = parseFloat(cargaHorariaAulaInput?.value || 0.75);
                const payload = {
                    tipo_periodo: tipoPeriodo,
                    data_inicio: dataInicioInput.value || '',
                    data_fim: dataFimInput.value || '',
                    numero_dias: numeroDiasInput.value || '',
                    aulas_por_semana: aulasPorSemana,
                    carga_horaria_aula: cargaAula
                };
                window.planejamentoWizard.formData[4] = Object.assign({}, window.planejamentoWizard.formData[4], payload);
                window.wizardData.step4 = Object.assign({}, window.wizardData.step4, payload);
            } catch (e) { console.warn('STEP-4: falha ao persistir período/duração', e); }

            console.log('DEBUG: Campos preenchidos:');
            console.log('  - data_inicio:', dataInicioInput.value);
            console.log('  - numero_dias:', numeroDiasInput.value);
            
            updateResumo();
        } else {
            console.log('DEBUG: Nenhum período selecionado');
        }
    }

    // Função para atualizar resumo
    function updateResumo() {
        const dataInicio = dataInicioInput.value;
        const dataFim = dataFimInput.value;
        const diasUteis = numeroDiasInput.value;
        
        if (dataInicio && dataFim && diasUteis) {
            const inicio = parseYMD(dataInicio);
            const fim = parseYMD(dataFim);
            
            const resumoHtml = `
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-gray-900 mb-2">Resumo do Período Selecionado</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                        <div>
                            <span class="text-gray-600">Período:</span>
                            <span class="font-medium">${inicio.toLocaleDateString('pt-BR')} a ${fim.toLocaleDateString('pt-BR')}</span>
                        </div>
                        <div>
                            <span class="text-gray-600">Dias Úteis:</span>
                            <span class="font-medium">${diasUteis} dias</span>
                        </div>
                        <div>
                            <span class="text-gray-600">Tipo:</span>
                            <span class="font-medium">Mensal</span>
                        </div>
                    </div>
                </div>
            `;
            
            const resumoPeriodoElement = document.getElementById('resumo-periodo');
            if (resumoPeriodoElement) {
                resumoPeriodoElement.innerHTML = resumoHtml;
            }
        }
    }

    function calcularResumo() {
        const dataInicio = dataInicioInput.value;
        const dataFim = dataFimInput.value;
        const cargaAula = parseFloat(document.getElementById('carga_horaria_aula')?.value || 0.75);
        const aulasPorSemana = parseInt(document.getElementById('aulas_por_semana')?.value || 5);

        let totalAulas = 0;
        let duracaoTexto = '-';
        let cargaTotal = 0;

        if (dataInicio && dataFim) {
            const inicio = parseYMD(dataInicio);
            const fim = parseYMD(dataFim);
            
            // Calcular dias úteis no período
            let diasUteis = 0;
            let currentDate = new Date(inicio);
            
            while (currentDate <= fim) {
                if (currentDate.getDay() !== 0 && currentDate.getDay() !== 6) {
                    diasUteis++;
                }
                currentDate.setDate(currentDate.getDate() + 1);
            }
            
            // Calcular total de aulas (aproximadamente 1 aula por dia útil)
            totalAulas = diasUteis;
            
            // Calcular carga horária total
            cargaTotal = totalAulas * cargaAula;
            
            duracaoTexto = `${diasUteis} dias úteis`;
        }

        // Atualizar resumo - verificando se os elementos existem antes de atualizar
        const totalAulasElement = document.getElementById('total-aulas');
        const cargaTotalElement = document.getElementById('carga-total');
        const duracaoPeriodoElement = document.getElementById('duracao-periodo');
        
        if (totalAulasElement) totalAulasElement.textContent = totalAulas > 0 ? totalAulas : '-';
        if (cargaTotalElement) cargaTotalElement.textContent = totalAulas > 0 ? `${cargaTotal.toFixed(1)}h` : '-';
        if (duracaoPeriodoElement) duracaoPeriodoElement.textContent = duracaoTexto;
    }

    // Event listeners para mudança de período
    periodoAtualRadio.addEventListener('change', atualizarCamposOcultos);
    periodoProximoRadio.addEventListener('change', atualizarCamposOcultos);

    // Inicializar
    inicializarPeriodos();
    calcularResumo();

    // Persistência inicial se já houver valores
    try {
        const tipoPeriodo = 'mensal';
        const payload = {
            tipo_periodo: tipoPeriodo,
            data_inicio: dataInicioInput.value || '',
            data_fim: dataFimInput.value || '',
            numero_dias: numeroDiasInput.value || '',
            aulas_por_semana: parseInt(aulasPorSemanaInput?.value || 5, 10),
            carga_horaria_aula: parseFloat(cargaHorariaAulaInput?.value || 0.75)
        };
        window.planejamentoWizard.formData[4] = Object.assign({}, window.planejamentoWizard.formData[4], payload);
        window.wizardData.step4 = Object.assign({}, window.wizardData.step4, payload);
    } catch (e) { console.warn('STEP-4: falha na persistência inicial', e); }
})();
</script>