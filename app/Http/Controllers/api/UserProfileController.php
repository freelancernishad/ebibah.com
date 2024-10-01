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
            'highest_qualification' => $user->partner_qualification,
            'working_sector' => $user->partner_working_with,
            'profession' => $user->partner_profession,
            'living_country' => $user->partner_country,
            'state' => $user->partner_state,
            'city_living_in' => $user->partner_city,
        ];

        // Initialize conditions for the SQL CASE statement
        $scoreConditions = [];
        $bindings = [];

        foreach ($partnerPreferences as $column => $value) {
            if (is_array($value) && !empty($value)) {
                // Use an IN clause if the value is an array
                $placeholders = implode(',', array_fill(0, count($value), '?'));
                $scoreConditions[] = "(CASE WHEN $column IN ($placeholders) THEN 1 ELSE 0 END)";
                $bindings = array_merge($bindings, $value);
            } elseif (!empty($value)) {
                // Use a simple comparison if the value is a single value
                $scoreConditions[] = "(CASE WHEN $column = ? THEN 1 ELSE 0 END)";
                $bindings[] = $value;
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
        $totalCriteria = count($scoreConditions);
        $query->selectRaw('
            users.*,
            (' . implode(' + ', $scoreConditions) . ') as match_score
        ', $bindings);

        // Calculate the match score threshold as 20% of the total number of scoring criteria
        $matchThreshold = ceil($totalCriteria * 0.2);
        $query->having('match_score', '>=', $matchThreshold);

        // Apply additional filters based on the type of match requested
        $this->applyMatchTypeFilters($query, $matchType, $user);

        // Order the results by the highest match score first
        $query->orderByDesc('match_score');

        // Execute the query and get the results
        $matchingUsers = $query->get();

        // Calculate and include the percentage for each user
        $matchingUsers->transform(function ($user) use ($totalCriteria) {
            $user->match_percentage = ($user->match_score / $totalCriteria) * 100;
            return $user;
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
                // Filter for new matches by sorting by created_at descending and limit the results
                $query->orderBy('created_at', 'desc');
                break;

            case 'today':
                // Filter for today's matches (assuming there's a 'matched_at' column)
                $query->whereDate('created_at', now()->toDateString());
                break;

            case 'my':
                // Filter for matches the user has already matched with
                $query->whereIn('id', function ($subQuery) use ($user) {
                    $subQuery->select('matched_user_id')
                        ->from('matches')
                        ->where('user_id', $user->id);
                });
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

        // Find the user by id
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
            'highest_qualification' => $authUser->partner_qualification,
            'working_sector' => $authUser->partner_working_with,
            'profession' => $authUser->partner_profession,
            'living_country' => $authUser->partner_country,
            'state' => $authUser->partner_state,
            'city_living_in' => $authUser->partner_city,
        ];

        // Initialize arrays for SQL CASE statements and bindings
        $scoreConditions = [];
        $bindings = [];
        $matches = [];  // This will store details of which criteria matched and which didn't

        foreach ($partnerPreferences as $column => $value) {
            // Skip the condition if the value is null or an empty array
            if (!isset($value) || (is_array($value) && empty($value))) {
                continue; // Skip to the next iteration if the value is null or an empty array
            }

            if (is_array($value)) {
                // Use an IN clause if the value is an array
                $placeholders = implode(',', array_fill(0, count($value), '?'));
                $scoreConditions[] = "(CASE WHEN $column IN ($placeholders) THEN 1 ELSE 0 END)";
                $bindings = array_merge($bindings, $value);
            } else {
                // Use a simple comparison if the value is a single value
                $scoreConditions[] = "(CASE WHEN $column = ? THEN 1 ELSE 0 END)";
                $bindings[] = $value;
            }
        }

        // If no score conditions are generated, set match score to 0
        if (empty($scoreConditions)) {
            $matchScore = 0;
            $matchPercentage = 0;
            $isMatch = false;
        } else {
            // Add match score calculations for the single user
            $totalCriteria = count($scoreConditions);
            $matchScoreQuery = DB::table('users')
                ->selectRaw(
                    'users.*,
                    (
                        ' . implode(' + ', $scoreConditions) . '
                    ) as match_score',
                    $bindings
                )
                ->where('users.id', $user->id) // Match only the user with the given username
                ->first();

            // Calculate the match score threshold as 20% of the total number of scoring criteria
            $matchThreshold = ceil($totalCriteria * 0.2);

            // Calculate match percentage
            $matchScore = $matchScoreQuery->match_score;
            $matchPercentage = ($matchScore / $totalCriteria) * 100;

            // Check if the single user meets the match threshold
            $isMatch = $matchScore >= $matchThreshold;
        }

// Define a mapping of column names to meaningful display names
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

// Now determine which criteria matched and which did not
foreach ($partnerPreferences as $preferenceCriteria => $preferenceValue) {
    $displayName = $displayNames[$preferenceCriteria] ?? $preferenceCriteria; // Default to the original key if not found

    if (is_array($preferenceValue)) {
        // Handle array preference values
        $matches[] = [
            'preference' => $displayName, // Use the display name
            'required' => $preferenceValue,
            'user_value' => $user->{$preferenceCriteria},
            'match' => in_array($user->{$preferenceCriteria}, $preferenceValue)
        ];
    } else {
        // Handle single value preferences
        $matches[] = [
            'preference' => $displayName, // Use the display name
            'required' => $preferenceValue,
            'user_value' => $user->{$preferenceCriteria},
            'match' => ($user->{$preferenceCriteria} == $preferenceValue)
        ];
    }
}


        // Hide specific relations by unsetting them
        $user->unsetRelation('sentInvitations');
        $user->unsetRelation('receivedInvitations');
        $user->unsetRelation('profileViews');
        $user->unsetRelation('payments');

        // Return the user and match details as a JSON response
        return response()->json([
            'user' => $user,
            'is_match' => $isMatch,
            'match_percentage' => $matchPercentage,
            'match_score' => $matchScore,
            'criteria_matches' => $matches  // Return detailed matching info
        ]);
    }











    }
