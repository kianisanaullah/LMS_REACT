<?php

namespace App\Http\Controllers;

use App\Models\Subcourse;
use App\Models\Course;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SubcourseController extends Controller
{
    
    public function indexPage()
    {
        return Inertia::render('Subcourses');
    }

    // API Methods
    public function index()
    {
        $userId = auth()->user()->id;

        return Subcourse::where('USER_ID', $userId)
            ->whereNull('DELETED_AT')
            ->with('course') 
            ->get()
            ->map(function ($subcourse) {
                $subcourse->course_name = $subcourse->course ? $subcourse->course->COURSE_NAME : null;

                $subcourse->attachment_url = $subcourse->ATTACHMENTS
                    ? asset('storage/' . $subcourse->ATTACHMENTS)
                    : null;

                return $subcourse;
            });
    }

    public function show($id)
    {
        $userId = auth()->user()->id;

        $subcourse = Subcourse::where('ID', $id)
            ->where('USER_ID', $userId)
            ->with('course')
            ->firstOrFail();

        $subcourse->course_name = $subcourse->course ? $subcourse->course->COURSE_NAME : null;

        $subcourse->attachment_url = $subcourse->ATTACHMENTS
            ? asset('storage/' . $subcourse->ATTACHMENTS)
            : null;

        return $subcourse;
    }

  
    public function store(Request $request)
{
    $request->validate([
        'COURSE_ID'      => 'required|integer',
        'SUBCOURSE_NAME' => 'required|string|max:255',
        'DESCRIPTION'    => 'nullable|string',
        'ATTACHMENTS'    => 'nullable|file|max:5120',
    ]);

   
    if (!Course::where('ID', $request->COURSE_ID)->exists()) {
        return response()->json(['error' => 'Invalid COURSE_ID'], 422);
    }

    $userId = auth()->user()->id;

    $subcourse = new Subcourse();
    $subcourse->COURSE_ID      = $request->COURSE_ID;
    $subcourse->SUBCOURSE_NAME = $request->SUBCOURSE_NAME;
    $subcourse->DESCRIPTION    = $request->DESCRIPTION;
    $subcourse->USER_ID        = $userId;
    $subcourse->CREATED_BY     = $userId;
    $subcourse->CREATED_AT     = now();

    if ($request->hasFile('ATTACHMENTS')) {
        $path = $request->file('ATTACHMENTS')->store('subcourses', 'public');
        $subcourse->ATTACHMENTS = $path;
    }

    $subcourse->save();

   
    $subcourse->course_name = $subcourse->course->COURSE_NAME ?? null;
    $subcourse->attachment_url = $subcourse->ATTACHMENTS
        ? asset('storage/' . $subcourse->ATTACHMENTS)
        : null;

    return response()->json($subcourse, 201);
}






   public function update(Request $request, $id)
    {
        $userId = auth()->user()->id;

        $data = [
            'UPDATED_BY' => $userId,
            'UPDATED_AT' => now()->format('Y-m-d H:i:s'),
        ];

        if ($request->has('COURSE_ID')) {
            $data['COURSE_ID'] = $request->COURSE_ID;
        }
        if ($request->has('SUBCOURSE_NAME')) {
            $data['SUBCOURSE_NAME'] = $request->SUBCOURSE_NAME;
        }
        if ($request->has('DESCRIPTION')) {
            $data['DESCRIPTION'] = $request->DESCRIPTION;
        }
        if ($request->hasFile('ATTACHMENTS')) {
            $path = $request->file('ATTACHMENTS')->store('subcourses', 'public');
            $data['ATTACHMENTS'] = $path;
        }

        $updated = \DB::table('LMS.SUBCOURSES')
            ->where('ID', $id)
            ->where('USER_ID', $userId)
            ->update($data);

        if (!$updated) {
            return response()->json(['error' => 'Subcourse not found'], 404);
        }

    $subcourse = Subcourse::where('ID', $id)
    ->where('USER_ID', $userId)
    ->firstOrFail();



        if (!empty($subcourse->ATTACHMENTS ?? $subcourse->attachments)) {
            $file = $subcourse->ATTACHMENTS ?? $subcourse->attachments;
            $subcourse->attachment_url = asset('storage/' . $file);
        } else {
            $subcourse->attachment_url = null;
        }

        $subcourse->course_name = Course::find($subcourse->COURSE_ID)->COURSE_NAME ?? null;

        return response()->json($subcourse, 200);
    }

    

    public function destroy($id)
    {
        $userId = auth()->user()->id;

        $data = [
            'DELETED_BY' => $userId,
            'DELETED_AT' => now()->format('Y-m-d H:i:s'),
        ];

        $deleted = \DB::table('LMS.SUBCOURSES')
            ->where('ID', $id)
            ->where('USER_ID', $userId)
            ->update($data);

        if (!$deleted) {
            return response()->json(['error' => 'Subcourse not found'], 404);
        }

        return response()->json(['message' => 'Subcourse deleted successfully']);
    }
}
