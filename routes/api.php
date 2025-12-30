<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;



    Route::prefix('user')->group(function () {

        // Public
        Route::get('/is-unique-user/{username}', [UserController::class, 'isUniqueUser']);
        Route::post('/register', [UserController::class, 'register']);
        Route::post('/login', [UserController::class, 'login']);

        // Custom middleware protected
        Route::middleware('api.auth')->group(function () {
        Route::post('/complete-profile', [UserController::class, 'completeProfile']);
        Route::get('/me', [UserController::class, 'getProfile']);
        Route::get('/by-id/{id}', [UserController::class, 'getUserById']);
        Route::post('/logout', [UserController::class, 'logout']);
        });

    });
 