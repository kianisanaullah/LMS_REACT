<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RolePermissionController extends Controller
{
public function index()
{
    return Inertia::render('Roles/Assign', [
        'users' => User::with('roles')->get(),
        'roles' => Role::whereIn('ROLE_ID', [7, 8, 9])->get(),
        // remove permissions for now
    ]);
}


public function assignRole(Request $request, $userId)
{
    $request->validate([
        'role_ids' => 'required|array',
        'role_ids.*' => 'in:7,8,9', //  only allow these 3 roles
    ]);

    $user = User::findOrFail($userId);
    $user->roles()->sync($request->role_ids);

    return back()->with('success', 'Roles assigned successfully');
}


}
