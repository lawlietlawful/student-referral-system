<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentSeminar extends Model
{
    protected $fillable = [
        'student_id',
        'seminar_id',
        'status',
        'attended_at',
        'remarks',
    ];

    protected $casts = [
        'attended_at' => 'datetime',
    ];

    // Relationships
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function seminar()
    {
        return $this->belongsTo(Seminar::class);
    }
}
