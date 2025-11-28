@extends('layouts.app')

@section('content')
    <x-card>
        <div class="flex flex-col mb-6 space-y-4 md:flex-row justify-between md:space-y-0 md:items-center">
            <div>
                <h1 class="text-lg md:text-2xl font-semibold text-gray-900">Histórico do Sistema</h1>
                <p class="mt-1 text-sm text-gray-600">Visualize todas as ações realizadas no sistema</p>
            </div>
        </div>

        <x-collapsible-filter 
            title="Filtros de Histórico" 
            :action="route('historico.index')" 
            :clear-route="route('historico.index')"
        >
            <x-filter-field 
                name="modelo" 
                label="Modelo" 
                type="select"
                empty-option="Todos os modelos"
                :options="[
                    'Escala' => 'Escalas',
                    'Aluno' => 'Alunos', 
                    'Funcionario' => 'Funcionários',
                    'Presenca' => 'Presenças',
                    'Sala' => 'Salas',
                    'Usuario' => 'Usuários',
                    'Cargo' => 'Cargos'
                ]"
            />
            
            <x-filter-field 
                name="acao" 
                label="Ação" 
                type="select"
                empty-option="Todas as ações"
                :options="[
                    'create' => 'Criação',
                    'update' => 'Atualização', 
                    'delete' => 'Exclusão',
                    'criado' => 'Criado',
                    'atualizado' => 'Atualizado',
                    'excluido' => 'Excluído'
                ]"
            />
            
            <x-filter-field 
                name="usuario_id" 
                label="Usuário" 
                type="select"
                empty-option="Todos os usuários"
                :options="$usuarios->pluck('name', 'id')"
            />
            
            <x-date-filter-with-arrows 
                name="data_inicio" 
                label="Data Início" 
                :value="request('data_inicio', now()->startOfMonth()->format('Y-m-d'))" 
                data-fim-name="data_fim"
                :data-fim-value="request('data_fim', now()->endOfMonth()->format('Y-m-d'))" 
            />
        </x-collapsible-filter>

        <!-- Tabela Desktop -->
        <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data/Hora</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuário</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ação</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Modelo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Resumo das Alterações</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($historicos as $historico)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $historico->created_at->format('d/m/Y H:i:s') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $historico->usuario->name ?? 'Sistema' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                    @if($historico->acao == 'criado' || $historico->acao == 'create') bg-green-100 text-green-800
                                    @elseif($historico->acao == 'atualizado' || $historico->acao == 'update') bg-yellow-100 text-yellow-800
                                    @elseif($historico->acao == 'excluido' || $historico->acao == 'delete') bg-red-100 text-red-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ ucfirst($historico->acao) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $historico->modelo }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $historico->modelo_id }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 max-w-xs">
                                @php
                                    $resumo = '';
                                    if ($historico->acao == 'criado' || $historico->acao == 'create') {
                                        if ($historico->dados_novos) {
                                            $dados = is_string($historico->dados_novos) ? json_decode($historico->dados_novos, true) : $historico->dados_novos;
                                            if ($historico->modelo == 'Aluno' && isset($dados['nome'])) {
                                                $resumo = 'Aluno: ' . ($dados['nome'] ?? '') . ' ' . ($dados['sobrenome'] ?? '');
                                            } elseif ($historico->modelo == 'Escala' && isset($dados['data'])) {
                                                $resumo = 'Escala: ' . ($dados['data'] ?? '') . ' - ' . ($dados['hora_inicio'] ?? '') . ' às ' . ($dados['hora_fim'] ?? '');
                                            } elseif ($historico->modelo == 'Funcionario' && isset($dados['nome'])) {
                                                $resumo = 'Funcionário: ' . ($dados['nome'] ?? '') . ' ' . ($dados['sobrenome'] ?? '');
                                            } elseif ($historico->modelo == 'Presenca') {
                                                $aluno = \App\Models\Aluno::find($dados['aluno_id'] ?? null);
                                                $presente = $dados['presente'] ?? false;
                                                $data = $dados['data'] ?? 'N/A';
                                                $resumo = 'Presença: ' . ($aluno->nomeCompleto ?? 'Aluno ID: ' . ($dados['aluno_id'] ?? 'N/A')) . ' - ' . ($presente ? 'Presente' : 'Ausente') . ' em ' . \Carbon\Carbon::parse($data)->format('d/m/Y');
                                            } else {
                                                $resumo = 'Novo registro criado';
                                            }
                                        }
                                    } elseif ($historico->acao == 'atualizado' || $historico->acao == 'update') {
                                        if ($historico->dados_antigos && $historico->dados_novos) {
                                            $antigos = is_string($historico->dados_antigos) ? json_decode($historico->dados_antigos, true) : $historico->dados_antigos;
                                            $novos = is_string($historico->dados_novos) ? json_decode($historico->dados_novos, true) : $historico->dados_novos;
                                            $alteracoes = [];
                                            
                                            foreach ($novos as $campo => $valor) {
                                                if (isset($antigos[$campo]) && $antigos[$campo] != $valor) {
                                                    $alteracoes[] = ucfirst(str_replace('_', ' ', $campo));
                                                }
                                            }
                                            
                                            if (count($alteracoes) > 0) {
                                                $resumo = 'Alterado: ' . implode(', ', array_slice($alteracoes, 0, 3));
                                                if (count($alteracoes) > 3) {
                                                    $resumo .= ' e mais ' . (count($alteracoes) - 3) . ' campo(s)';
                                                }
                                            } else {
                                                $resumo = 'Registro atualizado';
                                            }
                                        }
                                    } elseif ($historico->acao == 'excluido' || $historico->acao == 'delete') {
                                        if ($historico->dados_antigos) {
                                            $dados = is_string($historico->dados_antigos) ? json_decode($historico->dados_antigos, true) : $historico->dados_antigos;
                                            if ($historico->modelo == 'Aluno' && isset($dados['nome'])) {
                                                $resumo = 'Excluído: ' . ($dados['nome'] ?? '') . ' ' . ($dados['sobrenome'] ?? '');
                                            } elseif ($historico->modelo == 'Escala' && isset($dados['data'])) {
                                                $resumo = 'Excluído: Escala de ' . ($dados['data'] ?? '');
                                            } elseif ($historico->modelo == 'Funcionario' && isset($dados['nome'])) {
                                                $resumo = 'Excluído: ' . ($dados['nome'] ?? '') . ' ' . ($dados['sobrenome'] ?? '');
                                            } elseif ($historico->modelo == 'Presenca') {
                                                $aluno = \App\Models\Aluno::find($dados['aluno_id'] ?? null);
                                                $data = $dados['data'] ?? 'N/A';
                                                $resumo = 'Excluído: Presença de ' . ($aluno->nomeCompleto ?? 'Aluno ID: ' . ($dados['aluno_id'] ?? 'N/A')) . ' em ' . \Carbon\Carbon::parse($data)->format('d/m/Y');
                                            } else {
                                                $resumo = 'Registro excluído';
                                            }
                                        }
                                    }
                                @endphp
                                <div class="truncate" title="{{ $resumo }}">
                                    {{ $resumo ?: 'Sem detalhes disponíveis' }}
                                </div>
                                @if($historico->observacoes)
                                    <div class="text-xs text-gray-500 mt-1 truncate" title="{{ $historico->observacoes }}">
                                        {{ $historico->observacoes }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $historico->ip_address }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="{{ route('historico.show', $historico->id) }}" class="text-indigo-600 hover:text-indigo-900">
                                    Ver Detalhes
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                                Nenhum registro de histórico encontrado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Layout mobile otimizado com cards -->
        <div class="md:hidden space-y-4">
            @forelse($historicos as $historico)
                <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                    <!-- Header do card -->
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-500 mr-3">
                                <i class="fas fa-history text-lg"></i>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-mobile-title text-gray-900">{{ $historico->usuario->name ?? 'Sistema' }}</h3>
                                <p class="text-xs text-gray-500">{{ $historico->created_at->format('d/m/Y H:i:s') }}</p>
                            </div>
                        </div>
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                            @if($historico->acao == 'criado' || $historico->acao == 'create') bg-green-100 text-green-800
                            @elseif($historico->acao == 'atualizado' || $historico->acao == 'update') bg-yellow-100 text-yellow-800
                            @elseif($historico->acao == 'excluido' || $historico->acao == 'delete') bg-red-100 text-red-800
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ ucfirst($historico->acao) }}
                        </span>
                    </div>
                    
                    <!-- Informações em grid -->
                    <div class="grid grid-cols-2 gap-3 mb-3">
                        <div class="bg-gray-50 rounded-lg p-3">
                            <div class="text-xs text-gray-600 mb-1">Modelo</div>
                            <div class="text-mobile-body text-gray-900">{{ $historico->modelo }}</div>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <div class="text-xs text-gray-600 mb-1">ID</div>
                            <div class="text-mobile-body text-gray-900">{{ $historico->modelo_id }}</div>
                        </div>
                    </div>
                    
                    <!-- Resumo das alterações -->
                    <div class="mb-3 p-3 bg-gray-50 rounded-lg">
                        <div class="text-xs text-gray-600 mb-1">Resumo das Alterações</div>
                        @php
                            $resumo = '';
                            if ($historico->acao == 'criado' || $historico->acao == 'create') {
                                if ($historico->dados_novos) {
                                    $dados = is_string($historico->dados_novos) ? json_decode($historico->dados_novos, true) : $historico->dados_novos;
                                    if ($historico->modelo == 'Aluno' && isset($dados['nome'])) {
                                        $resumo = 'Aluno: ' . ($dados['nome'] ?? '') . ' ' . ($dados['sobrenome'] ?? '');
                                    } elseif ($historico->modelo == 'Escala' && isset($dados['data'])) {
                                        $resumo = 'Escala: ' . ($dados['data'] ?? '') . ' - ' . ($dados['hora_inicio'] ?? '') . ' às ' . ($dados['hora_fim'] ?? '');
                                    } elseif ($historico->modelo == 'Funcionario' && isset($dados['nome'])) {
                                        $resumo = 'Funcionário: ' . ($dados['nome'] ?? '') . ' ' . ($dados['sobrenome'] ?? '');
                                    } elseif ($historico->modelo == 'Presenca') {
                                        $aluno = \App\Models\Aluno::find($dados['aluno_id'] ?? null);
                                        $presente = $dados['presente'] ?? false;
                                        $data = $dados['data'] ?? 'N/A';
                                        $resumo = 'Presença: ' . ($aluno->nomeCompleto ?? 'Aluno ID: ' . ($dados['aluno_id'] ?? 'N/A')) . ' - ' . ($presente ? 'Presente' : 'Ausente') . ' em ' . \Carbon\Carbon::parse($data)->format('d/m/Y');
                                    } else {
                                        $resumo = 'Novo registro criado';
                                    }
                                }
                            } elseif ($historico->acao == 'atualizado' || $historico->acao == 'update') {
                                if ($historico->dados_antigos && $historico->dados_novos) {
                                    $antigos = is_string($historico->dados_antigos) ? json_decode($historico->dados_antigos, true) : $historico->dados_antigos;
                                    $novos = is_string($historico->dados_novos) ? json_decode($historico->dados_novos, true) : $historico->dados_novos;
                                    $alteracoes = [];
                                    
                                    foreach ($novos as $campo => $valor) {
                                        if (isset($antigos[$campo]) && $antigos[$campo] != $valor) {
                                            $alteracoes[] = ucfirst(str_replace('_', ' ', $campo));
                                        }
                                    }
                                    
                                    if (count($alteracoes) > 0) {
                                        $resumo = 'Alterado: ' . implode(', ', array_slice($alteracoes, 0, 3));
                                        if (count($alteracoes) > 3) {
                                            $resumo .= ' e mais ' . (count($alteracoes) - 3) . ' campo(s)';
                                        }
                                    } else {
                                        $resumo = 'Registro atualizado';
                                    }
                                }
                            } elseif ($historico->acao == 'excluido' || $historico->acao == 'delete') {
                                if ($historico->dados_antigos) {
                                    $dados = is_string($historico->dados_antigos) ? json_decode($historico->dados_antigos, true) : $historico->dados_antigos;
                                    if ($historico->modelo == 'Aluno' && isset($dados['nome'])) {
                                        $resumo = 'Excluído: ' . ($dados['nome'] ?? '') . ' ' . ($dados['sobrenome'] ?? '');
                                    } elseif ($historico->modelo == 'Escala' && isset($dados['data'])) {
                                        $resumo = 'Excluído: Escala de ' . ($dados['data'] ?? '');
                                    } elseif ($historico->modelo == 'Funcionario' && isset($dados['nome'])) {
                                        $resumo = 'Excluído: ' . ($dados['nome'] ?? '') . ' ' . ($dados['sobrenome'] ?? '');
                                    } elseif ($historico->modelo == 'Presenca') {
                                        $aluno = \App\Models\Aluno::find($dados['aluno_id'] ?? null);
                                        $data = $dados['data'] ?? 'N/A';
                                        $resumo = 'Excluído: Presença de ' . ($aluno->nomeCompleto ?? 'Aluno ID: ' . ($dados['aluno_id'] ?? 'N/A')) . ' em ' . \Carbon\Carbon::parse($data)->format('d/m/Y');
                                    } else {
                                        $resumo = 'Registro excluído';
                                    }
                                }
                            }
                        @endphp
                        <div class="text-mobile-body text-gray-900">
                            {{ $resumo ?: 'Sem detalhes disponíveis' }}
                        </div>
                        @if($historico->observacoes)
                            <div class="text-xs text-gray-500 mt-2">
                                <strong>Observações:</strong> {{ $historico->observacoes }}
                            </div>
                        @endif
                    </div>
                    
                    <!-- Footer com IP e ação -->
                    <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                        <div class="text-xs text-gray-500">
                            <i class="fas fa-globe mr-1"></i>{{ $historico->ip_address }}
                        </div>
                        <a href="{{ route('historico.show', $historico->id) }}" 
                           class="bg-indigo-600 hover:bg-indigo-700 text-white text-center py-2 px-4 rounded-lg text-mobile-button min-h-[40px] flex items-center justify-center transition-colors">
                            <i class="fas fa-eye mr-2"></i>
                            Ver Detalhes
                        </a>
                    </div>
                </div>
            @empty
                <div class="text-center py-8">
                    <div class="w-16 h-16 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
                        <i class="fas fa-inbox text-2xl text-gray-400"></i>
                    </div>
                    <p class="text-gray-500">Nenhum registro de histórico encontrado.</p>
                </div>
            @endforelse
        </div>

        <!-- Paginação -->
        @if($historicos->hasPages())
            <div class="mt-6">
                {{ $historicos->appends(request()->query())->links('components.pagination') }}
            </div>
        @endif
    </x-card>
@endsection