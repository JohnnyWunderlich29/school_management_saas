<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class BladeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Diretiva para verificar se o usuário tem uma permissão específica
        // 100% dependente da permissão explícita (sem atalhos)
        Blade::if('permission', function ($permission) {
            return Auth::check() && Auth::user()->temPermissao($permission);
        });

        // Diretiva para verificar se o usuário tem um cargo específico
        Blade::if('cargo', function ($cargo) {
            return Auth::check() && Auth::user()->temCargo($cargo);
        });

        // Diretiva para verificar se o usuário é super administrador
        Blade::if('superadmin', function () {
            return Auth::check() && Auth::user()->isSuperAdmin();
        });

        // Diretiva para verificar se o usuário tem qualquer uma das permissões listadas
        Blade::if('anypermission', function (...$permissions) {
            if (!Auth::check()) {
                return false;
            }
            
            $user = Auth::user();
            if ($user->isSuperAdmin()) {
                return true;
            }
            
            foreach ($permissions as $permission) {
                if ($user->temPermissao($permission)) {
                    return true;
                }
            }
            
            return false;
        });

        // Diretiva para verificar se o usuário tem todas as permissões listadas
        Blade::if('allpermissions', function (...$permissions) {
            if (!Auth::check()) {
                return false;
            }
            
            $user = Auth::user();
            if ($user->isSuperAdmin()) {
                return true;
            }
            
            foreach ($permissions as $permission) {
                if (!$user->temPermissao($permission)) {
                    return false;
                }
            }
            
            return true;
        });
    }
}
