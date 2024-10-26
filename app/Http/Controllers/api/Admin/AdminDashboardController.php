<?php

namespace App\Http\Controllers\api\Admin;

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
        // $activeSubscriptions = User::where('subscription_status', 'active')->count();
        // $expiredSubscriptions = User::where('subscription_status', 'expired')->count();

        $subscribedUsers = User::whereNotNull('active_package_id')->count();



        // Pending verifications
        $pendingVerifications = User::whereNull('email_verified_at')->count();


        return response()->json([
            'total_users' => $totalUsers,
            'new_registrations' => $newRegistrations,
            'subscribedUsers' => $subscribedUsers,
            // 'active_subscriptions' => $activeSubscriptions,
            // 'expired_subscriptions' => $expiredSubscriptions,
            'pending_verifications' => $pendingVerifications,
            'package_revenue' => getPackageRevenueData()['monthly_package_revenue'],
            'total_revenue_per_package' => getPackageRevenueData()['total_revenue_per_package'],
            'yearly_package_revenue' => getPackageRevenueData()['yearly_package_revenue'],
            'weekly_package_revenue' => getPackageRevenueData()['weekly_package_revenue'],
        ]);
    }
}
