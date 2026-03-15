<?php

namespace App\Providers\Producto;

use Illuminate\Support\ServiceProvider;
use App\Contracts\Producto\Services\IProductoService;
use App\Services\Producto\ProductoService;

class ServicesProviders extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(IProductoService::class, ProductoService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
