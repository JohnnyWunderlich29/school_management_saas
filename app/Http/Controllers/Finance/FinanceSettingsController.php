<?php

namespace App\Http\Controllers\Finance;

use App\Models\Finance\FinanceSettings;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;

class FinanceSettingsController extends Controller
{
    public function show(Request $request)
    {
        $schoolId = $this->resolveSchoolId($request);
        if (!$schoolId) {
            return response()->json(['message' => 'school_id required'], 422);
        }

        $settings = FinanceSettings::firstOrCreate(
            ['school_id' => $schoolId],
            ['currency' => 'BRL']
        );

        return response()->json($settings);
    }

    public function update(Request $request)
    {
        $schoolId = $this->resolveSchoolId($request);
        if (!$schoolId) {
            return response()->json(['message' => 'school_id required'], 422);
        }

        $validator = Validator::make($request->all(), [
            'default_gateway_alias' => 'nullable|string|max:64',
            'repasse_bank_account' => 'nullable|array',
            'penalty_policy' => 'nullable|array',
            'dunning_schedule' => 'nullable|array',
            'allowed_payment_methods' => 'nullable|array',
            'invoice_numbering' => 'nullable|array',
            'legal_texts' => 'nullable|array',
            'timezone' => 'nullable|string|max:64',
            'currency' => 'nullable|string|max:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $settings = FinanceSettings::firstOrCreate(
            ['school_id' => $schoolId],
            ['currency' => 'BRL']
        );

        $settings->fill($validator->validated());
        $settings->save();

        return response()->json($settings);
    }

    private function resolveSchoolId(Request $request): ?int
    {
        // Prefer authenticated user context, fallback to request parameter
        $user = $request->user();
        if ($user) {
            if (isset($user->school_id) && $user->school_id) return (int)$user->school_id;
            if (isset($user->escola_id) && $user->escola_id) return (int)$user->escola_id;
        }
        $schoolId = $request->input('school_id') ?? $request->input('escola_id');
        return $schoolId ? (int) $schoolId : null;
    }
}