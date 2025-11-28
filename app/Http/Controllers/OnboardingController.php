<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class OnboardingController extends Controller
{
    public function index(Request $request)
    {
        $steps = Config::get('onboarding.steps', []);
        $prefs = Auth::check() ? (Auth::user()->preferences ?? []) : [];
        $completed = $prefs['onboarding_completed'] ?? $request->session()->get('onboarding.completed', []);

        $total = count($steps);
        $done = count($completed);
        $progress = $total > 0 ? (int) round(($done / $total) * 100) : 0;

        return view('corporativo.onboarding.index', [
            'steps' => $steps,
            'completed' => $completed,
            'progress' => $progress,
        ]);
    }

    public function toggle(Request $request, string $slug)
    {
        $steps = collect(Config::get('onboarding.steps', []));
        $exists = $steps->contains(fn ($s) => ($s['slug'] ?? null) === $slug);

        if (! $exists) {
            abort(404);
        }

        $prefs = Auth::check() ? (Auth::user()->preferences ?? []) : [];
        $completed = $prefs['onboarding_completed'] ?? $request->session()->get('onboarding.completed', []);

        if (in_array($slug, $completed, true)) {
            $completed = array_values(array_diff($completed, [$slug]));
        } else {
            $completed[] = $slug;
        }

        if (Auth::check()) {
            $user = Auth::user();
            $targetUser = $user;
            // SuperAdmin/Suporte: aplicar mudança ao dono da escola atual
            if ($user->isSuperAdmin() || $user->temCargo('Suporte')) {
                $escolaId = session('escola_atual') ?: $user->escola_id;
                if ($escolaId) {
                    $owner = User::where('escola_id', $escolaId)->orderBy('id')->first();
                    if ($owner) {
                        $targetUser = $owner;
                    }
                }
            }

            $prefs = $targetUser->preferences ?? [];
            $prefs['onboarding_completed'] = $completed;
            $targetUser->preferences = $prefs;
            $targetUser->save();
        } else {
            $request->session()->put('onboarding.completed', $completed);
        }

        if ($request->wantsJson()) {
            return response()->json(['completed' => $completed]);
        }

        return redirect()->route('onboarding.index');
    }

    public function minimize(Request $request)
    {
        $state = (bool) $request->input('minimized', true);

        if (Auth::check()) {
            $user = Auth::user();
            $targetUser = $user;
            if ($user->isSuperAdmin() || $user->temCargo('Suporte')) {
                $escolaId = session('escola_atual') ?: $user->escola_id;
                if ($escolaId) {
                    $owner = User::where('escola_id', $escolaId)->orderBy('id')->first();
                    if ($owner) {
                        $targetUser = $owner;
                    }
                }
            }
            $prefs = $targetUser->preferences ?? [];
            $prefs['onboarding_minimized'] = $state;
            $targetUser->preferences = $prefs;
            $targetUser->save();
        } else {
            $request->session()->put('onboarding.minimized', $state);
        }

        return response()->json(['minimized' => $state]);
    }

    public function close(Request $request)
    {
        $state = (bool) $request->input('closed', true);

        if (Auth::check()) {
            $user = Auth::user();
            $targetUser = $user;
            if ($user->isSuperAdmin() || $user->temCargo('Suporte')) {
                $escolaId = session('escola_atual') ?: $user->escola_id;
                if ($escolaId) {
                    $owner = User::where('escola_id', $escolaId)->orderBy('id')->first();
                    if ($owner) {
                        $targetUser = $owner;
                    }
                }
            }
            $prefs = $targetUser->preferences ?? [];
            $prefs['onboarding_closed'] = $state;
            $targetUser->preferences = $prefs;
            $targetUser->save();
        } else {
            $request->session()->put('onboarding.closed', $state);
        }

        return response()->json(['closed' => $state]);
    }

    public function reopen(Request $request)
    {
        if (Auth::check()) {
            $user = Auth::user();

            // Se for SuperAdmin ou Suporte, aplicar a reabertura ao dono da escola atual
            if ($user->isSuperAdmin() || $user->temCargo('Suporte')) {
                $escolaId = session('escola_atual') ?: $user->escola_id;
                if ($escolaId) {
                    $owner = User::where('escola_id', $escolaId)->orderBy('id')->first();
                    if ($owner) {
                        $prefs = $owner->preferences ?? [];
                        $prefs['onboarding_closed'] = false;
                        $prefs['onboarding_minimized'] = false;
                        $owner->preferences = $prefs;
                        $owner->save();
                    } else {
                        // Sem dono identificado, reabrir para o próprio usuário como fallback
                        $prefs = $user->preferences ?? [];
                        $prefs['onboarding_closed'] = false;
                        $prefs['onboarding_minimized'] = false;
                        $user->preferences = $prefs;
                        $user->save();
                    }
                } else {
                    // Sem escola no contexto, reabrir para o próprio usuário
                    $prefs = $user->preferences ?? [];
                    $prefs['onboarding_closed'] = false;
                    $prefs['onboarding_minimized'] = false;
                    $user->preferences = $prefs;
                    $user->save();
                }
            } else {
                // Usuário comum: reabrir para si próprio
                $prefs = $user->preferences ?? [];
                $prefs['onboarding_closed'] = false;
                $prefs['onboarding_minimized'] = false;
                $user->preferences = $prefs;
                $user->save();
            }
        } else {
            $request->session()->put('onboarding.closed', false);
            $request->session()->put('onboarding.minimized', false);
        }

        if ($request->wantsJson()) {
            return response()->json(['reopened' => true]);
        }

        return back();
    }
}