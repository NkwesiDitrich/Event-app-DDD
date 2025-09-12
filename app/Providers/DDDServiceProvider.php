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

// Registration Domain Bindings
use App\Domain\Registration\Repositories\RegistrationRepositoryInterface;
use App\Infrastructure\Persistence\EloquentRegistrationRepository;
use App\Application\Handlers\Registration\CheckInParticipantHandler;
use App\Application\Handlers\Registration\UnattendParticipantHandler;
use App\Application\Handlers\Registration\GetUserRegistrationsHandler;

// NEW: User Management Handlers (Enhanced Dropdown Functionality)
use App\Application\Handlers\UserManagement\GetEventParticipantsHandler;
use App\Application\Handlers\UserManagement\GetEventsCreatedHandler;

class DDDServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // ========================================
        // EXISTING BINDINGS (Keep as they are)
        // ========================================
        
        // Bind Repository Interface to Implementation
        $this->app->bind(EventRepositoryInterface::class, EloquentEventRepository::class);
        $this->app->bind(RegistrationRepositoryInterface::class, EloquentRegistrationRepository::class);

        // Register Domain Service
        $this->app->singleton(EventDomainService::class, function ($app) {
            return new EventDomainService(
                $app->make(EventRepositoryInterface::class)
            );
        });

        // Register Event Command Handlers
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

        // Register Event Query Handlers
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

        // Register Registration Command Handlers
        $this->app->singleton(CheckInParticipantHandler::class, function ($app) {
            return new CheckInParticipantHandler(
                $app->make(RegistrationRepositoryInterface::class)
            );
        });

        $this->app->singleton(UnattendParticipantHandler::class, function ($app) {
            return new UnattendParticipantHandler(
                $app->make(RegistrationRepositoryInterface::class)
            );
        });

        // Register Registration Query Handlers
        $this->app->singleton(GetUserRegistrationsHandler::class, function ($app) {
            return new GetUserRegistrationsHandler(
                $app->make(RegistrationRepositoryInterface::class)
            );
        });

        // ========================================
        // NEW: Enhanced User Management Handlers
        // ========================================

        // Register Enhanced User Management Query Handlers
        $this->app->singleton(GetEventParticipantsHandler::class, function ($app) {
            return new GetEventParticipantsHandler(
                $app->make(RegistrationRepositoryInterface::class),
                $app->make(EventRepositoryInterface::class)
            );
        });

        $this->app->singleton(GetEventsCreatedHandler::class, function ($app) {
            return new GetEventsCreatedHandler(
                $app->make(EventRepositoryInterface::class),
                $app->make(RegistrationRepositoryInterface::class)
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
