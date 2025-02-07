<?php

namespace App\Http\Controllers\Auth\admins;

use Carbon\Carbon;
use App\Models\Admin;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Notifications\SendTwoFactorCode;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;

class AdminAuthController extends Controller
{

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:admins',
            'password' => 'required|string|min:8',
            'role' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }


        $admin = new Admin([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'role' => $request->input('role') ?? 'editor',
            'password' => Hash::make($request->input('password')),
        ]);

        $admin->save();
        return response()->json(['message' => 'Admin registered successfully'], 201);
        // Return a response or redirect
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::guard('admin')->attempt($credentials)) {
            $admin = Auth::guard('admin')->user();


            $token = JWTAuth::fromUser($admin);
            // $token = $admin->createToken('access_token')->accessToken;
            return response()->json(['token' => $token,'payload'=>$admin]);




            // Generate and send OTP
            $admin->generateTwoFactorCode();
            $admin->notify(new SendTwoFactorCode($admin->two_factor_code));

            return response()->json([
                'message' => 'A two-factor authentication code has been sent to your email.',
                'requires_2fa' => true, // Indicate that 2FA is required
            ]);
        }

        return response()->json(['error' => 'Unauthorized'], 401);
    }

    public function verifyTwoFactor(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'two_factor_code' => 'required|string|size:6',
        ]);

        $admin = Admin::where('email', $request->email)->first();

        if (!$admin) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Check if the OTP is valid and not expired
        if ($admin->two_factor_code === $request->two_factor_code &&
            Carbon::now()->lt($admin->two_factor_expires_at)) {

            // Reset the OTP after successful verification
            $admin->resetTwoFactorCode();

            // Generate JWT token
            $token = JWTAuth::fromUser($admin);

            return response()->json(['token' => $token]);
        }

        return response()->json(['error' => 'Invalid or expired two-factor code'], 401);
    }





    public function checkTokenExpiration(Request $request)
{
    // $token = $request->token;
    $token = $request->bearerToken();

    try {
        // Check if the token is valid and get the authenticated admin
        $user = Auth::guard('admin')->setToken($token)->authenticate();

        // Check if the token's expiration time (exp) is greater than the current timestamp
        $isExpired = JWTAuth::setToken($token)->checkOrFail();

        return response()->json(['message' => 'Token is valid', 'admin' => $user], 200);
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




public function logout(Request $request)
{
    try {
        $token = $request->bearerToken();
        if ($token) {
            JWTAuth::setToken($token)->invalidate();
            return response()->json(['message' => 'Logged out successfully']);
        } else {
            return response()->json(['message' => 'Invalid token'], 401);
        }
    } catch (JWTException $e) {
        return response()->json(['message' => 'Error while processing token'], 500);
    }
}

    public function checkToken(Request $request)
    {
        $admin = $request->user('admin');
        if ($admin) {
            return response()->json(['message' => 'Token is valid']);
        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }


}
