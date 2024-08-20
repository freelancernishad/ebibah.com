<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\UserController;
use App\Http\Controllers\api\PackageController;
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


    Route::post('packages', [PackageController::class, 'store']);
    Route::post('packages/{id}', [PackageController::class, 'update']);
    Route::delete('packages/{id}', [PackageController::class, 'destroy']);
    Route::post('packages/{packageId}/services', [PackageController::class, 'addServiceToPackage']);
    Route::post('packages/{packageId}/services/{serviceId}/activate', [PackageController::class, 'activateService']);
    Route::post('packages/{packageId}/services/{serviceId}/deactivate', [PackageController::class, 'deactivateService']);






});
