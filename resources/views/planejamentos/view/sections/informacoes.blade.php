<!-- Seção: Informações Gerais -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Informações Básicas -->
    <div class="space-y-4">
        <h3 class="text-sm font-medium text-gray-900 uppercase tracking-wide">Informações Básicas</h3>
        
        <div class="space-y-3">
            <div class="flex justify-between items-start">
                <span class="text-sm text-gray-600">Título:</span>
                <span class="text-sm font-medium text-gray-900 text-right max-w-xs">{{ $planejamento->titulo }}</span>
            </div>
            
            <div class="flex justify-between items-start">
                <span class="text-sm text-gray-600">Descrição:</span>
                <span class="text-sm text-gray-900 text-right max-w-xs">
                    {{ $planejamento->descricao ?: 'Não informado' }}
                </span>
            </div>
            
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600">Status:</span>
@include('planejamentos.components.status-badge', ['status' => $planejamento->status_efetivo])
            </div>
            
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600">Data de Criação:</span>
                <span class="text-sm text-gray-900">{{ $planejamento->created_at->format('d/m/Y H:i') }}</span>
            </div>
            
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600">Última Atualização:</span>
                <span class="text-sm text-gray-900">{{ $planejamento->updated_at->format('d/m/Y H:i') }}</span>
            </div>
        </div>
    </div>

    <!-- Modalidade e Nível -->
    <div class="space-y-4">
        <h3 class="text-sm font-medium text-gray-900 uppercase tracking-wide">Modalidade e Nível</h3>
        
        <div class="space-y-3">
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600">Modalidade de Ensino:</span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    {{ ucfirst($planejamento->modalidade_ensino) }}
                </span>
            </div>
            
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600">Nível de Ensino:</span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    {{ $planejamento->nivel_ensino }}
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Responsáveis -->
<div class="mt-6 pt-6 border-t border-gray-200">
    <h3 class="text-sm font-medium text-gray-900 uppercase tracking-wide mb-4">Responsáveis</h3>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <!-- Criado por -->
        <div class="bg-gray-50 rounded-lg p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                        <i class="fas fa-user text-gray-600"></i>
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-900">{{ $planejamento->criador->name ?? 'Sistema' }}</p>
                    <p class="text-xs text-gray-500">Criado por</p>
                </div>
            </div>
        </div>

        <!-- Professor Responsável -->
        @if($planejamento->professor)
        <div class="bg-blue-50 rounded-lg p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="h-10 w-10 rounded-full bg-blue-200 flex items-center justify-center">
                        <i class="fas fa-chalkboard-teacher text-blue-600"></i>
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-900">{{ $planejamento->professor->name }}</p>
                    <p class="text-xs text-gray-500">Professor Responsável</p>
                </div>
            </div>
        </div>
        @endif

        <!-- Aprovado por -->
        @if($planejamento->aprovador)
        <div class="bg-green-50 rounded-lg p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="h-10 w-10 rounded-full bg-green-200 flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600"></i>
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-900">{{ $planejamento->aprovador->name }}</p>
                    <p class="text-xs text-gray-500">Aprovado por</p>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Estatísticas -->
<div class="mt-6 pt-6 border-t border-gray-200">
    <h3 class="text-sm font-medium text-gray-900 uppercase tracking-wide mb-4">Estatísticas</h3>
    
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="text-center">
            <div class="text-2xl font-bold text-blue-600">{{ $planejamento->total_aulas ?? 0 }}</div>
            <div class="text-xs text-gray-500">Total de Aulas</div>
        </div>
        
        <div class="text-center">
            <div class="text-2xl font-bold text-green-600">{{ $planejamento->carga_horaria ?? 0 }}h</div>
            <div class="text-xs text-gray-500">Carga Horária</div>
        </div>
        
        <div class="text-center">
            <div class="text-2xl font-bold text-purple-600">{{ $planejamento->turma->alunos_count ?? 0 }}</div>
            <div class="text-xs text-gray-500">Alunos</div>
        </div>
        
        <div class="text-center">
            <div class="text-2xl font-bold text-orange-600">{{ $planejamento->objetivos_count ?? 0 }}</div>
            <div class="text-xs text-gray-500">Objetivos</div>
        </div>
    </div>
</div>

<!-- Categorias -->
@if($planejamento->campos_experiencia)
<div class="mt-6 pt-6 border-t border-gray-200">
    <h3 class="text-sm font-medium text-gray-900 uppercase tracking-wide mb-4">Categorias</h3>
    
    <div class="space-y-3">
        <div>
            <span class="text-xs text-gray-500 uppercase tracking-wide">Campos de Experiência:</span>
            @php
                $campos = is_string($planejamento->campos_experiencia)
                    ? json_decode($planejamento->campos_experiencia, true)
                    : $planejamento->campos_experiencia;
                $campos = $campos ?? [];

                // Mapear IDs para nomes quando necessário
                $labelsCampos = [];
                if (is_array($campos) && count($campos) > 0) {
                    $ids = array_values(array_filter($campos, function($c){ return is_numeric($c); }));
                    $nomesPorId = [];
                    if (count($ids) > 0) {
                        $nomesPorId = \App\Models\CampoExperiencia::whereIn('id', $ids)->pluck('nome', 'id')->toArray();
                    }
                    foreach ($campos as $c) {
                        if (is_numeric($c)) {
                            $labelsCampos[] = $nomesPorId[$c] ?? (string)$c;
                        } else {
                            $labelsCampos[] = is_array($c) ? ($c['nome'] ?? json_encode($c)) : (string)$c;
                        }
                    }
                }
            @endphp
            @if(isset($labelsCampos) && count($labelsCampos) > 0)
            <div class="mt-1 flex flex-wrap gap-1">
                @foreach($labelsCampos as $label)
                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-purple-100 text-purple-800">
                    {{ $label }}
                </span>
                @endforeach
            </div>
            @else
            <div class="mt-1 text-xs text-gray-500 italic">Nenhum campo de experiência selecionado</div>
            @endif
        </div>
    </div>
</div>
@endif
