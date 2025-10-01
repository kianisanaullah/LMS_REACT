<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;
use App\Models\User;

class UserController extends Controller
{
    // Show Inertia page
    public function indexPage()
    {
        return Inertia::render('Users/Index');
    }

    // Return list of users (for React table)
    public function index()
    {
        $users = DB::connection('oracle')
            ->table('LMS.USERS')
            ->select('ID', 'NAME', 'EMAIL')
            ->get();

        return response()->json($users);
    }

    // Store new user (permission check)
    public function store(Request $request)
    {
        $authUser = auth()->user();
        if (! $authUser) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        //  Check permission 
        $hasPermission = DB::connection('oracle')
            ->table('LMS.USER_ROLE as ur')
            ->join('LMS.PERMISSION_ROLE as pr', 'ur.ROLE_ID', '=', 'pr.ROLE_ID')
            ->join('LMS.PERMISSIONS as p', 'p.PERMISSION_ID', '=', 'pr.PERMISSION_ID')
            ->where('ur.USER_ID', $authUser->id)
            ->where('p.PERMISSION_NAME', 'create-user')
            ->whereNull('ur.DELETED_AT')
            ->whereNull('pr.DELETED_AT')
            ->exists();

        if (! $hasPermission) {
            return response()->json(['error' => 'Not allowed'], 403);
        }

        //  Validation
        $request->validate([
            'name'     => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:oracle.LMS.USERS,USERNAME',
            'email'    => 'required|string|email|max:255|unique:oracle.LMS.USERS,EMAIL',
            'password' => 'required|string|min:8',
        ]);

        //  Insert directly via query
        $id = DB::connection('oracle')
            ->table('LMS.USERS')
            ->insertGetId([
                'NAME'           => $request->name,
                'USERNAME'       => $request->username,
                'EMAIL'          => $request->email,
                'PASSWORD'       => Hash::make($request->password),
                'REMEMBER_TOKEN' => Str::random(60),
                'CREATED_BY'     => $authUser->id,
                'CREATED_AT'     => now()->format('Y-m-d H:i:s'),
                'ACTIVE'         => 1,
            ], 'ID'); 

        return response()->json([
            'message' => 'User created successfully',
            'user_id' => $id,
        ], 201);
    }
}
