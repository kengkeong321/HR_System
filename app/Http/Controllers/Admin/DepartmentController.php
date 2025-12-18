<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\DepartmentRepositoryInterface;
use App\Repositories\FacultyRepositoryInterface;
use App\Repositories\CourseRepositoryInterface;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function __construct(private DepartmentRepositoryInterface $depRepo, private FacultyRepositoryInterface $facRepo, private CourseRepositoryInterface $courseRepo) {}

    public function assign(string $department)
    {
        $department = $this->depRepo->find($department);
        $courses = $this->courseRepo->activeOrderedByName();
        $selected = $department->courses->pluck('course_id')->toArray();
        return view('admin.departments.assign', compact('department','courses','selected'));
    }

    public function assignStore(Request $request, string $department)
    {
        $data = $request->validate([
            'courses' => 'nullable|array',
            'courses.*' => 'string|exists:Course,course_id',
        ]);

        try {
            $d = $this->depRepo->find($department);
            $d->courses()->sync($data['courses'] ?? []);
            return redirect()->route('admin.departments.index')->with('success','Assigned courses updated');
        } catch (\Throwable $e) {
            return redirect()->route('admin.departments.assign', $department)->with('error', $e->getMessage());
        }
    }

    public function index(Request $request)
    {
        $page = null;
        if ($request->has('page') && $request->query('from_nav') === '1') {
            $page = (int) $request->query('page');
            session(['admin.departments.page' => $page]);
        } elseif (session()->has('admin.departments.page')) {
            $page = session('admin.departments.page');
        }

        $departments = $this->depRepo->paginate(10, $page);
        return view('admin.departments.index', compact('departments'));
    }

    public function create()
    {
        $faculties = $this->facRepo->all();
        return view('admin.departments.create', compact('faculties'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'depart_id' => 'required|string|max:5|unique:Department,depart_id',
            'faculty_id' => 'required|string|exists:Faculty,faculty_id',
            'depart_name' => 'required|string|max:60',
            'status' => 'required|in:Active,Inactive',
        ]);

        try {
            $this->depRepo->create($data);
            return redirect()->route('admin.departments.index')->with('success', 'Department created');
        } catch (\Throwable $e) {
            return redirect()->route('admin.departments.create')->with('error', $e->getMessage());
        }
    }

    public function edit(string $department)
    {
        $department = $this->depRepo->find($department);
        $faculties = $this->facRepo->all();
        return view('admin.departments.edit', compact('department', 'faculties'));
    }

    public function update(Request $request, string $department)
    {
        $data = $request->validate([
            'depart_id' => 'required|string|max:5|unique:Department,depart_id,'.$department.',depart_id',
            'faculty_id' => 'required|string|exists:Faculty,faculty_id',
            'depart_name' => 'required|string|max:60',
            'status' => 'required|in:Active,Inactive',
        ]);

        try {
            $this->depRepo->update($department, $data);
            return redirect()->route('admin.departments.index')->with('success', 'Department updated');
        } catch (\Throwable $e) {
            return redirect()->route('admin.departments.edit', $department)->with('error', $e->getMessage());
        }
    }
    public function destroy(string $department)
    {
        // Delete disabled — prefer status toggle
        return redirect()->route('admin.departments.index')->with('error', 'Delete operation is disabled. Use status to set Inactive');
    }

    public function toggleStatus(string $department)
    {
        try {
            $this->depRepo->toggleStatus($department);
            return redirect()->route('admin.departments.index')->with('success', 'Department status updated');
        } catch (\Throwable $e) {
            return redirect()->route('admin.departments.index')->with('error', $e->getMessage());
        }
    }

    // Return assigned courses for department (AJAX used by modal)
    public function assignments(string $department)
    {
        $d = $this->depRepo->getWithCourses($department);
        $courses = $d->courses->map(function($c) {
            return ['course_id' => $c->course_id, 'course_name' => $c->course_name];
        })->values();

        return response()->json([
            'department' => ['depart_id' => $d->depart_id, 'depart_name' => $d->depart_name],
            'courses' => $courses,
        ]);
    }

    public function page(Request $request)
    {
        $page = (int) $request->input('page', 1);
        session(['admin.departments.page' => $page]);
        $departments = $this->depRepo->paginate(10, $page);
        return view('admin.departments._list', compact('departments'));
    }
}
