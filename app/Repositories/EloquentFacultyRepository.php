<?php

namespace App\Repositories;

use App\Models\Faculty;

class EloquentFacultyRepository implements FacultyRepositoryInterface
{
    public function all()
    {
        return Faculty::all();
    }

    public function paginate(int $perPage = 10, ?int $page = null)
    {
        return Faculty::paginate($perPage, ['*'], 'page', $page);
    }

    public function find(string $id)
    {
        return Faculty::findOrFail($id);
    }

    public function create(array $data)
    {
        return Faculty::create($data);
    }

    public function update(string $id, array $data)
    {
        $f = Faculty::findOrFail($id);
        $f->update($data);
        return $f;
    }

    public function toggleStatus(string $id)
    {
        $f = Faculty::findOrFail($id);
        $f->status = $f->status === 'Active' ? 'Inactive' : 'Active';
        $f->save();
        return $f;
    }

    public function getWithDepartments(string $id)
    {
        // Include only active departments, ordered by name
        return Faculty::with(['departments' => function($q) { $q->where('status','Active')->orderBy('depart_name'); }])->findOrFail($id);
    }
}
