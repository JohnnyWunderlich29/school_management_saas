<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\DashboardPreference;

class DashboardPreferenceController extends Controller
{
    /**
     * Retorna preferências do dashboard do usuário para a escola atual.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'unauthenticated'], 401);
        }

        $schoolId = $this->resolveSchoolId($request);

        $pref = DashboardPreference::where('user_id', $user->id)
            ->where('school_id', $schoolId)
            ->first();

        if (!$pref) {
            return response()->json(['state' => null]);
        }

        return response()->json([
            'state' => $pref->state,
            'updated_at' => $pref->updated_at,
        ]);
    }

    /**
     * Salva preferências do dashboard.
     */
    public function save(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'unauthenticated'], 401);
        }

        $validated = $request->validate([
            'state' => 'required|array',
        ]);

        $schoolId = $this->resolveSchoolId($request);

        $pref = DashboardPreference::updateOrCreate(
            ['user_id' => $user->id, 'school_id' => $schoolId],
            ['state' => $validated['state']]
        );

        return response()->json(['ok' => true, 'state' => $pref->state]);
    }

    /**
     * Remove preferências (restaura padrão).
     */
    public function destroy(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'unauthenticated'], 401);
        }

        $schoolId = $this->resolveSchoolId($request);

        DashboardPreference::where('user_id', $user->id)
            ->where('school_id', $schoolId)
            ->delete();

        return response()->json(['ok' => true]);
    }

    private function resolveSchoolId(Request $request): ?int
    {
        $user = Auth::user();
        if (!$user) {
            return null;
        }

        // SuperAdmin/Suporte priorizam sessão; demais usam escola do usuário
        try {
            if (method_exists($user, 'isSuperAdmin') && ($user->isSuperAdmin() || (method_exists($user, 'temCargo') && $user->temCargo('Suporte')))) {
                return session('escola_atual') ?: ($request->integer('school_id') ?: $user->escola_id);
            }
        } catch (\Throwable $e) {
            Log::warning('resolveSchoolId: '.$e->getMessage());
        }

        return $user->escola_id;
    }
}