<?php

namespace App\Http\Controllers\api\Admin;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Package;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

class AdminDashboardController extends Controller
{
    /**
     * Display the dashboard metrics.
     *
     * @return JsonResponse
     */


     public function index(Request $request)
     {
         $year = $request->year ?? now()->year;
         $week = $request->week ?? 'current';

         $fromDate = $request->from_date;


         $toDate = isset($request->to_date) ? $request->to_date : $fromDate;





         // Total users
         $totalUsers = User::count();

         // New registrations in the last 7 days
         $newRegistrations = User::where('created_at', '>=', now()->subDays(7))->count();

         // Subscribed users
         $subscribedUsers = User::whereNotNull('active_package_id')->count();

         // Pending verifications
         $pendingVerifications = User::whereNull('email_verified_at')->count();

         // Package revenue data (monthly, yearly, weekly)
         $packageRevenueData = getPackageRevenueData($year, $week);

         // Total revenue by package
         $totalRevenueByPackage = $packageRevenueData['total_revenue_per_package'];

         // Weekly package revenue max value
         $weeklyPackageRevenueMax = $packageRevenueData['weekly_package_revenue_max'];

         // Calculate revenue by package within a date range if provided
         $revenueByDate = [];
         if ($fromDate) {
             $revenueByDate = Package::all()->map(function ($package) use ($fromDate, $toDate) {
                 // Get total revenue for the package within the specified date range or day
                 $totalAmount = Payment::whereHas('packagePurchase', function ($query) use ($package) {
                         $query->where('package_id', $package->id);
                     })
                     ->where('type', 'package')
                     ->where('status', 'completed');


                     if($toDate=='undefined'){
                        $fromDate = date("Y-m-d", strtotime($fromDate));
                        $totalAmount->where('date', $fromDate);
                    }else{
                        $totalAmount->whereBetween('date', [$fromDate, $toDate]);
                    }



                     $totalAmount = $totalAmount->sum('amount');

                 return [
                     'name' => $package->package_name,
                     'total_amount' => (int) $totalAmount, // Cast to integer
                 ];
             })->toArray();
         }

         // Calculate total revenue across all packages
         $totalRevenue = Payment::where('type', 'package')
             ->where('status', 'completed')
             ->sum('amount');

         return response()->json([
             'total_users' => $totalUsers,
             'new_registrations' => $newRegistrations,
             'subscribed_users' => $subscribedUsers,
             'pending_verifications' => $pendingVerifications,
             'package_revenue' => $packageRevenueData['monthly_package_revenue'],
             'package_revenue_max' => $packageRevenueData['monthly_package_revenue_max'],
             'total_revenue_per_package' => $totalRevenueByPackage,
             'yearly_package_revenue' => $packageRevenueData['yearly_package_revenue'],
             'weekly_package_revenue' => $packageRevenueData['weekly_package_revenue'],
             'weekly_package_revenue_max' => $weeklyPackageRevenueMax,
             'revenue_by_date' => $revenueByDate, // Revenue by package within date range
             'total_revenue' => (int) $totalRevenue, // Total revenue across all packages
         ]);
     }


}
