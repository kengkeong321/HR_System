<?php

namespace App\Repositories;

use App\Models\Course;

class EloquentCourseRepository implements CourseRepositoryInterface
{
    //get all courses
    public function all()
    {
        return Course::all();
    }

    //get all active courses ordered by name
    public function activeOrderedByName()
    {
        return Course::where('status', 'Active')->orderBy('course_name')->get();
    }

    //get paginated courses
    public function paginate(int $perPage = 10, ?int $page = null)
    {
        return Course::paginate($perPage, ['*'], 'page', $page);
    }

    //find course by id
    public function find(string $id)
    {
        return Course::findOrFail($id);
    }

    //create a new course
    public function create(array $data)
    {
        return Course::create($data);
    }

    //update an existing course
    public function update(string $id, array $data)
    {
        $c = Course::findOrFail($id);
        $c->update($data);
        return $c;
    }

    //toggle course status
    public function toggleStatus(string $id)
    {
        $c = Course::findOrFail($id);
        $c->status = $c->status === 'Active' ? 'Inactive' : 'Active';
        $c->save();
        return $c;
    }
}
