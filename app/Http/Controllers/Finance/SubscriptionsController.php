<?php

namespace App\Http\Controllers\Finance;

use App\Models\Finance\Subscription;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;

class SubscriptionsController extends Controller
{
    public function index(Request $request)
    {
        $schoolId = $this->resolveSchoolId($request);
        if (!$schoolId)
            return response()->json(['message' => 'school_id required'], 422);
        $query = Subscription::where('school_id', $schoolId);
        if ($request->filled('status'))
            $query->where('status', $request->get('status'));
        if ($request->filled('student_id'))
            $query->where('student_id', (int) $request->get('student_id'));
        if ($request->filled('payer_id'))
            $query->where('payer_id', (int) $request->get('payer_id'));
        if ($request->filled('billing_plan_id'))
            $query->where('billing_plan_id', (int) $request->get('billing_plan_id'));
        $subs = $query->orderByDesc('id')->paginate($request->get('per_page', 15));
        return response()->json($subs);
    }

    public function show(Request $request, int $id)
    {
        $schoolId = $this->resolveSchoolId($request);
        if (!$schoolId)
            return response()->json(['message' => 'school_id required'], 422);
        $sub = Subscription::where('school_id', $schoolId)->findOrFail($id);
        return response()->json($sub);
    }

    public function store(Request $request)
    {
        $schoolId = $this->resolveSchoolId($request);
        if (!$schoolId)
            return response()->json(['message' => 'school_id required'], 422);
        $validator = Validator::make($request->all(), [
            'student_id' => 'nullable|integer|min:1',
            'payer_id' => 'required|integer|min:1',
            'billing_plan_id' => 'nullable|integer|min:1',
            'amount_cents' => 'required|integer|min:0',
            'currency' => 'nullable|string|max:8',
            'charge_method_id' => 'required|integer|min:1',
            'day_of_month' => 'nullable|integer|min:1|max:31',
            'status' => 'nullable|string|in:active,paused,canceled',
            'description' => 'nullable|string|max:255',
            'start_at' => 'nullable|date',
            'end_at' => 'nullable|date|after_or_equal:start_at',
            'discount_percent' => 'nullable|integer|min:0|max:100',
            'early_discount_value' => 'nullable|integer|min:0|max:100',
            'early_discount_days' => 'nullable|integer|min:0|max:31',
            'early_discount_active' => 'nullable|boolean',
            'notes' => 'nullable|string|max:1024',
            'last_billed_at' => 'nullable|date',
        ]);
        if ($validator->fails())
            return response()->json(['errors' => $validator->errors()], 422);
        $data = $validator->validated();
        if (!isset($data['start_at']) || empty($data['start_at'])) {
            $data['start_at'] = now()->toDateString();
        }
        if (!isset($data['currency']))
            $data['currency'] = 'BRL';
        $sub = new Subscription($data);
        $sub->school_id = $schoolId;
        $sub->status = $sub->status ?? 'active';
        $sub->save();
        return response()->json(['id' => $sub->id], 201);
    }

    public function update(Request $request, int $id)
    {
        $schoolId = $this->resolveSchoolId($request);
        if (!$schoolId)
            return response()->json(['message' => 'school_id required'], 422);
        $validator = Validator::make($request->all(), [
            'student_id' => 'nullable|integer|min:1',
            'payer_id' => 'nullable|integer|min:1',
            'billing_plan_id' => 'nullable|integer|min:1',
            'amount_cents' => 'nullable|integer|min:0',
            'currency' => 'nullable|string|max:8',
            'charge_method_id' => 'nullable|integer|min:1',
            'day_of_month' => 'nullable|integer|min:1|max:31',
            'status' => 'nullable|string|in:active,paused,canceled',
            'description' => 'nullable|string|max:255',
            'start_at' => 'nullable|date',
            'end_at' => 'nullable|date|after_or_equal:start_at',
            'discount_percent' => 'nullable|integer|min:0|max:100',
            'early_discount_value' => 'nullable|integer|min:0|max:100',
            'early_discount_days' => 'nullable|integer|min:0|max:31',
            'early_discount_active' => 'nullable|boolean',
            'notes' => 'nullable|string|max:1024',
            'last_billed_at' => 'nullable|date',
        ]);
        if ($validator->fails())
            return response()->json(['errors' => $validator->errors()], 422);
        $sub = Subscription::where('school_id', $schoolId)->findOrFail($id);
        $sub->fill($validator->validated());
        $sub->save();
        return response()->json(['message' => 'updated']);
    }

    public function destroy(Request $request, int $id)
    {
        $schoolId = $this->resolveSchoolId($request);
        if (!$schoolId)
            return response()->json(['message' => 'school_id required'], 422);
        $sub = Subscription::where('school_id', $schoolId)->findOrFail($id);
        $sub->delete();
        return response()->json(['message' => 'deleted']);
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
}