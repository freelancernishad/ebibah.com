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



           // Age range from user input
    $ageRange = explode('-', $user->partner_age); // Assuming it's in the format "25-35"
    $minAge = (int)$ageRange[0];
    $maxAge = (int)$ageRange[1];

    // Calculate date range for age validation
    $currentDate = now();
    $minDateOfBirth = $currentDate->subYears($maxAge)->startOfDay();
    $maxDateOfBirth = $currentDate->subYears($minAge)->endOfDay();

    // Add age validation based on date_of_birth
    $query->whereBetween('date_of_birth', [$minDateOfBirth, $maxDateOfBirth]);









    // Initialize conditions for the SQL CASE statement
    $scoreConditions = [];

    // Check partner preferences using relationships
    if ($user->partnerMaritalStatuses) {
        $maritalStatuses = $user->partnerMaritalStatuses->pluck('status')->toArray();
        if (!empty($maritalStatuses)) {
            $query->whereIn('marital_status', $maritalStatuses);
            $scoreConditions[] = "1"; // Score for matching marital statuses
        }
    }

    if ($user->partnerReligions) {
        $religions = $user->partnerReligions->pluck('religion')->toArray();
        if (!empty($religions)) {
            $query->whereIn('religion', $religions);
            $scoreConditions[] = "1"; // Score for matching religions
        }
    }

    if ($user->partnerCommunities) {
        $communities = $user->partnerCommunities->pluck('community')->toArray();
        if (!empty($communities)) {
            $query->whereIn('community', $communities);
            $scoreConditions[] = "1"; // Score for matching communities
        }
    }

    if ($user->partnerMotherTongues) {
        $motherTongues = $user->partnerMotherTongues->pluck('mother_tongue')->toArray();
        if (!empty($motherTongues)) {
            $query->whereIn('mother_tongue', $motherTongues);
            $scoreConditions[] = "1"; // Score for matching mother tongues
        }
    }

    if ($user->partnerQualification) {
        $qualifications = $user->partnerQualification->pluck('qualification')->toArray();
        if (!empty($qualifications)) {
            $query->whereIn('highest_qualification', $qualifications);
            $scoreConditions[] = "1"; // Score for matching qualifications
        }
    }

    if ($user->partnerWorkingWith) {
        $workingSectors = $user->partnerWorkingWith->pluck('working_with')->toArray();
        if (!empty($workingSectors)) {
            $query->whereIn('working_sector', $workingSectors);
            $scoreConditions[] = "1"; // Score for matching working sectors
        }
    }

    if ($user->partnerProfessions) {
        $professions = $user->partnerProfessions->pluck('profession')->toArray();
        if (!empty($professions)) {
            $query->whereIn('profession', $professions);
            $scoreConditions[] = "1"; // Score for matching professions
        }
    }

    if ($user->partnerProfessionalDetails) {
        $professionalDetails = $user->partnerProfessionalDetails->pluck('detail')->toArray();
        if (!empty($professionalDetails)) {
            $query->whereIn('profession_details', $professionalDetails);
            $scoreConditions[] = "1"; // Score for matching professional details
        }
    }

    if ($user->partnerCountries) {
        $countries = $user->partnerCountries->pluck('country')->toArray();
        if (!empty($countries)) {
            $query->whereIn('living_country', $countries);
            $scoreConditions[] = "1"; // Score for matching countries
        }
    }

    if ($user->partnerStates) {
        $states = $user->partnerStates->pluck('state')->toArray();
        if (!empty($states)) {
            $query->whereIn('state', $states);
            $scoreConditions[] = "1"; // Score for matching states
        }
    }

    if ($user->partnerCities) {
        $cities = $user->partnerCities->pluck('city')->toArray();
        if (!empty($cities)) {
            $query->whereIn('city_living_in', $cities);
            $scoreConditions[] = "1"; // Score for matching cities
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
    return $matchingUsers;
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
            $partnerCountries = $user->partnerCountries->pluck('country')->toArray();
            $partnerStates = $user->partnerStates->pluck('state')->toArray();
            $partnerCities = $user->partnerCities->pluck('city')->toArray();

            $query->where(function ($subQuery) use ($partnerCountries, $partnerStates, $partnerCities) {
                if (!empty($partnerCountries)) {
                    $subQuery->whereIn('living_country', $partnerCountries);
                }
                if (!empty($partnerStates)) {
                    $subQuery->whereIn('state', $partnerStates);
                }
                if (!empty($partnerCities)) {
                    $subQuery->whereIn('city', $partnerCities);
                }
            });
            break;
    }
}
