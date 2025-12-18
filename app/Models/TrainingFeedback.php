<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrainingFeedback extends Model
{
    use HasFactory;

    // 👇👇👇 加上这一行，强制指定表名 (记得要有 's')
    protected $table = 'training_feedbacks'; 

    protected $fillable = [
        'user_id',
        'training_program_id',
        'comments',
        'rating'
    ];

    // ... 其他关联代码 ...
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}