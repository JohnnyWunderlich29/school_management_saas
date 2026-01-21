<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Notification;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SolicitacaoAcessoController extends Controller
{
    /**
     * Handle the incoming access request.
     */
    public function store(Request $request)
    {
        $request->validate([
            'url' => 'required|url',
        ]);

        $user = auth()->user();
        $url = $request->input('url');
        $escolaId = $user->escola_id;

        if (!$escolaId) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não vinculado a uma escola.'
            ], 422);
        }

        try {
            // Find school administrators
            $admins = User::where('escola_id', $escolaId)
                ->whereHas('cargos', function ($query) {
                    $query->where('nome', 'like', '%Administrador%')
                          ->orWhere('tipo_cargo', 'admin');
                })
                ->get();

            if ($admins->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nenhum administrador encontrado para esta escola.'
                ], 404);
            }

            // Send notification to each admin
            DB::beginTransaction();
            
            foreach ($admins as $admin) {
                // Check if a similar notification was sent recently to avoid spam (optional, simple check)
                // For now, we allow sending.
                
                Notification::createForUser(
                    $admin->id,
                    'info', // Type
                    'Solicitação de Acesso', // Title
                    "O usuário {$user->name} solicitou acesso à página: {$url}", // Message
                    ['requested_url' => $url, 'requester_id' => $user->id], // Data
                    route('funcionarios.edit', $user->id), // Action URL (Go to employee edit to give permissions)
                    'Gerenciar Permissões' // Action Text
                );
            }
            
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Solicitação enviada com sucesso ao administrador.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao enviar solicitação de acesso: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao processar sua solicitação.'
            ], 500);
        }
    }
}
