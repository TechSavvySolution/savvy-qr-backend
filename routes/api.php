<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Middleware\APIMiddleware; // <--- 1. Import the Middleware

Route::prefix('user')->group(function () {

    // 🟢 PUBLIC ROUTES
    Route::post('/register', [UserController::class, 'register']);
    Route::post('/login', [UserController::class, 'login']);
    Route::get('/is-unique-user/{username}', [UserController::class, 'isUniqueUser']);

    // 🔒 PROTECTED ROUTES
    // 2. Use the Class directly instead of 'api.auth'
    // This prevents "Target class does not exist" errors permanently.
    Route::middleware([APIMiddleware::class])->group(function () {
        
        Route::post('/complete-profile', [UserController::class, 'completeProfile']);
        Route::get('/me', [UserController::class, 'getProfile']);
        Route::get('/by-id/{id}', [UserController::class, 'getUserById']);
        Route::post('/logout', [UserController::class, 'logout']);
        
    });

});