<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class AdminDashboardController extends Controller
{
    /**
     * Display the dashboard metrics.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        // Total users
        $totalUsers = User::count();

        // New registrations in the last 7 days
        $newRegistrations = User::where('created_at', '>=', now()->subDays(7))->count();

        // Subscription status counts
        $activeSubscriptions = User::where('subscription_status', 'active')->count();
        $expiredSubscriptions = User::where('subscription_status', 'expired')->count();

        // Pending verifications
        $pendingVerifications = User::where('is_verified', false)->count();

        return response()->json([
            'total_users' => $totalUsers,
            'new_registrations' => $newRegistrations,
            'active_subscriptions' => $activeSubscriptions,
            'expired_subscriptions' => $expiredSubscriptions,
            'pending_verifications' => $pendingVerifications,
        ]);
    }
}