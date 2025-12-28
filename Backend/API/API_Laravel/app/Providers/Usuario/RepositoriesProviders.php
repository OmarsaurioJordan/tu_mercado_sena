<?php

namespace App\Providers\Usuario;

use Illuminate\Support\ServiceProvider;
use App\Contracts\Usuario\Repositories\IUsuarioRepository;
use App\Repositories\Usuario\UsuarioRepository;

class RepositoriesProviders extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(IUsuarioRepository::class, UsuarioRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
