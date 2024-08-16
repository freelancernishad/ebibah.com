<?php

namespace App\Http\Controllers\Auth\orgs;
use App\Http\Controllers\Controller;

use App\Models\Organization;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
class OrganizationAuthController extends Controller
{
  // Organization registration
  public function register(Request $request)
  {
       $validator = Validator::make($request->all(), [
          'logo' => 'nullable|string|max:255',
          'name' => 'required|string|max:255',
          'mobile' => [
              'required',
              'string',
              'min:11',
              'max:11',
              Rule::unique('organizations'),
          ],
          'email' => 'required|string|email|max:255|unique:organizations',
          'whatsapp_number' => 'required|string|max:15',
          'division' => 'required|string|max:255',
          'district' => 'required|string|max:255',
          'thana' => 'required|string|max:255',
          'union' => 'required|string|max:255',
          'password' => 'required|string|min:8',
      ]);

      if ($validator->fails()) {
          return response()->json(['errors' => $validator->errors()], 400);
      }

      $organization = new Organization([
          'logo' => $request->logo,
          'name' => $request->name,
          'mobile' => $request->mobile,
          'email' => $request->email,
          'whatsapp_number' => $request->whatsapp_number,
          'division' => $request->division,
          'district' => $request->district,
          'thana' => $request->thana,
          'union' => $request->union,
          'password' => Hash::make($request->password),
      ]);

      $organization->save();



      $token = JWTAuth::fromUser($organization);
      return response()->json(['token' => $token], 201);
      // You can generate a JWT token here and return it if needed
      // Refer to your JWT library's documentation for this

      return response()->json(['message' => 'Organization registered successfully'], 201);
  }

    public function login(Request $request)
    {
          $credentials = $request->only('email', 'password');

        if (Auth::guard('organization')->attempt($credentials)) {
            $organization = Auth::guard('organization')->user();
            $token = JWTAuth::fromUser($organization);
            // $token = $organization->createToken('access_token')->accessToken;
            return response()->json(['token' => $token]);
        }

        return response()->json(['error' => 'Unauthorized'], 401);
    }


    public function checkTokenExpiration(Request $request)
{
    // $token = $request->token;
    $token = $request->bearerToken();

    try {
        // Check if the token is valid and get the authenticated organization
        $user = Auth::guard('organization')->setToken($token)->authenticate();

        // Check if the token's expiration time (exp) is greater than the current timestamp
        $isExpired = JWTAuth::setToken($token)->checkOrFail();

        return response()->json(['message' => 'Token is valid', 'organization' => $user], 200);
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
        $organization = $request->user('organization');
        if ($organization) {
            return response()->json(['message' => 'Token is valid']);
        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
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
       $user = Auth::guard('organization')->user();
        if (!Hash::check($request->current_password, $user->password)) {
           return response()->json(['message' => 'Current password is incorrect.'], 400);
        }
        $user->password = Hash::make($request->new_password);
        $user->save();
        return response()->json(['message' => 'Password changed successfully.'], 200);
    }


}
