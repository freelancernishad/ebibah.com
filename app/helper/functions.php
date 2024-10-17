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


    // if ($limit !== null) {
    //     $finalMatchingUsers = $finalMatchingUsers->take($limit);
    // }


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






    $result = applyMatchTypeFilters($result, $matchType, $user);


    // Define the fields to be displayed
$fields = [
    'id',
    'name',
    'age',
    'gender',
    'Height',
    'city_living_in',
    'currently_living_in',
    'living_country',
    'religion',
    'marital_status',
    'working_sector',
    'profession',
    'about_myself',
    'profile_picture_url',
    'invitation_send_status',
    'is_favorited',
];

// Map the result to only include the specified fields
$result = $result->map(function ($user) use ($fields) {
    return array_intersect_key($user, array_flip($fields));
});

// Apply the optional limit if provided
if ($limit !== null) {
    $result = $result->take($limit);
}



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
            // For 'my', we will select matched users randomly
            // Here you can adjust the number of random users you want to return
            $randomUsers = $users->shuffle()->take(10); // Change 10 to your desired limit
            $users = $randomUsers; // Return random users directly for 'my' match type
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


    \Log::info('User gender:', ['current_user_gender' => $user->gender]);
    \Log::info('Before gender filtering:', $users->pluck('gender')->toArray());



    if (strcasecmp(trim($user->gender), 'Male') === 0) {
        $users = $users->filter(function ($filteredUser) {
            // Check if the filtered user is an object or an array
            if (is_array($filteredUser)) {
                $gender = $filteredUser['gender'] ?? null;  // Access gender if it's an array
            } elseif ($filteredUser instanceof \Illuminate\Database\Eloquent\Model) {
                $gender = $filteredUser->gender;  // Access gender if it's an object
            } else {
                return false;  // If it's neither an array nor an Eloquent model, exclude the user
            }

            \Log::info('Filtering out Male user:', ['gender' => $gender]);

            return strcasecmp(trim($gender), 'Male') !== 0;  // Filter out Male users
        });
    } elseif (strcasecmp(trim($user->gender), 'Female') === 0) {
        $users = $users->filter(function ($filteredUser) {
            // Check if the filtered user is an object or an array
            if (is_array($filteredUser)) {
                $gender = $filteredUser['gender'] ?? null;  // Access gender if it's an array
            } elseif ($filteredUser instanceof \Illuminate\Database\Eloquent\Model) {
                $gender = $filteredUser->gender;  // Access gender if it's an object
            } else {
                return false;  // If it's neither an array nor an Eloquent model, exclude the user
            }

            \Log::info('Filtering out Female user:', ['gender' => $gender]);

            return strcasecmp(trim($gender), 'Female') !== 0;  // Filter out Female users
        });
    }


    \Log::info('After filtering:', $users->toArray()); // Log users after filtering




    // Return the filtered and sorted users
    return $users->values(); // Reset the array keys after filtering
}




function maskUserData($user)
{
    // Define the fields that should not be masked (visible fields)
    $visibleFields = [

    'id',

    'name',
    'active_package_id',
    'email',
    'password',
    'role',
    'role_id',
    'profile_for',
    'profile_created_by',
    'mobile_number',
    'whatsapp',
    'date_of_birth',
    'gender',
    'first_name',
    'last_name',
    'father_name',
    'mother_name',
    'marital_status',
    'religion',
    'community',
    'mother_tongue',
    'sub_community',
    'nationality',
    'highest_qualification',
    'college_name',
    'working_sector',
    'profession',
    'profession_details',
    'monthly_income',
    'father_occupation',
    'mother_occupation',
    'living_country',
    'currently_living_in',
    'city_living_in',
    'family_details',
    'family_values',
    'family_location',
    'family_type',
    'family_native_place',
    'total_siblings',
    'siblings_married',
    'siblings_not_married',
    'height',
    'birth_place',
    'personal_values',
    'disability',
    'posted_by',
    'weight',
    'bodyType',
    'race',
    'blood_group',
    'mother_status',
    'state',
    'about_myself',
    'partner_age',
    'username',
    'step',
    'smoking',
    'other_lifestyle_preferences',
    'drinking',
    'diet',
    'email_verification_hash',
    'status',
    'otp',
    'otp_expires_at',
    'is_favorited',
    'age',
    'profile_picture_url',
    'active_package',
    'invitation_send_status',
    'received_invitations_count',
    'accepted_invitations_count',
    'favorites_count',
    'profile_completion',
    'what_u_looking',



    'partner_marital_statuses',
    'partner_religions',
    'partner_communities',
    'partner_mother_tongues',
    'partner_qualification',
    'partner_working_with',
    'partner_professions',
    'partner_professional_details', //////
    'partner_countries',
    'partner_states',
    'partner_cities',
    'created_at',
    'trusted_badge_access',
    'premium_member_badge',


];

    // Define the mapping of masked array fields to their respective columns
    $maskedArrayFields = [
        'partner_marital_statuses' => 'marital_status',
        'partner_religions' => 'religion',
        'partner_communities' => 'community',
        'partner_mother_tongues' => 'mother_tongue',
        'partner_qualification' => 'partner_qualifications',
        'partner_working_with' => 'working_with',
        'partner_professions' => 'profession',
        'partner_professional_details' => 'profession',
        'partner_countries' => 'country',
        'partner_states' => 'state',
        'partner_cities' => 'city',
    ];

    $maskedUser = [];

    // Loop through user attributes
    foreach ($user as $key => $value) {
        if (in_array($key, $visibleFields)) {
            // Show specified fields
            $maskedUser[$key] = $value;
        } elseif (array_key_exists($key, $maskedArrayFields) && is_array($value) && !empty($value)) {
            // Mask only one entry for fields that are arrays of objects
            $maskedUser[$key] = [
                [
                    $maskedArrayFields[$key] => '****' // Mask the appropriate column for this field
                ]
            ];
        } else {
            // Mask other simple fields
            $maskedUser[$key] = '****';
        }
    }

    return $maskedUser;
}





function hasServiceAccess(string $serviceName, $user = null): bool
{
    // Use the passed user or the authenticated user if none is provided
    $user = $user ?? Auth::user();

    // If no user is authenticated or the user doesn't have an active package, deny access
    if (!$user || !isset($user->active_package) || !isset($user->active_package['allowed_services'])) {
        return false; // Access denied
    }

    // Check if the user's active package allows the specified service
    foreach ($user->active_package['allowed_services'] as $service) {
        if (isset($service['name'], $service['status']) && $service['name'] === $serviceName && $service['status'] === 'active') {
            return true; // Access granted
        }
    }

    return false; // Access denied
}




