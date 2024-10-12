<?php

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

function int_en_to_bn($number)
{

    $bn_digits = array('০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯');
    $en_digits = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');

    return str_replace($en_digits, $bn_digits, $number);
}
function int_bn_to_en($number)
{

    $bn_digits = array('০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯');
    $en_digits = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');

    return str_replace($bn_digits, $en_digits, $number);
}

function month_number_en_to_bn_text($number)
{
    $en = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12);
    $bn = array('জানুয়ারি', 'ফেব্রুয়ারি', 'মার্চ', 'এপ্রিল', 'মে', 'জুন', 'জুলাই', 'অগাস্ট', 'সেপ্টেম্বর', 'অক্টোবর', 'নভেম্বর', 'ডিসেম্বর');

    // Adjust the number to be within 1-12 range
    $number = max(1, min(12, $number));

    return str_replace($en, $bn, $number);
}

function month_name_en_to_bn_text($name)
{
    $en = array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
    $bn = array('জানুয়ারি', 'ফেব্রুয়ারি', 'মার্চ', 'এপ্রিল', 'মে', 'জুন', 'জুলাই', 'অগাস্ট', 'সেপ্টেম্বর', 'অক্টোবর', 'নভেম্বর', 'ডিসেম্বর');
    return str_replace($en, $bn, $name);
}

 function extractUrlFromIframe($iframe)
{
    $dom = new \DOMDocument();
    @$dom->loadHTML($iframe);

    $iframes = $dom->getElementsByTagName('iframe');
    if ($iframes->length > 0) {
        $src = $iframes->item(0)->getAttribute('src');
        return $src;
    }

    return $iframe;
}


function routeUsesMiddleware($route, $middlewareName)
{
   return $middlewares = $route->gatherMiddleware();

    foreach ($middlewares as $middleware) {
        if (preg_match("/^$middlewareName:/", $middleware)) {
            return true;
        }
    }

    return false;
}

function generateCustomS3Url($path)
{
    // Generate the URL to the file on S3
    $url = Storage::disk('s3')->url($path);

    // Replace the default S3 URL with your custom domain
    // $url = str_replace('usa-marry-bucket.s3.us-west-1.amazonaws.com', 'media.usamarry.com', $url);

    return $url;
}


function jsonResponse($success, $message, $data = null, $statusCode = 200, array $extraFields = [])
{
    // Build the base response structure
    $response = [
        'success' => $success,
        'message' => $message,
        'data' => $data
    ];

    // Merge any extra fields into the response
    if (!empty($extraFields)) {
        $response = array_merge($response, $extraFields);
    }

    // Return the JSON response with the given status code
    return response()->json($response, $statusCode);
}




function profile_matches($type = '', $limit = null)
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
    $matchType = $type;



    // Only match users of the opposite gender and exclude the authenticated user
    $query->where('gender', '!==', $user->gender)
    ->where('id', '!==', $user->id);

    // Initialize conditions for the SQL CASE statement
    $scoreConditions = [];
    $totalCriteria = 0;

    // Initialize the array to store matched fields
    $matchedUsersDetails = [];

    // Define minAge and maxAge to avoid undefined variable errors
    $minAge = null;
    $maxAge = null;

    // Add other matching criteria checks
    addMatchingCriteria($query, $user, $scoreConditions, $totalCriteria, $matchedUsersDetails);


    // Filter by partner_age to match date_of_birth
    if (!empty($user->partner_age)) {
        $partnerAge = explode('-', $user->partner_age); // Assuming 'partner_age' is a range like '25-30'
        $minAge = isset($partnerAge[0]) ? (int)$partnerAge[0] : null;
        $maxAge = isset($partnerAge[1]) ? (int)$partnerAge[1] : null;

        $query->where(function ($subQuery) use ($minAge, $maxAge) {
            $subQuery->whereNotNull('date_of_birth'); // Only include users with a date of birth
            if ($minAge !== null) {
                $subQuery->whereRaw('TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) >= ?', [$minAge]);
            }
            if ($maxAge !== null) {
                $subQuery->whereRaw('TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) <= ?', [$maxAge]);
            }
        });
    }




    // Retrieve all users that match other criteria
    $matchingUsers = $query->get();
    // Log the SQL and bindings
    \Log::info($query->toSql());
    \Log::info($query->getBindings());
      // Apply additional filters based on the type of match requested


    // Log initial matching users count
    \Log::info('Initial Matching Users Count: ', ['count' => $matchingUsers->count()]);

    // Create a filtered collection for users that meet at least 2 matching criteria
    $finalMatchingUsers = $matchingUsers->filter(function ($matchingUser) use ($matchedUsersDetails, $minAge, $maxAge) {
        // Calculate age from date_of_birth for the matched user
        $age = \Carbon\Carbon::parse($matchingUser->date_of_birth)->age;

        // Ensure that the user has at least 2 matching fields and their age is within the partner age range
        return isset($matchedUsersDetails[$matchingUser->id]) &&
               count($matchedUsersDetails[$matchingUser->id]) >= 2 &&
               ($age >= $minAge && $age <= $maxAge); // Ensure age is in the range
    });

    // Log matched users
    \Log::info('Final Matching Users Count: ', ['count' => $finalMatchingUsers->count()]);

    // Log details of matched users
    foreach ($finalMatchingUsers as $matchingUser) {
        \Log::info('Matched User:', ['user_id' => $matchingUser->id]);
    }

    // Attach matched fields to the final matching users and remove the "user" key
    $result = $finalMatchingUsers->map(function ($matchedUser) use ($matchedUsersDetails, $user, $minAge, $maxAge) {
        // Prepare matched fields for partner age
        $partnerAgeMatch = [
            "field" => "partner_age",
            "auth_user_preference" => explode('-', $user->partner_age), // Assuming 'partner_age' is a range like '25-30'
            "matched_user_value" => \Carbon\Carbon::parse($matchedUser->date_of_birth)->age, // Age of the matched user
            "is_matched" => (\Carbon\Carbon::parse($matchedUser->date_of_birth)->age >= $minAge &&
                             \Carbon\Carbon::parse($matchedUser->date_of_birth)->age <= $maxAge) // True/False if age is within range
        ];

        // Add partner age match to matchedUsersDetails
        if (!isset($matchedUsersDetails[$matchedUser->id])) {
            $matchedUsersDetails[$matchedUser->id] = [];
        }
        $matchedUsersDetails[$matchedUser->id][] = $partnerAgeMatch;

        return array_merge(
            $matchedUser->toArray(), // Merge the user's attributes directly
            [
                'matched_fields' => $matchedUsersDetails[$matchedUser->id] // Attach the matched fields
            ]
        );
    })->values(); // Use values() to remove numeric keys



    // Apply the optional limit if provided
    if ($limit !== null) {
        $result = $result->take($limit);
    }


    $result = applyMatchTypeFilters($result, $matchType, $user);

    // Return the final matching users as a JSON response
    return $result;
}




function addMatchingCriteria($query, $user, &$scoreConditions, &$totalCriteria, &$matchedUsersDetails)
{
    // Map user relationships to corresponding columns in other users
    $criteriaMappings = [
        'partnerMaritalStatuses' => 'marital_status',
        'partnerReligions' => 'religion',
        'partnerCommunities' => 'community',
        'partnerMotherTongues' => 'mother_tongue',
        'partnerQualification' => 'qualification',
        'partnerWorkingWith' => 'working_with',
        'partnerProfessions' => 'profession',
        'partnerProfessionalDetails' => 'profession',
        'partnerCountries' => 'country',
        'partnerStates' => 'currently_living_in',
        'partnerCities' => 'city',
    ];

    foreach ($criteriaMappings as $relation => $column) {
        if ($user->$relation) {
            // Retrieve the values from the authenticated user's relationship
            $userValues = $user->$relation->pluck($column)->toArray();

            if (!empty($userValues)) {


                    // Initialize the user column mapping
                    $userColumn = $column;

                    // Adjust column names for specific mappings
                    if ($column === 'qualification') {
                        $userColumn = 'highest_qualification';
                    } elseif ($column === 'working_with') {
                        $userColumn = 'working_sector';
                    } elseif ($column === 'country') {
                        $userColumn = 'living_country';
                    } elseif ($column === 'city') {
                        $userColumn = 'city_living_in';
                    } elseif ($column === 'profession') {
                        if($relation=='partnerProfessionalDetails'){
                            $userColumn = 'profession_details';
                        }
                    }


                // Apply the query condition using where or orWhere for at least one match
                $query->orWhere(function ($q) use ($userColumn, $userValues, &$matchedUsersDetails, $user) {
                    foreach ($userValues as $value) {
                        $q->orWhere($userColumn, $value);
                    }
                });

                // Increment the total criteria count
                $scoreConditions[] = "1"; // Score for matching criteria
                $totalCriteria++; // Increment total criteria count

                // Retrieve other users' values from the same column
                $query->get()->each(function ($matchingUser) use ($userColumn, $userValues, &$matchedUsersDetails, $user, $relation) {
                    // Check if the other user's value matches the authenticated user's preference
                    $otherUserValue = $matchingUser->$userColumn;

                    // Only add details if there's a match
                    if (in_array($otherUserValue, $userValues)) {
                        // Log the matched field and values
                        $matchedUsersDetails[$matchingUser->id][] = [
                            'field' => $userColumn,
                            'auth_user_preference' => $userValues, // The values from the authenticated user's preferences
                            'matched_user_value' => $otherUserValue, // The value from the other user's profile
                            'matched' => true, // Indicate that it matched
                        ];

                        // Log the matched criteria
                        \Log::info("Matched " . ucfirst($relation) . ":", [
                            'auth_user_id' => $user->id,
                            'auth_user_values' => $userValues, // Log the user's preference values
                            'other_user_id' => $matchingUser->id,
                            'other_user_value' => $otherUserValue, // Log the matched value
                            'column' => $userColumn
                        ]);
                    }
                });
            } else {
                // Log unmatched criteria with user values
                \Log::info("No preferences set for " . ucfirst($relation) . ":", [
                    'user_id' => $user->id,
                    'column' => $column
                ]);
            }
        }
    }
}





function applyMatchTypeFilters($users, $matchType, $user)
{
    // Convert the users collection to a Laravel Collection if it's not already
    $users = collect($users);

    // Eager load the partner-related relationships to avoid N+1 problems
    $user->load(['partnerCountries', 'partnerStates', 'partnerCities']);

    switch ($matchType) {
        case 'new':
            // Sort users by created_at in descending order, ensuring the items are models
            $users = $users->sortByDesc(function ($user) {
                return $user instanceof \Illuminate\Database\Eloquent\Model ? $user->created_at : null;
            });
            break;

        case 'today':
            // Filter users created today
            $users = $users->filter(function ($user) {
                return $user instanceof \Illuminate\Database\Eloquent\Model && Carbon::today()->isSameDay($user->created_at);
            });
            break;

        case 'my':
            // For 'my', we will not use previously matched users or an ID check,
            // but instead rely entirely on the calculated match score from the preferences.
            // No additional filters are needed; the match score logic is already in place.
            break;

        case 'near':
            // Access partner's location attributes from related models
            $partnerCountries = $user->partnerCountries->pluck('country')->toArray();
            $partnerStates = $user->partnerStates->pluck('state')->toArray();
            $partnerCities = $user->partnerCities->pluck('city')->toArray();

            // Filter users based on location
            $users = $users->filter(function ($user) use ($partnerCountries, $partnerStates, $partnerCities) {
                if (!($user instanceof \Illuminate\Database\Eloquent\Model)) {
                    return false; // Ignore if not an Eloquent model
                }

                $matchesCountry = empty($partnerCountries) || in_array($user->living_country, $partnerCountries);
                $matchesState = empty($partnerStates) || in_array($user->state, $partnerStates);
                $matchesCity = empty($partnerCities) || in_array($user->city_living_in, $partnerCities);

                return $matchesCountry && $matchesState && $matchesCity;
            });
            break;

        default:
            // Handle unknown match types gracefully
            \Log::warning('Unknown match type: ' . $matchType);
            break;
    }


    // Apply gender filter: Exclude users with the same gender as the current user
    $excludedGender = $user->gender; // Get the gender of the current user (e.g., 'Male' or 'Female')

    // Filter out users with the same gender and exclude the current user
//    return $users = $users->filter(function ($filteredUser) use ($excludedGender, $user) {
//         return $filteredUser instanceof \Illuminate\Database\Eloquent\Model;
//             // && $filteredUser->gender !== $excludedGender;
//             // && $filteredUser->id !== $user->id;
//     });



    // Return the filtered and sorted users
    return $users->values(); // Reset the array keys after filtering
}










