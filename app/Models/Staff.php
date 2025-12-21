<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;  

class Staff extends Model
{
    protected $table = 'staff';
    protected $primaryKey = 'staff_id';
    public $incrementing = true;
    public $timestamps = false;
    protected $fillable = [
        'user_id', 
        'full_name', 
        'email', 
        'phone', 
        'depart_id', 
        'position', 
        'employment_type', 
        'basic_salary', 
        'hourly_rate', 
        'join_date', 
        'contract_expiry_date', 
        'bank_name', 
        'bank_account_no'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'depart_id', 'depart_id');
    }

    public function payrolls()
    {
        return $this->hasMany(Payroll::class, 'staff_id', 'staff_id');
    }
}
