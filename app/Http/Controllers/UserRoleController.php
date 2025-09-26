<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserRoleController extends Controller
{
    // Fetch roles assigned to a user
    public function getUserRoles($id)
    {
        $roles = DB::connection('oracle')
            ->table('LMS.USER_ROLE as ur')
            ->join('LMS.ROLES as r', 'ur.ROLE_ID', '=', 'r.ROLE_ID')
            ->where('ur.USER_ID', $id)
            ->select('r.ROLE_ID', 'r.ROLE_NAME')
            ->get();

        return response()->json($roles);
    }

    // Assign roles (bulk update)
    public function assignRoles(Request $request, $id)
    {
        $roleIds = $request->input('roles', []);

        // Remove old roles
        DB::connection('oracle')
            ->table('LMS.USER_ROLE')
            ->where('USER_ID', $id)
            ->delete();

        // Insert new roles
        foreach ($roleIds as $roleId) {
            DB::connection('oracle')
                ->table('LMS.USER_ROLE')
                ->insert([
                    'USER_ID' => $id,
                    'ROLE_ID' => $roleId,
                ]);
        }

        return response()->json(['message' => 'Roles updated successfully']);
    }

    // Remove a single role
    public function removeRole($id, $roleId)
    {
        DB::connection('oracle')
            ->table('LMS.USER_ROLE')
            ->where('USER_ID', $id)
            ->where('ROLE_ID', $roleId)
            ->delete();

        return response()->json(['message' => 'Role removed successfully']);
    }
}
