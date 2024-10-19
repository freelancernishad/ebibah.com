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



       $matchType = $request->input('type'); // e.g., 'new', 'today', 'my', 'near'

       $matchingUsers = profile_matches($matchType);
        // Return the matching users as a JSON response, including the match_percentage
        return response()->json([
            'status' => 'success',
            'data' => $matchingUsers,
        ]);
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

        return $userArray;
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
