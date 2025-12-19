<?php

namespace App\Repositories;

interface DepartmentRepositoryInterface
{
    //get all departments
    public function all();

    //get paginated departments
    public function paginate(int $perPage = 10, ?int $page = null);

    //find department by id
    public function find(string $id);

    //get department with its courses
    public function getWithCourses(string $id);

    //create a new department
    public function create(array $data);

    //update an existing department
    public function update(string $id, array $data);

    //toggle department status
    public function toggleStatus(string $id);
}
