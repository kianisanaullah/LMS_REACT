<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class PermissionController extends Controller
{
    public function indexPage()
    {
        return Inertia::render('Permissions');
    }

    // List permissions
    public function index()
    {
        $userId = auth()->user()->id;

        return Permission::where('USER_ID', $userId)
            ->whereNull('DELETED_AT')
            ->get();
    }

    // Show one permission
    public function show($id)
    {
        $userId = auth()->user()->id;

        return Permission::where('PERMISSION_ID', $id)
            ->where('USER_ID', $userId)
            ->firstOrFail();
    }

    // Create permission
    

    public function store(Request $request)
{
    $request->validate([
        'PERMISSION_NAME' => 'required|string|max:255',
    ]);

    $userId = auth()->user()->id;

    // ✅ Check duplicate (including soft-deleted)
    $existsAll = DB::table('LMS.PERMISSIONS')
        ->where('PERMISSION_NAME', $request->PERMISSION_NAME)
        ->exists();

    if ($existsAll) {
        return response()->json([
            'error' => 'Permission name already exists (even in deleted records)'
        ], 422);
    }

    // ✅ Existing active-only check (kept as is)
    $exists = DB::table('LMS.PERMISSIONS')
        ->where('PERMISSION_NAME', $request->PERMISSION_NAME)
        ->whereNull('DELETED_AT')
        ->exists();

    if ($exists) {
        return response()->json(['error' => 'Permission name already exists'], 422);
    }

    $data = [
        'PERMISSION_NAME' => $request->PERMISSION_NAME,
        'USER_ID'         => $userId,
        'CREATED_BY'      => $userId,
        'CREATED_AT'      => now()->format('Y-m-d H:i:s'),
    ];

    $id = DB::table('LMS.PERMISSIONS')->insertGetId($data, 'PERMISSION_ID');
    $permission = DB::table('LMS.PERMISSIONS')->where('PERMISSION_ID', $id)->first();

    return response()->json($permission, 201);
}


    // Update permission
    public function update(Request $request, $id)
    {
        $userId = auth()->user()->id;

        $data = [
            'UPDATED_BY' => $userId,
            'UPDATED_AT' => now()->format('Y-m-d H:i:s'),
        ];

        if ($request->has('PERMISSION_NAME')) {
            $data['PERMISSION_NAME'] = $request->PERMISSION_NAME;
        }

        $updated = DB::table('LMS.PERMISSIONS')
            ->where('PERMISSION_ID', $id)
            ->where('USER_ID', $userId)
            ->update($data);

        if (!$updated) {
            return response()->json(['error' => 'Permission not found'], 404);
        }

        $permission = DB::table('LMS.PERMISSIONS')
            ->where('PERMISSION_ID', $id)
            ->where('USER_ID', $userId)
            ->first();

        return response()->json($permission, 200);
    }

    // Soft delete
    public function destroy($id)
    {
        $userId = auth()->user()->id;

        $data = [
            'DELETED_BY' => $userId,
            'DELETED_AT' => now()->format('Y-m-d H:i:s'),
        ];

        $deleted = DB::table('LMS.PERMISSIONS')
            ->where('PERMISSION_ID', $id)
            ->where('USER_ID', $userId)
            ->update($data);

        if (!$deleted) {
            return response()->json(['error' => 'Permission not found'], 404);
        }

        return response()->json(['message' => 'Permission deleted successfully']);
    }
}
