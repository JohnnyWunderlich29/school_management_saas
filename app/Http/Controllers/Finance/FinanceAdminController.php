<?php

namespace App\Http\Controllers\Finance;

use App\Models\Finance\FinanceGateway;
use App\Models\Finance\FinanceSettings;
use App\Models\Finance\MailSettings;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\AlertService;
use Illuminate\Support\Facades\Mail;
use App\Mail\TestDunningEmail;

class FinanceAdminController extends Controller
{
    private function resolveSchoolId(Request $request): ?int
    {
        $user = $request->user();
        if ($user) {
            if (isset($user->escola_id) && $user->escola_id) return (int) $user->escola_id;
            if (isset($user->school_id) && $user->school_id) return (int) $user->school_id;
        }
        // Fallback para escola selecionada em sessão (suporte/superadmin via escola-switcher)
        $sessionSchool = session('escola_atual');
        if ($sessionSchool) return (int) $sessionSchool;

        // Último recurso: parâmetro explícito na requisição
        $schoolId = $request->input('school_id') ?? $request->input('escola_id');
        return $schoolId ? (int) $schoolId : null;
    }

    public function settings(Request $request)
    {
        $schoolId = $this->resolveSchoolId($request);
        if (!$schoolId) {
            return redirect()->back()->withErrors(['school_id' => 'school_id required']);
        }
        $settings = FinanceSettings::firstOrCreate(['school_id' => $schoolId], ['currency' => 'BRL']);
        $gateways = FinanceGateway::where('school_id', $schoolId)->orderBy('alias')->get();
        $financeEnv = config('features.finance_env', 'production');
        return view('finance.settings', compact('settings', 'gateways', 'financeEnv'));
    }

    public function saveSettings(Request $request)
    {
        $schoolId = $this->resolveSchoolId($request);
        if (!$schoolId) {
            return redirect()->back()->withErrors(['school_id' => 'school_id required']);
        }

        $validator = Validator::make($request->all(), [
            'default_gateway_alias' => 'nullable|string|max:64',
            'fine_percent' => 'nullable|numeric|min:0',
            'daily_interest_percent' => 'nullable|numeric|min:0',
            'grace_days' => 'nullable|integer|min:0|max:30',
            'max_interest_percent' => 'nullable|numeric|min:0',
            'allowed_payment_methods' => 'nullable|array',
            'dunning_schedule' => 'nullable|array',
            'timezone' => 'nullable|string|max:64',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $settings = FinanceSettings::firstOrCreate(['school_id' => $schoolId], ['currency' => 'BRL']);
        $data = $validator->validated();

        if (array_key_exists('default_gateway_alias', $data)) {
            $settings->default_gateway_alias = $data['default_gateway_alias'];
        }
        // Montar penalty_policy como JSON
        $penalty = [
            'fine_percent' => $request->input('fine_percent') !== null ? (float)$request->input('fine_percent') : null,
            'daily_interest_percent' => $request->input('daily_interest_percent') !== null ? (float)$request->input('daily_interest_percent') : null,
            'grace_days' => $request->input('grace_days') !== null ? (int)$request->input('grace_days') : null,
        ];
        if ($request->filled('max_interest_percent')) {
            $penalty['max_interest_percent'] = (float)$request->input('max_interest_percent');
        }
        $settings->penalty_policy = $penalty;

        if (array_key_exists('allowed_payment_methods', $data)) {
            $settings->allowed_payment_methods = $data['allowed_payment_methods'];
        }

        // Atualizar timezone, se enviada
        if (array_key_exists('timezone', $data)) {
            $settings->timezone = $data['timezone'];
        }

        // Normalizar e salvar dunning_schedule (se enviado)
        $schedule = $request->input('dunning_schedule');
        if (is_array($schedule)) {
            // enabled
            $schedule['enabled'] = !empty($schedule['enabled']);
            // dias da semana
            $validDays = ['seg','ter','qua','qui','sex','sab','dom'];
            $days = isset($schedule['days_of_week']) && is_array($schedule['days_of_week']) ? $schedule['days_of_week'] : [];
            $schedule['days_of_week'] = array_values(array_intersect($validDays, array_map('strtolower', $days)));
            // time windows
            $windows = isset($schedule['time_windows']) && is_array($schedule['time_windows']) ? $schedule['time_windows'] : [];
            $normWindows = [];
            foreach ($windows as $w) {
                $start = isset($w['start']) ? (string)$w['start'] : null;
                $end = isset($w['end']) ? (string)$w['end'] : null;
                if ($start && $end) {
                    $normWindows[] = ['start' => $start, 'end' => $end];
                }
            }
            if (empty($normWindows)) {
                $normWindows = [['start' => '08:00', 'end' => '18:00']];
            }
            $schedule['time_windows'] = $normWindows;
            // pre_due_offsets
            if (isset($schedule['pre_due_offsets'])) {
                $pre = $schedule['pre_due_offsets'];
                if (is_string($pre)) {
                    $pre = array_filter(array_map('intval', preg_split('/[,\s]+/', $pre)));
                }
                if (!is_array($pre)) $pre = [];
                $schedule['pre_due_offsets'] = array_values($pre);
            }
            // due_day
            $schedule['due_day'] = !empty($schedule['due_day']);
            // overdue_offsets
            if (isset($schedule['overdue_offsets'])) {
                $post = $schedule['overdue_offsets'];
                if (is_string($post)) {
                    $post = array_filter(array_map('intval', preg_split('/[,\s]+/', $post)));
                }
                if (!is_array($post)) $post = [];
                $schedule['overdue_offsets'] = array_values($post);
            }
            // channels
            $allowedChannels = ['email','whatsapp'];
            $channels = isset($schedule['channels']) && is_array($schedule['channels']) ? $schedule['channels'] : [];
            $schedule['channels'] = array_values(array_intersect($allowedChannels, $channels));
            // throttle_per_run
            $thr = isset($schedule['throttle_per_run']) ? (int)$schedule['throttle_per_run'] : null;
            $schedule['throttle_per_run'] = max(1, $thr ?: 50);

            $settings->dunning_schedule = $schedule;
        }

        $settings->save();
        AlertService::success('Configurações salvas com sucesso.');
        return redirect()->route('finance.settings');
    }

    public function gateways(Request $request)
    {
        $schoolId = $this->resolveSchoolId($request);
        if (!$schoolId) {
            return redirect()->back()->withErrors(['school_id' => 'school_id required']);
        }
        $gateways = FinanceGateway::where('school_id', $schoolId)->orderBy('alias')->get();
        $financeEnv = config('features.finance_env', 'production');
        return view('finance.gateways', compact('gateways', 'financeEnv'));
    }

    public function createGateway(Request $request)
    {
        $schoolId = $this->resolveSchoolId($request);
        if (!$schoolId) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['message' => 'school_id required'], 422);
            }
            return redirect()->back()->withErrors(['school_id' => 'school_id required']);
        }
        $validator = Validator::make($request->all(), [
            'alias' => 'required|string|max:64',
            'name' => 'nullable|string|max:128',
            'active' => 'nullable|boolean',
            'environment' => 'nullable|in:homolog,production',
            'webhook_secret' => 'nullable|string',
            'credentials_json' => 'nullable|string',
        ]);
        if ($validator->fails()) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }
        $data = $validator->validated();
        $gateway = new FinanceGateway();
        $gateway->school_id = $schoolId;

        // Normaliza alias e força mapeamento pelo provedor
        $alias = strtolower($data['alias']);
        if ($alias === 'assas') { $alias = 'asaas'; }

        $gateway->name = $data['name'] ?? null;
        $gateway->active = isset($data['active']) ? (bool)$data['active'] : true;
        $gateway->environment = $data['environment'] ?? 'production';
        if (!empty($data['webhook_secret'])) {
            $gateway->webhook_secret = $data['webhook_secret'];
        }
        if (!empty($data['credentials_json'])) {
            try {
                $creds = json_decode($data['credentials_json'], true) ?: [];
                // Se o provedor foi selecionado na UI, vincula alias automaticamente
                $provider = strtolower((string)($creds['provider'] ?? ''));
                if ($provider === 'asaas') { $alias = 'asaas'; }
                $gateway->credentials = $creds;
            } catch (\Throwable $e) {
                // ignora erro e não salva credenciais
            }
        }
        $gateway->alias = $alias;
        $gateway->save();
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'Gateway criado',
                'gateway' => [
                    'id' => $gateway->id,
                    'alias' => $gateway->alias,
                    'name' => $gateway->name,
                    'active' => $gateway->active,
                    'environment' => $gateway->environment,
                    'has_credentials' => !empty($gateway->credentials_encrypted),
                    'webhook_secret' => (bool) $gateway->webhook_secret,
                ],
            ], 201);
        }
        return redirect()->route('finance.gateways')->with('status', 'Gateway criado');
    }

    public function updateGateway(Request $request, int $id)
    {
        $schoolId = $this->resolveSchoolId($request);
        if (!$schoolId) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['message' => 'school_id required'], 422);
            }
            return redirect()->back()->withErrors(['school_id' => 'school_id required']);
        }
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:128',
            'active' => 'nullable|boolean',
            'environment' => 'nullable|in:homolog,production',
            'webhook_secret' => 'nullable|string',
            'credentials_json' => 'nullable|string',
            'penalty_policy_json' => 'nullable|string',
        ]);
        if ($validator->fails()) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }
        $gateway = FinanceGateway::where('school_id', $schoolId)->findOrFail($id);
        $data = $validator->validated();
        if (array_key_exists('name', $data)) $gateway->name = $data['name'];
        if (array_key_exists('active', $data)) $gateway->active = (bool)$data['active'];
        if (array_key_exists('environment', $data)) $gateway->environment = $data['environment'] ?? 'production';
        if (array_key_exists('webhook_secret', $data)) $gateway->webhook_secret = $data['webhook_secret'];
        if (!empty($data['credentials_json'])) {
            try {
                $creds = json_decode($data['credentials_json'], true) ?: [];
                $gateway->credentials = $creds;
            } catch (\Throwable $e) {
                // ignora erro
            }
        }
        // Atualizar penalty_policy dentro de credentials, se enviado
        if ($request->filled('penalty_policy_json')) {
            try {
                $policy = json_decode($request->input('penalty_policy_json'), true) ?: [];
                $creds = $gateway->credentials ?? [];
                $creds['penalty_policy'] = $policy;
                $gateway->credentials = $creds;
            } catch (\Throwable $e) {
                // ignorar erro de parse
            }
        }
        $gateway->save();
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => $request->filled('penalty_policy_json') ? 'Cobrança atualizada' : 'Gateway atualizado',
                'gateway' => [
                    'id' => $gateway->id,
                    'alias' => $gateway->alias,
                    'name' => $gateway->name,
                    'active' => $gateway->active,
                    'environment' => $gateway->environment,
                    'has_credentials' => !empty($gateway->credentials_encrypted),
                    'webhook_secret' => (bool) $gateway->webhook_secret,
                ],
            ]);
        }
        return redirect()->route('finance.gateways')->with('status', 'Gateway atualizado');
    }

    /**
     * Testa credenciais de um provedor de gateway sem salvar no banco.
     */
    public function testGatewayCredentials(Request $request)
    {
        $schoolId = $this->resolveSchoolId($request);
        if (!$schoolId) {
            return response()->json(['errors' => ['school_id' => ['school_id required']]], 422);
        }

        $validator = Validator::make($request->all(), [
            'provider' => 'required|string|in:asaas',
            'environment' => 'required|string|in:production,sandbox,homolog',
            'api_key' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();
        $provider = $data['provider'];
        $env = $data['environment'];
        $apiKey = $data['api_key'];

        if ($provider === 'asaas') {
            // Usar domínios e caminho corretos conforme documentação e sua orientação:
            // Produção: https://api.asaas.com/api/v3
            // Sandbox/Homolog: https://sandbox.asaas.com/api/v3
            $baseUrl = in_array($env, ['sandbox', 'homolog'])
                ? 'https://sandbox.asaas.com/api/v3'
                : 'https://api.asaas.com/api/v3';

            try {
                $headers = [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    // Compatibilizar ambos formatos de autenticação
                    'access_token' => $apiKey,
                    'Authorization' => $apiKey,
                ];
                $endpoint = $baseUrl . '/myAccount';
                Log::info('Asaas test call', ['env' => $env, 'endpoint' => $endpoint]);
                $resp = Http::withHeaders($headers)->get($endpoint);

                if ($resp->successful()) {
                    return response()->json([
                        'ok' => true,
                        'provider' => 'asaas',
                        'environment' => $env,
                        'account' => $resp->json(),
                    ]);
                }

                $errJson = $resp->json();
                $status = $resp->status();
                $friendly = 'Falha ao validar credenciais' . ($status ? " (status $status)" : '');
                Log::warning('Asaas test failed', ['status' => $status, 'error' => $errJson]);
                return response()->json([
                    'ok' => false,
                    'provider' => 'asaas',
                    'environment' => $env,
                    'status' => $status,
                    'errors' => ['api_key' => [$friendly]],
                    'error' => $errJson ?: ['message' => $friendly],
                ], 422);
            } catch (\Throwable $e) {
                Log::error('Asaas test exception', ['message' => $e->getMessage()]);
                return response()->json([
                    'ok' => false,
                    'provider' => 'asaas',
                    'environment' => $env,
                    'errors' => ['api_key' => ['Erro de conexão com Asaas']],
                    'error' => ['message' => 'Erro de conexão com Asaas', 'detail' => $e->getMessage()],
                ], 500);
            }
        }

        return response()->json(['errors' => ['provider' => ['Provider não suportado']]], 422);
    }

    public function getMailSettings(Request $request)
    {
        $schoolId = $this->resolveSchoolId($request);
        if (!$schoolId) {
            return response()->json(['message' => 'school_id required'], 422);
        }
        $ms = MailSettings::firstOrCreate(['school_id' => $schoolId], [
            'provider' => 'smtp',
            'active' => true,
        ]);
        $creds = $ms->credentials ?? [];
        $masked = [];
        foreach ($creds as $k => $v) {
            if (is_string($v) && strlen($v) > 4) {
                $masked[$k] = substr($v, 0, 2) . str_repeat('*', max(0, strlen($v) - 4)) . substr($v, -2);
            } else {
                $masked[$k] = $v;
            }
        }
        return response()->json([
            'provider' => $ms->provider,
            'sending_domain' => $ms->sending_domain,
            'from_email' => $ms->from_email,
            'from_name' => $ms->from_name,
            'reply_to_email' => $ms->reply_to_email,
            'active' => (bool) $ms->active,
            'verified' => (bool) $ms->verified,
            'has_credentials' => (bool) $ms->credentials_encrypted,
            'credentials_masked' => $masked,
            'dns_requirements' => $ms->dns_requirements,
            'dns_status' => $ms->dns_status,
            'last_checked_at' => $ms->last_checked_at,
        ]);
    }

    public function saveMailSettings(Request $request)
    {
        $schoolId = $this->resolveSchoolId($request);
        if (!$schoolId) {
            return response()->json(['message' => 'school_id required'], 422);
        }
        $validator = Validator::make($request->all(), [
            'provider' => 'required|in:smtp,mailgun,ses',
            'sending_domain' => 'nullable|string|max:255',
            'from_email' => 'nullable|email',
            'from_name' => 'nullable|string|max:128',
            'reply_to_email' => 'nullable|email',
            'active' => 'nullable|boolean',
            'credentials_json' => 'nullable|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $ms = MailSettings::firstOrCreate(['school_id' => $schoolId]);
        $data = $validator->validated();
        $ms->provider = $data['provider'];
        if (array_key_exists('sending_domain', $data)) $ms->sending_domain = $data['sending_domain'];
        if (array_key_exists('from_email', $data)) $ms->from_email = $data['from_email'];
        if (array_key_exists('from_name', $data)) $ms->from_name = $data['from_name'];
        if (array_key_exists('reply_to_email', $data)) $ms->reply_to_email = $data['reply_to_email'];
        if (array_key_exists('active', $data)) $ms->active = (bool) $data['active'];
        if (!empty($data['credentials_json'])) {
            try {
                $creds = json_decode($data['credentials_json'], true) ?: [];
                $ms->credentials = $creds;
            } catch (\Throwable $e) {
                // ignore parse error
            }
        }
        // Atualizar requisitos de DNS conforme provedor
        if ($ms->provider === 'mailgun' && $ms->sending_domain) {
            $ms->dns_requirements = $this->computeMailgunDNSRequirements($ms->sending_domain);
            $ms->verified = false;
        } elseif ($ms->provider === 'ses') {
            $ms->dns_requirements = [
                'info' => 'Registros DKIM/VERIFICAÇÃO devem ser obtidos na AWS SES para o domínio',
            ];
            $ms->verified = false;
        } else {
            $ms->dns_requirements = null;
            $ms->verified = false;
        }
        $ms->save();
        return response()->json(['message' => 'Configurações salvas', 'mail_settings_id' => $ms->id]);
    }

    public function verifyMailDNS(Request $request)
    {
        $schoolId = $this->resolveSchoolId($request);
        if (!$schoolId) {
            return response()->json(['message' => 'school_id required'], 422);
        }
        $ms = MailSettings::where('school_id', $schoolId)->first();
        if (!$ms) {
            return response()->json(['message' => 'MailSettings inexistente'], 404);
        }
        if ($ms->provider === 'mailgun' && $ms->sending_domain) {
            $requirements = $this->computeMailgunDNSRequirements($ms->sending_domain);
            $status = $this->checkDNSRecords($requirements);
            $ms->dns_requirements = $requirements;
            $ms->dns_status = $status;
            $ms->last_checked_at = now();
            $ms->verified = collect($status)->every(function ($r) { return !empty($r['ok']); });
            $ms->save();
            return response()->json([
                'verified' => $ms->verified,
                'dns_status' => $status,
                'dns_requirements' => $requirements,
            ]);
        }
        if ($ms->provider === 'ses') {
            return response()->json([
                'verified' => false,
                'message' => 'Para AWS SES, conclua verificação no console AWS e cole os CNAMEs DKIM fornecidos.',
            ], 200);
        }
        // SMTP genérico: sem verificação automatizada
        return response()->json([
            'verified' => false,
            'message' => 'Para SMTP genérico (Google/Microsoft/etc.), a verificação de DKIM/DMARC ocorre no provedor. Confirme manualmente.',
        ], 200);
    }

    private function computeMailgunDNSRequirements(string $sendingDomain): array
    {
        $root = $this->extractRootDomain($sendingDomain);
        return [
            [
                'type' => 'TXT',
                'name' => $sendingDomain,
                'expected' => 'v=spf1 include:mailgun.org ~all',
                'description' => 'SPF para permitir Mailgun enviar pelo subdomínio',
            ],
            [
                'type' => 'CNAME',
                'name' => 'smtp._domainkey.' . $sendingDomain,
                'expected' => 'smtp.' . $sendingDomain . '.domainkey.mailgun.org',
                'description' => 'DKIM CNAME para subdomínio do Mailgun',
            ],
            [
                'type' => 'CNAME',
                'name' => 'email.' . $sendingDomain,
                'expected' => 'mailgun.org',
                'description' => 'CNAME de tracking/clicks do Mailgun',
            ],
            [
                'type' => 'MX',
                'name' => $sendingDomain,
                'expected' => ['mxa.mailgun.org', 'mxb.mailgun.org'],
                'description' => 'MX para bounces/retornos do Mailgun',
            ],
            [
                'type' => 'TXT',
                'name' => '_dmarc.' . $root,
                'expected' => 'v=DMARC1; p=quarantine',
                'description' => 'DMARC no domínio raiz (pode ser reject)',
            ],
        ];
    }

    private function extractRootDomain(string $domain): string
    {
        $parts = explode('.', $domain);
        if (count($parts) <= 2) return $domain;
        return implode('.', array_slice($parts, -2));
    }

    private function checkDNSRecords(array $requirements): array
    {
        $results = [];
        foreach ($requirements as $req) {
            $ok = false; $found = [];
            try {
                switch ($req['type']) {
                    case 'TXT':
                        $records = dns_get_record($req['name'], DNS_TXT) ?: [];
                        foreach ($records as $r) {
                            if (!empty($r['txt'])) $found[] = $r['txt'];
                        }
                        foreach ($found as $txt) {
                            if (stripos($txt, 'v=spf1') === 0 && stripos($req['expected'], 'v=spf1') === 0) {
                                $ok = str_contains($txt, 'include:mailgun.org');
                            } elseif (stripos($txt, 'v=DMARC1') === 0 && stripos($req['expected'], 'v=DMARC1') === 0) {
                                $ok = true; // aceitamos qualquer política válida
                            } else {
                                $ok = trim($txt) === trim($req['expected']);
                            }
                        }
                        break;
                    case 'CNAME':
                        $records = dns_get_record($req['name'], DNS_CNAME) ?: [];
                        foreach ($records as $r) {
                            if (!empty($r['target'])) $found[] = rtrim($r['target'], '.');
                        }
                        $ok = in_array(rtrim($req['expected'], '.'), $found, true);
                        break;
                    case 'MX':
                        $records = dns_get_record($req['name'], DNS_MX) ?: [];
                        foreach ($records as $r) {
                            if (!empty($r['target'])) $found[] = rtrim($r['target'], '.');
                        }
                        $expected = (array) $req['expected'];
                        $ok = empty(array_diff($expected, $found)) && empty(array_diff($found, $expected));
                        break;
                }
            } catch (\Throwable $e) {
                $ok = false;
            }
            $results[] = [
                'type' => $req['type'],
                'name' => $req['name'],
                'expected' => $req['expected'],
                'found' => $found,
                'ok' => $ok,
                'description' => $req['description'] ?? null,
            ];
        }
        return $results;
    }

    public function testDunningEmail(Request $request)
    {
        $schoolId = $this->resolveSchoolId($request);
        if (!$schoolId) {
            return response()->json([
                'success' => false,
                'message' => 'school_id required',
            ], 422);
        }

        // Bloquear envio se mail_settings não estiver verificado/ativo
        $ms = MailSettings::where('school_id', $schoolId)->first();
        if ($ms && ((!$ms->verified) || (!$ms->active))) {
            return response()->json([
                'success' => false,
                'message' => 'Configuração de e-mail não verificada ou inativa. Conclua a verificação de DNS.',
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'test_email' => 'required|email',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'message' => 'E-mail inválido',
            ], 422);
        }
    
        $email = $request->input('test_email');
    
        try {
            Mail::to($email)->send(new TestDunningEmail($schoolId));
    
            return response()->json([
                'success' => true,
                'message' => 'E-mail de teste enviado com sucesso.',
            ]);
        } catch (\Throwable $e) {
            Log::error('Falha ao enviar e-mail de teste de cobranças', [
                'error' => $e->getMessage(),
            ]);
    
            return response()->json([
                'success' => false,
                'message' => 'Erro ao enviar e-mail de teste.',
            ], 500);
        }
    }
}