<?php

use Illuminate\Support\Facades\Route;
use App\Infrastructure\Http\UserManagementController;

/*
|--------------------------------------------------------------------------
| User Management Routes
|--------------------------------------------------------------------------
|
| Enhanced User Management routes with dropdown navigation structure
| These routes provide admin functionality for managing event participants
| and viewing events created by the authenticated user.
|
*/

Route::middleware(['auth'])->prefix('user-management')->name('user-management.')->group(function () {
    
    // Main User Management Dashboard (Dropdown entry point)
    Route::get('/dashboard', [UserManagementController::class, 'UserManagementDashboard'])
        ->name('dashboard');
    
    // Dropdown Option 1: Event Participants Management
    Route::get('/event-participants', [UserManagementController::class, 'EventParticipantsPage'])
        ->name('event-participants');
    
    // Dropdown Option 2: Events Created by User
    Route::get('/events-created', [UserManagementController::class, 'EventsCreatedPage'])
        ->name('events-created');
    
    // AJAX API Endpoints for dynamic data loading
    Route::prefix('api')->name('api.')->group(function () {
        
        // Get event participants with filtering and pagination
        Route::get('/event-participants', [UserManagementController::class, 'GetEventParticipants'])
            ->name('get-event-participants');
        
        // Get events created by user with statistics
        Route::get('/events-created', [UserManagementController::class, 'GetEventsCreated'])
            ->name('get-events-created');
        
        // Get participants for a specific event (for modal display)
        Route::get('/event/{eventId}/participants', [UserManagementController::class, 'GetEventParticipantsByEventId'])
            ->name('get-event-participants-by-id');
    });
    
    // Participant Management Actions
    Route::post('/check-in', [UserManagementController::class, 'CheckInParticipant'])
        ->name('check-in');
    
    Route::post('/unattend', [UserManagementController::class, 'UnattendParticipant'])
        ->name('unattend');
    
    // Legacy route support (for backward compatibility with your existing controller)
    Route::get('/page', [UserManagementController::class, 'UserManagementPage'])
        ->name('page');
    
    Route::get('/registrations', [UserManagementController::class, 'RegistrationList'])
        ->name('registrations');
});
