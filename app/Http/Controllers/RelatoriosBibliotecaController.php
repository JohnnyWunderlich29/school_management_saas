<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Emprestimo;
use App\Models\Reserva;
use App\Models\ItemBiblioteca;
use App\Models\User;
use App\Models\Multa;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Middleware\EscolaContext;

class RelatoriosBibliotecaController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:biblioteca.relatorios.ver')->only(['index', 'gerar']);
        $this->middleware('permission:biblioteca.relatorios.exportar')->only(['exportar']);
    }

    /**
     * Exibe a página principal de relatórios
     */
    public function index()
    {
        $escolaId = EscolaContext::getEscolaAtual();
        // Estatísticas gerais
        $estatisticas = [
            'total_emprestimos' => Emprestimo::where('status', 'ativo')
                ->when($escolaId, fn($q) => $q->where('escola_id', $escolaId))
                ->count(),
            'total_reservas' => Reserva::where('status', 'ativa')
                ->when($escolaId, fn($q) => $q->where('escola_id', $escolaId))
                ->count(),
            'emprestimos_atrasados' => Emprestimo::where('status', 'ativo')
                ->where('data_prevista', '<', now())
                ->when($escolaId, fn($q) => $q->where('escola_id', $escolaId))
                ->count(),
            'total_itens' => ItemBiblioteca::when($escolaId, fn($q) => $q->where('escola_id', $escolaId))->count(),
        ];

        // Top 10 usuários mais ativos
        $topUsuarios = User::when($escolaId, fn($q) => $q->where('escola_id', $escolaId))
            ->withCount([
                'emprestimos' => function ($query) use ($escolaId) {
                    $query->when($escolaId, fn($q) => $q->where('escola_id', $escolaId));
                },
                'reservas' => function ($query) use ($escolaId) {
                    $query->when($escolaId, fn($q) => $q->where('escola_id', $escolaId));
                }
            ])
            ->orderByDesc('emprestimos_count')
            ->limit(10)
            ->get();

        // Top 10 itens mais emprestados
        $topItens = ItemBiblioteca::when($escolaId, fn($q) => $q->where('escola_id', $escolaId))
            ->withCount([
                'emprestimos' => function ($query) use ($escolaId) {
                    $query->when($escolaId, fn($q) => $q->where('escola_id', $escolaId));
                }
            ])
            ->orderByDesc('emprestimos_count')
            ->limit(10)
            ->get();

        // Dados para gráficos
        $dadosGraficos = $this->getDadosGraficos();

        return view('biblioteca.relatorios.index', compact(
            'estatisticas',
            'topUsuarios', 
            'topItens',
            'dadosGraficos'
        ));
    }

    /**
     * Gera relatório baseado nos filtros
     */
    public function gerar(Request $request)
    {
        $tipo = $request->input('tipo_relatorio', 'emprestimos');
        $dataInicio = $request->input('data_inicio');
        $dataFim = $request->input('data_fim');
        $status = $request->input('status');
        $habilitado = $request->input('habilitado_emprestimo');
        $habilitado = in_array($habilitado, ['0','1'], true) ? (bool)((int)$habilitado) : null;

        switch ($tipo) {
            case 'emprestimos':
                return $this->relatorioEmprestimos($dataInicio, $dataFim, $status, $habilitado);
            case 'reservas':
                return $this->relatorioReservas($dataInicio, $dataFim, $status, $habilitado);
            case 'multas':
                return $this->relatorioMultas($dataInicio, $dataFim, $status);
            case 'usuarios_ativos':
                return $this->relatorioUsuariosAtivos($dataInicio, $dataFim);
            case 'itens_populares':
                return $this->relatorioItensPopulares($dataInicio, $dataFim, $habilitado);
            case 'estatisticas_gerais':
                return $this->relatorioEstatisticasGerais($dataInicio, $dataFim);
            default:
                return response()->json(['error' => 'Tipo de relatório inválido'], 400);
        }
    }

    /**
     * Exporta relatório em Excel ou PDF
     */
    public function exportar(Request $request)
    {
        $formato = $request->input('formato', 'excel');
        $tipo = $request->input('tipo_relatorio', 'emprestimos');
        $dataInicio = $request->input('data_inicio');
        $dataFim = $request->input('data_fim');
        $status = $request->input('status');
        $habilitado = $request->input('habilitado_emprestimo');
        $habilitado = in_array($habilitado, ['0','1'], true) ? (bool)((int)$habilitado) : null;

        $dados = $this->getDadosRelatorio($tipo, $dataInicio, $dataFim, $status, $habilitado);
        $nomeArquivo = "relatorio_{$tipo}_" . date('Y-m-d');

        if ($formato === 'excel') {
            return Excel::download(
                new RelatorioExport($dados, $tipo),
                "{$nomeArquivo}.xlsx"
            );
        } else {
            $pdf = Pdf::loadView('biblioteca.relatorios.pdf', [
                'dados' => $dados,
                'tipo' => $tipo,
                'dataInicio' => $dataInicio,
                'dataFim' => $dataFim
            ]);
            
            return $pdf->download("{$nomeArquivo}.pdf");
        }
    }

    /**
     * Relatório de empréstimos
     */
    private function relatorioEmprestimos($dataInicio, $dataFim, $status, $habilitado)
    {
        $query = Emprestimo::with(['usuario', 'item'])
            ->when($dataInicio, function ($q) use ($dataInicio) {
                return $q->whereDate('data_emprestimo', '>=', $dataInicio);
            })
            ->when($dataFim, function ($q) use ($dataFim) {
                return $q->whereDate('data_emprestimo', '<=', $dataFim);
            })
            ->when($status, function ($q) use ($status) {
                if ($status === 'atrasado') {
                    return $q->where('status', 'ativo')
                             ->where('data_prevista', '<', now());
                }
                return $q->where('status', $status);
            })
            ->when(!is_null($habilitado), function ($q) use ($habilitado) {
                return $q->whereHas('item', function ($qi) use ($habilitado) {
                    $qi->where('habilitado_emprestimo', $habilitado);
                });
            })
            ->orderBy('data_emprestimo', 'desc');

        // Escopo por escola
        $query = EscolaContext::aplicarFiltroEscola($query);

        $emprestimos = $query->paginate(50);

        return view('biblioteca.relatorios.emprestimos', compact('emprestimos'))->render();
    }

    /**
     * Relatório de reservas
     */
    private function relatorioReservas($dataInicio, $dataFim, $status, $habilitado)
    {
        $query = Reserva::with(['usuario', 'item'])
            ->when($dataInicio, function ($q) use ($dataInicio) {
                return $q->whereDate('data_reserva', '>=', $dataInicio);
            })
            ->when($dataFim, function ($q) use ($dataFim) {
                return $q->whereDate('data_reserva', '<=', $dataFim);
            })
            ->when($status, function ($q) use ($status) {
                return $q->where('status', $status);
            })
            ->when(!is_null($habilitado), function ($q) use ($habilitado) {
                return $q->whereHas('item', function ($qi) use ($habilitado) {
                    $qi->where('habilitado_emprestimo', $habilitado);
                });
            })
            ->orderBy('data_reserva', 'desc');

        // Escopo por escola
        $query = EscolaContext::aplicarFiltroEscola($query);

        $reservas = $query->paginate(50);

        return view('biblioteca.relatorios.reservas', compact('reservas'))->render();
    }

    /**
     * Relatório de multas
     */
    private function relatorioMultas($dataInicio, $dataFim, $status)
    {
        $query = Multa::with(['usuario', 'emprestimo.item'])
            ->when($dataInicio, function ($q) use ($dataInicio) {
                return $q->whereDate('data_multa', '>=', $dataInicio);
            })
            ->when($dataFim, function ($q) use ($dataFim) {
                return $q->whereDate('data_multa', '<=', $dataFim);
            })
            ->when($status, function ($q) use ($status) {
                return $q->where('status', $status);
            })
            ->orderBy('data_multa', 'desc');

        // Escopo por escola
        $query = EscolaContext::aplicarFiltroEscola($query);

        $multas = $query->paginate(50);

        // Totais agregados para a visão
        $baseQuery = clone $query;
        $valorTotal = (clone $baseQuery)->sum('valor');
        $pendentes = (clone $baseQuery)
            ->where(function($q){
                $q->where('status', 'pendente')
                  ->orWhereNull('status')
                  ->orWhere('paga', false);
            })->count();
        $pagas = (clone $baseQuery)
            ->where(function($q){
                $q->where('status', 'paga')
                  ->orWhere('paga', true);
            })->count();
        $canceladas = (clone $baseQuery)->where('status', 'cancelada')->count();

        $totais = [
            'valor_total' => $valorTotal,
            'pendentes' => $pendentes,
            'pagas' => $pagas,
            'canceladas' => $canceladas,
        ];

        return view('biblioteca.relatorios.multas', compact('multas', 'totais'))->render();
    }

    /**
     * Relatório de usuários mais ativos
     */
    private function relatorioUsuariosAtivos($dataInicio, $dataFim)
    {
        $escolaId = EscolaContext::getEscolaAtual();
        $usuarios = User::when($escolaId, fn($q) => $q->where('escola_id', $escolaId))
            ->withCount([
                'emprestimos' => function ($query) use ($dataInicio, $dataFim) {
                    $query->when($dataInicio, function ($q) use ($dataInicio) {
                        return $q->whereDate('data_emprestimo', '>=', $dataInicio);
                    })
                    ->when($dataFim, function ($q) use ($dataFim) {
                        return $q->whereDate('data_emprestimo', '<=', $dataFim);
                    })
                    ->when(EscolaContext::getEscolaAtual(), function ($q) {
                        $q->where('escola_id', EscolaContext::getEscolaAtual());
                    });
                },
                'reservas' => function ($query) use ($dataInicio, $dataFim) {
                    $query->when($dataInicio, function ($q) use ($dataInicio) {
                        return $q->whereDate('data_reserva', '>=', $dataInicio);
                    })
                    ->when($dataFim, function ($q) use ($dataFim) {
                        return $q->whereDate('data_reserva', '<=', $dataFim);
                    })
                    ->when(EscolaContext::getEscolaAtual(), function ($q) {
                        $q->where('escola_id', EscolaContext::getEscolaAtual());
                    });
                }
            ])
            // Compatível com Postgres: usar whereHas para filtrar por usuários com empréstimos no período
            ->whereHas('emprestimos', function ($q) use ($dataInicio, $dataFim) {
                $q->when($dataInicio, function ($qi) use ($dataInicio) {
                    return $qi->whereDate('data_emprestimo', '>=', $dataInicio);
                })
                ->when($dataFim, function ($qi) use ($dataFim) {
                    return $qi->whereDate('data_emprestimo', '<=', $dataFim);
                })
                ->when(EscolaContext::getEscolaAtual(), function ($qi) {
                    $qi->where('escola_id', EscolaContext::getEscolaAtual());
                });
            })
            ->orderByDesc('emprestimos_count')
            ->paginate(50);

        return view('biblioteca.relatorios.usuarios_ativos', compact('usuarios'))->render();
    }

    /**
     * Relatório de itens mais populares
     */
    private function relatorioItensPopulares($dataInicio, $dataFim, $habilitado)
    {
        $escolaId = EscolaContext::getEscolaAtual();
        $itens = ItemBiblioteca::when($escolaId, fn($q) => $q->where('escola_id', $escolaId))
            ->when(!is_null($habilitado), function ($q) use ($habilitado) {
                return $q->where('habilitado_emprestimo', $habilitado);
            })
            ->withCount([
                'emprestimos' => function ($query) use ($dataInicio, $dataFim) {
                    $query->when($dataInicio, function ($q) use ($dataInicio) {
                        return $q->whereDate('data_emprestimo', '>=', $dataInicio);
                    })
                    ->when($dataFim, function ($q) use ($dataFim) {
                        return $q->whereDate('data_emprestimo', '<=', $dataFim);
                    })
                    ->when(EscolaContext::getEscolaAtual(), function ($q) {
                        $q->where('escola_id', EscolaContext::getEscolaAtual());
                    });
                },
                'reservas' => function ($query) use ($dataInicio, $dataFim) {
                    $query->when($dataInicio, function ($q) use ($dataInicio) {
                        return $q->whereDate('data_reserva', '>=', $dataInicio);
                    })
                    ->when($dataFim, function ($q) use ($dataFim) {
                        return $q->whereDate('data_reserva', '<=', $dataFim);
                    })
                    ->when(EscolaContext::getEscolaAtual(), function ($q) {
                        $q->where('escola_id', EscolaContext::getEscolaAtual());
                    });
                }
            ])
            // Compatível com Postgres: usar whereHas para filtrar por itens com empréstimos no período
            ->whereHas('emprestimos', function ($q) use ($dataInicio, $dataFim) {
                $q->when($dataInicio, function ($qi) use ($dataInicio) {
                    return $qi->whereDate('data_emprestimo', '>=', $dataInicio);
                })
                ->when($dataFim, function ($qi) use ($dataFim) {
                    return $qi->whereDate('data_emprestimo', '<=', $dataFim);
                })
                ->when(EscolaContext::getEscolaAtual(), function ($qi) {
                    $qi->where('escola_id', EscolaContext::getEscolaAtual());
                });
            })
            ->orderByDesc('emprestimos_count')
            ->paginate(50);

        return view('biblioteca.relatorios.itens_populares', compact('itens'))->render();
    }

    /**
     * Relatório de estatísticas gerais
     */
    private function relatorioEstatisticasGerais($dataInicio, $dataFim)
    {
        $escolaId = EscolaContext::getEscolaAtual();
        $estatisticas = [
            'total_emprestimos' => Emprestimo::when($dataInicio, function ($q) use ($dataInicio) {
                    return $q->whereDate('data_emprestimo', '>=', $dataInicio);
                })
                ->when($dataFim, function ($q) use ($dataFim) {
                    return $q->whereDate('data_emprestimo', '<=', $dataFim);
                })
                ->when($escolaId, fn($q) => $q->where('escola_id', $escolaId))
                ->count(),
            
            'emprestimos_ativos' => Emprestimo::where('status', 'ativo')
                ->when($dataInicio, function ($q) use ($dataInicio) {
                    return $q->whereDate('data_emprestimo', '>=', $dataInicio);
                })
                ->when($dataFim, function ($q) use ($dataFim) {
                    return $q->whereDate('data_emprestimo', '<=', $dataFim);
                })
                ->when($escolaId, fn($q) => $q->where('escola_id', $escolaId))
                ->count(),
            
            'emprestimos_devolvidos' => Emprestimo::where('status', 'devolvido')
                ->when($dataInicio, function ($q) use ($dataInicio) {
                    return $q->whereDate('data_emprestimo', '>=', $dataInicio);
                })
                ->when($dataFim, function ($q) use ($dataFim) {
                    return $q->whereDate('data_emprestimo', '<=', $dataFim);
                })
                ->when($escolaId, fn($q) => $q->where('escola_id', $escolaId))
                ->count(),
            
            'emprestimos_atrasados' => Emprestimo::where('status', 'ativo')
                ->where('data_prevista', '<', now())
                ->when($dataInicio, function ($q) use ($dataInicio) {
                    return $q->whereDate('data_emprestimo', '>=', $dataInicio);
                })
                ->when($dataFim, function ($q) use ($dataFim) {
                    return $q->whereDate('data_emprestimo', '<=', $dataFim);
                })
                ->when($escolaId, fn($q) => $q->where('escola_id', $escolaId))
                ->count(),
            
            'total_reservas' => Reserva::when($dataInicio, function ($q) use ($dataInicio) {
                    return $q->whereDate('data_reserva', '>=', $dataInicio);
                })
                ->when($dataFim, function ($q) use ($dataFim) {
                    return $q->whereDate('data_reserva', '<=', $dataFim);
                })
                ->when($escolaId, fn($q) => $q->where('escola_id', $escolaId))
                ->count(),
            
            'reservas_ativas' => Reserva::where('status', 'ativa')
                ->when($dataInicio, function ($q) use ($dataInicio) {
                    return $q->whereDate('data_reserva', '>=', $dataInicio);
                })
                ->when($dataFim, function ($q) use ($dataFim) {
                    return $q->whereDate('data_reserva', '<=', $dataFim);
                })
                ->when($escolaId, fn($q) => $q->where('escola_id', $escolaId))
                ->count(),
            
            'total_multas' => Multa::when($dataInicio, function ($q) use ($dataInicio) {
                    return $q->whereDate('data_multa', '>=', $dataInicio);
                })
                ->when($dataFim, function ($q) use ($dataFim) {
                    return $q->whereDate('data_multa', '<=', $dataFim);
                })
                ->when($escolaId, fn($q) => $q->where('escola_id', $escolaId))
                ->count(),
            
            'valor_total_multas' => Multa::when($dataInicio, function ($q) use ($dataInicio) {
                    return $q->whereDate('data_multa', '>=', $dataInicio);
                })
                ->when($dataFim, function ($q) use ($dataFim) {
                    return $q->whereDate('data_multa', '<=', $dataFim);
                })
                ->when($escolaId, fn($q) => $q->where('escola_id', $escolaId))
                ->sum('valor'),
        ];

        // Estatísticas por tipo de item (agregação segura para PostgreSQL)
        // Usa JOIN com emprestimos e agrupa por tipo, retornando par tipo => total
        $estatisticasPorTipo = Emprestimo::join('item_biblioteca', 'emprestimos.item_id', '=', 'item_biblioteca.id')
            ->when($dataInicio, function ($q) use ($dataInicio) {
                return $q->whereDate('data_emprestimo', '>=', $dataInicio);
            })
            ->when($dataFim, function ($q) use ($dataFim) {
                return $q->whereDate('data_emprestimo', '<=', $dataFim);
            })
            ->when($escolaId, function ($q) use ($escolaId) {
                $q->where('emprestimos.escola_id', $escolaId)
                  ->where('item_biblioteca.escola_id', $escolaId);
            })
            ->selectRaw('item_biblioteca.tipo as tipo, COUNT(emprestimos.id) AS total_emprestimos')
            ->groupBy('item_biblioteca.tipo')
            ->orderByDesc('total_emprestimos')
            ->pluck('total_emprestimos', 'tipo');

        return view('biblioteca.relatorios.estatisticas_gerais', compact(
            'estatisticas', 
            'estatisticasPorTipo'
        ))->render();
    }

    /**
     * Obtém dados para os gráficos
     */
    private function getDadosGraficos()
    {
        $escolaId = EscolaContext::getEscolaAtual();
        // Empréstimos por mês (últimos 12 meses)
        $emprestimosUltimos12Meses = [];
        $meses = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $data = Carbon::now()->subMonths($i);
            $meses[] = $data->format('M/Y');
            
            $count = Emprestimo::whereYear('data_emprestimo', $data->year)
                ->whereMonth('data_emprestimo', $data->month)
                ->when($escolaId, fn($q) => $q->where('escola_id', $escolaId))
                ->count();
            
            $emprestimosUltimos12Meses[] = $count;
        }

        // Tipos de itens mais emprestados (agregação segura para PostgreSQL)
        $tiposDados = Emprestimo::join('item_biblioteca', 'emprestimos.item_id', '=', 'item_biblioteca.id')
            ->selectRaw('item_biblioteca.tipo as tipo, COUNT(emprestimos.id) AS total_emprestimos')
            ->when($escolaId, function ($q) use ($escolaId) {
                $q->where('emprestimos.escola_id', $escolaId)
                  ->where('item_biblioteca.escola_id', $escolaId);
            })
            ->groupBy('item_biblioteca.tipo')
            ->orderByDesc('total_emprestimos')
            ->get();

        $tiposLabels = $tiposDados->pluck('tipo')->map(function ($tipo) {
            return ucfirst($tipo);
        })->toArray();
        
        $tiposContadores = $tiposDados->pluck('total_emprestimos')->toArray();

        return [
            'meses' => $meses,
            'emprestimos' => $emprestimosUltimos12Meses,
            'tipos_labels' => $tiposLabels,
            'tipos_dados' => $tiposContadores,
        ];
    }

    /**
     * Obtém dados do relatório para exportação
     */
    private function getDadosRelatorio($tipo, $dataInicio, $dataFim, $status, $habilitado)
    {
        switch ($tipo) {
            case 'emprestimos':
                $qEmp = Emprestimo::with(['usuario', 'item'])
                    ->when($dataInicio, function ($q) use ($dataInicio) {
                        return $q->whereDate('data_emprestimo', '>=', $dataInicio);
                    })
                    ->when($dataFim, function ($q) use ($dataFim) {
                        return $q->whereDate('data_emprestimo', '<=', $dataFim);
                    })
                    ->when($status, function ($q) use ($status) {
                        if ($status === 'atrasado') {
                            return $q->where('status', 'ativo')
                                     ->where('data_prevista', '<', now());
                        }
                        return $q->where('status', $status);
                    })
                    ->when(!is_null($habilitado), function ($q) use ($habilitado) {
                        return $q->whereHas('item', function ($qi) use ($habilitado) {
                            $qi->where('habilitado_emprestimo', $habilitado);
                        });
                    })
                    ->orderBy('data_emprestimo', 'desc');
                $qEmp = EscolaContext::aplicarFiltroEscola($qEmp);
                return $qEmp->get();

            case 'reservas':
                $qRes = Reserva::with(['usuario', 'item'])
                    ->when($dataInicio, function ($q) use ($dataInicio) {
                        return $q->whereDate('data_reserva', '>=', $dataInicio);
                    })
                    ->when($dataFim, function ($q) use ($dataFim) {
                        return $q->whereDate('data_reserva', '<=', $dataFim);
                    })
                    ->when($status, function ($q) use ($status) {
                        return $q->where('status', $status);
                    })
                    ->when(!is_null($habilitado), function ($q) use ($habilitado) {
                        return $q->whereHas('item', function ($qi) use ($habilitado) {
                            $qi->where('habilitado_emprestimo', $habilitado);
                        });
                    })
                    ->orderBy('data_reserva', 'desc');
                $qRes = EscolaContext::aplicarFiltroEscola($qRes);
                return $qRes->get();

            case 'multas':
                $qMul = Multa::with(['usuario', 'emprestimo.item'])
                    ->when($dataInicio, function ($q) use ($dataInicio) {
                        return $q->whereDate('data_multa', '>=', $dataInicio);
                    })
                    ->when($dataFim, function ($q) use ($dataFim) {
                        return $q->whereDate('data_multa', '<=', $dataFim);
                    })
                    ->when($status, function ($q) use ($status) {
                        return $q->where('status', $status);
                    })
                    ->orderBy('data_multa', 'desc');
                $qMul = EscolaContext::aplicarFiltroEscola($qMul);
                return $qMul->get();

            default:
                return collect();
        }
    }
}