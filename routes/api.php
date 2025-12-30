<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;

// We use the alias 'api.auth' which you defined in bootstrap/app.php
// This is safer than importing the class directly when caching issues happen.

Route::prefix('user')->group(function () {

    // 🟢 PUBLIC ROUTES (No Token Needed)
    Route::post('/register', [UserController::class, 'register']);
    Route::post('/login', [UserController::class, 'login']);
    Route::get('/is-unique-user/{username}', [UserController::class, 'isUniqueUser']);

    // 🔒 PROTECTED ROUTES (Requires Login)
    // We group these under the 'api.auth' middleware
    Route::middleware('api.auth')->group(function () {
        
        Route::post('/complete-profile', [UserController::class, 'completeProfile']);
        Route::get('/me', [UserController::class, 'getProfile']);
        Route::get('/by-id/{id}', [UserController::class, 'getUserById']);
        Route::post('/logout', [UserController::class, 'logout']);
        
    });

});