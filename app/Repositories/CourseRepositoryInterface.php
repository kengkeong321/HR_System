<?php

namespace App\Repositories;

interface CourseRepositoryInterface
{
    //get all courses
    public function all();

    //get all active courses ordered by name
    public function activeOrderedByName();

    //get paginated courses
    public function paginate(int $perPage = 10, ?int $page = null);

    //find course by id
    public function find(string $id);

    //create a new course
    public function create(array $data);

    //update an existing course
    public function update(string $id, array $data);

    //toggle course status
    public function toggleStatus(string $id);
}
