<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WelcomeController extends Controller
{
    /**
     * Marca o modal de boas-vindas como visto para o usuário atual
     */
    public function dismiss(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'unauthenticated'], 401);
        }

        // Evitar acesso direto ao atributo em modo estrito; usar helper seguro
        if (method_exists($user, 'hasSeenWelcome') && !$user->hasSeenWelcome()) {
            $user->forceFill(['welcome_seen_at' => now()])->save();
        }

        return response()->json(['status' => 'ok', 'welcome_seen_at' => $user->welcome_seen_at]);
    }
}