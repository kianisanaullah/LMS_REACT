<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

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
    $request->validate([
        'COURSE_NAME' => 'required|string|max:255',
        'DESCRIPTION' => 'nullable|string',
        'SHORT_NAME'  => 'nullable|string|max:50',
    ]);

    $userId = auth()->user()->id;

    $course = Course::where('ID', $id)
                    ->where('USER_ID', $userId)
                    ->whereNull('DELETED_AT')
                    ->firstOrFail();

    $course->course_name = $request->input('COURSE_NAME'); 
    $course->description = $request->input('DESCRIPTION'); 
    $course->short_name  = $request->input('SHORT_NAME');  
    $course->updated_by  = $userId;                        
    $course->updated_at  = now();

    $course->save();

    return response()->json($course);
}




    public function destroy($id)
    {
        $userId = auth()->user()->id;

        $course = Course::where('ID', $id)
                        ->where('USER_ID', $userId)
                        ->whereNull('DELETED_AT')
                        ->firstOrFail();

        $course->softDelete($userId); // ✅ Works now

        return response()->json(['message' => 'Course deleted successfully']);
    }


}
