<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\WebsiteController; // 1. Import the New Controller
use App\Http\Middleware\APIMiddleware; // import from middleware
use App\Http\Controllers\Api\UploadController;
use App\Http\Controllers\Api\Admin\MasterTemplateController;

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

// WEBSITE BUILDER ROUTES

// These are ALL protected because you must be logged in to build a site.
Route::prefix('website')->middleware([APIMiddleware::class])->group(function () {

    // 1. Create New Website
    Route::post('/create', [WebsiteController::class, 'store']); 

    // 2. Get My Website Data
    Route::get('/my-site', [WebsiteController::class, 'show']); 
    
    // 3. Save Changes
    Route::post('/update', [WebsiteController::class, 'update']);

    // 4.(Image Upload)
    // Final URL: http://127.0.0.1:8000/api/website/upload-image
    Route::post('/upload-image', [UploadController::class, 'upload']);

});

// PUBLIC WEBSITE ROUTES (No Login Needed) 
// It sits outside the middleware so anyone can visit.
// URL: http://127.0.0.1:8000/api/view-site/sarik
Route::get('/website/{username}', [WebsiteController::class, 'view']);


//ADMIN ROUTES (Template Builder)
Route::prefix('admin')->group(function () {
    
    // 1. Templates
    Route::get('/templates', [MasterTemplateController::class, 'getTemplates']);
    Route::post('/templates', [MasterTemplateController::class, 'storeTemplate']);
    // Route::get('/templates/{id}', [MasterTemplateController::class, 'getTemplateById']);
    Route::post('/templates/{id}/update_details', [MasterTemplateController::class, 'updateDetails']);

    // 2. Sections (The Dynamic Rules)
    Route::post('/sections', [MasterTemplateController::class, 'storeSection']);

}); 

// Route::get('/admin/templates/{id}', [MasterTemplateController::class, 'show']);

Route::get('/templates/{id}', [MasterTemplateController::class, 'getTemplateById']);

// Matches your diagram: /api/websites/{user_id}
Route::post('/websites/{user_id}', [WebsiteController::class, 'store']);
// 2. GET Websites (GET) - 🟢 ADD THIS LINE
Route::get('/websites/{user_id}', [WebsiteController::class, 'index']);