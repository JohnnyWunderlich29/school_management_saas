<?php

namespace App\Http\Controllers\Finance;

use App\Models\Finance\FinanceGateway;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;

class FinanceGatewaysController extends Controller
{
    public function index(Request $request)
    {
        $schoolId = $this->resolveSchoolId($request);
        if (!$schoolId) {
            return response()->json(['message' => 'school_id required'], 422);
        }

        $gateways = FinanceGateway::where('school_id', $schoolId)
            ->orderBy('alias')
            ->get()
            ->map(function (FinanceGateway $g) {
                return [
                    'id' => $g->id,
                    'alias' => $g->alias,
                    'name' => $g->name,
                    'active' => $g->active,
                    'environment' => $g->environment ?? 'production',
                    'has_credentials' => (bool) $g->credentials_encrypted,
                ];
            });

        return response()->json($gateways);
    }

    public function show(Request $request, int $id)
    {
        $schoolId = $this->resolveSchoolId($request);
        if (!$schoolId) {
            return response()->json(['message' => 'school_id required'], 422);
        }
        $gateway = FinanceGateway::where('school_id', $schoolId)->findOrFail($id);
        // Mask secrets: return only non-sensitive fields and minimal credentials if needed
        return response()->json([
            'id' => $gateway->id,
            'alias' => $gateway->alias,
            'name' => $gateway->name,
            'active' => $gateway->active,
            'credentials' => $gateway->credentials ? array_map(function ($v, $k) {
                // Basic masking for strings
                if (is_string($v) && strlen($v) > 6) {
                    return substr($v, 0, 3) . str_repeat('*', max(0, strlen($v) - 6)) . substr($v, -3);
                }
                return $v;
            }, $gateway->credentials, array_keys($gateway->credentials)) : null,
        ]);
    }

    public function store(Request $request)
    {
        $schoolId = $this->resolveSchoolId($request);
        if (!$schoolId) {
            return response()->json(['message' => 'school_id required'], 422);
        }

        $validator = Validator::make($request->all(), [
            'alias' => 'required|string|max:64',
            'name' => 'nullable|string|max:128',
            'active' => 'nullable|boolean',
            'credentials' => 'nullable|array',
            'webhook_secret' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();
        $gateway = new FinanceGateway();
        $gateway->school_id = $schoolId;

        // Normaliza alias e força mapeamento pelo provedor
        $alias = strtolower($data['alias']);
        if ($alias === 'assas') { $alias = 'asaas'; }

        $gateway->name = $data['name'] ?? null;
        $gateway->active = $data['active'] ?? true;
        if (isset($data['credentials'])) {
            // Se o cliente enviar o provedor nas credenciais, vincula alias
            $provider = strtolower((string)($data['credentials']['provider'] ?? ''));
            if ($provider === 'asaas') { $alias = 'asaas'; }
            $gateway->credentials = $data['credentials'];
        }
        if (isset($data['webhook_secret'])) {
            $gateway->webhook_secret = $data['webhook_secret'];
        }
        $gateway->alias = $alias;
        $gateway->save();

        return response()->json(['id' => $gateway->id], 201);
    }

    public function update(Request $request, int $id)
    {
        $schoolId = $this->resolveSchoolId($request);
        if (!$schoolId) {
            return response()->json(['message' => 'school_id required'], 422);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:128',
            'active' => 'nullable|boolean',
            'credentials' => 'nullable|array',
            'webhook_secret' => 'nullable|string',
            'environment' => 'nullable|in:homolog,production',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $gateway = FinanceGateway::where('school_id', $schoolId)->findOrFail($id);

        $data = $validator->validated();
        if (array_key_exists('name', $data)) {
            $gateway->name = $data['name'];
        }
        if (array_key_exists('active', $data)) {
            $gateway->active = (bool) $data['active'];
        }
        if (array_key_exists('credentials', $data)) {
            $gateway->credentials = $data['credentials'];
        }
        if (array_key_exists('webhook_secret', $data)) {
            $gateway->webhook_secret = $data['webhook_secret'];
        }
        if (array_key_exists('environment', $data)) {
            $gateway->environment = $data['environment'] ?? 'production';
        }
        $gateway->save();

        return response()->json(['message' => 'updated']);
    }

    public function destroy(Request $request, int $id)
    {
        $schoolId = $this->resolveSchoolId($request);
        if (!$schoolId) {
            return response()->json(['message' => 'school_id required'], 422);
        }
        $gateway = FinanceGateway::where('school_id', $schoolId)->findOrFail($id);
        $gateway->delete();
        return response()->json(['message' => 'deleted']);
    }

    private function resolveSchoolId(Request $request): ?int
    {
        $user = $request->user();
        if ($user) {
            if (isset($user->school_id) && $user->school_id) return (int)$user->school_id;
            if (isset($user->escola_id) && $user->escola_id) return (int)$user->escola_id;
        }
        $schoolId = $request->input('school_id') ?? $request->input('escola_id');
        return $schoolId ? (int) $schoolId : null;
    }
}