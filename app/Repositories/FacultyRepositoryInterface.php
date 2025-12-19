<?php

namespace App\Repositories;

use Illuminate\Contracts\Pagination\Paginator;

interface FacultyRepositoryInterface
{
    //get all faculties
    public function all();

    //get paginated faculties
    public function paginate(int $perPage = 10, ?int $page = null);

    //find faculty by id
    public function find(string $id);

    //get faculty with its departments
    public function getWithDepartments(string $id);

    //create a new faculty
    public function create(array $data);

    //update an existing faculty
    public function update(string $id, array $data);

    //toggle faculty status
    public function toggleStatus(string $id);
}
