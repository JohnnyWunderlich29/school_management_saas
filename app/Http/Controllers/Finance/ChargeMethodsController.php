<?php

namespace App\Http\Controllers\Finance;

use App\Models\Finance\ChargeMethod;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class ChargeMethodsController extends Controller
{
    public function index(Request $request)
    {
        $schoolId = $this->resolveSchoolId($request);
        if (!$schoolId) return response()->json(['message' => 'school_id required'], 422);
        $perPage = (int) $request->get('per_page', 15);
        $page = (int) $request->get('page', 1);
        $alias = strtolower((string) $request->get('gateway_alias', 'all'));
        $method = strtolower((string) $request->get('method', 'all'));
        $active = $request->has('active') ? (filter_var($request->get('active'), FILTER_VALIDATE_BOOLEAN) ? '1' : '0') : 'all';

        $cacheKey = sprintf('cm:list:%d:%s:%s:%s:%d:%d', $schoolId, $alias, $method, $active, $page, $perPage);
        $data = Cache::remember($cacheKey, 300, function () use ($request, $schoolId, $perPage) {
            $query = ChargeMethod::where('school_id', $schoolId);
            if ($request->filled('gateway_alias')) $query->where('gateway_alias', $request->get('gateway_alias'));
            if ($request->filled('method')) $query->where('method', $request->get('method'));
            if ($request->filled('active')) $query->where('active', filter_var($request->get('active'), FILTER_VALIDATE_BOOLEAN));
            $items = $query->orderBy('gateway_alias')->orderBy('method')->paginate($perPage);
            return $items->toArray();
        });
        return response()->json($data);
    }

    public function show(Request $request, int $id)
    {
        $schoolId = $this->resolveSchoolId($request);
        if (!$schoolId) return response()->json(['message' => 'school_id required'], 422);
        $cm = ChargeMethod::where('school_id', $schoolId)->findOrFail($id);
        return response()->json($cm);
    }

    public function store(Request $request)
    {
        $schoolId = $this->resolveSchoolId($request);
        if (!$schoolId) return response()->json(['message' => 'school_id required'], 422);
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'gateway_alias' => ['required','string','max:64'],
            'method' => [
                'required','string', Rule::in(['credit_card','debit_card','pix','boleto']),
                Rule::unique('charge_methods')->where(function($q) use ($schoolId, $request) {
                    return $q->where('school_id', $schoolId)
                             ->where('gateway_alias', $request->input('gateway_alias'));
                })
            ],
            'penalty_policy' => 'nullable|array',
            'active' => 'nullable|boolean',
        ]);
        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);
        $data = $validator->validated();
        $cm = new ChargeMethod();
        $cm->school_id = $schoolId;
        $cm->gateway_alias = $data['gateway_alias'];
        $cm->method = $data['method'];
        $cm->penalty_policy = $data['penalty_policy'] ?? null;
        $cm->active = array_key_exists('active', $data) ? (bool)$data['active'] : true;
        $cm->save();
        $this->forgetChargeMethodsCache($schoolId, $cm->gateway_alias);
        return response()->json(['id' => $cm->id], 201);
    }

    public function update(Request $request, int $id)
    {
        $schoolId = $this->resolveSchoolId($request);
        if (!$schoolId) return response()->json(['message' => 'school_id required'], 422);
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'gateway_alias' => 'nullable|string|max:64',
            'method' => [
                'nullable','string', Rule::in(['credit_card','debit_card','pix','boleto']),
            ],
            'penalty_policy' => 'nullable|array',
            'active' => 'nullable|boolean',
        ]);
        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);
        $cm = ChargeMethod::where('school_id', $schoolId)->findOrFail($id);
        $data = $validator->validated();
        // Enforce uniqueness in case gateway_alias/method are changed
        if (array_key_exists('gateway_alias', $data) || array_key_exists('method', $data)) {
            $newAlias = $data['gateway_alias'] ?? $cm->gateway_alias;
            $newMethod = $data['method'] ?? $cm->method;
            $exists = ChargeMethod::where('school_id', $schoolId)
                ->where('gateway_alias', $newAlias)
                ->where('method', $newMethod)
                ->where('id', '!=', $cm->id)
                ->exists();
            if ($exists) {
                return response()->json(['errors' => ['method' => ['Já existe forma para este gateway e método.']]], 422);
            }
        }
        $cm->fill($data);
        $cm->save();
        $this->forgetChargeMethodsCache($schoolId, $cm->gateway_alias);
        return response()->json(['message' => 'updated']);
    }

    public function destroy(Request $request, int $id)
    {
        $schoolId = $this->resolveSchoolId($request);
        if (!$schoolId) return response()->json(['message' => 'school_id required'], 422);
        $cm = ChargeMethod::where('school_id', $schoolId)->findOrFail($id);
        $cm->delete();
        $this->forgetChargeMethodsCache($schoolId, $cm->gateway_alias);
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
        return $schoolId ? (int)$schoolId : null;
    }

    private function forgetChargeMethodsCache(int $schoolId, ?string $gatewayAlias = null): void
    {
        $aliases = [strtolower((string) $gatewayAlias), 'all'];
        $aliases = array_unique(array_filter($aliases));
        // Invalidate likely-used keys (first page, common perPage values)
        foreach ($aliases as $alias) {
            foreach ([1] as $page) {
                foreach ([15, 250] as $perPage) {
                    foreach (['all'] as $method) {
                        foreach (['all'] as $active) {
                            $key = sprintf('cm:list:%d:%s:%s:%s:%d:%d', $schoolId, $alias, $method, $active, $page, $perPage);
                            Cache::forget($key);
                        }
                    }
                }
            }
        }
    }
}