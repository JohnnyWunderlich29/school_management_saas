<?php

namespace App\Http\Controllers;

use App\Models\ModalidadeEnsino;
use App\Models\Grupo;
use App\Models\Turno;
use App\Models\Disciplina;
use Illuminate\Http\Request;

class ConfiguracoesController extends Controller
{
    /**
     * Display the configurations page with tabs
     */
    public function index(Request $request)
    {
        $activeTab = $request->get('tab', 'modalidades');
        
        // Determinar escola_id baseado no usuário e sessão
        $user = auth()->user();
        $escolaId = null;
        
        if ($user->isSuperAdmin() || $user->temCargo('Suporte')) {
            $escolaId = session('escola_atual');
        } else {
            $escolaId = $user->escola_id;
        }
        
        $data = [
            'activeTab' => $activeTab,
            'modalidades' => [],
            'grupos' => [],
            'turnos' => [],
            'disciplinas' => []
        ];
        
        // Load additional data for filters
        if ($activeTab === 'disciplinas') {
            $modalidadesQuery = ModalidadeEnsino::where('ativo', true);
            if ($escolaId) {
                $modalidadesQuery->where('escola_id', $escolaId);
            } else {
                $modalidadesQuery->where('escola_id', -1);
            }
            $data['modalidades'] = $modalidadesQuery->orderBy('nome')->get();
        }
        
        // Load data based on active tab to optimize performance
        switch ($activeTab) {
            case 'modalidades':
                $query = ModalidadeEnsino::query();
                
                // Apply school isolation
                if ($escolaId) {
                    $query->where('escola_id', $escolaId);
                } else {
                    $query->where('escola_id', -1);
                }
                
                // Apply filters
                if ($request->filled('search')) {
                    $search = $request->search;
                    $query->where(function($q) use ($search) {
                        $q->where('nome', 'like', "%{$search}%")
                          ->orWhere('descricao', 'like', "%{$search}%");
                    });
                }
                
                if ($request->filled('ativo')) {
                    $query->where('ativo', $request->ativo == '1');
                }
                
                $data['modalidades'] = $query->orderBy('nome')->paginate(15);
                break;
                
            case 'grupos':
                $query = Grupo::query();
                
                // Apply school isolation
                if ($escolaId) {
                    $query->where('escola_id', $escolaId);
                } else {
                    $query->where('escola_id', -1);
                }
                
                // Apply filters
                if ($request->filled('search')) {
                    $search = $request->search;
                    $query->where(function($q) use ($search) {
                        $q->where('nome', 'like', "%{$search}%")
                          ->orWhere('codigo', 'like', "%{$search}%")
                          ->orWhere('descricao', 'like', "%{$search}%");
                    });
                }
                
                if ($request->filled('ativo')) {
                    $query->where('ativo', $request->ativo == '1');
                }
                
                $data['grupos'] = $query->orderBy('nome')->paginate(15);
                break;
                
            case 'turnos':
                $query = Turno::query();
                
                // Apply school isolation
                if ($escolaId) {
                    $query->where('escola_id', $escolaId);
                } else {
                    $query->where('escola_id', -1);
                }
                
                // Apply filters
                if ($request->filled('search')) {
                    $search = $request->search;
                    $query->where(function($q) use ($search) {
                        $q->where('nome', 'like', "%{$search}%")
                          ->orWhere('codigo', 'like', "%{$search}%")
                          ->orWhere('descricao', 'like', "%{$search}%");
                    });
                }
                
                if ($request->filled('ativo')) {
                    $query->where('ativo', $request->ativo == '1');
                }
                
                $data['turnos'] = $query->orderBy('nome')->paginate(15);
                break;
                
            case 'disciplinas':
                $query = Disciplina::query();
                
                // Apply filters
                if ($request->filled('search')) {
                    $search = $request->search;
                    $query->where(function($q) use ($search) {
                        $q->where('nome', 'like', "%{$search}%")
                          ->orWhere('codigo', 'like', "%{$search}%")
                          ->orWhere('descricao', 'like', "%{$search}%");
                    });
                }
                
                if ($request->filled('modalidade_ensino_id')) {
                    $query->where('modalidade_ensino_id', $request->modalidade_ensino_id);
                }
                
                if ($request->filled('area_conhecimento')) {
                    $query->where('area_conhecimento', $request->area_conhecimento);
                }
                
                if ($request->filled('ativo')) {
                    $query->where('ativo', $request->ativo == '1');
                }
                
                $data['disciplinas'] = $query->orderBy('nome')->paginate(15);
                break;
        }
        
        return view('admin.configuracoes.index', $data)->with('request', $request);
    }
}