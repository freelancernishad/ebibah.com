<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Permission;

class RolePermissionController extends Controller
{
    public function attachPermission(Role $role, Permission $permission)
    {
      
        $role->permissions()->attach($permission);
        return response()->json(null, 204);
    }

    public function addPermissionsToRole(Request $request, $roleId)
    {
      // Validate the request data
      $request->validate([
        'permission_ids' => 'required|array',
        'permission_ids.*' => 'exists:permissions,id',
    ]);

    // Get the role
    $role = Role::findOrFail($roleId);
    
    // Get the permission IDs from the request
    $permissionIds = $request->input('permission_ids');
    
    // Detach all previous permissions associated with the role
    $role->permissions()->detach();
    
    // Attach the new permissions to the role
    foreach ($permissionIds as $permissionId) {
        $permission = Permission::findOrFail($permissionId);
        $role->permissions()->attach($permissionId);
    }

    // Optionally, you can return a response indicating success
    return response()->json(['message' => 'Permissions added to role successfully', 'role' => $role], 200);
    }


    public function detachPermission(Role $role, Permission $permission)
    {
        $role->permissions()->detach($permission);
        return response()->json(null, 204);
    }
}
