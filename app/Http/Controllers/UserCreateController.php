<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class UserCreateController extends Controller
{
    /**
     * Create a new user with all required information
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function createUser(Request $request)
    {
        // Validate all input fields
        $validator = Validator::make($request->all(), [
            // Basic Information
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'nullable|string',
            'role_id' => 'nullable|integer',
            'profile_for' => 'required|string|in:self,son,daughter,brother,sister,relative,friend',
            'profile_created_by' => 'required|string|in:self,parent,brother,sister,relative,friend',
            'mobile_number' => 'required|string|max:20',
            'whatsapp' => 'nullable|string|max:20',
            'date_of_birth' => 'required|date|before_or_equal:' . Carbon::now()->subYears(18)->format('Y-m-d'),
            'gender' => 'required|string|in:Male,Female,Other',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'father_name' => 'nullable|string|max:255',
            'mother_name' => 'nullable|string|max:255',

            // Personal Details
            'marital_status' => 'required|string|in:Never Married,Widowed,Divorced,Awaiting Divorce,Annulled',
            'religion' => 'required|string|max:255',
            'community' => 'required|string|max:255',
            'mother_tongue' => 'required|string|max:255',
            'sub_community' => 'nullable|string|max:255',
            'nationality' => 'required|string|max:255',

            // Education & Career
            'highest_qualification' => 'required|string|max:255',
            'college_name' => 'required|string|max:255',
            'working_sector' => 'required|string|max:255',
            'profession' => 'required|string|max:255',
            'profession_details' => 'required|string|max:500',
            'monthly_income' => 'required|numeric',
            'father_occupation' => 'required|string|max:255',
            'mother_occupation' => 'required|string|max:255',

            // Location
            'living_country' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'city_living_in' => 'required|string|max:255',
            'currently_living_in' => 'required|string|max:255',

            // Family Details
            'family_details' => 'required|string|max:500',
            'family_values' => 'required|string|max:255',
            'family_location' => 'required|string|max:255',
            'family_type' => 'required|string|max:255',
            'family_native_place' => 'required|string|max:255',
            'total_siblings' => 'required|integer|min:0',
            'siblings_married' => 'required|integer|min:0',
            'siblings_not_married' => 'required|integer|min:0',

            // Physical Attributes
            'height' => 'required|string|regex:/^\d+ft\s*\d+in$/',
            'birth_place' => 'required|string|max:255',
            'personal_values' => 'required|string|max:500',
            'disability' => 'nullable|string|max:255',
            'weight' => 'nullable|numeric',
            'bodyType' => 'nullable|string|max:255',
            'race' => 'nullable|string|max:255',
            'blood_group' => 'required|string|max:10',
            'mother_status' => 'required|string|max:255',

            // About & Lifestyle
            'about_myself' => 'required|string|max:1000',
            'partner_age' => 'required|string|max:255',
            'smoking' => 'required|string|max:255',
            'other_lifestyle_preferences' => 'required|string|max:500',
            'drinking' => 'required|string|max:255',
            'diet' => 'required|string|max:255',

            // Social Media
            'facebook' => 'nullable|url|max:255',
            'instagram' => 'nullable|url|max:255',
            'twitter' => 'nullable|url|max:255',
            'linkedin' => 'nullable|url|max:255',

            // Partner Preferences (arrays for multiple selections)
            'partner_marital_statuses' => 'nullable|array',
            'partner_marital_statuses.*' => 'string|in:Never Married,Widowed,Divorced,Awaiting Divorce,Annulled',
            'partner_religions' => 'nullable|array',
            'partner_religions.*' => 'string|max:255',
            'partner_communities' => 'nullable|array',
            'partner_communities.*' => 'string|max:255',
            'partner_mother_tongues' => 'nullable|array',
            'partner_mother_tongues.*' => 'string|max:255',
            'partner_qualifications' => 'nullable|array',
            'partner_qualifications.*' => 'string|max:255',
            'partner_working_withs' => 'nullable|array',
            'partner_working_withs.*' => 'string|max:255',
            'partner_professions' => 'nullable|array',
            'partner_professions.*' => 'string|max:255',
            'partner_professional_details' => 'nullable|array',
            'partner_countries' => 'nullable|array',
            'partner_countries.*' => 'string|max:255',
            'partner_states' => 'nullable|array',
            'partner_states.*' => 'string|max:255',
            'partner_cities' => 'nullable|array',
            'partner_cities.*' => 'string|max:255',
        ]);

        // Return validation errors if any
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Begin database transaction
            \DB::beginTransaction();

            // Create the user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role ?? 'user',
                'role_id' => $request->role_id ?? 2, // Default to regular user role
                'profile_for' => $request->profile_for,
                'profile_created_by' => $request->profile_created_by,
                'mobile_number' => $request->mobile_number,
                'whatsapp' => $request->whatsapp ?? $request->mobile_number,
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'father_name' => $request->father_name,
                'mother_name' => $request->mother_name,
                'marital_status' => $request->marital_status,
                'religion' => $request->religion,
                'community' => $request->community,
                'mother_tongue' => $request->mother_tongue,
                'sub_community' => $request->sub_community,
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
                'state' => $request->state,
                'city_living_in' => $request->city_living_in,
                'currently_living_in' => $request->currently_living_in,
                'family_details' => $request->family_details,
                'family_values' => $request->family_values,
                'family_location' => $request->family_location,
                'family_type' => $request->family_type,
                'family_native_place' => $request->family_native_place,
                'total_siblings' => $request->total_siblings,
                'siblings_married' => $request->siblings_married,
                'siblings_not_married' => $request->siblings_not_married,
                'height' => $request->height,
                'birth_place' => $request->birth_place,
                'personal_values' => $request->personal_values,
                'disability' => $request->disability,
                'weight' => $request->weight,
                'bodyType' => $request->bodyType,
                'race' => $request->race,
                'blood_group' => $request->blood_group,
                'mother_status' => $request->mother_status,
                'about_myself' => $request->about_myself,
                'partner_age' => $request->partner_age,
                'username' => explode('@', $request->email)[0],
                'smoking' => $request->smoking,
                'other_lifestyle_preferences' => $request->other_lifestyle_preferences,
                'drinking' => $request->drinking,
                'diet' => $request->diet,
                'status' => 'active', // Default to active status
                'contact_view_balance' => 0, // Default to 0 views
                'facebook' => $request->facebook,
                'instagram' => $request->instagram,
                'twitter' => $request->twitter,
                'linkedin' => $request->linkedin,
                'email_verified_at' => now(),
            ]);

            // Add partner preferences if provided
            if ($request->has('partner_marital_statuses')) {
                foreach ($request->partner_marital_statuses as $status) {
                    $user->partnerMaritalStatuses()->create(['marital_status' => $status]);
                }
            }

            if ($request->has('partner_religions')) {
                foreach ($request->partner_religions as $religion) {
                    $user->partnerReligions()->create(['religion' => $religion]);
                }
            }

            if ($request->has('partner_communities')) {
                foreach ($request->partner_communities as $community) {
                    $user->partnerCommunities()->create(['community' => $community]);
                }
            }

            if ($request->has('partner_mother_tongues')) {
                foreach ($request->partner_mother_tongues as $tongue) {
                    $user->partnerMotherTongues()->create(['mother_tongue' => $tongue]);
                }
            }

            if ($request->has('partner_qualifications')) {
                foreach ($request->partner_qualifications as $qualification) {
                    $user->partnerQualification()->create(['qualification' => $qualification]);
                }
            }

            if ($request->has('partner_working_withs')) {
                foreach ($request->partner_working_withs as $workingWith) {
                    $user->partnerWorkingWith()->create(['working_with' => $workingWith]);
                }
            }

            if ($request->has('partner_professions')) {
                foreach ($request->partner_professions as $profession) {
                    $user->partnerProfessions()->create(['profession' => $profession]);
                }
            }

            if ($request->has('partner_professional_details')) {
                $user->partnerProfessionalDetails()->create($request->partner_professional_details);
            }

            if ($request->has('partner_countries')) {
                foreach ($request->partner_countries as $country) {
                    $user->partnerCountries()->create(['country' => $country]);
                }
            }

            if ($request->has('partner_states')) {
                foreach ($request->partner_states as $state) {
                    $user->partnerStates()->create(['state' => $state]);
                }
            }

            if ($request->has('partner_cities')) {
                foreach ($request->partner_cities as $city) {
                    $user->partnerCities()->create(['city' => $city]);
                }
            }

            // Commit the transaction
            \DB::commit();

            // Return success response
            return response()->json([
                'status' => true,
                'message' => 'User created successfully',
                'user' => $user->toArrayWithRelations(),
                'profile_completion' => $user->profile_completion
            ], 201);

        } catch (\Exception $e) {
            // Rollback the transaction on error
            \DB::rollBack();

            // Log the error
            \Log::error('User creation failed: ' . $e->getMessage());

            // Return error response
            return response()->json([
                'status' => false,
                'message' => 'User creation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
