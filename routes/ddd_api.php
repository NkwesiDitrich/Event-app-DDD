<?php

use Illuminate\Support\Facades\Route;
use App\Infrastructure\Http\EventController;

/*
|--------------------------------------------------------------------------
| DDD API Routes
|--------------------------------------------------------------------------
|
| Here are the DDD-based API routes for the Event domain.
| These routes replace the traditional Laravel MVC routes.
|
*/

// Event CRUD Operations (DDD Style)
Route::prefix('api/v1')->group(function () {
    
    // Core Event Operations
    Route::get('/events', [EventController::class, 'index'])->name('ddd.events.index');
    Route::get('/events/{id}', [EventController::class, 'show'])->name('ddd.events.show');
    Route::post('/events', [EventController::class, 'store'])->name('ddd.events.store');
    Route::put('/events/{id}', [EventController::class, 'update'])->name('ddd.events.update');
    Route::delete('/events/{id}', [EventController::class, 'destroy'])->name('ddd.events.destroy');
    
    // Domain-Specific Event Queries
    Route::get('/events/upcoming', [EventController::class, 'upcoming'])->name('ddd.events.upcoming');
    Route::get('/events/featured', [EventController::class, 'featured'])->name('ddd.events.featured');
    Route::get('/events/today', [EventController::class, 'today'])->name('ddd.events.today');
    
});

// Alternative: If you want to completely replace web routes
Route::prefix('events')->group(function () {
    
    // Web-based DDD Routes (for forms/views)
    Route::get('/', [EventController::class, 'index'])->name('events.index');
    Route::get('/create', function() {
        return view('events.create'); // You'll need to create this view
    })->name('events.create');
    Route::post('/', [EventController::class, 'store'])->name('events.store');
    Route::get('/{id}', [EventController::class, 'show'])->name('events.show');
    Route::get('/{id}/edit', function($id) {
        return view('events.edit', compact('id')); // You'll need to create this view
    })->name('events.edit');
    Route::put('/{id}', [EventController::class, 'update'])->name('events.update');
    Route::delete('/{id}', [EventController::class, 'destroy'])->name('events.destroy');
    
    // Domain-specific routes
    Route::get('/upcoming', [EventController::class, 'upcoming'])->name('events.upcoming');
    Route::get('/featured', [EventController::class, 'featured'])->name('events.featured');
    Route::get('/today', [EventController::class, 'today'])->name('events.today');
    
});
