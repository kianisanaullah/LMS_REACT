<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permissionName)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $userId = $user->id ?? $user->ID ?? null;
        if (!$userId) {
            return response()->json(['error' => 'Unauthorized (no userId)'], 401);
        }

        // ✅ Step 1: Get user role IDs
        $roleIds = DB::connection('oracle')
            ->table('lms.user_role as ur')
            ->join('lms.roles as r', 'ur.role_id', '=', 'r.role_id')
            ->where('ur.user_id', $userId)
            ->whereNull('ur.deleted_at')
            ->whereNull('r.deleted_at')
            ->pluck('ur.role_id')
            ->toArray();

        if (empty($roleIds)) {
            return response()->json(['error' => 'Forbidden (no roles assigned)'], 403);
        }

        // ✅ Step 2: Get permissions for these roles
        $permissions = DB::connection('oracle')
            ->table('lms.permission_role as pr')
            ->join('lms.permissions as p', 'pr.permission_id', '=', 'p.permission_id')
            ->whereIn('pr.role_id', $roleIds)
            ->whereNull('pr.deleted_at')
            ->whereNull('p.deleted_at')
            ->pluck('p.permission_name')
            ->toArray();

        // ✅ Step 3: Check if required permission is present
        if (!in_array($permissionName, $permissions)) {
            return response()->json(['error' => "Forbidden (missing permission: {$permissionName})"], 403);
        }

        return $next($request);
    }
}
