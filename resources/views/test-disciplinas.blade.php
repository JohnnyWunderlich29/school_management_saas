@extends('layouts.app')

@section('title', 'Teste - Disciplinas por Modalidade, Turno e Grupo')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">Teste - Carregamento de Disciplinas</h1>
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Modalidade</label>
                    <select id="modalidade" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        <option value="">Selecione...</option>
                        @foreach($modalidades as $codigo => $nome)
                            <option value="{{ $codigo }}">{{ $nome }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Turno</label>
                    <select id="turno" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        <option value="">Selecione...</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Grupo</label>
                    <select id="grupo" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        <option value="">Selecione...</option>
                    </select>
                </div>
            </div>
            
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Disciplinas Encontradas</label>
                <select id="disciplinas" class="w-full px-3 py-2 border border-gray-300 rounded-md" size="8">
                    <option value="">Nenhuma disciplina carregada</option>
                </select>
            </div>
            
            <div id="debug-info" class="bg-gray-100 p-4 rounded-md">
                <h3 class="font-medium text-gray-900 mb-2">Debug Info:</h3>
                <pre id="debug-content" class="text-sm text-gray-600">Selecione modalidade, turno e grupo para ver as disciplinas</pre>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modalidadeSelect = document.getElementById('modalidade');
    const turnoSelect = document.getElementById('turno');
    const grupoSelect = document.getElementById('grupo');
    const disciplinasSelect = document.getElementById('disciplinas');
    const debugContent = document.getElementById('debug-content');
    
    function updateDebug(message) {
        debugContent.textContent = message;
    }
    
    modalidadeSelect.addEventListener('change', async function() {
        const modalidadeId = this.value;
        turnoSelect.innerHTML = '<option value="">Carregando...</option>';
        grupoSelect.innerHTML = '<option value="">Selecione...</option>';
        disciplinasSelect.innerHTML = '<option value="">Nenhuma disciplina carregada</option>';
        
        if (modalidadeId) {
            try {
                const response = await fetch(`/planejamentos/turnos-disponiveis?modalidade_id=${modalidadeId}`);
                const turnos = await response.json();
                
                turnoSelect.innerHTML = '<option value="">Selecione...</option>';
                turnos.forEach(turno => {
                    turnoSelect.innerHTML += `<option value="${turno.id}">${turno.nome}</option>`;
                });
                
                updateDebug(`Modalidade: ${modalidadeId}, Turnos carregados: ${turnos.length}`);
            } catch (error) {
                updateDebug(`Erro ao carregar turnos: ${error.message}`);
            }
        }
    });
    
    turnoSelect.addEventListener('change', async function() {
        const turnoId = this.value;
        const modalidadeId = modalidadeSelect.value;
        grupoSelect.innerHTML = '<option value="">Carregando...</option>';
        disciplinasSelect.innerHTML = '<option value="">Nenhuma disciplina carregada</option>';
        
        if (turnoId && modalidadeId) {
            try {
                const response = await fetch(`/planejamentos/grupos-educacionais?modalidade_id=${modalidadeId}&turno_id=${turnoId}`);
                const grupos = await response.json();
                
                grupoSelect.innerHTML = '<option value="">Selecione...</option>';
                grupos.forEach(grupo => {
                    grupoSelect.innerHTML += `<option value="${grupo.id}">${grupo.nome}</option>`;
                });
                
                updateDebug(`Modalidade: ${modalidadeId}, Turno: ${turnoId}, Grupos carregados: ${grupos.length}`);
            } catch (error) {
                updateDebug(`Erro ao carregar grupos: ${error.message}`);
            }
        }
    });
    
    grupoSelect.addEventListener('change', async function() {
        const grupoId = this.value;
        const modalidadeId = modalidadeSelect.value;
        const turnoId = turnoSelect.value;
        disciplinasSelect.innerHTML = '<option value="">Carregando...</option>';
        
        if (grupoId && modalidadeId && turnoId) {
            try {
                const response = await fetch(`/planejamentos/disciplinas-por-modalidade-turno-grupo?modalidade_id=${modalidadeId}&turno_id=${turnoId}&grupo_id=${grupoId}`);
                const disciplinas = await response.json();
                
                disciplinasSelect.innerHTML = '';
                if (disciplinas.length > 0) {
                    disciplinas.forEach(disciplina => {
                        disciplinasSelect.innerHTML += `<option value="${disciplina.id}">${disciplina.nome}</option>`;
                    });
                    updateDebug(`Modalidade: ${modalidadeId}, Turno: ${turnoId}, Grupo: ${grupoId}, Disciplinas encontradas: ${disciplinas.length}\n\nDisciplinas: ${disciplinas.map(d => d.nome).join(', ')}`);
                } else {
                    disciplinasSelect.innerHTML = '<option value="">Nenhuma disciplina encontrada</option>';
                    updateDebug(`Modalidade: ${modalidadeId}, Turno: ${turnoId}, Grupo: ${grupoId}, Nenhuma disciplina encontrada`);
                }
            } catch (error) {
                disciplinasSelect.innerHTML = '<option value="">Erro ao carregar disciplinas</option>';
                updateDebug(`Erro ao carregar disciplinas: ${error.message}`);
            }
        }
    });
});
</script>
@endsection