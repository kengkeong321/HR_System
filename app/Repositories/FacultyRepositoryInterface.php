<?php

namespace App\Repositories;

use Illuminate\Contracts\Pagination\Paginator;

interface FacultyRepositoryInterface
{
    public function all();
    public function paginate(int $perPage = 15, ?int $page = null);
    public function find(string $id);
    public function create(array $data);
    public function update(string $id, array $data);
    public function toggleStatus(string $id);
}
