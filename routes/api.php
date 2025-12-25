 <?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;

// Route::prefix('user')->group(function () {

//     Route::get('/is-unique-user/{username}', [UserController::class, 'isUniqueUser']);

//     Route::post('/register', [UserController::class, 'register']);

//     Route::post('/complete-profile', [UserController::class, 'completeProfile']);

//     Route::post('/login', [UserController::class, 'login']);

//     Route::get('/{uid}', [UserController::class, 'getUser']);

// });




/*
|--------------------------------------------------------------------------
| User APIs (JWT Based)
|--------------------------------------------------------------------------
*/

Route::prefix('user')->group(function () {

    // Public APIs (NO TOKEN REQUIRED)
    Route::get('/is-unique-user/{username}', [UserController::class, 'isUniqueUser']);
    Route::post('/register', [UserController::class, 'register']);
    Route::post('/login', [UserController::class, 'login']);

    // Protected APIs (JWT TOKEN REQUIRED)
    Route::middleware('auth:api')->group(function () {

        Route::post('/complete-profile', [UserController::class, 'completeProfile']);

        Route::get('/me', [UserController::class, 'getProfile']);

        Route::post('/logout', [UserController::class, 'logout']); // optional
    });
});