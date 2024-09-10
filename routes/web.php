<?php

use App\Http\Controllers\InvoiceController;
use App\Models\Article;
use App\Services\DateService;
use App\Services\ContentService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});




require __DIR__.'/auth.php';



Route::get('/invoice/{name}/{id}', [InvoiceController::class,'invoice']);


Route::get('/files/{path}', function ($path) {

    // Serve the file from the protected disk
    return response()->file(Storage::disk('protected')->path($path));
})->where('path', '.*');



Route::get('/s3/{path}', function ($path) {
    // Generate the URL to the file on S3
    $url = Storage::disk('s3')->url($path);

    // Replace the default S3 URL with your custom domain
    $url = str_replace('usa-marry-bucket.s3.us-west-1.amazonaws.com', 'media.usamarry.com', $url);

    // Redirect to the custom domain URL
    return $url;

})->where('path', '.*');

