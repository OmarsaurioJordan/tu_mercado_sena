<?php

namespace App\Providers\Producto;

use Illuminate\Support\ServiceProvider;
use App\Contracts\Producto\Repositories\IProductoRepository;
use App\Repositories\Producto\ProductoRepository;

class RepositoriesProviders extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(IProductoRepository::class, ProductoRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
