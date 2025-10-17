<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserRoleController extends Controller
{
public function getUserRoles($id)
{
    $roles = DB::connection('oracle')
        ->table('LMS.USER_ROLE as ur')
        ->join('LMS.ROLES as r', 'ur.ROLE_ID', '=', 'r.ROLE_ID')
        ->where('ur.USER_ID', $id)
        ->where(function ($q) {
            $q->whereNull('ur.DELETED_AT')
              ->orWhere('ur.DELETED_AT', '');
        })
        ->select('r.ROLE_ID', 'r.ROLE_NAME')
        ->get();

    return response()->json($roles);
}


public function assignRoles(Request $request, $id)
{
    $authId = auth()->user()->id;
    $newRoles = $request->input('roles', []);

    // 1️⃣ Soft delete roles not in the new list
    DB::connection('oracle')
        ->table('LMS.USER_ROLE')
        ->where('USER_ID', $id)
        ->whereNotIn('ROLE_ID', $newRoles)
        ->whereNull('DELETED_AT')
        ->update([
            'DELETED_AT' => now()->format('Y-m-d H:i:s'),
            'DELETED_BY' => $authId,
        ]);

    // 2️⃣ For each new role → restore if deleted OR insert if new
    foreach ($newRoles as $roleId) {
        $existing = DB::connection('oracle')
            ->table('LMS.USER_ROLE')
            ->where('USER_ID', $id)
            ->where('ROLE_ID', $roleId)
            ->first();

        if ($existing) {
            // Safely check deleted field in both naming styles
            $deletedAt = null;
            if (property_exists($existing, 'DELETED_AT')) {
                $deletedAt = $existing->DELETED_AT;
            } elseif (property_exists($existing, 'deleted_at')) {
                $deletedAt = $existing->deleted_at;
            }

            if (!is_null($deletedAt) && $deletedAt !== '') {
                // 🔄 Restore previously deleted role
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
            }
            // else already active, skip
        } else {
            // 🆕 Insert brand new record
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

public function countRoles()
{
    $authId = auth()->user()->id;

    // ✅ Step 1: Check if logged-in user has Admin role
    $isAdmin = DB::connection('oracle')
        ->table('LMS.USER_ROLE as ur')
        ->join('LMS.ROLES as r', 'ur.ROLE_ID', '=', 'r.ROLE_ID')
        ->where('ur.USER_ID', $authId)
        ->whereNull('ur.DELETED_AT')
        ->where('r.ROLE_NAME', 'Admin')
        ->exists();

    if (!$isAdmin) {
        return response()->json(['error' => 'Access denied. Only admins can view role counts.'], 403);
    }

    //  Step 2: If admin, return counts
    $counts = DB::connection('oracle')
        ->table('LMS.USER_ROLE as ur')
        ->join('LMS.ROLES as r', 'ur.ROLE_ID', '=', 'r.ROLE_ID')
        ->whereNull('ur.DELETED_AT') // only count active roles
        ->select(
            'r.ROLE_NAME',
            DB::raw('COUNT(DISTINCT ur.USER_ID) as total_users')
        )
        ->groupBy('r.ROLE_NAME')
        ->get();

    return response()->json($counts);
}



}
