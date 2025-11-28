<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class NotificationController extends Controller
{
    /**
     * Exibir lista de notificações do usuário
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        
        if (!$user) {
            abort(401, 'Usuário não autenticado');
        }

        $query = Notification::forUser($user->id)
            ->orderBy('created_at', 'desc');

        // Aplicar filtros
        if ($request->filled('type')) {
            $query->ofType($request->type);
        }

        if ($request->filled('status')) {
            if ($request->status === 'read') {
                $query->read();
            } elseif ($request->status === 'unread') {
                $query->unread();
            }
        }

        $notifications = $query->paginate(20);
        
        // Estatísticas
        $stats = [
            'total' => Notification::forUser($user->id)->count(),
            'unread' => Notification::getUnreadCountForUser($user->id),
            'today' => Notification::forUser($user->id)
                ->whereDate('created_at', today())
                ->count()
        ];

        // Tipos disponíveis para filtro
        $types = Notification::forUser($user->id)
            ->distinct()
            ->pluck('type')
            ->filter()
            ->sort()
            ->values();

        return view('notifications.index', compact('notifications', 'stats', 'types'));
    }

    /**
     * Obter notificações não lidas (para AJAX)
     */
    public function unread(): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'error' => 'Usuário não autenticado',
                    'notifications' => [],
                    'count' => 0
                ], 401);
            }

            $notifications = Notification::forUser($user->id)
                ->unread()
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($notification) {
                    return [
                        'id' => $notification->id,
                        'type' => $notification->type,
                        'title' => $notification->title,
                        'message' => $notification->message,
                        'data' => $notification->data,
                        'action_url' => $notification->action_url,
                        'action_text' => $notification->action_text,
                        'is_global' => $notification->is_global,
                        'created_at' => $notification->created_at->toISOString(),
                        'created_at_formatted' => $notification->created_at_formatted,
                        'created_at_relative' => $notification->created_at_relative,
                        'type_class' => $notification->type_class,
                        'type_icon' => $notification->type_icon
                    ];
                });

            $totalCount = Notification::getUnreadCountForUser($user->id);
            
            return response()->json([
                'success' => true,
                'notifications' => $notifications,
                'count' => $totalCount,
                'user_authenticated' => true,
                'user_id' => $user->id
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao carregar notificações não lidas', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Erro interno do servidor',
                'notifications' => [],
                'count' => 0
            ], 500);
        }
    }

    /**
     * Obter contagem de notificações não lidas
     */
    public function getUnreadCount(): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'error' => 'Usuário não autenticado',
                    'count' => 0
                ], 401);
            }

            $count = Notification::getUnreadCountForUser($user->id);

            return response()->json([
                'success' => true,
                'count' => $count,
                'user_authenticated' => true
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao obter contagem de notificações', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Erro interno do servidor',
                'count' => 0
            ], 500);
        }
    }

    /**
     * Marcar notificação como lida
     */
    public function markAsRead(Notification $notification): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'error' => 'Usuário não autenticado'
                ], 401);
            }

            // Verificar se o usuário pode marcar esta notificação
            if ($notification->user_id !== $user->id && !$notification->is_global) {
                return response()->json([
                    'error' => 'Não autorizado a marcar esta notificação'
                ], 403);
            }

            $notification->markAsRead();

            Log::info('Notificação marcada como lida', [
                'notification_id' => $notification->id,
                'user_id' => $user->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Notificação marcada como lida',
                'unread_count' => Notification::getUnreadCountForUser($user->id)
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao marcar notificação como lida', [
                'notification_id' => $notification->id ?? null,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Marcar todas as notificações como lidas
     */
    public function markAllAsRead(): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'error' => 'Usuário não autenticado'
                ], 401);
            }

            $count = Notification::markAllAsReadForUser($user->id);

            Log::info('Todas as notificações marcadas como lidas', [
                'user_id' => $user->id,
                'count' => $count
            ]);

            return response()->json([
                'success' => true,
                'message' => "$count notificações marcadas como lidas",
                'unread_count' => 0
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao marcar todas as notificações como lidas', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Excluir notificação
     */
    public function destroy(Notification $notification): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'error' => 'Usuário não autenticado'
                ], 401);
            }

            // Verificar se o usuário pode excluir esta notificação
            if ($notification->user_id !== $user->id && !$notification->is_global) {
                return response()->json([
                    'error' => 'Não autorizado a excluir esta notificação'
                ], 403);
            }

            $notification->delete();

            Log::info('Notificação excluída', [
                'notification_id' => $notification->id,
                'user_id' => $user->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Notificação excluída com sucesso',
                'unread_count' => Notification::getUnreadCountForUser($user->id)
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao excluir notificação', [
                'notification_id' => $notification->id ?? null,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Mark multiple notifications as read.
     */
    public function markMultipleAsRead(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'notification_ids' => 'required|array',
                'notification_ids.*' => 'integer|exists:notifications,id'
            ]);

            $notificationIds = $request->notification_ids;
            $userId = auth()->id();

            // Marcar apenas notificações que pertencem ao usuário ou são globais
            $updated = Notification::whereIn('id', $notificationIds)
                ->where(function ($query) use ($userId) {
                    $query->where('user_id', $userId)
                          ->orWhere('is_global', true);
                })
                ->whereNull('read_at')
                ->update(['read_at' => now()]);

            return response()->json([
                'success' => true,
                'message' => "$updated notificações marcadas como lidas.",
                'updated_count' => $updated
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao marcar múltiplas notificações como lidas: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor.'
            ], 500);
        }
    }

    /**
     * Delete multiple notifications.
     */
    public function deleteMultiple(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'notification_ids' => 'required|array',
                'notification_ids.*' => 'integer|exists:notifications,id'
            ]);

            $notificationIds = $request->notification_ids;
            $userId = auth()->id();

            // Excluir apenas notificações que pertencem ao usuário ou são globais
            $deleted = Notification::whereIn('id', $notificationIds)
                ->where(function ($query) use ($userId) {
                    $query->where('user_id', $userId)
                          ->orWhere('is_global', true);
                })
                ->delete();

            return response()->json([
                'success' => true,
                'message' => "$deleted notificações excluídas com sucesso.",
                'deleted_count' => $deleted
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao excluir múltiplas notificações: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor.'
            ], 500);
        }
    }

    /**
     * Exibir formulário para criar notificação
     */
    public function create(): View
    {
        $types = ['success', 'info', 'warning', 'error'];
        return view('notifications.create', compact('types'));
    }

    /**
     * Criar nova notificação
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'type' => ['required', Rule::in(['success', 'info', 'warning', 'error'])],
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
            'is_global' => 'boolean',
            'user_id' => 'nullable|exists:users,id',
            'action_url' => 'nullable|url|max:255',
            'action_text' => 'nullable|string|max:100'
        ]);

        try {
            if ($request->boolean('is_global')) {
                $notification = Notification::createGlobal(
                    $request->type,
                    $request->title,
                    $request->message,
                    [],
                    $request->action_url,
                    $request->action_text
                );
            } else {
                $userId = $request->user_id ?? Auth::id();
                $notification = Notification::createForUser(
                    $userId,
                    $request->type,
                    $request->title,
                    $request->message,
                    [],
                    $request->action_url,
                    $request->action_text
                );
            }

            Log::info('Nova notificação criada', [
                'notification_id' => $notification->id,
                'type' => $notification->type,
                'is_global' => $notification->is_global,
                'created_by' => Auth::id()
            ]);

            return redirect()->route('notifications.index')
                ->with('success', 'Notificação criada com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao criar notificação', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Erro ao criar notificação. Tente novamente.');
        }
    }

    /**
     * Diagnóstico do sistema de notificações
     */
    public function diagnostic(): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $diagnostics = [
                'timestamp' => now()->toISOString(),
                'user_authenticated' => !is_null($user),
                'user_id' => $user?->id,
                'user_name' => $user?->name,
                'session_id' => session()->getId(),
                'csrf_token' => csrf_token(),
                'database_connection' => true,
                'notifications_table_exists' => true,
                'total_notifications' => 0,
                'user_notifications' => 0,
                'global_notifications' => 0,
                'unread_notifications' => 0
            ];

            // Testar conexão com banco de dados
            try {
                $diagnostics['total_notifications'] = Notification::count();
                $diagnostics['global_notifications'] = Notification::where('is_global', true)->count();
                
                if ($user) {
                    $diagnostics['user_notifications'] = Notification::forUser($user->id)->count();
                    $diagnostics['unread_notifications'] = Notification::getUnreadCountForUser($user->id);
                }
            } catch (\Exception $e) {
                $diagnostics['database_connection'] = false;
                $diagnostics['database_error'] = $e->getMessage();
            }

            return response()->json($diagnostics);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro no diagnóstico',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}