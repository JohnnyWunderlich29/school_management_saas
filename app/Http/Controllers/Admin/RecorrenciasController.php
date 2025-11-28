<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Finance\Subscription;
use App\Models\Finance\ChargeMethod;
use App\Models\Finance\Invoice;
use Illuminate\Support\Facades\Validator;

class RecorrenciasController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->string('status')->toString();
        $method = $request->string('method')->toString();
        $de = $request->string('de')->toString();
        $ate = $request->string('ate')->toString();
        $sort = $request->string('sort')->toString();
        $direction = $request->string('direction')->toString();

        $schoolId = auth()->user()->escola_id ?? null;

        $query = Subscription::query();
        if ($schoolId) {
            $query->where('school_id', $schoolId);
        }

        if (!empty($status)) {
            $query->where('status', $status);
        }

        if (!empty($method)) {
            // Filtrar por método através da tabela charge_methods
            $methodIds = ChargeMethod::query()
                ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
                ->where('method', $method)
                ->pluck('id');
            $query->whereIn('charge_method_id', $methodIds);
        }

        if (!empty($de)) {
            $query->whereDate('start_at', '>=', $de);
        }
        if (!empty($ate)) {
            $query->whereDate('start_at', '<=', $ate);
        }

        // Carregar relações e último faturamento via subselect
        $query->with(['payer', 'chargeMethod'])
            ->select('subscriptions.*')
            ->selectSub(
                Invoice::query()
                    ->selectRaw('MAX(due_date)')
                    ->whereColumn('subscription_id', 'subscriptions.id'),
                'last_invoice_date'
            );

        // Ordenação dinâmica
        if (!empty($sort)) {
            $query->orderBy($sort, in_array($direction, ['asc','desc']) ? $direction : 'desc');
        } else {
            $query->orderByDesc('start_at');
        }

        $recorrencias = $query->paginate(15)->appends($request->query());

        $statusOptions = [
            'active' => 'Ativa',
            'paused' => 'Pausada',
            'canceled' => 'Cancelada',
            'ended' => 'Encerrada',
        ];

        // Opções de métodos configurados para a escola (para filtro)
        $methodOptions = ChargeMethod::query()
            ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
            ->select('method')
            ->distinct()
            ->pluck('method')
            ->filter()
            ->values();

        // Lista de métodos com IDs (para modal de alteração)
        $methodList = ChargeMethod::query()
            ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
            ->orderBy('method')
            ->get(['id','method']);

        $breadcrumbs = [
            ['title' => 'Administração', 'url' => route('dashboard')],
            ['title' => 'Recorrências', 'url' => route('admin.recorrencias.index')],
        ];

        return view('admin.recorrencias.index', compact('recorrencias', 'statusOptions', 'methodOptions', 'methodList', 'breadcrumbs'));
    }

    protected function ensureSameSchool(Subscription $subscription): void
    {
        $schoolId = auth()->user()->escola_id ?? null;
        if ($schoolId && (int)$subscription->school_id !== (int)$schoolId) {
            abort(403, 'Assinatura pertence a outra escola');
        }
    }

    public function updateMethod(Request $request, Subscription $subscription)
    {
        $this->ensureSameSchool($subscription);
        $validator = Validator::make($request->all(), [
            'charge_method_id' => 'required|integer|min:1',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $methodId = (int)$validator->validated()['charge_method_id'];
        $schoolId = auth()->user()->escola_id ?? null;
        $method = ChargeMethod::where('id', $methodId)
            ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
            ->first();
        if (!$method) {
            return response()->json(['message' => 'Método inválido para esta escola'], 422);
        }
        $subscription->charge_method_id = $method->id;
        $subscription->save();
        $subscription->load('chargeMethod');
        return response()->json([
            'message' => 'Método de cobrança atualizado',
            'method' => $subscription->chargeMethod?->method,
        ]);
    }

    public function pause(Request $request, Subscription $subscription)
    {
        $this->ensureSameSchool($subscription);
        if (in_array($subscription->status, ['canceled','ended'])) {
            return response()->json(['message' => 'Assinatura já encerrada/cancelada'], 422);
        }
        $subscription->status = 'paused';
        $subscription->save();
        return response()->json(['message' => 'Assinatura pausada', 'status' => $subscription->status]);
    }

    public function resume(Request $request, Subscription $subscription)
    {
        $this->ensureSameSchool($subscription);
        if (in_array($subscription->status, ['canceled','ended'])) {
            return response()->json(['message' => 'Assinatura encerrada/cancelada não pode ser retomada'], 422);
        }
        $subscription->status = 'active';
        $subscription->save();
        return response()->json(['message' => 'Assinatura retomada', 'status' => $subscription->status]);
    }

    public function cancel(Request $request, Subscription $subscription)
    {
        $this->ensureSameSchool($subscription);
        if ($subscription->status === 'canceled') {
            return response()->json(['message' => 'Assinatura já cancelada'], 422);
        }
        $subscription->status = 'canceled';
        if (!$subscription->end_at) {
            $subscription->end_at = now();
        }
        $subscription->save();
        return response()->json(['message' => 'Assinatura cancelada', 'status' => $subscription->status, 'end_at' => $subscription->end_at]);
    }
}