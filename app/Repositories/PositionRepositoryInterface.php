<?php

namespace App\Repositories;

interface PositionRepositoryInterface
{
    public function all();

    public function paginate(int $perPage = 10, ?int $page = null);

    public function find(string $id);

    public function create(array $data);

    public function update(string $id, array $data);

    public function toggleStatus(string $id);
}
