<?php

namespace App\Policies;

use App\Models\Comunicado;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ComunicadoPolicy
{
    use HandlesAuthorization;

    /**
     * Determina se o usuário pode visualizar comunicados
     */
    public function viewAny(User $user): bool
    {
        return $user->temPermissao('comunicados.ver');
    }

    /**
     * Determina se o usuário pode visualizar um comunicado específico
     */
    public function view(User $user, Comunicado $comunicado): bool
    {
        // Verifica permissão básica
        if (!$user->temPermissao('comunicados.ver')) {
            return false;
        }

        // Verifica se o comunicado pertence à mesma escola do usuário
        if ($user->isSuperAdmin() || $user->temCargo('Suporte')) {
            return true;
        }

        return $comunicado->autor->escola_id === $user->escola_id;
    }

    /**
     * Determina se o usuário pode criar comunicados
     */
    public function create(User $user): bool
    {
        return $user->temPermissao('comunicados.criar');
    }

    /**
     * Determina se o usuário pode editar um comunicado
     */
    public function update(User $user, Comunicado $comunicado): bool
    {
        // Verifica permissão básica
        if (!$user->temPermissao('comunicados.editar')) {
            return false;
        }

        // Super admins e suporte podem editar qualquer comunicado
        if ($user->isSuperAdmin() || $user->temCargo('Suporte')) {
            return true;
        }

        // Usuários normais só podem editar comunicados da sua escola
        // e que ainda não foram publicados ou são autores
        return $comunicado->autor->escola_id === $user->escola_id && 
               ($comunicado->autor_id === $user->id || !$comunicado->publicado_em);
    }

    /**
     * Determina se o usuário pode excluir um comunicado
     */
    public function delete(User $user, Comunicado $comunicado): bool
    {
        // Verifica permissão básica
        if (!$user->temPermissao('comunicados.excluir')) {
            return false;
        }

        // Super admins e suporte podem excluir qualquer comunicado
        if ($user->isSuperAdmin() || $user->temCargo('Suporte')) {
            return true;
        }

        // Usuários normais só podem excluir comunicados da sua escola
        // e que são autores ou ainda não foram publicados
        return $comunicado->autor->escola_id === $user->escola_id && 
               ($comunicado->autor_id === $user->id || !$comunicado->publicado_em);
    }

    /**
     * Determina se o usuário pode publicar um comunicado
     */
    public function publish(User $user, Comunicado $comunicado): bool
    {
        // Verifica permissão básica
        if (!$user->temPermissao('comunicados.publicar')) {
            return false;
        }

        // Super admins e suporte podem publicar qualquer comunicado
        if ($user->isSuperAdmin() || $user->temCargo('Suporte')) {
            return true;
        }

        // Usuários normais só podem publicar comunicados da sua escola
        return $comunicado->autor->escola_id === $user->escola_id;
    }
}