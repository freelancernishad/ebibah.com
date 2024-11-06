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



                   // Apply height filter
            if ($request->has('height_from') && $request->has('height_to')) {
                $heightFromInches = $this->convertHeightToInches($request->height_from);
                $heightToInches = $this->convertHeightToInches($request->height_to);

                if ($heightFromInches !== null && $heightToInches !== null) {
                    $query->whereBetween('height', [$heightFromInches, $heightToInches]);
                }
            }

            // Exclude the authenticated user
            if ($authUserId) {
                $query->where('users.id', '!=', $authUserId);
            }

            // Exclude the current user
            if ($currentUser) {
                $query->where('gender', '!=', $currentUser->gender)
                      ->where('users.id', '!=', $currentUser->id);
            }

            // Get all users based on the query
            $users = $query->get();




            // 1. Get priority users with access to 'Priority Listing'
            $priorityUsers = $users->filter(function ($user) {
                return hasServiceAccess('Priority Listing', $user);
            });

            // 2. Get non-priority users with active_package_id not null
            $nonPriorityWithPackage = $users->filter(function ($user) {
                return !hasServiceAccess('Priority Listing', $user) && !is_null($user->active_package_id);
            });

            // 3. Get non-priority users with active_package_id as null (directly from the query)
            $nonPriorityUsers = $query->whereNull('active_package_id')->get();

            // 4. Merge all three collections in the desired order
            $combinedUsers = $priorityUsers->merge($nonPriorityWithPackage)->merge($nonPriorityUsers);


            // Paginate the combined users using Laravel's paginator
            $perPage = 10; // Number of users per page
            $currentPage = $request->input('page', 1); // Current page
            $offset = ($currentPage - 1) * $perPage; // Calculate the offset

            // Slice the combined users for pagination
            $paginatedUsers = $combinedUsers->slice($offset, $perPage)->values(); // Ensure re-indexing

            // Create a paginator instance
            $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
                $paginatedUsers, // Current items
                $combinedUsers->count(), // Total items
                $perPage, // Items per page
                $currentPage, // Current page
                ['path' => $request->url(), 'query' => $request->query()] // Pagination path and query parameters
            );

            // Return only the paginated users
            return $paginator; // or simply return $paginatedUsers if you only want the user data

        } catch (\Exception $e) {
            \Log::error('Error fetching users: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to fetch users. ' . $e->getMessage()], 500);
        }
    }


    private function convertHeightToInches($height)
    {
        if (preg_match('/^(\d+)ft\+(\d+)in$/', $height, $matches)) {
            $feet = (int) $matches[1];
            $inches = (int) $matches[2];
            return ($feet * 12) + $inches;
        }
        return null; // Return null if the format is invalid
    }








}
