<?php


use App\Models\Permission;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MacController;

use App\Http\Controllers\PageController;



use App\Http\Controllers\RoleController;
use App\Http\Controllers\VisitorController;
use App\Http\Controllers\WeatherController;


use App\Http\Controllers\api\UserController;

use App\Http\Controllers\RoleUserController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\SocialLinkController;
use App\Http\Controllers\api\StudentController;
use App\Http\Controllers\AdvertisementController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\Auth\users\AuthController;
use App\Http\Controllers\api\OrganizationController;
use App\Http\Controllers\Auth\admins\AdminAuthController;
use App\Http\Controllers\Auth\students\StudentAuthController;
use App\Http\Controllers\Auth\orgs\OrganizationAuthController;



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

// Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
//     return $request->user();
// });




Route::get('get/all/route/name', function () {
// Get all routes
$routes = Route::getRoutes();

// Iterate through routes
foreach ($routes as $route) {
    // Get route action
    $action = $route->getAction();


    // Check if middleware is defined in the action
    if (isset($action['middleware'])) {
        $hasCheckPermission = false;
        foreach ($action['middleware'] as $middleware) {
            if (strpos($middleware, 'checkPermission') !== false) {
                $hasCheckPermission = true;
            }
        }



        // Check if the middleware is 'checkPermission'
        if ($hasCheckPermission) {



            // Get route name
            $routeName = $route->getName();


            // Check if route has a name
            if ($routeName) {
                // Check if permission already exists
                $existingPermission = Permission::where('path', $routeName)->first();

                // If permission doesn't exist, create it
                if (!$existingPermission) {
                    Permission::create([
                        'name' => $routeName,
                        'path' => $routeName,
                        // Add other attributes like element, permission, description if needed
                    ]);
                }
            }






        }
    }
}
});








Route::get('roles', [RoleController::class, 'index']);
Route::post('roles', [RoleController::class, 'store']);
Route::post('roles/{roles}', [RoleController::class, 'update']);
// Route::apiResource('roles', RoleController::class);

// Route::get('permissions/', [PermissionController::class, 'index']);
// Route::post('permissions', [PermissionController::class, 'store']);
// Route::post('permissions/{permissions}', [PermissionController::class, 'update']);

Route::apiResource('permissions', PermissionController::class);



Route::post('get/permissions/{id}', [RoleController::class, 'getPermissionsByRoleName']);
Route::post('roles/{role}/permissions/{permission}', [RolePermissionController::class, 'attachPermission']);
Route::post('roles/{roleId}/permissions', [RolePermissionController::class, 'addPermissionsToRole']);
Route::delete('roles/{role}/permissions/{permission}', [RolePermissionController::class, 'detachPermission']);






Route::get('/userAgent', function (Request $request) {
    return $userAgent = request()->header('User-Agent');
});


Route::get('/organizations', [OrganizationController::class, 'listOrganizations']);
Route::get('/organizations/lists', [OrganizationController::class, 'listOrganizationsWithPaginate']);
Route::get('organizations/single/{id}', [OrganizationController::class, 'show']);



//// user auth
Route::post('/user/login', [AuthController::class, 'login'])->name('login');
Route::post('/user/check/login', [AuthController::class, 'checkTokenExpiration'])->name('checklogin');
Route::post('/user/check-token', [AuthController::class, 'checkToken']);
Route::post('/user/register', [AuthController::class, 'register']);


Route::group(['middleware' => ['auth:api']], function () {
    Route::post('/user/logout', [AuthController::class, 'logout'])->name('user.logout');




    ///role users

    Route::prefix('users/role/system')->group(function () {
        Route::get('/', [RoleUserController::class, 'index']); // List all users
        Route::post('/', [RoleUserController::class, 'store']); // store a specific user
        Route::post('/{id}', [RoleUserController::class, 'update']); // Update a specific user
        Route::get('/{id}', [RoleUserController::class, 'show']); // show a specific user
        Route::delete('/{id}', [RoleUserController::class, 'destroy']); // Delete a specific user
    });




    Route::post('users/change-password', [UserController::class, 'changePassword'])->name('users.change_password')->middleware('checkPermission:users.change_password');
    Route::get('/user-access', function (Request $request) {
        return 'user access';
    })->name('user.access')->middleware('checkPermission:user.access');

    // Add names to other routes










    // Add names to other routes
    Route::post('/social-links', [SocialLinkController::class, 'store'])->name('social_links.store')->middleware('checkPermission:social_links.store');
    Route::post('/social-links/{idOrPlatform}', [SocialLinkController::class, 'update'])->name('social_links.update')->middleware('checkPermission:social_links.update');
    Route::delete('/social-links/{socialLink}', [SocialLinkController::class, 'destroy'])->name('social_links.destroy')->middleware('checkPermission:social_links.destroy');

    // Add names to other routes
    Route::get('/pages', [PageController::class, 'index'])->name('pages.index')->middleware('checkPermission:pages.index');
    Route::post('/pages', [PageController::class, 'store'])->name('pages.store')->middleware('checkPermission:pages.store');
    Route::get('/pages/{page}', [PageController::class, 'show'])->name('pages.show')->middleware('checkPermission:pages.show');
    Route::post('/pages/{page}', [PageController::class, 'update'])->name('pages.update')->middleware('checkPermission:pages.update');
    Route::delete('/pages/{page}', [PageController::class, 'destroy'])->name('pages.destroy')->middleware('checkPermission:pages.destroy');

    Route::post('advertisements', [AdvertisementController::class, 'store'])->name('advertisements.store')->middleware('checkPermission:advertisements.store');
    Route::delete('advertisements/{slug}', [AdvertisementController::class, 'destroy'])->name('advertisements.destroy')->middleware('checkPermission:advertisements.destroy');















});


Route::get('/weather', [WeatherController::class, 'show']);


















    Route::get('/social-links', [SocialLinkController::class, 'index']);
    Route::get('/social-links/{platform}', [SocialLinkController::class, 'showByPlatform']);

    Route::get('/pages/slug/{slug}', [PageController::class, 'showBySlug']);

    Route::get('advertisements', [AdvertisementController::class, 'index']);

    Route::get('/visitors', [VisitorController::class, 'index']);
    Route::get('/visitors/reports', [VisitorController::class, 'generateReports']);





//// organization auth
Route::post('/organization/login', [OrganizationAuthController::class, 'login']);
Route::post('/organization/check/login', [OrganizationAuthController::class, 'checkTokenExpiration']);
Route::post('/organization/check-token', [OrganizationAuthController::class, 'checkToken']);
Route::post('organization/register', [OrganizationAuthController::class, 'register']); // Organization registration

Route::group(['middleware' => ['auth:organization']], function () {
    Route::post('/organization/logout', [OrganizationAuthController::class, 'logout']);

    // Routes for organization registration, update, delete, and show
    Route::prefix('organizations')->group(function () {
        Route::put('{id}', [OrganizationController::class, 'update']);       // Update organization by ID
        Route::delete('{id}', [OrganizationController::class, 'delete']);    // Delete organization by ID
        Route::get('{id}', [OrganizationController::class, 'show']);          // Show organization details by ID
    });
    Route::post('organization/doners', [OrganizationController::class, 'getDonersByOrganization']);

    Route::post('organization/change-password', [OrganizationController::class, 'changePassword']);


    Route::get('/organization-access', function (Request $request) {
        return 'organization access';
    });
});



//// admin auth
Route::post('/admin/login', [AdminAuthController::class, 'login']);
Route::post('/admin/check/login', [AdminAuthController::class, 'checkTokenExpiration']);
Route::post('/admin/check-token', [AdminAuthController::class, 'checkToken']);
Route::post('admin/register', [AdminAuthController::class, 'register']);

Route::group(['middleware' => ['auth:admin']], function () {
    Route::post('admin/logout', [AdminAuthController::class, 'logout']);
    Route::get('/admin-access', function (Request $request) {
        return 'admin access';
    });
});


Route::post('/student/login', [StudentAuthController::class, 'login']);
Route::post('/student/check/login', [StudentAuthController::class, 'checkTokenExpiration']);
Route::post('/student/check-token', [StudentAuthController::class, 'checkToken']);
Route::post('/student/register', [StudentAuthController::class, 'register']); // Organization registration

Route::middleware('auth:student')->group(function () {
    Route::post('/student/logout', [StudentAuthController::class, 'logout']);

    Route::get('/students/profile/{id}', [StudentController::class, 'show']);

    Route::get('/student-access', function (Request $request) {
        return 'student access';
    });
});







