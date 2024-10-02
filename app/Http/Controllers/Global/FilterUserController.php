<?php

namespace App\Http\Controllers\Global;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class FilterUserController extends Controller
{
    public function filter(Request $request)
    {
        try {
            $authUserId = Auth::id(); // Get the authenticated user's ID
            $query = User::query();

            // Apply filters
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
                $query->whereBetween('date_of_birth', [
                    now()->subYears($request->age_to)->toDateString(),
                    now()->subYears($request->age_from)->toDateString(),
                ]);
            }

            if ($request->has('living_country')) {
                $livingCountries = explode(',', $request->living_country);
                $query->whereIn('living_country', $livingCountries);
            }

            if ($request->has('highest_qualification')) {
                $qualifications = explode(',', $request->highest_qualification);
                $query->whereIn('highest_qualification', $qualifications);
            }

            // Exclude the authenticated user from the results if they are logged in
            if ($authUserId) {
                $query->where('users.id', '!=', $authUserId); // Qualify the id column
            }

            // Add sorting by popularity and select specific columns
            $query->leftJoin('popularities', 'users.id', '=', 'popularities.user_id')
                  ->select('users.*', 'popularities.views', 'popularities.likes')
                  ->orderByDesc('popularities.views')
                  ->orderByDesc('popularities.likes');

            // Pagination
            $users = $query->paginate(10); // 10 users per page

            // Add age calculation to each user
            $users->getCollection()->transform(function ($user) {
                $user->age = calculateAge($user->date_of_birth);
                return $user;
            });

            return response()->json($users);

        } catch (\Exception $e) {
            // Log the error message and return a response
            \Log::error('Error fetching users: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to fetch users.'], 500);
        }
    }



}
