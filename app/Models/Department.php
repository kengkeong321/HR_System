<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $table = 'Department';
    protected $primaryKey = 'depart_id';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'depart_id',
        'faculty_id',
        'depart_name',
        'status',
    ];

    public function faculty()
    {
        return $this->belongsTo(Faculty::class, 'faculty_id', 'faculty_id');
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'DepartCourse', 'depart_id', 'course_id');
    }
}
