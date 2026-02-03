<?php

namespace App\Providers\Auth;


use App\Models\Chat;
use App\Policies\ChatPolicy;
use App\Contracts\Auth\Services\IAuthService;
use App\Contracts\Auth\Services\IRecuperarContrasenaService;
use App\Contracts\Auth\Services\IRegistroService;
use App\Services\Auth\AuthService;
use App\Services\Auth\RecuperarContrasenaCorreoService;
use App\Services\Auth\RegistroService;
use Illuminate\Support\ServiceProvider;

class ServicesProviders extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(IAuthService::class, AuthService::class);

        $this->app->bind(IRecuperarContrasenaService::class, RecuperarContrasenaCorreoService::class);

        $this->app->bind(IRegistroService::class, RegistroService::class);
    }

    protected $policies = [
        Chat::class => ChatPolicy::class,
    ];

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
