<?php

namespace App\Http\Controllers\Finance;

use App\Models\Finance\Invoice;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Models\Finance\FinanceGateway;
use App\Models\Finance\Subscription;
use App\Models\Finance\ChargeMethod;
use App\Models\Finance\GatewayCustomer;
use App\Services\Payments\GatewayManager;
use App\Services\Payments\AsaasGateway;
use App\Services\Payments\NuPayGateway;
use App\Models\Responsavel;
use App\Models\Historico; // Added for history logging
use App\Services\AlertService; // Added for alert standardization
use Illuminate\Support\Facades\Mail; // Added to send emails
use App\Mail\DunningReminder; // Mailable for invoice reminder
use Illuminate\Support\Carbon;

class InvoicesController extends Controller
{
    public function index(Request $request)
    {

        $schoolId = $this->resolveSchoolId($request);
        if (!$schoolId)
            return response()->json(['message' => 'school_id required'], 422);
        $query = Invoice::where('school_id', $schoolId);
        // Suporte a filtro direto por payer_id (via subscriptions)
        if ($request->filled('payer_id')) {
            $subIds = Subscription::where('school_id', $schoolId)
                ->where('payer_id', (int) $request->get('payer_id'))
                ->pluck('id');
            if ($subIds->isNotEmpty()) {
                $query->whereIn('subscription_id', $subIds);
            } else {
                $query->whereRaw('0=1');
            }
        }
        if ($request->filled('subscription_id'))
            $query->where('subscription_id', (int) $request->get('subscription_id'));
        if ($request->filled('status'))
            $query->where('status', $request->get('status'));
        if ($request->filled('due_from'))
            $query->whereDate('due_date', '>=', $request->get('due_from'));
        if ($request->filled('due_to'))
            $query->whereDate('due_date', '<=', $request->get('due_to'));
        $invoices = $query->orderBy('due_date')->paginate($request->get('per_page', 15));

        return response()->json($invoices);
    }

    public function show(Request $request, int $id)
    {
        $schoolId = $this->resolveSchoolId($request);
        if (!$schoolId)
            return response()->json(['message' => 'school_id required'], 422);
        $invoice = Invoice::where('school_id', $schoolId)->findOrFail($id);
        return response()->json($invoice);
    }

    public function store(Request $request)
    {
        $schoolId = $this->resolveSchoolId($request);
        if (!$schoolId)
            return response()->json(['message' => 'school_id required'], 422);
        $validator = Validator::make($request->all(), [
            'subscription_id' => 'required|integer|min:1',
            'due_date' => 'required|date',
            'total_cents' => 'required|integer|min:0',
            'currency' => 'nullable|string|max:8',
            'status' => 'nullable|string|in:pending,overdue,paid,canceled',
            'gateway_alias' => 'nullable|string|max:64',
            'description' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1024',
        ]);
        if ($validator->fails()) {
            // Padronizar resposta de validação para o frontend (ValidationHandler + AlertSystem)
            $processed = AlertService::validationErrors($validator->errors()->toArray());
            return response()->json([
                'success' => false,
                'message' => 'Por favor, corrija os erros de validação.',
                'errors' => $validator->errors(),
                'processed_errors' => $processed,
            ], 422);
        }
        $data = $validator->validated();

        // Garantir que a assinatura pertence à mesma escola para evitar inconsistências de filtro
        $sub = Subscription::where('school_id', $schoolId)->find($data['subscription_id']);
        if (!$sub) {
            return response()->json([
                'success' => false,
                'message' => 'Assinatura não encontrada para esta escola.',
                'errors' => ['subscription_id' => ['Assinatura inválida para a escola selecionada']],
            ], 422);
        }

        $invoice = new Invoice($data);
        $invoice->school_id = $schoolId;
        $invoice->status = $data['status'] ?? 'pending';
        $invoice->currency = $data['currency'] ?? 'BRL';
        $invoice->number = $this->generateNumber($schoolId);
        $invoice->save();

        // Update subscription last_billed_at
        if ($sub && $invoice->due_date) {
            $sub->last_billed_at = $invoice->due_date;
            $sub->save();
        }
        Log::info('Invoice created', [
            'invoice_id' => $invoice->id,
            'school_id' => $schoolId,
            'user_id' => optional($request->user())->id,
            'subscription_id' => $invoice->subscription_id,
            'total_cents' => $invoice->total_cents,
            'status' => $invoice->status,
        ]);

        // Registrar no histórico (padronização)
        try {
            Historico::registrar(
                'criado',
                'Invoice',
                $invoice->id,
                null,
                $invoice->fresh()->toArray(),
                'Fatura ' . $invoice->number . ' criada'
            );
        } catch (\Throwable $e) {
            Log::warning('Falha ao registrar histórico de criação de fatura', ['invoice_id' => $invoice->id, 'error' => $e->getMessage()]);
        }

        // Try direct ASAAS charge creation after saving invoice
        $result = ['id' => $invoice->id];
        try {
            // Determine gateway alias/method from subscription charge method
            $sub = Subscription::where('school_id', $schoolId)->find($invoice->subscription_id);
            $alias = $data['gateway_alias'] ?? null;
            $method = null;
            if ($sub) {
                $cm = ChargeMethod::where('school_id', $schoolId)->find($sub->charge_method_id);
                if ($cm) {
                    $alias = $alias ?: $cm->gateway_alias;
                    $method = $cm->method;
                }
            }
            if (!$alias) {
                // Fallback: use invoice gateway_alias if provided
                $alias = $invoice->gateway_alias ?: null;
            }
            if (!$alias) {
                // Fallback: use default active charge method for the school
                $defaultCm = ChargeMethod::where('school_id', $schoolId)
                    ->where('active', true)
                    ->orderBy('gateway_alias')
                    ->orderBy('method')
                    ->first();
                if ($defaultCm) {
                    $alias = $defaultCm->gateway_alias;
                    $method = $method ?: $defaultCm->method;
                }
            }

            // Normalize alias/method and log resolution
            $alias = strtolower((string) $alias);
            if ($alias === 'assas') {
                $alias = 'asaas';
            }
            $method = strtolower((string) ($method ?? ''));
            // Persist resolved alias into invoice if missing
            if (!$invoice->gateway_alias && $alias) {
                $invoice->gateway_alias = $alias;
                $invoice->save();
            }
            Log::info('Invoice gateway resolved', [
                'invoice_id' => $invoice->id,
                'alias' => $alias,
                'method' => $method,
            ]);

            if (in_array($alias, ['asaas', 'assas'])) {
                $config = FinanceGateway::where('school_id', $schoolId)->where('alias', $alias)->first();
                if ($config && $config->active) {
                    $gm = new GatewayManager();
                    $gm->register(new AsaasGateway());
                    $gw = $gm->forAlias('asaas', $config);

                    // Resolve payer
                    $payerModel = $sub ? Responsavel::find($sub->payer_id) : null;
                    $cpf = $payerModel ? preg_replace('/\D/', '', (string) ($payerModel->cpf ?? '')) : null;
                    $payer = [
                        'name' => $payerModel ? trim(($payerModel->nome ?? '') . ' ' . ($payerModel->sobrenome ?? '')) : null,
                        'email' => $payerModel->email ?? null,
                        'cpfCnpj' => $cpf ?: null,
                        'phone' => $payerModel->telefone_secundario ?? null,
                        'mobilePhone' => $payerModel->telefone_principal ?? null,
                        'address' => $payerModel->endereco ?? null,
                        'postalCode' => $payerModel->cep ?? null,
                        'city' => $payerModel->cidade ?? null,
                        'state' => $payerModel->estado ?? null,
                    ];
                    $gc = null;
                    if ($payerModel) {
                        $gc = GatewayCustomer::where('school_id', $schoolId)
                            ->where('gateway_alias', 'asaas')
                            ->where('payer_id', $payerModel->id)
                            ->first();
                    }
                    if ($gc) {
                        $payer['external_id'] = $gc->external_customer_id;
                        $payer['externalReference'] = (string) ($payerModel->id);
                    }
                    $cust = $gw->createOrUpdateCustomer(['school_id' => $schoolId], $payer);
                    $customerId = $cust['id'] ?? null;
                    if (!$customerId) {
                        Log::warning('ASAAS customer creation failed', ['invoice_id' => $invoice->id, 'error' => $cust['error'] ?? null]);
                        $status = $cust['status'] ?? null;
                        $err = $cust['error'] ?? null;
                        $code = is_array($err) ? ($err['errors'][0]['code'] ?? ($err['code'] ?? null)) : null;
                        $msg = is_array($err) ? ($err['errors'][0]['description'] ?? ($err['message'] ?? json_encode($err))) : (is_string($err) ? $err : null);
                        $invoice->gateway_status = $status ? (string) $status : null;
                        $invoice->gateway_error_code = $code ? (string) $code : null;
                        $invoice->gateway_error = $msg;
                        $invoice->save();
                    } else {
                        // Persist mapping if new
                        if (!$gc && $payerModel) {
                            $gc = new GatewayCustomer([
                                'school_id' => $schoolId,
                                'payer_id' => $payerModel->id,
                                'gateway_alias' => 'asaas',
                                'external_customer_id' => $customerId,
                                'status' => 'active',
                            ]);
                            $gc->save();
                        }

                        // Create charge (fix method normalization)
                        $mt = in_array($method, ['pix', 'boleto']) ? $method : 'boleto';
                        $billingType = $mt === 'pix' ? 'PIX' : 'BOLETO';
                        Log::info('ASAAS charge attempt', [
                            'invoice_id' => $invoice->id,
                            'customer_id' => $customerId,
                            'billingType' => $billingType,
                            'amount_cents' => $invoice->total_cents,
                            'due_date' => $invoice->due_date ? $invoice->due_date->toDateString() : null,
                        ]);
                        $charge = $gw->createCharge([
                            'customer_id' => $customerId,
                            'method' => $billingType,
                            'amount_cents' => $invoice->total_cents,
                            'description' => $data['description'] ?? null,
                            'due_date' => $invoice->due_date ? $invoice->due_date->toDateString() : null,
                            'invoice_id' => $invoice->id,
                            'invoice_number' => $invoice->number,
                        ]);
                        if (!empty($charge['charge_id'])) {
                            $invoice->charge_id = $charge['charge_id'];
                            $invoice->boleto_url = $charge['boleto_url'] ?? null;
                            $invoice->linha_digitavel = $charge['linha_digitavel'] ?? null;
                            $invoice->barcode = $charge['barcode'] ?? null;
                            $invoice->pix_qr_code = $charge['pix_qr_code'] ?? null;
                            $invoice->pix_code = $charge['pix_code'] ?? null;
                            $invoice->save();
                            Log::info('ASAAS charge created', [
                                'invoice_id' => $invoice->id,
                                'charge_id' => $invoice->charge_id,
                                'billingType' => $billingType,
                            ]);
                            $result = array_merge($result, [
                                'charge_id' => $invoice->charge_id,
                                'boleto_url' => $invoice->boleto_url,
                                'linha_digitavel' => $invoice->linha_digitavel,
                                'barcode' => $invoice->barcode,
                                'pix_qr_code' => $invoice->pix_qr_code,
                                'pix_code' => $invoice->pix_code,
                            ]);
                        } else {
                            Log::warning('ASAAS charge creation failed', ['invoice_id' => $invoice->id, 'error' => $charge['error'] ?? null, 'status' => $charge['status'] ?? null]);
                            $status = $charge['status'] ?? null;
                            $err = $charge['error'] ?? null;
                            $code = is_array($err) ? ($err['errors'][0]['code'] ?? ($err['code'] ?? null)) : null;
                            $msg = is_array($err) ? ($err['errors'][0]['description'] ?? ($err['message'] ?? json_encode($err))) : (is_string($err) ? $err : null);
                            $invoice->gateway_status = $status ? (string) $status : null;
                            $invoice->gateway_error_code = $code ? (string) $code : null;
                            $invoice->gateway_error = $msg;
                            $invoice->save();
                        }
                    }
                } else {
                    Log::warning('ASAAS gateway missing or inactive', [
                        'invoice_id' => $invoice->id,
                        'school_id' => $schoolId,
                        'alias' => $alias,
                    ]);
                }
            }
            if ($alias === 'nupay') {
                $config = FinanceGateway::where('school_id', $schoolId)->where('alias', $alias)->first();
                if ($config && $config->active) {
                    $gm = new GatewayManager();
                    $gm->register(new NuPayGateway());
                    $gw = $gm->forAlias('nupay', $config);

                    // Resolve payer (shopper) para NuPay
                    $payerModel = $sub ? Responsavel::find($sub->payer_id) : null;
                    $firstName = null;
                    $lastName = null;
                    if ($payerModel) {
                        $names = trim(($payerModel->nome ?? '') . ' ' . ($payerModel->sobrenome ?? ''));
                        $parts = preg_split('/\s+/', $names, -1, PREG_SPLIT_NO_EMPTY);
                        $firstName = $parts[0] ?? null;
                        $lastName = isset($parts[1]) ? implode(' ', array_slice($parts, 1)) : null;
                    }
                    $cpf = $payerModel ? preg_replace('/\D/', '', (string) ($payerModel->cpf ?? '')) : null;

                    $invoiceContext = [
                        'invoice_id' => (string) $invoice->id,
                        'invoice_number' => (string) ($invoice->number ?? ''),
                        'description' => $invoice->description ?? null,
                        'amount_cents' => (int) $invoice->total_cents,
                        'payer' => [
                            'firstName' => $firstName,
                            'lastName' => $lastName,
                            'cpf' => $cpf,
                            'email' => $payerModel->email ?? null,
                            'mobilePhone' => $payerModel->telefone_principal ?? null,
                            'phone' => $payerModel->telefone_secundario ?? null,
                        ],
                        // opcional: retornos
                        'return_url' => url('/finance/settings'),
                        'order_url' => route('finance.settings'),
                        'authorization_type' => 'manually_authorized',
                    ];

                    $created = $gw->createCharge($invoiceContext);
                    if (!empty($created['charge_id'])) {
                        $invoice->charge_id = (string) $created['charge_id'];
                        $invoice->save();
                        $result['gateway'] = 'nupay';
                        $result['charge_id'] = $invoice->charge_id;
                        if (!empty($created['payment_url'])) {
                            $result['payment_url'] = $created['payment_url'];
                        }
                    } else {
                        $error = $created['error'] ?? ['message' => 'Falha ao criar pedido NuPay'];
                        return response()->json(['message' => 'Falha ao criar cobrança no NuPay', 'error' => $error, 'invoice' => $invoice], 502);
                    }
                } else {
                    return response()->json(['message' => 'Gateway NuPay inativo ou não configurado'], 409);
                }
            }
        } catch (\Throwable $e) {
            Log::error('Direct ASAAS charge exception', ['invoice_id' => $invoice->id, 'message' => $e->getMessage()]);
        }

        // Mensagem de sucesso padronizada (sem quebrar formato esperado pelo frontend)
        AlertService::success('Fatura criada com sucesso!');
        return response()->json(array_merge([
            'success' => true,
            'message' => 'Fatura criada com sucesso!'
        ], $result), 201);
    }

    public function update(Request $request, int $id)
    {
        $schoolId = $this->resolveSchoolId($request);
        if (!$schoolId)
            return response()->json(['message' => 'school_id required'], 422);
        $validator = Validator::make($request->all(), [
            'due_date' => 'nullable|date',
            'total_cents' => 'nullable|integer|min:0',
            'currency' => 'nullable|string|max:8',
            'status' => 'nullable|string|in:pending,overdue,paid,canceled',
            'gateway_alias' => 'nullable|string|max:64',
            'charge_id' => 'nullable|string|max:128',
            'boleto_url' => 'nullable|string',
            'barcode' => 'nullable|string|max:256',
            'linha_digitavel' => 'nullable|string|max:256',
            'pix_qr_code' => 'nullable|string',
            'pix_code' => 'nullable|string',
        ]);
        if ($validator->fails()) {
            $processed = AlertService::validationErrors($validator->errors()->toArray());
            return response()->json([
                'success' => false,
                'message' => 'Por favor, corrija os erros de validação.',
                'errors' => $validator->errors(),
                'processed_errors' => $processed,
            ], 422);
        }
        $invoice = Invoice::where('school_id', $schoolId)->findOrFail($id);
        $dadosAntigos = $invoice->toArray();
        $invoice->fill($validator->validated());
        $invoice->save();
        Log::info('Invoice updated', [
            'invoice_id' => $invoice->id,
            'school_id' => $schoolId,
            'user_id' => optional($request->user())->id,
            'status' => $invoice->status,
        ]);

        // Registrar no histórico (padronização)
        try {
            Historico::registrar(
                'atualizado',
                'Invoice',
                $invoice->id,
                $dadosAntigos,
                $invoice->fresh()->toArray(),
                'Fatura ' . $invoice->number . ' atualizada'
            );
        } catch (\Throwable $e) {
            Log::warning('Falha ao registrar histórico de atualização de fatura', ['invoice_id' => $invoice->id, 'error' => $e->getMessage()]);
        }

        AlertService::success('Fatura atualizada com sucesso!');
        return response()->json([
            'success' => true,
            'message' => 'Fatura atualizada com sucesso!',
            'invoice' => $invoice,
        ]);
    }

    public function syncGatewayStatus(Request $request, int $id)
    {
        $schoolId = $this->resolveSchoolId($request);
        if (!$schoolId)
            return response()->json(['message' => 'school_id required'], 422);

        $invoice = Invoice::where('school_id', $schoolId)->findOrFail($id);
        if (!$invoice->charge_id) {
            // Resolver alias do gateway: invoice -> assinatura -> método padrão da escola
            $alias = strtolower((string) ($invoice->gateway_alias ?? ''));
            if (!$alias) {
                $sub = Subscription::where('school_id', $schoolId)->find($invoice->subscription_id);
                $cm = $sub ? ChargeMethod::where('school_id', $schoolId)->find($sub->charge_method_id) : null;
                if ($cm && $cm->gateway_alias) {
                    $alias = strtolower((string) $cm->gateway_alias);
                } else {
                    $defaultCm = ChargeMethod::where('school_id', $schoolId)->where('active', true)
                        ->orderBy('gateway_alias')->orderBy('method')->first();
                    if ($defaultCm) {
                        $alias = strtolower((string) $defaultCm->gateway_alias);
                    }
                }
                if ($alias) {
                    $invoice->gateway_alias = $alias;
                    $invoice->save();
                }
            }
            if ($alias === 'assas') {
                $alias = 'asaas';
            }
            // Tentativa de recuperação via externalReference no gateway
            $config = FinanceGateway::where('school_id', $schoolId)->where('alias', $alias)->first();
            if ($config && $config->active) {
                $gm = new GatewayManager();
                $gm->register(new AsaasGateway());
                $gw = $gm->forAlias($alias, $config);
                // externalReference usado no create: invoice_id ou invoice_number
                $found = null;
                $refId = (string) $invoice->id;
                if ($refId !== '') {
                    $found = method_exists($gw, 'findPaymentByExternalReference') ? $gw->findPaymentByExternalReference($refId) : null;
                }
                if ((!$found || empty($found['id'])) && !empty($invoice->number)) {
                    $found = method_exists($gw, 'findPaymentByExternalReference') ? $gw->findPaymentByExternalReference((string) $invoice->number) : null;
                }
                if ($found && !empty($found['id'])) {
                    // Atualiza a fatura com charge_id e segue fluxo normal
                    $invoice->charge_id = (string) $found['id'];
                    $invoice->save();
                } else {
                    // Fallback: criar cobrança no gateway se não encontrada por externalReference
                    $sub = Subscription::where('school_id', $schoolId)->find($invoice->subscription_id);
                    $cm = $sub ? ChargeMethod::where('school_id', $schoolId)->find($sub->charge_method_id) : null;
                    $method = $cm && in_array(strtolower((string) $cm->method), ['pix', 'boleto']) ? strtolower((string) $cm->method) : 'boleto';
                    $billingType = $method === 'pix' ? 'PIX' : 'BOLETO';

                    // Resolver customer
                    $payerModel = $sub ? Responsavel::find($sub->payer_id) : null;
                    $cpf = $payerModel ? preg_replace('/\D/', '', (string) ($payerModel->cpf ?? '')) : null;
                    $payer = [
                        'name' => $payerModel ? trim(($payerModel->nome ?? '') . ' ' . ($payerModel->sobrenome ?? '')) : null,
                        'email' => $payerModel->email ?? null,
                        'cpfCnpj' => $cpf ?: null,
                        'phone' => $payerModel->telefone_secundario ?? null,
                        'mobilePhone' => $payerModel->telefone_principal ?? null,
                        'address' => $payerModel->endereco ?? null,
                        'postalCode' => $payerModel->cep ?? null,
                        'city' => $payerModel->cidade ?? null,
                        'state' => $payerModel->estado ?? null,
                    ];
                    $gc = null;
                    if ($payerModel) {
                        $gc = GatewayCustomer::where('school_id', $schoolId)
                            ->where('gateway_alias', $alias)
                            ->where('payer_id', $payerModel->id)
                            ->first();
                    }
                    if ($gc) {
                        $payer['external_id'] = $gc->external_customer_id;
                        $payer['externalReference'] = (string) ($payerModel->id);
                    }
                    $cust = $gw->createOrUpdateCustomer(['invoice_id' => $invoice->id], $payer);
                    $customerId = $cust['id'] ?? ($gc ? $gc->external_customer_id : null);
                    if ($customerId && (!$gc || !$gc->external_customer_id)) {
                        $gc = $gc ?: new GatewayCustomer([
                            'school_id' => $schoolId,
                            'payer_id' => $payerModel ? $payerModel->id : null,
                            'gateway_alias' => $alias,
                            'status' => 'active',
                        ]);
                        $gc->external_customer_id = $customerId;
                        $gc->save();
                    }

                    if ($customerId) {
                        $charge = $gw->createCharge([
                            'customer_id' => $customerId,
                            'method' => $billingType,
                            'amount_cents' => $invoice->total_cents,
                            'description' => null,
                            'due_date' => $invoice->due_date ? $invoice->due_date->toDateString() : null,
                            'invoice_id' => $invoice->id,
                            'invoice_number' => $invoice->number,
                        ]);

                        if (!empty($charge['charge_id'])) {
                            $invoice->charge_id = (string) $charge['charge_id'];
                            $invoice->boleto_url = $charge['boleto_url'] ?? null;
                            $invoice->linha_digitavel = $charge['linha_digitavel'] ?? null;
                            $invoice->barcode = $charge['barcode'] ?? null;
                            $invoice->pix_qr_code = $charge['pix_qr_code'] ?? null;
                            $invoice->pix_code = $charge['pix_code'] ?? null;
                            $invoice->save();
                        } else {
                            $status = $charge['status'] ?? null;
                            $err = $charge['error'] ?? null;
                            $code = is_array($err) ? ($err['errors'][0]['code'] ?? ($err['code'] ?? null)) : null;
                            $msg = is_array($err) ? ($err['errors'][0]['description'] ?? ($err['message'] ?? json_encode($err))) : (is_string($err) ? $err : null);
                            $invoice->gateway_status = $status ? (string) $status : null;
                            $invoice->gateway_error_code = $code ? (string) $code : null;
                            $invoice->gateway_error = $msg;
                            $invoice->save();
                            return response()->json(['message' => 'Falha ao criar cobrança no gateway', 'invoice' => $invoice], 502);
                        }
                    } else {
                        return response()->json(['message' => 'charge_id ausente e cliente não resolvido para criação'], 422);
                    }
                }
            } else {
                return response()->json(['message' => 'charge_id ausente para consulta no gateway', 'hint' => 'Gateway inativo ou não configurado'], 422);
            }
        }
        // Resolver alias do gateway
        $alias = strtolower((string) ($invoice->gateway_alias ?? ''));
        if (!$alias) {
            $sub = Subscription::where('school_id', $schoolId)->find($invoice->subscription_id);
            if ($sub) {
                $cm = ChargeMethod::where('school_id', $schoolId)->find($sub->charge_method_id);
                if ($cm && $cm->gateway_alias) {
                    $alias = strtolower((string) $cm->gateway_alias);
                }
            }
        }
        if ($alias === 'assas') {
            $alias = 'asaas';
        }
        if (!$alias) {
            $alias = 'asaas';
        }

        $config = FinanceGateway::where('school_id', $schoolId)->where('alias', $alias)->first();
        if (!$config || !$config->active) {
            return response()->json(['message' => 'Gateway inativo ou não configurado', 'alias' => $alias], 409);
        }

        try {
            $gm = new GatewayManager();
            $gm->register(new AsaasGateway());
            $gm->register(new NuPayGateway());
            $gw = $gm->forAlias($alias, $config);
            // externalReference usado no create: invoice_id ou invoice_number
            $found = null;
            $refId = (string) $invoice->id;
            if ($refId !== '') {
                $found = method_exists($gw, 'findPaymentByExternalReference') ? $gw->findPaymentByExternalReference($refId) : null;
            }
            if ((!$found || empty($found['id'])) && !empty($invoice->number)) {
                $found = method_exists($gw, 'findPaymentByExternalReference') ? $gw->findPaymentByExternalReference((string) $invoice->number) : null;
            }
            if ($found && !empty($found['id'])) {
                // Atualiza a fatura com charge_id e segue fluxo normal
                $invoice->charge_id = (string) $found['id'];
                $invoice->save();
            } else {
                // Fallback: criar cobrança no gateway se não encontrada por externalReference
                $sub = Subscription::where('school_id', $schoolId)->find($invoice->subscription_id);
                $cm = $sub ? ChargeMethod::where('school_id', $schoolId)->find($sub->charge_method_id) : null;
                $method = $cm && in_array(strtolower((string) $cm->method), ['pix', 'boleto']) ? strtolower((string) $cm->method) : 'boleto';
                $billingType = $method === 'pix' ? 'PIX' : 'BOLETO';

                // Resolver customer
                $payerModel = $sub ? Responsavel::find($sub->payer_id) : null;
                $cpf = $payerModel ? preg_replace('/\D/', '', (string) ($payerModel->cpf ?? '')) : null;
                $payer = [
                    'name' => $payerModel ? trim(($payerModel->nome ?? '') . ' ' . ($payerModel->sobrenome ?? '')) : null,
                    'email' => $payerModel->email ?? null,
                    'cpfCnpj' => $cpf ?: null,
                    'phone' => $payerModel->telefone_secundario ?? null,
                    'mobilePhone' => $payerModel->telefone_principal ?? null,
                    'address' => $payerModel->endereco ?? null,
                    'postalCode' => $payerModel->cep ?? null,
                    'city' => $payerModel->cidade ?? null,
                    'state' => $payerModel->estado ?? null,
                ];
                $gc = null;
                if ($payerModel) {
                    $gc = GatewayCustomer::where('school_id', $schoolId)
                        ->where('gateway_alias', $alias)
                        ->where('payer_id', $payerModel->id)
                        ->first();
                }
                if ($gc) {
                    $payer['external_id'] = $gc->external_customer_id;
                    $payer['externalReference'] = (string) ($payerModel->id);
                }
                $cust = $gw->createOrUpdateCustomer(['invoice_id' => $invoice->id], $payer);
                $customerId = $cust['id'] ?? ($gc ? $gc->external_customer_id : null);
                if ($customerId && (!$gc || !$gc->external_customer_id)) {
                    $gc = $gc ?: new GatewayCustomer([
                        'school_id' => $schoolId,
                        'payer_id' => $payerModel ? $payerModel->id : null,
                        'gateway_alias' => $alias,
                        'status' => 'active',
                    ]);
                    $gc->external_customer_id = $customerId;
                    $gc->save();
                }

                if ($customerId) {
                    $charge = $gw->createCharge([
                        'customer_id' => $customerId,
                        'method' => $billingType,
                        'amount_cents' => $invoice->total_cents,
                        'description' => null,
                        'due_date' => $invoice->due_date ? $invoice->due_date->toDateString() : null,
                        'invoice_id' => $invoice->id,
                        'invoice_number' => $invoice->number,
                    ]);

                    if (!empty($charge['charge_id'])) {
                        $invoice->charge_id = (string) $charge['charge_id'];
                        $invoice->boleto_url = $charge['boleto_url'] ?? null;
                        $invoice->linha_digitavel = $charge['linha_digitavel'] ?? null;
                        $invoice->barcode = $charge['barcode'] ?? null;
                        $invoice->pix_qr_code = $charge['pix_qr_code'] ?? null;
                        $invoice->pix_code = $charge['pix_code'] ?? null;
                        $invoice->save();
                    } else {
                        $status = $charge['status'] ?? null;
                        $err = $charge['error'] ?? null;
                        $code = is_array($err) ? ($err['errors'][0]['code'] ?? ($err['code'] ?? null)) : null;
                        $msg = is_array($err) ? ($err['errors'][0]['description'] ?? ($err['message'] ?? json_encode($err))) : (is_string($err) ? $err : null);
                        $invoice->gateway_status = $status ? (string) $status : null;
                        $invoice->gateway_error_code = $code ? (string) $code : null;
                        $invoice->gateway_error = $msg;
                        $invoice->save();
                        return response()->json(['message' => 'Falha ao criar cobrança no gateway', 'invoice' => $invoice], 502);
                    }
                } else {
                    return response()->json(['message' => 'charge_id ausente e cliente não resolvido para criação'], 422);
                }
            }

            // Com charge_id garantido, consultar status da cobrança e refletir na fatura
            $payment = method_exists($gw, 'getPayment') ? $gw->getPayment($invoice->charge_id) : null;
            if (is_array($payment)) {
                $gatewayStatus = strtolower((string) ($payment['status'] ?? ''));
                $invoice->gateway_status = $gatewayStatus ?: $invoice->gateway_status;

                // Mapear status do gateway (Asaas) para status local da fatura
                $localStatus = null;
                switch ($gatewayStatus) {
                    case 'pending':
                    case 'waiting':
                    case 'waiting_payment':
                        $localStatus = 'pending';
                        break;
                    case 'overdue':
                        $localStatus = 'overdue';
                        break;
                    case 'received':
                    case 'confirmed':
                    case 'paid':
                        $localStatus = 'paid';
                        break;
                    case 'canceled':
                        $localStatus = 'canceled';
                        break;
                    case 'chargeback':
                    case 'refunded':
                    case 'payment_failed':
                        $localStatus = 'failed';
                        break;
                }

                if ($localStatus) {
                    $invoice->status = $localStatus;
                    if ($localStatus === 'paid') {
                        $paidAt = $payment['confirmedDate'] ?? $payment['paymentDate'] ?? $payment['paid_at'] ?? null;
                        if ($paidAt) {
                            try {
                                $invoice->paid_at = Carbon::parse($paidAt);
                            } catch (\Throwable $e) {
                                // Ignorar parse inválido e manter valor existente
                            }
                        }
                    }
                }

                $invoice->save();
                return response()->json([
                    'message' => 'Sincronizado com gateway',
                    'invoice' => $invoice,
                    'payment' => $payment,
                    'alias' => $alias,
                ]);
            }

            // Caso não tenha sido possível consultar o pagamento
            return response()->json([
                'message' => 'Cobrança encontrada/criada, mas não foi possível consultar status no gateway',
                'invoice' => $invoice,
                'alias' => $alias,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Erro ao criar cobrança no gateway: ' . $e->getMessage(), ['invoice_id' => $invoice->id, 'school_id' => $schoolId]);
            return response()->json(['message' => 'charge_id ausente para consulta no gateway', 'hint' => 'Gateway inativo ou não configurado'], 422);
        }
    }

    private function generateNumber(int $schoolId): string
    {
        $last = Invoice::where('school_id', $schoolId)->orderByDesc('id')->first();
        $next = $last ? ((int) preg_replace('/\D/', '', (string) $last->number)) + 1 : 1;
        return sprintf('F%06d', $next);
    }

    private function resolveSchoolId(Request $request): ?int
    {
        $user = $request->user();
        if ($user) {
            if (isset($user->school_id) && $user->school_id)
                return (int) $user->school_id;
            if (isset($user->escola_id) && $user->escola_id)
                return (int) $user->escola_id;
        }
        $schoolId = $request->input('school_id') ?? $request->input('escola_id');
        return $schoolId ? (int) $schoolId : null;
    }

    public function cancel(Request $request, int $id)
    {
        $schoolId = $this->resolveSchoolId($request);
        if (!$schoolId)
            return response()->json(['message' => 'school_id required'], 422);

        $invoice = Invoice::where('school_id', $schoolId)->findOrFail($id);
        if ($invoice->status === 'paid') {
            return response()->json(['message' => 'Não é possível cancelar fatura já paga', 'invoice' => $invoice], 409);
        }
        if ($invoice->status === 'canceled') {
            return response()->json(['message' => 'Fatura já cancelada', 'invoice' => $invoice]);
        }

        // Atualiza status localmente; integração com gateway pode ser adicionada futuramente
        $invoice->status = 'canceled';
        $invoice->gateway_status = 'canceled';
        $invoice->save();

        Log::info('Fatura cancelada', ['invoice_id' => $invoice->id, 'school_id' => $schoolId]);
        return response()->json(['message' => 'cancelada', 'invoice' => $invoice]);
    }

    public function cancelBatch(Request $request)
    {
        $schoolId = $this->resolveSchoolId($request);
        if (!$schoolId)
            return response()->json(['message' => 'school_id required'], 422);

        $ids = $request->input('invoice_ids');
        if (!is_array($ids) || empty($ids)) {
            return response()->json(['message' => 'invoice_ids array required'], 422);
        }

        $results = ['canceled' => [], 'skipped' => [], 'errors' => []];
        foreach ($ids as $id) {
            try {
                $invoice = Invoice::where('school_id', $schoolId)->find($id);
                if (!$invoice) {
                    $results['errors'][] = ['id' => $id, 'error' => 'not_found'];
                    continue;
                }
                if ($invoice->status === 'paid' || $invoice->status === 'canceled') {
                    $results['skipped'][] = ['id' => $id, 'status' => $invoice->status];
                    continue;
                }
                $invoice->status = 'canceled';
                $invoice->gateway_status = 'canceled';
                $invoice->save();
                $results['canceled'][] = $id;
            } catch (\Throwable $e) {
                $results['errors'][] = ['id' => $id, 'error' => $e->getMessage()];
            }
        }

        Log::info('Cancelamento em lote de faturas', ['school_id' => $schoolId, 'results' => $results]);
        return response()->json(['message' => 'batch_cancel_completed', 'results' => $results]);
    }

    // New endpoint: resend invoice email to payer
    public function resendEmail(Request $request, int $id)
    {
        $schoolId = $this->resolveSchoolId($request);
        if (!$schoolId)
            return response()->json(['success' => false, 'message' => 'school_id required'], 422);

        $invoice = Invoice::where('school_id', $schoolId)->find($id);
        if (!$invoice) {
            return response()->json(['success' => false, 'message' => 'Fatura não encontrada'], 404);
        }

        $sub = Subscription::where('school_id', $schoolId)->find($invoice->subscription_id);
        $payer = $sub ? Responsavel::find($sub->payer_id) : null;
        $email = $payer ? ($payer->email ?? null) : null;
        if (!$email) {
            return response()->json(['success' => false, 'message' => 'Não existe e-mail cadastrado para o pagador.'], 422);
        }

        $payerName = trim(((string) ($payer->nome ?? '')) . ' ' . ((string) ($payer->sobrenome ?? '')));
        $payload = [
            'subject' => 'Cobrança ' . ($invoice->number ?? $invoice->id),
            'payer_name' => $payerName ?: 'Responsável',
            'invoice_number' => $invoice->number ?? (string) $invoice->id,
            'due_date' => $invoice->due_date ? $invoice->due_date->format('d/m/Y') : null,
            'amount_cents' => (int) ($invoice->total_cents ?? 0),
            'payment_url' => $invoice->boleto_url ?: null,
        ];

        try {
            Mail::to($email)->send(new DunningReminder($payload));
            Log::info('Cobrança reenviada por e-mail', ['invoice_id' => $invoice->id, 'email' => $email]);
            return response()->json(['success' => true, 'message' => 'Cobrança reenviada para o e-mail cadastrado.']);
        } catch (\Throwable $e) {
            Log::error('Falha ao reenviar cobrança por e-mail', ['invoice_id' => $invoice->id, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Erro ao enviar e-mail de cobrança.'], 500);
        }
    }
}