<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use HasFactory;

    protected $table = 'payrolls'; 
    protected $primaryKey = 'id';

    protected $fillable = [
        'batch_id',       
        'staff_id',
        'month',
        'year',
        'basic_salary',
        'allowances',
        'deduction',
        'manual_deduction',
        'net_salary',
        'status',
        'attendance_count',
        'allowance_remark',
        'breakdown'
    ];

    protected $casts = [
        'breakdown' => 'array',
    ];

    public function batch()
    {
        return $this->belongsTo(PayrollBatch::class, 'batch_id', 'id');
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }
}