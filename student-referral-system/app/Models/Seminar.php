<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seminar extends Model
{
    protected $fillable = [
        'title',
        'description',
        'date',
        'time',
        'venue',
        'speaker',
        'is_required',
        'target_grade_level',
        'max_participants',
        'status',
    ];

    protected $casts = [
        'date'        => 'date',
        'is_required' => 'boolean',
    ];

    // Relationships
    public function students()
    {
        return $this->belongsToMany(Student::class, 'student_seminars')
                    ->withPivot('status', 'attended_at', 'remarks')
                    ->withTimestamps();
    }

    // Helper — count attended students
    public function getTotalAttendeesAttribute()
    {
        return $this->students()
                    ->wherePivot('status', 'attended')
                    ->count();
    }
}
