<?php

namespace App\Providers\Auth;

use App\Contracts\Auth\Repositories\UserRepositoryInterface;
use App\Contracts\Auth\Repositories\ICuentaRepository;
use App\Repositories\Auth\UserRepository;
use App\Repositories\Auth\CuentaRepository;

use Illuminate\Support\ServiceProvider;


class RepositoriesProviders extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class,
        UserRepository::class);
        $this->app->bind(ICuentaRepository::class, CuentaRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
