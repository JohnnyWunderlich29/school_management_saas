<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\Cargo;
use App\Models\Permissao;

class CreateSuperUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-super-user {email} {password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cria um superusuário com o e-mail e senha fornecidos.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->argument('password');

        $user = User::firstOrNew(['email' => $email]);

        if (!$user->exists) {
            $user->name = 'SuperUser';
            $user->password = Hash::make($password);
            $user->is_admin = true;
            $user->save();
            $this->info('Superusuário criado com sucesso: ' . $user->email);
        } else {
            $this->info('Usuário com este e-mail já existe. Atribuindo permissões ao usuário existente: ' . $user->email);
        }

        $superAdminCargo = Cargo::firstOrCreate(
            ['nome' => 'Super Administrador'],
            ['descricao' => 'Acesso total ao sistema', 'ativo' => true]
        );

        $user->cargos()->syncWithoutDetaching([$superAdminCargo->id]);

        $this->info('Cargo "Super Administrador" atribuído ao usuário ' . $user->email);

        $allPermissions = Permissao::where('ativo', true)->pluck('id');
        $superAdminCargo->permissoes()->syncWithoutDetaching($allPermissions);

        $this->info('Todas as permissões ativas atribuídas ao cargo "Super Administrador".');

        $this->info('Superusuário criado com sucesso: ' . $user->email);
        return Command::SUCCESS;
    }
}
