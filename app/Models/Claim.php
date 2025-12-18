<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Claim extends Model
{
    use HasFactory;

    protected $table = 'claims'; 
    protected $fillable = [
    'staff_id', 
    'claim_type', 
    'amount', 
    'description', 
    'receipt_path', 
    'status', 
    'rejection_reason'
];

    /**
     * The owner of the claim
     */
    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id', 'staff_id');
    }

    /**
     * The HR/Admin who acted on the claim
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'action_by');
    }
}