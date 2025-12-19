<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model; 

class PayrollBatch extends Model
{
    const STATUS_DRAFT = 'Draft';
    const STATUS_PAID = 'Paid';

    const STATUS_L1_APPROVED = 'L1_Approved';
    const STATUS_L2_APPROVED = 'L2_Approved';

    protected $fillable = ['month_year', 'status', 'total_staff', 'total_amount'];
    
    public function payrolls()
    {
        return $this->hasMany(Payroll::class, 'batch_id','id');
    }
}