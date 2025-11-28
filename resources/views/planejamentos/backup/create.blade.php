@extends('layouts.app')

@section('title', 'Criar Planejamento de Aula')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900">Criar Planejamento de Aula</h1>
            <p class="mt-2 text-sm text-gray-600">Siga as etapas abaixo para criar um novo planejamento de aula</p>
        </div>

        <!-- Stepper Visual -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <!-- Etapa 1: Modalidade -->
                <div class="flex items-center" :class="{ 'opacity-50': currentStep < 1 }">
                    <div class="flex items-center justify-center w-10 h-10 rounded-full font-semibold text-sm step-circle"
                        :class="{ 'bg-indigo-600 text-white': currentStep >= 1, 'bg-gray-300 text-gray-600': currentStep < 1 }">
                        1
                    </div>
                    <div class="ml-3 hidden md:block">
                        <p class="text-sm font-medium step-title"
                            :class="{ 'text-gray-900': currentStep >= 1, 'text-gray-500': currentStep < 1 }">Modalidade</p>
                        <p class="text-xs step-description"
                            :class="{ 'text-gray-500': currentStep >= 1, 'text-gray-400': currentStep < 1 }">Educação Básica
                        </p>
                    </div>
                </div>

                <!-- Linha conectora -->
                <div class="flex-1 mx-4">
                    <div class="h-0.5 step-connector"
                        :class="{ 'bg-indigo-600': currentStep > 1, 'bg-gray-300': currentStep <= 1 }"></div>
                </div>

                <!-- Etapa 2: Unidade Escolar -->
                <div class="flex items-center" :class="{ 'opacity-50': currentStep < 2 }">
                    <div class="flex items-center justify-center w-10 h-10 rounded-full font-semibold text-sm step-circle"
                        :class="{ 'bg-indigo-600 text-white': currentStep >= 2, 'bg-gray-300 text-gray-600': currentStep < 2 }">
                        2
                    </div>
                    <div class="ml-3 hidden md:block">
                        <p class="text-sm font-medium step-title"
                            :class="{ 'text-gray-900': currentStep >= 2, 'text-gray-500': currentStep < 2 }">Unidade</p>
                        <p class="text-xs step-description"
                            :class="{ 'text-gray-500': currentStep >= 2, 'text-gray-400': currentStep < 2 }">Escola</p>
                    </div>
                </div>

                <!-- Linha conectora -->
                <div class="flex-1 mx-4">
                    <div class="h-0.5 step-connector"
                        :class="{ 'bg-indigo-600': currentStep > 2, 'bg-gray-300': currentStep <= 2 }"></div>
                </div>

                <!-- Etapa 3: Turno -->
                <div class="flex items-center" :class="{ 'opacity-50': currentStep < 3 }">
                    <div class="flex items-center justify-center w-10 h-10 rounded-full font-semibold text-sm step-circle"
                        :class="{ 'bg-indigo-600 text-white': currentStep >= 3, 'bg-gray-300 text-gray-600': currentStep < 3 }">
                        3
                    </div>
                    <div class="ml-3 hidden md:block">
                        <p class="text-sm font-medium step-title"
                            :class="{ 'text-gray-900': currentStep >= 3, 'text-gray-500': currentStep < 3 }">Turno</p>
                        <p class="text-xs step-description"
                            :class="{ 'text-gray-500': currentStep >= 3, 'text-gray-400': currentStep < 3 }">Período</p>
                    </div>
                </div>

                <!-- Linha conectora -->
                <div class="flex-1 mx-4">
                    <div class="h-0.5 step-connector"
                        :class="{ 'bg-indigo-600': currentStep > 3, 'bg-gray-300': currentStep <= 3 }"></div>
                </div>

                <!-- Etapa 4: Grupo -->
                <div class="flex items-center" :class="{ 'opacity-50': currentStep < 4 }">
                    <div class="flex items-center justify-center w-10 h-10 rounded-full font-semibold text-sm step-circle"
                        :class="{ 'bg-indigo-600 text-white': currentStep >= 4, 'bg-gray-300 text-gray-600': currentStep < 4 }">
                        4
                    </div>
                    <div class="ml-3 hidden md:block">
                        <p class="text-sm font-medium step-title"
                            :class="{ 'text-gray-900': currentStep >= 4, 'text-gray-500': currentStep < 4 }">Grupo</p>
                        <p class="text-xs step-description"
                            :class="{ 'text-gray-500': currentStep >= 4, 'text-gray-400': currentStep < 4 }">Educacional</p>
                    </div>
                </div>

                <!-- Linha conectora -->
                <div class="flex-1 mx-4">
                    <div class="h-0.5 step-connector"
                        :class="{ 'bg-indigo-600': currentStep > 4, 'bg-gray-300': currentStep <= 4 }"></div>
                </div>

                <!-- Etapa 5: Disciplina -->
                <div class="flex items-center" :class="{ 'opacity-50': currentStep < 5 }">
                    <div class="flex items-center justify-center w-10 h-10 rounded-full font-semibold text-sm step-circle"
                        :class="{ 'bg-indigo-600 text-white': currentStep >= 5, 'bg-gray-300 text-gray-600': currentStep < 5 }">
                        5
                    </div>
                    <div class="ml-3 hidden md:block">
                        <p class="text-sm font-medium step-title"
                            :class="{ 'text-gray-900': currentStep >= 5, 'text-gray-500': currentStep < 5 }">Disciplina</p>
                        <p class="text-xs step-description"
                            :class="{ 'text-gray-500': currentStep >= 5, 'text-gray-400': currentStep < 5 }">Matéria</p>
                    </div>
                </div>

                <!-- Linha conectora -->
                <div class="flex-1 mx-4">
                    <div class="h-0.5 step-connector"
                        :class="{ 'bg-indigo-600': currentStep > 5, 'bg-gray-300': currentStep <= 5 }"></div>
                </div>

                <!-- Etapa 6: Turma -->
                <div class="flex items-center" :class="{ 'opacity-50': currentStep < 6 }">
                    <div class="flex items-center justify-center w-10 h-10 rounded-full font-semibold text-sm step-circle"
                        :class="{ 'bg-indigo-600 text-white': currentStep >= 6, 'bg-gray-300 text-gray-600': currentStep < 6 }">
                        6
                    </div>
                    <div class="ml-3 hidden md:block">
                        <p class="text-sm font-medium step-title"
                            :class="{ 'text-gray-900': currentStep >= 6, 'text-gray-500': currentStep < 6 }">Turma</p>
                        <p class="text-xs step-description"
                            :class="{ 'text-gray-500': currentStep >= 6, 'text-gray-400': currentStep < 6 }">Série</p>
                    </div>
                </div>

                <!-- Linha conectora -->
                <div class="flex-1 mx-4">
                    <div class="h-0.5 step-connector"
                        :class="{ 'bg-indigo-600': currentStep > 6, 'bg-gray-300': currentStep <= 6 }"></div>
                </div>

                <!-- Etapa 7: Período -->
                <div class="flex items-center" :class="{ 'opacity-50': currentStep < 7 }">
                    <div class="flex items-center justify-center w-10 h-10 rounded-full font-semibold text-sm step-circle"
                        :class="{ 'bg-indigo-600 text-white': currentStep >= 7, 'bg-gray-300 text-gray-600': currentStep < 7 }">
                        7
                    </div>
                    <div class="ml-3 hidden md:block">
                        <p class="text-sm font-medium step-title"
                            :class="{ 'text-gray-900': currentStep >= 7, 'text-gray-500': currentStep < 7 }">Período</p>
                        <p class="text-xs step-description"
                            :class="{ 'text-gray-500': currentStep >= 7, 'text-gray-400': currentStep < 7 }">Datas</p>
                    </div>
                </div>
            </div>

            <!-- Stepper Mobile -->
            <div class="sm:hidden mt-4">
                <div class="flex items-center justify-center">
                    <div class="flex items-center space-x-2">
                        <span class="text-sm font-medium text-gray-900" x-text="`Etapa ${currentStep} de 7`"></span>
                        <span class="text-sm text-gray-500" x-text="stepTitles[currentStep - 1]"></span>
                    </div>
                </div>
                <div class="mt-2">
                    <div class="bg-gray-200 rounded-full h-2">
                        <div class="bg-indigo-600 h-2 rounded-full transition-all duration-300"
                            :style="`width: ${currentStep / 7 * 100}%`"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-card>
        <form action="{{ route('planejamentos.store') }}" method="POST" id="planejamentoForm" x-data="planejamentoForm()"
            @submit.prevent="submitForm">
            @csrf

            <!-- Layout responsivo com melhor espaçamento mobile -->
            <div class="space-y-6">
                <!-- Etapa 1: Modalidade da Educação Básica -->
                <div class="space-y-4" id="etapa-modalidade" x-show="currentStep === 1">
                    <div class="border-b border-gray-200 pb-4">
                        <h3 class="text-base font-medium text-gray-900 flex items-center">
                            <span
                                class="bg-blue-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm mr-3">1</span>
                            <i class="fas fa-graduation-cap text-blue-600 mr-2"></i>
                            Etapa da Educação Básica / Modalidade
                        </h3>
                        <p class="text-sm text-gray-600 mt-1">Selecione a modalidade de ensino</p>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div>
                            <label for="modalidade" class="block text-sm font-medium text-gray-700 mb-2">
                                Modalidade <span class="text-red-500">*</span>
                            </label>
                            <select name="modalidade_id" id="modalidade"
                                class="block w-full px-4 py-3 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                required x-model="modalidadeId" @change="onModalidadeChange(modalidadeId)">
                                <option value="">Selecione uma modalidade...</option>
                                @foreach ($modalidades as $modalidade)
                                    <option value="{{ $modalidade->id }}"
                                        {{ old('modalidade_id') == $modalidade->id ? 'selected' : '' }}>{{ $modalidade->nome }}
                                    </option>
                                @endforeach
                            </select>
                            @error('modalidade')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Etapa 2: Unidade Escolar -->
                <div class="space-y-4" id="etapa-unidade" x-show="currentStep === 2">
                    <div class="border-b border-gray-200 pb-4">
                        <h3 class="text-base font-medium text-gray-900 flex items-center">
                            <span
                                class="bg-blue-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm mr-3">2</span>
                            <i class="fas fa-school text-green-600 mr-2"></i>
                            Escola
                        </h3>
                        <p class="text-sm text-gray-600 mt-1">Escola vinculada ao seu perfil</p>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Escola</label>

                            <!-- Select para administradores -->
                            <div x-show="isAdmin">
                                <select name="escola_id" id="escola_id"
                                    class="block w-full px-4 py-3 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    x-model="escolaId">
                                    <option value="">Selecione uma escola...</option>
                                    <template x-for="escola in escolas" :key="escola.id">
                                        <option :value="escola.id" x-text="escola.nome"></option>
                                    </template>
                                </select>
                            </div>

                            <!-- Campo fixo para outros usuários -->
                            <div x-show="!isAdmin"
                                class="block w-full rounded-md border-gray-300 bg-gray-50 px-3 py-2 text-gray-700 text-sm min-h-[38px] flex items-center"
                                x-text="escolaNome">
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Etapa 3: Turno -->
                <div class="space-y-4" id="etapa-turno" x-show="currentStep === 3">
                    <div class="border-b border-gray-200 pb-4">
                        <h3 class="text-base font-medium text-gray-900 flex items-center">
                            <span
                                class="bg-blue-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm mr-3">3</span>
                            <i class="fas fa-clock text-purple-600 mr-2"></i>
                            Turno
                        </h3>
                        <p class="text-sm text-gray-600 mt-1">Selecione o turno para o planejamento</p>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div>
                            <label for="turno" class="block text-sm font-medium text-gray-700 mb-2">
                                Turno <span class="text-red-500">*</span>
                            </label>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" id="turnos-container">
                                <template x-for="turno in turnosDisponiveis" :key="turno.id">
                                    <label
                                        class="relative block cursor-pointer rounded-lg border border-gray-300 bg-white px-6 py-4 shadow-sm focus-within:ring-2 focus-within:ring-indigo-500 focus-within:ring-offset-2 hover:border-gray-400">
                                        <input type="radio" name="turno_id" :value="turno.id" class="sr-only"
                                            x-model="turnoId">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center">
                                                <i :class="turno.icone" class="text-2xl text-indigo-600 mr-4"></i>
                                                <div class="text-sm">
                                                    <p class="font-medium text-gray-900" x-text="turno.label"></p>
                                                    <p class="text-gray-500"
                                                        x-text="`${turno.salas_ativas} salas ativas`"></p>
                                                </div>
                                            </div>
                                            <div x-show="turnoId == turno.id" class="flex-shrink-0 text-indigo-600">
                                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"
                                                    aria-hidden="true">
                                                    <path fill-rule="evenodd"
                                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.857a.75.75 0 00-1.214-.886l-3.442 4.616-1.728-1.729a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l4-5.333z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                        </div>
                                        <span
                                            class="pointer-events-none absolute -inset-px rounded-lg border-2 border-transparent"
                                            aria-hidden="true"></span>
                                    </label>
                                </template>
                                <p x-show="turnosDisponiveis.length === 0" class="text-gray-500">Nenhum turno disponível
                                    para esta modalidade.</p>
                            </div>
                            @error('turno')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Etapa 4: Grupo Educacional -->
                <div class="space-y-4" id="etapa-grupo" x-show="currentStep === 4">
                    <div class="border-b border-gray-200 pb-4">
                        <h3 class="text-base font-medium text-gray-900 flex items-center">
                            <span
                                class="bg-blue-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm mr-3">4</span>
                            <i class="fas fa-users text-orange-600 mr-2"></i>
                            Grupo Educacional
                        </h3>
                        <p class="text-sm text-gray-600 mt-1">Selecione o grupo educacional</p>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div>
                            <label for="grupo" class="block text-sm font-medium text-gray-700 mb-2">
                                Grupo <span class="text-red-500">*</span>
                            </label>
                            <select name="grupo_id" id="grupo" x-model="grupoEducacionalId"
                                @change="onGrupoEducacionalChange(grupoEducacionalId)"
                                class="block w-full px-4 py-3 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                required>
                                <option value="">Selecione um grupo...</option>
                                <template x-for="grupo in gruposEducacionais" :key="grupo.id">
                                    <option :value="grupo.id" x-text="grupo.nome"></option>
                                </template>
                            </select>
                            @error('grupo')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Etapa 5: Disciplina -->
                <div class="space-y-4" id="etapa-disciplina" x-show="currentStep === 5">
                    <div class="border-b border-gray-200 pb-4">
                        <h3 class="text-base font-medium text-gray-900 flex items-center">
                            <span
                                class="bg-blue-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm mr-3">5</span>
                            <i class="fas fa-book-open text-yellow-600 mr-2"></i>
                            Disciplina
                        </h3>
                        <p class="text-sm text-gray-600 mt-1">Selecione a disciplina</p>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div>
                            <label for="disciplina" class="block text-sm font-medium text-gray-700 mb-2">
                                Disciplina <span class="text-red-500">*</span>
                            </label>
                            <select name="disciplina" id="disciplina" x-model="disciplinaId"
                                @change="onDisciplinaChange(disciplinaId)"
                                class="block w-full px-4 py-3 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                required >
                                <option value="">Selecione uma disciplina...</option>
                                <template x-for="disciplina in disciplinasDisponiveis" :key="disciplina.id">
                                    <option :value="disciplina.id" x-text="disciplina.nome"></option>
                                </template>
                            </select>
                            @error('disciplina')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Etapa 6: Turma -->
                <div class="space-y-4" id="etapa-turma" x-show="currentStep === 6">
                    <div class="border-b border-gray-200 pb-4">
                        <h3 class="text-base font-medium text-gray-900 flex items-center">
                            <span
                                class="bg-blue-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm mr-3">6</span>
                            <i class="fas fa-chalkboard text-red-600 mr-2"></i>
                            Turma
                        </h3>
                        <p class="text-sm text-gray-600 mt-1">Selecione a turma</p>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div>
                            <label for="turma" class="block text-sm font-medium text-gray-700 mb-2">
                                Turma <span class="text-red-500">*</span>
                            </label>
                            <select name="turma" id="turma" x-model="turmaId" @change="onTurmaChange(turmaId)"
                                class="block w-full px-4 py-3 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                required>
                                <option value="">Selecione uma turma...</option>
                                <template x-for="turma in turmasDisponiveis" :key="turma.id">
                                    <option :value="turma.id" x-text="turma.nome"></option>
                                </template>
                            </select>
                            @error('turma')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Etapa 7: Período -->
                <div class="space-y-4" id="etapa-periodo" x-show="currentStep === 7">
                    <div class="border-b border-gray-200 pb-4">
                        <h3 class="text-base font-medium text-gray-900 flex items-center">
                            <span
                                class="bg-blue-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm mr-3">7</span>
                            <i class="fas fa-calendar-alt text-teal-600 mr-2"></i>
                            Período do Planejamento
                        </h3>
                        <p class="text-sm text-gray-600 mt-1">Selecione a quantidade de dias para o planejamento (1 a 15
                            dias)</p>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div>
                            <label for="numero_dias" class="block text-sm font-medium text-gray-700 mb-2">
                                Número de Dias <span class="text-red-500">*</span>
                            </label>
                            <select name="numero_dias" id="numero_dias" x-model="numeroDias"
                                class="block w-full px-4 py-3 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                required>
                                <option value="">Selecione a quantidade de dias...</option>
                                <template x-for="i in 15" :key="i">
                                    <option :value="i" x-text="`${i} ${i === 1 ? 'dia' : 'dias'}`"></option>
                                </template>
                            </select>
                            @error('numero_dias')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div x-show="numeroDias">
                            <label for="data_inicio_preview" class="block text-sm font-medium text-gray-700 mb-2">
                                Período Calculado
                            </label>
                            <div
                                class="block w-full px-4 py-3 rounded-md border border-gray-200 bg-gray-50 text-sm text-gray-700">
                                <template x-if="numeroDias && dataInicioCalculada">
                                    <span
                                        x-text="`${formatDate(dataInicioCalculada)} até ${formatDate(dataFimCalculada)}`"></span>
                                </template>
                                <template x-if="!numeroDias || !dataInicioCalculada">
                                    <span class="text-gray-400">Selecione o número de dias</span>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Campos ocultos para envio -->
                    <input type="hidden" name="data_inicio" :value="dataInicioCalculada">
                    <input type="hidden" name="data_fim" :value="dataFimCalculada">
                </div>





                <!-- Botões de Navegação -->
                <div
                    class="flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0 pt-6 border-t border-gray-200">
                    <button type="button" x-show="currentStep > 1" @click="prevStep()"
                        class="w-full sm:w-auto px-6 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Anterior
                    </button>

                    <div class="flex space-x-3 w-full sm:w-auto">
                        <button type="button" x-show="currentStep < 7" @click="nextStep()"
                            class="w-full sm:w-auto px-6 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Próximo
                            <i class="fas fa-arrow-right ml-2"></i>
                        </button>

                        <button type="submit" x-show="currentStep === 7"
                            class="w-full sm:w-auto px-6 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            <i class="fas fa-folder-open mr-2"></i>
                            Abrir Planejamento
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </x-card>

    <!-- Resumo do Planejamento (Mobile) -->
    <div class="lg:hidden mt-6" id="resumo-mobile">
        <x-card>
            <h3 class="text-base font-medium text-gray-900 mb-4 flex items-center">
                <i class="fas fa-list-alt text-blue-600 mr-2"></i>
                Resumo do Planejamento
            </h3>
            <div id="resumo-content" class="space-y-2 text-sm text-gray-600">
                <p>Complete as etapas acima para ver o resumo</p>
            </div>
        </x-card>
    </div>

@endsection



@push('scripts')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        function planejamentoForm() {
            return {
                currentStep: 1,
                escolaId: '{{ $escolaId ?? '' }}',
                escolaNome: '{{ $escolaNome ?? 'Escola' }}',
                escolas: @json($escolas ?? []),
                isAdmin: {{ Auth::user()->isSuperAdmin() ? 'true' : 'false' }},
                modalidadeId: '',
                modalidades: @json($modalidades),

                turnoId: '',
                grupoEducacionalId: '', // Adicionado para a Etapa 4
                turmaId: '', // Adicionado para a Etapa 5
                disciplinaId: '', // Adicionado para a Etapa 6

                professorId: '{{ Auth::id() }}', // ID do usuário logado como professor
                dataInicio: '', // Adicionado para a Etapa 8
                numeroDias: '', // Adicionado para a Etapa 8
                dataInicioCalculada: '',
                dataFimCalculada: '',
                ultimoPlanejamentoData: null,
                // nivelEnsinoId removido - agora usa grupoEducacionalId
                turnosDisponiveis: [],
                niveisEnsino: [],
                gruposEducacionais: [],
                turmasDisponiveis: [],
                disciplinasDisponiveis: [],
                professores: [],
                dataFim: '',
                errors: {},
                stepTitles: [
                        'Modalidade',
                        'Unidade Escolar',
                        'Turno',
                        'Grupo Educacional',
                        'Disciplina',
                        'Turma',
                        'Período'
                    ],

                init() {
                    console.log('escolaId no init:', this.escolaId);
                    this.debugLog('Inicialização do formulário', {
                        escolaId: this.escolaId
                    });

                    this.$watch('currentStep', (step) => this.updateStepper(step));
                    this.$watch('modalidadeId', (id) => this.onModalidadeChange(id));
                    this.$watch('escolaId', (id) => this.onModalidadeChange(id));
                    this.$watch('turnoId', (id) => this.onTurnoChange(id));
                    this.$watch('grupoEducacionalId', (id) => this.onGrupoEducacionalChange(id));
                    this.$watch('turmaId', (id) => {
                        this.onTurmaChange(id);
                        if (id && this.disciplinaId) {
                            this.loadUltimoPlanejamento();
                        }
                    });
                    this.$watch('disciplinaId', (id) => {
                        this.onDisciplinaChange(id);
                        if (id && this.turmaId) {
                            this.loadUltimoPlanejamento();
                        }
                    });

                    this.$watch('numeroDias', () => this.calculatePeriodo());

                    // Carrega a data do último planejamento
                    this.loadUltimoPlanejamento();
                },

                nextStep() {
                    if (this.validateStep(this.currentStep) && this.currentStep < 7) {
                        this.debugLog('Avançar etapa', {
                            fromStep: this.currentStep,
                            toStep: this.currentStep + 1
                        });
                        this.currentStep++;
                        this.scrollToTop();
                    }
                },

                prevStep() {
                    if (this.currentStep > 1) {
                        this.debugLog('Voltar etapa', {
                            fromStep: this.currentStep,
                            toStep: this.currentStep - 1
                        });
                        this.currentStep--;
                        this.scrollToTop();
                    }
                },

                scrollToTop() {
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                },

                updateStepper(step) {
                    // As cores são atualizadas automaticamente pelo Alpine.js através das diretivas :class
                    // Não é necessário manipular o DOM manualmente
                    this.debugLog('Stepper atualizado', {
                        currentStep: step
                    });
                },

                validateStep(step) {
                    this.errors = {}; // Limpa erros anteriores
                    let isValid = true;

                    switch (step) {
                        case 1:
                            if (!this.modalidadeId) {
                                this.errors.modalidade = 'Selecione uma modalidade.';
                                isValid = false;
                            }
                            break;
                        case 2:
                            console.log('Validando Etapa 2 - unidadeEscolarId:', this.escolaId);
                            if (!this.escolaId) {
                                this.errors.escolaId = 'Selecione uma unidade escolar.';
                                isValid = false;
                            }
                            break;
                        case 3:
                            if (!this.turnoId) {
                                this.errors.turno = 'Selecione um turno.';
                                isValid = false;
                            }
                            break;
                        case 4:
                            if (!this.grupoEducacionalId) {
                                this.errors.grupoEducacional = 'Selecione um grupo educacional.';
                                isValid = false;
                            }
                            break;
                        case 5:
                            if (!this.disciplinaId) {
                                this.errors.disciplina = 'Selecione uma disciplina.';
                                isValid = false;
                            }
                            break;
                        case 6:
                            if (!this.turmaId) {
                                this.errors.turma = 'Selecione uma turma.';
                                isValid = false;
                            }
                            break;
                        case 7:
                            if (!this.dataInicioCalculada || !this.numeroDias) {
                                this.errors.periodo = 'Preencha a data de início e o número de dias.';
                                isValid = false;
                            }
                            break;
                    }
                    return isValid;
                },

                async onModalidadeChange(modalidadeId) {
                    this.debugLog('Modalidade selecionada', {
                        modalidadeId,
                        timestamp: new Date().toISOString()
                    });

                    // Limpa todas as seleções subsequentes
                    this.turnoId = '';
                    this.grupoEducacionalId = '';
                    this.disciplinaId = '';
                    this.turmaId = '';
                    this.turnosDisponiveis = [];
                    this.gruposEducacionais = [];
                    this.disciplinasDisponiveis = [];
                    this.turmasDisponiveis = [];

                    if (modalidadeId) {
                        await this.loadTurnosDisponiveis(modalidadeId);
                    }
                    // Não avança automaticamente - deixa o usuário escolher quando avançar
                },

                async onTurnoChange(turnoId) {
                    this.debugLog('Turno selecionado', {
                        turnoId,
                        modalidadeId: this.modalidadeId,
                        escolaId: this.escolaId,
                        timestamp: new Date().toISOString()
                    });

                    // Limpa seleções subsequentes
                    this.grupoEducacionalId = '';
                    this.disciplinaId = '';
                    this.turmaId = '';
                    this.gruposEducacionais = [];
                    this.disciplinasDisponiveis = [];
                    this.turmasDisponiveis = [];

                    if (turnoId && this.modalidadeId) {
                        await this.loadGruposEducacionais(this.modalidadeId, turnoId);
                    }
                },

                async onGrupoEducacionalChange(grupoId) {
                    this.debugLog('Grupo Educacional selecionado', {
                        grupoId,
                        modalidadeId: this.modalidadeId,
                        turnoId: this.turnoId,
                        timestamp: new Date().toISOString()
                    });

                    // Limpa seleções subsequentes
                    this.disciplinaId = '';
                    this.turmaId = '';
                    this.disciplinasDisponiveis = [];
                    this.turmasDisponiveis = [];

                    if (grupoId && this.modalidadeId && this.turnoId) {
                        await this.loadDisciplinasPorModalidadeTurnoGrupo(this.modalidadeId, this.turnoId, grupoId);
                    }
                },

                async loadTurnosDisponiveis(modalidadeId) {
                    try {
                        const response = await fetch(`/planejamentos/turnos-disponiveis?modalidade_id=${modalidadeId}`);
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        const data = await response.json();
                        this.turnosDisponiveis = data;
                    } catch (error) {
                        console.error('Erro ao carregar turnos disponíveis:', error);
                        // Tratar erro, talvez exibir uma mensagem para o usuário
                    }
                },



                async loadGruposEducacionais(modalidadeId, turnoId) {
                    try {
                        const response = await fetch(
                            `/planejamentos/grupos-educacionais?modalidade_id=${modalidadeId}&turno_id=${turnoId}`);
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        const data = await response.json();
                        this.gruposEducacionais = data;

                    } catch (error) {
                        console.error('Erro ao carregar grupos educacionais:', error);
                    }
                },

                async loadDisciplinasPorModalidadeTurnoGrupo(modalidadeId, turnoId, grupoId) {
                    this.debugLog('Carregando disciplinas por modalidade, turno e grupo', {
                        modalidadeId,
                        turnoId,
                        grupoId,
                        timestamp: new Date().toISOString()
                    });
                    try {
                        const response = await fetch(
                            `/planejamentos/disciplinas-por-modalidade-turno-grupo?modalidade_id=${modalidadeId}&turno_id=${turnoId}&grupo_id=${grupoId}&escola_id=${this.escolaId}`
                            );
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        const data = await response.json();
                        console.log(data);
                        this.disciplinasDisponiveis = data;
                    } catch (error) {
                        console.error('Erro ao carregar disciplinas:', error);
                    }
                },

                async loadTurmasPorDisciplina(disciplinaId, modalidadeId, turnoId, grupoId) {
                    try {
                        const response = await fetch(
                            `/planejamentos/turmas-por-disciplina?disciplina_id=${disciplinaId}&modalidade_id=${modalidadeId}&turno_id=${turnoId}&grupo_id=${grupoId}`
                            );
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        const data = await response.json();
                        this.turmasDisponiveis = data;
                    } catch (error) {
                        console.error('Erro ao carregar turmas:', error);
                    }
                },

                async loadDisciplinasPorTurma(turmaId) {
                    try {
                        const response = await fetch(
                            `/planejamentos/get-disciplinas-por-turma?turma_id=${turmaId}`
                            );
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        const data = await response.json();
                        this.disciplinas = data;
                    } catch (error) {
                        console.error('Erro ao carregar disciplinas por turma:', error);
                    }
                },

                clearTurnos() {
                    this.turnosDisponiveis = [];
                },

                async loadTurmasPorGrupoTurno(grupoId, turnoId) {
                    try {
                        const response = await fetch(
                            `/planejamentos/turmas-por-grupo-turno?grupo_id=${grupoId}&turno_id=${turnoId}`);
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        const data = await response.json();
                        this.turmas = data.turmas;
                    } catch (error) {
                        console.error('Erro ao carregar turmas:', error);
                    }
                },

                async loadNiveisEnsino(modalidadeId, turnoId) {
                    try {
                        const response = await fetch(
                            `/planejamentos/niveis-ensino?modalidade=${modalidadeId}&turno=${turnoId}`);
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        const data = await response.json();
                        this.niveisEnsino = data;
                    } catch (error) {
                        console.error('Erro ao carregar níveis de ensino:', error);
                    }
                },

                async loadGruposPorModalidadeTurno(modalidadeId, turnoId) {
                    try {
                        const response = await fetch(
                            `/planejamentos/grupos-educacionais?modalidade=${modalidadeId}&turno=${turnoId}`);
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        const data = await response.json();
                        this.gruposEducacionais = data;
                    } catch (error) {
                        console.error('Erro ao carregar grupos educacionais:', error);
                    }
                },



                async loadNiveisEnsino(modalidadeId, turnoId) {
                    try {
                        const response = await fetch(
                            `/planejamentos/niveis-ensino?modalidade_id=${modalidadeId}&turno_id=${turnoId}`);
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        const data = await response.json();
                        this.niveisEnsino = data.niveis_ensino;
                    } catch (error) {
                        console.error('Erro ao carregar níveis de ensino:', error);
                    }
                },

                async loadGruposPorModalidadeTurno(modalidadeId, turnoId) {
                    try {
                        const response = await fetch(
                            `/planejamentos/grupos-educacionais?modalidade_id=${modalidadeId}&turno_id=${turnoId}`);
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        const data = await response.json();
                        this.gruposEducacionais = data.grupos_educacionais;
                    } catch (error) {
                        console.error('Erro ao carregar grupos educacionais:', error);
                    }
                },



                async onDisciplinaChange(disciplinaId) {
                    this.debugLog('Disciplina selecionada', {
                        disciplinaId,
                        modalidadeId: this.modalidadeId,
                        turnoId: this.turnoId,
                        grupoEducacionalId: this.grupoEducacionalId,
                        escolaId: this.escolaId,
                        timestamp: new Date().toISOString()
                    });

                    // Limpa seleções subsequentes
                    this.turmaId = '';
                    this.turmasDisponiveis = [];

                    if (disciplinaId && this.modalidadeId && this.turnoId && this.grupoEducacionalId) {
                        await this.loadTurmasPorDisciplina(disciplinaId, this.modalidadeId, this.turnoId, this
                            .grupoEducacionalId);
                    }
                },

                async onTurmaChange(turmaId) {
                    this.debugLog('Turma selecionada', {
                        turmaId,
                        disciplinaId: this.disciplinaId,
                        modalidadeId: this.modalidadeId,
                        turnoId: this.turnoId,
                        grupoEducacionalId: this.grupoEducacionalId,
                        escolaId: this.escolaId,
                        timestamp: new Date().toISOString()
                    });
                    
                    // Limpa seleções subsequentes se necessário
                    this.disciplinas = [];
                    this.professores = [];

                },

                debugLog(action, data) {
                    const logEntry = {
                        action,
                        data,
                        user: '{{ auth()->user()->name ?? 'Usuário não identificado' }}',
                        userId: '{{ auth()->user()->id ?? 'N/A' }}',
                        timestamp: new Date().toISOString(),
                        step: this.currentStep
                    };

                    console.group(`🔍 Debug Planejamento - ${action}`);
                    console.log('Ação:', action);
                    console.log('Dados:', data);
                    console.log('Usuário:', logEntry.user);
                    console.log('Etapa atual:', logEntry.step);
                    console.log('Timestamp:', logEntry.timestamp);
                    console.groupEnd();

                    // Armazena no localStorage para debug persistente
                    const debugLogs = JSON.parse(localStorage.getItem('planejamento_debug_logs') || '[]');
                    debugLogs.push(logEntry);
                    // Mantém apenas os últimos 50 logs
                    if (debugLogs.length > 50) {
                        debugLogs.splice(0, debugLogs.length - 50);
                    }
                    localStorage.setItem('planejamento_debug_logs', JSON.stringify(debugLogs));
                },

                // Função para visualizar todos os logs de debug
                viewDebugLogs() {
                    const logs = JSON.parse(localStorage.getItem('planejamento_debug_logs') || '[]');
                    console.group('📋 Histórico completo de Debug - Planejamento');
                    logs.forEach((log, index) => {
                        console.group(`${index + 1}. ${log.action} - ${log.timestamp}`);
                        console.log('Usuário:', log.user);
                        console.log('Etapa:', log.step);
                        console.log('Dados:', log.data);
                        console.groupEnd();
                    });
                    console.groupEnd();
                    return logs;
                },

                // Função para limpar logs de debug
                clearDebugLogs() {
                    localStorage.removeItem('planejamento_debug_logs');
                    console.log('🗑️ Logs de debug limpos');
                },



                async loadUltimoPlanejamento() {
                    try {
                        // Definir data padrão (hoje) como fallback
                        const dataAtual = new Date().toISOString().split('T')[0];
                        this.ultimoPlanejamentoData = dataAtual;
                        
                        // Só buscar se temos os parâmetros necessários
                        if (!this.turmaId || !this.disciplinaId || !this.tipoProfessor) {
                            this.debugLog('Parâmetros insuficientes para buscar último planejamento', {
                                turmaId: this.turmaId,
                                disciplinaId: this.disciplinaId,
                                tipoProfessor: this.tipoProfessor,
                                usando_data_atual: this.ultimoPlanejamentoData
                            });
                            return;
                        }

                        const params = new URLSearchParams({
                            turma_id: this.turmaId,
                            disciplina_id: this.disciplinaId,
                            modalidade_id: this.modalidadeId || '',
                            turno_id: this.turnoId || '',
                            grupo_educacional_id: this.grupoEducacionalId || '',
                            tipo_professor: this.tipoProfessor || ''
                        });

                        this.debugLog('Fazendo chamada para:', `/planejamentos/get-ultimo-periodo-planejamento?${params.toString()}`);
                        
                        try {
                            const response = await fetch(`/planejamentos/get-ultimo-periodo-planejamento?${params.toString()}`);
                            
                            if (!response.ok) {
                                console.warn(`Erro na resposta da API: ${response.status}`);
                                return; // Continua usando a data atual definida acima
                            }
                            
                            const data = await response.json();
                            
                            if (data.ultimo_periodo) {
                                this.ultimoPlanejamentoData = data.ultimo_periodo;
                                this.debugLog('Último planejamento encontrado', {
                                    ultimoPlanejamentoData: this.ultimoPlanejamentoData,
                                    parametros: {
                                        turmaId: this.turmaId,
                                        disciplinaId: this.disciplinaId,
                                        modalidadeId: this.modalidadeId,
                                        turnoId: this.turnoId,
                                        grupoEducacionalId: this.grupoEducacionalId,
                                        tipoProfessor: this.tipoProfessor
                                    }
                                });
                            } else {
                                this.debugLog('Nenhum planejamento anterior encontrado', {
                                    usando_data_atual: this.ultimoPlanejamentoData
                                });
                            }
                        } catch (apiError) {
                            console.error('Erro na chamada da API:', apiError);
                            // Continua usando a data atual definida acima
                        }
                    } catch (error) {
                        console.error('Erro geral ao buscar último planejamento:', error);
                        // Fallback para data atual
                        this.ultimoPlanejamentoData = new Date().toISOString().split('T')[0];
                        this.debugLog('Erro ao buscar último planejamento', {
                            error: error.message,
                            usando_data_atual: this.ultimoPlanejamentoData
                        });
                    }
                },

                calculatePeriodo() {
                    if (this.numeroDias && this.ultimoPlanejamentoData) {
                        // O backend já retorna a próxima data de início correta
                        this.dataInicioCalculada = this.ultimoPlanejamentoData;

                        // Calcula data de fim: data início + dias selecionados - 1
                        const dataFim = new Date(this.dataInicioCalculada);
                        dataFim.setDate(dataFim.getDate() + parseInt(this.numeroDias) - 1);
                        this.dataFimCalculada = dataFim.toISOString().split('T')[0];

                        this.debugLog('Período calculado', {
                            proximaDataInicio: this.ultimoPlanejamentoData,
                            dataInicioCalculada: this.dataInicioCalculada,
                            numeroDias: this.numeroDias,
                            dataFimCalculada: this.dataFimCalculada
                        });
                    } else {
                        this.dataInicioCalculada = '';
                        this.dataFimCalculada = '';
                    }
                },

                formatDate(dateString) {
                    if (!dateString) return '';
                    const date = new Date(dateString);
                    return date.toLocaleDateString('pt-BR');
                },

                async submitForm() {
                    if (!this.validateStep(7)) {
                        return;
                    }

                    this.debugLog('Iniciando submissão do formulário', {
                        modalidadeId: this.modalidadeId,
                        escolaId: this.escolaId,
                        turnoId: this.turnoId,
                        grupoEducacionalId: this.grupoEducacionalId,
                        disciplinaId: this.disciplinaId,
                        turmaId: this.turmaId,
                        dataInicio: this.dataInicioCalculada,
                        dataFim: this.dataFimCalculada
                    });

                    try {
                        const formData = new FormData();

                        // Obter os códigos dos itens selecionados a partir dos arrays de dados
                        const modalidadeSelecionada = this.modalidades && Array.isArray(this.modalidades) ?
                            this.modalidades.find(m => m.id == this.modalidadeId) :
                            null;
                        const turnoSelecionado = this.turnosDisponiveis && Array.isArray(this.turnosDisponiveis) ?
                            this.turnosDisponiveis.find(t => t.id == this.turnoId) :
                            null;

                        // Usar códigos em vez de nomes para validação
                        const modalidadeCodigo = modalidadeSelecionada?.codigo || '';
                        
                        // Obter turno selecionado dos radio buttons
                        const turnoRadioSelecionado = document.querySelector('input[name="turno_radio"]:checked');
                        const turnoCodigo = turnoRadioSelecionado ? turnoRadioSelecionado.value : '';
                        
                        // Definir tipo de professor baseado no usuário logado
                        const tipoProf = 'pedagogia'; // Valor padrão - pode ser determinado pelo perfil do usuário

                        this.debugLog('Dados antes de envio', {
                            modalidadeCodigo,
                            turnoCodigo,
                            tipoProf,
                            turmaId: this.turmaId,
                            dataInicioCalculada: this.dataInicioCalculada,
                            numeroDias: this.numeroDias
                        });

                        formData.append('modalidade_id', this.modalidadeId);
                        formData.append('unidade_escolar', this.escolaNome || '');
                        formData.append('turno_id', this.turnoId);
                        formData.append('grupo_id', this.grupoEducacionalId);
                        formData.append('disciplina_id', this.disciplinaId);
                        formData.append('tipo_professor', tipoProf); // Mantido para compatibilidade
                        formData.append('turma_id', this.turmaId);

                        formData.append('data_inicio', this.dataInicioCalculada);
                        formData.append('numero_dias', this.numeroDias);
                        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute(
                            'content'));

                        this.debugLog('Dados sendo enviados', {
                            modalidade: modalidadeCodigo,
                            unidade_escolar: this.escolaNome,
                            turno: turnoCodigo,
                            tipo_professor: tipoProf,
                            turma_id: this.turmaId,
                            data_inicio: this.dataInicioCalculada,
                            numero_dias: this.numeroDias,
                            escolaId: this.escolaId,
                            modalidadeSelecionada: modalidadeSelecionada,
                            turnoSelecionado: turnoSelecionado,
                            modalidades: this.modalidades,
                            turnosDisponiveis: this.turnosDisponiveis
                        });

                        const response = await fetch('{{ route('planejamentos.store') }}', {
                            method: 'POST',
                            body: formData
                        });

                        if (response.ok) {
                            const result = await response.json();
                            this.debugLog('Formulário enviado com sucesso', {
                                redirecting: true,
                                planejamentoId: result.id
                            });
                            // Redirecionar para a tela de planejamento detalhado
                            window.location.href = `/planejamentos/${result.id}/detalhado`;
                        } else {
                            const errorData = await response.json();
                            this.debugLog('Erro na submissão', {
                                error: errorData
                            });
                            console.error('Erro ao salvar:', errorData);

                            // Exibir erros específicos de validação usando o sistema de alertas
                            if (errorData.errors) {
                                const errors = [];
                                Object.keys(errorData.errors).forEach(field => {
                                    const fieldName = field === 'data_inicio' ? 'Data de início' : field;
                                    errors.push(`${fieldName}: ${errorData.errors[field].join(', ')}`);
                                });
                                window.alertSystem.error('Erro ao salvar planejamento', {
                                    errors: errors,
                                    timeout: 8000
                                });
                            } else {
                                window.alertSystem.error('Erro ao salvar o planejamento. Verifique os dados e tente novamente.');
                            }
                        }
                    } catch (error) {
                        this.debugLog('Erro de rede/exceção', {
                            error: error.message
                        });
                        console.error('Erro:', error);
                        window.alertSystem.error('Erro ao salvar o planejamento. Tente novamente.');
                    }
                }
            }
        }
    </script>
@endpush
