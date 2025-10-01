<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class AttachUserRoles
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (! $user) {
            return $next($request);
        }

        // Always get the user ID via auth()
        $userId = auth()->user()->id;

        // Fetch roles from Oracle with alias
        $roles = DB::connection('oracle')
            ->table('LMS.USER_ROLE as ur')
            ->join('LMS.ROLES as r', 'ur.ROLE_ID', '=', 'r.ROLE_ID')
            ->where('ur.USER_ID', $userId)
            ->whereNull('ur.DELETED_AT')
            ->whereNull('r.DELETED_AT')
            ->select('r.ROLE_NAME as role_name')
            ->pluck('role_name')
            ->map(fn ($v) => trim((string) $v))
            ->toArray();

        $isAdmin = in_array('Admin', $roles, true);

        $permissions = DB::connection('oracle')
    ->table('lms.permission_role as pr')
    ->join('lms.permissions as p', 'pr.permission_id', '=', 'p.permission_id')
    ->whereIn('pr.role_id', function ($q) use ($userId) {
        $q->select('role_id')
          ->from('lms.user_role')
          ->where('user_id', $userId)
          ->whereNull('deleted_at');
    })
    ->whereNull('pr.deleted_at')
    ->whereNull('p.deleted_at')
    ->select('p.permission_name as permission_name')
    ->pluck('permission_name')   // must match alias
    ->map(fn ($v) => trim((string) $v))
    ->toArray();


        // Attach roles & admin flag to user object
        $user->roles = $roles;
        $user->isAdmin = $isAdmin;
        $user->permissions = $permissions;

        // Also attach to request attributes
        $request->attributes->set('user_roles', $roles);
        $request->attributes->set('is_admin', $isAdmin);

         // Share with Inertia
        Inertia::share('auth', function () use ($user, $roles, $isAdmin, $permissions) {
            return [
                'user'        => $user,
                'roles'       => $roles,
                'isAdmin'     => $isAdmin,
                'permissions' => $permissions,
            ];
        });

        Log::debug("AttachUserRoles: userId={$userId} roles=" . json_encode($roles) . " permissions=" . json_encode($permissions));

        return $next($request);
    }
}