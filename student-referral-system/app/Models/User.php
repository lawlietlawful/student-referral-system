<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Relationships
    public function student()
    {
        return $this->hasOne(Student::class);
    }

    public function referralsReferred()
    {
        return $this->hasMany(Referral::class, 'referred_by');
    }

    public function referralsCounselor()
    {
        return $this->hasMany(Referral::class, 'counselor_id');
    }

    public function interventions()
    {
        return $this->hasMany(Intervention::class, 'counselor_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function behavioralReports()
    {
        return $this->hasMany(BehavioralReport::class, 'reported_by');
    }

    // Role checker helpers
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isCounselor()
    {
        return $this->role === 'guidance_counselor';
    }

    public function isTeacher()
    {
        return $this->role === 'teacher';
    }

    public function isStudent()
    {
        return $this->role === 'student';
    }
}
