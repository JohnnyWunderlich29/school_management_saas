<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

use App\Models\Comunicado;
use App\Models\Conversa;
use App\Models\Planejamento;
use App\Models\Reserva;
use App\Policies\ComunicadoPolicy;
use App\Policies\ConversaPolicy;
use App\Policies\PlanejamentoPolicy;
use App\Policies\ReservaPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Comunicado::class => ComunicadoPolicy::class,
        Conversa::class => ConversaPolicy::class,
        Planejamento::class => PlanejamentoPolicy::class,
        Reserva::class => ReservaPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        Gate::before(function ($user, $ability) {
            return $user->isSuperAdmin() ? true : null;
        });
    }
}