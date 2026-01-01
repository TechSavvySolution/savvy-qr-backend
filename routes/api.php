<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\WebsiteController; // 1. Import the New Controller
use App\Http\Middleware\APIMiddleware; // import from middleware


// USER ROUTES (Prefix: /api/user)

Route::prefix('user')->group(function () {

    // PUBLIC ROUTES (No Token Needed)
    Route::post('/register', [UserController::class, 'register']);
    Route::post('/login', [UserController::class, 'login']);
    Route::get('/is-unique-user/{username}', [UserController::class, 'isUniqueUser']);

    //  PROTECTED ROUTES (Token Required)
    Route::middleware([APIMiddleware::class])->group(function () {
        
        Route::post('/complete-profile', [UserController::class, 'completeProfile']);
        Route::get('/me', [UserController::class, 'getProfile']);
        Route::get('/by-id/{id}', [UserController::class, 'getUserById']);
        Route::post('/logout', [UserController::class, 'logout']);
        
    });

});


// WEBSITE BUILDER ROUTES (Prefix: /api/website)

// These are ALL protected because you must be logged in to build a site.
Route::prefix('website')->middleware([APIMiddleware::class])->group(function () {

    // 1️⃣ Create New Website (When clicking "Use Template")
    // URL: http://.../api/website/create
    Route::post('/create', [WebsiteController::class, 'store']); 

    // 2️ .  Get My Website Data (For the Editor)
    // URL: http://.../api/website/my-site
    Route::get('/my-site', [WebsiteController::class, 'show']); 

});