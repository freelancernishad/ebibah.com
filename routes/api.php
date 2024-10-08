<?php

use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\VisitorController;
use App\Http\Controllers\WeatherController;
use App\Http\Controllers\SocialLinkController;
use App\Http\Controllers\api\PackageController;
use App\Http\Controllers\AdvertisementController;
use App\Http\Controllers\StripePaymentController;
use App\Http\Controllers\Global\FilterUserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/





Route::get('/userAgent', function (Request $request) {
    return request()->header('User-Agent');
});


Route::get('/settings', [SettingController::class, 'index']);
Route::post('/settings', [SettingController::class, 'store']);


Route::get('/weather', [WeatherController::class, 'show']);

Route::get('/social-links', [SocialLinkController::class, 'index']);
Route::get('/social-links/{platform}', [SocialLinkController::class, 'showByPlatform']);

Route::get('/pages/slug/{slug}', [PageController::class, 'showBySlug']);

Route::get('advertisements', [AdvertisementController::class, 'index']);

Route::get('/visitors', [VisitorController::class, 'index']);
Route::get('/visitors/reports', [VisitorController::class, 'generateReports']);




Route::get('/search/partner', [FilterUserController::class, 'filter']);


Route::get('packages', [PackageController::class, 'index']);
Route::get('packages/{id}', [PackageController::class, 'show']);
Route::get('packages/{packageId}/services', [PackageController::class, 'getPackageServices']);
Route::get('package-services', [PackageController::class, 'getAllPackageServices']);



Route::post('stripe/webhook', [StripePaymentController::class, 'handleWebhook']);
