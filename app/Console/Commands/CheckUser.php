<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CheckUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-user {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verificar se um usuário existe no banco';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        
        $user = User::where('email', $email)->first();
        
        if ($user) {
            $this->info('Usuário encontrado:');
            $this->info('ID: ' . $user->id);
            $this->info('Nome: ' . $user->name);
            $this->info('Email: ' . $user->email);
            $this->info('Escola ID: ' . $user->escola_id);
            $this->info('Criado em: ' . $user->created_at);
            
            // Testar senha
            if (Hash::check('12345', $user->password)) {
                $this->info('✅ Senha "12345" está correta');
            } else {
                $this->error('❌ Senha "12345" está incorreta');
            }
        } else {
            $this->error('Usuário não encontrado!');
        }
    }
}
