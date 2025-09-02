<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\CourseController;


Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('/courses', [CourseController::class, 'indexPage'])->name('courses.index');

    // API routes (for React axios) inside web.php so session works
    Route::get('/courses/list', [CourseController::class, 'index']);
    Route::get('/courses/{id}', [CourseController::class, 'show']);
    Route::post('/courses', [CourseController::class, 'store']);
    Route::put('/courses/{id}', [CourseController::class, 'update']);
    Route::delete('/courses/{id}', [CourseController::class, 'destroy']);
});

// Debug Oracle users (remove in production!)
Route::get('/lms-users', function () {
    $email = 'wasimssindhu@gmail.com';

    $user = DB::connection('oracle')
        ->table('LMS.USERS')
        ->where('EMAIL', $email)
        ->selectRaw('PASSWORD AS password, EMAIL AS email')
        ->first();

    return response()->json($user);
});

// test-auth route
Route::get('/test-auth', function() {
    return auth()->user();
});



Route::get('dashboard', function (\Illuminate\Http\Request $request) {
    return Inertia::render('dashboard', [
        'user_id' => $request->query('user_id'),
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
