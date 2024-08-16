<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PermissionController extends Controller
{
    public function index()
    {
        $permissions = Permission::all();
        return response()->json($permissions);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:roles|max:255',
            'path' => 'nullable|max:255',
            'element' => 'nullable|max:255',
            'permission' => 'nullable|max:255',
            'description' => 'nullable|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $permission = Permission::create([
            'name' => $request->name,
            'path' => $request->path,
            'element' => $request->element,
            'permission' => $request->permission,
            'description' => $request->description,
        ]);

        return response()->json($permission, 201);
    }

    public function show($id)
    {
        $permission = Permission::findOrFail($id);
        return response()->json($permission);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|unique:permissions,name,'.$id.'|max:255',
            'description' => 'nullable|max:255',
        ]);

        $permission = Permission::findOrFail($id);

        $permission->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return response()->json($permission);
    }

    public function destroy($id)
    {
        $permission = Permission::findOrFail($id);
        $permission->delete();
        return response()->json(null, 204);
    }
}
