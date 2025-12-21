<?php
//Dephnie Ong Yan Yee
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use HasFactory;

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

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }
}