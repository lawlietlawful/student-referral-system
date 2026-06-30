<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Seminar;
use App\Models\StudentSeminar;
use Illuminate\Http\Request;

class SeminarCheckinController extends Controller
{
    public function checkin(Seminar $seminar)
    {
        $user = auth()->user();
        if ($user->role !== 'student' || !$user->student) {
            abort(403, 'Only students can check in to seminars.');
        }

        $studentId = $user->student->id;

        $enrollment = StudentSeminar::where('student_id', $studentId)
            ->where('seminar_id', $seminar->id)
            ->first();

        if (!$enrollment) {
            return view('student.seminars.checkin_result', [
                'success' => false,
                'message' => 'You are not enrolled in this seminar.',
                'seminar' => $seminar
            ]);
        }

        if ($enrollment->status === 'attended') {
            return view('student.seminars.checkin_result', [
                'success' => true,
                'message' => 'You have already checked in to this seminar. Thank you!',
                'seminar' => $seminar
            ]);
        }

        $enrollment->update([
            'status' => 'attended',
            'attended_at' => now(),
        ]);

        return view('student.seminars.checkin_result', [
            'success' => true,
            'message' => 'Check-in successful! Your attendance has been recorded.',
            'seminar' => $seminar
        ]);
    }
}
