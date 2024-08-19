<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\UserController;
use App\Http\Controllers\Auth\admins\AdminAuthController;

// Admin auth routes
Route::post('/admin/login', [AdminAuthController::class, 'login']);
Route::post('/admin/check/login', [AdminAuthController::class, 'checkTokenExpiration']);
Route::post('/admin/check-token', [AdminAuthController::class, 'checkToken']);
Route::post('/admin/register', [AdminAuthController::class, 'register']);

Route::middleware('auth:admin')->group(function () {
    Route::post('admin/logout', [AdminAuthController::class, 'logout']);
    Route::get('/admin-access', function () {
        return 'admin access';
    });



    Route::prefix('users')->group(function () {
        // Update user by id
        Route::put('update/{id}', [UserController::class, 'update']);
        // Delete user by id
        Route::delete('delete/{id}', [UserController::class, 'delete']);
        // List all users
        Route::get('/', [UserController::class, 'index']);
        // Show user details by id
        Route::get('{id}', [UserController::class, 'show']);
    });



});
