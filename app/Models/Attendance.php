<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $table = 'attendances';

    // Allow these fields to be filled (Data Protection [138])
    protected $fillable = [
    'user_id',
    'status',
    'attendance_date',
    'clock_in_time',
    'clock_out_time', 
    'remarks'
]   ;

    /**
     * Define the relationship to the User.
     * This fixes the "Undefined relationship [user]" error.
     */
    public function user()
    {
        // Link the 'user_id' in attendances table to the 'user_id' in users table
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}