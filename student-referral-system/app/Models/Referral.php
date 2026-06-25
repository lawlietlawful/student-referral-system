<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Referral extends Model
{
    protected $fillable = [
        'student_id',
        'referred_by',
        'counselor_id',
        'risk_assessment_id',
        'referral_type',
        'reason',
        'priority',
        'status',
        'counselor_notes',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    // Relationships
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function referredBy()
    {
        return $this->belongsTo(User::class, 'referred_by');
    }

    public function counselor()
    {
        return $this->belongsTo(User::class, 'counselor_id');
    }

    public function riskAssessment()
    {
        return $this->belongsTo(RiskAssessment::class);
    }

    public function interventions()
    {
        return $this->hasMany(Intervention::class);
    }

    public function smsLogs()
    {
        return $this->hasMany(SmsLog::class);
    }

    // Helper — get priority badge color
    public function getPriorityColorAttribute()
    {
        return match($this->priority) {
            'high'     => 'red',
            'moderate' => 'yellow',
            'low'      => 'green',
            default    => 'gray',
        };
    }
}
