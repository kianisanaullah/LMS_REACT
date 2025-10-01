<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class CourseController extends Controller
{
    public function indexPage()
    {
        return Inertia::render('Courses');
    }

    // List all courses (not just the user's)
    public function index()
    {
        return Course::whereNull('DELETED_AT')
            ->get()
            ->map(function ($course) {
                $course->attachment_url = $course->ATTACHMENTS
                    ? asset('storage/' . $course->ATTACHMENTS)
                    : null;
                return $course;
            });
    }

    // Show any course
    public function show($id)
    {
        $course = Course::where('ID', $id)->firstOrFail();

        $course->attachment_url = $course->ATTACHMENTS
            ? asset('storage/' . $course->ATTACHMENTS)
            : null;

        return $course;
    }

    // Create new course
    public function store(Request $request)
    {
        $request->validate([
            'COURSE_NAME' => 'required|string|max:255',
            'DESCRIPTION' => 'nullable|string',
            'SHORT_NAME'  => 'nullable|string|max:50',
            'ATTACHMENTS' => 'nullable|file|max:5120', // 5MB
        ]);

        $userId = auth()->user()->id;

        // Duplicate check
        $existsAll = Course::where('COURSE_NAME', $request->COURSE_NAME)->exists();
        if ($existsAll) {
            return response()->json([
                'error' => 'Course name already exists (even in deleted records)'
            ], 422);
        }

        $exists = Course::where('COURSE_NAME', $request->COURSE_NAME)
            ->whereNull('DELETED_AT')
            ->exists();
        if ($exists) {
            return response()->json(['error' => 'Course name already exists'], 422);
        }

        $course = new Course();
        $course->COURSE_NAME = $request->COURSE_NAME;
        $course->DESCRIPTION = $request->DESCRIPTION;
        $course->SHORT_NAME  = $request->SHORT_NAME;
        $course->USER_ID     = $userId;
        $course->CREATED_BY  = $userId;
        $course->CREATED_AT  = now();

        if ($request->hasFile('ATTACHMENTS')) {
            $path = $request->file('ATTACHMENTS')->store('courses', 'public');
            $course->ATTACHMENTS = $path;
        }

        $course->save();

        $course->attachment_url = $course->ATTACHMENTS
            ? asset('storage/' . $course->ATTACHMENTS)
            : null;

        return response()->json($course, 201);
    }

    // Update any course
    public function update(Request $request, $id)
    {
        $userId = auth()->user()->id;

        $data = [
            'UPDATED_BY' => $userId,
            'UPDATED_AT' => now()->format('Y-m-d H:i:s'),
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

        if ($request->hasFile('ATTACHMENTS')) {
            $path = $request->file('ATTACHMENTS')->store('courses', 'public');
            $data['ATTACHMENTS'] = $path;
        }

        $updated = DB::table('LMS.COURSES')
            ->where('ID', $id)
            ->update($data);

        if (!$updated) {
            return response()->json(['error' => 'Course not found'], 404);
        }

        $course = DB::table('LMS.COURSES')->where('ID', $id)->first();

        $file = $course->ATTACHMENTS ?? $course->attachments ?? null;
        $course->attachment_url = $file ? asset('storage/' . $file) : null;

        return response()->json($course, 200);
    }

    // Delete any course (soft delete)
    public function destroy($id)
    {
        $userId = auth()->user()->id;

        $data = [
            'DELETED_BY' => $userId,
            'DELETED_AT' => now()->format('Y-m-d H:i:s'),
        ];

        $deleted = DB::table('LMS.COURSES')
            ->where('ID', $id)
            ->update($data);

        if (!$deleted) {
            return response()->json(['error' => 'Course not found'], 404);
        }

        return response()->json(['message' => 'Course deleted successfully']);
    }
}
