<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\api\UserController;
use App\Http\Controllers\RoleUserController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\Auth\users\AuthController;

Route::post('store/permissions', [RolePermissionController::class, 'storePermissions']);

Route::get('roles', [RoleController::class, 'index']);
Route::post('roles', [RoleController::class, 'store']);
Route::post('roles/{roles}', [RoleController::class, 'update']);
Route::apiResource('permissions', PermissionController::class);

Route::post('get/permissions/{id}', [RoleController::class, 'getPermissionsByRoleName']);
Route::post('roles/{role}/permissions/{permission}', [RolePermissionController::class, 'attachPermission']);
Route::post('roles/{roleId}/permissions', [RolePermissionController::class, 'addPermissionsToRole']);
Route::delete('roles/{role}/permissions/{permission}', [RolePermissionController::class, 'detachPermission']);



// User authentication routes
Route::post('/user/login', [AuthController::class, 'login'])->name('login');
Route::post('/user/check/login', [AuthController::class, 'checkTokenExpiration'])->name('checklogin');
Route::post('/user/check-token', [AuthController::class, 'checkToken']);
Route::post('/user/register', [AuthController::class, 'register']);

// Register a new user
// Route::post('users/register', [UserController::class, 'register']);







Route::middleware(['auth:api'])->group(function () {
    Route::post('/user/logout', [AuthController::class, 'logout'])->name('user.logout');

    Route::prefix('users/role/system')->group(function () {
        Route::get('/', [RoleUserController::class, 'index']);
        Route::post('/', [RoleUserController::class, 'store']);
        Route::post('/{id}', [RoleUserController::class, 'update']);
        Route::get('/{id}', [RoleUserController::class, 'show']);
        Route::delete('/{id}', [RoleUserController::class, 'destroy']);
    });

    Route::post('users/change-password', [UserController::class, 'changePassword'])
        ->name('users.change_password')
        ->middleware('checkPermission:users.change_password');


    Route::post('/users/{id}/basics-lifestyle', [UserController::class, 'updateBasicsAndLifestyle']);
    Route::put('/users/{id}/religious-background', [UserController::class, 'updateReligiousBackground']);
    Route::put('/users/{id}/education-career', [UserController::class, 'updateEducationAndCareer']);
    Route::put('/users/{id}/family-details', [UserController::class, 'updateFamilyDetails']);
    // Update Hobbies and Interests
    Route::put('/users/{id}/hobbies-interests', [UserController::class, 'updateHobbiesAndInterests']);
    Route::put('/user/{id}/update-partner-basics-lifestyle', [UserController::class, 'updatePartnerBasicsAndLifestyle']);
    Route::put('/user/{id}/update-partner-location-details', [UserController::class, 'updatePartnerLocationDetails']);
    Route::put('/user/{id}/update-partner-education-career', [UserController::class, 'updatePartnerEducationAndCareer']);
    Route::put('/users/{id}/profile', [UserController::class, 'update'])->name('users.update');




    Route::get('/user-access', function () {
        return 'user access';
    })->name('user.access')->middleware('checkPermission:user.access');
});





