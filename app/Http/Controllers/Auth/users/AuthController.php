<?php
// namespace App\Http\Controllers\Auth;
namespace App\Http\Controllers\Auth\users;
use App\Http\Controllers\Controller;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    public function login(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $credentials = [
            'email' => $request->email,
            'password' => $request->password,
        ];



        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            // $token = JWTAuth::fromUser($user);
             $payload = [
            'email' => $user->email,
            'name' => $user->name,
        ];
            $token = JWTAuth::fromUser($user, ['guard' => 'api']);
            return response()->json(['token' => $token,'user'=>$payload], 200);
        }

        return response()->json(['message' => 'Invalid credentials'], 401);
    }



public function checkTokenExpiration(Request $request)
{


    // return $token = $request->token;
     $token = $request->bearerToken();


    try {

        $payload = JWTAuth::setToken($token)->getPayload();

        // Check if the token's expiration time (exp) is greater than the current timestamp
        $isExpired = $payload->get('exp') < time();

        $user = Auth::guard('api')->setToken($token)->authenticate();


        // Get user's roles
      $roles = $user->roles;
    // return $roles->permissions;

    // Initialize an empty array to store permissions
    $permissions = [];

    // Loop through each role to fetch permissions
    // foreach ($roles as $role) {
        // Merge permissions associated with the current role into the permissions array
        $permissions = array_merge($permissions, $roles->permissions->toArray());
    // }

    // Remove duplicates and re-index the array
    $permissions = array_values(array_unique($permissions, SORT_REGULAR));

    // Now $permissions contains all unique permissions associated with the user
    // You can use $permissions as needed

        // $user = JWTAuth::setToken($token)->authenticate();
        return response()->json(['message' => 'Token is valid', 'user' => $user ], 200);
    } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        // Token has expired
        return response()->json(['message' => 'Token has expired'], 401);
    } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
        // Token is invalid
        return response()->json(['message' => 'Invalid token'], 401);
    } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
        // Token not found or other JWT exception
        return response()->json(['message' => 'Error while processing token'], 500);
    }
}
public function checkToken(Request $request)
{
    $user = Auth::guard('api')->user();
    if ($user) {
        return response()->json(['message' => 'Token is valid']);
    } else {
        return response()->json(['error' => 'Unauthorized'], 401);
    }
}
    public function logout(Request $request)
    {
        try {
            $token = $request->bearerToken();
            if ($token) {
                JWTAuth::setToken($token)->invalidate();
                return response()->json(['message' => 'Logged out successfully'], 200);
            } else {
                return response()->json(['message' => 'Invalid token'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['message' => 'Error while processing token'], 500);
        }
    }



    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',

            'email' => 'nullable|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'nullable|string|max:255',
            'role_id' => 'nullable|integer',
            'profile_for' => 'nullable|string|max:255',
            'mobile_number' => 'nullable|string|max:15',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|string|max:10',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'father_name' => 'nullable|string|max:255',
            'mother_name' => 'nullable|string|max:255',
            'marital_status' => 'nullable|string|max:255',
            'religion' => 'nullable|string|max:255',
            'nationality' => 'nullable|string|max:255',
            'highest_qualification' => 'nullable|string|max:255',
            'college_name' => 'nullable|string|max:255',
            'working_sector' => 'nullable|string|max:255',
            'profession' => 'nullable|string|max:255',
            'profession_details' => 'nullable|string|max:255',
            'monthly_income' => 'nullable|string|max:255',
            'father_occupation' => 'nullable|string|max:255',
            'mother_occupation' => 'nullable|string|max:255',
            'living_country' => 'nullable|string|max:255',
            'currently_living_in' => 'nullable|string|max:255',
            'city_living_in' => 'nullable|string|max:255',
            'family_details' => 'nullable|string',
            'height' => 'nullable|string|max:255',
            'weight' => 'nullable|string|max:255',
            'bodyType' => 'nullable|string|max:255',
            'race' => 'nullable|string|max:255',
            'blood_group' => 'nullable|string|max:255',
            'mother_status' => 'nullable|string|max:255',
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

        // Generate JWT token for the registered user
        $token = JWTAuth::fromUser($user);

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user,
            'token' => $token // Return JWT token
        ], 201);
    }








         public function changePassword(Request $request)
         {
             $validator = Validator::make($request->all(), [
                'current_password' => 'required',
                 'new_password' => 'required|min:8|confirmed',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 400);
            }
            $user = Auth::guard('api')->user();
             if (!Hash::check($request->current_password, $user->password)) {
                return response()->json(['message' => 'Current password is incorrect.'], 400);
             }
             $user->password = Hash::make($request->new_password);
             $user->save();
             return response()->json(['message' => 'Password changed successfully.'], 200);
         }


}
