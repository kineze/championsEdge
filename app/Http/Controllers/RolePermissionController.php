<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;

class RolePermissionController extends Controller
{
    public function index()
    {
        // Always load roles for the 'web' guard
        return Role::where('guard_name', 'web')
            ->select('id', 'name')
            ->orderBy('name')
            ->get();
    }

    /**
     * Store a newly created role.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles,name',
        ]);

        // Force guard to 'web' to prevent sanctum mismatch
        $role = Role::create([
            'name' => $request->name,
            'guard_name' => 'web',
        ]);

        return response()->json([
            'message' => 'Role created successfully',
            'role' => $role,
        ]);
    }

    /**
     * Display a specific role with its permissions.
     */
    public function show($id)
    {
        // Ensure we load using correct guard
        $role = Role::findById($id, 'web');

        return response()->json([
            'role' => $role,
            'permissions' => $role->permissions->pluck('name'),
        ]);
    }

    /**
     * Update the given role.
     */
    public function update(Request $request, $id)
    {
        $role = Role::findById($id, 'web');

        $request->validate([
            'name' => 'required|unique:roles,name,' . $role->id,
        ]);

        $role->update(['name' => $request->name]);

        return response()->json([
            'message' => 'Role updated successfully',
        ]);
    }

    /**
     * Delete a role.
     */
    public function destroy($id)
    {
        $role = Role::findById($id, 'web');
        $role->delete();

        return response()->json([
            'message' => 'Role deleted successfully',
        ]);
    }

    /**
     * List all permissions.
     */
    public function permissions()
    {
        return Permission::where('guard_name', 'web')
            ->select('id', 'name')
            ->orderBy('name')
            ->get();
    }

    /**
     * Sync permissions for a role.
     */
    public function syncPermissions(Request $request, $id)
    {
        $role = Role::findById($id, 'web');

        // Create missing permissions automatically if needed
        $requestedPermissions = collect($request->permissions ?? [])
            ->map(function ($permName) {
                return Permission::firstOrCreate([
                    'name' => $permName,
                    'guard_name' => 'web',
                ])->name;
            })
            ->toArray();

        // Sync permissions safely
        $role->syncPermissions($requestedPermissions);

        Log::info("Permissions synced for role: {$role->name}", [
            'permissions' => $requestedPermissions,
        ]);

        return response()->json([
            'message' => 'Permissions updated successfully',
        ]);
    }
}
