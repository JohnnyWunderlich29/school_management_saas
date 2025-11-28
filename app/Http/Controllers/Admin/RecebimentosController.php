<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Finance\Invoice;
use Illuminate\Http\JsonResponse;

class RecebimentosController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->string('status')->toString();
        // Default para exibir apenas pendentes quando nenhum filtro for informado
        if (empty($status)) {
            $status = 'pending';
        }
        $gateway = $request->string('gateway')->toString();
        $de = $request->string('de')->toString();
        $ate = $request->string('ate')->toString();
        $sort = $request->string('sort')->toString();
        $direction = $request->string('direction', 'desc')->toString();

        $schoolId = auth()->user()->escola_id ?? null;

        $query = Invoice::query();
        if ($schoolId) {
            $query->where('invoices.school_id', $schoolId);
        }

        // Sempre aplicar filtro de status (por padrão, 'pending')
        $query->where('invoices.status', $status);

        if (!empty($gateway)) {
            $query->where('invoices.gateway_alias', $gateway);
        }

        if (!empty($de)) {
            $query->whereDate('invoices.due_date', '>=', $de);
        }
        if (!empty($ate)) {
            $query->whereDate('invoices.due_date', '<=', $ate);
        }

        // Incluir informações do pagador (responsável) via join com subscriptions e responsaveis
        $query->leftJoin('subscriptions', 'invoices.subscription_id', '=', 'subscriptions.id')
            ->leftJoin('responsaveis', 'subscriptions.payer_id', '=', 'responsaveis.id')
            ->select('invoices.*', 'responsaveis.id as payer_id', 'responsaveis.nome as payer_nome', 'responsaveis.sobrenome as payer_sobrenome');

        // Implementar ordenação dinâmica
        $allowedSorts = ['due_date', 'number', 'gateway_alias', 'total_cents', 'status', 'payer'];
        $direction = in_array(strtolower($direction), ['asc', 'desc']) ? strtolower($direction) : 'desc';
        if (!empty($sort) && in_array($sort, $allowedSorts)) {
            if ($sort === 'payer') {
                // Ordenar por nome e sobrenome do responsável
                $query->orderBy('responsaveis.nome', $direction)
                      ->orderBy('responsaveis.sobrenome', $direction);
            } else {
                $query->orderBy("invoices.{$sort}", $direction);
            }
        } else {
            // Ordenação padrão
            $query->orderByDesc('invoices.due_date');
        }

        $recebimentos = $query->paginate(15)->appends($request->query());

        $statusOptions = [
            'pending' => 'Pendente',
            'paid' => 'Pago',
            'overdue' => 'Vencido',
            'canceled' => 'Cancelado',
            'failed' => 'Falhou',
        ];

        // Opções de gateways com base nos registros existentes da escola
        $gatewayOptions = Invoice::query()
            ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
            ->select('gateway_alias')
            ->whereNotNull('gateway_alias')
            ->distinct()
            ->pluck('gateway_alias')
            ->filter()
            ->values();

        $breadcrumbs = [
            ['title' => 'Administração', 'url' => route('dashboard')],
            ['title' => 'Recebimentos', 'url' => route('admin.recebimentos.index')],
        ];

        return view('admin.recebimentos.index', compact('recebimentos', 'statusOptions', 'gatewayOptions', 'breadcrumbs'));
    }

    /**
     * Retorna os detalhes da fatura em JSON para consumo via AJAX na tela.
     */
    public function details(Request $request, $invoice): JsonResponse
    {
        $schoolId = auth()->user()->escola_id ?? null;

        $inv = Invoice::query()
            ->when($schoolId, fn($q) => $q->where('invoices.school_id', $schoolId))
            ->leftJoin('subscriptions', 'invoices.subscription_id', '=', 'subscriptions.id')
            ->leftJoin('responsaveis', 'subscriptions.payer_id', '=', 'responsaveis.id')
            ->where('invoices.id', $invoice)
            ->select('invoices.*', 'responsaveis.nome as payer_nome', 'responsaveis.sobrenome as payer_sobrenome')
            ->first();

        if (!$inv) {
            return response()->json(['message' => 'Fatura não encontrada'], 404);
        }

        return response()->json([
            'id' => $inv->id,
            'number' => $inv->number,
            'status' => $inv->status,
            'total_cents' => $inv->total_cents,
            'due_date' => $inv->due_date ? $inv->due_date->toDateString() : null,
            'gateway_alias' => $inv->gateway_alias,
            'gateway_status' => $inv->gateway_status,
            'boleto_url' => $inv->boleto_url,
            'pix_qr_code' => $inv->pix_qr_code,
            'pix_code' => $inv->pix_code,
            'barcode' => $inv->barcode,
            'linha_digitavel' => $inv->linha_digitavel,
            'payer_nome' => $inv->payer_nome ?? null,
            'payer_sobrenome' => $inv->payer_sobrenome ?? null,
        ]);
    }
}