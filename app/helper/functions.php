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
    $matchType = $type; // e.g., 'new', 'today', 'my', 'near'

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
        return []; // No matches found
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
    applyMatchTypeFilters($query, $matchType, $user);

    // Order the results by the highest match score first
    $query->orderByDesc('match_score');

    // Include the relationships to load the necessary data
    $query->with(['partnerQualifications', 'partnerWorkingWith', 'partnerProfessions']);

    // Execute the query and get the results
    $matchingUsers = $query->get();

    // Apply the optional limit if provided
    if ($limit !== null) {
        $matchingUsers = $matchingUsers->take($limit);
    }

    // Calculate and include the percentage for each user
    $matchingUsers->transform(function ($matchingUser) use ($totalCriteria) {
        // Match percentage calculation (ensure division by the correct totalCriteria)
        $matchingUser->match_percentage = ($matchingUser->match_score / $totalCriteria) * 100;
        return $matchingUser;
    });

    // Return the matching users as a JSON response, including the match_percentage
    return response()->json(['status' => 'success', 'data' => $matchingUsers], 200);
}




 function applyMatchTypeFilters($query, $matchType, $user)
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
