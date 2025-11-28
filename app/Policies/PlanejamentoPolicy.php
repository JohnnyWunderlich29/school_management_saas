<?php

namespace App\Policies;

use App\Models\Planejamento;
use App\Models\User;

class PlanejamentoPolicy
{
    /**
     * Permite visualizar um planejamento.
     * Professores podem visualizar seus próprios planejamentos, e coordenadores/admins conforme acesso.
     */
    public function view(User $user, Planejamento $planejamento): bool
    {
        if ($user->isAdministrador() || $user->isCoordenador()) {
            return true;
        }

        return $planejamento->user_id === $user->id;
    }

    /**
     * Permite criar planejamentos.
     * Controla a diretiva @can('create', Planejamento::class) nas views.
     */
    public function create(User $user): bool
    {
        // Permitir pela permissão explícita ou por perfis docentes/gestão
        return $user->temPermissao('planejamentos.criar')
            || $user->isProfessor()
            || $user->isCoordenador()
            || $user->isAdministrador();
    }

    /**
     * Permite editar/atualizar um planejamento.
     * Somente o professor autor pode editar quando o status é 'rascunho'.
     * Super Admin mantém acesso total.
     */
    public function update(User $user, Planejamento $planejamento): bool
    {
        // Acesso amplo para gestão
        if ($user->isSuperAdmin() || $user->isAdministrador() || $user->isCoordenador()) {
            return true;
        }

        // Professores podem editar quando são responsáveis pelo planejamento e status permite edição
        $isResponsavel = ($planejamento->user_id === $user->id)
            || ($planejamento->professor_id && $planejamento->professor_id === $user->id);

        return $user->isProfessor()
            && $isResponsavel
            && in_array($planejamento->status_efetivo, ['rascunho', 'revisao', 'rejeitado']);
    }

    /**
     * Permite listar planejamentos.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdministrador() || $user->isCoordenador() || $user->isProfessor();
    }
}