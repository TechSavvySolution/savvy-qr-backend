<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\WebsiteController;
use App\Http\Controllers\Api\UploadController;
use App\Http\Controllers\Api\Admin\MasterTemplateController;
use App\Http\Controllers\Api\AdminAuthController;
use App\Http\Middleware\APIMiddleware;

//1️⃣ AUTHENTICATION ROUTES (Public)

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
        Route::post('/update-profile', [UserController::class, 'updateProfile']);
        
    });

});

//ADMIN ROUTES (Template Builder)
Route::prefix('admin')->group(function () {
    
    // 1. Templates
    
    Route::post('/templates', [MasterTemplateController::class, 'storeTemplate']);
    // Route::get('/templates/{id}', [MasterTemplateController::class, 'getTemplateById']);
    Route::post('/templates/{id}/update_details', [MasterTemplateController::class, 'updateDetails']);
    Route::delete('/templates/{id}', [MasterTemplateController::class, 'destroy']);

    // 2. Sections (The Dynamic Rules)
    Route::post('/sections', [MasterTemplateController::class, 'storeSection']);

}); 
Route::get('/templates', [MasterTemplateController::class, 'getTemplates']);

// Admin Auth
Route::post('/admin/login', [AdminAuthController::class, 'login']);

// WEBSITE BUILDER ROUTES

// These are ALL protected because you must be logged in to build a site.
Route::prefix('website')->middleware([APIMiddleware::class])->group(function () {

    // 1. Create New Website
    Route::post('/create', [WebsiteController::class, 'store']); 

    // 2. Get My Website Data
    Route::get('/my-site', [WebsiteController::class, 'mySite']);
    
    // 3. Save Changes
    Route::post('/update', [WebsiteController::class, 'update']);

    // 4.(Image Upload)
    // Final URL: http://127.0.0.1:8000/api/website/upload-image
    Route::post('/upload-image', [UploadController::class, 'upload']);

});


// 🎨 GET TEMPLATE DETAILS: Used by the Builder to know what fields to show
Route::get('/templates/{id}', [MasterTemplateController::class, 'getTemplateById']);//currently not use

// 🌍 PUBLIC VIEW: The actual link people share (e.g., savvyqr.com/website/1)
Route::get('/websites/{user_id}', [WebsiteController::class, 'index']);//currently not use


// Route::get('/admin/templates/{id}', [MasterTemplateController::class, 'show']);


// Matches your diagram: /api/websites/{user_id}
Route::post('/websites/{user_id}', [WebsiteController::class, 'store']); //currently not use

// PUBLIC WEBSITE ROUTES (No Login Needed) 
// It sits outside the middleware so anyone can visit.
// URL: http://127.0.0.1:8000/api/view-site/sarik
Route::get('/website/{username}', [WebsiteController::class, 'view']);//currently not use
