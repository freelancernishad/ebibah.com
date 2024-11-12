<?php

use Carbon\Carbon;
use App\Models\User;
use App\Models\Package;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
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

    // Determine gender to match against based on authenticated user's gender
    if ($user->gender === 'Male') {
        // If authenticated user is Male, show Female users
        $query->where('gender', 'Female');
    } elseif ($user->gender === 'Female') {
        // If authenticated user is Female, show Male users
        $query->where('gender', 'Male');
    }

    // Exclude the authenticated user
    $query->where('id', '!=', $user->id);

    // Initialize criteria count and details arrays
    $matchedUsersDetails = [];

    // Add matching criteria checks
    addMatchingCriteria($query, $user, $matchedUsersDetails);

    // Add age filtering
    filterByAge($query, $user);

    // Get matched users
    $matchingUsers = $query->get();


    // Filter matching users based on gende
    $matchingUsers = $matchingUsers->filter(function ($matchedUser) use ($user) {
        return $user->gender === 'Male' ? $matchedUser->gender === 'Female' : $matchedUser->gender === 'Male';
    });
    // Final filtering based on match type
    $finalMatchingUsers = filterFinalMatches($matchingUsers, $user, $type);

    // Attach criteria matched details to each user
    $finalMatchingUsers->each(function ($matchedUser) use (&$matchedUsersDetails) {
        if (isset($matchedUsersDetails[$matchedUser->id])) {
            $matchedUser->totalCriteriaMatched = $matchedUsersDetails[$matchedUser->id]['criteria_matched'];
            $matchedUser->matched_fields = $matchedUsersDetails[$matchedUser->id]['fields'];
        } else {
            $matchedUser->totalCriteriaMatched = 0;
            $matchedUser->matched_fields = [];
        }
    });

    if($type=='new'){

    }else{

        $finalMatchingUsers = $finalMatchingUsers->sortByDesc('totalCriteriaMatched');
    }

    // Apply the optional limit if provided
    if ($limit !== null) {
        $finalMatchingUsers = $finalMatchingUsers->take($limit);
    }

    // Return the final matching users as a JSON response
    return prepareResponse($finalMatchingUsers, $limit,$type);
}


function addMatchingCriteria($query, $user, &$matchedUsersDetails)
{
    $authUserId = $user->id;

    // Pre-fetch users to improve performance, especially if there's a large dataset
    $query->get()->each(function ($matchingUser) use ($authUserId, &$matchedUsersDetails) {
        $userId = $matchingUser->id;

        // Fetch matching criteria for the authenticated and target user
        $criteriaMatches = getCriteriaMatches($authUserId, $userId);

        // Initialize criteria count and fields if no matches yet
        $matchedUsersDetails[$userId] = [
            'criteria_matched' => 0,
            'fields' => [],
        ];

        // Count and store each matched field
        foreach ($criteriaMatches as $match) {
            if ($match['match']) {
                $matchedUsersDetails[$userId]['criteria_matched']++; // Increment match count
                $matchedUsersDetails[$userId]['fields'][] = [
                    'field' => $match['preference'],
                    'auth_user_preference' => $match['required'],
                    'matched_user_value' => $match['user_value'],
                    'matched' => $match['match'],
                ];
            }
        }
    });
}


function filterByAge($query, $user)
{
    if (!empty($user->partner_age)) {
        [$minAge, $maxAge] = explode('-', $user->partner_age);

        $query->where(function ($subQuery) use ($minAge, $maxAge) {
            $subQuery->whereNotNull('date_of_birth');
            if ($minAge !== null) {
                $subQuery->whereRaw('TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) >= ?', [$minAge]);
            }
            if ($maxAge !== null) {
                $subQuery->whereRaw('TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) <= ?', [$maxAge]);
            }
        });
    }
}
function filterFinalMatches($matchingUsers, $user, $matchType)
{
    switch ($matchType) {
        case 'new':
            // Sort by creation date if the type is 'new'
            $matchingUsers = $matchingUsers->sortByDesc(function ($user) {
                return $user instanceof \Illuminate\Database\Eloquent\Model ? $user->created_at : null;
            });
            break;

        case 'today':
            // Filter for users created today
            $matchingUsers = $matchingUsers->filter(function ($user) {
                return $user instanceof \Illuminate\Database\Eloquent\Model && Carbon::today()->isSameDay($user->created_at);
            });
            break;
    }

    return $matchingUsers;
}



function prepareResponse($users, $limit, $type = '')
{
    $fields = [
        'id', 'name', 'age', 'gender', 'height', 'city_living_in', 'state', 'living_country',
        'religion', 'marital_status', 'working_sector', 'profession', 'about_myself',
        'profile_picture_url', 'invitation_send_status', 'is_favorited', 'premium_member_badge',
        'trusted_badge_access', 'totalCriteriaMatched', 'matched_fields',
    ];

    if ($type == 'near') {
        // Keep all users regardless of match count
        $result = $users->map(function ($user) use ($fields) {
            return array_intersect_key($user->toArray(), array_flip($fields));
        })->values()->all();
    } else {
        // Filter for users with at least one match
        $filteredUsers = $users->filter(function ($user) {
            return $user->totalCriteriaMatched > 0;
        });

        // Map results with selected fields
        $result = $filteredUsers->map(function ($user) use ($fields) {
            return array_intersect_key($user->toArray(), array_flip($fields));
        })->values()->all();
    }

    // Apply limit if specified
    if ($limit !== null) {
        $result = array_slice($result, 0, $limit);
    }

    return $result;
}
















function maskUserData($user,$currentUser)
{
    // Define the fields that should not be masked (visible fields)
    $visibleFields = [

    'id',

    'name',
    'active_package_id',

    'password',
    'role',
    'role_id',
    'profile_for',
    'profile_created_by',

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
    'is_contact_details_viewed',



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

   // Define the fields that are conditionally viewable
   $conditionallyViewableFields = [
    'mobile_number',
    'whatsapp',
    'email',
    'living_country',
    'currently_living_in',
    'city_living_in',
];


// Check if the profile has been viewed by the current user
 $hasViewedProfile = $currentUser->hasViewedProfile($user['id']);

// If the profile has been viewed, merge conditionally viewable fields into visibleFields
if ($hasViewedProfile) {
    $visibleFields = array_merge($visibleFields, $conditionallyViewableFields);
}


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











function hasServiceAccess(string $serviceName, $user = null)
{
    // return $user;
    // Use the passed user or the authenticated user if none is provided
    $user = $user ?? Auth::user();

    // If no user is authenticated or the user doesn't have an active package, deny access
    if (!$user || !isset($user->active_package) || !isset($user->active_package['allowed_services'])) {
        return false; // Access denied
    }

    // return $user->active_package;
    // Check if the user's active package allows the specified service
    foreach ($user->active_package['allowed_services'] as $service) {
        if (isset($service['name'], $service['status']) && $service['name'] === $serviceName && $service['status'] === 'active') {
            return true; // Access granted
        }
    }

    return false; // Access denied
}


function allowed_services($activePackage) {
    if ($activePackage) {
        return [
            'id' => $activePackage->id,
            'package_name' => $activePackage->package_name,
            'price' => $activePackage->price,
            'discount_type' => $activePackage->discount_type,
            'discount' => $activePackage->discount,
            'sub_total_price' => $activePackage->sub_total_price,
            'currency' => $activePackage->currency,
            'duration' => $activePackage->duration,
            // 'created_at' => $activePackage->created_at,
            // 'updated_at' => $activePackage->updated_at,
            'allowed_services' => $activePackage->activeServices
                ->sortBy(function ($service) {
                    return $service->service->indexno; // Sort by the indexno of the related package_service
                })
                ->map(function ($service) use ($activePackage) {
                    // Conditional logic to display "View up to X Contact Details" only if profile_view is present
                    if ($service->service->name === 'View up to 180 Contact Details') {
                        return [
                            'name' => 'View up to ' . $activePackage->profile_view . ' Contact Details',
                            'status' => $service->status,
                        ];
                    }
                    return [
                        'name' => $service->service->name,
                        'status' => $service->status,
                    ];
                })
                ->values() // Re-index the collection to remove the original keys
                ->toArray(), // Convert the collection to an array
        ];
    }

    return null;
}


function getPackageRevenueData($year = null, $week = 'current')
{
    // Default to the current year if no year is provided
    $year = $year ?? now()->year;

    // Initialize arrays to store the results
    $monthlyResult = [];
    $totalRevenueByPackage = [];
    $totalRevenueByPackageYearly = [];
    $totalRevenueByPackageWeekly = [];

    // Define the week date range based on the $week parameter
    if ($week === 'current') {
        $weekStart = Carbon::now()->startOfWeek();
        $weekEnd = Carbon::now()->endOfWeek();
    } elseif ($week === 'last') {
        $weekStart = Carbon::now()->subWeek()->startOfWeek();
        $weekEnd = Carbon::now()->subWeek()->endOfWeek();
    }

    // Retrieve all packages and loop through each
    $packages = Package::all();
    foreach ($packages as $package) {
        // Prepare an array of 12 months initialized to 0 for monthly revenue
        $monthlyData = array_fill(0, 12, 0);

        // Fetch payments for the current package and year, grouped by month
        $payments = Payment::select(DB::raw('MONTH(date) as month'), DB::raw('SUM(amount) as total_amount'))
            ->join('package_purchases', 'payments.package_purchase_id', '=', 'package_purchases.id')
            ->where('package_purchases.package_id', $package->id)
            ->where('payments.type', 'package')
            ->where('payments.status', 'completed')
            ->whereYear('payments.date', $year)
            ->groupBy(DB::raw('MONTH(date)'))
            ->get();

        // Map the monthly payment totals to the monthly data array
        foreach ($payments as $payment) {
            $monthlyData[$payment->month - 1] = (int) $payment->total_amount;  // Cast to integer
        }

        // Calculate total revenue for the package and cast to integer
        $totalRevenue = (int) array_sum($monthlyData);

        // Add the package's data to the monthly result array
        $monthlyResult[] = [
            'name' => $package->package_name,
            'data' => $monthlyData,
        ];

        // Add total revenue by package
        $totalRevenueByPackage[] = [
            'name' => $package->package_name,
            'total_revenue' => $totalRevenue,
        ];

        // Fetch total revenue for the package for the entire year and cast to integer
        $yearlyRevenue = (int) Payment::whereHas('packagePurchase', function ($query) use ($package) {
                $query->where('package_id', $package->id);
            })
            ->where('type', 'package')
            ->where('status', 'completed')
            ->whereYear('date', $year)
            ->sum('amount');

        // Add total yearly revenue by package
        $totalRevenueByPackageYearly[] = [
            'name' => $package->package_name,
            'total_revenue_yearly' => $yearlyRevenue,
        ];

        // Prepare an array for weekly revenue, initialized to 0 for each day
        $weeklyData = array_fill(0, 7, 0);

        // Fetch total revenue for the specified week, grouped by day
        $weeklyPayments = Payment::select(DB::raw('DAYOFWEEK(date) as day'), DB::raw('SUM(amount) as total_amount'))
            ->join('package_purchases', 'payments.package_purchase_id', '=', 'package_purchases.id')
            ->where('package_purchases.package_id', $package->id)
            ->where('payments.type', 'package')
            ->where('payments.status', 'completed')
            ->whereBetween('date', [$weekStart, $weekEnd])
            ->groupBy(DB::raw('DAYOFWEEK(date)'))
            ->get();

        // Map the daily payment totals to the weekly data array
        foreach ($weeklyPayments as $payment) {
            $weeklyData[$payment->day - 1] = (int) $payment->total_amount;  // Cast to integer
        }

        // Add to total revenue by package weekly
        $totalRevenueByPackageWeekly[] = [
            'name' => $package->package_name,
            'data' => $weeklyData,
        ];
    }

    // Find the maximum total revenue from monthly package revenues
    $maxMonthlyRevenue = max(array_column($totalRevenueByPackage, 'total_revenue'));

    // Find the maximum total revenue from weekly package revenues
    $maxWeeklyRevenue = 0;
    foreach ($totalRevenueByPackageWeekly as $weeklyRevenue) {
        // Get the maximum value for the current package's weekly data
        $currentPackageMax = max($weeklyRevenue['data']);
        // Update the overall maximum if the current package's max is greater
        $maxWeeklyRevenue = max($maxWeeklyRevenue, $currentPackageMax);
    }

    // Return the combined result along with the maximum monthly and weekly revenue
    return [
        'monthly_package_revenue' => $monthlyResult,
        'monthly_package_revenue_max' => getDynamicMaxValue($maxMonthlyRevenue),
        'total_revenue_per_package' => $totalRevenueByPackage,
        'yearly_package_revenue' => $totalRevenueByPackageYearly,
        'weekly_package_revenue' => $totalRevenueByPackageWeekly,
        'weekly_package_revenue_max' => getDynamicMaxValue($maxWeeklyRevenue), // Max value for weekly revenue
    ];
}



function getDynamicMaxValue($value)
{
    // If the value is less than or equal to 0, return 0
    if ($value <= 0) {
        return 0;
    }

    // Determine the number of digits in the value
    $digitCount = strlen((string)$value);

    // Calculate the base scale dynamically based on the digit count
    $baseScale = 10 ** ($digitCount - 1); // Example: For 3 digits, baseScale = 100 (10^2)

    // For 1 and 2 digits, we set a minimum scaling factor of 100
    if ($digitCount < 3) {
        $baseScale = 100; // Minimum base scale for 1 or 2 digits
    }

    // Calculate the next max value based on the scaling factor
    $maxValue = ceil($value / $baseScale) * $baseScale;

    // Ensure the maxValue is at least the original value
    if ($maxValue < $value) {
        $maxValue += $baseScale;
    }

    return $maxValue;
}





function convertHeightToInches($height)
{
    if (preg_match('/^(\d+)ft[+\s]?(\d+)in$/', $height, $matches)) {
        $feet = (int) $matches[1];
        $inches = (int) $matches[2];
        return ($feet * 12) + $inches;
    }
    return null; // Return null if the format is invalid
}
