<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use App\States\User\UserStatusState;
use App\States\User\ActiveState;
use App\States\User\InactiveState;
use App\States\User\SuspendedState;

class User extends Authenticatable
{
    const ROLE_ADMIN = 'Admin';
    const ROLE_HR = 'HR';
    const ROLE_FINANCE = 'Finance';
    const ROLE_STAFF = 'Staff';

    use HasFactory, Notifiable;

    protected $table = 'user';
    protected $primaryKey = 'user_id';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'user_name',
        'password',
        'role',
        'status',
    ];

    protected $hidden = [
        'password',
    ];

    public function verifyPassword(string $plain): bool
    {
        return hash('sha256', $plain) === $this->password;
    }

    public function staff()
    {
        return $this->hasOne(Staff::class, 'user_id', 'user_id');
    }

    public function statusState(): UserStatusState
    {
        return match ($this->status) {
            'Active'    => new ActiveState(),
            'Inactive'  => new InactiveState(),
            default     => new InactiveState(),
        };
    }


public function staffRecord()
{
    return $this->hasOne(Staff::class, 'user_id', 'user_id');
}
    public function trainings()
    {
        return $this->belongsToMany(TrainingProgram::class, 'training_attendance', 'user_id', 'training_program_id')
                    ->withPivot('status') 
                    ->withTimestamps();
    }
}
