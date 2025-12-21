<?php
//Woo Keng Keong
namespace App\Repositories;

use App\Models\Faculty;

class EloquentFacultyRepository implements FacultyRepositoryInterface
{
    //get all faculties
    public function all()
    {
        return Faculty::all();
    }

    //get paginated faculties
    public function paginate(int $perPage = 10, ?int $page = null)
    {
        return Faculty::paginate($perPage, ['*'], 'page', $page);
    }

    //find faculty by id
    public function find(string $id)
    {
        return Faculty::findOrFail($id);
    }

    //create a new faculty
    public function create(array $data)
    {
        return Faculty::create($data);
    }

    //update an existing faculty
    public function update(string $id, array $data)
    {
        $f = Faculty::findOrFail($id);
        $f->update($data);
        return $f;
    }

    //toggle faculty status
    public function toggleStatus(string $id)
    {
        $f = Faculty::findOrFail($id);
        $f->status = $f->status === 'Active' ? 'Inactive' : 'Active';
        $f->save();
        return $f;
    }

    //get faculty with its departments
    public function getWithDepartments(string $id)
    {
        //return active departments ordered by name
        return Faculty::with(['departments' => function($q) { $q->where('status','Active')->orderBy('depart_name'); }])->findOrFail($id);
    }
}
