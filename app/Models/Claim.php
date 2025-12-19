<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Claim extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_id',
        'claim_type',
        'description',
        'amount',
        'receipt_path',
        'status',
        'rejection_reason',
        'approved_by',
        'approved_at',
        'rejected_by',
        'is_seen',
    ];

    // relationship to staff
    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id', 'staff_id');
    }

    // relationship to HR(approver)
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}