<?php

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
    $query->where('gender', '!=', $user->gender)
          ->where('id', '!=', $user->id);

    // Initialize conditions for the SQL CASE statement
    $scoreConditions = [];
    $totalCriteria = 0;

    // Initialize the array to store matched fields
    $matchedUsersDetails = [];

    // Add other matching criteria checks
    addMatchingCriteria($query, $user, $scoreConditions, $totalCriteria, $matchedUsersDetails);




    // Retrieve all users that match other criteria
    $matchingUsers = $query->get();

    // Log initial matching users count
    \Log::info('Initial Matching Users Count: ', ['count' => $matchingUsers->count()]);

    // Create a filtered collection for users that meet at least 2 matching criteria
    $finalMatchingUsers = $matchingUsers->filter(function ($matchingUser) use ($matchedUsersDetails) {
        // Ensure that the user has at least 2 matching fields
        return isset($matchedUsersDetails[$matchingUser->id]) && count($matchedUsersDetails[$matchingUser->id]) >= 2;
    });

    // Log matched users
    \Log::info('Final Matching Users Count: ', ['count' => $finalMatchingUsers->count()]);

    // Log details of matched users
    foreach ($finalMatchingUsers as $matchingUser) {
        \Log::info('Matched User:', ['user_id' => $matchingUser->id]);
    }

    // Attach matched fields to the final matching users and remove the "user" key
    $result = $finalMatchingUsers->map(function ($user) use ($matchedUsersDetails) {
        return array_merge(
            $user->toArray(), // Merge the user's attributes directly
            ['matched_fields' => $matchedUsersDetails[$user->id] ?? []] // Add matched fields
        );
    })->values(); // Use values() to remove numeric keys

    // Apply additional filters based on the type of match requested
    applyMatchTypeFilters($result, $matchType, $user);

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



function applyMatchTypeFilters($finalMatchingUsers, $matchType, $user)
{
    // Additional filters based on match type can be added here
    if ($matchType) {
        // Example logic for match type filtering
        switch ($matchType) {
            case 'preferred':
                // Apply preferred match type logic here
                break;
            case 'strict':
                // Apply strict match type logic here
                break;
            default:
                break;
        }
    }
}





