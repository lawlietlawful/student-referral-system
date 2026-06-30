<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable = [
        'user_id',
        'student_id_number',
        'course',
        'first_name',
        'last_name',
        'middle_name',
        'gender',
        'birthdate',
        'grade_level',
        'section',
        'school_year',
        'parent_name',
        'parent_contact',
        'parent_email',
        'student_contact',
        'address',
        'status',
    ];

    protected $casts = [
        'birthdate' => 'date',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attendance()
    {
        return $this->hasMany(Attendance::class);
    }

    public function behavioralReports()
    {
        return $this->hasMany(BehavioralReport::class);
    }

    public function riskAssessments()
    {
        return $this->hasMany(RiskAssessment::class);
    }

    public function referrals()
    {
        return $this->hasMany(Referral::class);
    }

    public function seminars()
    {
        return $this->belongsToMany(Seminar::class, 'student_seminars')
                    ->withPivot('status', 'attended_at', 'remarks')
                    ->withTimestamps();
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id', 'user_id');
    }

    // Helper — get full name
    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    // Helper — count total absences
    public function getTotalAbsencesAttribute()
    {
        return $this->attendance()
                    ->where('status', 'absent')
                    ->where(function($query) {
                        $query->where('absence_type', 'unexcused')
                              ->orWhereNull('absence_type'); // Default to unexcused if null
                    })
                    ->count();
    }

    // Helper — get latest risk level
    public function getLatestRiskLevelAttribute()
    {
        $latest = $this->riskAssessments()->latest()->first();
        return $latest ? $latest->risk_level : 'not assessed';
    }
}
