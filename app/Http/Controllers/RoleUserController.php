<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RoleUserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::all();
        return response()->json(['users' => $users], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users|max:255',
            'password' => 'required|string|min:6',
            'mobile' => 'required|string|max:20',
            'role_id' => 'required|exists:roles,id',
            // Add other validation rules for your fields here
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // Get the role based on role_id
        $role = Role::findOrFail($request->role_id);

        $user = User::create(array_merge($request->all(), ['role' => $role->name]));
        return response()->json(['user' => $user], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::findOrFail($id);
        return response()->json(['user' => $user], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'email' => 'string|email|max:255|unique:users,email,'.$id,
            'password' => 'string|min:6',
            'mobile' => 'string|max:20',
            'role_id' => 'exists:roles,id',
            // Add other validation rules for your fields here
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // Get the role based on role_id
        if ($request->has('role_id')) {
            $role = Role::findOrFail($request->role_id);
            $request->merge(['role' => $role->name]);
        }

        $user = User::findOrFail($id);
        $user->update($request->all());
        return response()->json(['user' => $user], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        if($user){
            $user->delete();
            return ['status'=>'success'];
        }
        return ['status'=>'not found'];
    }
}
