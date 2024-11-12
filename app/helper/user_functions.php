<?php

use App\Models\User;

function calculateAge($dateOfBirth)
{
    if (!$dateOfBirth) {
        return null;
    }

    $birthDate = new \DateTime($dateOfBirth);
    $today = new \DateTime();
    return $today->diff($birthDate)->y;
}

 function generateTrxId()
{
    return strtoupper(uniqid(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 3) . rand(10000, 99999)));
}



// Add this new function to handle the criteria matches logic
 function getCriteriaMatches($authUserId, $userId)
{
    // Get the authenticated user and target user
    $authUser = User::find($authUserId);
    $user = User::find($userId);

    if (!$authUser || !$user) {
        return response()->json(['message' => 'User not found'], 404);
    }

    // Define the authenticated user's partner preferences
    $partnerPreferences = [
        'marital_status' => $authUser->partnerMaritalStatuses()->pluck('marital_status')->toArray(),
        'religion' => $authUser->partnerReligions()->pluck('religion')->toArray(),
        'community' => $authUser->partnerCommunities()->pluck('community')->toArray(),
        'mother_tongue' => $authUser->partnerMotherTongues()->pluck('mother_tongue')->toArray(),
        'highest_qualification' => $authUser->partnerQualification()->pluck('qualification')->toArray(),
        'working_sector' => $authUser->partnerWorkingWith()->pluck('working_with')->toArray(),
        'profession' => $authUser->partnerProfessions()->pluck('profession')->toArray(),
        'profession_details' => $authUser->partnerProfessionalDetails()->pluck('profession')->toArray(),
        'living_country' => $authUser->partnerCountries()->pluck('country')->toArray(),
        'state' => $authUser->partnerStates()->pluck('state')->toArray(),
        'city_living_in' => $authUser->partnerCities()->pluck('city')->toArray(),
    ];

    // Define display names for preferences
    $displayNames = [
        'marital_status' => 'Marital Status',
        'religion' => 'Religion',
        'community' => 'Community',
        'mother_tongue' => 'Mother Tongue',
        'highest_qualification' => 'Highest Qualification',
        'working_sector' => 'Working Sector',
        'profession' => 'Profession',
        'profession_details' => 'Professional Detail',
        'living_country' => 'Living Country',
        'state' => 'State',
        'city_living_in' => 'City Living In',
    ];

    // Initialize the matches array
    $matches = [];

    // Compare preferences to create match details
    foreach ($partnerPreferences as $preferenceCriteria => $preferenceValue) {
        $displayName = $displayNames[$preferenceCriteria] ?? $preferenceCriteria;
        $userValue = $user->{$preferenceCriteria} ?? null;

        if (is_array($preferenceValue)) {
            $matches[] = [
                'preference' => $displayName,
                'required' => $preferenceValue,
                'user_value' => $userValue,
                'match' => in_array(strtolower($userValue), array_map('strtolower', $preferenceValue))
            ];
        } else {
            $matches[] = [
                'preference' => $displayName,
                'required' => $preferenceValue,
                'user_value' => $userValue,
                'match' => (strtolower($userValue) === strtolower($preferenceValue))
            ];
        }
    }

    return  $matches;
}
