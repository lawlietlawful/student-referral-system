<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BehavioralReport extends Model
{
    protected $fillable = [
        'student_id',
        'reported_by',
        'incident_type',
        'description',
        'severity',
        'incident_date',
        'location',
        'status',
    ];

    protected $casts = [
        'incident_date' => 'date',
    ];

    // Relationships
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function reportedBy()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }
}
