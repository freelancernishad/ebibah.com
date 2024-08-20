<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\api\StudentController;
use App\Http\Controllers\Auth\students\StudentAuthController;

// Student auth routes
Route::post('/student/login', [StudentAuthController::class, 'login']);
Route::post('/student/check/login', [StudentAuthController::class, 'checkTokenExpiration']);
Route::post('/student/check-token', [StudentAuthController::class, 'checkToken']);
Route::post('/student/register', [StudentAuthController::class, 'register']);

Route::middleware('auth:student')->group(function () {
    Route::post('/student/logout', [StudentAuthController::class, 'logout']);
    Route::get('/students/profile/{id}', [StudentController::class, 'show']);
    Route::get('/student-access', function () {
        return 'student access';
    });
});
