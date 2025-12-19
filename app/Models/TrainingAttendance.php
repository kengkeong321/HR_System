<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrainingAttendance extends Model
{
   
    protected $table = 'training_attendance';

    protected $fillable = ['user_id', 'training_id', 'status'];

  public function trainingProgram()
{
    return $this->belongsTo(TrainingProgram::class, 'training_program_id');
}
}
