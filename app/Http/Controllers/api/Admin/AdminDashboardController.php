<?php

namespace App\Http\Controllers\api\Admin;

use Mpdf\Mpdf;
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
         $toDate = $request->to_date ?? $fromDate;

         // Total users
         $totalUsers = User::count();

         // New registrations in the last 7 days
         $newRegistrations = User::where('created_at', '>=', now()->subDays(7))->count();

         // Subscribed users
         $subscribedUsers = User::whereNotNull('active_package_id')->count();

         // Pending verifications
         $pendingVerifications = User::whereNull('email_verified_at')->count();

         // Package revenue data
         $packageRevenueData = getPackageRevenueData($year, $week);

         // Total revenue by package
         $totalRevenueByPackage = $packageRevenueData['total_revenue_per_package'];

         // Weekly package revenue max value
         $weeklyPackageRevenueMax = $packageRevenueData['weekly_package_revenue_max'];

         // Revenue by date range
         $revenueByDate = [];
         if ($fromDate) {
             $revenueByDate = Package::all()->map(function ($package) use ($fromDate, $toDate) {
                 $totalAmount = Payment::whereHas('packagePurchase', function ($query) use ($package) {
                     $query->where('package_id', $package->id);
                 })
                 ->where('type', 'package')
                 ->where('status', 'completed');

                 if ($toDate == 'undefined') {
                     $fromDate = date("Y-m-d", strtotime($fromDate));
                     $totalAmount->where('date', $fromDate);
                 } else {
                     $totalAmount->whereBetween('date', [$fromDate, $toDate]);
                 }

                 $totalAmount = $totalAmount->sum('amount');

                 return [
                     'name' => $package->package_name,
                     'total_amount' => (int) $totalAmount,
                 ];
             })->toArray();
         }

         // Total revenue across all packages
         $totalRevenue = Payment::where('type', 'package')
             ->where('status', 'completed')
             ->sum('amount');

         // Check if PDF generation is requested
         if ($request->pdf == 'true') {
             $data = [
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
                 'revenue_by_date' => $revenueByDate,
                 'total_revenue' => (int) $totalRevenue,
             ];

             // Load mPDF and generate the PDF
             $mpdf = new Mpdf();
             $html = view('reports.package_revenue', $data)->render(); // Create a Blade view for the report
             $mpdf->WriteHTML($html);
             return $mpdf->Output('report.pdf', 'I'); // Output to browser
         }

         // Return JSON response
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
             'revenue_by_date' => $revenueByDate,
             'total_revenue' => (int) $totalRevenue,
         ]);
     }



}
