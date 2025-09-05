<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CourseController extends Controller
{
    // Web page (Inertia)
    public function indexPage()
    {
        return Inertia::render('Courses');
    }

    // API Methods
    public function index()
    {
        $userId = auth()->user()->id;

        return Course::where('USER_ID', $userId)
            ->whereNull('DELETED_AT')
            ->get()
            ->map(function ($course) {
                $course->attachment_url = $course->ATTACHMENTS
                    ? asset('storage/' . $course->ATTACHMENTS)
                    : null;
                return $course;
            });
    }

    public function show($id)
    {
        $userId = auth()->user()->id;

        $course = Course::where('ID', $id)
            ->where('USER_ID', $userId)
            ->firstOrFail();

        $course->attachment_url = $course->ATTACHMENTS
            ? asset('storage/' . $course->ATTACHMENTS)
            : null;

        return $course;
    }

    public function store(Request $request)
    {
        $request->validate([
            'COURSE_NAME' => 'required|string|max:255',
            'DESCRIPTION' => 'nullable|string',
            'SHORT_NAME'  => 'nullable|string|max:50',
            'ATTACHMENTS' => 'nullable|file|max:5120', // max 5MB
        ]);

        $userId = auth()->user()->id;

        $course = new Course();
        $course->COURSE_NAME = $request->COURSE_NAME;
        $course->DESCRIPTION = $request->DESCRIPTION;
        $course->SHORT_NAME  = $request->SHORT_NAME;
        $course->USER_ID     = $userId;
        $course->CREATED_BY  = $userId;
        $course->CREATED_AT  = now();

        // ✅ store file on disk and save path in DB
        if ($request->hasFile('ATTACHMENTS')) {
            $path = $request->file('ATTACHMENTS')->store('courses', 'public');
            $course->ATTACHMENTS = $path; // e.g. "courses/filename.pdf"
        }

        $course->save();

        $course->attachment_url = $course->ATTACHMENTS
            ? asset('storage/' . $course->ATTACHMENTS)
            : null;

        return response()->json($course, 201);
    }

public function update(Request $request, $id)
{
    $userId = auth()->user()->id;

    // Prepare update data
    $data = [
        'UPDATED_BY' => $userId,
        'UPDATED_AT' => now()->format('Y-m-d H:i:s'), // ✅ Oracle safe datetime
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

    // ✅ handle file upload
    if ($request->hasFile('ATTACHMENTS')) {
        $path = $request->file('ATTACHMENTS')->store('courses', 'public');
        $data['ATTACHMENTS'] = $path;
    }

    // Run update only for this user’s course
    $updated = \DB::table('LMS.COURSES')
        ->where('ID', $id)
        ->where('USER_ID', $userId)
        ->update($data);

    if (!$updated) {
        return response()->json(['error' => 'Course not found'], 404);
    }

    // Fetch updated course
    $course = \DB::table('LMS.COURSES')
        ->where('ID', $id)
        ->where('USER_ID', $userId)
        ->first();

// ✅ attach URL for frontend
if (!empty($course->ATTACHMENTS ?? $course->attachments)) {
    $file = $course->ATTACHMENTS ?? $course->attachments;
    $course->attachment_url = asset('storage/' . $file);
} else {
    $course->attachment_url = null;
}


    return response()->json($course, 200);
}




public function destroy($id)
{
    $userId = auth()->user()->id;

    $data = [
        'DELETED_BY' => $userId,
        'DELETED_AT' => now()->format('Y-m-d H:i:s'),
    ];

    // Soft delete only if it belongs to this user
    $deleted = \DB::table('LMS.COURSES')
        ->where('ID', $id)
        ->where('USER_ID', $userId) // ✅ numeric match
        ->update($data);

    if (!$deleted) {
        return response()->json(['error' => 'Course not found'], 404);
    }

    return response()->json(['message' => 'Course deleted successfully']);
}

}
