<?php

namespace App\Http\Controllers\Finance;

use App\Models\Finance\Payment;
use App\Models\Finance\Invoice;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class PaymentsController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::query();
        if ($request->filled('invoice_id')) $query->where('invoice_id', (int)$request->get('invoice_id'));
        if ($request->filled('status')) $query->where('status', $request->get('status'));
        if ($request->filled('method')) $query->where('method', $request->get('method'));
        $payments = $query->orderByDesc('paid_at')->paginate($request->get('per_page', 15));
        return response()->json($payments);
    }

    public function show(Request $request, int $id)
    {
        $payment = Payment::findOrFail($id);
        return response()->json($payment);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'invoice_id' => 'required|integer|min:1',
            'amount_paid_cents' => 'required|integer|min:0',
            'paid_at' => 'nullable|date',
            'method' => 'required|string|in:boleto,pix,card,cash,transfer',
            'gateway_fee_cents' => 'nullable|integer|min:0',
            'net_amount_cents' => 'nullable|integer|min:0',
            'currency' => 'nullable|string|max:8',
            'gateway_payment_id' => 'nullable|string|max:128',
            'status' => 'nullable|string|in:received,confirmed,refunded,canceled',
            'settled_at' => 'nullable|date',
            'settlement_ref' => 'nullable|string|max:128',
            'reconciliation_status' => 'nullable|string|in:pending,matched,mismatch',
        ]);
        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);
        $data = $validator->validated();
        $payment = new Payment($data);
        $payment->currency = $data['currency'] ?? 'BRL';
        $payment->status = $data['status'] ?? 'received';
        if (!isset($data['net_amount_cents']) && isset($data['gateway_fee_cents'])) {
            $payment->net_amount_cents = max(0, (int)$data['amount_paid_cents'] - (int)$data['gateway_fee_cents']);
        }
        $payment->save();
        Log::info('Payment created', [
            'payment_id' => $payment->id,
            'invoice_id' => $payment->invoice_id,
            'amount_paid_cents' => $payment->amount_paid_cents,
            'status' => $payment->status,
            'user_id' => optional($request->user())->id,
        ]);

        // Marcar fatura como paga quando o pagamento é confirmado manualmente
        if ($payment->status === 'confirmed') {
            $invoice = Invoice::find($payment->invoice_id);
            if ($invoice && $invoice->status !== 'paid') {
                $invoice->status = 'paid';
                if (!$invoice->paid_at) {
                    $invoice->paid_at = $payment->paid_at ?: now();
                }
                // Flag manual settlement for cash/transfer methods
                if (in_array($payment->method, ['cash', 'transfer'])) {
                    $invoice->gateway_status = 'manual';
                }
                $invoice->save();
                Log::info('Invoice marked paid via manual payment confirmation', [
                    'invoice_id' => $invoice->id,
                    'payment_id' => $payment->id,
                ]);
            }
        }

        return response()->json(['id' => $payment->id], 201);
    }

    public function update(Request $request, int $id)
    {
        $validator = Validator::make($request->all(), [
            'amount_paid_cents' => 'nullable|integer|min:0',
            'paid_at' => 'nullable|date',
            'method' => 'nullable|string|in:boleto,pix,card,cash,transfer',
            'gateway_fee_cents' => 'nullable|integer|min:0',
            'net_amount_cents' => 'nullable|integer|min:0',
            'currency' => 'nullable|string|max:8',
            'gateway_payment_id' => 'nullable|string|max:128',
            'status' => 'nullable|string|in:received,confirmed,refunded,canceled',
            'settled_at' => 'nullable|date',
            'settlement_ref' => 'nullable|string|max:128',
            'reconciliation_status' => 'nullable|string|in:pending,matched,mismatch',
        ]);
        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);
        $payment = Payment::findOrFail($id);
        $payment->fill($validator->validated());
        if ($request->filled('amount_paid_cents') && $request->filled('gateway_fee_cents') && !$request->filled('net_amount_cents')) {
            $payment->net_amount_cents = max(0, (int)$request->get('amount_paid_cents') - (int)$request->get('gateway_fee_cents'));
        }
        $payment->save();
        Log::info('Payment updated', [
            'payment_id' => $payment->id,
            'invoice_id' => $payment->invoice_id,
            'status' => $payment->status,
            'user_id' => optional($request->user())->id,
        ]);

        // Marcar fatura como paga quando o pagamento é atualizado para confirmado
        if ($payment->status === 'confirmed') {
            $invoice = Invoice::find($payment->invoice_id);
            if ($invoice && $invoice->status !== 'paid') {
                $invoice->status = 'paid';
                if (!$invoice->paid_at) {
                    $invoice->paid_at = $payment->paid_at ?: now();
                }
                // Flag manual settlement for cash/transfer methods
                if (in_array($payment->method, ['cash', 'transfer'])) {
                    $invoice->gateway_status = 'manual';
                }
                $invoice->save();
                Log::info('Invoice marked paid via payment update to confirmed', [
                    'invoice_id' => $invoice->id,
                    'payment_id' => $payment->id,
                ]);
            }
        }

        return response()->json(['message' => 'updated']);
    }

    public function destroy(Request $request, int $id)
    {
        $payment = Payment::findOrFail($id);
        if ($payment->status === 'confirmed' || $payment->status === 'refunded') {
            return response()->json(['message' => 'cannot delete settled or refunded payments'], 422);
        }
        $payment->delete();
        return response()->json(['message' => 'deleted']);
    }
}