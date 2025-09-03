<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class CourseController extends Controller
{
  

    // Web page (Inertia)
    public function indexPage()
    {
        return Inertia::render('Courses');
    }

    // API Methods
    public function index() // for apiResource
    {
        $userId = auth()->user()->id;
        return Course::where('USER_ID', $userId)
                     ->whereNull('DELETED_AT')
                     ->get();
    }

    public function show($id)
    {
        $userId = auth()->user()->id;
        return Course::where('ID', $id)
                     ->where('USER_ID', $userId)
                     ->firstOrFail();
    }

    public function store(Request $request)
    {
        $request->validate([
            'COURSE_NAME' => 'required|string|max:255',
            'DESCRIPTION' => 'nullable|string',
            'SHORT_NAME' => 'nullable|string|max:50',
        ]);

        $userId = auth()->user()->id;

        $course = new Course();
        $course->COURSE_NAME = $request->COURSE_NAME;
        $course->DESCRIPTION = $request->DESCRIPTION;
        $course->SHORT_NAME = $request->SHORT_NAME;
        $course->USER_ID = $userId;
        $course->CREATED_BY = $userId;
        $course->CREATED_AT = now();
        $course->save();

        return response()->json($course, 201);
    }


    public function update(Request $request, $id)
{
    // Prepare update data
    $data = [
        'UPDATED_BY' => auth()->user()->id ?? 1,
        'UPDATED_AT' => now()->format('Y-m-d H:i:s'), // Oracle expects proper datetime format
    ];

    if ($request->has('COURSE_NAME')) {
        $data['COURSE_NAME'] = $request->COURSE_NAME;
    }
    if ($request->has('SHORT_NAME')) {
        $data['SHORT_NAME'] = $request->SHORT_NAME;
    }
    if ($request->has('DESCRIPTION')) {
        $data['DESCRIPTION'] = $request->DESCRIPTION;
    }

    // Run update
    $updated = \DB::table('LMS.COURSES')
        ->where('ID', $id)
        ->update($data);

    if (!$updated) {
        return response()->json(['error' => 'Course not found'], 404);
    }

    // Fetch updated course
    $course = \DB::table('LMS.COURSES')
        ->where('ID', $id)
        ->first();

    return response()->json($course, 200);
}





   public function destroy($id)
{
    $data = [
        'DELETED_BY' => auth()->user()->id  ?? 1,
        'DELETED_AT' => now()->format('Y-m-d H:i:s'),
    ];

    $deleted = \DB::table('LMS.COURSES')
        ->where('ID', $id)
        ->update($data);

    if (!$deleted) {
        return response()->json(['error' => 'Course not found'], 404);
    }

    return response()->json(['message' => 'Course deleted successfully']);
}
}

