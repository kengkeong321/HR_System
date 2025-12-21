<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrainingProgram extends Model
{
   
    protected $guarded = [];

    public function participants()
    {
       
        return $this->belongsToMany(User::class, 'training_attendance', 'training_program_id', 'user_id')
                    ->withPivot('id', 'status')
                    ->withTimestamps();
    }

    
    public function feedbacks()
    {
        return $this->hasMany(TrainingFeedback::class, 'training_program_id');
    }

}