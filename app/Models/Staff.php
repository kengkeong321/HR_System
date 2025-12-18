<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Staff extends Model
{
    protected $table = 'staff';
    protected $primaryKey = 'staff_id';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'depart_id',
        'full_name',
        'email',
        'phone',
        'position',
        'basic_salary',
        'join_date'
    ];

    // Link back to User login info
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    // Link to Department
    public function department()
    {
        return $this->belongsTo(Department::class, 'depart_id', 'depart_id');
    }

    // Link to Payroll History
    public function payrolls()
    {
        return $this->hasMany(Payroll::class, 'staff_id', 'staff_id');
    }
}
