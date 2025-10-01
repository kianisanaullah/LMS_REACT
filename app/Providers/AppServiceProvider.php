<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */


public function boot()
{
    Inertia::share('auth', function () {
        $user = Auth::user();
        if (!$user) {
            return null;
        }

        $userId = $user->id ?? $user->ID;

        // Roles
        $roles = DB::connection('oracle')
            ->table('LMS.USER_ROLE as ur')
            ->join('LMS.ROLES as r', 'ur.ROLE_ID', '=', 'r.ROLE_ID')
            ->where('ur.USER_ID', $userId)
            ->whereNull('ur.DELETED_AT')
            ->whereNull('r.DELETED_AT')
            ->pluck('r.ROLE_NAME')
            ->toArray();

        // Permissions
        $permissions = DB::connection('oracle')
            ->table('LMS.PERMISSION_ROLE as pr')
            ->join('LMS.PERMISSIONS as p', 'pr.PERMISSION_ID', '=', 'p.PERMISSION_ID')
            ->whereIn('pr.ROLE_ID', function ($q) use ($userId) {
                $q->select('ROLE_ID')
                  ->from('LMS.USER_ROLE')
                  ->where('USER_ID', $userId)
                  ->whereNull('DELETED_AT');
            })
            ->whereNull('pr.DELETED_AT')
            ->whereNull('p.DELETED_AT')
            ->pluck('p.PERMISSION_NAME')
            ->toArray();

        return [
            'user'        => $user,
            'roles'       => $roles,
            'permissions' => $permissions, // ✅ share permissions
            'isAdmin'     => in_array('Admin', $roles),
            'debug_roles' => $roles,
            'debug_perms' => $permissions, // ✅ debug
        ];
    });
}



}
