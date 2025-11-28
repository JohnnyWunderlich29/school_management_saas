@extends('layouts.app')

@section('title', 'Calendário Escolar')

@section('content')
    <!-- FullCalendar CSS -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
    <!-- Ajuste da paleta dos botões do FullCalendar para o primário do sistema (indigo) -->
    <style>
        /* FullCalendar v5/6: usar variáveis de botão para garantir consistência */
        .fc {
            --fc-button-bg-color: #4f46e5; /* indigo-600 */
            --fc-button-border-color: #4f46e5;
            --fc-button-text-color: #ffffff;
            --fc-button-hover-bg-color: #4338ca; /* indigo-700 */
            --fc-button-hover-border-color: #4338ca;
            --fc-button-active-bg-color: #3730a3; /* indigo-800 */
            --fc-button-active-border-color: #3730a3;
        }

        .fc .fc-button:disabled {
            opacity: .6;
            cursor: not-allowed;
        }

        /* Destaque visual para fins de semana (sábado e domingo) */
        .fc .fc-day-sat .fc-daygrid-day-frame,
        .fc .fc-day-sun .fc-daygrid-day-frame {
            background-color: #f8fafc; /* slate-50 */
        }

        .fc .fc-timegrid-col.fc-day-sat .fc-timegrid-col-frame,
        .fc .fc-timegrid-col.fc-day-sun .fc-timegrid-col-frame {
            background-color: #f8fafc; /* slate-50 */
        }

        .fc .fc-col-header-cell.fc-day-sat,
        .fc .fc-col-header-cell.fc-day-sun {
            background-color: #f1f5f9; /* slate-100 */
        }

        /* Ajustes de responsividade do header do FullCalendar no mobile */
        @media (max-width: 640px) {
            /* Evitar que o header extrapole a largura e permitir quebra em duas linhas */
            .fc .fc-header-toolbar {
                flex-wrap: wrap;
                gap: 0.5rem;
            }
            .fc .fc-toolbar-chunk {
                flex: 1 1 100%;
                display: flex;
                justify-content: space-between;
            }
            .fc .fc-toolbar-title {
                font-size: 1.25rem;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            /* Evitar scroll horizontal no container do calendário */
            #calendar {
                overflow-x: hidden;
                max-width: 100%;
            }

            /* Melhorias visuais na listWeek (mobile): reduzir negrito e ajustar layout */
            .fc .fc-list-day-cushion {
                font-weight: 500; /* reduzir negrito do título do dia */
                background-color: #f8fafc; /* slate-50 */
                border-radius: 0.5rem;
                padding: 0.5rem 0.75rem;
            }
            .fc .fc-list-day-side-text {
                font-weight: 400;
                color: #64748b; /* slate-500 */
            }
            .fc .fc-list-event {
                padding: 0.25rem 0.5rem;
            }
            .fc .fc-list-table td {
                padding: 0.5rem 0.75rem;
            }
        }
    </style>

    <div class="min-h-screen" x-data="calendarPage()" x-init="init()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <x-breadcrumbs :items="[['title' => 'Calendário', 'url' => route('calendario.index')]]" />

<x-card id="calendar-container">
            <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h1 class="text-3xl md:text-4xl font-bold text-gray-900">Calendário Escolar</h1>
                    <p class="mt-1 text-sm text-gray-600">Visão mensal, semanal e agenda de eventos</p>
                </div>
                <div class="flex sm:items-center gap-2">
                    <x-button class="w-full sm:w-auto" color="primary" @click="openCreate()">
                        <i class="fas fa-plus mr-2"></i> Novo Evento
                    </x-button>
                    <x-button href="{{ route('calendario.pdf') }}?ano={{ request('ano', date('Y')) }}" x-bind:href="`{{ route('calendario.pdf') }}?ano=${filters.ano}`" color="secondary" class="w-full sm:w-auto inline-flex items-center gap-2" target="_blank">
                        <i class="fas fa-file-pdf"></i> Exportar PDF Anual
                    </x-button>
                </div>
            </div>

            <!-- Mobile: abas semanais para alternar rapidamente entre Agenda e Grade -->
            <div class="sm:hidden mb-4">
                <div class="inline-flex rounded-lg overflow-hidden border border-gray-200">
                    <button type="button"
                        @click="setMobileWeekly('listWeek')"
                        :class="mobileWeeklyMode === 'listWeek' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700'"
                        class="px-3 py-2 text-sm font-medium">
                        Agenda semanal
                    </button>
                    <button type="button"
                        @click="setMobileWeekly('timeGridWeek')"
                        :class="mobileWeeklyMode === 'timeGridWeek' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700'"
                        class="px-3 py-2 text-sm font-medium border-l border-gray-200">
                        Grade semanal
                    </button>
                </div>
            </div>

            @php
                $anoAtual = (int) date('Y');
                // Mostrar dois anos anteriores e um futuro
                $anos = [];
                for ($i = -2; $i <= 1; $i++) {
                    $y = $anoAtual + $i;
                    $anos[(string) $y] = (string) $y;
                }
            @endphp
            <x-collapsible-filter title="Filtros do Calendário" :action="route('calendario.index')" :clear-route="route('calendario.index')"
                target="calendar-container">
                <x-filter-field name="categoria" label="Categoria" type="select" :options="[
                    'aula' => 'Aulas',
                    'feriado' => 'Feriados',
                    'recesso' => 'Recesso',
                    'avaliacao' => 'Avaliações',
                    'evento' => 'Eventos',
                    'matricula' => 'Matrículas',
                ]" emptyOption="Todas" />
                <x-filter-field name="audiencia" label="Audiência" type="select" :options="[
                    'gestores' => 'Gestores',
                    'docentes' => 'Docentes',
                    'responsaveis' => 'Responsáveis',
                    'alunos' => 'Alunos',
                ]" emptyOption="Todos" />
                <x-filter-field name="ano" label="Ano Letivo" type="select" :options="$anos" :emptyOption="false" />
            </x-collapsible-filter>

            

            
            
                <div class="p-4">
                    <div class="relative">
                        <div id="calendar"></div>
                        <x-loading-overlay id="calendar-loading" />
                    </div>
                </div>
                <div class="mt-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-2">Legenda de categorias</h2>
                    <div class="flex flex-wrap gap-3 text-sm text-gray-700">
                        <span class="inline-flex items-center gap-2"><span class="w-3 h-3 rounded"
                                style="background:#4f46e5"></span> Aula</span>
                        <span class="inline-flex items-center gap-2"><span class="w-3 h-3 rounded"
                                style="background:#ef4444"></span> Feriado</span>
                        <span class="inline-flex items-center gap-2"><span class="w-3 h-3 rounded"
                                style="background:#f59e0b"></span> Recesso</span>
                        <span class="inline-flex items-center gap-2"><span class="w-3 h-3 rounded"
                                style="background:#7c3aed"></span> Avaliação</span>
                        <span class="inline-flex items-center gap-2"><span class="w-3 h-3 rounded"
                                style="background:#10b981"></span> Evento</span>
                        <span class="inline-flex items-center gap-2"><span class="w-3 h-3 rounded"
                                style="background:#3b82f6"></span> Matrícula</span>
                    </div>
                </div>
            </x-card>
            <!-- Modal para criar/editar eventos -->
            <x-modal name="event-modal" maxWidth="w-11/12 md:w-5/6 lg:w-2/3">
                <x-slot name="title">
                    <span x-text="form.id ? 'Editar Evento' : 'Novo Evento'"></span>
                </x-slot>

                <form @submit.prevent="save">
                    <div class="grid grid-cols-2 md:grid-cols-2 gap-6">
                        <!-- Título -->
                        <div class="mb-4">
                            <label for="title" class="block text-sm font-medium text-gray-700">Título</label>
                            <x-input id="title" name="title" type="text" x-model="form.title" x-ref="firstField"
                                required class="mt-1 w-full" />
                            <p x-show="errors.title" x-text="errors.title" class="mt-1 text-sm text-red-600"></p>
                        </div>

                        <!-- Categoria -->
                        <div class="mb-4">
                            <label for="categoria" class="block text-sm font-medium text-gray-700">Categoria</label>
                            <x-select id="categoria" name="categoria" x-model="form.categoria" class="mt-1 w-full">
                                <option value="evento">Evento</option>
                                <option value="aula">Aula</option>
                                <option value="feriado">Feriado</option>
                                <option value="recesso">Recesso</option>
                                <option value="avaliacao">Avaliação</option>
                                <option value="matricula">Matrícula</option>
                            </x-select>
                        </div>

                        <!-- Audiência -->
                        <div class="mb-4">
                            <label for="audiencia" class="block text-sm font-medium text-gray-700">Audiência</label>
                            <x-select id="audiencia" name="audiencia" x-model="form.audiencia" class="mt-1 w-full">
                                <option value="gestores">Gestores</option>
                                <option value="docentes">Docentes</option>
                                <option value="responsaveis">Responsáveis</option>
                                <option value="alunos">Alunos</option>
                            </x-select>
                        </div>

                        <!-- Dia inteiro -->
                        <div class="mb-4">
                            <label class="flex items-center">
                                <input type="checkbox" x-model="form.allDay"
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-700">Dia inteiro</span>
                            </label>
                        </div>

                        <!-- Data/Hora de início -->
                        <div class="mb-4">
                            <label for="start" class="block text-sm font-medium text-gray-700">
                                <span x-text="form.allDay ? 'Data de início' : 'Data e hora de início'"></span>
                            </label>
                            <x-input id="start" name="start" x-bind:type="form.allDay ? 'date' : 'datetime-local'"
                                x-model="form.start" required class="mt-1 w-full" />
                            <p x-show="errors.start" x-text="errors.start" class="mt-1 text-sm text-red-600"></p>
                        </div>

                        <!-- Data/Hora de fim -->
                        <div class="mb-4">
                            <label for="end" class="block text-sm font-medium text-gray-700">
                                <span
                                    x-text="form.allDay ? 'Data de fim' : 'Data e hora de fim'"></span>
                            </label>
                            <x-input id="end" name="end" x-bind:type="form.allDay ? 'date' : 'datetime-local'"
                                x-model="form.end" class="mt-1 w-full" />
                            <p class="text-sm text-gray-600">opcional</p>
                            <p x-show="errors.end" x-text="errors.end" class="mt-1 text-sm text-red-600"></p>
                        </div>

                        <!-- Descrição -->
                        <div class="mb-4 col-span-2">
                            <label for="descricao" class="block text-sm font-medium text-gray-700">Descrição</label>
                            <textarea x-model="form.descricao" id="descricao" rows="3"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                placeholder="Descrição opcional do evento..."></textarea>
                        </div>

                        <x-slot name="footer">
                            <div class="flex justify-end space-x-2">
                                <button @click="$dispatch('close-modal', 'event-modal')" type="button"
                                    class="inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:text-sm">
                                    Cancelar
                                </button>
                                <button x-show="form.id" @click.prevent="confirmDelete" type="button" x-bind:disabled="deleting"
                                    class="inline-flex justify-center rounded-md border border-red-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-red-700 hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:text-sm"
                                    :class="{ 'opacity-50 cursor-not-allowed': deleting }">
                                    <template x-if="deleting">
                                        <span>
                                            <i class="fas fa-spinner fa-spin mr-1"></i>
                                            Excluindo...
                                        </span>
                                    </template>
                                    <template x-if="!deleting">
                                        <span>Excluir</span>
                                    </template>
                                </button>
                                <button type="button" @click.prevent="save" x-bind:disabled="saving"
                                    class="inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:text-sm"
                                    :class="{ 'opacity-50 cursor-not-allowed': saving }">
                                    <template x-if="saving">
                                        <span>
                                            <i class="fas fa-spinner fa-spin mr-1"></i>
                                            <span x-text="form.id ? 'Atualizando...' : 'Salvando...'"></span>
                                        </span>
                                    </template>
                                    <template x-if="!saving">
                                        <span x-text="form.id ? 'Atualizar' : 'Salvar'"></span>
                                    </template>
                                </button>
                            </div>
                        </x-slot>
                    </div>
                </form>

            </x-modal>
        </div>
    </div>

    <!-- Modal de Confirmação -->
    <x-confirmation-modal id="delete-event-confirmation" title="Confirmar Exclusão"
        message="Tem certeza que deseja excluir este evento?" confirm-text="Excluir" cancel-text="Cancelar"
        confirm-color="red" />
    </div>
    </div>

    <script>
        // Interceptar o submit do filtro para usar refetchEvents em vez de recarregar a página
        document.addEventListener('DOMContentLoaded', function() {
            const filterForm = document.getElementById('autoFilterForm');
            if (filterForm) {
                // Remover o event listener padrão do componente collapsible-filter
                const newForm = filterForm.cloneNode(true);
                filterForm.parentNode.replaceChild(newForm, filterForm);

                // Adicionar nosso próprio event listener
                newForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    // Obter os valores do formulário
                    const formData = new FormData(newForm);
                    const params = {};

                    for (let [key, value] of formData.entries()) {
                        if (value && value.trim() !== '') {
                            params[key] = value;
                        }
                    }

                    // Atualizar os filtros no Alpine.js
                    const calendarComponent = Alpine.$data(document.querySelector(
                        '[x-data*="calendarPage"]'));
                    if (calendarComponent) {
                        calendarComponent.filters = {
                            categoria: params.categoria || '',
                            audiencia: params.audiencia || '',
                            ano: params.ano || new Date().getFullYear().toString()
                        };

                        // Refetch dos eventos do FullCalendar
                        if (calendarComponent.calendar) {
                            calendarComponent.calendar.refetchEvents();
                        }
                    }

                    // Atualizar a URL sem recarregar a página
                    const urlParams = new URLSearchParams();
                    for (let [key, value] of Object.entries(params)) {
                        urlParams.append(key, value);
                    }
                    const newUrl = urlParams.toString() ?
                        `${window.location.pathname}?${urlParams.toString()}` :
                        window.location.pathname;
                    window.history.replaceState(null, '', newUrl);
                });

                // Também interceptar mudanças nos selects para filtro automático
                const selectInputs = newForm.querySelectorAll('select');
                selectInputs.forEach(select => {
                    select.addEventListener('change', function() {
                        // Trigger do submit personalizado
                        newForm.dispatchEvent(new Event('submit'));
                    });
                });
            }
        });
    </script>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales-all.min.js"></script>
        <script>
            function calendarPage() {
                return {
                    filters: {
                        categoria: '{{ request('categoria') ?: 'todos' }}',
                        audiencia: '{{ request('audiencia') ?: 'todos' }}',
                        ano: '{{ request('ano') ?: date('Y') }}'
                    },
                    form: {
                        id: null,
                        title: '',
                        categoria: 'evento',
                        audiencia: 'gestores',
                        start: '',
                        end: '',
                        allDay: false,
                        descricao: ''
                    },
                    mobileWeeklyMode: 'listWeek',
                    currentEventId: null,
                    calendar: null,
                    errors: {},
                    saving: false,
                    deleting: false,
                    calendarLoading: false,
                    init() {
                        var calendarEl = document.getElementById('calendar');
                        var self = this;
                        const isMobileInit = window.innerWidth < 640;
                        const initialView = isMobileInit ? 'listWeek' : 'dayGridMonth';
                        this.calendar = new FullCalendar.Calendar(calendarEl, {
                            initialView: initialView,
                            timeZone: 'local',
                            locale: 'pt-br',
                            height: 'auto',
                            views: {
                                listWeek: {
                                    listDayFormat: { weekday: 'short', day: '2-digit' },
                                    listDaySideFormat: { month: 'short' }
                                }
                            },
                            headerToolbar: isMobileInit ? {
                                left: 'prev,next',
                                center: 'title',
                                right: ''
                            } : {
                                left: 'prev,next today',
                                center: 'title',
                                right: 'dayGridMonth,timeGridWeek,listMonth'
                            },
                            navLinks: true,
                            weekNumbers: false,
                            editable: true,
                            eventStartEditable: true,
                            eventDurationEditable: true,
                            eventOrder: 'start',
                            buttonText: {
                                today: 'Hoje',
                                month: 'Mês',
                                week: 'Semana',
                                list: 'Agenda'
                            },
                            events: {
                                url: '{{ route('calendario.events') }}',
                                method: 'GET',
                                extraParams: function() {
                                    return {
                                        categoria: self.filters.categoria,
                                        audiencia: self.filters.audiencia,
                                        ano: self.filters.ano,
                                    };
                                }
                            },
                            eventTimeFormat: {
                                hour: '2-digit',
                                minute: '2-digit',
                                meridiem: false
                            },
                            eventDisplay: 'block',
                            loading(isLoading) {
                                self.calendarLoading = !!isLoading;
                                const overlay = document.getElementById('calendar-loading');
                                if (overlay) overlay.classList.toggle('hidden', !isLoading);
                            },
                            windowResize(arg) {
                                const isMobile = window.innerWidth < 640;
                                // Ajustar header toolbar para mobile/desktop
                                self.calendar.setOption('headerToolbar', isMobile ? {
                                    left: 'prev,next',
                                    center: 'title',
                                    right: ''
                                } : {
                                    left: 'prev,next today',
                                    center: 'title',
                                    right: 'dayGridMonth,timeGridWeek,listMonth'
                                });

                                // Em mobile, preferir agenda semanal; em desktop, voltar para mensal
                                const currentView = self.calendar.view ? self.calendar.view.type : null;
                                if (isMobile && currentView === 'dayGridMonth') {
                                    self.calendar.changeView('listWeek');
                                    self.mobileWeeklyMode = 'listWeek';
                                } else if (!isMobile && currentView === 'listWeek') {
                                    self.calendar.changeView('dayGridMonth');
                                }
                            },
                            eventDrop(info) {
                                self.updateEventTime(info.event, info.revert);
                            },
                            eventResize(info) {
                                self.updateEventTime(info.event, info.revert);
                            },
                            eventClick(info) {
                                self.openEdit(info.event);
                            },
                            dateClick(info) {
                                self.openCreateOnDate(info.dateStr);
                            }
                        });
                        this.calendar.setOption('headerToolbar', isMobileInit ? {
                            left: 'prev,next',
                            center: 'title',
                            right: ''
                        } : {
                            left: 'prev,next today',
                            center: 'title',
                            right: 'dayGridMonth,timeGridWeek,listMonth'
                        });
                        this.calendar.render();
                        if (isMobileInit) {
                            this.mobileWeeklyMode = 'listWeek';
                        }
                    },
                    setMobileWeekly(mode) {
                        if (!this.calendar) return;
                        this.mobileWeeklyMode = mode;
                        this.calendar.changeView(mode);
                    },
                    openCreate() {
                        this.form = {
                            id: null,
                            title: '',
                            categoria: 'evento',
                            audiencia: 'gestores',
                            start: '',
                            end: '',
                            allDay: false,
                            descricao: ''
                        };
                        this.errors = {}; // Limpar erros
                        this.$dispatch('open-modal', 'event-modal');
                        this.$nextTick(() => {
                            this.$refs.firstField && this.$refs.firstField.focus();
                        });
                    },
                    openCreateOnDate(dateStr) {
                        // Preenche início com a data clicada e marca dia inteiro
                        const start = `${dateStr}T00:00`;
                        this.form = {
                            id: null,
                            title: '',
                            categoria: 'evento',
                            audiencia: 'gestores',
                            start,
                            end: '',
                            allDay: true,
                            descricao: ''
                        };
                        this.currentEventId = null;
                        this.errors = {}; // Limpar erros
                        this.$dispatch('open-modal', 'event-modal');
                        this.$nextTick(() => {
                            this.$refs.firstField && this.$refs.firstField.focus();
                        });
                    },
                    openEdit(fcEvent) {
                        const xp = fcEvent.extendedProps || {};
                        // Formatação correta das datas para os inputs datetime-local
                        let startStr = '';
                        let endStr = '';

                        if (fcEvent.start) {
                            // Para eventos de dia inteiro, usar apenas a data
                            if (fcEvent.allDay) {
                                startStr = fcEvent.start.toISOString().slice(0, 10) + 'T00:00';
                            } else {
                                // Para eventos com horário, manter o horário original
                                const localStart = new Date(fcEvent.start);
                                startStr = localStart.getFullYear() + '-' +
                                    String(localStart.getMonth() + 1).padStart(2, '0') + '-' +
                                    String(localStart.getDate()).padStart(2, '0') + 'T' +
                                    String(localStart.getHours()).padStart(2, '0') + ':' +
                                    String(localStart.getMinutes()).padStart(2, '0');
                            }
                        }

                        if (fcEvent.end) {
                            if (fcEvent.allDay) {
                                // Para eventos de dia inteiro, a data de fim deve ser o dia anterior ao que o FullCalendar retorna
                                const endDate = new Date(fcEvent.end);
                                endDate.setDate(endDate.getDate() - 1);
                                endStr = endDate.toISOString().slice(0, 10) + 'T23:59';
                            } else {
                                const localEnd = new Date(fcEvent.end);
                                endStr = localEnd.getFullYear() + '-' +
                                    String(localEnd.getMonth() + 1).padStart(2, '0') + '-' +
                                    String(localEnd.getDate()).padStart(2, '0') + 'T' +
                                    String(localEnd.getHours()).padStart(2, '0') + ':' +
                                    String(localEnd.getMinutes()).padStart(2, '0');
                            }
                        }

                        this.form = {
                            id: fcEvent.id,
                            title: fcEvent.title || '',
                            categoria: xp.categoria || 'evento',
                            audiencia: xp.audiencia || 'gestores',
                            start: startStr,
                            end: endStr,
                            allDay: fcEvent.allDay || false,
                            descricao: xp.descricao || ''
                        };
                        this.currentEventId = fcEvent.id;
                        this.errors = {}; // Limpar erros
                        this.$dispatch('open-modal', 'event-modal');
                        this.$nextTick(() => {
                            this.$refs.firstField && this.$refs.firstField.focus();
                        });
                    },
                    validateForm() {
                        this.errors = {};

                        if (!this.form.title || this.form.title.trim() === '') {
                            this.errors.title = 'O título é obrigatório';
                        }

                        if (!this.form.categoria) {
                            this.errors.categoria = 'A categoria é obrigatória';
                        }

                        if (!this.form.audiencia) {
                            this.errors.audiencia = 'A audiência é obrigatória';
                        }

                        if (!this.form.start) {
                            this.errors.start = 'A data/hora de início é obrigatória';
                        }

                        if (this.form.start && this.form.end && new Date(this.form.start) >= new Date(this.form.end)) {
                            this.errors.end = 'A data/hora de fim deve ser posterior ao início';
                        }

                        return Object.keys(this.errors).length === 0;
                    },
                    formatLocal(date) {
                        if (!date) return null;
                        const pad = (n) => String(n).padStart(2, '0');
                        const y = date.getFullYear();
                        const m = pad(date.getMonth() + 1);
                        const d = pad(date.getDate());
                        const hh = pad(date.getHours());
                        const mm = pad(date.getMinutes());
                        const ss = pad(date.getSeconds());
                        return `${y}-${m}-${d} ${hh}:${mm}:${ss}`;
                    },
                    updateEventTime(fcEvent, revert) {
                        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        const payload = {
                            title: fcEvent.title || '',
                            start: fcEvent.start ? this.formatLocal(fcEvent.start) : null,
                            end: fcEvent.end ? this.formatLocal(fcEvent.end) : null,
                            all_day: !!fcEvent.allDay,
                            categoria: (fcEvent.extendedProps && fcEvent.extendedProps.categoria) || 'evento',
                            audiencia: (fcEvent.extendedProps && fcEvent.extendedProps.audiencia) || 'gestores',
                            descricao: (fcEvent.extendedProps && fcEvent.extendedProps.descricao) || null
                        };

                        fetch(`/calendario/events/${fcEvent.id}`, {
                                method: 'PUT',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': token,
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                credentials: 'same-origin',
                                body: JSON.stringify(payload)
                            })
                            .then(r => r.json())
                            .then(data => {
                                if (data && data.success) {
                                    showToast('Evento atualizado (arrastar/redimensionar)!', 'success');
                                    this.calendar && this.calendar.refetchEvents();
                                } else {
                                    showToast((data && data.message) ? data.message : 'Falha ao atualizar evento', 'error');
                                    if (typeof revert === 'function') revert();
                                }
                            })
                            .catch(err => {
                                console.error(err);
                                showToast('Erro ao atualizar evento', 'error');
                                if (typeof revert === 'function') revert();
                            });
                    },
                    save() {
                        if (!this.validateForm()) {
                            showToast('Por favor, corrija os erros no formulário', 'error');
                            return;
                        }

                        this.saving = true;

                        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        const payload = {
                            title: this.form.title.trim(),
                            start: this.form.start,
                            end: this.form.end || null,
                            all_day: !!this.form.allDay,
                            categoria: this.form.categoria,
                            audiencia: this.form.audiencia,
                            descricao: this.form.descricao || null
                        };
                        const isEdit = !!this.currentEventId;
                        const url = isEdit ? `/calendario/events/${this.currentEventId}` : `/calendario/events`;
                        const method = isEdit ? 'PUT' : 'POST';
                        fetch(url, {
                                method,
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': token,
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                credentials: 'same-origin',
                                body: JSON.stringify(payload)
                            })
                            .then(r => r.json())
                            .then(data => {
                                if (data && data.success) {
                                    showToast(isEdit ? 'Evento atualizado!' : 'Evento criado!', 'success');
                                    this.$dispatch('close-modal', 'event-modal');
                                    this.currentEventId = null;
                                    this.calendar && this.calendar.refetchEvents();
                                } else {
                                    // Tratar erros de validação do servidor
                                    if (data && data.errors) {
                                        this.errors = data.errors;
                                        showToast('Por favor, corrija os erros no formulário', 'error');
                                    } else {
                                        showToast((data && data.message) ? data.message : 'Erro ao salvar evento', 'error');
                                    }
                                }
                            })
                            .catch(err => {
                                console.error(err);
                                showToast('Erro ao salvar evento', 'error');
                            })
                            .finally(() => { this.saving = false; });
                    },
                    confirmDelete() {
                        if (!this.currentEventId) return;

                        // Usar o modal de confirmação padrão do sistema
                        window.showConfirmation({
                            title: 'Confirmar Exclusão',
                            message: 'Tem certeza que deseja excluir este evento? Esta ação não pode ser desfeita.',
                            confirmText: 'Excluir',
                            cancelText: 'Cancelar',
                            confirmColor: 'red',
                            callback: () => this.deleteEvent()
                        });
                    },
                    deleteEvent() {
                        if (!this.currentEventId) return;
                        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        this.deleting = true;
                        fetch(`/calendario/events/${this.currentEventId}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': token,
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                credentials: 'same-origin'
                            })
                            .then(r => r.json())
                            .then(data => {
                                if (data && data.success) {
                                    showToast('Evento excluído!', 'success');
                                    this.$dispatch('close-modal', 'event-modal');
                                    this.currentEventId = null;
                                    this.calendar && this.calendar.refetchEvents();
                                } else {
                                    showToast((data && data.message) ? data.message : 'Erro ao excluir evento', 'error');
                                }
                            })
                            .catch(err => {
                                console.error(err);
                                showToast('Erro ao excluir evento', 'error');
                            })
                            .finally(() => { this.deleting = false; });
                    }
                }
            }

            function colorByCategory(cat) {
                var map = {
                    aula: '#10b981',
                    feriado: '#ef4444',
                    recesso: '#f59e0b',
                    avaliacao: '#8b5cf6',
                    evento: '#3b82f6',
                    matricula: '#ec4899',
                };
                return map[cat] || '#3b82f6';
            }
        </script>
        <script>
            // Util padrão de toast do sistema (notifications)
            function showToast(message, type = 'info') {
                const toast = document.createElement('div');
                toast.className =
                    `fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg text-white transition-all duration-300 transform translate-x-full`;
                switch (type) {
                    case 'success':
                        toast.classList.add('bg-green-600');
                        break;
                    case 'error':
                        toast.classList.add('bg-red-600');
                        break;
                    case 'warning':
                        toast.classList.add('bg-yellow-600');
                        break;
                    default:
                        toast.classList.add('bg-blue-600');
                }
                toast.textContent = message;
                document.body.appendChild(toast);
                setTimeout(() => {
                    toast.classList.remove('translate-x-full');
                }, 100);
                setTimeout(() => {
                    toast.classList.add('translate-x-full');
                    setTimeout(() => {
                        document.body.removeChild(toast);
                    }, 300);
                }, 3000);
            }
        </script>
    @endpush
@endsection
