<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CourseController;

Route::middleware('auth:sanctum')->group(function () {
    // Full API resource for courses
    Route::apiResource('courses', CourseController::class);
});
