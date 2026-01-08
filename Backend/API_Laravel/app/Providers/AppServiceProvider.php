<?php

namespace App\Providers;


use Illuminate\Support\ServiceProvider;
use Tymon\JWTAuth\JWTGuard;




class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Crear la inyección de dependencias para el guard JWT
        $this->app->bind(JWTGuard::class, function($app){
            return auth('api');
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
