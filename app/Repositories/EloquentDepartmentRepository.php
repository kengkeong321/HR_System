<?php
//Woo Keng Keong
namespace App\Repositories;

use App\Models\Department;

class EloquentDepartmentRepository implements DepartmentRepositoryInterface
{
    //get all departments
    public function all()
    {
        return Department::with('faculty')->get();
    }

    //get paginated departments
    public function paginate(int $perPage = 10, ?int $page = null)
    {
        return Department::with('faculty')->paginate($perPage, ['*'], 'page', $page);
    }

    //find department by id
    public function find(string $id)
    {
        return Department::findOrFail($id);
    }

    //create a new department
    public function create(array $data)
    {
        return Department::create($data);
    }


    //update an existing department
    public function update(string $id, array $data)
    {
        $d = Department::findOrFail($id);
        $d->update($data);
        return $d;
    }

    //toggle department status
    public function toggleStatus(string $id)
    {
        $d = Department::findOrFail($id);
        $d->status = $d->status === 'Active' ? 'Inactive' : 'Active';
        $d->save();
        return $d;
    }

    //get department with its courses
    public function getWithCourses(string $id)
    {
        //return active courses ordered by name
        return Department::with(['faculty', 'courses' => function($q) { $q->orderBy('course_name'); }])->findOrFail($id);
    }
}
