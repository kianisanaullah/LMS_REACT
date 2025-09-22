<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\SubcourseController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::middleware(['auth'])->group(function () {
    // Courses
    Route::get('/courses', [CourseController::class, 'indexPage'])->name('courses.page');
    Route::get('/courses/list', [CourseController::class, 'index']);
    Route::resource('courses', CourseController::class)->except(['index']);

    // Subcourses
    Route::get('/subcourses', [SubcourseController::class, 'indexPage'])->name('subcourses.page');
    Route::get('/subcourses/list', [SubcourseController::class, 'index']);
    Route::resource('subcourses', SubcourseController::class)->except(['index']);

    //  Roles
    
Route::get('/roles', [RoleController::class, 'indexPage'])->name('roles.page');
Route::get('/roles/list', [RoleController::class, 'index']);
Route::post('/roles/{role}/assign-permissions', [RoleController::class, 'assignPermissions'])
    ->name('roles.assign-permissions');
Route::resource('roles', RoleController::class)->except(['index']);


Route::get('/roles/pivot-data', [RoleController::class, 'pivotData'])->name('roles.pivot');


    // Permissions
    Route::get('/permissions', [PermissionController::class, 'indexPage'])->name('permissions.page');
    Route::get('/permissions/list', [PermissionController::class, 'index']);
    Route::resource('permissions', PermissionController::class)->except(['index']);
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
Route::get('/test-auth', function () {
    return auth()->user();
});

Route::get('dashboard', function (\Illuminate\Http\Request $request) {
    return Inertia::render('dashboard', [
        'user_id' => $request->query('user_id'),
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
