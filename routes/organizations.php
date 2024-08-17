<?php

use App\Http\Controllers\api\OrganizationController;
use App\Http\Controllers\Auth\orgs\OrganizationAuthController;
use Illuminate\Support\Facades\Route;



Route::get('/organizations', [OrganizationController::class, 'listOrganizations']);
Route::get('/organizations/lists', [OrganizationController::class, 'listOrganizationsWithPaginate']);
Route::get('organizations/single/{id}', [OrganizationController::class, 'show']);


//// Organization auth routes
Route::post('/organization/login', [OrganizationAuthController::class, 'login']);
Route::post('/organization/check/login', [OrganizationAuthController::class, 'checkTokenExpiration']);
Route::post('/organization/check-token', [OrganizationAuthController::class, 'checkToken']);
Route::post('organization/register', [OrganizationAuthController::class, 'register']);

Route::group(['middleware' => ['auth:organization']], function () {
    Route::post('/organization/logout', [OrganizationAuthController::class, 'logout']);

    Route::prefix('organizations')->group(function () {
        Route::put('{id}', [OrganizationController::class, 'update']);
        Route::delete('{id}', [OrganizationController::class, 'delete']);
        Route::get('{id}', [OrganizationController::class, 'show']);
    });

    Route::post('organization/doners', [OrganizationController::class, 'getDonersByOrganization']);
    Route::post('organization/change-password', [OrganizationController::class, 'changePassword']);

    Route::get('/organization-access', function () {
        return 'organization access';
    });
});
