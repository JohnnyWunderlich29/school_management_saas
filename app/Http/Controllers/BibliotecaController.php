<?php

namespace App\Http\Controllers;

use App\Models\ItemBiblioteca;
use Illuminate\Http\Request;
use App\Models\Historico;

class BibliotecaController extends Controller
{
    public function index(Request $request)
    {
        $escolaId = auth()->user()->escola_id ?? $request->get('escola_id');
        $query = ItemBiblioteca::query();

        if ($escolaId) {
            $query->where('escola_id', $escolaId);
        }

        // Filtros padrão
        if ($request->filled('titulo')) {
            $query->where('titulo', 'like', '%' . trim($request->get('titulo')) . '%');
        }

        if ($request->filled('autores')) {
            $query->where('autores', 'like', '%' . trim($request->get('autores')) . '%');
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->get('tipo'));
        }

        // Filtro por habilitado para empréstimo
        if ($request->filled('habilitado_emprestimo')) {
            $habilitado = $request->get('habilitado_emprestimo');
            if ($habilitado === '1' || $habilitado === '0') {
                $query->where('habilitado_emprestimo', (bool) ((int) $habilitado));
            }
        }

        // Ordenação padrão
        $allowedSorts = ['id', 'titulo', 'autores', 'tipo', 'created_at'];
        $sort = $request->get('sort', 'titulo');
        $direction = $request->get('direction', 'asc') === 'desc' ? 'desc' : 'asc';
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'titulo';
        }

        // Eager loading de anexos digitais para evitar N+1 e permitir contagem/preview
        $query->with(['arquivosDigitais:id,item_id,tipo'])
              ->withCount('arquivosDigitais');

        $items = $query->orderBy($sort, $direction)->paginate(15)->withQueryString();

        return view('biblioteca.index', [
            'items' => $items,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'autores' => 'nullable|string|max:255',
            'editora' => 'nullable|string|max:255',
            'ano' => 'nullable|integer',
            'isbn' => 'nullable|string|max:50',
            'tipo' => 'required|string|in:livro,revista,digital,audio,video',
            'categorias' => 'nullable|array',
            'palavras_chave' => 'nullable|array',
            'quantidade_fisica' => 'nullable|integer|min:0',
            'metadata' => 'nullable|array',
            'status' => 'nullable|string|in:ativo,inativo,disponivel,indisponivel',
            'habilitado_emprestimo' => 'nullable|boolean',
        ]);

        // Resolver escola_id considerando Super Admin/Suporte e contexto de sessão
        $user = auth()->user();
        if ($user && ($user->isSuperAdmin() || $user->temCargo('Suporte'))) {
            $escolaId = $request->get('escola_id') ?: session('escola_atual') ?: $user->escola_id;
        } else {
            $escolaId = $user ? ($user->escola_id ?: $request->get('escola_id')) : $request->get('escola_id');
        }

        // Bloquear criação sem escola definida
        if (!$escolaId) {
            return back()
                ->withErrors(['escola_id' => 'Selecione uma escola para cadastrar itens da biblioteca.'])
                ->withInput();
        }

        $validated['escola_id'] = $escolaId;
        // Default para habilitado_emprestimo quando não enviado explicitamente
        if (!array_key_exists('habilitado_emprestimo', $validated)) {
            $validated['habilitado_emprestimo'] = true;
        }

        $item = ItemBiblioteca::create($validated);
        Historico::registrar('criado', 'ItemBiblioteca', $item->id, null, $item->toArray());

        // Responder em JSON para submissões via AJAX
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Item criado com sucesso.',
                'item' => $item,
            ]);
        }

        return redirect()->route('biblioteca.index')->with('success', 'Item criado com sucesso.');
    }

    public function update(Request $request, ItemBiblioteca $item)
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'autores' => 'nullable|string|max:255',
            'editora' => 'nullable|string|max:255',
            'ano' => 'nullable|integer',
            'isbn' => 'nullable|string|max:50',
            'tipo' => 'required|string|in:livro,revista,digital,audio,video',
            'categorias' => 'nullable|array',
            'palavras_chave' => 'nullable|array',
            'quantidade_fisica' => 'nullable|integer|min:0',
            'metadata' => 'nullable|array',
            'status' => 'nullable|string|in:ativo,inativo,disponivel,indisponivel',
            'habilitado_emprestimo' => 'nullable|boolean',
        ]);

        // Garantir escola correta no contexto de edição
        $user = auth()->user();
        $escolaContext = $user ? ($user->escola_id ?: session('escola_atual')) : session('escola_atual');

        if ($escolaContext && $item->escola_id && $item->escola_id !== $escolaContext && !($user && ($user->isSuperAdmin() || $user->temCargo('Suporte')))) {
            return back()->withErrors(['escola_id' => 'Você não pode editar itens de outra escola.']);
        }

        $dadosAntigos = $item->toArray();
        $item->fill($validated);
        $item->save();
        $dadosNovos = $item->fresh()->toArray();
        Historico::registrar('atualizado', 'ItemBiblioteca', $item->id, $dadosAntigos, $dadosNovos);

        return redirect()->route('biblioteca.index')->with('success', 'Item atualizado com sucesso.');
    }

    public function getItemUploads(Request $request, $itemId)
    {
        $item = ItemBiblioteca::with('arquivosDigitais')->find($itemId);
        
        if (!$item) {
            return response()->json(['error' => 'Item não encontrado'], 404);
        }

        // Verificar permissão de acesso ao item
        $user = auth()->user();
        $escolaContext = $user ? ($user->escola_id ?: session('escola_atual')) : session('escola_atual');

        if ($escolaContext && $item->escola_id && $item->escola_id !== $escolaContext && !($user && ($user->isSuperAdmin() || $user->temCargo('Suporte')))) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }

        return response()->json([
            'success' => true,
            'digitais' => $item->arquivosDigitais->map(function($digital) {
                return [
                    'id' => $digital->id,
                    'tipo' => $digital->tipo,
                    'nome_arquivo' => $digital->nome_arquivo ?? null,
                ];
            })
        ]);
    }
}
