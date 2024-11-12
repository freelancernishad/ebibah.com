<?php
// namespace App\Http\Controllers\Auth;
namespace App\Http\Controllers\Auth\users;
use App\Models\User;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Notifications\VerifyEmail;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use App\Mail\RegistrationSuccessful;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Notifications\OtpNotification;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{

    public function login(Request $request)
    {
        User::setApplyActiveScope(false);

        if ($request->has('access_token')) {
            // Validate access_token
            $validator = Validator::make($request->all(), [
                'access_token' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Verify the access token using Google's tokeninfo endpoint
            $response = Http::get('https://www.googleapis.com/oauth2/v3/userinfo', [
                'access_token' => $request->access_token,
            ]);

            if ($response->failed()) {
                return response()->json(['error' => 'Invalid access token'], 400);
            }

            $userData = $response->json();

            // Check if the email exists in your database
            $user = User::where('email', $userData['email'])->first();
            if (!$user) {
                // If user does not exist, create a new user
                $username = explode('@', $userData['email'])[0]; // Extract username from email
                $user = User::create([
                    'username' => $username,
                    'email' => $userData['email'],
                    'first_name' => $userData['given_name']  ?? '',
                    'last_name' => $userData['family_name']  ?? '',
                    'name' => $userData['name'],
                    'password' => Hash::make(Str::random(16)), // Generate a random password
                    'step' => 1, // Set step value to 1
                    'email_verified_at' => now(),
                ]);
            } else {
                // Check if the user is banned
                if ($user->status === 'banned') {
                    Auth::logout();
                    return response()->json(['error' => 'Your account has been banned. Please contact support for further assistance.'], 403);
                }


                // Check if email is not verified
                if (is_null($user->email_verified_at)) {
                    // If not verified, set email_verified_at to current timestamp
                    $user->email_verified_at = now();
                    $user->save();
                }
            }

            // Login the user
            Auth::login($user);

            // Build the payload including the username, step, and email verification status
            $payload = [
                'email' => $user->email,
                'mobile_number' => $user->mobile_number,
                'username' => $user->username, // Include username here
                'name' => $user->name, // Include name here
                'step' => $user->step, // Include step here
                'verified' => $user->hasVerifiedEmail(), // Add email verification status
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

                // Check if the user is banned
                if ($user->status === 'banned') {
                    Auth::logout();
                    return response()->json(['error' => 'Your account has been banned. Please contact support for further assistance.'], 403);
                }


                // Build the payload including the username, step, and email verification status
                $payload = [
                    'email' => $user->email,
                    'mobile_number' => $user->mobile_number,
                    'username' => $user->username, // Include username here
                    'name' => $user->name, // Include name here
                    'step' => $user->step, // Include step here
                    'verified' => $user->hasVerifiedEmail(), // Add email verification status
                ];

                $token = JWTAuth::fromUser($user); // Generate JWT token for the user
                return response()->json(['token' => $token, 'user' => $payload], 200);
            }

            return response()->json(['message' => 'The email or password you entered is incorrect. Please try again.'], 401);

        }
    }

    public function resendVerificationLink(Request $request)
    {
        User::setApplyActiveScope(false);
        // Validate the request
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            // Optionally validate verify_url if it's part of the request
            'verify_url' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Find the user by email
        $user = User::where('email', $request->email)->first();

        // Check if the user exists and if the email is not already verified
        if (!$user || $user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email is either already verified or user does not exist.'], 400);
        }

        // Generate a new verification token
        $verificationToken = Str::random(60); // Generate a unique token
        $user->email_verification_hash = $verificationToken;
        $user->save();

        // Build the new verification URL
        $verify_url = $request->verify_url;

        // Resend the verification email
        $user->notify(new VerifyEmail($user, $verify_url));

        return response()->json(['message' => 'Verification link has been sent.'], 200);
    }


    public function resendOtp(Request $request)
{
    // Validate the request
    $validator = Validator::make($request->all(), [
        'email' => 'required|email|exists:users,email',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    // Find the user by email
    $user = User::where('email', $request->email)->first();

    // Check if the user exists and if the email is not already verified
    if (!$user || $user->hasVerifiedEmail()) {
        return response()->json(['message' => 'Email is either already verified or user does not exist.'], 400);
    }

    // Generate a new 6-digit numeric OTP
    $otp = random_int(100000, 999999); // Generates a random integer between 100000 and 999999
    $user->otp = Hash::make($otp); // Store hashed OTP
    $user->otp_expires_at = now()->addMinutes(5); // Set expiration time
    $user->save();

    // Send the new OTP via email
    $user->notify(new OtpNotification($otp));

    return response()->json(['message' => 'A new OTP has been sent to your email.'], 200);
}




public function checkTokenExpiration(Request $request)
{
    User::setApplyActiveScope(false);

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
        return response()->json(['message' => 'Token is valid', 'user' => $user->toArrayProfileWithoutRelation() ], 200);
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
    User::setApplyActiveScope(false);
    $user = Auth::guard('web')->user();
    if ($user) {
        return response()->json(['message' => 'Token is valid']);
    } else {
        return response()->json(['error' => 'Unauthorized'], 401);
    }
}
    public function logout(Request $request)
    {
        User::setApplyActiveScope(false);
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
        // Check if access_token is present
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
                'email_verified_at' => now(),
            ]);

            $user->save();


            $data = [
                'name' => $user->name,
            ];
            Mail::to($user->email)->send(new RegistrationSuccessful($data));

        } else {
            // Validate all fields for traditional registration
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:users',
                'mobile_number' => 'required',
                'password' => 'required|string|min:6|confirmed', // This validates both password and password_confirmation
                'date_of_birth' => 'nullable|date',
                'religion' => 'nullable|string|max:255',
                'gender' => 'nullable|string|max:10',
                'verify_url' => 'nullable|url', // Ensure verify_url is a valid URL
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 400);
            }

            // Create a new user with data from request
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'mobile_number' => $request->mobile_number,
                'password' => Hash::make($request->password),
                'date_of_birth' => $request->date_of_birth,
                'religion' => $request->religion,
                'gender' => $request->gender,
                'step' => 1, // Set step value to 1
                'email_verification_hash' => Str::random(60),
            ]);


                // Generate verification URL
        // $verify_url = $request->verify_url;

        // Send email verification
        // $user->notify(new VerifyEmail($user, $verify_url));




       // Generate a 6-digit numeric OTP
        $otp = random_int(100000, 999999); // Generates a random integer between 100000 and 999999
        $user->otp = Hash::make($otp); // Store hashed OTP
        $user->otp_expires_at = now()->addMinutes(5); // Set expiration time
        $user->save();

        // Send OTP via email
        $user->notify(new OtpNotification($otp));


        }



        // Build the payload including the username, step, and email verification status
        $payload = [
            'name' => $user->name,
            'email' => $user->email,
            'mobile_number' => $user->mobile_number,
            'username' => $user->username ?? $user->name, // Include username or name here
            'step' => $user->step, // Include step here
            'verified' => $user->hasVerifiedEmail(), // Add email verification status
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
            User::setApplyActiveScope(false);
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
