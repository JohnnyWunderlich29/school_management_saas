<?php

namespace App\Services;

use App\Models\ItemBiblioteca;
use App\Models\Emprestimo;
use App\Models\Reserva;
use App\Models\PoliticaAcesso;
use App\Models\MultaRegra;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class BibliotecaService
{
    /**
     * Validar se um usuário pode fazer empréstimo de um item
     */
    public function validarEmprestimo(User $usuario, ItemBiblioteca $item): array
    {
        $errors = [];
        $politica = $this->obterPoliticaAcesso($usuario, $item);

        // Verificar disponibilidade do item
        if (!$this->itemDisponivel($item)) {
            $errors[] = 'Item não está disponível para empréstimo';
        }

        // Verificar limite de empréstimos ativos
        $emprestimosAtivos = $this->contarEmprestimosAtivos($usuario, $item->escola_id);
        if ($emprestimosAtivos >= $politica->max_emprestimos) {
            $errors[] = "Limite de empréstimos atingido ({$politica->max_emprestimos})";
        }

        // Verificar multas pendentes
        if ($this->temMultasPendentes($usuario, $item->escola_id)) {
            $errors[] = 'Usuário possui multas pendentes';
        }

        // Verificar janela de horário
        if (!$this->horarioPermitido($politica)) {
            $errors[] = 'Empréstimo não permitido neste horário';
        }

        return [
            'valido' => empty($errors),
            'errors' => $errors,
            'politica' => $politica
        ];
    }

    /**
     * Validar se um usuário pode fazer reserva de um item
     */
    public function validarReserva(User $usuario, ItemBiblioteca $item): array
    {
        $errors = [];
        $politica = $this->obterPoliticaAcesso($usuario, $item);

        // Verificar se item já está disponível
        if ($this->itemDisponivel($item)) {
            $errors[] = 'Item está disponível para empréstimo direto';
        }

        // Verificar limite de reservas ativas
        $reservasAtivas = $this->contarReservasAtivas($usuario, $item->escola_id);
        if ($reservasAtivas >= $politica->max_reservas) {
            $errors[] = "Limite de reservas atingido ({$politica->max_reservas})";
        }

        // Verificar se já tem reserva para este item
        if ($this->temReservaAtiva($usuario, $item)) {
            $errors[] = 'Usuário já possui reserva ativa para este item';
        }

        return [
            'valido' => empty($errors),
            'errors' => $errors,
            'politica' => $politica
        ];
    }

    /**
     * Calcular data de devolução baseada na política
     */
    public function calcularDataDevolucao(User $usuario, ItemBiblioteca $item): Carbon
    {
        $politica = $this->obterPoliticaAcesso($usuario, $item);
        return Carbon::now()->addDays($politica->prazo_dias);
    }

    /**
     * Calcular multa por atraso
     */
    public function calcularMulta(Emprestimo $emprestimo): float
    {
        if ($emprestimo->data_devolucao_real || !$emprestimo->data_devolucao_prevista->isPast()) {
            return 0.0;
        }

        $regra = MultaRegra::where('escola_id', $emprestimo->item->escola_id)->first();
        if (!$regra) {
            return 0.0;
        }

        $diasAtraso = $this->calcularDiasAtraso($emprestimo, $regra);
        $valorBase = $diasAtraso * $regra->taxa_por_dia;

        // Aplicar desconto por perfil
        $desconto = $this->obterDescontoPerfil($emprestimo->usuario, $regra);
        $valorComDesconto = $valorBase * (1 - $desconto);

        // Aplicar valor máximo
        return min($valorComDesconto, $regra->valor_maximo);
    }

    /**
     * Verificar se usuário pode renovar empréstimo
     */
    public function podeRenovar(Emprestimo $emprestimo): array
    {
        $errors = [];
        $politica = $this->obterPoliticaAcesso($emprestimo->usuario, $emprestimo->item);

        // Verificar se renovação é permitida pela política
        if (!($politica->regras['renovacao_permitida'] ?? false)) {
            $errors[] = 'Renovação não permitida para este tipo de item';
        }

        // Verificar limite de renovações
        if ($emprestimo->renovacoes >= ($politica->regras['max_renovacoes'] ?? 0)) {
            $errors[] = 'Limite de renovações atingido';
        }

        // Verificar se há reservas pendentes
        if ($this->temReservasPendentes($emprestimo->item)) {
            $errors[] = 'Item possui reservas pendentes';
        }

        // Verificar multas
        if ($this->temMultasPendentes($emprestimo->usuario, $emprestimo->item->escola_id)) {
            $errors[] = 'Usuário possui multas pendentes';
        }

        return [
            'pode_renovar' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Obter política de acesso para usuário e item
     */
    public function obterPoliticaAcesso(User $usuario, ItemBiblioteca $item): PoliticaAcesso
    {
        $perfil = $this->determinarPerfilUsuario($usuario);

        // Buscar política específica
        $politica = PoliticaAcesso::where('escola_id', $item->escola_id)
            ->where('perfil', $perfil)
            ->where('tipo_item', $item->tipo)
            ->first();

        // Fallback para política geral do perfil
        if (!$politica) {
            $politica = PoliticaAcesso::where('escola_id', $item->escola_id)
                ->where('perfil', $perfil)
                ->where('tipo_item', 'geral')
                ->first();
        }

        // Fallback para política geral
        if (!$politica) {
            $politica = PoliticaAcesso::where('escola_id', $item->escola_id)
                ->where('perfil', 'geral')
                ->where('tipo_item', 'geral')
                ->first();
        }

        if (!$politica) {
            throw new \Exception('Nenhuma política de acesso encontrada');
        }

        return $politica;
    }

    /**
     * Determinar perfil do usuário
     */
    private function determinarPerfilUsuario(User $usuario): string
    {
        // Lógica para determinar perfil baseado nos roles/relacionamentos
        if ($usuario->hasRole('Professor')) {
            return 'professor';
        }

        if ($usuario->hasRole('Aluno')) {
            return 'aluno';
        }

        if ($usuario->hasRole(['Secretário', 'Administrador de Escola'])) {
            return 'funcionario';
        }

        return 'geral';
    }

    /**
     * Verificar se item está disponível
     */
    private function itemDisponivel(ItemBiblioteca $item): bool
    {
        // Elegível quando status for 'disponivel' ou 'ativo' e houver exemplares livres
        if (!$item->habilitado_emprestimo || !in_array($item->status, ['disponivel', 'ativo'])) {
            return false;
        }

        $emprestimosAtivos = Emprestimo::where('item_biblioteca_id', $item->id)
            ->whereNull('data_devolucao_real')
            ->count();

        return $emprestimosAtivos < (int) $item->quantidade_fisica;
    }

    /**
     * Contar empréstimos ativos do usuário
     */
    private function contarEmprestimosAtivos(User $usuario, int $escolaId): int
    {
        return Emprestimo::whereHas('item', function($query) use ($escolaId) {
                $query->where('escola_id', $escolaId);
            })
            ->where('user_id', $usuario->id)
            ->whereNull('data_devolucao_real')
            ->count();
    }

    /**
     * Contar reservas ativas do usuário
     */
    private function contarReservasAtivas(User $usuario, int $escolaId): int
    {
        return Reserva::whereHas('item', function($query) use ($escolaId) {
                $query->where('escola_id', $escolaId);
            })
            ->where('user_id', $usuario->id)
            ->where('status', 'ativa')
            ->count();
    }

    /**
     * Verificar se usuário tem reserva ativa para o item
     */
    private function temReservaAtiva(User $usuario, ItemBiblioteca $item): bool
    {
        return Reserva::where('user_id', $usuario->id)
            ->where('item_biblioteca_id', $item->id)
            ->where('status', 'ativa')
            ->exists();
    }

    /**
     * Verificar se usuário tem multas pendentes
     */
    private function temMultasPendentes(User $usuario, int $escolaId): bool
    {
        return Emprestimo::whereHas('item', function($query) use ($escolaId) {
                $query->where('escola_id', $escolaId);
            })
            ->where('user_id', $usuario->id)
            ->where('multa_valor', '>', 0)
            ->where('multa_paga', false)
            ->exists();
    }

    /**
     * Verificar se horário atual é permitido
     */
    private function horarioPermitido(PoliticaAcesso $politica): bool
    {
        $horaAtual = Carbon::now()->hour;
        $janelas = $politica->janelas ?? [];

        if (empty($janelas)) {
            return true;
        }

        foreach ($janelas as $janela) {
            switch ($janela) {
                case 'manha':
                    if ($horaAtual >= 6 && $horaAtual < 12) return true;
                    break;
                case 'tarde':
                    if ($horaAtual >= 12 && $horaAtual < 18) return true;
                    break;
                case 'noite':
                    if ($horaAtual >= 18 || $horaAtual < 6) return true;
                    break;
            }
        }

        return false;
    }

    /**
     * Verificar se item tem reservas pendentes
     */
    private function temReservasPendentes(ItemBiblioteca $item): bool
    {
        return Reserva::where('item_biblioteca_id', $item->id)
            ->where('status', 'ativa')
            ->exists();
    }

    /**
     * Calcular dias de atraso considerando exceções
     */
    private function calcularDiasAtraso(Emprestimo $emprestimo, MultaRegra $regra): int
    {
        $dataVencimento = $emprestimo->data_devolucao_prevista;
        $dataAtual = Carbon::now();
        
        $diasAtraso = $dataVencimento->diffInDays($dataAtual, false);
        
        if ($diasAtraso <= 0) {
            return 0;
        }

        $excecoes = $regra->excecoes ?? [];

        // Descontar feriados se configurado
        if ($excecoes['feriados_nao_contam'] ?? false) {
            // Implementar lógica de feriados se necessário
        }

        // Descontar fins de semana se configurado
        if ($excecoes['fins_semana_nao_contam'] ?? false) {
            $diasUteis = 0;
            $data = $dataVencimento->copy();
            
            while ($data->lt($dataAtual)) {
                if (!$data->isWeekend()) {
                    $diasUteis++;
                }
                $data->addDay();
            }
            
            return $diasUteis;
        }

        return $diasAtraso;
    }

    /**
     * Obter desconto por perfil
     */
    private function obterDescontoPerfil(User $usuario, MultaRegra $regra): float
    {
        $perfil = $this->determinarPerfilUsuario($usuario);
        $descontos = $regra->excecoes['perfis_com_desconto'] ?? [];
        
        return $descontos[$perfil] ?? 0.0;
    }
}