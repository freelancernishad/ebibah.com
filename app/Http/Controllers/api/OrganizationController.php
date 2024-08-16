<?php

namespace App\Http\Controllers\api;

use App\Models\User;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Tymon\JWTAuth\Facades\JWTAuth;


use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;

class OrganizationController extends Controller
{


    // Organization update
    public function update(Request $request, $id)
    {


        $organization = Organization::find($id);


        $validator = Validator::make($request->all(), [
            'mobile' => [
                'string',
                'max:15',
                Rule::unique('organizations')->ignore($organization->id),
            ],
            // Add validation rules for other fields as needed
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $organization->logo = $request->logo;
        $organization->name = $request->name;
        $organization->mobile = $request->mobile;
        $organization->whatsapp_number = $request->whatsapp_number;
        $organization->division = $request->division;
        $organization->district = $request->district;
        $organization->thana = $request->thana;
        $organization->union = $request->union;

        // Update other fields here

        $organization->save();

        return response()->json(['message' => 'Organization updated successfully'], 200);
    }

    // Organization delete
    public function delete($id)
    {
        $organization = Organization::find($id);

        if (!$organization) {
            return response()->json(['message' => 'Organization not found'], 404);
        }

        $organization->delete();

        return response()->json(['message' => 'Organization deleted successfully'], 200);
    }

    // Show organization details
    public function show($id)
    {
        $organization = Organization::with('doners')->find($id);

        if (!$organization) {
            return response()->json(['message' => 'Organization not found'], 404);
        }

        return response()->json($organization, 200);
    }



    public function getDonersByOrganization(Request $request)
    {
        $perpage = 20;
        if($request->perpage){
            $perpage = $request->perpage;
        }

        $organization = Auth::guard('organization')->user();
        if (!$organization) {
            return response()->json(['message' => 'Organization not found'], 404);
        }
        $users = User::with(['organization','donationLogs'])->where('org', $organization->id)->orderBy('id','desc')->paginate($perpage);
        return response()->json($users, 200);
    }


    public function listOrganizations(Request $request)
    {
        $unionFilter = $request->input('union'); // Get the union parameter from the request

        // Query organizations based on the union filter (if provided)
        $query = Organization::query();
        // $query->with(['doners']);
        if ($unionFilter) {
            $query->where('union', $unionFilter);
        }
        $perpage = 20;
        //  if($request->perpage){
        //      $perpage = $request->perpage;
        //  }
        $organizations = $query->get();

        return response()->json(['organizations' => $organizations]);
    }

    public function listOrganizationsWithPaginate(Request $request)
    {
        $unionFilter = $request->input('union'); // Get the union parameter from the request

        // Query organizations based on the union filter (if provided)
        $query = Organization::query();
        // $query->with(['doners']);
        if ($unionFilter) {
            $query->where('union', $unionFilter);
        }
        $perpage = 20;
        //  if($request->perpage){
        //      $perpage = $request->perpage;
        //  }
        $organizations = $query->paginate($perpage);

        return response()->json(['organizations' => $organizations]);
    }

}
