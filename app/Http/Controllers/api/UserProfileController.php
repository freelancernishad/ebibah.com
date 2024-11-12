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
        $oppositeGender = $authUser->gender === 'male' ? 'female' : 'male';

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

        // Find the user by id with the related images
        $user = User::with('userImages')->find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Convert the user model to an array with relations
        $userArray = $user->toArrayWithRelations();

        $userArray['is_contact_details_viewed'] = $authUser->hasViewedProfile($userArray['id']);

        // Define the authenticated user's partner preferences using relations
        $partnerPreferences = [
            'marital_status' => $authUser->partnerMaritalStatuses()->pluck('marital_status')->toArray(),
            'religion' => $authUser->partnerReligions()->pluck('religion')->toArray(),
            'community' => $authUser->partnerCommunities()->pluck('community')->toArray(),
            'mother_tongue' => $authUser->partnerMotherTongues()->pluck('mother_tongue')->toArray(),
            'highest_qualification' => $authUser->partnerQualification()->pluck('qualification')->toArray(),
            'working_sector' => $authUser->partnerWorkingWith()->pluck('working_with')->toArray(),
            'profession' => $authUser->partnerProfessions()->pluck('profession')->toArray(),
            'profession_details' => $authUser->partnerProfessionalDetails()->pluck('profession')->toArray(),
            'living_country' => $authUser->partnerCountries()->pluck('country')->toArray(),
            'state' => $authUser->partnerStates()->pluck('state')->toArray(),
            'city_living_in' => $authUser->partnerCities()->pluck('city')->toArray(),
        ];

        // Initialize arrays for SQL CASE statements and bindings
        $scoreConditions = [];
        $bindings = [];
        $matches = []; // This will store details of which criteria matched and which didn't

        foreach ($partnerPreferences as $column => $value) {
            if (!isset($value) || (is_array($value) && empty($value))) {
                continue;
            }

            if (is_array($value)) {
                $placeholders = implode(',', array_fill(0, count($value), '?'));
                $scoreConditions[] = "(CASE WHEN LOWER($column) IN (" . $placeholders . ") THEN 1 ELSE 0 END)";
                $bindings = array_merge($bindings, array_map('strtolower', $value));
            } else {
                $scoreConditions[] = "(CASE WHEN LOWER($column) = LOWER(?) THEN 1 ELSE 0 END)";
                $bindings[] = strtolower($value);
            }
        }


        if (empty($scoreConditions)) {
            return response()->json([
                'user' => maskUserData($userArray,$authUser),
                'is_match' => false,
                'match_percentage' => 0,
                'match_score' => 0,
                'criteria_matches' => [],
                'similar_profiles' => [],
            ]);
        }

        // Prepare SQL query to calculate match score
        $totalCriteria = count($scoreConditions);
        $matchScoreQuery = DB::table('users')
            ->selectRaw(
                'users.*, (' . implode(' + ', $scoreConditions) . ') as match_score',
                $bindings
            )
            ->where('users.id', $userArray['id'])
            ->first();

        // Calculate match score and percentage
        $matchScore = $matchScoreQuery->match_score;
        $matchPercentage = ($matchScore / $totalCriteria) * 100;

        // Define match threshold (e.g., 20% match required)
        $matchThreshold = ceil($totalCriteria * 0.2);
        $isMatch = $matchScore >= $matchThreshold;

        // Define display names for preferences
        $displayNames = [
            'marital_status' => 'Marital Status',
            'religion' => 'Religion',
            'community' => 'Community',
            'mother_tongue' => 'Mother Tongue',
            'highest_qualification' => 'Highest Qualification',
            'working_sector' => 'Working Sector',
            'profession' => 'Profession',
            'profession_details' => 'Professional Detail',
            'living_country' => 'Living Country',
            'state' => 'State',
            'city_living_in' => 'City Living In',
        ];

        // Compare preferences and create match details
        foreach ($partnerPreferences as $preferenceCriteria => $preferenceValue) {
            $displayName = $displayNames[$preferenceCriteria] ?? $preferenceCriteria;

            if (is_array($preferenceValue)) {
                $matches[] = [
                    'preference' => $displayName,
                    'required' => $preferenceValue,
                    'user_value' => $userArray[$preferenceCriteria] ?? null,
                    'match' => in_array(strtolower($userArray[$preferenceCriteria] ?? ''), array_map('strtolower', $preferenceValue))
                ];
            } else {
                $matches[] = [
                    'preference' => $displayName,
                    'required' => $preferenceValue,
                    'user_value' => $userArray[$preferenceCriteria] ?? null,
                    'match' => (strtolower($userArray[$preferenceCriteria] ?? '') === strtolower($preferenceValue))
                ];
            }
        }

        // Get similar profiles (make sure this method exists in your User model)
        $similar_profiles = $user->getSimilarProfiles(10);



        return response()->json([
            'user' => maskUserData($userArray,$authUser),
            'is_match' => $isMatch,
            'match_percentage' => $matchPercentage,
            'match_score' => $matchScore,
            'criteria_matches' => $matches,
            'similar_profiles' => $similar_profiles,
        ]);
    }


















    }
