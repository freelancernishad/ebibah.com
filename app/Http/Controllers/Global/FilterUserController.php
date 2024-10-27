<?php

namespace App\Http\Controllers\Global;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\PackageService;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class FilterUserController extends Controller
{

    public function filter(Request $request)
    {
        try {
            $authUserId = Auth::id();
            $currentUser = Auth::user();
            $query = User::query();

            // Apply basic filters
            if ($request->has('gender')) {
                $query->where('gender', $request->gender);
            }

            if ($request->has('religion')) {
                $religions = explode(',', $request->religion);
                $query->whereIn('religion', $religions);
            }

            if ($request->has('marital_status')) {
                $maritalStatuses = explode(',', $request->marital_status);
                $query->whereIn('marital_status', $maritalStatuses);
            }

            if ($request->has('age_from') && $request->has('age_to')) {
                $ageFrom = $request->age_from;
                $ageTo = $request->age_to;
                $dateFrom = now()->subYears($ageTo + 1)->addDay()->toDateString();
                $dateTo = now()->subYears($ageFrom)->toDateString();
                $query->whereBetween('date_of_birth', [$dateFrom, $dateTo]);
            }

            if ($request->has('living_country')) {
                $livingCountries = explode(',', $request->living_country);
                $query->whereIn('living_country', $livingCountries);
            }

            if ($request->has('highest_qualification')) {
                $qualifications = explode(',', $request->highest_qualification);
                $query->whereIn('highest_qualification', $qualifications);
            }

            if ($authUserId) {
                $query->where('users.id', '!=', $authUserId);
            }

            if ($currentUser) {
                $query->where('gender', '!=', $currentUser->gender)
                      ->where('users.id', '!=', $currentUser->id);
            }

            // Retrieve the ID of the "priority listing" service from PackageService
            $priorityService = PackageService::where('slug', 'priority-listing')->first();
            $priorityServiceId = $priorityService->id ?? null;

            // Separate priority users based on active "priority listing" service
            $priorityUsersQuery = (clone $query)
                ->whereExists(function ($subQuery) use ($priorityServiceId) {
                    $subQuery->select(DB::raw(1))
                             ->from('package_purchases')
                             ->join('package_active_services', 'package_purchases.package_id', '=', 'package_active_services.package_id')
                             ->whereColumn('package_purchases.user_id', 'users.id')
                             ->where('package_active_services.service_id', $priorityServiceId)
                             ->where('package_active_services.status', 'active');
                });

            // Regular users without priority listing
            $nonPriorityUsersQuery = (clone $query)
                ->whereNotExists(function ($subQuery) use ($priorityServiceId) {
                    $subQuery->select(DB::raw(1))
                             ->from('package_purchases')
                             ->join('package_active_services', 'package_purchases.package_id', '=', 'package_active_services.package_id')
                             ->whereColumn('package_purchases.user_id', 'users.id')
                             ->where('package_active_services.service_id', $priorityServiceId)
                             ->where('package_active_services.status', 'active');
                });

            // Combine both priority and non-priority users
            $users = $priorityUsersQuery->union($nonPriorityUsersQuery)->paginate(10);


            return response()->json($users);

        } catch (\Exception $e) {
            \Log::error('Error fetching users: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to fetch users.'], 500);
        }
    }





}
