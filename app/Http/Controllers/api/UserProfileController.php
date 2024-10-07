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
        // Get the authenticated user
        $user = Auth::user();

        // Check if the user is authenticated
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        // Start the query with the User model
        $query = User::query();

        // Filter based on requested type
        $matchType = $request->input('type'); // e.g., 'new', 'today', 'my', 'near'

        // Only match users of the opposite gender and exclude the authenticated user
        $query->where('gender', '!=', $user->gender)
              ->where('id', '!=', $user->id);

        // Define partner preferences
        $partnerPreferences = [
            'marital_status' => $user->partner_marital_status,
            'religion' => $user->partner_religion,
            'community' => $user->partner_community,
            'mother_tongue' => $user->partner_mother_tongue,
            'living_country' => $user->partner_country,
            'state' => $user->partner_state,
            'city_living_in' => $user->partner_city,
        ];

        // Initialize conditions for the SQL CASE statement
        $scoreConditions = [];
        $bindings = [];

        // Loop through each preference and build conditions
        foreach ($partnerPreferences as $column => $value) {
            if (!empty($value)) {
                $query->where($column, $value);
                $scoreConditions[] = "1"; // Assigning 1 for score if condition matches
            }
        }

        // Include preferences from the relationships
        if ($user->partnerQualifications) {
            $qualifications = $user->partnerQualifications->pluck('qualification')->toArray();
            if (!empty($qualifications)) {
                $query->whereIn('partner_qualifications.qualification', $qualifications);
                $scoreConditions[] = "1"; // Score for matching qualifications
            }
        }

        if ($user->partnerWorkingWith) {
            $workingSectors = $user->partnerWorkingWith->pluck('working_with')->toArray();
            if (!empty($workingSectors)) {
                $query->whereIn('partner_working_with.working_with', $workingSectors);
                $scoreConditions[] = "1"; // Score for matching working sectors
            }
        }

        if ($user->partnerProfessions) {
            $professions = $user->partnerProfessions->pluck('profession')->toArray();
            if (!empty($professions)) {
                $query->whereIn('partner_professions.profession', $professions);
                $scoreConditions[] = "1"; // Score for matching professions
            }
        }

        // Check if score conditions are available
        if (empty($scoreConditions)) {
            // Return early if there are no valid matching criteria
            return response()->json([
                'status' => 'success',
                'data' => [], // No matches found
            ]);
        }

        // Add matching conditions based on user's partner preferences
        $totalCriteria = count($scoreConditions); // This should reflect the actual number of conditions
        $query->selectRaw('
            users.*,
            (' . implode(' + ', $scoreConditions) . ') as match_score
        ');

        // Calculate the match score threshold as 20% of the total number of scoring criteria
        $matchThreshold = ceil($totalCriteria * 0.2);
        $query->having('match_score', '>=', $matchThreshold);

        // Apply additional filters based on the type of match requested
        $this->applyMatchTypeFilters($query, $matchType, $user);

        // Order the results by the highest match score first
        $query->orderByDesc('match_score');

        // Include the relationships to load the necessary data
        $query->with(['partnerQualifications', 'partnerWorkingWith', 'partnerProfessions']);

        // Execute the query and get the results
        $matchingUsers = $query->get();

        // Calculate and include the percentage for each user
        $matchingUsers->transform(function ($matchingUser) use ($totalCriteria) {
            // Match percentage calculation (ensure division by the correct totalCriteria)
            $matchingUser->match_percentage = ($matchingUser->match_score / $totalCriteria) * 100;
            return $matchingUser;
        });

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
    private function applyMatchTypeFilters($query, $matchType, $user)
    {
        switch ($matchType) {
            case 'new':
                // Filter for new users based on their creation date
                $query->orderBy('created_at', 'desc');
                break;

            case 'today':
                // Filter for users who were created today
                $query->whereDate('created_at', now()->toDateString());
                break;

            case 'my':
                // For 'my', we will not use previously matched users or an ID check,
                // but instead rely entirely on the calculated match score from the preferences.
                // No additional filters are needed, the match score logic is already in place.
                break;

            case 'near':
                // Filter based on location attributes of the user and the potential matches
                $partnerCountry = $user->partner_country;
                $partnerState = $user->partner_state;
                $partnerCity = $user->partner_city;

                $query->where(function ($subQuery) use ($partnerCountry, $partnerState, $partnerCity) {
                    if (!empty($partnerCountry)) {
                        $subQuery->where('living_country', $partnerCountry);
                    }
                    if (!empty($partnerState)) {
                        $subQuery->where('state', $partnerState);
                    }
                    if (!empty($partnerCity)) {
                        $subQuery->where('city_living_in', $partnerCity);
                    }
                });
                break;
        }
    }




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

        // Find the user by id with the related images
        $user = User::with('userImages')->find($id);



        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Define the authenticated user's partner preferences
        $partnerPreferences = [
            'marital_status' => $authUser->partner_marital_status,
            'religion' => $authUser->partner_religion,
            'community' => $authUser->partner_community,
            'mother_tongue' => $authUser->partner_mother_tongue,
            'highest_qualification' => $authUser->partnerQualification ? $authUser->partnerQualification->pluck('qualification')->toArray() : [],
            'working_sector' => $authUser->partnerWorkingWith ? $authUser->partnerWorkingWith->pluck('sector')->toArray() : [],
            'profession' => $authUser->partnerProfession ? $authUser->partnerProfession->pluck('profession')->toArray() : [],
            'living_country' => $authUser->partner_country,
            'state' => $authUser->partner_state,
            'city_living_in' => $authUser->partner_city,
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
                'user' => $user,
                'is_match' => false,
                'match_percentage' => 0,
                'match_score' => 0,
                'criteria_matches' => [],
                'similar_profiles' => [],
            ]);
        }

        $totalCriteria = count($scoreConditions);
        $matchScoreQuery = DB::table('users')
            ->selectRaw(
                'users.*, (' . implode(' + ', $scoreConditions) . ') as match_score',
                $bindings
            )
            ->where('users.id', $user->id)
            ->first();

        $matchScore = $matchScoreQuery->match_score;
        $matchPercentage = ($matchScore / $totalCriteria) * 100;

        $matchThreshold = ceil($totalCriteria * 0.2);
        $isMatch = $matchScore >= $matchThreshold;

        $displayNames = [
            'marital_status' => 'Marital Status',
            'religion' => 'Religion',
            'community' => 'Community',
            'mother_tongue' => 'Mother Tongue',
            'highest_qualification' => 'Highest Qualification',
            'working_sector' => 'Working Sector',
            'profession' => 'Profession',
            'living_country' => 'Living Country',
            'state' => 'State',
            'city_living_in' => 'City Living In',
        ];

        foreach ($partnerPreferences as $preferenceCriteria => $preferenceValue) {
            $displayName = $displayNames[$preferenceCriteria] ?? $preferenceCriteria;

            if (is_array($preferenceValue)) {
                $matches[] = [
                    'preference' => $displayName,
                    'required' => $preferenceValue,
                    'user_value' => $user->{$preferenceCriteria},
                    'match' => in_array(strtolower($user->{$preferenceCriteria}), array_map('strtolower', $preferenceValue))
                ];
            } else {
                $matches[] = [
                    'preference' => $displayName,
                    'required' => $preferenceValue,
                    'user_value' => $user->{$preferenceCriteria},
                    'match' => (strtolower($user->{$preferenceCriteria}) === strtolower($preferenceValue))
                ];
            }
        }

        // Unset specific relationships


        // Hide specific attributes

        // Get similar profiles
        $similar_profiles = $user->getSimilarProfiles(10);

        return response()->json([
            'user' => $user,
            'is_match' => $isMatch,
            'match_percentage' => $matchPercentage,
            'match_score' => $matchScore,
            'criteria_matches' => $matches,
            'similar_profiles' => $similar_profiles,
        ]);
    }
















    }
