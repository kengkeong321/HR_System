<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrainingFeedback extends Model
{
    use HasFactory;

   
    protected $table = 'training_feedbacks'; 

    protected $fillable = [
        'user_id',
        'training_program_id',
        'comments',
        'rating'
    ];

   
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}