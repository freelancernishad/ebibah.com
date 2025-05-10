<?php

namespace App\Http\Middleware;

use Closure;

class IpAddressProtectionMiddleware
{


    protected $allowedIPs = [
        '',
        'http://test.localhost:8000',
        'http://localhost:5173',
        'https://usamarry.com',
        'https://www.usamarry.com',
        'https://ebibah.com',
        'https://www.ebibah.com',
        'https://admin.ebibah.com',
        'https://www.admin.ebibah.com',
        'https://ebibah-dashboard.vercel.app',
        'https://ebibah.zsi.ai',

    ];


    public function handle($request, Closure $next)
    {
       $requestIP = $request->header('Origin');
        if (!in_array($requestIP, $this->allowedIPs)) {
            return response()->json([
                'message' => 'Access denied. Your IP is not allowed.',
            ], 403);
        }

        return $next($request);
    }
}
