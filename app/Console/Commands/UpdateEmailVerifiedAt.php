<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UpdateEmailVerifiedAt extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:update-email-verified';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Atualiza o campo email_verified_at para usuários que não possuem este campo preenchido';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando atualização do campo email_verified_at...');
        
        // Buscar usuários sem email_verified_at
        $usersToUpdate = User::whereNull('email_verified_at')->get();
        
        if ($usersToUpdate->isEmpty()) {
            $this->info('Nenhum usuário encontrado para atualizar.');
            return 0;
        }
        
        $this->info("Encontrados {$usersToUpdate->count()} usuários para atualizar.");
        
        // Confirmar ação
        if (!$this->confirm('Deseja continuar com a atualização?')) {
            $this->info('Operação cancelada.');
            return 0;
        }
        
        $now = now();
        $updatedCount = 0;
        
        // Atualizar em lote para melhor performance
        try {
            DB::beginTransaction();
            
            $updatedCount = User::whereNull('email_verified_at')
                ->update(['email_verified_at' => $now]);
            
            DB::commit();
            
            $this->info("✅ {$updatedCount} usuários atualizados com sucesso!");
            $this->info("Data definida: {$now->format('d/m/Y H:i:s')}");
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("❌ Erro ao atualizar usuários: {$e->getMessage()}");
            return 1;
        }
        
        return 0;
    }
}