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

        // Only match users of the opposite gender
        $query->where('gender', '!=', $user->gender);

        // Exclude the authenticated user from the result set
        $query->where('id', '!=', $user->id);

        // Define partner preferences with possible multiple values
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

        // Initialize arrays for the SQL CASE statement and bindings
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
            return response()->json(['message' => 'No valid matching criteria'], 400);
        }


        // Add matching conditions based on user's partner preferences
        $totalCriteria = count($scoreConditions);
        $query->selectRaw('
            users.*,
            (
                ' . implode(' + ', $scoreConditions) . '
            ) as match_score
        ', $bindings);

        // Calculate the match score threshold as 20% of the total number of scoring criteria
        $matchThreshold = ceil($totalCriteria * 0.2);

        // Filter users who have a match score of 20% or higher
        $query->having('match_score', '>=', $matchThreshold);

        // Additional filters based on the type of match requested
        switch ($matchType) {
            case 'new':
                // Filter for new matches (assuming there's a 'created_at' or similar column)
                $query->whereDate('created_at', '=', now()->toDateString());
                break;

            case 'today':
                // Filter for today's matches (assuming there's amatched_at or similar column)
                $query->whereDate('matched_at', '=', now()->toDateString()); break;
                case 'my':
                    // Filter for matches the user has already matched with (assuming a pivot table or similar)
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
                        $subQuery->where(function ($q) use ($partnerCountry) {
                            if (!empty($partnerCountry)) {
                                $q->where('living_country', $partnerCountry);
                            }
                        })
                        ->where(function ($q) use ($partnerState) {
                            if (!empty($partnerState)) {
                                $q->where('state', $partnerState);
                            }
                        })
                        ->where(function ($q) use ($partnerCity) {
                            if (!empty($partnerCity)) {
                                $q->where('city_living_in', $partnerCity);
                            }
                        });
                    });
                    break;
            }

            // Order the results by the highest match score first
            $query->orderByDesc('match_score');

            // Execute the query and get the results
            $matchingUsers = $query->get();

            // Calculate and include the percentage for each user
            $matchingUsers = $matchingUsers->map(function ($user) use ($totalCriteria) {
                $user->match_percentage = ($user->match_score / $totalCriteria) * 100;
                return $user;
            });

            // Return the matching users as a JSON response, including the match_percentage
            return response()->json($matchingUsers);
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

        // Find the user by username
        $user = User::find($id);

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

        // If no preferences are defined, return early
        // if (empty($scoreConditions)) {
        //     return response()->json(['message' => 'No preferences found for the authenticated user'], 400);
        // }

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
        $matchPercentage = ($matchScoreQuery->match_score / $totalCriteria) * 100;

        // Check if the single user meets the match threshold
        $isMatch = $matchScoreQuery->match_score >= $matchThreshold;

        // Now determine which criteria matched and which did not
        foreach ($partnerPreferences as $column => $value) {
            if (is_array($value)) {
                // Handle array preference values
                $matches[] = [
                    'preference' => $column,
                    'required' => $value,
                    'user_value' => $user->{$column},
                    'match' => in_array($user->{$column}, $value)
                ];
            } else {
                // Handle single value preferences
                $matches[] = [
                    'preference' => $column,
                    'required' => $value,
                    'user_value' => $user->{$column},
                    'match' => ($user->{$column} == $value)
                ];
            }
        }

        // Return the user and match details as a JSON response
        return response()->json([
            'user' => $user,
            'is_match' => $isMatch,
            'match_percentage' => $matchPercentage,
            'match_score' => $matchScoreQuery->match_score,
            'criteria_matches' => $matches  // Return detailed matching info
        ]);
    }









    }
