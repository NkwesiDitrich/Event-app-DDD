<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;


Route::get('/',[HomeController::class,'IndexPage']);

Route::get('/post/{id}',[HomeController::class,'PostPage']);

Route::post('/event-registration',[HomeController::class,'EventRegistration']);
  
require_once __DIR__ . '/ddd_api.php';

// NEW: User Management routes (following your pattern)
require_once __DIR__ . '/user_management.php';
