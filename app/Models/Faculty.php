<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Faculty extends Model
{
    use HasFactory;

    protected $table = 'Faculty';
    protected $primaryKey = 'faculty_id';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'faculty_id',
        'faculty_name',
        'status',
    ];

    public function departments()
    {
        return $this->hasMany(Department::class, 'faculty_id', 'faculty_id');
    }
}
