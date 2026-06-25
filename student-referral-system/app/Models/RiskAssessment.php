<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiskAssessment extends Model
{
    protected $fillable = [
        'student_id',
        'avg_grade',
        'total_absences',
        'behavioral_reports_count',
        'failed_subjects',
        'risk_score',
        'risk_level',
        'risk_factors',
        'assessed_at',
    ];

    protected $casts = [
        'risk_factors' => 'array',
        'assessed_at'  => 'datetime',
    ];

    // Relationships
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function referral()
    {
        return $this->hasOne(Referral::class);
    }

    // Helper — get risk level badge color
    public function getRiskColorAttribute()
    {
        return match($this->risk_level) {
            'high'     => 'red',
            'moderate' => 'yellow',
            'low'      => 'green',
            default    => 'gray',
        };
    }
}
