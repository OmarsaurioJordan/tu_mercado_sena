<?php

namespace App\Providers\Chats;

use Illuminate\Support\ServiceProvider;
use App\Contracts\Chat\Repositories\IChatRepository;
use App\Repositories\Chat\ChatRepository;
use App\Contracts\Mensaje\Repositories\IMensajeRepository;
use App\Repositories\Mensaje\MensajeRepository;
use App\Contracts\Chat\Services\IChatService;
use App\Contracts\Mensaje\Repository\IMensajeRepository as RepositoryIMensajeRepository;
use App\Services\Chat\ChatService;
use App\Contracts\Mensaje\Services\IMensajeService;
use App\Services\Mensaje\MensajeService;

class RepositoryServicesProviders extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(IChatRepository::class, ChatRepository::class);
        $this->app->bind(RepositoryIMensajeRepository::class, MensajeRepository::class);
        $this->app->bind(IChatService::class, ChatService::class);
        $this->app->bind(IMensajeService::class, MensajeService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
