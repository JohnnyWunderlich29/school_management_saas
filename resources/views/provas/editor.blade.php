@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8" x-data="provaEditor()">
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">{{ isset($prova) ? 'Editar Prova' : 'Criar Nova Prova' }}</h1>
                <p class="text-gray-600 mt-1">Configure o cabeçalho e adicione as questões abaixo.</p>
            </div>
            <div class="flex space-x-3 w-full md:w-auto">
                <button type="button" @click="save('rascunho')"
                    class="flex-1 md:flex-none bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-6 rounded-lg transition">
                    Salvar Rascunho
                </button>
                <button type="button" @click="save('publicada')"
                    class="flex-1 md:flex-none bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-6 rounded-lg shadow-md transition">
                    Publicar Prova
                </button>
            </div>
        </div>

        <form id="provaForm" action="{{ isset($prova) ? route('provas.update', $prova) : route('provas.store') }}"
            method="POST" enctype="multipart/form-data">
            @csrf
            @if (isset($prova))
                @method('PUT')
            @endif
            <input type="hidden" name="status" x-model="status">

            <!-- Informações Básicas -->
            <div class="bg-white rounded-xl shadow-md p-6 mb-8 border border-gray-100">
                <h2 class="text-xl font-semibold text-gray-800 mb-6 flex items-center border-b pb-4">
                    <i class="fas fa-info-circle mr-2 text-indigo-500"></i> Informações Gerais
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Título da Prova</label>
                        <input type="text" name="titulo" x-model="titulo"
                            class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm"
                            placeholder="Ex: Avaliação Bimestral de Matemática">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Turma</label>
                        <select name="turma_id" x-model="turma_id" @change="fetchSlots()"
                            class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm text-sm">
                            <option value="">Selecione a turma</option>
                            @foreach ($turmas as $turma)
                                <option value="{{ $turma->id }}">{{ $turma->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Disciplina</label>
                        <select name="disciplina_id" x-model="disciplina_id" @change="fetchSlots()"
                            class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm text-sm">
                            <option value="">Selecione a disciplina</option>
                            @foreach ($disciplinas as $disciplina)
                                <option value="{{ $disciplina->id }}">{{ $disciplina->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Data de Aplicação</label>
                        <input type="date" name="data_aplicacao" x-model="data_aplicacao"
                            class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Horário (Slot)</label>
                        <select name="grade_aula_id" x-model="grade_aula_id"
                            class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm text-sm">
                            <option value="">Selecione o horário</option>
                            <template x-for="slot in slots" :key="slot.id">
                                <option :value="slot.id"
                                    x-text="slot.dia_semana_formatado + ' - ' + slot.tempo_slot.nome"></option>
                            </template>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Instruções / Descrição</label>
                        <textarea name="descricao" x-model="descricao" rows="2"
                            class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm"
                            placeholder="Instruções para os alunos..."></textarea>
                    </div>
                </div>
            </div>

            <!-- Questões -->
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                    <i class="fas fa-tasks mr-2 text-indigo-500"></i> Questões
                </h2>

                <div class="space-y-6">
                    <template x-for="(questao, index) in questoes" :key="questao.uid">
                        <div
                            class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden relative group transition duration-300 hover:border-indigo-300">
                            <div class="bg-gray-50 px-6 py-3 border-b border-gray-100 flex justify-between items-center">
                                <span class="font-bold text-gray-700" x-text="'Questão ' + (index + 1)"></span>
                                <div class="flex items-center space-x-3">
                                    <span
                                        class="text-xs font-semibold uppercase px-2 py-1 rounded bg-indigo-100 text-indigo-700"
                                        x-text="questao.tipo === 'multipla_escolha' ? 'Múltipla Escolha' : 'Descritiva'"></span>
                                    <button type="button" @click="removeQuestao(index)"
                                        class="text-red-400 hover:text-red-600 transition p-2">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="p-6">
                                <div class="mb-4">
                                    <input type="hidden" :name="'questoes[' + index + '][tipo]'" :value="questao.tipo">
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Enunciado</label>
                                    <textarea :name="'questoes[' + index + '][enunciado]'" x-model="questao.enunciado" rows="3"
                                        class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm"
                                        placeholder="Escreva a pergunta..."></textarea>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-1">Imagem (Opcional)</label>
                                        <input type="file" :name="'questoes[' + index + '][imagem]'"
                                            class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition border rounded-lg p-1">
                                    </div>
                                    <div class="flex items-end space-x-4">
                                        <div class="flex-1">
                                            <label class="block text-sm font-bold text-gray-700 mb-1">Valor</label>
                                            <input type="number" step="0.1" :name="'questoes[' + index + '][valor]'"
                                                x-model="questao.valor"
                                                class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm"
                                                placeholder="1.0">
                                        </div>
                                        <div class="flex-1 hidden md:block"></div>
                                    </div>
                                </div>

                                <!-- Alternativas se for múltipla escolha -->
                                <template x-if="questao.tipo === 'multipla_escolha'">
                                    <div class="mt-6 border-t border-gray-100 pt-6">
                                        <div class="flex justify-between items-center mb-4">
                                            <h3 class="font-semibold text-gray-800 flex items-center">
                                                <i class="fas fa-list-ol mr-2 text-gray-400"></i> Alternativas
                                            </h3>
                                            <button type="button" @click="addAlternativa(index)"
                                                class="text-xs bg-indigo-50 text-indigo-600 font-bold py-1.5 px-3 rounded-full hover:bg-indigo-100 transition flex items-center border border-indigo-200">
                                                <i class="fas fa-plus mr-1"></i> Adicionar Alternativa
                                            </button>
                                        </div>
                                        <div class="space-y-3">
                                            <template x-for="(alternativa, altIndex) in questao.alternativas"
                                                :key="altIndex">
                                                <div
                                                    class="flex items-center space-x-3 bg-gray-50 p-2 rounded-lg border border-transparent hover:border-indigo-200 transition">
                                                    <div class="flex items-center justify-center">
                                                        <input type="radio"
                                                            :name="'questoes[' + index + '][correta_radio]'"
                                                            :checked="alternativa.correta"
                                                            @change="setCorreta(index, altIndex)"
                                                            class="w-4 h-4 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                                                    </div>
                                                    <input type="text"
                                                        :name="'questoes[' + index + '][alternativas][' + altIndex + '][texto]'"
                                                        x-model="alternativa.texto"
                                                        class="flex-1 rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm text-sm"
                                                        placeholder="Texto da alternativa...">
                                                    <input type="hidden"
                                                        :name="'questoes[' + index + '][alternativas][' + altIndex +
                                                            '][correta]'"
                                                        :value="alternativa.correta ? 1 : 0">
                                                    <button type="button" @click="removeAlternativa(index, altIndex)"
                                                        class="text-gray-400 hover:text-red-500 transition p-1">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Botão Adicionar Questão -->
                <div class="mt-10 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <button type="button" @click="addQuestao('multipla_escolha')"
                        class="flex items-center justify-center px-6 py-4 bg-white border-2 border-dashed border-indigo-300 text-indigo-600 rounded-xl hover:bg-indigo-50 hover:border-indigo-400 transition transform hover:-translate-y-1 group">
                        <div class="bg-indigo-100 group-hover:bg-indigo-200 p-3 rounded-lg mr-4 transition">
                            <i class="fas fa-list-ul text-lg"></i>
                        </div>
                        <div class="text-left">
                            <div class="font-bold text-lg">+ Múltipla Escolha</div>
                            <div class="text-xs text-gray-500">Questão com alternativas únicas</div>
                        </div>
                    </button>
                    <button type="button" @click="addQuestao('descritiva')"
                        class="flex items-center justify-center px-6 py-4 bg-white border-2 border-dashed border-gray-300 text-gray-600 rounded-xl hover:bg-gray-50 hover:border-gray-400 transition transform hover:-translate-y-1 group">
                        <div class="bg-gray-100 group-hover:bg-gray-200 p-3 rounded-lg mr-4 transition">
                            <i class="fas fa-align-left text-lg"></i>
                        </div>
                        <div class="text-left">
                            <div class="font-bold text-lg">+ Descritiva</div>
                            <div class="text-xs text-gray-500">Questão de resposta aberta</div>
                        </div>
                    </button>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            function provaEditor() {
                return {
                    titulo: '{{ isset($prova) ? $prova->titulo : '' }}',
                    turma_id: '{{ isset($prova) ? $prova->turma_id : '' }}',
                    disciplina_id: '{{ isset($prova) ? $prova->disciplina_id : '' }}',
                    data_aplicacao: '{{ isset($prova) ? $prova->data_aplicacao->format('Y-m-d') : '' }}',
                    grade_aula_id: '{{ isset($prova) ? $prova->grade_aula_id : '' }}',
                    descricao: '{{ isset($prova) ? $prova->descricao : '' }}',
                    status: 'rascunho',
                    slots: [],
                    questoes: {!! $questoesJson !!},

                    init() {
                        if (this.turma_id && this.disciplina_id) {
                            this.fetchSlots();
                        }
                    },

                    async fetchSlots() {
                        if (!this.turma_id || !this.disciplina_id) {
                            this.slots = [];
                            return;
                        }
                        try {
                            const response = await fetch(
                                `{{ route('provas.get-slots') }}?turma_id=${this.turma_id}&disciplina_id=${this.disciplina_id}`
                            );
                            const data = await response.json();
                            this.slots = data;

                            // Formatar dia_semana
                            this.slots.forEach(slot => {
                                const dias = {
                                    'segunda': 'Segunda-feira',
                                    'terca': 'Terça-feira',
                                    'quarta': 'Quarta-feira',
                                    'quinta': 'Quinta-feira',
                                    'sexta': 'Sexta-feira',
                                    'sabado': 'Sábado'
                                };
                                slot.dia_semana_formatado = dias[slot.dia_semana] || slot.dia_semana;
                            });
                        } catch (e) {
                            console.error('Erro ao buscar slots:', e);
                        }
                    },

                    addQuestao(tipo) {
                        this.questoes.push({
                            uid: Date.now() + Math.random(),
                            tipo: tipo,
                            enunciado: '',
                            valor: 1.0,
                            alternativas: tipo === 'multipla_escolha' ? [{
                                    texto: '',
                                    correta: true
                                },
                                {
                                    texto: '',
                                    correta: false
                                }
                            ] : []
                        });

                        // Scroll para a nova questão
                        this.$nextTick(() => {
                            window.scrollTo({
                                top: document.body.scrollHeight,
                                behavior: 'smooth'
                            });
                        });
                    },

                    removeQuestao(index) {
                        if (confirm('Deseja remover esta questão?')) {
                            this.questoes.splice(index, 1);
                        }
                    },

                    addAlternativa(qIndex) {
                        this.questoes[qIndex].alternativas.push({
                            texto: '',
                            correta: false
                        });
                    },

                    removeAlternativa(qIndex, altIndex) {
                        if (this.questoes[qIndex].alternativas.length > 2) {
                            this.questoes[qIndex].alternativas.splice(altIndex, 1);
                        } else {
                            alert('Uma questão de múltipla escolha deve ter pelo menos 2 alternativas.');
                        }
                    },

                    setCorreta(qIndex, altIndex) {
                        this.questoes[qIndex].alternativas.forEach((alt, i) => {
                            alt.correta = (i === altIndex);
                        });
                    },

                    save(status) {
                        this.status = status;

                        // Validação simples
                        if (!this.titulo) {
                            alert('Digite o título da prova');
                            return;
                        }
                        if (!this.turma_id) {
                            alert('Selecione uma turma');
                            return;
                        }
                        if (!this.disciplina_id) {
                            alert('Selecione uma disciplina');
                            return;
                        }
                        if (!this.data_aplicacao) {
                            alert('Selecione a data de aplicação');
                            return;
                        }
                        if (this.questoes.length === 0) {
                            alert('Adicione pelo menos uma questão');
                            return;
                        }

                        // Validar se todas as questões tem enunciado
                        let valid = true;
                        this.questoes.forEach((q, i) => {
                            if (!q.enunciado.trim()) {
                                alert(`A questão ${i+1} está sem enunciado.`);
                                valid = false;
                            }
                            if (q.tipo === 'multipla_escolha') {
                                let temTexto = q.alternativas.every(a => a.texto.trim());
                                if (!temTexto) {
                                    alert(`A questão ${i+1} tem alternativas em branco.`);
                                    valid = false;
                                }
                            }
                        });

                        if (valid) {
                            document.getElementById('provaForm').submit();
                        }
                    }
                }
            }
        </script>
    @endpush
@endsection
