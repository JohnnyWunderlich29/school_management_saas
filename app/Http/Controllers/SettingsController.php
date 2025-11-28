<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\Escola;
use App\Models\ModalidadeEnsino;
use App\Models\NivelEnsino;
use App\Models\Turno;
use App\Models\Finance\FinanceSettings;
use App\Models\Finance\FinanceGateway;

class SettingsController extends Controller
{
    /**
     * Página unificada de configurações com seções (financeiro, educacional, administrativo).
     */
    public function index(Request $request)
    {
        $schoolId = $request->query('school_id')
            ?? optional(auth()->user())->escola_id
            ?? optional(auth()->user())->school_id
            ?? session('escola_atual');

        // Tab ativa por query param, padrão 'financeiro'
        $activeTab = $request->query('tab', 'financeiro');

        // Quando a aba financeiro for solicitada, carregar os dados necessários
        if ($activeTab === 'financeiro' && $schoolId) {
            $settings = FinanceSettings::firstOrCreate(['school_id' => $schoolId], ['currency' => 'BRL']);
            $gateways = FinanceGateway::where('school_id', $schoolId)->orderBy('alias')->get();
            $financeEnv = config('features.finance_env', 'production');

            $currentSchoolId = $schoolId;
            $tab = $activeTab;

            return view('settings.index', compact(
                'currentSchoolId',
                'tab',
                'settings',
                'gateways',
                'financeEnv'
            ));
        }

        // Quando a aba educacional for solicitada, carregar os dados necessários para renderizar inline
        if ($activeTab === 'educacional' && $schoolId) {
            $escola = Escola::with([
                'modalidadeConfigs.modalidadeEnsino',
                'nivelConfigs.nivelEnsino'
            ])->find($schoolId);

            if ($escola) {
                // IDs das modalidades já configuradas
                $modalidadesConfiguradas = $escola->modalidadeConfigs()
                    ->pluck('modalidade_ensino_id')
                    ->toArray();

                // Modalidades padrão (BNCC) não configuradas ainda
                $modalidadesPadrao = ModalidadeEnsino::whereNull('escola_id')
                    ->whereNotIn('id', $modalidadesConfiguradas)
                    ->orderBy('nome')
                    ->get();

                // Modalidades personalizadas da escola não configuradas ainda
                $modalidadesPersonalizadas = ModalidadeEnsino::where('escola_id', $escola->id)
                    ->whereNotIn('id', $modalidadesConfiguradas)
                    ->orderBy('nome')
                    ->get();

                // IDs dos níveis já configurados
                $niveisConfigurados = $escola->nivelConfigs()
                    ->pluck('nivel_ensino_id')
                    ->toArray();

                // Níveis disponíveis (exclui os já configurados)
                $niveisDisponiveis = NivelEnsino::whereNotIn('id', $niveisConfigurados)
                    ->orderBy('nome')
                    ->get();

                // Turnos da escola para gestão na aba educacional
                $turnos = Turno::ordenados()->where('escola_id', $schoolId)->get();

                // Garantir que as variáveis esperadas pelo Blade estejam definidas
                $currentSchoolId = $schoolId;
                $tab = $activeTab;

                return view('settings.index', compact(
                    'currentSchoolId',
                    'tab',
                    'escola',
                    'modalidadesPadrao',
                    'modalidadesPersonalizadas',
                    'niveisDisponiveis',
                    'turnos'
                ));
            }
        }

        // Garantir nomes esperados pelo blade
        $currentSchoolId = $schoolId;
        $tab = $activeTab;
        return view('settings.index', compact('currentSchoolId', 'tab'));
    }
}