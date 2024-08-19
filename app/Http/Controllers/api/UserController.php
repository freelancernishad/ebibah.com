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
    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            // 'mobile' => [
            //     'required',
            //     'string',
            //     'max:15',
            //     Rule::unique('users')->ignore($user->id),
            // ],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            // Add validation rules for other fields as needed
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
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
}
