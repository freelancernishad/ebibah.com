<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPermission
{
    public function handle(Request $request, Closure $next, $routeName)
    {
        // Check if the user has the specified permission for the route
        if ($request->user()->hasPermission($routeName)) {
            return $next($request);
        }

        // If the user doesn't have the required permission, return 403 Forbidden
        return response()->json(['error' => 'Permission denied.','status'=>403], 200);
    }
}
