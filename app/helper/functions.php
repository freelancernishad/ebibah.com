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


    if($matchType=='near'){
       return $matchingUsers = getNearbyMatches($query,$user);
    }else{
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
    }








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










    $result = $finalMatchingUsers->map(function ($matchedUser) use ($matchedUsersDetails, $user, $minAge, $maxAge) {
        $partnerAgeMatch = [
            "field" => "partner_age",
            "auth_user_preference" => explode('-', $user->partner_age),
            "matched_user_value" => \Carbon\Carbon::parse($matchedUser->date_of_birth)->age,
            "is_matched" => (\Carbon\Carbon::parse($matchedUser->date_of_birth)->age >= $minAge &&
                             \Carbon\Carbon::parse($matchedUser->date_of_birth)->age <= $maxAge)
        ];

        if (!isset($matchedUsersDetails[$matchedUser->id])) {
            $matchedUsersDetails[$matchedUser->id] = [
                'criteria_matched' => 0,
                'fields' => []
            ];
        }

        $matchedUsersDetails[$matchedUser->id]['fields'][] = $partnerAgeMatch;

        return array_merge(
            $matchedUser->toArray(),
            [
                'matched_fields' => $matchedUsersDetails[$matchedUser->id]['fields'],
                'totalCriteriaMatched' => $matchedUsersDetails[$matchedUser->id]['criteria_matched']
            ]
        );
    })->sortByDesc('totalCriteriaMatched')->values(); // Sort by criteria matched











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
        'premium_member_badge',
        'trusted_badge_access',
        'totalCriteriaMatched',
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
            $userValues = $user->$relation->pluck($column)->toArray();

            if (!empty($userValues)) {
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
                } elseif ($column === 'profession' && $relation == 'partnerProfessionalDetails') {
                    $userColumn = 'profession_details';
                }

                $query->orWhere(function ($q) use ($userColumn, $userValues, &$matchedUsersDetails, &$totalCriteria, $user) {
                    foreach ($userValues as $value) {
                        $q->orWhere($userColumn, $value);
                    }
                });

                $scoreConditions[] = "1"; // Score for matching criteria
                $totalCriteria++; // Increment total criteria count

                // Count matches for each user
                $query->get()->each(function ($matchingUser) use ($userColumn, $userValues, &$matchedUsersDetails) {
                    $otherUserValue = $matchingUser->$userColumn;

                    if (in_array($otherUserValue, $userValues)) {
                        if (!isset($matchedUsersDetails[$matchingUser->id])) {
                            $matchedUsersDetails[$matchingUser->id] = [
                                'criteria_matched' => 0,
                                'fields' => []
                            ];
                        }

                        $matchedUsersDetails[$matchingUser->id]['fields'][] = [
                            'field' => $userColumn,
                            'auth_user_preference' => $userValues,
                            'matched_user_value' => $otherUserValue,
                            'matched' => true
                        ];

                        // Increment the match count for this user
                        $matchedUsersDetails[$matchingUser->id]['criteria_matched']++;
                    }
                });
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




function getNearbyMatches($query, $user)
{
    $partnerCountries = $user->partnerCountries->pluck('country')->toArray();
    $partnerStates = $user->partnerStates->pluck('state')->toArray();
    $partnerCities = $user->partnerCities->pluck('city')->toArray();

    // Ensure at least one set of matching criteria exists before proceeding
    if (!empty($partnerCountries) || !empty($partnerStates) || !empty($partnerCities)) {
        $query->where(function ($q) use ($partnerCountries, $partnerStates, $partnerCities) {
            if (!empty($partnerCountries)) {
                $q->orWhereIn('living_country', $partnerCountries);
            }
            if (!empty($partnerStates)) {
                $q->orWhereIn('state', $partnerStates);
            }
            if (!empty($partnerCities)) {
                $q->orWhereIn('city_living_in', $partnerCities);
            }
        });
    }

    // Fetch and return only the matched users
    $matchedUsers = $query->get();

    // Optional: Prepare the match details for each user
    $matchedUsersDetails = $matchedUsers->map(function ($matchedUser) use ($partnerCountries, $partnerStates, $partnerCities) {
        return [
            'user_id' => $matchedUser->id,
            'matches' => [
                'country' => in_array($matchedUser->country, $partnerCountries) ? $matchedUser->country : null,
                'currently_living_in' => in_array($matchedUser->currently_living_in, $partnerStates) ? $matchedUser->currently_living_in : null,
                'city' => in_array($matchedUser->city, $partnerCities) ? $matchedUser->city : null,
            ]
        ];
    })->filter(function ($details) {
        // Only keep users with at least one matching field
        return array_filter($details['matches']);
    });

    return $matchedUsersDetails;
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





