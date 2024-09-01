<?php
// namespace App\Http\Controllers\Auth;
namespace App\Http\Controllers\Auth\users;
use App\Models\User;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{

    public function login(Request $request)
    {
        if ($request->has('access_token')) {
            // Validate access_token
            $validator = Validator::make($request->all(), [
                'access_token' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Verify the access token using Google's tokeninfo endpoint
            $response = Http::get('https://oauth2.googleapis.com/tokeninfo', [
                'access_token' => $request->access_token,
            ]);

            if ($response->failed()) {
                return response()->json(['error' => 'Invalid access token'], 400);
            }

           return $userData = $response->json();

            // Check if the email exists in your database
            $user = User::where('email', $userData['email'])->first();
            if (!$user) {
                // If user does not exist, create a new user
                $username = explode('@', $userData['email'])[0]; // Extract username from email
                $user = User::create([
                    'username' => $username,
                    'email' => $userData['email'],
                    'name' => $userData['name'], // Store the name from Google
                    'password' => Hash::make(Str::random(16)), // Generate a random password
                    'step' => 1, // Set step value to 1
                ]);
            }

            // Login the user
            Auth::login($user);

            // Build the payload including the username and step
            $payload = [
                'email' => $user->email,
                'username' => $user->username, // Include username here
                'name' => $user->name, // Include name here
                'step' => $user->step, // Include step here
            ];

            $token = JWTAuth::fromUser($user); // Generate JWT token for the user
            return response()->json(['token' => $token, 'user' => $payload], 200);
        } else {
            // Validate email and password
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

                // Build the payload including the username and step
                $payload = [
                    'email' => $user->email,
                    'username' => $user->username, // Include username here
                    'name' => $user->name, // Include name here
                    'step' => $user->step, // Include step here
                ];

                $token = JWTAuth::fromUser($user); // Generate JWT token for the user
                return response()->json(['token' => $token, 'user' => $payload], 200);
            }

            return response()->json(['message' => 'Invalid credentials'], 401);
        }
    }





public function checkTokenExpiration(Request $request)
{


    // return $token = $request->token;
     $token = $request->bearerToken();


    try {

        $payload = JWTAuth::setToken($token)->getPayload();

        // Check if the token's expiration time (exp) is greater than the current timestamp
        $isExpired = $payload->get('exp') < time();

        $user = Auth::guard('web')->setToken($token)->authenticate();


        // Get user's roles
    //   $roles = $user->roles;
    // return $roles->permissions;

    // Initialize an empty array to store permissions
    // $permissions = [];

    // Loop through each role to fetch permissions
    // foreach ($roles as $role) {
        // Merge permissions associated with the current role into the permissions array
        // $permissions = array_merge($permissions, $roles->permissions->toArray());
    // }

    // Remove duplicates and re-index the array
    // $permissions = array_values(array_unique($permissions, SORT_REGULAR));

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
    $user = Auth::guard('web')->user();
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
        if ($request->has('access_token')) {
            // Validate access_token
            $validator = Validator::make($request->all(), [
                'access_token' => 'required|string',
                'name' => 'nullable|string|max:255',
                'date_of_birth' => 'nullable|date',
                'religion' => 'nullable|string|max:255',
                'gender' => 'nullable|string|max:10',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 400);
            }

            // Fetch user info from Google API using the access token
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $request->access_token,
            ])->get('https://www.googleapis.com/oauth2/v3/userinfo');

            if ($response->failed()) {
                return response()->json(['error' => 'Invalid access token'], 400);
            }

            $userData = $response->json();

            // Check if the email already exists
            $existingUser = User::where('email', $userData['email'])->first();
            if ($existingUser) {
                return response()->json(['error' => 'Email already registered'], 400);
            }

            // Extract username from email
            $username = explode('@', $userData['email'])[0];

            // Create a new user with data from Google and additional fields
            $user = new User([
                'username' => $username,
                'email' => $userData['email'],
                'name' => $request->input('name', $userData['name']), // Use the provided name or default to Google name
                'password' => Hash::make(Str::random(16)), // Generate a random password
                'date_of_birth' => $request->input('date_of_birth', null), // Use the provided date of birth if available
                'religion' => $request->input('religion', null), // Use the provided religion if available
                'gender' => $request->input('gender', null), // Use the provided gender if available
                'step' => 1, // Set step value to 1
            ]);

            $user->save();
        } else {
            // Validate all fields for traditional registration
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:users',
                'password' => 'required|string|min:6|confirmed', // This validates both password and password_confirmation
                'date_of_birth' => 'nullable|date',
                'religion' => 'nullable|string|max:255',
                'gender' => 'nullable|string|max:10',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 400);
            }

            // Create a new user with data from request
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'date_of_birth' => $request->date_of_birth,
                'religion' => $request->religion,
                'gender' => $request->gender,
                'step' => 1, // Set step value to 1
            ]);
        }

        // Build the payload including the username and step
        $payload = [
            'name' => $user->name,
            'email' => $user->email,
            'username' => $user->username ?? $user->name, // Include username or name here
            'step' => $user->step, // Include step here
        ];

        // Generate JWT token
        $token = JWTAuth::fromUser($user);

        // Return the response with token and user data
        return response()->json([
            'message' => 'User registered successfully',
            'user' => $payload,
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
