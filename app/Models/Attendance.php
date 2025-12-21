<?php
//Mu Jun Yi
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $table = 'attendances';

    protected $fillable = [
    'user_id',
    'status',
    'attendance_date',
    'clock_in_time',
    'clock_out_time', 
    'remarks'
]   ;

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}