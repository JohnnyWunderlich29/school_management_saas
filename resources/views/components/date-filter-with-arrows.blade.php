@props([
    'title' => 'Período de datas',
    'name' => '',
    'label' => '',
    'value' => '',
    'required' => false,
    'dataFimName' => '',
    'dataFimValue' => '',
])

<div>


    <!-- Período de datas com navegação conjunta -->
    <div class="flex flex-col items-center justify-center space-x-3">
        <span class="text-sm font-medium text-gray-700">{{ $title }}</span>
        <div class="flex items-center">
            <!-- Seta para esquerda (navegar período anterior) -->
            <button type="button" onclick="event.preventDefault(); event.stopPropagation(); navigatePeriod(-1)"
                class="text-gray-600 hover:text-gray-800 focus:outline-none p-2 rounded-full hover:bg-gray-100 transition-colors"
                title="Período anterior">
                <i class="fas fa-chevron-left text-lg"></i>
            </button>

            <!-- Período de datas -->

            <div class="flex text-center cursor-pointer p-3 rounded-lg transition-colors bg-white">
                <div class="flex items-center space-x-2">
                    <span id="{{ $name }}_formatted"
                        class="text-sm font-medium text-gray-700 hover:text-blue-600"
                        onclick="(function(){ const el = document.getElementById('{{ $name }}'); if(!el) return; try { el.focus(); if (el.showPicker) { el.showPicker(); } else { el.click(); } } catch(e){ el.click(); } })()"></span>
                    <span class="text-gray-400">-</span>
                    @if ($dataFimName)
                        <span id="{{ $dataFimName }}_formatted"
                            class="text-sm font-medium text-gray-700 hover:text-blue-600"
                            onclick="(function(){ const el = document.getElementById('{{ $dataFimName }}'); if(!el) return; try { el.focus(); if (el.showPicker) { el.showPicker(); } else { el.click(); } } catch(e){ el.click(); } })()"></span>
                    @endif
                </div>

                <!-- Inputs ocultos -->
                <input type="date" name="{{ $name }}" id="{{ $name }}"
                    value="{{ request($name, $value) }}" class="opacity-0 w-0 h-0" {{ $required ? 'required' : '' }}
                    onchange="updateDateDisplay('{{ $name }}')">
                @if ($dataFimName)
                    <input type="date" name="{{ $dataFimName }}" id="{{ $dataFimName }}"
                        value="{{ request($dataFimName, $dataFimValue) }}" class="opacity-0 w-0 h-0"
                        onchange="updateDateDisplay('{{ $dataFimName }}')">
                @endif
            </div>

            <!-- Seta para direita (navegar próximo período) -->
            <button type="button" onclick="event.preventDefault(); event.stopPropagation(); navigatePeriod(1)"
                class="text-gray-600 hover:text-gray-800 focus:outline-none p-2 rounded-full hover:bg-gray-100 transition-colors"
                title="Próximo período">
                <i class="fas fa-chevron-right text-lg"></i>
            </button>
        </div>
    </div>
</div>

<script>
    function navigatePeriod(direction) {
        const dataInicio = document.getElementById('{{ $name }}');
        const dataFim = document.getElementById('{{ $dataFimName }}');

        if (!dataInicio) return;

        const baseDate = dataInicio.value ? new Date(dataInicio.value + 'T00:00:00') : new Date();
        // Navegar por mês
        const targetMonth = new Date(baseDate.getFullYear(), baseDate.getMonth() + direction, 1);
        const startOfMonth = new Date(targetMonth.getFullYear(), targetMonth.getMonth(), 1);
        const endOfMonth = new Date(targetMonth.getFullYear(), targetMonth.getMonth() + 1, 0);

        const toYMD = (d) => {
            const y = d.getFullYear();
            const m = String(d.getMonth() + 1).padStart(2, '0');
            const day = String(d.getDate()).padStart(2, '0');
            return `${y}-${m}-${day}`;
        };

        dataInicio.value = toYMD(startOfMonth);

        if (dataFim) {
            dataFim.value = toYMD(endOfMonth);
            dataFim.dispatchEvent(new Event('change', { bubbles: true }));
            updateDateDisplay('{{ $dataFimName }}');
        }

        dataInicio.dispatchEvent(new Event('change', { bubbles: true }));
        updateDateDisplay('{{ $name }}');
    }

    function navigateDate(fieldName, direction) {
        const input = document.getElementById(fieldName);

        if (!input) return;

        let currentDate = input.value ? new Date(input.value + 'T00:00:00') : new Date();

        // Navegar por dia
        currentDate.setDate(currentDate.getDate() + direction);

        const year = currentDate.getFullYear();
        const month = String(currentDate.getMonth() + 1).padStart(2, '0');
        const day = String(currentDate.getDate()).padStart(2, '0');

        input.value = `${year}-${month}-${day}`;

        // Disparar evento de mudança para ativar o filtro automático
        input.dispatchEvent(new Event('change', {
            bubbles: true
        }));

        // Atualizar o display
        updateDateDisplay(fieldName);
    }

    function formatDateToBR(dateString) {
        if (!dateString) return '';

        const date = new Date(dateString + 'T00:00:00');
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = String(date.getFullYear()).slice(-2); // Apenas os últimos 2 dígitos do ano

        return `${day}/${month}/${year}`;
    }

    function updateDateDisplay(fieldName) {
        const field = document.getElementById(fieldName);
        const display = document.getElementById(fieldName + '_formatted');

        if (!display) {
            return; // Se o elemento de display não existir, não fazer nada
        }

        if (field && field.value) {
            display.textContent = formatDateToBR(field.value);
        } else {
            display.textContent = '';
        }
    }

    // Inicializar quando a página carregar
    document.addEventListener('DOMContentLoaded', function() {
        const dataInicio = document.getElementById('{{ $name }}');
        const dataFim = document.getElementById('{{ $dataFimName }}');

        // Definir início e fim do mês atual quando não houver valores
        if (dataInicio && !dataInicio.value) {
            const now = new Date();
            const start = new Date(now.getFullYear(), now.getMonth(), 1);
            const y = start.getFullYear();
            const m = String(start.getMonth() + 1).padStart(2, '0');
            const d = String(start.getDate()).padStart(2, '0');
            dataInicio.value = `${y}-${m}-${d}`;
            dataInicio.dispatchEvent(new Event('change', { bubbles: true }));
        }

        if (dataFim && !dataFim.value) {
            const now = new Date();
            const end = new Date(now.getFullYear(), now.getMonth() + 1, 0);
            const y = end.getFullYear();
            const m = String(end.getMonth() + 1).padStart(2, '0');
            const d = String(end.getDate()).padStart(2, '0');
            dataFim.value = `${y}-${m}-${d}`;
            dataFim.dispatchEvent(new Event('change', { bubbles: true }));
        }

        // Atualizar o display inicial para ambas as datas
        updateDateDisplay('{{ $name }}');
        if (dataFim) {
            updateDateDisplay('{{ $dataFimName }}');
        }

        // Atualizar display quando qualquer data mudar
        if (dataInicio) {
            dataInicio.addEventListener('change', function() {
                updateDateDisplay('{{ $name }}');
            });
        }

        if (dataFim) {
            dataFim.addEventListener('change', function() {
                updateDateDisplay('{{ $dataFimName }}');
            });
        }
    });
</script>
