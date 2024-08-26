<?php

namespace App\Http\Controllers\Global;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class FilterUserController extends Controller
{
    public function filter(Request $request)
    {
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
            $query->where('marital_status', $request->marital_status);
        }

        if ($request->has('age_from') && $request->has('age_to')) {
            $query->whereBetween('date_of_birth', [
                now()->subYears($request->age_to)->toDateString(),
                now()->subYears($request->age_from)->toDateString(),
            ]);
        }

        if ($request->has('living_country')) {
            $query->where('living_country', $request->living_country);
        }

        if ($request->has('highest_qualification')) {
            $qualifications = explode(',', $request->highest_qualification);
            $query->whereIn('highest_qualification', $qualifications);
        }

        // Add sorting by popularity
        $query->with('popularity')
            ->leftJoin('popularities', 'users.id', '=', 'popularities.user_id')
            ->orderByDesc('popularities.views')
            ->orderByDesc('popularities.likes');

        // Pagination
        $users = $query->paginate(10); // 10 users per page

        return response()->json($users);
    }
}