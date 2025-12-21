<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;  

class Staff extends Model
{
    protected $table = 'staff';
    
    // 1. Tell Laravel your custom primary key name
    protected $primaryKey = 'staff_id';

    // 2. Since staff_id is AUTO_INCREMENT, set this to true
    public $incrementing = true;

    public $timestamps = false;
    // 3. Ensure all form fields are in the fillable array
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
