<?php

namespace App\Http\Controllers\Finance;

use App\Models\Finance\BillingPlan;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;

class PlansController extends Controller
{
    public function index(Request $request)
    {
        $schoolId = $this->resolveSchoolId($request);
        if (!$schoolId) return response()->json(['message' => 'school_id required'], 422);

        $query = BillingPlan::where('school_id', $schoolId);
        if ($request->has('active')) {
            $query->where('active', filter_var($request->get('active'), FILTER_VALIDATE_BOOLEAN));
        }
        $plans = $query->orderBy('name')->paginate($request->get('per_page', 15));
        return response()->json($plans);
    }

    public function show(Request $request, int $id)
    {
        $schoolId = $this->resolveSchoolId($request);
        if (!$schoolId) return response()->json(['message' => 'school_id required'], 422);
        $plan = BillingPlan::where('school_id', $schoolId)->findOrFail($id);
        return response()->json($plan);
    }

    public function store(Request $request)
    {
        $schoolId = $this->resolveSchoolId($request);
        if (!$schoolId) return response()->json(['message' => 'school_id required'], 422);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:128',
            'amount_cents' => 'required|integer|min:0',
            'currency' => 'nullable|string|max:8',
            'gateway_alias' => 'required|string|max:64',
            'periodicity' => 'nullable|string|in:monthly,bimonthly,annual',
            'day_of_month' => 'nullable|integer|min:1|max:28',
            'grace_days' => 'nullable|integer|min:0|max:30',
            'penalty_policy' => 'nullable|array',
            'allowed_payment_methods' => 'nullable|array',
            'penalty_policy_by_method' => 'nullable|array',
            'active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();
        $plan = new BillingPlan($data);
        $plan->school_id = $schoolId;
        $plan->currency = $data['currency'] ?? 'BRL';
        $plan->periodicity = $data['periodicity'] ?? 'monthly';
        $plan->day_of_month = $data['day_of_month'] ?? 5;
        $plan->active = $data['active'] ?? true;
        $plan->save();
        return response()->json(['id' => $plan->id], 201);
    }

    public function update(Request $request, int $id)
    {
        $schoolId = $this->resolveSchoolId($request);
        if (!$schoolId) return response()->json(['message' => 'school_id required'], 422);

        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:128',
            'amount_cents' => 'nullable|integer|min:0',
            'currency' => 'nullable|string|max:8',
            'gateway_alias' => 'nullable|string|max:64',
            'periodicity' => 'nullable|string|in:monthly,bimonthly,annual',
            'day_of_month' => 'nullable|integer|min:1|max:28',
            'grace_days' => 'nullable|integer|min:0|max:30',
            'penalty_policy' => 'nullable|array',
            'allowed_payment_methods' => 'nullable|array',
            'penalty_policy_by_method' => 'nullable|array',
            'active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $plan = BillingPlan::where('school_id', $schoolId)->findOrFail($id);
        $plan->fill($validator->validated());
        $plan->save();
        return response()->json(['message' => 'updated']);
    }

    public function destroy(Request $request, int $id)
    {
        $schoolId = $this->resolveSchoolId($request);
        if (!$schoolId) return response()->json(['message' => 'school_id required'], 422);
        $plan = BillingPlan::where('school_id', $schoolId)->findOrFail($id);
        $plan->delete();
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