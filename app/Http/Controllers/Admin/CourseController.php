<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\CourseRepositoryInterface;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function __construct(private CourseRepositoryInterface $courseRepo) {}

    public function index(Request $request)
    {
        $page = null;
        if ($request->has('page') && $request->query('from_nav') === '1') {
            $page = (int) $request->query('page');
            session(['admin.courses.page' => $page]);
        } elseif (session()->has('admin.courses.page')) {
            $page = session('admin.courses.page');
        }

        $courses = $this->courseRepo->paginate(10, $page);
        return view('admin.courses.index', compact('courses'));
    }

    public function create()
    {
        return view('admin.courses.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'course_id' => 'required|string|max:8|unique:Course,course_id',
            'course_name' => 'required|string|max:60',
            'status' => 'required|in:Active,Inactive',
        ]);

        try {
            $this->courseRepo->create([
                'course_id' => $data['course_id'],
                'course_name' => $data['course_name'],
                'status' => $data['status'],
            ]);

            return redirect()->route('admin.courses.index')->with('success', 'Course created');
        } catch (\Throwable $e) {
            return redirect()->route('admin.courses.create')->with('error', $e->getMessage());
        }
    }

    public function edit(string $course)
    {
        $course = $this->courseRepo->find($course);
        return view('admin.courses.edit', compact('course'));
    }

    public function update(Request $request, string $course)
    {
        $data = $request->validate([
            'course_id' => 'required|string|max:8|unique:Course,course_id,'.$course.',course_id',
            'course_name' => 'required|string|max:60',
            'status' => 'required|in:Active,Inactive',
        ]);

        try {
            $this->courseRepo->update($course, [
                'course_id' => $data['course_id'],
                'course_name' => $data['course_name'],
                'status' => $data['status'],
            ]);

            return redirect()->route('admin.courses.index')->with('success', 'Course updated');
        } catch (\Throwable $e) {
            return redirect()->route('admin.courses.edit', $course)->with('error', $e->getMessage());
        }
    }

    public function destroy(string $course)
    {
        // Delete disabled — prefer status toggle
        return redirect()->route('admin.courses.index')->with('error', 'Delete operation is disabled. Use status to set Inactive');
    }

    public function toggleStatus(string $course)
    {
        try {
            $this->courseRepo->toggleStatus($course);
            return redirect()->route('admin.courses.index')->with('success', 'Course status updated');
        } catch (\Throwable $e) {
            return redirect()->route('admin.courses.index')->with('error', $e->getMessage());
        }
    }

    public function page(Request $request)
    {
        $page = (int) $request->input('page', 1);
        session(['admin.courses.page' => $page]);
        $courses = $this->courseRepo->paginate(10, $page);
        return view('admin.courses._list', compact('courses'));
    }
}
