<?php

namespace App\Providers\Usuario;

use App\Contracts\Pqrs\IPqrsService;
use Illuminate\Support\ServiceProvider;
use App\Contracts\Usuario\Services\IUsuarioService;
use App\Services\Usuario\UsuarioService;
use App\Contracts\Usuario\Services\IBloqueadoService;
use App\Services\Pqrs\PqrsServices;
use App\Services\Usuario\BloqueadoService;

class ServicesProviders extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(IUsuarioService::class, UsuarioService::class);
        $this->app->bind(IBloqueadoService::class, BloqueadoService::class);
        $this->app->bind(IPqrsService::class, PqrsServices::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
