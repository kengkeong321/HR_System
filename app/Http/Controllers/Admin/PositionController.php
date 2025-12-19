<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\PositionRepositoryInterface;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    public function __construct(private PositionRepositoryInterface $posRepo) {}

    public function index(Request $request)
    {
        $page = null;
        if ($request->has('page') && $request->query('from_nav') === '1') {
            $page = (int) $request->query('page');
            session(['admin.positions.page' => $page]);
        } elseif (session()->has('admin.positions.page')) {
            $page = session('admin.positions.page');
        }

        $positions = $this->posRepo->paginate(10, $page);
        return view('admin.positions.index', compact('positions'));
    }

    public function create()
    {
        return view('admin.positions.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:20|unique:Position,name',
            'status' => 'required|in:Active,Inactive',
        ]);

        try {
            $this->posRepo->create($data);
            return redirect()->route('admin.positions.index')->with('success', 'Position created');
        } catch (\Throwable $e) {
            return redirect()->route('admin.positions.create')->with('error', $e->getMessage());
        }
    }

    public function edit(string $position)
    {
        $position = $this->posRepo->find($position);
        return view('admin.positions.edit', compact('position'));
    }

    public function update(Request $request, string $position)
    {
        $data = $request->validate([
            'name' => 'required|string|max:20|unique:Position,name,'.$position.',position_id',
            'status' => 'required|in:Active,Inactive',
        ]);

        try {
            $this->posRepo->update($position, $data);
            return redirect()->route('admin.positions.index')->with('success', 'Position updated');
        } catch (\Throwable $e) {
            return redirect()->route('admin.positions.edit', $position)->with('error', $e->getMessage());
        }
    }

    public function destroy(string $position)
    {
        return redirect()->route('admin.positions.index')->with('error', 'Delete operation is disabled. Use status to set Inactive');
    }

    public function toggleStatus(string $position)
    {
        try {
            $this->posRepo->toggleStatus($position);
            return redirect()->route('admin.positions.index')->with('success', 'Position status updated');
        } catch (\Throwable $e) {
            return redirect()->route('admin.positions.index')->with('error', $e->getMessage());
        }
    }

    public function page(Request $request)
    {
        $page = (int) $request->input('page', 1);
        session(['admin.positions.page' => $page]);
        $positions = $this->posRepo->paginate(10, $page);
        return view('admin.positions._list', compact('positions'));
    }
}
