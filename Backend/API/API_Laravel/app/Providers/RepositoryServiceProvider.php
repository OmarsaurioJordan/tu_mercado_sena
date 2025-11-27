<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\UserRepository;

/**
 * Provedor de servicios para los repositorios
 * 
 * Esta clase es el lugar central donde se configuran las inyecciones de dependecias
 */

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Configurar la interfaz del repositorio del usuario y el repositorio
        $this->app->bind(
            UserRepositoryInterface::class, // Lo que se pide (interfaz)
            UserRepository::class // Lo que da (Implementación)
        );

        $this->app->bind(
            \App\Repositories\Contracts\ICorreoRepository::class,
            \App\Repositories\CorreoRepository::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
