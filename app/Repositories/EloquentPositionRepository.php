<?php
//Woo Keng Keong
namespace App\Repositories;

use App\Models\Position;

class EloquentPositionRepository implements PositionRepositoryInterface
{
    public function all()
    {
        return Position::all();
    }

    public function paginate(int $perPage = 10, ?int $page = null)
    {
        return Position::paginate($perPage, ['*'], 'page', $page);
    }

    public function find(string $id)
    {
        return Position::findOrFail($id);
    }

    public function create(array $data)
    {
        return Position::create($data);
    }

    public function update(string $id, array $data)
    {
        $p = Position::findOrFail($id);
        $p->update($data);
        return $p;
    }

    public function toggleStatus(string $id)
    {
        $p = Position::findOrFail($id);
        $p->status = $p->status === 'Active' ? 'Inactive' : 'Active';
        $p->save();
        return $p;
    }
}
