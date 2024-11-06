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
use App\Http\Controllers\ServerStatusController;
use App\Http\Controllers\AdvertisementController;
use App\Http\Controllers\StripePaymentController;
use App\Http\Controllers\Global\FilterUserController;
use App\Models\User;

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

// Retrieve User-Agent
Route::get('/userAgent', function (Request $request) {
    return request()->header('User-Agent');
});

// Settings routes
Route::get('/settings', [SettingController::class, 'index']);
Route::post('/settings', [SettingController::class, 'store']);

// Weather route
Route::get('/weather', [WeatherController::class, 'show']);

// Social Links routes
Route::get('/social-links', [SocialLinkController::class, 'index']);
Route::get('/social-links/{platform}', [SocialLinkController::class, 'showByPlatform']);

// Pages route by slug
Route::get('/pages/slug/{slug}', [PageController::class, 'showBySlug']);

// Advertisements route
Route::get('advertisements', [AdvertisementController::class, 'index']);

// Visitors routes
Route::get('/visitors', [VisitorController::class, 'index']);
Route::get('/visitors/reports', [VisitorController::class, 'generateReports']);

// Partner search filter
Route::get('/search/partner', [FilterUserController::class, 'filter']);

// Package routes
Route::get('packages', [PackageController::class, 'index']);
Route::get('packages/{id}', [PackageController::class, 'show']);
Route::get('packages/{packageId}/services', [PackageController::class, 'getPackageServices']);
Route::get('package-services', [PackageController::class, 'getAllPackageServices']);

// Stripe webhook
Route::post('stripe/webhook', [StripePaymentController::class, 'handleWebhook']);



Route::get('/server-status', [ServerStatusController::class, 'status']);



Route::get('update-random-height', function () {
    // Get all users from the database
    $users = User::all();

    // Loop through each user
    foreach ($users as $user) {
        // Generate a random height value between 50 and 66 inches
        $randomHeight = rand(50, 66); // Random number between 50 and 66 inches

        // Update the user's height with the random value
        $user->update(['height' => $randomHeight]);
    }

    return "Random height update complete.";
});

