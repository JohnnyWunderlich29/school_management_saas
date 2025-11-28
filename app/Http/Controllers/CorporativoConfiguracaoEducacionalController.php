<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Escola;
use App\Models\ModalidadeEnsino;
use App\Models\NivelEnsino;
use App\Models\EscolaModalidadeConfig;
use App\Models\EscolaNivelConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CorporativoConfiguracaoEducacionalController extends Controller
{
    /**
     * Obter escolas que o usuário pode acessar (SuperAdmin/Suporte veem todas)
     */
    private function getEscolasAcessiveis()
    {
        $user = Auth::user();
        
        // Super admins e suporte podem ver todas as escolas
        if ($user->isSuperAdmin() || $user->temCargo('Suporte')) {
            return Escola::with(['modalidadeConfigs.modalidadeEnsino', 'nivelConfigs.nivelEnsino'])
                ->orderBy('nome')
                ->get();
        }
        
        // Usuários normais não devem acessar esta área
        abort(403, 'Acesso negado. Esta área é restrita a administradores.');
    }

    public function index()
    {
        $escolas = $this->getEscolasAcessiveis();

        return view('corporativo.configuracao-educacional.index', compact('escolas'));
    }

    public function relatorio()
    {
        $user = Auth::user();
        
        // Verificar se é SuperAdmin ou Suporte
        if (!$user->isSuperAdmin() && !$user->temCargo('Suporte')) {
            abort(403, 'Acesso negado. Esta área é restrita a administradores.');
        }
        
        // Definir query base para escolas (SuperAdmin/Suporte veem todas)
        $escolasQuery = Escola::query();
        
        // Estatísticas básicas
        $totalEscolas = $escolasQuery->count();
        $escolasComModalidades = (clone $escolasQuery)->whereHas('modalidadeConfigs')->count();
        $escolasComNiveis = (clone $escolasQuery)->whereHas('nivelConfigs')->count();
        $escolasSemConfig = $totalEscolas - (clone $escolasQuery)->whereHas('modalidadeConfigs')->orWhereHas('nivelConfigs')->count();

        // Contagens de configurações (todas as escolas)
        $modalidadeConfigQuery = EscolaModalidadeConfig::query();
        $nivelConfigQuery = EscolaNivelConfig::query();
        
        $estatisticas = [
            'total_escolas' => $totalEscolas,
            'total_modalidades_config' => $modalidadeConfigQuery->count(),
            'total_niveis_config' => $nivelConfigQuery->count(),
            'escolas_sem_config' => $escolasSemConfig,
        ];

        // Modalidades mais populares
        $modalidadesPopularesQuery = DB::table('escola_modalidades_config')
            ->join('modalidades_ensino', 'escola_modalidades_config.modalidade_ensino_id', '=', 'modalidades_ensino.id')
            ->select('modalidades_ensino.nome', DB::raw('count(distinct escola_modalidades_config.escola_id) as total_escolas'))
            ->where('escola_modalidades_config.ativo', true);
        
        $modalidadesPopulares = $modalidadesPopularesQuery
            ->groupBy('modalidades_ensino.id', 'modalidades_ensino.nome')
            ->orderByDesc('total_escolas')
            ->limit(10)
            ->get();

        // Níveis mais populares
        $niveisPopularesQuery = DB::table('escola_niveis_config')
            ->join('niveis_ensino', 'escola_niveis_config.nivel_ensino_id', '=', 'niveis_ensino.id')
            ->select('niveis_ensino.nome', DB::raw('count(distinct escola_niveis_config.escola_id) as total_escolas'))
            ->where('escola_niveis_config.ativo', true);
        
        $niveisPopulares = $niveisPopularesQuery
            ->groupBy('niveis_ensino.id', 'niveis_ensino.nome')
            ->orderByDesc('total_escolas')
            ->limit(10)
            ->get();

        // Estatísticas de turnos para modalidades
        $turnosModalidadesQuery = EscolaModalidadeConfig::where('ativo', true);
        
        $turnosModalidades = [
            'matutino' => (clone $turnosModalidadesQuery)->where('permite_turno_matutino', true)->count(),
            'vespertino' => (clone $turnosModalidadesQuery)->where('permite_turno_vespertino', true)->count(),
            'noturno' => (clone $turnosModalidadesQuery)->where('permite_turno_noturno', true)->count(),
            'integral' => (clone $turnosModalidadesQuery)->where('permite_turno_integral', true)->count(),
        ];

        // Estatísticas de turnos para níveis
        $turnosNiveisQuery = EscolaNivelConfig::where('ativo', true);
        
        $turnosNiveis = [
            'matutino' => (clone $turnosNiveisQuery)->where('permite_turno_matutino', true)->count(),
            'vespertino' => (clone $turnosNiveisQuery)->where('permite_turno_vespertino', true)->count(),
            'noturno' => (clone $turnosNiveisQuery)->where('permite_turno_noturno', true)->count(),
            'integral' => (clone $turnosNiveisQuery)->where('permite_turno_integral', true)->count(),
        ];

        // Escolas com configurações incompletas
        $escolasIncompletasQuery = Escola::with(['modalidadeConfigs', 'nivelConfigs']);
        
        $escolasIncompletas = $escolasIncompletasQuery
            ->get()
            ->filter(function ($escola) {
                $modalidadesCount = $escola->modalidadeConfigs->where('ativo', true)->count();
                $niveisCount = $escola->nivelConfigs->where('ativo', true)->count();
                return $modalidadesCount == 0 || $niveisCount == 0;
            })
            ->map(function ($escola) {
                $escola->modalidades_count = $escola->modalidadeConfigs->where('ativo', true)->count();
                $escola->niveis_count = $escola->nivelConfigs->where('ativo', true)->count();
                return $escola;
            });

        // Capacidades por modalidades
        $capacidadesModalidadesQuery = DB::table('escola_modalidades_config')
            ->join('modalidades_ensino', 'escola_modalidades_config.modalidade_ensino_id', '=', 'modalidades_ensino.id')
            ->select(
                'modalidades_ensino.nome',
                DB::raw('AVG(escola_modalidades_config.capacidade_minima_turma) as capacidade_minima_media'),
                DB::raw('AVG(escola_modalidades_config.capacidade_maxima_turma) as capacidade_maxima_media'),
                DB::raw('COUNT(*) as total_configuracoes')
            )
            ->where('escola_modalidades_config.ativo', true)
            ->whereNotNull('escola_modalidades_config.capacidade_minima_turma')
            ->whereNotNull('escola_modalidades_config.capacidade_maxima_turma');
        
        $capacidadesModalidades = $capacidadesModalidadesQuery
            ->groupBy('modalidades_ensino.id', 'modalidades_ensino.nome')
            ->orderBy('modalidades_ensino.nome')
            ->get();

        // Capacidades por níveis
        $capacidadesNiveisQuery = DB::table('escola_niveis_config')
            ->join('niveis_ensino', 'escola_niveis_config.nivel_ensino_id', '=', 'niveis_ensino.id')
            ->select(
                'niveis_ensino.nome',
                DB::raw('AVG(escola_niveis_config.capacidade_minima_turma) as capacidade_minima_media'),
                DB::raw('AVG(escola_niveis_config.capacidade_maxima_turma) as capacidade_maxima_media'),
                DB::raw('COUNT(*) as total_configuracoes')
            )
            ->where('escola_niveis_config.ativo', true)
            ->whereNotNull('escola_niveis_config.capacidade_minima_turma')
            ->whereNotNull('escola_niveis_config.capacidade_maxima_turma');
        
        $capacidadesNiveis = $capacidadesNiveisQuery
            ->groupBy('niveis_ensino.id', 'niveis_ensino.nome')
            ->orderBy('niveis_ensino.nome')
            ->get();

        // Adicionar dados aos estatísticas
        $estatisticas['modalidades_populares'] = $modalidadesPopulares;
        $estatisticas['niveis_populares'] = $niveisPopulares;
        $estatisticas['turnos_modalidades'] = $turnosModalidades;
        $estatisticas['turnos_niveis'] = $turnosNiveis;
        $estatisticas['escolas_incompletas'] = $escolasIncompletas;
        $estatisticas['capacidades_modalidades'] = $capacidadesModalidades;
        $estatisticas['capacidades_niveis'] = $capacidadesNiveis;

        return view('corporativo.configuracao-educacional.relatorio', compact('estatisticas'));
    }
}