<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class UserProfileController extends Controller
{
    /**
     * Get users matching the profile partner details.
     *
     * @return \Illuminate\Http\Response
     */
    public function getMatchingUsers(Request $request)
    {


        $perPage = $request->input('per_page', 10);
       $matchType = $request->input('type'); // e.g., 'new', 'today', 'my', 'near'

       if ($matchType == 'near') {
        // Ensure the user is authenticated
        $authUser = Auth::user();

        if (!$authUser) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Get all states associated with the authenticated user's PartnerStates
        $authUserStates = $authUser->partnerStates->pluck('state')->toArray();

        // Determine the gender to filter opposite to the authenticated user's gender
        //  $oppositeGender = $authUser->gender === 'Male' ? 'Female' : 'Male';


         if ($authUser->gender === 'Male') {
            // If authenticated user is Male, show Female users
            $oppositeGender = 'Female';
        } elseif ($authUser->gender === 'Female') {
            // If authenticated user is Female, show Male users
            $oppositeGender = 'Male';
        }


        // Fetch users who are in the authenticated user's PartnerStates, have the opposite gender, and exclude the authenticated user
        $matchingUsersArray = User::whereIn('state', $authUserStates)
            ->where('gender', $oppositeGender)
            ->where('id', '!=', $authUser->id) // Exclude the authenticated user
            ->get();

        // Prepare the response with only the specified fields
        $matchingUsers = prepareResponse($matchingUsersArray, null, $matchType);
    } else {
        // Use the profile_matches function for other match types
        $matchingUsers = profile_matches($matchType);
    }





        // Return the matching users as a JSON response, including the match_percentage
        return response()->json([
            'status' => 'success',
            'data' => $matchingUsers,
        ]);
    }



    function prepareNearResponse($users, $limit)
    {
        // Define the fields to be displayed
        $fields = [
            'id', 'name', 'age', 'gender', 'height',
            'city_living_in', 'state', 'living_country',
            'religion', 'marital_status', 'working_sector',
            'profession', 'about_myself', 'profile_picture_url',
            'invitation_send_status', 'is_favorited',
            'premium_member_badge', 'trusted_badge_access',
            'totalCriteriaMatched', 'matched_fields', 'is_friend'
        ];

        // Map the result to include the specified fields without nesting
        $result = $users->map(function ($user) use ($fields) {
            return array_intersect_key($user->toArray(), array_flip($fields));
        })->values()->all(); // Reset keys

        // Apply the optional limit if provided
        if ($limit !== null) {
            $result = array_slice($result, 0, $limit); // Use array_slice to limit results
        }
        $pagination = [
            'current_page' => $users->currentPage(),
            'data' => $result,
            'first_page_url' => $users->url(1),
            'from' => $users->firstItem(),
            'last_page' => $users->lastPage(),
            'last_page_url' => $users->url($users->lastPage()),
            'links' => [],
            'next_page_url' => $users->nextPageUrl(),
            'path' => $users->path(),
            'per_page' => $users->perPage(),
            'prev_page_url' => $users->previousPageUrl(),
            'to' => $users->lastItem(),
            'total' => $users->total(),
        ];

        return $pagination;
    }

    /**
     * Apply additional filters based on the type of match requested.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $matchType
     * @param \App\Models\User $user
     */





    /**
     * Get a single user by username and check if they match the authenticated user's partner preferences.
     *
     * @param  string $username
     * @return \Illuminate\Http\Response
     */
    public function getSingleUserWithAuthUserMatch($id)
    {
        // Get the authenticated user
        $authUser = Auth::user();

        // Check if the requested user ID is the same as the authenticated user ID
        if ($authUser->id == $id) {
            return response()->json(['message' => 'You cannot access your own data.'], 403);
        }

        // Find the user by ID with the related images
        $user = User::with('userImages')->find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Convert the user model to an array with relations
        $userArray = $user->toArrayWithRelations();
        $userArray['is_contact_details_viewed'] = $authUser->hasViewedProfile($userArray['id']);

        // Call the global getCriteriaMatches function to get matching criteria
        $criteriaMatches = getCriteriaMatches($authUser->id, $id) ?? [];

        // Calculate match score and percentage
        $matchScore = array_reduce($criteriaMatches, function ($carry, $match) {
            return $carry + ($match['match'] ? 1 : 0);
        }, 0);

        $totalCriteria = count($criteriaMatches);
        $matchPercentage = $totalCriteria > 0 ? ($matchScore / $totalCriteria) * 100 : 0;

        // Define match threshold (e.g., 20% match required)
        $matchThreshold = ceil($totalCriteria * 0.2);
        $isMatch = $matchScore >= $matchThreshold;

        // Get similar profiles (make sure this method exists in your User model)
        $similarProfiles = $user->getSimilarProfiles(10);

        return response()->json([
            'user' => maskUserData($userArray, $authUser),
            'is_match' => $isMatch,
            'match_percentage' => $matchPercentage,
            'match_score' => $matchScore,
            'criteria_matches' => $criteriaMatches,
            'similar_profiles' => $similarProfiles,
        ]);
    }


















    }
