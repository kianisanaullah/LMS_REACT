<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

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
}
