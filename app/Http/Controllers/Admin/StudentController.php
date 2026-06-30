<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $course = $request->get('course');
        $grade_level = $request->get('grade_level');
        $status = $request->get('status');
        
        // Get distinct values for dropdowns
        $courses = Student::select('course')->distinct()->whereNotNull('course')->pluck('course');

        $students = Student::query()
            ->when($search, function ($query, $search) {
                $query->where(function($q) use ($search) {
                    $q->where('student_id_number', 'like', "%{$search}%")
                      ->orWhere('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%");
                });
            })
            ->when($course, function ($query, $course) {
                $query->where('course', $course);
            })
            ->when($grade_level, function ($query, $grade_level) {
                $query->where('grade_level', $grade_level);
            })
            ->when($status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(10);
            
        $totalStudents = Student::count();
        $activeStudents = Student::where('status', 'Active')->count();
        $studentsWithReferrals = Student::has('referrals')->count();
        $atRiskStudents = Student::whereHas('riskAssessments', function($q) {
            $q->whereIn('risk_level', ['high', 'moderate']);
        })->count();

        return view('admin.students.index', compact('students', 'search', 'courses', 'course', 'grade_level', 'status', 'totalStudents', 'activeStudents', 'studentsWithReferrals', 'atRiskStudents'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.students.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreStudentRequest $request)
    {
        try {
            DB::beginTransaction();

            $user = null;
            
            // Automatically create a user account for the student
            // Username and password both default to their student ID number
            $user = User::create([
                'name' => $request->first_name . ' ' . $request->last_name,
                'username' => $request->student_id_number,
                'email' => null, // Email is nullable now
                'password' => Hash::make($request->student_id_number),
                'role' => 'student',
            ]);

            $student = Student::create([
                'user_id' => $user->id,
                'student_id_number' => $request->student_id_number,
                'course' => $request->course,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'middle_name' => $request->middle_name,
                'gender' => $request->gender,
                'birthdate' => $request->birthdate,
                'grade_level' => $request->grade_level,
                'section' => $request->section,
                'school_year' => $request->school_year,
                'parent_name' => $request->parent_name,
                'parent_contact' => $request->parent_contact,
                'parent_email' => $request->parent_email,
                'address' => $request->address,
                'status' => $request->status,
            ]);

            DB::commit();

            return redirect()->route('admin.students.index')
                ->with('success', 'Student and user account created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error creating student: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Student $student)
    {
        // Eager load relationships for the student profile view
        $student->load(['attendance', 'behavioralReports', 'riskAssessments', 'referrals', 'user']);
        
        $timeline = collect();

        foreach ($student->referrals as $referral) {
            $timeline->push([
                'type' => 'referral',
                'title' => 'Referral: ' . $referral->referral_type,
                'description' => $referral->reason,
                'status' => $referral->status,
                'date' => $referral->created_at,
                'icon' => 'ti-file-description',
                'color' => 'blue'
            ]);
        }

        foreach ($student->behavioralReports as $report) {
            $timeline->push([
                'type' => 'behavioral',
                'title' => 'Incident Report: ' . $report->incident_type,
                'description' => $report->description,
                'status' => $report->status,
                'date' => \Carbon\Carbon::parse($report->incident_date),
                'icon' => 'ti-message-report',
                'color' => 'red'
            ]);
        }
        
        foreach ($student->riskAssessments as $risk) {
            $timeline->push([
                'type' => 'risk',
                'title' => 'Risk Assessment: ' . ucfirst($risk->risk_level),
                'description' => 'Score: ' . $risk->total_score,
                'status' => '',
                'date' => $risk->created_at,
                'icon' => 'ti-chart-pie',
                'color' => 'amber'
            ]);
        }

        $timeline = $timeline->sortByDesc('date');

        return view('admin.students.show', compact('student', 'timeline'));
    }



    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateStudentRequest $request, Student $student)
    {
        try {
            DB::beginTransaction();

            $student->update([
                'student_id_number' => $request->student_id_number,
                'course' => $request->course,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'middle_name' => $request->middle_name,
                'gender' => $request->gender,
                'birthdate' => $request->birthdate,
                'grade_level' => $request->grade_level,
                'section' => $request->section,
                'school_year' => $request->school_year,
                'parent_name' => $request->parent_name,
                'parent_contact' => $request->parent_contact,
                'student_contact' => $request->student_contact,
                'parent_email' => $request->parent_email,
                'address' => $request->address,
                'status' => $request->status,
            ]);

            // Sync user account details if needed
            if ($student->user) {
                $student->user->update([
                    'name' => $request->first_name . ' ' . $request->last_name,
                    'username' => $request->student_id_number,
                ]);
            }

            DB::commit();

            return redirect()->route('admin.students.show', $student->id)
                ->with('success', 'Student updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error updating student: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Student $student)
    {
        try {
            DB::beginTransaction();
            
            $user = $student->user;
            
            $student->delete();
            
            if ($user) {
                $user->delete();
            }

            DB::commit();

            return redirect()->route('admin.students.index')
                ->with('success', 'Student deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error deleting student: ' . $e->getMessage());
        }
    }
}
