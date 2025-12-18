<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use HasFactory;

    protected $table = 'payrolls'; 

    // 2. Primary Key
    protected $primaryKey = 'id';

    // 3. ALLOWED FIELDS 
    protected $fillable = [
        'batch_id',       
        'staff_id',
        'month',
        'year',
        'basic_salary',
        'allowances',
        'deduction',
        'net_salary',
        'status',
        'allowance_remark',
        'breakdown'
    ];

    protected $casts = [
        'breakdown' => 'array',
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }
}