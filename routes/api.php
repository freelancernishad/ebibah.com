<?php

use App\Models\Permission;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\PageController;
use App\Http\Controllers\VisitorController;
use App\Http\Controllers\WeatherController;
use App\Http\Controllers\SocialLinkController;
use App\Http\Controllers\AdvertisementController;

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

Route::get('/weather', [WeatherController::class, 'show']);

Route::get('/social-links', [SocialLinkController::class, 'index']);
Route::get('/social-links/{platform}', [SocialLinkController::class, 'showByPlatform']);

Route::get('/pages/slug/{slug}', [PageController::class, 'showBySlug']);

Route::get('advertisements', [AdvertisementController::class, 'index']);

Route::get('/visitors', [VisitorController::class, 'index']);
Route::get('/visitors/reports', [VisitorController::class, 'generateReports']);
