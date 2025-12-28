<?php

namespace App\Providers\Usuario;

use Illuminate\Support\ServiceProvider;
use App\Contracts\Usuario\Services\IUsuarioService;
use App\Services\Usuario\UsuarioService;

class ServicesProviders extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(IUsuarioService::class, UsuarioService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
