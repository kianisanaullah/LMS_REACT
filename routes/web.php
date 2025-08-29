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

    dd(
        DB::connection('oracle')->table('LMS.USERS')->where('EMAIL','wasimssindhu@gmail.com')->first()
    );

    $email = 'wasimssindhu@gmail.com';

    // Make sure you hit Oracle and alias the uppercase column to "password"
    $user = DB::connection('oracle')
        ->table('USERS')                 // or 'LMS.USERS' if schema-qualified
        ->where('EMAIL', $email)         // Oracle column name
        ->selectRaw('PASSWORD AS password, EMAIL AS email')
        ->first();

    dd($user, optional($user)->password, $user ? Hash::check('123', $user->password) : null);


    $user = DB::table('users')->where('email','wasimssindhu@gmail.com')->first();
    dd(Hash::check('123', $user->password));
    $user = User::find(417);
    Auth::login($user);
    dd($user);
    $users = DB::select("SELECT * FROM LMS.USERS");
    return response()->json($users); // return as JSON
});

Route::get('dashboard', function (\Illuminate\Http\Request $request) {
    return Inertia::render('dashboard', [
        'user_id' => $request->query('user_id'),
    ]);
})->middleware(['auth','verified'])->name('dashboard');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
