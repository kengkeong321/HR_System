<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $table = 'Course';
    protected $primaryKey = 'course_id';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'course_id',
        'course_name',
        'status',
    ];
}

