<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    use HasFactory;

    protected $table = 'Position';
    protected $primaryKey = 'position_id';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'name',
        'status',
    ];
}
