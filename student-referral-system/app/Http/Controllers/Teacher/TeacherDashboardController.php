<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;

class TeacherDashboardController extends Controller
{
    public function index()
    {
        return view('teacher.dashboard');
    }
}
