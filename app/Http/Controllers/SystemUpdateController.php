<?php

namespace App\Http\Controllers;

use App\Models\SystemUpdate;
use App\Models\SystemUpdateView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;

class SystemUpdateController extends Controller
{
    // API: list recent updates (authenticated users)
    public function listRecent()
    {
        if (!Auth::check()) {
            return response()->json(['updates' => []]);
        }
        $updates = SystemUpdate::orderByDesc('created_at')->limit(10)->get();
        return response()->json(['updates' => $updates]);
    }

    public function index()
    {
        $this->authorizeAccess();
        $updates = SystemUpdate::with('creator')->orderByDesc('created_at')->paginate(15);
        return view('corporativo.atualizacoes.index', compact('updates'));
    }

    public function create()
    {
        $this->authorizeAccess();
        return view('corporativo.atualizacoes.create');
    }

    public function store(Request $request)
    {
        $this->authorizeAccess();
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'image' => ['nullable', 'image', 'max:10240'], // 10MB
        ], [
            'image.image' => 'O arquivo deve ser uma imagem válida.',
            'image.max' => 'A imagem não pode exceder 10MB.',
        ]);

        $path = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('system_updates', 'public');
        }

        $update = SystemUpdate::create([
            'title' => $data['title'],
            'body' => $data['body'],
            'image_path' => $path ? ('storage/' . $path) : null,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('corporativo.atualizacoes.index')->with('success', 'Atualização criada com sucesso.');
    }

    public function edit(SystemUpdate $atualizacao)
    {
        $this->authorizeAccess();
        return view('corporativo.atualizacoes.edit', ['update' => $atualizacao]);
    }

    public function update(Request $request, SystemUpdate $atualizacao)
    {
        $this->authorizeAccess();
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'image' => ['nullable', 'image', 'max:10240'], // 10MB
        ], [
            'image.image' => 'O arquivo deve ser uma imagem válida.',
            'image.max' => 'A imagem não pode exceder 10MB.',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('system_updates', 'public');
            $atualizacao->image_path = 'storage/' . $path;
        }

        $atualizacao->title = $data['title'];
        $atualizacao->body = $data['body'];
        $atualizacao->save();

        return redirect()->route('corporativo.atualizacoes.index')->with('success', 'Atualização atualizada com sucesso.');
    }

    public function destroy(SystemUpdate $atualizacao)
    {
        $this->authorizeAccess();
        $atualizacao->delete();
        return redirect()->route('corporativo.atualizacoes.index')->with('success', 'Atualização excluída.');
    }

    // API: latest unseen for current user
    public function latestUnseen()
    {
        $userId = Auth::id();
        if (!$userId) {
            return response()->json(['update' => null]);
        }
        $update = SystemUpdate::whereDoesntHave('views', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->orderByDesc('created_at')
            ->first();

        return response()->json(['update' => $update]);
    }

    // API: mark viewed
    public function markViewed(SystemUpdate $update)
    {
        $userId = Auth::id();
        if (!$userId) {
            return response()->json(['success' => false], 401);
        }
        SystemUpdateView::updateOrCreate(
            [
                'system_update_id' => $update->id,
                'user_id' => $userId,
            ],
            [
                'viewed_at' => now(),
            ]
        );
        return response()->json(['success' => true]);
    }

    private function authorizeAccess(): void
    {
        $user = Auth::user();
        if (!$user || (!($user->hasRole('superadmin') || $user->hasRole('admin')))) {
            abort(403);
        }
    }
}