<?php

namespace App\Repositories;

use App\Models\Department;

class EloquentDepartmentRepository implements DepartmentRepositoryInterface
{
    public function all()
    {
        return Department::with('faculty')->get();
    }

    public function paginate(int $perPage = 15, ?int $page = null)
    {
        return Department::with('faculty')->paginate($perPage, ['*'], 'page', $page);
    }

    public function find(string $id)
    {
        return Department::findOrFail($id);
    }

    public function create(array $data)
    {
        return Department::create($data);
    }

    public function update(string $id, array $data)
    {
        $d = Department::findOrFail($id);
        $d->update($data);
        return $d;
    }

    public function toggleStatus(string $id)
    {
        $d = Department::findOrFail($id);
        $d->status = $d->status === 'Active' ? 'Inactive' : 'Active';
        $d->save();
        return $d;
    }
}
