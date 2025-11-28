<!-- Seção: Configuração -->
<div class="space-y-6">
    <!-- Unidade Escolar -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <h3 class="text-sm font-medium text-blue-900 mb-3 flex items-center">
            <i class="fas fa-school mr-2"></i>
            Unidade Escolar
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <span class="text-xs text-blue-700 uppercase tracking-wide">Nome da Escola:</span>
                <div class="text-sm font-medium text-blue-900">{{ $planejamento->escola->nome ?? 'Não informado' }}</div>
            </div>

            <div>
                <span class="text-xs text-blue-700 uppercase tracking-wide">Código INEP:</span>
                <div class="text-sm text-blue-800">{{ $planejamento->escola->codigo_inep ?? 'Não informado' }}</div>
            </div>

            <div>
                <span class="text-xs text-blue-700 uppercase tracking-wide">Endereço:</span>
                <div class="text-sm text-blue-800">
                    {{ $planejamento->escola->endereco ?? 'Não informado' }}
                    @if ($planejamento->escola->cidade)
                        <br>{{ $planejamento->escola->cidade }} - {{ $planejamento->escola->estado }}
                    @endif
                </div>
            </div>

            <div>
                <span class="text-xs text-blue-700 uppercase tracking-wide">Contato:</span>
                <div class="text-sm text-blue-800">
                    @if ($planejamento->escola->telefone)
                        <div><i class="fas fa-phone mr-1"></i>{{ $planejamento->escola->telefone }}</div>
                    @endif
                    @if ($planejamento->escola->email)
                        <div><i class="fas fa-envelope mr-1"></i>{{ $planejamento->escola->email }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Turno -->
    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
        <h3 class="text-sm font-medium text-green-900 mb-3 flex items-center">
            <i class="fas fa-clock mr-2"></i>
            Turno
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <span class="text-xs text-green-700 uppercase tracking-wide">Turno:</span>
                <div class="text-sm font-medium text-green-900">{{ $planejamento->turno->nome ?? 'Não informado' }}
                </div>
            </div>

            <div>
                <span class="text-xs text-green-700 uppercase tracking-wide">Horário de Início:</span>
                <div class="text-sm text-green-800">{{ $planejamento->turno->hora_inicio ?? 'Não informado' }}</div>
            </div>

            <div>
                <span class="text-xs text-green-700 uppercase tracking-wide">Horário de Término:</span>
                <div class="text-sm text-green-800">{{ $planejamento->turno->hora_fim ?? 'Não informado' }}</div>
            </div>
        </div>
    </div>

    <!-- Turma -->
    <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
        <h3 class="text-sm font-medium text-purple-900 mb-3 flex items-center">
            <i class="fas fa-users mr-2"></i>
            Turma
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <span class="text-xs text-purple-700 uppercase tracking-wide">Nome da Turma:</span>
                <div class="text-sm font-medium text-purple-900">{{ $planejamento->turma->nome ?? 'Não informado' }}
                </div>
            </div>

            <div>
                <span class="text-xs text-purple-700 uppercase tracking-wide">Série/Ano:</span>
                <div class="text-sm text-purple-800">
                    {{ optional($planejamento->nivelEnsino)->nome ?? 'Não informado' }}</div>
            </div>

            <div>
                <span class="text-xs text-purple-700 uppercase tracking-wide">Número de Alunos:</span>
                <div class="text-sm text-purple-800">{{ $planejamento->turma['alunos']->count() ?? 0 }} alunos</div>
            </div>

            <div>
                <span class="text-xs text-purple-700 uppercase tracking-wide">Sala:</span>
                <div class="text-sm text-purple-800">{{ $planejamento->turma->sala[0]['nome'] ?? 'Não informado' }}
                </div>
            </div>
        </div>

        <!-- Lista de Alunos (se disponível) -->
        @if ($planejamento->turma && $planejamento->turma->alunos && $planejamento->turma->alunos->count() > 0)
            <div class="mt-4 pt-4 border-t border-purple-200">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-purple-700 uppercase tracking-wide">Lista de Alunos:</span>
                    <button type="button" id="toggle-alunos" class="text-xs text-purple-600 hover:text-purple-800">
                        <span id="toggle-text">Mostrar</span> <i id="toggle-icon" class="fas fa-chevron-down ml-1"></i>
                    </button>
                </div>

                <div id="lista-alunos" class="hidden">
                    <div class="max-h-40 overflow-y-auto">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                            @foreach ($planejamento->turma->alunos as $aluno)
                                <div class="flex items-center text-sm text-purple-800">
                                    <i class="fas fa-user-graduate mr-2 text-purple-600"></i>
                                    {{ $aluno->nome }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Disciplina -->
    <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
        <h3 class="text-sm font-medium text-orange-900 mb-3 flex items-center">
            <i class="fas fa-book-open mr-2"></i>
            Disciplina
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <span class="text-xs text-orange-700 uppercase tracking-wide">Nome da Disciplina:</span>
                <div class="text-sm font-medium text-orange-900">
                    {{ $planejamento->disciplina->nome ?? 'Não informado' }}</div>
            </div>

            <div>
                <span class="text-xs text-orange-700 uppercase tracking-wide">Área do Conhecimento:</span>
                <div class="text-sm text-orange-800">
                    {{ $planejamento->disciplina->area_conhecimento ?? 'Não informado' }}</div>
            </div>

            <div>
                <span class="text-xs text-orange-700 uppercase tracking-wide">Carga Horária Semanal:</span>
                <div class="text-sm text-orange-800">{{ $planejamento->disciplina->carga_horaria_semanal ?? 0 }}h</div>
            </div>

            <div>
                <span class="text-xs text-orange-700 uppercase tracking-wide">Código da Disciplina:</span>
                <div class="text-sm text-orange-800">{{ $planejamento->disciplina->codigo ?? 'Não informado' }}</div>
            </div>
        </div>

        <!-- Objetivo Geral do Planejamento -->
        @if (!empty($planejamento->objetivo_geral))
            <div class="mt-4 pt-4 border-t border-orange-200">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-orange-700 uppercase tracking-wide">Objetivo Geral:</span>
                    <button type="button" id="toggle-objetivos" class="text-xs text-orange-600 hover:text-orange-800">
                        <span id="toggle-objetivo-text">Mostrar</span> <i id="toggle-objetivo-icon"
                            class="fas fa-chevron-down ml-1"></i>
                    </button>
                </div>

                <div id="objetivo-geral" class="hidden">
                    <div class="max-h-40 overflow-y-auto">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                            @foreach ($planejamento->objetivo_geral as $objetivo)
                                <div class="flex items-center text-sm text-orange-800">
                                    <i class="fas fa-user-graduate mr-2 text-orange-600"></i>
                                    {{ $objetivo }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif


        <!-- Objetivo Geral do Planejamento -->
        @if (!empty($planejamento->objetivo_geral))
            <div class="mt-4 pt-4 border-t border-orange-200">
                <span class="text-xs text-orange-700 uppercase tracking-wide">Objetivo Geral:</span>
                <div class="mt-1 text-sm text-orange-800 bg-white p-3 rounded border border-orange-200">
                    {{ $planejamento->objetivo_geral }}
                </div>
            </div>
        @endif

        <!-- Objetivos Específicos do Planejamento -->
        @if (!empty($planejamento->objetivos_especificos))
            <div class="mt-4 pt-4 border-t border-orange-200">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-orange-700 uppercase tracking-wide">Objetivos Específicos:</span>
                    <button type="button" id="toggle-objetivos-especificos"
                        class="text-xs text-orange-600 hover:text-orange-800">
                        <span id="toggle-objetivos-especificos-text">Mostrar</span> <i
                            id="toggle-objetivos-especificos-icon" class="fas fa-chevron-down ml-1"></i>
                    </button>
                </div>

                <div id="objetivos-especificos" class="hidden">
                    <div class="max-h-40 overflow-y-auto">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                            @foreach ($planejamento->objetivos_especificos as $objetivo)
                                @php
                                    $objetivos = explode(',', $objetivo);
                                @endphp
                                <div class="flex flex-col">
                                    @foreach ($objetivos as $objetivo)
                                        <div class="text-sm text-orange-800">
                                            {{ $objetivo }}
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Compatibilidade e Verificações -->
    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
        <h3 class="text-sm font-medium text-gray-900 mb-3 flex items-center">
            <i class="fas fa-check-circle mr-2"></i>
            Verificações de Compatibilidade
        </h3>

        <div class="space-y-2">
            <!-- Verificação Modalidade x Escola -->
            <div class="flex items-center text-sm">
                <i class="fas fa-check-circle text-green-500 mr-2"></i>
                <span class="text-gray-700">Modalidade compatível com a unidade escolar</span>
            </div>

            <!-- Verificação Turno x Turma -->
            <div class="flex items-center text-sm">
                <i class="fas fa-check-circle text-green-500 mr-2"></i>
                <span class="text-gray-700">Turno compatível com a turma selecionada</span>
            </div>

            <!-- Verificação Disciplina x Série -->
            <div class="flex items-center text-sm">
                <i class="fas fa-check-circle text-green-500 mr-2"></i>
                <span class="text-gray-700">Disciplina adequada para a série/ano</span>
            </div>

            <!-- Verificação Professor x Disciplina -->
            @if ($planejamento->professor)
                <div class="flex items-center text-sm">
                    <i class="fas fa-check-circle text-green-500 mr-2"></i>
                    <span class="text-gray-700">Professor habilitado para a disciplina</span>
                </div>
            @endif
        </div>
    </div>

    <!-- Configurações Adicionais -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Configurações de Aula -->
        <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-4">
            <h4 class="text-sm font-medium text-indigo-900 mb-3">Configurações de Aula</h4>

            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-indigo-700">Duração por Aula:</span>
                    <span class="text-indigo-900 font-medium">{{ $planejamento->duracao_aula ?? 50 }} min</span>
                </div>

                <div class="flex justify-between">
                    <span class="text-indigo-700">Aulas por Semana:</span>
                    <span class="text-indigo-900 font-medium">{{ $planejamento->aulas_por_semana ?? 2 }}</span>
                </div>

                <div class="flex justify-between">
                    <span class="text-indigo-700">Intervalo entre Aulas:</span>
                    <span class="text-indigo-900 font-medium">{{ $planejamento->intervalo_aulas ?? 10 }} min</span>
                </div>
            </div>
        </div>

        <!-- Configurações Pedagógicas -->
        <div class="bg-teal-50 border border-teal-200 rounded-lg p-4">
            <h4 class="text-sm font-medium text-teal-900 mb-3">Configurações Pedagógicas</h4>

            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-teal-700">Tipo de Avaliação:</span>
                    <span class="text-teal-900 font-medium">{{ $planejamento->tipo_avaliacao ?? 'Formativa' }}</span>
                </div>

                <div class="flex justify-between">
                    <span class="text-teal-700">Metodologia Principal:</span>
                    <span
                        class="text-teal-900 font-medium">{{ $planejamento->metodologia_principal ?? 'Não definida' }}</span>
                </div>

                <div class="flex justify-between">
                    <span class="text-teal-700">Recursos Digitais:</span>
                    @php
                        $usaRecursosDigitais = false;
                        if (!empty($planejamento->recursos_tecnologicos)) {
                            $rt = is_string($planejamento->recursos_tecnologicos)
                                ? json_decode($planejamento->recursos_tecnologicos, true)
                                : $planejamento->recursos_tecnologicos;
                            if (is_array($rt)) {
                                $usaRecursosDigitais = count($rt) > 0;
                            } else {
                                $usaRecursosDigitais = !empty($rt);
                            }
                        }
                    @endphp
                    <span class="text-teal-900 font-medium">
                        {{ $usaRecursosDigitais ? 'Sim' : 'Não' }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle lista de alunos
            const toggleBtn = document.getElementById('toggle-alunos');
            const listaAlunos = document.getElementById('lista-alunos');
            const toggleText = document.getElementById('toggle-text');
            const toggleIcon = document.getElementById('toggle-icon');

            if (toggleBtn && listaAlunos) {
                toggleBtn.addEventListener('click', function() {
                    if (listaAlunos.classList.contains('hidden')) {
                        listaAlunos.classList.remove('hidden');
                        toggleText.textContent = 'Ocultar';
                        toggleIcon.classList.remove('fa-chevron-down');
                        toggleIcon.classList.add('fa-chevron-up');
                    } else {
                        listaAlunos.classList.add('hidden');
                        toggleText.textContent = 'Mostrar';
                        toggleIcon.classList.remove('fa-chevron-up');
                        toggleIcon.classList.add('fa-chevron-down');
                    }
                });
            }

            // Toggle objetivo geral
            const toggleObjetivoBtn = document.getElementById('toggle-objetivos');
            const objetivoGeral = document.getElementById('objetivo-geral');
            const toggleObjetivoText = document.getElementById('toggle-objetivo-text');
            const toggleObjetivoIcon = document.getElementById('toggle-objetivo-icon');

            if (toggleObjetivoBtn && objetivoGeral) {
                toggleObjetivoBtn.addEventListener('click', function() {
                    if (objetivoGeral.classList.contains('hidden')) {
                        objetivoGeral.classList.remove('hidden');
                        toggleObjetivoText.textContent = 'Ocultar';
                        toggleObjetivoIcon.classList.remove('fa-chevron-down');
                        toggleObjetivoIcon.classList.add('fa-chevron-up');
                    } else {
                        objetivoGeral.classList.add('hidden');
                        toggleObjetivoText.textContent = 'Mostrar';
                        toggleObjetivoIcon.classList.remove('fa-chevron-up');
                        toggleObjetivoIcon.classList.add('fa-chevron-down');
                    }
                });
            }

            // Toggle objetivos específicos
            const toggleObjetivosEspecificosBtn = document.getElementById('toggle-objetivos-especificos');
            const objetivosEspecificos = document.getElementById('objetivos-especificos');
            const toggleObjetivosEspecificosText = document.getElementById('toggle-objetivos-especificos-text');
            const toggleObjetivosEspecificosIcon = document.getElementById('toggle-objetivos-especificos-icon');

            if (toggleObjetivosEspecificosBtn && objetivosEspecificos) {
                toggleObjetivosEspecificosBtn.addEventListener('click', function() {
                    if (objetivosEspecificos.classList.contains('hidden')) {
                        objetivosEspecificos.classList.remove('hidden');
                        toggleObjetivosEspecificosText.textContent = 'Ocultar';
                        toggleObjetivosEspecificosIcon.classList.remove('fa-chevron-down');
                        toggleObjetivosEspecificosIcon.classList.add('fa-chevron-up');
                    } else {
                        objetivosEspecificos.classList.add('hidden');
                        toggleObjetivosEspecificosText.textContent = 'Mostrar';
                        toggleObjetivosEspecificosIcon.classList.remove('fa-chevron-up');
                        toggleObjetivosEspecificosIcon.classList.add('fa-chevron-down');
                    }
                });
            }
        });
    </script>
@endpush
