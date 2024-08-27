<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{



    public function myProfile()
    {
        // Get the authenticated user
        $user = Auth::guard('api')->user();

        // Check if the user is authenticated
        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }



        $user->load([
            'sentInvitations',
            'receivedInvitations',
            'profileViews',
            'viewedProfiles',
            'payments',
            'userImages',
        ]);


           // Calculate age
    $age = calculateAge($user->date_of_birth);

    // Convert user to array and include age
    $userArray = $user->toArray();
    $userArray['age'] = $age;

        // Return the authenticated user's profile
        return response()->json(['user' => $userArray], 200);
    }



    // User registration
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            // 'mobile' => 'required|string|max:15|unique:users',
            'email' => 'nullable|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            // Add validation rules for other fields as needed
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'role_id' => $request->role_id,
            'profile_for' => $request->profile_for,
            'mobile_number' => $request->mobile_number,
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'father_name' => $request->father_name,
            'mother_name' => $request->mother_name,
            'marital_status' => $request->marital_status,
            'religion' => $request->religion,
            'nationality' => $request->nationality,
            'highest_qualification' => $request->highest_qualification,
            'college_name' => $request->college_name,
            'working_sector' => $request->working_sector,
            'profession' => $request->profession,
            'profession_details' => $request->profession_details,
            'monthly_income' => $request->monthly_income,
            'father_occupation' => $request->father_occupation,
            'mother_occupation' => $request->mother_occupation,
            'living_country' => $request->living_country,
            'currently_living_in' => $request->currently_living_in,
            'city_living_in' => $request->city_living_in,
            'family_details' => $request->family_details,
            'height' => $request->height,
            'weight' => $request->weight,
            'bodyType' => $request->bodyType,
            'race' => $request->race,
            'blood_group' => $request->blood_group,
            'mother_status' => $request->mother_status,
        ]);

        return response()->json(['message' => 'User registered successfully', 'user' => $user], 201);
    }

// User update
public function update(Request $request)
{
    $user = auth()->user();

    if (!$user) {
        return response()->json(['message' => 'User not found'], 404);
    }

    // Validation rules for all fields
    $validator = Validator::make($request->all(), [
        'date_of_birth' => 'nullable|date',
        'gender' => 'nullable|string|max:10',
        'first_name' => 'nullable|string|max:255',
        'last_name' => 'nullable|string|max:255',
        'father_name' => 'nullable|string|max:255',
        'mother_name' => 'nullable|string|max:255',
        'marital_status' => 'nullable|string|max:50',
        'religion' => 'nullable|string|max:255',
        'nationality' => 'nullable|string|max:255',
        'highest_qualification' => 'nullable|string|max:255',
        'college_name' => 'nullable|string|max:255',
        'working_sector' => 'nullable|string|max:255',
        'profession' => 'nullable|string|max:255',
        'profession_details' => 'nullable|string|max:255',
        'monthly_income' => 'nullable|numeric',
        'father_occupation' => 'nullable|string|max:255',
        'mother_occupation' => 'nullable|string|max:255',
        'living_country' => 'nullable|string|max:255',
        'currently_living_in' => 'nullable|string|max:255',
        'city_living_in' => 'nullable|string|max:255',
        'family_details' => 'nullable|string|max:255',
        'height' => 'nullable|string|max:50',
        'weight' => 'nullable|string|max:50',
        'bodyType' => 'nullable|string|max:50',
        'race' => 'nullable|string|max:50',
        'blood_group' => 'nullable|string|max:10',
        'mother_status' => 'nullable|string|max:50',
        'birth_place' => 'nullable|string|max:255',
        'personal_values' => 'nullable|string|max:255',
        'disability' => 'nullable|string|max:255',
        'posted_by' => 'nullable|string|max:255',
        'profile_created_by' => 'nullable|string|max:255',
        'whatsapp' => 'nullable|string|max:20',
        'community' => 'nullable|string|max:255',
        'mother_tongue' => 'nullable|string|max:255',
        'sub_community' => 'nullable|string|max:255',
        'family_values' => 'nullable|string|max:255',
        'family_location' => 'nullable|string|max:255',
        'family_type' => 'nullable|string|max:255',
        'family_native_place' => 'nullable|string|max:255',
        'total_siblings' => 'nullable|integer',
        'siblings_married' => 'nullable|integer',
        'siblings_not_married' => 'nullable|integer',
        'state' => 'nullable|string|max:255',
        'about_myself' => 'nullable|string',
        'partner_age' => 'nullable|string|max:50',
        'partner_marital_status' => 'nullable|string|max:50',
        'partner_religion' => 'nullable|string|max:255',
        'partner_community' => 'nullable|string|max:255',
        'partner_mother_tongue' => 'nullable|string|max:255',
        'partner_qualification' => 'nullable|array',
        'partner_working_with' => 'nullable|array',
        'partner_profession' => 'nullable|array',
        'partner_professional_details' => 'nullable|string',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 400);
    }

    // Filter out null values from the request data
    $data = array_filter($request->only([
        'name',
        'email',
        'role',
        'role_id',
        'profile_for',
        'mobile_number',
        'date_of_birth',
        'gender',
        'first_name',
        'last_name',
        'father_name',
        'mother_name',
        'marital_status',
        'religion',
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
        'height',
        'weight',
        'bodyType',
        'race',
        'blood_group',
        'mother_status',
        'birth_place',
        'personal_values',
        'disability',
        'posted_by',
        'profile_created_by',
        'whatsapp',
        'community',
        'mother_tongue',
        'sub_community',
        'family_values',
        'family_location',
        'family_type',
        'family_native_place',
        'total_siblings',
        'siblings_married',
        'siblings_not_married',
        'state',
        'about_myself',
        'partner_age',
        'partner_marital_status',
        'partner_religion',
        'partner_community',
        'partner_mother_tongue',
        'partner_qualification',
        'partner_working_with',
        'partner_profession',
        'partner_professional_details',
    ]));

    // Update the user with the filtered data
    $user->update($data);

    return response()->json(['message' => 'User updated successfully'], 200);
}


    // User delete
    public function delete($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully'], 200);
    }

    // List users (index)
    public function index()
    {
        $users = User::all();
        return response()->json(['users' => $users], 200);
    }

    // Show user details
    public function show($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json(['user' => $user], 200);
    }



    public function updateBasicsAndLifestyle(Request $request, $id)
{
    $user = User::find($id);

    if (!$user) {
        return response()->json(['message' => 'User not found'], 404);
    }

    // Validate the request data
    $validator = Validator::make($request->all(), [
        'date_of_birth' => 'required|date',
        'marital_status' => 'required|string|max:255',
        'religion' => 'required|string|max:255',
        'height' => 'required|string|max:10',
        'currently_living_in' => 'required|string|max:255', // Replaced location with currently_living_in
        'birth_place' => 'nullable|string|max:255',
        'personal_values' => 'nullable|string|max:255',
        'blood_group' => 'nullable|string|max:10',
        'disability' => 'nullable|string|max:255',
        'posted_by' => 'nullable|string|max:255',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 400);
    }

    // Update user basics and lifestyle information
    $user->date_of_birth = $request->date_of_birth;
    $user->marital_status = $request->marital_status;
    $user->religion = $request->religion;
    $user->height = $request->height;
    $user->currently_living_in = $request->currently_living_in;
    $user->birth_place = $request->birth_place;
    $user->personal_values = $request->personal_values;
    $user->blood_group = $request->blood_group;
    $user->disability = $request->disability;
    $user->posted_by = $request->posted_by;

    // Save the updated information
    $user->save();

    return response()->json(['message' => 'Basics & Lifestyle updated successfully', 'user' => $user], 200);
}


    // Update Religious Background
    public function updateReligiousBackground(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'religion' => 'required|string|max:255',
            'community' => 'nullable|string|max:255',
            'sub_community' => 'nullable|string|max:255',
            'mother_tongue' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $user->religion = $request->religion;
        $user->community = $request->community;
        $user->sub_community = $request->sub_community;
        $user->mother_tongue = $request->mother_tongue;

        $user->save();

        return response()->json(['message' => 'Religious background updated successfully'], 200);
    }



    // Update Education & Career
    public function updateEducationAndCareer(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'highest_qualification' => 'required|string|max:255',
            'college_name' => 'required|string|max:255',
            'working_sector' => 'required|string|max:255',
            'profession' => 'required|string|max:255',
            'monthly_income' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $user->highest_qualification = $request->highest_qualification;
        $user->college_name = $request->college_name;
        $user->working_sector = $request->working_sector;
        $user->profession = $request->profession;
        $user->monthly_income = $request->monthly_income;

        $user->save();

        return response()->json(['message' => 'Education and career details updated successfully'], 200);
    }

    // Update Family Details
    public function updateFamilyDetails(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'father_occupation' => 'required|string|max:255',  // Father's Status
            'mother_occupation' => 'required|string|max:255',  // Mother's Status
            'family_location' => 'required|string|max:255',
            'family_values' => 'nullable|string|max:255',
            'native_place' => 'nullable|string|max:255',
            'no_of_brothers' => 'required|integer|min:0',
            'no_of_sisters' => 'required|integer|min:0',
            'family_type' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $user->father_occupation = $request->father_occupation;  // Father's Status
        $user->mother_occupation = $request->mother_occupation;  // Mother's Status
        $user->family_location = $request->family_location;
        $user->family_values = $request->family_values;
        $user->native_place = $request->native_place;
        $user->no_of_brothers = $request->no_of_brothers;
        $user->no_of_sisters = $request->no_of_sisters;
        $user->family_type = $request->family_type;

        $user->save();

        return response()->json(['message' => 'Family details updated successfully'], 200);
    }


    // Update Hobbies and Interests
    public function updateHobbiesAndInterests(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'hobbies_and_interests' => 'required|array', // Expecting an array of hobbies and interests
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // Storing the array of hobbies and interests as JSON
        $user->hobbies_and_interests = json_encode($request->hobbies_and_interests);

        $user->save();

        return response()->json(['message' => 'Hobbies and interests updated successfully'], 200);
    }

    // Update Partner Preferences: Basics & Lifestyle
public function updatePartnerBasicsAndLifestyle(Request $request, $id)
{
    $user = User::find($id);

    if (!$user) {
        return response()->json(['message' => 'User not found'], 404);
    }

    $validator = Validator::make($request->all(), [
        'partner_age' => 'required|integer',
        'partner_religion' => 'required|string|max:255',
        'partner_community' => 'required|string|max:255',
        'partner_height' => 'required|string|max:50',
        'partner_location' => 'required|string|max:255',
        'partner_mother_tongue' => 'required|string|max:255',
        'partner_marital_status' => 'required|string|max:255',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 400);
    }

    $user->partner_age = $request->partner_age;
    $user->partner_religion = $request->partner_religion;
    $user->partner_community = $request->partner_community;
    $user->partner_height = $request->partner_height;
    $user->partner_location = $request->partner_location;
    $user->partner_mother_tongue = $request->partner_mother_tongue;
    $user->partner_marital_status = $request->partner_marital_status;

    $user->save();

    return response()->json(['message' => 'Partner Basics & Lifestyle updated successfully'], 200);
}

// Update Partner Preferences: Location Details
public function updatePartnerLocationDetails(Request $request, $id)
{
    $user = User::find($id);

    if (!$user) {
        return response()->json(['message' => 'User not found'], 404);
    }

    $validator = Validator::make($request->all(), [
        'partner_country_living_in' => 'required|string|max:255',
        'partner_state_living_in' => 'required|string|max:255',
        'partner_city_district' => 'required|string|max:255',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 400);
    }

    $user->partner_country_living_in = $request->partner_country_living_in;
    $user->partner_state_living_in = $request->partner_state_living_in;
    $user->partner_city_district = $request->partner_city_district;

    $user->save();

    return response()->json(['message' => 'Partner Location Details updated successfully'], 200);
}

// Update Partner Preferences: Education & Career
public function updatePartnerEducationAndCareer(Request $request, $id)
{
    $user = User::find($id);

    if (!$user) {
        return response()->json(['message' => 'User not found'], 404);
    }

    $validator = Validator::make($request->all(), [
        'partner_qualification' => 'required|string|max:255',
        'partner_working_with' => 'required|string|max:255',
        'partner_profession' => 'required|string|max:255',
        'partner_professional_details' => 'nullable|string|max:255',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 400);
    }

    $user->partner_qualification = $request->partner_qualification;
    $user->partner_working_with = $request->partner_working_with;
    $user->partner_profession = $request->partner_profession;
    $user->partner_professional_details = $request->partner_professional_details;

    $user->save();

    return response()->json(['message' => 'Partner Education & Career updated successfully'], 200);
}




}
