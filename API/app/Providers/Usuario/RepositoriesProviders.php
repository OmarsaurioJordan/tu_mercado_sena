<?php

namespace App\Providers\Usuario;

use App\Contracts\Pqrs\IPqrsRepository;
use App\Repositories\Pqrs\PqrsRepository;
use Illuminate\Support\ServiceProvider;
use App\Contracts\Usuario\Repositories\IUsuarioRepository;
use App\Repositories\Usuario\UsuarioRepository;
use App\Contracts\Usuario\Repositories\IBloqueadoRepository;
use App\Repositories\Usuario\BloqueadoRepository;

class RepositoriesProviders extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(IUsuarioRepository::class, UsuarioRepository::class);
        $this->app->bind(IBloqueadoRepository::class, BloqueadoRepository::class);
        $this->app->bind(IPqrsRepository::class, PqrsRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
