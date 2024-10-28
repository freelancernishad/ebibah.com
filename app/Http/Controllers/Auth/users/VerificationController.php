<?php

namespace App\Http\Controllers\Auth\users;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use App\Mail\RegistrationSuccessful;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class VerificationController extends Controller
{

    public function verifyEmail(Request $request, $hash)
    {
        User::setApplyActiveScope(false);
        // Find the user by the hash
        $user = User::where('email_verification_hash', $hash)->first();

        if (!$user) {
            return response()->json(['error' => 'Invalid or expired verification link.'], 400);
        }

        // Check if the email is already verified
        if ($user->hasVerifiedEmail()) {
            // Generate a new token for the user
            $token = JWTAuth::fromUser($user);

            return response()->json([
                'message' => 'Email already verified.',
                'user' => [
                    'email' => $user->email,
                    'name' => $user->name,
                    'username' => $user->username,
                    'step' => $user->step,
                    'verified' => true, // Email was already verified
                ],
                'token' => $token // Return the new token
            ], 200);
        }

        // If not verified, verify the user's email
        $user->markEmailAsVerified();

        // Generate a new token for the user after verification
        $token = JWTAuth::fromUser($user);

        return response()->json([
            'message' => 'Email verified successfully.',
            'user' => [
                'email' => $user->email,
                'name' => $user->name,
                'username' => $user->username,
                'step' => $user->step,
                'verified' => true, // Email was already verified
            ],
            'token' => $token // Return the new token
        ], 200);
    }




    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|digits:6', // Validate OTP as 6 digits
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // Find the user by email
        $user = User::where('email', $request->email)->first();

        // Check if the OTP has expired
        if ($user->otp_expires_at < now()) {
            return response()->json(['error' => 'OTP has expired'], 400);
        }

        // Check if the provided OTP matches the stored OTP
        if (Hash::check($request->otp, $user->otp)) {
            // Check if the email is already verified
            if ($user->hasVerifiedEmail()) {
                // Generate a new token for the user
                $token = JWTAuth::fromUser($user);

                return response()->json([
                    'message' => 'Email already verified.',
                    'user' => [
                        'email' => $user->email,
                        'name' => $user->name,
                        'username' => $user->username,
                        'step' => $user->step,
                        'verified' => true, // Email was already verified
                    ],
                    'token' => $token // Return the new token
                ], 200);
            }

            // If not verified, verify the user's email
            $user->markEmailAsVerified();

            // Clear the OTP from the user model
            $user->otp = null;
            $user->otp_expires_at = null; // Clear expiration time
            $user->save();

            // Generate a new token for the user after verification
            $token = JWTAuth::fromUser($user);

            $data = [
                'name' => $user->name,
            ];
            Mail::to($user->email)->send(new RegistrationSuccessful($data));


            return response()->json([
                'message' => 'Email verified successfully.',
                'user' => [
                    'email' => $user->email,
                    'name' => $user->name,
                    'username' => $user->username,
                    'step' => $user->step,
                    'verified' => true, // Email was verified
                ],
                'token' => $token // Return the new token
            ], 200);
        }

        return response()->json(['error' => 'Invalid OTP'], 400);
    }



}
