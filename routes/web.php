<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\SubcourseController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserRoleController;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

// =======================
// Authenticated + roles attached
// =======================
Route::middleware(['auth', 'attach.roles'])->group(function () {
    // =======================
    // Courses
    // =======================
   
Route::get('/courses', [CourseController::class, 'indexPage'])
    ->name('courses.page')
    ->middleware('must.have:view-course'); // 👀 Example permission for listing

Route::get('/courses/list', [CourseController::class, 'index'])
    ->middleware('must.have:view-course');

Route::post('/courses', [CourseController::class, 'store'])
    ->middleware('must.have:create-course')
    ->name('courses.store');

Route::put('/courses/{id}', [CourseController::class, 'update'])
    ->middleware('must.have:edit-course')
    ->name('courses.update');

Route::delete('/courses/{id}', [CourseController::class, 'destroy'])
    ->middleware('must.have:delete-course')
    ->name('courses.destroy');

Route::get('/courses/{id}', [CourseController::class, 'show'])
    ->middleware('must.have:view-course')
    ->name('courses.show');


   // =======================
// Subcourses
// =======================
Route::get('/subcourses', [SubcourseController::class, 'indexPage'])
    ->name('subcourses.page')
    ->middleware('must.have:view-subcourse');

Route::get('/subcourses/list', [SubcourseController::class, 'index'])
    ->middleware('must.have:view-subcourse');

Route::post('/subcourses', [SubcourseController::class, 'store'])
    ->name('subcourses.store')
    ->middleware('must.have:create-subcourse');

Route::get('/subcourses/{id}', [SubcourseController::class, 'show'])
    ->name('subcourses.show')
    ->middleware('must.have:view-subcourse');

Route::put('/subcourses/{id}', [SubcourseController::class, 'update'])
    ->name('subcourses.update')
    ->middleware('must.have:edit-subcourse');

Route::delete('/subcourses/{id}', [SubcourseController::class, 'destroy'])
    ->name('subcourses.destroy')
    ->middleware('must.have:delete-subcourse');


    // =======================
    // Roles & User ↔ Roles (protected by AdminOnly)
    // =======================
    Route::middleware(['admin.only'])->group(function () {
        // Roles
        Route::get('/roles', [RoleController::class, 'indexPage'])->name('roles.page');
        Route::get('/roles/list', [RoleController::class, 'index']);
        Route::post('/roles/{role}/assign-permissions', [RoleController::class, 'assignPermissions'])
            ->name('roles.assign-permissions');
        Route::resource('roles', RoleController::class)->except(['index']);
        Route::get('/roles/pivot-data', [RoleController::class, 'pivotData'])->name('roles.pivot');

        // User ↔ Roles
        Route::get('/users/{id}/roles', [UserRoleController::class, 'getUserRoles'])->name('users.roles');
        Route::post('/users/{id}/roles', [UserRoleController::class, 'assignRoles'])->name('users.roles.assign');
        Route::delete('/users/{id}/roles/{roleId}', [UserRoleController::class, 'removeRole'])->name('users.roles.remove');
    });

    // =======================
    // Permissions
    // =======================
    // Everyone can see & list
    Route::get('/permissions', [PermissionController::class, 'indexPage'])->name('permissions.page');
    Route::get('/permissions/list', [PermissionController::class, 'index']);
    Route::get('/permissions/{id}', [PermissionController::class, 'show'])->name('permissions.show');

    // Admin-only for CRUD
    Route::middleware(['admin.only'])->group(function () {
        Route::post('/permissions', [PermissionController::class, 'store'])->name('permissions.store');
        Route::put('/permissions/{id}', [PermissionController::class, 'update'])->name('permissions.update');
        Route::delete('/permissions/{id}', [PermissionController::class, 'destroy'])->name('permissions.destroy');
    });

    // =======================
    // Users (basic CRUD)
    // =======================
    Route::get('/users', [UserController::class, 'indexPage'])->name('users.page');
    Route::get('/users/list', [UserController::class, 'index']);

    // ✅ still manual permission check in controller
    Route::get('/users/create', fn () => Inertia::render('UserCreate'))
        ->name('users.create');

    Route::post('/users', [UserController::class, 'store'])->name('users.store');
});

// =======================
// Debug / Misc
// =======================
Route::get('/lms-users', function () {
    $email = 'wasimssindhu@gmail.com';
    $user = DB::connection('oracle')
        ->table('LMS.USERS')
        ->where('EMAIL', $email)
        ->selectRaw('PASSWORD AS password, EMAIL AS email')
        ->first();
    return response()->json($user);
});

Route::get('/test-auth', fn () => auth()->user());

Route::get('/dashboard', function (\Illuminate\Http\Request $request) {
    return Inertia::render('dashboard', [
        'user_id' => $request->query('user_id'),
    ]);
})->middleware(['auth', 'attach.roles', 'verified'])->name('dashboard');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
