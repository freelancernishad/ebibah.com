<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\UserController;
use App\Http\Controllers\api\PackageController;
use App\Http\Controllers\Api\Admin\AdminUserController;
use App\Http\Controllers\api\PackagePurchaseController;
use App\Http\Controllers\Backed\SettingBackedController;
use App\Http\Controllers\Auth\admins\AdminAuthController;
use App\Http\Controllers\Api\Admin\AdminDashboardController;

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


    Route::post('/settings', [SettingBackedController::class, 'store']);
    Route::put('/settings/{id}', [SettingBackedController::class, 'update']);
    Route::delete('/settings/{id}', [SettingBackedController::class, 'destroy']);


    Route::post('/settings/{setting_id}/values', [SettingBackedController::class, 'storeValue']);
    Route::put('/settings/{setting_id}/values/{value_id}', [SettingBackedController::class, 'updateValue']);
    Route::delete('/settings/{setting_id}/values/{value_id}', [SettingBackedController::class, 'destroyValue']);






        // Dashboard
        Route::get('admin/dashboard', [AdminDashboardController::class, 'index']);

        // User Management
        Route::get('admin/users', [AdminUserController::class, 'index']);
        Route::get('/admin/users/inactive', [AdminUserController::class, 'inactiveUsers']);
        Route::get('/admin/users/banned', [AdminUserController::class, 'bannedUsers']);



        Route::get('admin/users/{id}', [AdminUserController::class, 'show']);
        Route::put('admin/users/{id}', [UserController::class, 'update']);

        Route::post('admin/users/{id}/activate', [AdminUserController::class, 'activate'])->name('users.activate');
        Route::post('admin/users/{id}/deactivate', [AdminUserController::class, 'deactivate'])->name('users.deactivate');
        Route::post('admin/users/{id}/ban', [AdminUserController::class, 'ban']);








    Route::post('packages', [PackageController::class, 'store']);
    Route::post('packages/{id}', [PackageController::class, 'update']);
    Route::delete('packages/{id}', [PackageController::class, 'destroy']);

    Route::post('packages/create/services', [PackageController::class, 'createPackageService']);
    Route::post('packages/update/services/{id}', [PackageController::class, 'updatePackageService']);
    Route::delete('packages/delete/services/{id}', [PackageController::class, 'deletePackageService']);


    Route::post('packages/{packageId}/services/{serviceId}/activate', [PackageController::class, 'activateService']);
    Route::post('packages/{packageId}/services/{serviceId}/deactivate', [PackageController::class, 'deactivateService']);
    Route::post('packages/{packageId}/services/multiple', [PackageController::class, 'addMultipleServicesToPackage']);
    Route::post('packages/{packageId}/services/deactivate', [PackageController::class, 'deactivateMultipleServices']);

    Route::post('packages/{packageId}/services/update-status', [PackageController::class, 'updateServicesStatus']);

    Route::get('all-purchases', [PackagePurchaseController::class, 'allPurchases']);

});
