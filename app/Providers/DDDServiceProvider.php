<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\Event\Repositories\EventRepositoryInterface;
use App\Infrastructure\Persistence\EloquentEventRepository;
use App\Domain\Event\Services\EventDomainService;
use App\Application\Handlers\CreateEventHandler;
use App\Application\Handlers\UpdateEventHandler;
use App\Application\Handlers\DeleteEventHandler;
use App\Application\Handlers\GetEventHandler;
use App\Application\Handlers\GetUserEventsHandler;

class DDDServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind Repository Interface to Implementation
        $this->app->bind(EventRepositoryInterface::class, EloquentEventRepository::class);

        // Register Domain Service
        $this->app->singleton(EventDomainService::class, function ($app) {
            return new EventDomainService(
                $app->make(EventRepositoryInterface::class)
            );
        });

        // Register Command Handlers
        $this->app->singleton(CreateEventHandler::class, function ($app) {
            return new CreateEventHandler(
                $app->make(EventRepositoryInterface::class),
                $app->make(EventDomainService::class)
            );
        });

        $this->app->singleton(UpdateEventHandler::class, function ($app) {
            return new UpdateEventHandler(
                $app->make(EventRepositoryInterface::class),
                $app->make(EventDomainService::class)
            );
        });

        $this->app->singleton(DeleteEventHandler::class, function ($app) {
            return new DeleteEventHandler(
                $app->make(EventRepositoryInterface::class),
                $app->make(EventDomainService::class)
            );
        });

        // Register Query Handlers
        $this->app->singleton(GetEventHandler::class, function ($app) {
            return new GetEventHandler(
                $app->make(EventRepositoryInterface::class)
            );
        });

        $this->app->singleton(GetUserEventsHandler::class, function ($app) {
            return new GetUserEventsHandler(
                $app->make(EventRepositoryInterface::class)
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
