<?php

namespace App\Policies;

use App\Models\Conversa;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ConversaPolicy
{
    use HandlesAuthorization;

    /**
     * Determina se o usuário pode visualizar conversas
     */
    public function viewAny(User $user): bool
    {
        return $user->temPermissao('conversas.ver');
    }

    /**
     * Determina se o usuário pode visualizar uma conversa específica
     */
    public function view(User $user, Conversa $conversa): bool
    {
        // Verifica permissão básica
        if (!$user->temPermissao('conversas.ver')) {
            return false;
        }

        // Verifica se o usuário é participante da conversa
        return $conversa->isParticipante($user->id);
    }

    /**
     * Determina se o usuário pode criar conversas
     */
    public function create(User $user): bool
    {
        return $user->temPermissao('conversas.criar');
    }

    /**
     * Determina se o usuário pode participar de conversas
     */
    public function participate(User $user, Conversa $conversa): bool
    {
        // Verifica permissão básica
        if (!$user->temPermissao('conversas.participar')) {
            return false;
        }

        // Super admins e suporte podem participar de qualquer conversa
        if ($user->isSuperAdmin() || $user->temCargo('Suporte')) {
            return true;
        }

        // Verifica se o usuário pertence à mesma escola
        if ($conversa->turma) {
            // Se a conversa está vinculada a uma turma, verifica se o usuário tem acesso à turma
            return $conversa->turma->escola_id === $user->escola_id;
        }

        // Para conversas gerais, verifica se todos os participantes são da mesma escola
        $participantes = $conversa->participantes;
        foreach ($participantes as $participante) {
            if ($participante->escola_id !== $user->escola_id) {
                return false;
            }
        }

        return true;
    }

    /**
     * Determina se o usuário pode editar uma conversa
     */
    public function update(User $user, Conversa $conversa): bool
    {
        // Apenas o criador da conversa pode editá-la
        return $conversa->criador_id === $user->id || 
               $user->isSuperAdmin() || 
               $user->temCargo('Suporte');
    }

    /**
     * Determina se o usuário pode excluir uma conversa
     */
    public function delete(User $user, Conversa $conversa): bool
    {
        // Apenas o criador da conversa pode excluí-la
        return $conversa->criador_id === $user->id || 
               $user->isSuperAdmin() || 
               $user->temCargo('Suporte');
    }

    /**
     * Determina se o usuário pode enviar mensagens na conversa
     */
    public function sendMessage(User $user, Conversa $conversa): bool
    {
        // Verifica se o usuário é participante e tem permissão
        return $this->participate($user, $conversa) && 
               $conversa->isParticipante($user->id);
    }

    /**
     * Determina se o usuário pode adicionar participantes
     */
    public function addParticipants(User $user, Conversa $conversa): bool
    {
        // Apenas o criador ou admins podem adicionar participantes
        return $conversa->criador_id === $user->id || 
               $user->isSuperAdmin() || 
               $user->temCargo('Suporte') ||
               $user->temCargo('Administrador') ||
               $user->temCargo('Coordenador');
    }

    /**
     * Determina se o usuário pode remover participantes
     */
    public function removeParticipants(User $user, Conversa $conversa): bool
    {
        // Apenas o criador ou admins podem remover participantes
        return $conversa->criador_id === $user->id || 
               $user->isSuperAdmin() || 
               $user->temCargo('Suporte') ||
               $user->temCargo('Administrador') ||
               $user->temCargo('Coordenador');
    }
}