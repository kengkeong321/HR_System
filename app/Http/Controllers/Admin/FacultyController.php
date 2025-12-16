<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\FacultyRepositoryInterface;
use Illuminate\Http\Request;

class FacultyController extends Controller
{
    public function __construct(private FacultyRepositoryInterface $facRepo) {}

    public function index(Request $request)
    {
        $page = null;
        // Only accept page param when navigation comes from our pagination links
        if ($request->has('page') && $request->query('from_nav') === '1') {
            $page = (int) $request->query('page');
            session(['admin.faculties.page' => $page]);
        } elseif (session()->has('admin.faculties.page')) {
            $page = session('admin.faculties.page');
        }

        $faculties = $this->facRepo->paginate(10, $page);
        return view('admin.faculties.index', compact('faculties'));
    }

    public function create()
    {
        return view('admin.faculties.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'faculty_id' => 'required|string|max:5|unique:Faculty,faculty_id',
            'faculty_name' => 'required|string|max:60',
            'status' => 'required|in:Active,Inactive',
        ]);

        try {
            $this->facRepo->create($data);
            return redirect()->route('admin.faculties.index')->with('success', 'Faculty created');
        } catch (\Throwable $e) {
            return redirect()->route('admin.faculties.create')->with('error', $e->getMessage());
        }
    }

    public function edit(string $faculty)
    {
        $faculty = $this->facRepo->find($faculty);
        return view('admin.faculties.edit', compact('faculty'));
    }

    public function update(Request $request, string $faculty)
    {
        $data = $request->validate([
            'faculty_id' => 'required|string|max:5|unique:Faculty,faculty_id,'.$faculty.',faculty_id',
            'faculty_name' => 'required|string|max:60',
            'status' => 'required|in:Active,Inactive',
        ]);

        try {
            $this->facRepo->update($faculty, $data);
            return redirect()->route('admin.faculties.index')->with('success', 'Faculty updated');
        } catch (\Throwable $e) {
            return redirect()->route('admin.faculties.edit', $faculty)->with('error', $e->getMessage());
        }
    }

    public function destroy(string $faculty)
    {
        // Delete disabled — prefer status toggle
        return redirect()->route('admin.faculties.index')->with('error', 'Delete operation is disabled. Use status to set Inactive');
    }

    public function toggleStatus(string $faculty)
    {
        try {
            $this->facRepo->toggleStatus($faculty);
            return redirect()->route('admin.faculties.index')->with('success', 'Faculty status updated');
        } catch (\Throwable $e) {
            return redirect()->route('admin.faculties.index')->with('error', $e->getMessage());
        }
    }

    public function page(Request $request)
    {
        $page = (int) $request->input('page', 1);
        session(['admin.faculties.page' => $page]);
        $faculties = $this->facRepo->paginate(10, $page);
        return view('admin.faculties._list', compact('faculties'));
    }
}
