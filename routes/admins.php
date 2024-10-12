<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\UserController;
use App\Http\Controllers\api\PackageController;
use App\Http\Controllers\api\Admin\AdminUserController;
use App\Http\Controllers\api\PackagePurchaseController;
use App\Http\Controllers\api\Admin\PaymentLogController;
use App\Http\Controllers\Backed\SettingBackedController;
use App\Http\Controllers\Auth\admins\AdminAuthController;
use App\Http\Controllers\api\Admin\AdminDashboardController;
use App\Http\Controllers\api\Admin\AdminUserImageController;
use App\Http\Controllers\api\Admin\AdminSupportTicketApiController;

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
        Route::delete('/admin/users/{id}', [AdminUserController::class, 'destroy']);


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



     // List all support tickets
     Route::get('/admin/support', [AdminSupportTicketApiController::class, 'index']);

     // View a specific support ticket
     Route::get('/admin/support/{ticket}', [AdminSupportTicketApiController::class, 'show']);

     // Reply to a support ticket
     Route::post('/admin/support/{ticket}/reply', [AdminSupportTicketApiController::class, 'reply']);

     // Update the status of a support ticket
     Route::patch('/admin/support/{ticket}/status', [AdminSupportTicketApiController::class, 'updateStatus']);



       // List approved user images
    Route::get('/admin/user-images', [AdminUserImageController::class, 'index']);

    // Approve a user image
    Route::patch('/admin/user-images/{id}/approve', [AdminUserImageController::class, 'approve']);

    // Reject a user image
    Route::patch('/admin/user-images/{id}/reject', [AdminUserImageController::class, 'reject']);


    // List approved user images
    Route::get('/admin/user-images/approved', [AdminUserImageController::class, 'approved']);

    // List rejected user images
    Route::get('/admin/user-images/rejected', [AdminUserImageController::class, 'rejected']);

    // List pending user images
    Route::get('/admin/user-images/pending', [AdminUserImageController::class, 'pending']);



        // List all payment transactions
        Route::get('/admin/payment-logs', [PaymentLogController::class, 'index']);

        // View details of a specific payment transaction
        Route::get('/admin/payment-logs/{id}', [PaymentLogController::class, 'show']);





});
