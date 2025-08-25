<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

// 👇 This will fetch users from LMS.USERS
Route::get('/lms-users', function () {
    $user = User::find(417);
    Auth::login($user);
    dd($user);
    $users = DB::select("SELECT * FROM LMS.USERS");
    return response()->json($users); // return as JSON
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';