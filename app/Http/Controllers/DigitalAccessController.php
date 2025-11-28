<?php

namespace App\Http\Controllers;

use App\Models\ArquivoDigital;
use App\Models\ItemBiblioteca;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DigitalAccessController extends Controller
{
    public function upload(Request $request, $itemId)
    {
        $item = ItemBiblioteca::findOrFail($itemId);

        // Validar upload de arquivo digital ou capa
        $validated = $request->validate([
            'file' => 'required|file|max:51200', // 50MB genérico
            'tipo' => 'required|string|in:pdf,epub,mp3,mp4,capa',
        ]);

        $file = $validated['file'];
        $tipo = $validated['tipo'];

        // Regra de negócio: permitir apenas 1 livro (pdf/epub/mp3/mp4) e 1 capa por item
        $hasCover = $item->arquivosDigitais()->where('tipo', 'capa')->exists();
        $hasBook = $item->arquivosDigitais()->whereIn('tipo', ['pdf','epub','mp3','mp4'])->exists();
        if ($tipo === 'capa' && $hasCover) {
            // Se já existe capa, bloquear novo upload
            if ($request->header('X-Requested-With') === 'XMLHttpRequest' || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Já existe uma capa anexada. Exclua a capa antes de enviar outra.',
                    'item_id' => $item->id,
                ], 422);
            }
            return redirect()->back()->withErrors(['capa' => 'Já existe uma capa anexada. Exclua a capa antes de enviar outra.']);
        }
        if ($tipo !== 'capa' && $hasBook) {
            // Se já existe livro (qualquer tipo), bloquear novo upload
            if ($request->header('X-Requested-With') === 'XMLHttpRequest' || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Já existe um arquivo de livro anexado. Exclua o arquivo antes de enviar outro.',
                    'item_id' => $item->id,
                ], 422);
            }
            return redirect()->back()->withErrors(['file' => 'Já existe um arquivo de livro anexado. Exclua o arquivo antes de enviar outro.']);
        }

        // Se for capa: armazenar diretamente (redimensionamento será feito no cliente)
        if ($tipo === 'capa') {
            $filename = Str::uuid()->toString() . '.jpg'; // padronizar jpeg
            $path = $file->storeAs('library/' . $item->escola_id . '/covers', $filename);

            $digital = ArquivoDigital::create([
                'escola_id' => $item->escola_id,
                'item_id' => $item->id,
                'tipo' => 'capa',
                'storage_path' => $path,
                'tamanho' => $file->getSize(),
                'hash' => @hash_file('sha256', $file->getRealPath()) ?: null,
                'watermark' => null,
                'expires_at' => null,
            ]);

            if ($request->header('X-Requested-With') === 'XMLHttpRequest' || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'tipo' => 'capa',
                    'item_id' => $item->id,
                    'digital_id' => $digital->id,
                    'cover_url' => route('biblioteca.cover', ['digitalId' => $digital->id]),
                    'digitais_count' => $item->arquivosDigitais()->count(),
                ]);
            }
            return redirect()->route('biblioteca.index')->with('success', 'Capa anexada com sucesso.');
        }

        // Upload de arquivo digital padrão
        $filename = Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('library/' . $item->escola_id, $filename);

        $digital = ArquivoDigital::create([
            'escola_id' => $item->escola_id,
            'item_id' => $item->id,
            'tipo' => $tipo,
            'storage_path' => $path,
            'tamanho' => $file->getSize(),
            'hash' => hash_file('sha256', $file->getRealPath()),
            'watermark' => null,
            'expires_at' => null,
        ]);
        if ($request->header('X-Requested-With') === 'XMLHttpRequest' || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'tipo' => $tipo,
                'item_id' => $item->id,
                'digital_id' => $digital->id,
                'digitais_count' => $item->arquivosDigitais()->count(),
            ]);
        }
        return redirect()->route('biblioteca.index')->with('success', 'Arquivo digital anexado com sucesso.');
    }

    public function preview(Request $request, $digitalId)
    {
        $digital = ArquivoDigital::findOrFail($digitalId);
        // MVP: apenas baixa o arquivo; futuramente limitar preview
        if (!Storage::exists($digital->storage_path)) {
            abort(404);
        }
        return Storage::download($digital->storage_path);
    }

    public function cover(Request $request, $digitalId)
    {
        $digital = ArquivoDigital::findOrFail($digitalId);
        if ($digital->tipo !== 'capa') {
            abort(404);
        }
        if (!Storage::exists($digital->storage_path)) {
            abort(404);
        }
        $path = Storage::path($digital->storage_path);
        $content = @file_get_contents($path);
        if ($content === false) {
            abort(404);
        }
        // Retornar como JPEG inline
        return response($content, 200)->header('Content-Type', 'image/jpeg');
    }

    /**
     * Excluir arquivo digital (livro ou capa), respeitando regra de 1 livro + 1 capa.
     */
    public function destroy(Request $request, $digitalId)
    {
        $digital = ArquivoDigital::findOrFail($digitalId);
        $item = ItemBiblioteca::findOrFail($digital->item_id);

        // Remover do storage se existir
        if (Storage::exists($digital->storage_path)) {
            Storage::delete($digital->storage_path);
        }

        $tipo = $digital->tipo;
        $digital->delete();

        if ($request->header('X-Requested-With') === 'XMLHttpRequest' || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'item_id' => $item->id,
                'tipo' => $tipo,
                'digitais_count' => $item->arquivosDigitais()->count(),
            ]);
        }

        return redirect()->route('biblioteca.index')->with('success', 'Arquivo digital excluído com sucesso.');
    }
}