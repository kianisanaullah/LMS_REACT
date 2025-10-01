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

 public function assignRoles(Request $request, $id)
{
    $authId = auth()->user()->id;
    $roleIds = $request->input('roles', []);

    // Soft-delete old roles instead of hard delete
    DB::connection('oracle')
        ->table('LMS.USER_ROLE')
        ->where('USER_ID', $id)
        ->whereNull('DELETED_AT')
        ->update([
            'DELETED_BY' => $authId,
            'DELETED_AT' => now()->format('Y-m-d H:i:s'),
        ]);

    // Insert new roles
    foreach ($roleIds as $roleId) {
        $exists = DB::connection('oracle')
            ->table('LMS.USER_ROLE')
            ->where('USER_ID', $id)
            ->where('ROLE_ID', $roleId)
            ->first();

        if ($exists && $exists->DELETED_AT) {
            // Restore if previously soft-deleted
            DB::connection('oracle')
                ->table('LMS.USER_ROLE')
                ->where('USER_ID', $id)
                ->where('ROLE_ID', $roleId)
                ->update([
                    'DELETED_AT' => null,
                    'DELETED_BY' => null,
                    'UPDATED_BY' => $authId,
                    'UPDATED_AT' => now()->format('Y-m-d H:i:s'),
                ]);
        } elseif (!$exists) {
            // Insert fresh
            DB::connection('oracle')
                ->table('LMS.USER_ROLE')
                ->insert([
                    'USER_ID'    => $id,
                    'ROLE_ID'    => $roleId,
                    'CREATED_BY' => $authId,
                    'CREATED_AT' => now()->format('Y-m-d H:i:s'),
                ]);
        }
    }

    return response()->json(['message' => 'Roles updated successfully']);
}

public function removeRole($id, $roleId)
{
    $authId = auth()->user()->id;

    DB::connection('oracle')
        ->table('LMS.USER_ROLE')
        ->where('USER_ID', $id)
        ->where('ROLE_ID', $roleId)
        ->whereNull('DELETED_AT')
        ->update([
            'DELETED_BY' => $authId,
            'DELETED_AT' => now()->format('Y-m-d H:i:s'),
        ]);

    return response()->json(['message' => 'Role removed successfully']);
}

}
