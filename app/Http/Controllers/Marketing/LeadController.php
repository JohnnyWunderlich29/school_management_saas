<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function store(Request $request)
    {
        if ($request->filled('website')) {
            return redirect()->back()->withErrors(['message' => 'Falha ao enviar.'])->withInput();
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2'],
            'email' => ['required', 'email'],
            'phone' => ['nullable', 'string'],
            'school' => ['nullable', 'string'],
            'role' => ['nullable', 'string'],
            'message' => ['required', 'string', 'min:10'],
            'consent' => ['accepted'],
            'utm_source' => ['nullable', 'string'],
            'utm_medium' => ['nullable', 'string'],
            'utm_campaign' => ['nullable', 'string'],
            'utm_term' => ['nullable', 'string'],
            'utm_content' => ['nullable', 'string'],
        ]);

        $lead = new Lead();
        $lead->fill($validated);
        $lead->origin_url = $request->input('origin_url') ?: $request->headers->get('referer');
        $lead->status = 'new';
        $lead->save();

        return redirect()->route('institucional.contato')->with('status', 'Recebemos seu contato. Em breve retornaremos!');
    }
}

