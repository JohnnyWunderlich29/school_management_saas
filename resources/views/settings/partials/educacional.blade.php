<div class="space-y-6">
    <!-- Cabeçalho alinhado ao modelo antigo -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200 flex flex-col items-center justify-between md:flex-row">
            <div class="flex items-center">
                <svg class="w-6 h-6 text-indigo-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">Configurações Educacionais</h2>
                    <p class="text-sm text-gray-600 mt-1">{{ $escola->nome ?? '—' }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2 mt-2">
                <button type="button" onclick="openTemplatesBnccModal()"
                        class="inline-flex items-center px-4 py-2 border border-blue-300 rounded-md shadow-sm text-sm font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200"
                        title="Usar templates pré-configurados da BNCC">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Templates
                </button>
            </div>
        </div>

        <!-- Tabs de navegação (modelo antigo) -->
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-4 sm:space-x-8 px-2 sm:px-6 overflow-x-auto" aria-label="Tabs">
                <button onclick="showTab('tab-modalidades')" id="modalidades-tab"
                        class="edutab-btn whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                    Modalidades de Ensino
                </button>
                <button onclick="showTab('tab-niveis')" id="niveis-tab"
                        class="edutab-btn whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                    Níveis de Ensino
                </button>
                <button onclick="showTab('tab-disciplinas')" id="disciplinas-tab"
                        class="edutab-btn whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                    Disciplinas e Cargas Horárias
                </button>
                <button onclick="showTab('tab-turnos')" id="turnos-tab"
                        class="edutab-btn whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                    Turnos
                </button>
            </nav>
        </div>
    </div>

    

    <!-- Conteúdo das sub-abas -->
    <div id="tab-modalidades" class="edutab-content hidden">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-1">
                <x-card>
                    <h3 class="text-base font-semibold mb-4">Adicionar/Configurar Modalidade</h3>
                    <form method="POST" action="{{ route('admin.configuracao-educacional.store-modalidade', ['escola' => $escola->id]) }}" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Modalidade</label>
                            <select name="modalidade_ensino_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <optgroup label="Padrão (BNCC)">
                                    @foreach($modalidadesPadrao as $m)
                                        <option value="{{ $m->id }}">{{ $m->nome }}</option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="Personalizadas da Escola">
                                    @foreach($modalidadesPersonalizadas as $m)
                                        <option value="{{ $m->id }}">{{ $m->nome }}</option>
                                    @endforeach
                                </optgroup>
                            </select>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="checkbox" name="ativo" id="modalidade_ativo" checked>
                            <label for="modalidade_ativo" class="text-sm text-gray-700">Ativo</label>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Capacidade mínima</label>
                                <input type="number" name="capacidade_minima_turma" min="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Capacidade máxima</label>
                                <input type="number" name="capacidade_maxima_turma" min="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
                            </div>
                        </div>
                        <fieldset class="border rounded p-3">
                            <legend class="text-sm font-medium text-gray-700">Turnos permitidos</legend>
                            <div class="grid grid-cols-2 gap-2 mt-2">
                                <label class="flex items-center gap-2"><input type="checkbox" name="turno_matutino"> Matutino</label>
                                <label class="flex items-center gap-2"><input type="checkbox" name="turno_vespertino"> Vespertino</label>
                                <label class="flex items-center gap-2"><input type="checkbox" name="turno_noturno"> Noturno</label>
                                <label class="flex items-center gap-2"><input type="checkbox" name="turno_integral"> Integral</label>
                            </div>
                        </fieldset>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Observações</label>
                            <textarea name="observacoes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded hover:bg-indigo-700">Salvar</button>
                        </div>
                    </form>
                </x-card>
            </div>
            <div class="lg:col-span-2">
                <x-card>
                    <h3 class="text-base font-semibold mb-4">Modalidades configuradas</h3>
                    <div class="overflow-x-auto">
                        <!-- Lista Mobile (cards) -->
                        <div class="md:hidden" id="modalidades-mobile-container">
                            <div id="modalidades-mobile-list" class="space-y-2">
                                @forelse($escola->modalidadeConfigs as $config)
                                    <div class="p-3 border rounded-md"
                                         data-config-id="{{ $config->id }}"
                                         data-modalidade-id="{{ $config->modalidadeEnsino->id }}"
                                         data-modalidade-nome="{{ $config->modalidadeEnsino->nome }}"
                                         data-ativo="{{ $config->ativo ? 1 : 0 }}"
                                         data-cap-min="{{ $config->capacidade_minima_turma ?? '' }}"
                                         data-cap-max="{{ $config->capacidade_maxima_turma ?? '' }}"
                                         data-turno-matutino="{{ $config->permite_turno_matutino ? 1 : 0 }}"
                                         data-turno-vespertino="{{ $config->permite_turno_vespertino ? 1 : 0 }}"
                                         data-turno-noturno="{{ $config->permite_turno_noturno ? 1 : 0 }}"
                                         data-turno-integral="{{ $config->permite_turno_integral ? 1 : 0 }}"
                                         data-observacoes="{{ $config->observacoes ?? '' }}">
                                        <div class="flex items-start justify-between">
                                            <div>
                                                <div class="text-sm font-semibold text-gray-900">{{ $config->modalidadeEnsino->nome }}</div>
                                                <div class="text-xs text-gray-600">Cap.: {{ $config->capacidade_minima_turma }}–{{ $config->capacidade_maxima_turma }}</div>
                                                <div class="text-xs text-gray-600">Turnos: {{ implode(', ', $config->getTurnosPermitidos()) ?: '—' }}</div>
                                            </div>
                                            <div>
                                                @if($config->ativo)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded bg-green-100 text-green-800 text-xs font-medium">Ativo</span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded bg-gray-100 text-gray-800 text-xs font-medium">Inativo</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="mt-2 flex flex-wrap gap-2">
                                            <button type="button" onclick="openModalidadeEditModal({{ $config->modalidadeEnsino->id }})" class="px-2 py-1 text-xs bg-indigo-600 text-white rounded hover:bg-indigo-700">Editar</button>
                                            <button type="button" class="px-2 py-1 text-xs rounded text-white {{ $config->ativo ? 'bg-orange-500 hover:bg-orange-600' : 'bg-green-600 hover:bg-green-700' }}" onclick="openModalidadeToggleModal({{ $config->modalidadeEnsino->id }}, '{{ $config->modalidadeEnsino->nome }}', {{ $config->ativo ? 'true' : 'false' }})">
                                                {{ $config->ativo ? 'Inativar' : 'Ativar' }}
                                            </button>
                                            <button type="button" onclick="openModalidadeDeleteModal({{ $config->id }}, '{{ $config->modalidadeEnsino->nome }}')" class="px-2 py-1 text-xs bg-red-600 text-white rounded hover:bg-red-700">Excluir</button>
                                        </div>
                                    </div>
                                @empty
                                    <div class="p-3 text-sm text-gray-500">Nenhuma modalidade configurada.</div>
                                @endforelse
                            </div>
                        </div>

                        <!-- Tabela Desktop -->
                        <table class="hidden md:table min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Modalidade</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Status</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Capacidade</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Turnos</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200" id="modalidades-tbody">
                                @forelse($escola->modalidadeConfigs as $config)
                                    <tr data-config-id="{{ $config->id }}"
                                        data-modalidade-id="{{ $config->modalidadeEnsino->id }}"
                                        data-modalidade-nome="{{ $config->modalidadeEnsino->nome }}"
                                        data-ativo="{{ $config->ativo ? 1 : 0 }}"
                                        data-cap-min="{{ $config->capacidade_minima_turma ?? '' }}"
                                        data-cap-max="{{ $config->capacidade_maxima_turma ?? '' }}"
                                        data-turno-matutino="{{ $config->permite_turno_matutino ? 1 : 0 }}"
                                        data-turno-vespertino="{{ $config->permite_turno_vespertino ? 1 : 0 }}"
                                        data-turno-noturno="{{ $config->permite_turno_noturno ? 1 : 0 }}"
                                        data-turno-integral="{{ $config->permite_turno_integral ? 1 : 0 }}"
                                        data-observacoes="{{ $config->observacoes ?? '' }}">
                                        <td class="px-4 py-2">{{ $config->modalidadeEnsino->nome }}</td>
                                        <td class="px-4 py-2">
                                            @if($config->ativo)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded bg-green-100 text-green-800 text-xs font-medium">Ativo</span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded bg-gray-100 text-gray-800 text-xs font-medium">Inativo</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2">{{ $config->capacidade_minima_turma }}–{{ $config->capacidade_maxima_turma }}</td>
                                        <td class="px-4 py-2">{{ implode(', ', $config->getTurnosPermitidos()) ?: '—' }}</td>
                                        <td class="px-4 py-2 text-right space-x-2">
                                            <button type="button" onclick="openModalidadeEditModal({{ $config->modalidadeEnsino->id }})" class="px-3 py-1.5 text-xs bg-indigo-600 text-white rounded hover:bg-indigo-700">Editar</button>
                                            <button type="button" class="px-3 py-1.5 text-xs rounded text-white {{ $config->ativo ? 'bg-orange-500 hover:bg-orange-600' : 'bg-green-600 hover:bg-green-700' }}" onclick="openModalidadeToggleModal({{ $config->modalidadeEnsino->id }}, '{{ $config->modalidadeEnsino->nome }}', {{ $config->ativo ? 'true' : 'false' }})">
                                                {{ $config->ativo ? 'Inativar' : 'Ativar' }}
                                            </button>
                                            <button type="button" onclick="openModalidadeDeleteModal({{ $config->id }}, '{{ $config->modalidadeEnsino->nome }}')" class="px-3 py-1.5 text-xs bg-red-600 text-white rounded hover:bg-red-700">Excluir</button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="px-4 py-3 text-sm text-gray-500">Nenhuma modalidade configurada.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </x-card>
            </div>
        </div>
    </div>

    <div id="tab-niveis" class="edutab-content hidden">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-1">
                <x-card>
                    <h3 class="text-base font-semibold mb-4">Adicionar/Configurar Nível</h3>
                    <form method="POST" action="{{ route('admin.configuracao-educacional.store-nivel', ['escola' => $escola->id]) }}" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nível de Ensino</label>
                            <select name="nivel_ensino_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                @foreach($niveisDisponiveis as $n)
                                    <option value="{{ $n->id }}">{{ $n->nome }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="checkbox" name="ativo" id="nivel_ativo" checked>
                            <label for="nivel_ativo" class="text-sm text-gray-700">Ativo</label>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Capacidade mínima</label>
                                <input type="number" name="capacidade_minima_turma" min="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Capacidade máxima</label>
                                <input type="number" name="capacidade_maxima_turma" min="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
                            </div>
                        </div>
                        <fieldset class="border rounded p-3">
                            <legend class="text-sm font-medium text-gray-700">Turnos permitidos</legend>
                            <div class="grid grid-cols-2 gap-2 mt-2">
                                <label class="flex items-center gap-2"><input type="checkbox" name="turno_matutino"> Matutino</label>
                                <label class="flex items-center gap-2"><input type="checkbox" name="turno_vespertino"> Vespertino</label>
                                <label class="flex items-center gap-2"><input type="checkbox" name="turno_noturno"> Noturno</label>
                                <label class="flex items-center gap-2"><input type="checkbox" name="turno_integral"> Integral</label>
                            </div>
                        </fieldset>
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">C.H. semanal (min)</label>
                                <input type="number" name="carga_horaria_semanal" min="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Aulas por dia</label>
                                <input type="number" name="numero_aulas_dia" min="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Duração aula (min)</label>
                                <input type="number" name="duracao_aula_minutos" min="30" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Idade mínima</label>
                                <input type="number" name="idade_minima" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Idade máxima</label>
                                <input type="number" name="idade_maxima" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Observações</label>
                            <textarea name="observacoes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded hover:bg-indigo-700">Salvar</button>
                        </div>
                    </form>
                </x-card>
            </div>
            <div class="lg:col-span-2">
                <x-card>
                    <h3 class="text-base font-semibold mb-4">Níveis configurados</h3>
                    <div class="overflow-x-auto">
                        <!-- Lista Mobile (cards) -->
                        <div class="md:hidden" id="niveis-mobile-container">
                            <div id="niveis-mobile-list" class="space-y-2">
                                @forelse($escola->nivelConfigs as $config)
                                    <div class="p-3 border rounded-md"
                                         data-config-id="{{ $config->id }}"
                                         data-nivel-id="{{ $config->nivelEnsino->id }}"
                                         data-nivel-nome="{{ $config->nivelEnsino->nome }}"
                                         data-ativo="{{ $config->ativo ? 1 : 0 }}"
                                         data-cap-min="{{ $config->capacidade_minima_turma ?? '' }}"
                                         data-cap-max="{{ $config->capacidade_maxima_turma ?? '' }}"
                                         data-turno-matutino="{{ $config->permite_turno_matutino ? 1 : 0 }}"
                                         data-turno-vespertino="{{ $config->permite_turno_vespertino ? 1 : 0 }}"
                                         data-turno-noturno="{{ $config->permite_turno_noturno ? 1 : 0 }}"
                                         data-turno-integral="{{ $config->permite_turno_integral ? 1 : 0 }}"
                                         data-ch-semanal="{{ $config->carga_horaria_semanal ?? '' }}"
                                         data-num-aulas-dia="{{ $config->numero_aulas_dia ?? '' }}"
                                         data-duracao-aula="{{ $config->duracao_aula_minutos ?? '' }}"
                                         data-idade-minima="{{ $config->idade_minima ?? '' }}"
                                         data-idade-maxima="{{ $config->idade_maxima ?? '' }}"
                                         data-observacoes="{{ $config->observacoes ?? '' }}">
                                        <div class="flex items-start justify-between">
                                            <div>
                                                <div class="text-sm font-semibold text-gray-900">{{ $config->nivelEnsino->nome }}</div>
                                                <div class="text-xs text-gray-600">
                                                    @if($config->carga_horaria_semanal)
                                                        {{ $config->carga_horaria_semanal }} min/sem
                                                    @else
                                                        —
                                                    @endif
                                                </div>
                                                <div class="text-xs text-gray-600">Turnos: {{ implode(', ', $config->getTurnosPermitidos()) ?: '—' }}</div>
                                            </div>
                                            <div>
                                                @if($config->ativo)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded bg-green-100 text-green-800 text-xs font-medium">Ativo</span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded bg-gray-100 text-gray-800 text-xs font-medium">Inativo</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="mt-2 flex gap-2">
                                            <button type="button" onclick="openNivelEditModal({{ $config->nivelEnsino->id }})" class="px-2 py-1 text-xs bg-indigo-600 text-white rounded hover:bg-indigo-700">Editar</button>
                                            <button type="button" class="px-2 py-1 text-xs rounded text-white {{ $config->ativo ? 'bg-orange-500 hover:bg-orange-600' : 'bg-green-600 hover:bg-green-700' }}" onclick="openNivelToggleModal({{ $config->nivelEnsino->id }}, '{{ $config->nivelEnsino->nome }}', {{ $config->ativo ? 'true' : 'false' }})">
                                                {{ $config->ativo ? 'Inativar' : 'Ativar' }}
                                            </button>
                                            <form method="POST" action="{{ route('admin.configuracao-educacional.destroy-nivel', ['escola' => $escola->id, 'nivelConfig' => $config->id]) }}" onsubmit="return confirm('Remover configuração deste nível?')" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button class="px-2 py-1 text-xs bg-red-600 text-white rounded hover:bg-red-700">Excluir</button>
                                            </form>
                                        </div>
                                    </div>
                                @empty
                                    <div class="p-3 text-sm text-gray-500">Nenhum nível configurado.</div>
                                @endforelse
                            </div>
                        </div>

                        <!-- Tabela Desktop -->
                        <table class="hidden md:table min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Nível</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Status</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Carga Horária</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Turnos</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">Ações</th>
                                </tr>
                            </thead>
                            <tbody id="niveis-tbody" class="divide-y divide-gray-200">
                                @forelse($escola->nivelConfigs as $config)
                                    <tr data-config-id="{{ $config->id }}"
                                        data-nivel-id="{{ $config->nivelEnsino->id }}"
                                        data-nivel-nome="{{ $config->nivelEnsino->nome }}"
                                        data-ativo="{{ $config->ativo ? 1 : 0 }}"
                                        data-cap-min="{{ $config->capacidade_minima_turma ?? '' }}"
                                        data-cap-max="{{ $config->capacidade_maxima_turma ?? '' }}"
                                        data-turno-matutino="{{ $config->permite_turno_matutino ? 1 : 0 }}"
                                        data-turno-vespertino="{{ $config->permite_turno_vespertino ? 1 : 0 }}"
                                        data-turno-noturno="{{ $config->permite_turno_noturno ? 1 : 0 }}"
                                        data-turno-integral="{{ $config->permite_turno_integral ? 1 : 0 }}"
                                        data-ch-semanal="{{ $config->carga_horaria_semanal ?? '' }}"
                                        data-num-aulas-dia="{{ $config->numero_aulas_dia ?? '' }}"
                                        data-duracao-aula="{{ $config->duracao_aula_minutos ?? '' }}"
                                        data-idade-minima="{{ $config->idade_minima ?? '' }}"
                                        data-idade-maxima="{{ $config->idade_maxima ?? '' }}"
                                        data-observacoes="{{ $config->observacoes ?? '' }}">
                                        <td class="px-4 py-2">{{ $config->nivelEnsino->nome }}</td>
                                        <td class="px-4 py-2">
                                            @if($config->ativo)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded bg-green-100 text-green-800 text-xs font-medium">Ativo</span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded bg-gray-100 text-gray-800 text-xs font-medium">Inativo</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2">
                                            @if($config->carga_horaria_semanal)
                                                {{ $config->carga_horaria_semanal }} min/sem
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="px-4 py-2">{{ implode(', ', $config->getTurnosPermitidos()) ?: '—' }}</td>
                                        <td class="px-4 py-2 text-right space-x-2">
                                            <button type="button" onclick="openNivelEditModal({{ $config->nivelEnsino->id }})" class="px-3 py-1.5 text-xs bg-indigo-600 text-white rounded hover:bg-indigo-700">Editar</button>
                                            <button type="button" class="px-3 py-1.5 text-xs rounded text-white {{ $config->ativo ? 'bg-orange-500 hover:bg-orange-600' : 'bg-green-600 hover:bg-green-700' }}" onclick="openNivelToggleModal({{ $config->nivelEnsino->id }}, '{{ $config->nivelEnsino->nome }}', {{ $config->ativo ? 'true' : 'false' }})">
                                                {{ $config->ativo ? 'Inativar' : 'Ativar' }}
                                            </button>
                                            <form method="POST" action="{{ route('admin.configuracao-educacional.destroy-nivel', ['escola' => $escola->id, 'nivelConfig' => $config->id]) }}" onsubmit="return confirm('Remover configuração deste nível?')" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button class="px-3 py-1.5 text-xs bg-red-600 text-white rounded hover:bg-red-700">Excluir</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="px-4 py-3 text-sm text-gray-500">Nenhum nível configurado.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </x-card>
            </div>
        </div>
    </div>

    <div id="tab-disciplinas" class="edutab-content hidden">
        <x-card>
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-semibold">Disciplinas e Cargas Horárias</h3>
                <div class="text-sm text-gray-600">Gerencie disciplinas por nível e área do conhecimento.</div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Filtrar por nível</label>
                    <select id="filtro-nivel" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">Todos os níveis</option>
                        @foreach($escola->nivelConfigs as $cfg)
                            <option value="{{ $cfg->nivelEnsino->id }}">{{ $cfg->nivelEnsino->nome }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Filtrar por área</label>
                    <select id="filtro-area" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">Todas as áreas</option>
                        <option value="linguagens">Linguagens</option>
                        <option value="matematica">Matemática</option>
                        <option value="ciencias">Ciências</option>
                        <option value="humanas">Ciências Humanas</option>
                        <option value="tecnologia">Tecnologia</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="button" onclick="loadDisciplinas()" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded hover:bg-indigo-700 w-full">Carregar</button>
                </div>
            </div>

            <div id="disciplinas-list" class=""></div>
        </x-card>
    </div>

    <div id="tab-turnos" class="edutab-content hidden">
        <x-card>
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-semibold">Turnos</h3>
                <div>
                    <button type="button" onclick="openTurnoCreateModal()" class="px-3 py-1.5 text-sm bg-indigo-600 text-white rounded hover:bg-indigo-700">Novo Turno</button>
                </div>
            </div>
            <div class="overflow-x-auto">
                <!-- Lista Mobile (cards) -->
                <div class="md:hidden" id="turnos-mobile-container">
                    <div id="turnos-mobile-list" class="space-y-2">
                        @forelse(($turnos ?? []) as $t)
                            <div class="p-3 border rounded-md" data-id="{{ $t->id }}" data-nome="{{ $t->nome }}" data-codigo="{{ $t->codigo }}" data-inicio="{{ $t->hora_inicio }}" data-fim="{{ $t->hora_fim }}" data-ativo="{{ $t->ativo ? 1 : 0 }}" data-descricao="{{ $t->descricao ?? '' }}" data-ordem="{{ $t->ordem ?? '' }}">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <div class="text-sm font-semibold text-gray-900">{{ $t->nome }}</div>
                                        <div class="text-xs text-gray-600">Código: {{ $t->codigo }}</div>
                                        <div class="text-xs text-gray-600">{{ $t->hora_inicio }} – {{ $t->hora_fim }}</div>
                                    </div>
                                    <div>
                                        @if($t->ativo)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded bg-green-100 text-green-800 text-xs font-medium">Ativo</span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded bg-gray-100 text-gray-800 text-xs font-medium">Inativo</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    <button type="button" onclick="openTurnoEditModal({{ $t->id }})" class="px-2 py-1 text-xs bg-indigo-600 text-white rounded hover:bg-indigo-700">Editar</button>
                                    <button type="button" onclick="openTurnoTempoModal({{ $t->id }})" class="px-2 py-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-700">Tempo</button>
                                    <button type="button" onclick="openTurnoSlotsModal({{ $t->id }})" class="px-2 py-1 text-xs bg-teal-600 text-white rounded hover:bg-teal-700">Slots</button>
                                    <button type="button" class="px-2 py-1 text-xs rounded text-white {{ $t->ativo ? 'bg-orange-500 hover:bg-orange-600' : 'bg-green-600 hover:bg-green-700' }}" onclick="openTurnoToggleModal({{ $t->id }}, '{{ $t->nome }}', {{ $t->ativo ? 'true' : 'false' }})">
                                        {{ $t->ativo ? 'Inativar' : 'Ativar' }}
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="p-3 text-sm text-gray-500">Nenhum turno cadastrado.</div>
                        @endforelse
                    </div>
                </div>

                <!-- Tabela Desktop -->
                <table class="hidden md:table min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Nome</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Código</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Horário</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Status</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="turnos-tbody" class="divide-y divide-gray-200">
                        @forelse(($turnos ?? []) as $t)
                            <tr data-id="{{ $t->id }}" data-nome="{{ $t->nome }}" data-codigo="{{ $t->codigo }}" data-inicio="{{ $t->hora_inicio }}" data-fim="{{ $t->hora_fim }}" data-ativo="{{ $t->ativo ? 1 : 0 }}" data-descricao="{{ $t->descricao ?? '' }}" data-ordem="{{ $t->ordem ?? '' }}">
                                <td class="px-4 py-2">{{ $t->nome }}</td>
                                <td class="px-4 py-2">{{ $t->codigo }}</td>
                                <td class="px-4 py-2">{{ $t->hora_inicio }} – {{ $t->hora_fim }}</td>
                                <td class="px-4 py-2">
                                    @if($t->ativo)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded bg-green-100 text-green-800 text-xs font-medium">Ativo</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded bg-gray-100 text-gray-800 text-xs font-medium">Inativo</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 text-right space-x-2">
                                    <button type="button" onclick="openTurnoEditModal({{ $t->id }})" class="px-3 py-1.5 text-xs bg-indigo-600 text-white rounded hover:bg-indigo-700">Editar</button>
                                    <button type="button" onclick="openTurnoTempoModal({{ $t->id }})" class="px-3 py-1.5 text-xs bg-blue-600 text-white rounded hover:bg-blue-700">Tempo</button>
                                    <button type="button" onclick="openTurnoSlotsModal({{ $t->id }})" class="px-3 py-1.5 text-xs bg-teal-600 text-white rounded hover:bg-teal-700">Slots</button>
                                    <button type="button" class="px-3 py-1.5 text-xs rounded text-white {{ $t->ativo ? 'bg-orange-500 hover:bg-orange-600' : 'bg-green-600 hover:bg-green-700' }}" onclick="openTurnoToggleModal({{ $t->id }}, '{{ $t->nome }}', {{ $t->ativo ? 'true' : 'false' }})">
                                        {{ $t->ativo ? 'Inativar' : 'Ativar' }}
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-3 text-sm text-gray-500">Nenhum turno cadastrado.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>
    </div>

    <!-- Modal blocks movidos para fora do container com space-y-6 para evitar mt no overlay -->
</div>

<!-- Modais de Turnos usando x-modal -->
<x-modal name="modal-turno-create" title="Novo Turno">
    <div class="p-4 space-y-3">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Nome</label>
                <input type="text" id="turno-create-nome" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Código</label>
                <input type="text" id="turno-create-codigo" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Hora Início</label>
                <input type="time" id="turno-create-inicio" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Hora Fim</label>
                <input type="time" id="turno-create-fim" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Descrição</label>
            <textarea id="turno-create-descricao" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Ordem</label>
                <input type="number" id="turno-create-ordem" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" id="turno-create-ativo" checked />
                <label class="text-sm text-gray-700">Ativo</label>
            </div>
        </div>
        <div id="turno-create-errors" class="text-sm text-red-600"></div>
    </div>
    <div class="px-4 py-3 border-t flex justify-end gap-2">
        <button type="button" class="px-4 py-2 text-sm bg-white border border-gray-300 rounded hover:bg-gray-50" onclick="closeXModal('modal-turno-create')">Cancelar</button>
        <button type="button" class="px-4 py-2 text-sm text-white bg-indigo-600 rounded hover:bg-indigo-700" onclick="submitTurnoCreate()">Salvar</button>
    </div>
    <div></div>
</x-modal>

<!-- Modais de Modalidades usando x-modal -->
<x-modal name="modal-modalidade-edit" title="Editar Modalidade">
    <div class="p-4 space-y-4">
        <input type="hidden" id="modalidade-edit-id" />
        <div>
            <div class="text-sm font-medium text-gray-700 mb-1">Modalidade</div>
            <div id="modalidade-edit-nome" class="text-sm text-gray-900"></div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="flex items-center gap-2">
                <input type="checkbox" id="modalidade-edit-ativo" />
                <label class="text-sm text-gray-700">Ativo</label>
            </div>
            <div></div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Capacidade mínima da turma</label>
                <input type="number" id="modalidade-edit-cap-min" min="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Capacidade máxima da turma</label>
                <input type="number" id="modalidade-edit-cap-max" min="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
            </div>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="flex items-center gap-2">
                <input type="checkbox" id="modalidade-edit-turno-matutino" />
                <label class="text-sm text-gray-700">Matutino</label>
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" id="modalidade-edit-turno-vespertino" />
                <label class="text-sm text-gray-700">Vespertino</label>
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" id="modalidade-edit-turno-noturno" />
                <label class="text-sm text-gray-700">Noturno</label>
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" id="modalidade-edit-turno-integral" />
                <label class="text-sm text-gray-700">Integral</label>
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Observações</label>
            <textarea id="modalidade-edit-observacoes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
        </div>
        <div id="modalidade-edit-errors" class="text-sm text-red-600"></div>
    </div>
    <div class="px-4 py-3 border-t flex justify-end gap-2">
        <button type="button" class="px-4 py-2 text-sm bg-white border border-gray-300 rounded hover:bg-gray-50" onclick="closeXModal('modal-modalidade-edit')">Cancelar</button>
        <button type="button" class="px-4 py-2 text-sm text-white bg-indigo-600 rounded hover:bg-indigo-700" onclick="submitModalidadeEdit()">Salvar</button>
    </div>
</x-modal>

<x-modal name="modal-modalidade-toggle" title="Alterar Status da Modalidade">
    <div class="p-4">
        <input type="hidden" id="modalidade-toggle-id" />
        <input type="hidden" id="modalidade-toggle-next" />
        <p class="text-sm text-gray-700">Você deseja <span id="modalidade-toggle-action" class="font-semibold"></span> a modalidade <span id="modalidade-toggle-nome" class="font-semibold"></span>?</p>
        <div id="modalidade-toggle-errors" class="text-sm text-red-600 mt-2"></div>
    </div>
    <div class="px-4 py-3 border-t flex justify-end gap-2">
        <button type="button" class="px-4 py-2 text-sm bg-white border border-gray-300 rounded hover:bg-gray-50" onclick="closeXModal('modal-modalidade-toggle')">Cancelar</button>
        <button type="button" class="px-4 py-2 text-sm text-white bg-indigo-600 rounded hover:bg-indigo-700" onclick="submitModalidadeToggle()">Confirmar</button>
    </div>
</x-modal>

<x-modal name="modal-modalidade-delete" title="Excluir Modalidade">
    <div class="p-4">
        <input type="hidden" id="modalidade-delete-config-id" />
        <p class="text-sm text-gray-700">Confirma a exclusão da configuração da modalidade <span id="modalidade-delete-nome" class="font-semibold"></span>?</p>
        <div id="modalidade-delete-errors" class="text-sm text-red-600 mt-2"></div>
    </div>
    <div class="px-4 py-3 border-t flex justify-end gap-2">
        <button type="button" class="px-4 py-2 text-sm bg-white border border-gray-300 rounded hover:bg-gray-50" onclick="closeXModal('modal-modalidade-delete')">Cancelar</button>
        <button type="button" class="px-4 py-2 text-sm text-white bg-red-600 rounded hover:bg-red-700" onclick="submitModalidadeDelete()">Excluir</button>
    </div>
</x-modal>

<!-- Modais de Níveis usando x-modal -->
<x-modal name="modal-nivel-edit" title="Editar Nível">
    <div class="p-4 space-y-4">
        <input type="hidden" id="nivel-edit-config-id" />
        <input type="hidden" id="nivel-edit-nivel-id" />
        <div>
            <div class="text-sm font-medium text-gray-700 mb-1">Nível</div>
            <div id="nivel-edit-nivel-nome" class="text-sm text-gray-900"></div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="flex items-center gap-2">
                <input type="checkbox" id="nivel-edit-ativo" />
                <label class="text-sm text-gray-700">Ativo</label>
            </div>
            <div></div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Capacidade mínima da turma</label>
                <input type="number" id="nivel-edit-cap-min" min="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Capacidade máxima da turma</label>
                <input type="number" id="nivel-edit-cap-max" min="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
            </div>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="flex items-center gap-2">
                <input type="checkbox" id="nivel-edit-turno-matutino" />
                <label class="text-sm text-gray-700">Matutino</label>
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" id="nivel-edit-turno-vespertino" />
                <label class="text-sm text-gray-700">Vespertino</label>
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" id="nivel-edit-turno-noturno" />
                <label class="text-sm text-gray-700">Noturno</label>
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" id="nivel-edit-turno-integral" />
                <label class="text-sm text-gray-700">Integral</label>
            </div>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Carga horária semanal (min)</label>
                <input type="number" id="nivel-edit-ch-semanal" min="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Número de aulas/dia</label>
                <input type="number" id="nivel-edit-num-aulas-dia" min="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Duração aula (min)</label>
                <input type="number" id="nivel-edit-duracao-aula" min="30" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
            </div>
            <div></div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Idade mínima</label>
                <input type="number" id="nivel-edit-idade-minima" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Idade máxima</label>
                <input type="number" id="nivel-edit-idade-maxima" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Observações</label>
            <textarea id="nivel-edit-observacoes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
        </div>
        <div id="nivel-edit-errors" class="text-sm text-red-600"></div>
    </div>
    <div class="px-4 py-3 border-t flex justify-end gap-2">
        <button type="button" class="px-4 py-2 text-sm bg-white border border-gray-300 rounded hover:bg-gray-50" onclick="closeXModal('modal-nivel-edit')">Cancelar</button>
        <button type="button" class="px-4 py-2 text-sm text-white bg-indigo-600 rounded hover:bg-indigo-700" onclick="submitNivelEdit()">Salvar</button>
    </div>
</x-modal>

<x-modal name="modal-nivel-toggle" title="Alterar Status do Nível">
    <div class="p-4">
        <input type="hidden" id="nivel-toggle-nivel-id" />
        <input type="hidden" id="nivel-toggle-next" />
        <p class="text-sm text-gray-700">Você deseja <span id="nivel-toggle-action" class="font-semibold"></span> o nível <span id="nivel-toggle-nome" class="font-semibold"></span>?</p>
        <div id="nivel-toggle-errors" class="text-sm text-red-600 mt-2"></div>
    </div>
    <div class="px-4 py-3 border-t flex justify-end gap-2">
        <button type="button" class="px-4 py-2 text-sm bg-white border border-gray-300 rounded hover:bg-gray-50" onclick="closeXModal('modal-nivel-toggle')">Cancelar</button>
        <button type="button" class="px-4 py-2 text-sm text-white bg-indigo-600 rounded hover:bg-indigo-700" onclick="submitNivelToggle()">Confirmar</button>
    </div>
</x-modal>

<x-modal name="modal-turno-edit" title="Editar Turno">
    <div class="p-4 space-y-3">
        <input type="hidden" id="turno-edit-id" />
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Nome</label>
                <input type="text" id="turno-edit-nome" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Código</label>
                <input type="text" id="turno-edit-codigo" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Hora Início</label>
                <input type="time" id="turno-edit-inicio" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Hora Fim</label>
                <input type="time" id="turno-edit-fim" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Descrição</label>
            <textarea id="turno-edit-descricao" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Ordem</label>
                <input type="number" id="turno-edit-ordem" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" id="turno-edit-ativo" />
                <label class="text-sm text-gray-700">Ativo</label>
            </div>
        </div>
        <div id="turno-edit-errors" class="text-sm text-red-600"></div>
    </div>
    <div class="px-4 py-3 border-t flex justify-end gap-2">
        <button type="button" class="px-4 py-2 text-sm bg-white border border-gray-300 rounded hover:bg-gray-50" onclick="closeXModal('modal-turno-edit')">Cancelar</button>
        <button type="button" class="px-4 py-2 text-sm text-white bg-indigo-600 rounded hover:bg-indigo-700" onclick="submitTurnoEdit()">Salvar</button>
    </div>
</x-modal>

<x-modal name="modal-turno-tempo" title="Tempo do Turno">
    <div class="p-4 space-y-4">
        <div id="turno-tempo-content">
            <div class="text-sm text-gray-600">Carregando...</div>
        </div>
    </div>
    <div class="px-4 py-3 border-t flex justify-end">
        <button type="button" class="px-4 py-2 text-sm bg-white border border-gray-300 rounded hover:bg-gray-50" onclick="closeXModal('modal-turno-tempo')">Fechar</button>
    </div>
</x-modal>

<x-modal name="modal-turno-slots" title="Gerenciar Slots">
    <div class="p-4 space-y-4">
        <!-- Toolbar de criação -->
        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-600" id="turno-slots-turno-nome"></div>
            <button type="button" id="btn-open-create-slot" class="px-3 py-1.5 text-xs bg-indigo-600 text-white rounded hover:bg-indigo-700">Adicionar Slot</button>
        </div>
        <!-- Form de criação -->
        <div id="turno-slot-create-form" class="hidden border rounded p-3 space-y-3">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nome</label>
                    <input type="text" id="slot-create-nome" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tipo</label>
                    <select id="slot-create-tipo" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="aula">Aula</option>
                        <option value="intervalo">Intervalo</option>
                        <option value="almoco">Almoço</option>
                        <option value="outro">Outro</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Hora Início</label>
                    <input type="time" id="slot-create-inicio" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Hora Fim</label>
                    <input type="time" id="slot-create-fim" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Ordem</label>
                    <input type="number" id="slot-create-ordem" min="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Duração (min)</label>
                    <input type="number" id="slot-create-duracao" min="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Descrição</label>
                <textarea id="slot-create-descricao" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" id="slot-create-ativo" checked />
                <label class="text-sm text-gray-700">Ativo</label>
            </div>
            <div id="slot-create-errors" class="text-sm text-red-600"></div>
            <div class="flex justify-end gap-2">
                <button type="button" class="px-3 py-1.5 text-xs bg-white border border-gray-300 rounded hover:bg-gray-50" id="btn-cancel-create-slot">Cancelar</button>
                <button type="button" class="px-3 py-1.5 text-xs text-white bg-indigo-600 rounded hover:bg-indigo-700" id="btn-submit-create-slot">Salvar</button>
            </div>
        </div>

        <!-- Lista de slots -->
        <div id="turno-slots-content">
            <div class="text-sm text-gray-600">Carregando...</div>
        </div>
    </div>
    <div class="px-4 py-3 border-t flex justify-end">
        <button type="button" class="px-4 py-2 text-sm bg-white border border-gray-300 rounded hover:bg-gray-50" onclick="closeXModal('modal-turno-slots')">Fechar</button>
    </div>
</x-modal>

<x-modal name="modal-turno-toggle" title="Alterar Status do Turno">
    <div class="p-4 space-y-2">
        <div class="text-sm text-gray-700">Tem certeza que deseja <span id="turno-toggle-action" class="font-semibold">inativar</span> o turno <span id="turno-toggle-nome" class="font-semibold"></span>?</div>
        <input type="hidden" id="turno-toggle-id" />
        <input type="hidden" id="turno-toggle-next" />
        <div id="turno-toggle-errors" class="text-sm text-red-600"></div>
    </div>
    <div class="px-4 py-3 border-t flex justify-end gap-2">
        <button type="button" class="px-4 py-2 text-sm bg-white border border-gray-300 rounded hover:bg-gray-50" onclick="closeXModal('modal-turno-toggle')">Cancelar</button>
        <button type="button" class="px-4 py-2 text-sm text-white bg-orange-600 rounded hover:bg-orange-700" onclick="submitTurnoToggle()">Confirmar</button>
    </div>
</x-modal>

<!-- Modal Templates BNCC (padronizado ao layout base) -->
<div id="templatesBnccModal" class="fixed inset-0 mt-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <!-- Header -->
            <div class="flex items-center justify-between pb-4 mb-4 border-b border-gray-200">
                <div>
                    <h3 class="text-lg font-medium text-gray-900">Templates BNCC</h3>
                    <p class="text-sm text-gray-600">Aplicar modelos por categoria/subcategoria e modalidade compatível</p>
                </div>
                <button type="button" class="text-gray-400 hover:text-gray-600" onclick="closeTemplatesBnccModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Conteúdo -->
            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="md:col-span-1">
                        <label class="block text-sm font-medium text-gray-700">Filtrar por modalidade</label>
                        <select id="bncc-filtro-modalidade" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" onchange="filtrarTemplatesPorModalidade()">
                            <option value="">Todas</option>
                            @foreach($escola->modalidadeConfigs as $mc)
                                <option value="{{ $mc->modalidadeEnsino->id }}">{{ $mc->modalidadeEnsino->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div id="templates-bncc-list" class="space-y-4"></div>
            </div>

            <!-- Ações -->
            <div class="flex justify-end space-x-3 pt-4 mt-4 border-t border-gray-200">
                <button type="button" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" onclick="closeTemplatesBnccModal()">Fechar</button>
                <button type="button" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" onclick="applySelectedTemplatesBncc()">Aplicar Selecionados</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar Disciplina usando x-modal -->
<x-modal name="modal-disciplina-edit" title="Editar Disciplina">
    <div class="p-4 space-y-4">
        <input type="hidden" id="disciplina-id" />
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Nome</label>
            <input type="text" id="disciplina-nome" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Código</label>
                <input type="text" id="disciplina-codigo" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Cor</label>
                <input type="color" id="disciplina-cor" class="w-16 h-10 rounded-md border border-gray-300 shadow-sm" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Área do Conhecimento</label>
                <select id="disciplina-area" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Selecione...</option>
                    <option value="linguagens">Linguagens</option>
                    <option value="matematica">Matemática</option>
                    <option value="ciencias">Ciências</option>
                    <option value="humanas">Ciências Humanas</option>
                    <option value="tecnologia">Tecnologia</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Ordem</label>
                <input type="number" id="disciplina-ordem" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
            </div>
        </div>
        <div>
            <h4 class="text-sm font-semibold text-gray-900 mt-2">Configurações por nível</h4>
            <div id="disciplina-niveis-container" class="space-y-3 mt-2"></div>
        </div>
    </div>
    <div class="px-4 py-3 border-t flex justify-end gap-2">
        <button type="button" class="px-4 py-2 text-sm bg-white border border-gray-300 rounded hover:bg-gray-50" onclick="closeXModal('modal-disciplina-edit')">Cancelar</button>
        <button type="button" class="px-4 py-2 text-sm text-white bg-indigo-600 rounded hover:bg-indigo-700" onclick="saveDisciplina()">Salvar</button>
    </div>
    <div></div>
</x-modal>

<!-- Modal de Ativação/Inativação da Disciplina -->
<x-modal name="modal-disciplina-toggle" title="Alterar Status">
    <div class="p-4">
        <input type="hidden" id="disciplina-toggle-id" />
        <input type="hidden" id="disciplina-toggle-next" />
        <p class="text-sm text-gray-700">Você deseja <span id="disciplina-toggle-action" class="font-semibold"></span> a disciplina <span id="disciplina-toggle-nome" class="font-semibold"></span>?</p>
        <div id="disciplina-toggle-errors" class="text-sm text-red-600 mt-2"></div>
    </div>
    <div class="px-4 py-3 border-t flex justify-end gap-2">
        <button type="button" class="px-4 py-2 text-sm bg-white border border-gray-300 rounded hover:bg-gray-50" onclick="closeXModal('modal-disciplina-toggle')">Cancelar</button>
        <button type="button" class="px-4 py-2 text-sm text-white bg-indigo-600 rounded hover:bg-indigo-700" onclick="submitDisciplinaToggle()">Confirmar</button>
    </div>
    <div></div>
</x-modal>

<script>
    // Navegação das sub-abas com animação de transição
    function showTab(tabId) {
        const next = document.getElementById(tabId);
        if (!next) return;

        // Encontrar aba atualmente visível
        const current = document.querySelector('.edutab-content:not(.hidden)');

        // Reset estados dos botões
        document.querySelectorAll('.edutab-btn').forEach(el => {
            el.classList.remove('border-indigo-500', 'text-indigo-600');
            el.classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
        });

        // Ativar botão da aba selecionada
        const map = {
            'tab-modalidades': 'modalidades-tab',
            'tab-niveis': 'niveis-tab',
            'tab-disciplinas': 'disciplinas-tab',
            'tab-turnos': 'turnos-tab'
        };
        const btnId = map[tabId];
        const btn = btnId ? document.getElementById(btnId) : null;
        if (btn) {
            btn.classList.add('border-indigo-500', 'text-indigo-600');
            btn.classList.remove('border-transparent', 'text-gray-500');
        }

        // Esconder todas as outras abas imediatamente
        document.querySelectorAll('.edutab-content').forEach(el => {
            if (el !== current && el !== next) el.classList.add('hidden');
        });

        // Animação de saída da aba atual
        if (current && current !== next) {
            current.classList.add('transition', 'duration-200', 'ease-in', 'opacity-0', 'translate-y-1');
            current.addEventListener('transitionend', function handleExit() {
                current.removeEventListener('transitionend', handleExit);
                current.classList.add('hidden');
                current.classList.remove('transition', 'duration-200', 'ease-in', 'opacity-0', 'translate-y-1');
            }, { once: true });
        }

        // Preparar próxima aba e animar entrada
        next.classList.remove('hidden');
        next.classList.add('transition', 'duration-200', 'ease-out', 'opacity-0', 'translate-y-1');
        // Forçar próximo frame para transição
        requestAnimationFrame(() => {
            next.classList.remove('opacity-0', 'translate-y-1');
        });
        next.addEventListener('transitionend', function handleEnter() {
            next.removeEventListener('transitionend', handleEnter);
            next.classList.remove('transition', 'duration-200', 'ease-out');
        }, { once: true });
    }

    // CSRF token para requisições
    function csrfToken() {
        const el = document.querySelector('meta[name="csrf-token"]');
        return el ? el.getAttribute('content') : '';
    }

    // Helpers genéricos de modal (padronizados)
    function openModal(id) {
        const el = document.getElementById(id);
        if (el) el.classList.remove('hidden');
    }
    function closeModal(id) {
        const el = document.getElementById(id);
        if (el) el.classList.add('hidden');
    }

    // Abstrações para x-modal (fallback para modais locais)
    function openXModal(id) {
        if (typeof showModal === 'function') {
            showModal(id);
        } else {
            openModal(id);
        }
    }
    function closeXModal(id) {
        // Fechar modais do componente x-modal via evento global
        try {
            window.dispatchEvent(new CustomEvent('close-modal'));
        } catch (e) {
            // Fallback mínimo caso não haja suporte ao evento
            const el = document.getElementById(id);
            if (el) el.classList.add('hidden');
        }
    }

    // Modal Templates BNCC
    function openTemplatesBnccModal() {
        openModal('templatesBnccModal');
        loadTemplatesBncc();
    }
    function closeTemplatesBnccModal() {
        closeModal('templatesBnccModal');
    }
    async function loadTemplatesBncc() {
        const url = "{{ route('admin.configuracao-educacional.templates-bncc', ['escola' => $escola->id]) }}";
        const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
        const data = await res.json();
        // Guardar globalmente para re-render com filtros
        window.bnccTemplatesData = data || {};
        renderTemplatesBncc();
    }
    function filtrarTemplatesPorModalidade() {
        renderTemplatesBncc();
    }
    function renderTemplatesBncc() {
        const list = document.getElementById('templates-bncc-list');
        list.innerHTML = '';
        const data = window.bnccTemplatesData || {};
        const selectedMod = document.getElementById('bncc-filtro-modalidade')?.value || '';
        if (data.templates && typeof data.templates === 'object') {
            Object.entries(data.templates).forEach(([categoria, subcategorias]) => {
                const wrap = document.createElement('div');
                wrap.className = 'border rounded-lg overflow-hidden';
                const head = document.createElement('div');
                head.className = 'px-4 py-2 bg-gray-50 border-b text-sm font-semibold text-gray-700';
                head.textContent = categoria;
                wrap.appendChild(head);
                const body = document.createElement('div');
                body.className = 'p-4 space-y-3';
                wrap.appendChild(body);
                Object.entries(subcategorias || {}).forEach(([subcategoria, items]) => {
                    const subEl = document.createElement('div');
                    subEl.className = 'text-xs text-gray-600 mt-1';
                    subEl.textContent = subcategoria;
                    body.appendChild(subEl);
                    (items || []).forEach(t => {
                        // Detectar modalidades compatíveis em possíveis chaves
                        const mods = t.modalidades_compat || t.modalidades || t.compatible_modalidades || [];
                        const match = !selectedMod || (Array.isArray(mods) && mods.map(String).includes(String(selectedMod)));
                        if (!match) return;
                        const item = document.createElement('label');
                        item.className = 'flex items-start gap-3 p-3 border rounded';
                        const disabled = t.ja_configurado ? 'disabled' : '';
                        const chip = t.ja_configurado ? '<span class="ml-2 inline-flex items-center px-2 py-0.5 rounded bg-gray-100 text-gray-600 text-xs">Já configurado</span>' : '';
                        const modsText = Array.isArray(mods) && mods.length
                            ? `<div class=\"mt-1 text-xs text-gray-500\">Compatível: ${mods.join(', ')}</div>`
                            : '';
                        item.innerHTML = `<input type=\"checkbox\" class=\"mt-1\" value=\"${t.id}\" ${disabled}>` +
                                         `<div><div class=\"font-medium\">${t.nome}</div>` +
                                         `<div class=\"text-xs text-gray-500\">${t.codigo || ''}</div>` +
                                         `<div class=\"text-sm text-gray-600\">${t.descricao || ''}${chip}</div>${modsText}</div>`;
                        body.appendChild(item);
                    });
                });
                list.appendChild(wrap);
            });
        } else {
            list.innerHTML = '<div class="text-sm text-gray-600">Nenhum template disponível.</div>';
        }
    }
    async function applySelectedTemplatesBncc() {
        const selected = Array.from(document.querySelectorAll('#templates-bncc-list input[type="checkbox"]:checked')).map(i => i.value);
        if (!selected.length) {
            alert('Selecione ao menos um template.');
            return;
        }
        const url = "{{ route('admin.configuracao-educacional.aplicar-templates-bncc', ['escola' => $escola->id]) }}";
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },
            body: JSON.stringify({ templates: selected })
        });
        const data = await res.json();
        if (data.success) {
            alert(data.message || 'Templates aplicados com sucesso');
            closeTemplatesBnccModal();
            loadDisciplinas();
        } else {
            alert(data.message || 'Falha ao aplicar templates');
        }
    }

    // Disciplinas
    async function loadDisciplinas() {
        const nivelId = document.getElementById('filtro-nivel').value || '';
        const area = document.getElementById('filtro-area').value || '';
        const params = new URLSearchParams();
        if (nivelId) params.set('nivel_id', nivelId);
        if (area) params.set('area', area);
        const url = "{{ route('admin.configuracao-educacional.disciplinas', ['escola' => $escola->id]) }}" + (params.toString() ? ('?' + params.toString()) : '');
        const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
        const data = await res.json();
        // Guardar níveis disponíveis para uso no modal
        if (data && Array.isArray(data.niveis)) {
            window.educacaoNiveis = data.niveis;
        }
        renderDisciplinas(data);
    }
    function renderDisciplinas(data) {
        const container = document.getElementById('disciplinas-list');
        container.innerHTML = '';
        if (!data || !Array.isArray(data.disciplinas) || !data.disciplinas.length) {
            container.innerHTML = '<div class="text-sm text-gray-600">Nenhuma disciplina encontrada com os filtros atuais.</div>';
            return;
        }
        function areaBadgeHtml(area) {
            const map = {
                linguagens: 'bg-blue-100 text-blue-800',
                matematica: 'bg-indigo-100 text-indigo-800',
                ciencias: 'bg-emerald-100 text-emerald-800',
                humanas: 'bg-amber-100 text-amber-800',
                tecnologia: 'bg-purple-100 text-purple-800'
            };
            const cls = map[area] || 'bg-gray-100 text-gray-800';
            const label = area ? area.charAt(0).toUpperCase() + area.slice(1) : '—';
            return `<span class="inline-flex items-center px-2 py-0.5 rounded ${cls} text-xs font-medium">${label}</span>`;
        }
        // Lista Mobile (cards)
        const mobileWrap = document.createElement('div');
        mobileWrap.className = 'md:hidden';
        const mobileList = document.createElement('div');
        mobileList.id = 'disciplinas-mobile-list';
        mobileList.className = 'space-y-2';
        mobileWrap.appendChild(mobileList);

        // Tabela Desktop
        const desktopWrap = document.createElement('div');
        desktopWrap.className = 'hidden md:block';
        const table = document.createElement('table');
        table.className = 'min-w-full divide-y divide-gray-200';
        table.innerHTML = `
            <thead>
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Disciplina</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Área</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Ativa</th>
                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200"></tbody>
        `;
        const tbody = table.querySelector('tbody');

        // Renderizar itens nas duas visões
        data.disciplinas.forEach(d => {
            // Desktop row
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="px-4 py-2">${d.nome}</td>
                <td class="px-4 py-2">${areaBadgeHtml(d.area_conhecimento || '')}</td>
                <td class="px-4 py-2">${d.ativo ? '<span class="inline-flex items-center px-2 py-0.5 rounded bg-green-100 text-green-800 text-xs font-medium">Sim</span>' : '<span class="inline-flex items-center px-2 py-0.5 rounded bg-gray-100 text-gray-800 text-xs font-medium">Não</span>'}</td>
                <td class="px-4 py-2 text-right space-x-2">
                    <button class="px-3 py-1.5 text-xs bg-indigo-600 text-white rounded hover:bg-indigo-700" onclick='editDisciplina(${JSON.stringify(d).replace(/'/g, "&apos;")})'>Editar</button>
                    <button class="px-3 py-1.5 text-xs rounded text-white ${d.ativo ? 'bg-orange-500 hover:bg-orange-600' : 'bg-green-600 hover:bg-green-700'}" onclick="openDisciplinaToggleModal(${d.id}, '${(d.nome || '').replace(/'/g, '&#39;')}', ${d.ativo ? 'true' : 'false'})">${d.ativo ? 'Inativar' : 'Ativar'}</button>
                </td>
            `;
            tbody.appendChild(tr);

            // Mobile card
            const card = document.createElement('div');
            card.className = 'p-3 border rounded-md';
            const nomeEsc = (d.nome || '').replace(/'/g, "&#39;");
            const payload = JSON.stringify(d).replace(/'/g, "&apos;");
            const rels = d.disciplina_niveis || d.disciplinaNiveis || [];
            const resumo = [];
            for (const r of rels) {
                const nv = r.nivel_ensino || r.nivelEnsino || {};
                const nomeNv = nv.nome || `Nível ${nv.id || ''}`;
                const chs = (typeof r.carga_horaria_semanal !== 'undefined' && r.carga_horaria_semanal !== null) ? `${r.carga_horaria_semanal}h/sem` : null;
                if (chs) resumo.push(`${nomeNv}: ${chs}`);
                if (resumo.length >= 3) break;
            }
            const extraCount = (rels || []).filter(r => (r.carga_horaria_semanal ?? '') !== '').length - resumo.length;
            const resumoHtml = resumo.length ? `<div class=\"text-xs text-gray-600 mt-1\">${resumo.join(' • ')}${extraCount > 0 ? ` • +${extraCount}` : ''}</div>` : '';
            card.innerHTML = `
                <div class="flex items-start justify-between">
                    <div>
                        <div class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                            <span>${nomeEsc}</span>
                            ${areaBadgeHtml(d.area_conhecimento || '')}
                        </div>
                        ${resumoHtml}
                    </div>
                    <div>
                        ${d.ativo ? '<span class="inline-flex items-center px-2 py-0.5 rounded bg-green-100 text-green-800 text-xs font-medium">Ativa</span>' : '<span class="inline-flex items-center px-2 py-0.5 rounded bg-gray-100 text-gray-800 text-xs font-medium">Inativa</span>'}
                    </div>
                </div>
                <div class="mt-2 flex flex-wrap gap-2">
                    <button type="button" class="px-2 py-1 text-xs bg-indigo-600 text-white rounded hover:bg-indigo-700" onclick='editDisciplina(${payload})'>Editar</button>
                    <button type="button" class="px-2 py-1 text-xs rounded text-white ${d.ativo ? 'bg-orange-500 hover:bg-orange-600' : 'bg-green-600 hover:bg-green-700'}" onclick="openDisciplinaToggleModal(${d.id}, '${nomeEsc}', ${d.ativo ? 'true' : 'false'})">${d.ativo ? 'Inativar' : 'Ativar'}</button>
                </div>
            `;
            mobileList.appendChild(card);
        });

        desktopWrap.appendChild(table);
        container.appendChild(mobileWrap);
        container.appendChild(desktopWrap);
    }
    function editDisciplina(d) {
        document.getElementById('disciplina-id').value = d.id;
        document.getElementById('disciplina-nome').value = d.nome || '';
        document.getElementById('disciplina-codigo').value = d.codigo || '';
        document.getElementById('disciplina-cor').value = d.cor_hex || '#000000';
        document.getElementById('disciplina-area').value = d.area_conhecimento || '';
        document.getElementById('disciplina-ordem').value = (typeof d.ordem !== 'undefined' && d.ordem !== null) ? d.ordem : '';

        const container = document.getElementById('disciplina-niveis-container');
        container.innerHTML = '';
        const niveis = window.educacaoNiveis || [];
        const rels = d.disciplina_niveis || d.disciplinaNiveis || [];
        const relPorNivelId = {};
        rels.forEach(r => {
            const nv = r.nivel_ensino || r.nivelEnsino;
            if (nv && nv.id) {
                relPorNivelId[nv.id] = r;
            }
        });
        niveis.forEach(nv => {
            const existing = relPorNivelId[nv.id] || {};
            const grp = document.createElement('div');
            grp.className = 'nivel-config border rounded p-3';
            grp.setAttribute('data-nivel-id', nv.id);
            grp.innerHTML = `
                <div class="flex items-center justify-between mb-2">
                    <div class="text-sm font-medium text-gray-800">${nv.nome}</div>
                    <div class="text-xs text-gray-500">ID: ${nv.id}</div>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700">C.H. semanal</label>
                        <input type="number" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" value="${existing.carga_horaria_semanal ?? ''}" data-field="carga_horaria_semanal" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700">C.H. anual</label>
                        <input type="number" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" value="${existing.carga_horaria_anual ?? ''}" data-field="carga_horaria_anual" />
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" ${existing.obrigatoria ? 'checked' : ''} data-field="obrigatoria" />
                        <label class="text-xs text-gray-700">Obrigatória</label>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700">Ordem</label>
                        <input type="number" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" value="${typeof existing.ordem !== 'undefined' ? existing.ordem : ''}" data-field="ordem" />
                    </div>
                </div>
            `;
            container.appendChild(grp);
        });
        openXModal('modal-disciplina-edit');
    }
    function closeEditarDisciplina() {
        closeXModal('modal-disciplina-edit');
    }
    async function saveDisciplina() {
        const id = document.getElementById('disciplina-id').value;
        const nome = document.getElementById('disciplina-nome').value;
        const codigo = document.getElementById('disciplina-codigo').value.trim();
        const corHex = document.getElementById('disciplina-cor').value.trim();
        const area = document.getElementById('disciplina-area').value;
        const ordemRaw = document.getElementById('disciplina-ordem').value;
        const ordem = ordemRaw === '' ? null : parseInt(ordemRaw, 10);

        // Atualizar dados básicos da disciplina
        const urlDisc = "{{ route('admin.configuracao-educacional.update-disciplina', ['escola' => $escola->id]) }}";
        const resDisc = await fetch(urlDisc, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },
            body: JSON.stringify({
                disciplina_id: id,
                nome,
                codigo: codigo || null,
                cor_hex: corHex || null,
                area_conhecimento: area || null,
                ordem: isNaN(ordem) ? null : ordem
            })
        });
        const dataDisc = await resDisc.json();
        if (!dataDisc.success) {
            alert(dataDisc.message || 'Erro ao atualizar disciplina');
            return;
        }

        // Atualizar configurações por nível (apenas onde houver C.H. semanal informada)
        const urlNivel = "{{ route('admin.configuracao-educacional.update-disciplina-nivel', ['escola' => $escola->id]) }}";
        const grupos = Array.from(document.querySelectorAll('#disciplina-niveis-container .nivel-config'));
        for (const grp of grupos) {
            const nivelId = grp.getAttribute('data-nivel-id');
            const chSemanalEl = grp.querySelector('[data-field="carga_horaria_semanal"]');
            const chAnualEl = grp.querySelector('[data-field="carga_horaria_anual"]');
            const obrigatoriaEl = grp.querySelector('[data-field="obrigatoria"]');
            const ordemEl = grp.querySelector('[data-field="ordem"]');
            const chSemanalRaw = chSemanalEl?.value ?? '';
            if (chSemanalRaw === '') continue; // requer c.h. semanal
            const chSemanal = parseFloat(chSemanalRaw);
            const chAnualRaw = chAnualEl?.value ?? '';
            const chAnual = chAnualRaw === '' ? null : parseFloat(chAnualRaw);
            const obrigatoria = !!(obrigatoriaEl && obrigatoriaEl.checked);
            const ordemNivelRaw = ordemEl?.value ?? '';
            const ordemNivel = ordemNivelRaw === '' ? null : parseInt(ordemNivelRaw, 10);

            const resNivel = await fetch(urlNivel, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                },
                body: JSON.stringify({
                    disciplina_id: id,
                    nivel_ensino_id: nivelId,
                    carga_horaria_semanal: chSemanal,
                    carga_horaria_anual: chAnual,
                    obrigatoria: obrigatoria,
                    ordem: isNaN(ordemNivel) ? null : ordemNivel
                })
            });
            const dataNivel = await resNivel.json();
            if (!dataNivel.success) {
                alert(dataNivel.message || `Erro ao atualizar carga horária do nível ${nivelId}`);
                return;
            }
        }

        alert('Disciplina atualizada com sucesso');
        closeXModal('modal-disciplina-edit');
        loadDisciplinas();
    }

    // Toggle de ativação/inativação da disciplina via x-modal
    function openDisciplinaToggleModal(id, nome, ativo) {
        const next = ativo ? 0 : 1;
        const idEl = document.getElementById('disciplina-toggle-id');
        const nextEl = document.getElementById('disciplina-toggle-next');
        const nomeEl = document.getElementById('disciplina-toggle-nome');
        const actionEl = document.getElementById('disciplina-toggle-action');
        const errEl = document.getElementById('disciplina-toggle-errors');
        if (idEl) idEl.value = id;
        if (nextEl) nextEl.value = next;
        if (nomeEl) nomeEl.textContent = nome || '';
        if (actionEl) actionEl.textContent = ativo ? 'inativar' : 'ativar';
        if (errEl) errEl.textContent = '';
        openXModal('modal-disciplina-toggle');
    }

    async function submitDisciplinaToggle() {
        const id = document.getElementById('disciplina-toggle-id').value;
        const next = document.getElementById('disciplina-toggle-next').value;
        const urlDisc = "{{ route('admin.configuracao-educacional.update-disciplina', ['escola' => $escola->id]) }}";
        const res = await fetch(urlDisc, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },
            body: JSON.stringify({
                disciplina_id: id,
                ativo: parseInt(next, 10)
            })
        });
        const data = await res.json().catch(() => ({ success: false }));
        if (!res.ok || !data.success) {
            const errEl = document.getElementById('disciplina-toggle-errors');
            if (errEl) errEl.textContent = (data && data.message) ? data.message : 'Erro ao alterar status';
            return;
        }
        closeXModal('modal-disciplina-toggle');
        loadDisciplinas();
    }

    // ===== Níveis (AJAX + x-modal) =====
    function _findNivelElementById(nivelId) {
        let el = document.querySelector(`#niveis-tbody tr[data-nivel-id="${nivelId}"]`);
        if (!el) {
            el = document.querySelector(`#niveis-mobile-list div[data-nivel-id="${nivelId}"]`);
        }
        return el;
    }
    function openNivelEditModal(nivelId) {
        const el = _findNivelElementById(nivelId);
        if (!el) return;
        document.getElementById('nivel-edit-config-id').value = el.getAttribute('data-config-id') || '';
        document.getElementById('nivel-edit-nivel-id').value = nivelId;
        document.getElementById('nivel-edit-nivel-nome').textContent = el.getAttribute('data-nivel-nome') || '';
        document.getElementById('nivel-edit-ativo').checked = (el.getAttribute('data-ativo') === '1');
        document.getElementById('nivel-edit-cap-min').value = el.getAttribute('data-cap-min') || '';
        document.getElementById('nivel-edit-cap-max').value = el.getAttribute('data-cap-max') || '';
        document.getElementById('nivel-edit-turno-matutino').checked = (el.getAttribute('data-turno-matutino') === '1');
        document.getElementById('nivel-edit-turno-vespertino').checked = (el.getAttribute('data-turno-vespertino') === '1');
        document.getElementById('nivel-edit-turno-noturno').checked = (el.getAttribute('data-turno-noturno') === '1');
        document.getElementById('nivel-edit-turno-integral').checked = (el.getAttribute('data-turno-integral') === '1');
        document.getElementById('nivel-edit-ch-semanal').value = el.getAttribute('data-ch-semanal') || '';
        document.getElementById('nivel-edit-num-aulas-dia').value = el.getAttribute('data-num-aulas-dia') || '';
        document.getElementById('nivel-edit-duracao-aula').value = el.getAttribute('data-duracao-aula') || '';
        document.getElementById('nivel-edit-idade-minima').value = el.getAttribute('data-idade-minima') || '';
        document.getElementById('nivel-edit-idade-maxima').value = el.getAttribute('data-idade-maxima') || '';
        document.getElementById('nivel-edit-observacoes').value = el.getAttribute('data-observacoes') || '';
        document.getElementById('nivel-edit-errors').textContent = '';
        openXModal('modal-nivel-edit');
    }
    async function submitNivelEdit() {
        const nivelId = document.getElementById('nivel-edit-nivel-id').value;
        const payload = {
            nivel_ensino_id: nivelId,
            ativo: document.getElementById('nivel-edit-ativo').checked ? 1 : 0,
            capacidade_minima_turma: (function(){ const v = document.getElementById('nivel-edit-cap-min').value; return v === '' ? null : parseInt(v, 10); })(),
            capacidade_maxima_turma: (function(){ const v = document.getElementById('nivel-edit-cap-max').value; return v === '' ? null : parseInt(v, 10); })(),
            turno_matutino: document.getElementById('nivel-edit-turno-matutino').checked ? 1 : 0,
            turno_vespertino: document.getElementById('nivel-edit-turno-vespertino').checked ? 1 : 0,
            turno_noturno: document.getElementById('nivel-edit-turno-noturno').checked ? 1 : 0,
            turno_integral: document.getElementById('nivel-edit-turno-integral').checked ? 1 : 0,
            carga_horaria_semanal: (function(){ const v = document.getElementById('nivel-edit-ch-semanal').value; return v === '' ? null : parseInt(v, 10); })(),
            numero_aulas_dia: (function(){ const v = document.getElementById('nivel-edit-num-aulas-dia').value; return v === '' ? null : parseInt(v, 10); })(),
            duracao_aula_minutos: (function(){ const v = document.getElementById('nivel-edit-duracao-aula').value; return v === '' ? null : parseInt(v, 10); })(),
            idade_minima: (function(){ const v = document.getElementById('nivel-edit-idade-minima').value; return v === '' ? null : parseInt(v, 10); })(),
            idade_maxima: (function(){ const v = document.getElementById('nivel-edit-idade-maxima').value; return v === '' ? null : parseInt(v, 10); })(),
            observacoes: document.getElementById('nivel-edit-observacoes').value || null,
        };
        const url = "{{ route('admin.configuracao-educacional.store-nivel', ['escola' => $escola->id]) }}";
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },
            body: JSON.stringify(payload)
        });
        if (!res.ok) {
            const txt = await res.text();
            document.getElementById('nivel-edit-errors').textContent = 'Erro ao salvar: ' + (txt || res.status);
            return;
        }
        // O backend redireciona; recarregar para refletir mudanças
        closeXModal('modal-nivel-edit');
        window.location.reload();
    }

    function openNivelToggleModal(id, nome, ativo) {
        const next = ativo ? 0 : 1;
        document.getElementById('nivel-toggle-nivel-id').value = id;
        document.getElementById('nivel-toggle-next').value = next;
        document.getElementById('nivel-toggle-nome').textContent = nome || '';
        document.getElementById('nivel-toggle-action').textContent = ativo ? 'inativar' : 'ativar';
        document.getElementById('nivel-toggle-errors').textContent = '';
        openXModal('modal-nivel-toggle');
    }
    async function submitNivelToggle() {
        const id = document.getElementById('nivel-toggle-nivel-id').value;
        const next = document.getElementById('nivel-toggle-next').value;
        const url = "{{ route('admin.configuracao-educacional.store-nivel', ['escola' => $escola->id]) }}";
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },
            body: JSON.stringify({
                nivel_ensino_id: id,
                ativo: parseInt(next, 10)
            })
        });
        if (!res.ok) {
            const txt = await res.text();
            document.getElementById('nivel-toggle-errors').textContent = 'Erro ao alterar status: ' + (txt || res.status);
            return;
        }
        closeXModal('modal-nivel-toggle');
        window.location.reload();
    }

    // ===== Modalidades (AJAX + x-modal) =====
    function _findModalidadeElementById(modalidadeId) {
        let el = document.querySelector(`#modalidades-tbody tr[data-modalidade-id="${modalidadeId}"]`);
        if (!el) {
            el = document.querySelector(`#modalidades-mobile-list div[data-modalidade-id="${modalidadeId}"]`);
        }
        return el;
    }
    function openModalidadeEditModal(modalidadeId) {
        const el = _findModalidadeElementById(modalidadeId);
        if (!el) return;
        document.getElementById('modalidade-edit-id').value = modalidadeId;
        document.getElementById('modalidade-edit-nome').textContent = el.getAttribute('data-modalidade-nome') || '';
        document.getElementById('modalidade-edit-ativo').checked = (el.getAttribute('data-ativo') === '1');
        document.getElementById('modalidade-edit-cap-min').value = el.getAttribute('data-cap-min') || '';
        document.getElementById('modalidade-edit-cap-max').value = el.getAttribute('data-cap-max') || '';
        document.getElementById('modalidade-edit-turno-matutino').checked = (el.getAttribute('data-turno-matutino') === '1');
        document.getElementById('modalidade-edit-turno-vespertino').checked = (el.getAttribute('data-turno-vespertino') === '1');
        document.getElementById('modalidade-edit-turno-noturno').checked = (el.getAttribute('data-turno-noturno') === '1');
        document.getElementById('modalidade-edit-turno-integral').checked = (el.getAttribute('data-turno-integral') === '1');
        document.getElementById('modalidade-edit-observacoes').value = el.getAttribute('data-observacoes') || '';
        document.getElementById('modalidade-edit-errors').textContent = '';
        openXModal('modal-modalidade-edit');
    }
    async function submitModalidadeEdit() {
        const modalidadeId = document.getElementById('modalidade-edit-id').value;
        const payload = {
            modalidade_ensino_id: modalidadeId,
            ativo: document.getElementById('modalidade-edit-ativo').checked ? 1 : 0,
            capacidade_minima_turma: (function(){ const v = document.getElementById('modalidade-edit-cap-min').value; return v === '' ? null : parseInt(v, 10); })(),
            capacidade_maxima_turma: (function(){ const v = document.getElementById('modalidade-edit-cap-max').value; return v === '' ? null : parseInt(v, 10); })(),
            turno_matutino: document.getElementById('modalidade-edit-turno-matutino').checked ? 1 : 0,
            turno_vespertino: document.getElementById('modalidade-edit-turno-vespertino').checked ? 1 : 0,
            turno_noturno: document.getElementById('modalidade-edit-turno-noturno').checked ? 1 : 0,
            turno_integral: document.getElementById('modalidade-edit-turno-integral').checked ? 1 : 0,
            observacoes: document.getElementById('modalidade-edit-observacoes').value || null,
        };
        const url = "{{ route('admin.configuracao-educacional.store-modalidade', ['escola' => $escola->id]) }}";
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },
            body: JSON.stringify(payload)
        });
        if (!res.ok) {
            const txt = await res.text();
            document.getElementById('modalidade-edit-errors').textContent = 'Erro ao salvar: ' + (txt || res.status);
            return;
        }
        closeXModal('modal-modalidade-edit');
        window.location.reload();
    }
    function openModalidadeToggleModal(id, nome, ativo) {
        const next = ativo ? 0 : 1;
        document.getElementById('modalidade-toggle-id').value = id;
        document.getElementById('modalidade-toggle-next').value = next;
        document.getElementById('modalidade-toggle-nome').textContent = nome || '';
        document.getElementById('modalidade-toggle-action').textContent = ativo ? 'inativar' : 'ativar';
        document.getElementById('modalidade-toggle-errors').textContent = '';
        openXModal('modal-modalidade-toggle');
    }
    async function submitModalidadeToggle() {
        const id = document.getElementById('modalidade-toggle-id').value;
        const next = document.getElementById('modalidade-toggle-next').value;
        const url = "{{ route('admin.configuracao-educacional.store-modalidade', ['escola' => $escola->id]) }}";
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },
            body: JSON.stringify({
                modalidade_ensino_id: id,
                ativo: parseInt(next, 10)
            })
        });
        if (!res.ok) {
            const txt = await res.text();
            document.getElementById('modalidade-toggle-errors').textContent = 'Erro ao alterar status: ' + (txt || res.status);
            return;
        }
        closeXModal('modal-modalidade-toggle');
        window.location.reload();
    }
    function openModalidadeDeleteModal(configId, nome) {
        document.getElementById('modalidade-delete-config-id').value = configId;
        document.getElementById('modalidade-delete-nome').textContent = nome || '';
        document.getElementById('modalidade-delete-errors').textContent = '';
        openXModal('modal-modalidade-delete');
    }
    async function submitModalidadeDelete() {
        const configId = document.getElementById('modalidade-delete-config-id').value;
        const url = `/admin/configuracao-educacional/{{ $escola->id }}/modalidade/${configId}`;
        const res = await fetch(url, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            }
        });
        if (!res.ok) {
            const txt = await res.text();
            document.getElementById('modalidade-delete-errors').textContent = 'Erro ao excluir: ' + (txt || res.status);
            return;
        }
        closeXModal('modal-modalidade-delete');
        window.location.reload();
    }


    // Inicialização
    document.addEventListener('DOMContentLoaded', () => {
        showTab('tab-modalidades');
        loadDisciplinas();
    });

    // ===== Turnos (AJAX + x-modal) =====
    const TURNOS_LIST_URL = "{{ route('admin.turnos.listar') }}";
    const TURNOS_BASE_URL = "{{ url('/admin/turnos') }}";
    const TEMPOSLOTS_INDEX_URL_TMPL = "{{ route('admin.turnos.tempo-slots.index', ['turno' => '__ID__']) }}";
    const TEMPOSLOTS_STORE_URL_TMPL = "{{ route('admin.turnos.tempo-slots.store', ['turno' => '__ID__']) }}";

    function _tempoSlotsUrl(tmpl, turnoId) {
        return (tmpl || '').replace('__ID__', String(turnoId));
    }

    function openTurnoCreateModal() {
        document.getElementById('turno-create-nome').value = '';
        document.getElementById('turno-create-codigo').value = '';
        document.getElementById('turno-create-inicio').value = '';
        document.getElementById('turno-create-fim').value = '';
        document.getElementById('turno-create-descricao').value = '';
        document.getElementById('turno-create-ordem').value = '';
        document.getElementById('turno-create-ativo').checked = true;
        document.getElementById('turno-create-errors').textContent = '';
        openXModal('modal-turno-create');
    }
    async function submitTurnoCreate() {
        const payload = {
            nome: document.getElementById('turno-create-nome').value,
            codigo: document.getElementById('turno-create-codigo').value || null,
            hora_inicio: document.getElementById('turno-create-inicio').value,
            hora_fim: document.getElementById('turno-create-fim').value,
            descricao: document.getElementById('turno-create-descricao').value || null,
            ordem: (function(){ const v = document.getElementById('turno-create-ordem').value; return v === '' ? null : parseInt(v, 10); })(),
            ativo: document.getElementById('turno-create-ativo').checked ? 1 : 0,
        };
        const url = TURNOS_BASE_URL;
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },
            body: JSON.stringify(payload)
        });
        if (!res.ok) {
            const txt = await res.text();
            document.getElementById('turno-create-errors').textContent = 'Erro ao salvar: ' + (txt || res.status);
            return;
        }
        const data = await res.json();
        if (!data.success) {
            document.getElementById('turno-create-errors').textContent = data.message || 'Falha ao criar turno';
            return;
        }
        closeXModal('modal-turno-create');
        await refreshTurnos();
    }

    function openTurnoEditModal(id) {
        let el = document.querySelector(`#turnos-tbody tr[data-id="${id}"]`);
        if (!el) {
            el = document.querySelector(`#turnos-mobile-list div[data-id="${id}"]`);
        }
        if (!el) return;
        document.getElementById('turno-edit-id').value = id;
        document.getElementById('turno-edit-nome').value = el.getAttribute('data-nome') || '';
        document.getElementById('turno-edit-codigo').value = el.getAttribute('data-codigo') || '';
        document.getElementById('turno-edit-inicio').value = el.getAttribute('data-inicio') || '';
        document.getElementById('turno-edit-fim').value = el.getAttribute('data-fim') || '';
        document.getElementById('turno-edit-descricao').value = el.getAttribute('data-descricao') || '';
        const ordem = el.getAttribute('data-ordem');
        document.getElementById('turno-edit-ordem').value = ordem === null ? '' : ordem;
        document.getElementById('turno-edit-ativo').checked = (el.getAttribute('data-ativo') === '1');
        document.getElementById('turno-edit-errors').textContent = '';
        openXModal('modal-turno-edit');
    }
    async function submitTurnoEdit() {
        const id = document.getElementById('turno-edit-id').value;
        const payload = {
            nome: document.getElementById('turno-edit-nome').value,
            codigo: document.getElementById('turno-edit-codigo').value || null,
            hora_inicio: document.getElementById('turno-edit-inicio').value,
            hora_fim: document.getElementById('turno-edit-fim').value,
            descricao: document.getElementById('turno-edit-descricao').value || null,
            ordem: (function(){ const v = document.getElementById('turno-edit-ordem').value; return v === '' ? null : parseInt(v, 10); })(),
            ativo: document.getElementById('turno-edit-ativo').checked ? 1 : 0,
        };
        const url = `${TURNOS_BASE_URL}/${id}`;
        const res = await fetch(url, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },
            body: JSON.stringify(payload)
        });
        const data = await res.json().catch(() => ({ success: false }));
        if (!res.ok || !data.success) {
            document.getElementById('turno-edit-errors').textContent = (data && data.message) ? data.message : 'Erro ao atualizar turno';
            return;
        }
        closeXModal('modal-turno-edit');
        await refreshTurnos();
    }

    async function openTurnoTempoModal(turnoId) {
        const container = document.getElementById('turno-tempo-content');
        container.innerHTML = '<div class="text-sm text-gray-600">Carregando...</div>';
        openXModal('modal-turno-tempo');
        try {
            const url = _tempoSlotsUrl(TEMPOSLOTS_INDEX_URL_TMPL, turnoId);
            const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
            const data = await res.json();
            if (!data.success) throw new Error('Falha ao carregar slots');
            renderTempoResumo(container, data);
        } catch (e) {
            container.innerHTML = '<div class="text-sm text-red-600">Erro ao carregar tempo do turno.</div>';
        }
    }

    let CURRENT_TURNO_ID = null;
    async function openTurnoSlotsModal(turnoId) {
        CURRENT_TURNO_ID = turnoId;
        const container = document.getElementById('turno-slots-content');
        const tituloTurnoEl = document.getElementById('turno-slots-turno-nome');
        container.innerHTML = '<div class="text-sm text-gray-600">Carregando...</div>';
        openXModal('modal-turno-slots');
        try {
            const url = _tempoSlotsUrl(TEMPOSLOTS_INDEX_URL_TMPL, turnoId);
            const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
            const data = await res.json();
            if (!data.success) throw new Error('Falha ao carregar slots');
            tituloTurnoEl.textContent = `Turno: ${data.turno?.nome || ''}`;
            renderTempoSlotsList(container, data);
            wireCreateSlotEvents(turnoId);
        } catch (e) {
            container.innerHTML = '<div class="text-sm text-red-600">Erro ao carregar slots do turno.</div>';
        }
    }

    function renderTempoResumo(container, data) {
        const slots = (data && data.slots) ? data.slots : [];
        if (!slots.length) {
            container.innerHTML = '<div class="text-sm text-gray-600">Nenhum slot configurado para este turno.</div>';
            return;
        }
        const wrap = document.createElement('div');
        wrap.className = 'space-y-2';
        slots.forEach(s => {
            const item = document.createElement('div');
            item.className = 'flex items-center justify-between p-2 border rounded';
            item.innerHTML = `
                <div>
                    <div class="text-sm font-medium">${s.nome} <span class="text-xs text-gray-500">(${s.tipo_formatado})</span></div>
                    <div class="text-xs text-gray-600">${s.horario_formatado} • Duração: ${s.duracao_minutos} min • Ordem: ${s.ordem}</div>
                    <div class="text-xs text-gray-500">${s.descricao || ''}</div>
                </div>
                <div>${s.ativo ? '<span class="px-2 py-0.5 rounded bg-green-100 text-green-800 text-xs">Ativo</span>' : '<span class="px-2 py-0.5 rounded bg-gray-100 text-gray-800 text-xs">Inativo</span>'}</div>
            `;
            wrap.appendChild(item);
        });
        container.innerHTML = '';
        container.appendChild(wrap);
    }

    function renderTempoSlotsList(container, data) {
        const slots = (data && data.slots) ? data.slots : [];
        const table = document.createElement('table');
        table.className = 'min-w-full divide-y divide-gray-200';
        table.innerHTML = `
            <thead>
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Nome</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Tipo</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Horário</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Duração</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Ordem</th>
                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200" id="tempo-slots-tbody"></tbody>
        `;
        container.innerHTML = '';
        container.appendChild(table);
        const tbody = table.querySelector('#tempo-slots-tbody');
        if (!slots.length) {
            tbody.innerHTML = '<tr><td colspan="6" class="px-4 py-3 text-sm text-gray-500">Nenhum slot configurado.</td></tr>';
            return;
        }
        slots.forEach(s => {
            const tr = document.createElement('tr');
            tr.setAttribute('data-id', s.id);
            tr.innerHTML = `
                <td class="px-4 py-2">${s.nome}</td>
                <td class="px-4 py-2">${s.tipo_formatado}</td>
                <td class="px-4 py-2">${s.hora_inicio} – ${s.hora_fim}</td>
                <td class="px-4 py-2">${s.duracao_minutos} min</td>
                <td class="px-4 py-2">${s.ordem}</td>
                <td class="px-4 py-2 text-right space-x-2">
                    <button type="button" class="px-2 py-1 text-xs bg-indigo-600 text-white rounded hover:bg-indigo-700" data-action="edit">Editar</button>
                    <button type="button" class="px-2 py-1 text-xs ${s.ativo ? 'bg-orange-500 hover:bg-orange-600' : 'bg-green-600 hover:bg-green-700'} text-white rounded" data-action="toggle">${s.ativo ? 'Inativar' : 'Ativar'}</button>
                    <button type="button" class="px-2 py-1 text-xs bg-red-600 text-white rounded hover:bg-red-700" data-action="delete">Excluir</button>
                </td>
            `;
            // Guardar payload atual para updates rápidos
            tr.dataset.payload = JSON.stringify(s);
            tbody.appendChild(tr);
        });
        tbody.addEventListener('click', async (ev) => {
            const btn = ev.target.closest('button');
            if (!btn) return;
            const tr = btn.closest('tr');
            const slot = JSON.parse(tr.dataset.payload || '{}');
            if (btn.dataset.action === 'delete') {
                await deleteTempoSlot(slot);
                await openTurnoSlotsModal(CURRENT_TURNO_ID);
            } else if (btn.dataset.action === 'toggle') {
                await toggleTempoSlot(slot);
                await openTurnoSlotsModal(CURRENT_TURNO_ID);
            } else if (btn.dataset.action === 'edit') {
                openInlineEditSlot(tr, slot);
            }
        });
    }

    function wireCreateSlotEvents(turnoId) {
        const openBtn = document.getElementById('btn-open-create-slot');
        const cancelBtn = document.getElementById('btn-cancel-create-slot');
        const submitBtn = document.getElementById('btn-submit-create-slot');
        const form = document.getElementById('turno-slot-create-form');
        openBtn.onclick = () => { form.classList.remove('hidden'); };
        cancelBtn.onclick = () => { form.classList.add('hidden'); resetCreateSlotForm(); };
        submitBtn.onclick = async () => {
            await submitCreateTempoSlot(turnoId);
            form.classList.add('hidden');
            resetCreateSlotForm();
            await openTurnoSlotsModal(turnoId);
        };
    }

    function resetCreateSlotForm() {
        document.getElementById('slot-create-nome').value = '';
        document.getElementById('slot-create-tipo').value = 'aula';
        document.getElementById('slot-create-inicio').value = '';
        document.getElementById('slot-create-fim').value = '';
        document.getElementById('slot-create-ordem').value = '';
        document.getElementById('slot-create-duracao').value = '';
        document.getElementById('slot-create-descricao').value = '';
        document.getElementById('slot-create-ativo').checked = true;
        document.getElementById('slot-create-errors').textContent = '';
    }

    async function submitCreateTempoSlot(turnoId) {
        const payload = {
            nome: document.getElementById('slot-create-nome').value,
            tipo: document.getElementById('slot-create-tipo').value,
            hora_inicio: document.getElementById('slot-create-inicio').value,
            hora_fim: document.getElementById('slot-create-fim').value,
            ordem: (function(){ const v = document.getElementById('slot-create-ordem').value; return v === '' ? null : parseInt(v, 10); })(),
            duracao_minutos: (function(){ const v = document.getElementById('slot-create-duracao').value; return v === '' ? null : parseInt(v, 10); })(),
            descricao: document.getElementById('slot-create-descricao').value || null,
            ativo: document.getElementById('slot-create-ativo').checked ? 1 : 0,
        };
        const url = _tempoSlotsUrl(TEMPOSLOTS_STORE_URL_TMPL, turnoId);
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },
            body: JSON.stringify(payload)
        });
        if (!res.ok) {
            const txt = await res.text();
            document.getElementById('slot-create-errors').textContent = 'Erro ao salvar: ' + (txt || res.status);
            return;
        }
        const data = await res.json().catch(() => ({ success: false }));
        if (!data.success) {
            document.getElementById('slot-create-errors').textContent = data.message || 'Falha ao criar slot';
            return;
        }
    }

    async function deleteTempoSlot(slot) {
        const res = await fetch(slot.delete_url, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            }
        });
        if (!res.ok) {
            alert('Erro ao excluir slot');
        }
    }

    async function toggleTempoSlot(slot) {
        const payload = {
            nome: slot.nome,
            tipo: slot.tipo,
            hora_inicio: slot.hora_inicio,
            hora_fim: slot.hora_fim,
            ordem: slot.ordem,
            duracao_minutos: slot.duracao_minutos,
            descricao: slot.descricao,
            ativo: slot.ativo ? 0 : 1,
        };
        const res = await fetch(slot.update_url, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },
            body: JSON.stringify(payload)
        });
        if (!res.ok) {
            alert('Erro ao atualizar status do slot');
        }
    }

    function openInlineEditSlot(tr, slot) {
        // Se já existe editor aberto, remove
        const nextRow = tr.nextElementSibling;
        if (nextRow && nextRow.classList.contains('inline-edit-row')) {
            nextRow.remove();
        }
        const editTr = document.createElement('tr');
        editTr.className = 'inline-edit-row';
        editTr.innerHTML = `
            <td colspan="6" class="px-4 py-3 bg-gray-50">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nome</label>
                        <input type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" id="slot-edit-nome" value="${slot.nome}" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tipo</label>
                        <select class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" id="slot-edit-tipo">
                            <option value="aula" ${slot.tipo==='aula'?'selected':''}>Aula</option>
                            <option value="intervalo" ${slot.tipo==='intervalo'?'selected':''}>Intervalo</option>
                            <option value="almoco" ${slot.tipo==='almoco'?'selected':''}>Almoço</option>
                            <option value="outro" ${slot.tipo==='outro'?'selected':''}>Outro</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Hora Início</label>
                        <input type="time" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" id="slot-edit-inicio" value="${slot.hora_inicio || ''}" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Hora Fim</label>
                        <input type="time" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" id="slot-edit-fim" value="${slot.hora_fim || ''}" />
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4 mt-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Ordem</label>
                        <input type="number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" id="slot-edit-ordem" value="${slot.ordem}" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Duração (min)</label>
                        <input type="number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" id="slot-edit-duracao" value="${slot.duracao_minutos || ''}" />
                    </div>
                </div>
                <div class="mt-3">
                    <label class="block text-sm font-medium text-gray-700">Descrição</label>
                    <textarea rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" id="slot-edit-descricao">${slot.descricao || ''}</textarea>
                </div>
                <div class="flex items-center gap-2 mt-2">
                    <input type="checkbox" id="slot-edit-ativo" ${slot.ativo ? 'checked' : ''} />
                    <label class="text-sm text-gray-700">Ativo</label>
                </div>
                <div class="text-sm text-red-600 mt-2" id="slot-edit-errors"></div>
                <div class="flex justify-end gap-2 mt-3">
                    <button type="button" class="px-3 py-1.5 text-xs bg-white border border-gray-300 rounded hover:bg-gray-50" id="slot-edit-cancel">Cancelar</button>
                    <button type="button" class="px-3 py-1.5 text-xs text-white bg-indigo-600 rounded hover:bg-indigo-700" id="slot-edit-save">Salvar</button>
                </div>
            </td>
        `;
        tr.after(editTr);
        editTr.querySelector('#slot-edit-cancel').onclick = () => editTr.remove();
        editTr.querySelector('#slot-edit-save').onclick = async () => {
            const payload = {
                nome: editTr.querySelector('#slot-edit-nome').value,
                tipo: editTr.querySelector('#slot-edit-tipo').value,
                hora_inicio: editTr.querySelector('#slot-edit-inicio').value,
                hora_fim: editTr.querySelector('#slot-edit-fim').value,
                ordem: parseInt(editTr.querySelector('#slot-edit-ordem').value, 10),
                duracao_minutos: (function(){ const v = editTr.querySelector('#slot-edit-duracao').value; return v === '' ? null : parseInt(v, 10); })(),
                descricao: editTr.querySelector('#slot-edit-descricao').value || null,
                ativo: editTr.querySelector('#slot-edit-ativo').checked ? 1 : 0,
            };
            const res = await fetch(slot.update_url, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                },
                body: JSON.stringify(payload)
            });
            const data = await res.json().catch(() => ({ success: false }));
            if (!res.ok || !data.success) {
                editTr.querySelector('#slot-edit-errors').textContent = (data && data.message) ? data.message : 'Erro ao atualizar slot';
                return;
            }
            await openTurnoSlotsModal(CURRENT_TURNO_ID);
        };
    }

    function openTurnoToggleModal(id, nome, ativo) {
        document.getElementById('turno-toggle-id').value = id;
        const next = ativo ? 0 : 1;
        document.getElementById('turno-toggle-next').value = next;
        document.getElementById('turno-toggle-nome').textContent = nome;
        document.getElementById('turno-toggle-action').textContent = ativo ? 'inativar' : 'ativar';
        document.getElementById('turno-toggle-errors').textContent = '';
        openXModal('modal-turno-toggle');
    }
    async function submitTurnoToggle() {
        const id = document.getElementById('turno-toggle-id').value;
        const url = `${TURNOS_BASE_URL}/${id}/toggle-status`;
        const res = await fetch(url, {
            method: 'PATCH',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            }
        });
        const data = await res.json().catch(() => ({ success: false }));
        if (!res.ok || !data.success) {
            document.getElementById('turno-toggle-errors').textContent = (data && data.message) ? data.message : 'Erro ao alterar status';
            return;
        }
        closeXModal('modal-turno-toggle');
        await refreshTurnos();
    }

    async function refreshTurnos() {
        try {
            const res = await fetch(`${TURNOS_LIST_URL}?all=1`, { headers: { 'Accept': 'application/json' } });
            const list = await res.json();
            const tbody = document.getElementById('turnos-tbody');
            const mobile = document.getElementById('turnos-mobile-list');
            tbody.innerHTML = '';
            if (mobile) mobile.innerHTML = '';
            (list || []).forEach(t => {
                const tr = document.createElement('tr');
                tr.setAttribute('data-id', t.id);
                tr.setAttribute('data-nome', t.nome || '');
                tr.setAttribute('data-codigo', t.codigo || '');
                tr.setAttribute('data-inicio', t.hora_inicio || '');
                tr.setAttribute('data-fim', t.hora_fim || '');
                tr.setAttribute('data-ativo', t.ativo ? '1' : '0');
                tr.setAttribute('data-descricao', t.descricao || '');
                tr.setAttribute('data-ordem', (typeof t.ordem !== 'undefined' && t.ordem !== null) ? t.ordem : '');
                tr.innerHTML = `
                    <td class="px-4 py-2">${t.nome || ''}</td>
                    <td class="px-4 py-2">${t.codigo || ''}</td>
                    <td class="px-4 py-2">${(t.hora_inicio || '')} – ${(t.hora_fim || '')}</td>
                    <td class="px-4 py-2">${t.ativo ? '<span class="inline-flex items-center px-2 py-0.5 rounded bg-green-100 text-green-800 text-xs font-medium">Ativo</span>' : '<span class="inline-flex items-center px-2 py-0.5 rounded bg-gray-100 text-gray-800 text-xs font-medium">Inativo</span>'}</td>
                    <td class="px-4 py-2 text-right space-x-2">
                        <button type="button" onclick="openTurnoEditModal(${t.id})" class="px-3 py-1.5 text-xs bg-indigo-600 text-white rounded hover:bg-indigo-700">Editar</button>
                        <button type="button" onclick="openTurnoTempoModal(${t.id})" class="px-3 py-1.5 text-xs bg-blue-600 text-white rounded hover:bg-blue-700">Tempo</button>
                        <button type="button" onclick="openTurnoSlotsModal(${t.id})" class="px-3 py-1.5 text-xs bg-teal-600 text-white rounded hover:bg-teal-700">Slots</button>
                        <button type="button" class="px-3 py-1.5 text-xs rounded text-white ${t.ativo ? 'bg-orange-500 hover:bg-orange-600' : 'bg-green-600 hover:bg-green-700'}" onclick="openTurnoToggleModal(${t.id}, '${(t.nome || '').replace(/'/g, "&#39;")}', ${t.ativo ? 'true' : 'false'})">${t.ativo ? 'Inativar' : 'Ativar'}</button>
                    </td>
                `;
                tbody.appendChild(tr);

                // Renderização mobile
                if (mobile) {
                    const card = document.createElement('div');
                    card.className = 'p-3 border rounded-md';
                    card.setAttribute('data-id', t.id);
                    card.setAttribute('data-nome', t.nome || '');
                    card.setAttribute('data-codigo', t.codigo || '');
                    card.setAttribute('data-inicio', t.hora_inicio || '');
                    card.setAttribute('data-fim', t.hora_fim || '');
                    card.setAttribute('data-ativo', t.ativo ? '1' : '0');
                    card.setAttribute('data-descricao', t.descricao || '');
                    card.setAttribute('data-ordem', (typeof t.ordem !== 'undefined' && t.ordem !== null) ? t.ordem : '');
                    const nomeEsc = (t.nome || '').replace(/'/g, "&#39;");
                    card.innerHTML = `
                        <div class="flex items-start justify-between">
                            <div>
                                <div class="text-sm font-semibold text-gray-900">${t.nome || ''}</div>
                                <div class="text-xs text-gray-600">Código: ${t.codigo || ''}</div>
                                <div class="text-xs text-gray-600">${(t.hora_inicio || '')} – ${(t.hora_fim || '')}</div>
                            </div>
                            <div>
                                ${t.ativo ? '<span class="inline-flex items-center px-2 py-0.5 rounded bg-green-100 text-green-800 text-xs font-medium">Ativo</span>' : '<span class="inline-flex items-center px-2 py-0.5 rounded bg-gray-100 text-gray-800 text-xs font-medium">Inativo</span>'}
                            </div>
                        </div>
                        <div class="mt-2 flex flex-wrap gap-2">
                            <button type="button" onclick="openTurnoEditModal(${t.id})" class="px-2 py-1 text-xs bg-indigo-600 text-white rounded hover:bg-indigo-700">Editar</button>
                            <button type="button" onclick="openTurnoTempoModal(${t.id})" class="px-2 py-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-700">Tempo</button>
                            <button type="button" onclick="openTurnoSlotsModal(${t.id})" class="px-2 py-1 text-xs bg-teal-600 text-white rounded hover:bg-teal-700">Slots</button>
                            <button type="button" class="px-2 py-1 text-xs rounded text-white ${t.ativo ? 'bg-orange-500 hover:bg-orange-600' : 'bg-green-600 hover:bg-green-700'}" onclick="openTurnoToggleModal(${t.id}, '${nomeEsc}', ${t.ativo ? 'true' : 'false'})">${t.ativo ? 'Inativar' : 'Ativar'}</button>
                        </div>
                    `;
                    mobile.appendChild(card);
                }
            });
        } catch (e) {
            console.error('Erro ao carregar turnos', e);
        }
    }
</script>