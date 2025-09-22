<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    // Show roles page
    public function indexPage()
    {
        $roles = Role::with('permissions')->get();
        $permissions = Permission::all();

        return Inertia::render('Roles', [
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }

    // List roles (with permissions)
    public function index()
    {
        $userId = auth()->user()->id;

        return Role::with('permissions')
            ->where('USER_ID', $userId)
            ->whereNull('DELETED_AT')
            ->get();
    }

  public function show($id)
{
    $userId = auth()->user()->id;

    $role = Role::where('ROLE_ID', $id)
        ->where('USER_ID', $userId)
        ->firstOrFail();

    $permissions = DB::connection('oracle')
        ->table('LMS.PERMISSIONS as p')
        ->join('LMS.PERMISSION_ROLE as pr', 'p.PERMISSION_ID', '=', 'pr.PERMISSION_ID')
        ->where('pr.ROLE_ID', $id)
        ->whereNull('pr.DELETED_AT')
        ->select(
            DB::raw('p.PERMISSION_ID as "PERMISSION_ID"'),
            DB::raw('p.PERMISSION_NAME as "PERMISSION_NAME"')
        )
        ->get();

    $role->permissions = $permissions;

    return response()->json($role);
}


    // Store new role
   public function store(Request $request)
{
    $request->validate([
        'ROLE_NAME' => 'required|string|max:255',
    ]);

    $userId = auth()->user()->id;

    //  Check duplicate (including soft-deleted)
    $existsAll = Role::where('ROLE_NAME', $request->ROLE_NAME)->exists();
    if ($existsAll) {
        return response()->json([
            'error' => 'Role name already exists (even in deleted records)'
        ], 422);
    }

    //  Active-only check
    $exists = Role::where('ROLE_NAME', $request->ROLE_NAME)
        ->whereNull('DELETED_AT')
        ->exists();
    if ($exists) {
        return response()->json(['error' => 'Role name already exists'], 422);
    }

    $role = Role::create([
        'ROLE_NAME'  => $request->ROLE_NAME,
        'USER_ID'    => $userId,
        'CREATED_BY' => $userId,
        'CREATED_AT' => now()->format('Y-m-d H:i:s'),
    ]);

    return response()->json($role->load('permissions'), 201);
}


    // Update role
   public function update(Request $request, $id)
{
    $userId = auth()->user()->id;

    $data = [
        'UPDATED_BY' => $userId,
        'UPDATED_AT' => now()->format('Y-m-d H:i:s'),
    ];

    if ($request->has('ROLE_NAME')) {
        $data['ROLE_NAME'] = $request->ROLE_NAME;
    }

    $updated = DB::table('LMS.ROLES')
        ->where('ROLE_ID', $id)
        ->where('USER_ID', $userId)
        ->update($data);

    if (!$updated) {
        return response()->json(['error' => 'Role not found'], 404);
    }

    $role = DB::table('LMS.ROLES')
        ->where('ROLE_ID', $id)
        ->where('USER_ID', $userId)
        ->first();

    return response()->json($role, 200);
}


    // Soft delete
 public function destroy($id)
{
    $userId = auth()->user()->id;

    $data = [
        'DELETED_BY' => $userId,
        'DELETED_AT' => now()->format('Y-m-d H:i:s'),
    ];

    $deleted = DB::table('LMS.ROLES')
        ->where('ROLE_ID', $id)
        ->where('USER_ID', $userId)
        ->update($data);

    if (!$deleted) {
        return response()->json(['error' => 'Role not found'], 404);
    }

    return response()->json(['message' => 'Role deleted successfully']);
}


// Assign permissions to role
public function assignPermissions(Request $request, $roleId)
{
    $request->validate([
        'permissions' => 'required|array',
    ]);

    $userId = auth()->user()->id;
    $role   = Role::findOrFail($roleId);

    $newPermissions = collect($request->permissions)->map(fn($id) => (int) $id)->all();

    // Soft delete removed permissions
    DB::connection('oracle')
        ->table('lms.permission_role')
        ->where('role_id', $roleId)
        ->whereNotIn('permission_id', $newPermissions)
        ->whereNull('deleted_at')
        ->update([
            'deleted_by' => $userId,
            'deleted_at' => now()->format('Y-m-d H:i:s'),
        ]);

    // Add or restore permissions
    foreach ($newPermissions as $permissionId) {
        $exists = DB::connection('oracle')
            ->table('lms.permission_role')
            ->select('role_id', 'permission_id', 'deleted_at')
            ->where('role_id', $roleId)
            ->where('permission_id', $permissionId)
            ->first();

        if ($exists) {
            //soft-deleted → restore it
            if (!is_null($exists->deleted_at)) {
                DB::connection('oracle')
                    ->table('lms.permission_role')
                    ->where('role_id', $roleId)
                    ->where('permission_id', $permissionId)
                    ->update([
                        'deleted_at' => null,
                        'deleted_by' => null,
                        'updated_by' => $userId,
                        'updated_at' => now()->format('Y-m-d H:i:s'),
                    ]);
            }
        } else {
            // Insert new record
            DB::connection('oracle')
                ->table('lms.permission_role')
                ->insert([
                    'role_id'       => $roleId,
                    'permission_id' => $permissionId,
                    'user_id'       => $userId,
                    'created_by'    => $userId,
                    'created_at'    => now()->format('Y-m-d H:i:s'),
                ]);
        }
    }

    return response()->json([
        'message' => 'Permissions updated successfully',
        'role'    => $role->load('permissions'),
    ]);
}


}
