<?php

namespace App\Http\Controllers\Counselor;

use App\Http\Controllers\Controller;

class CounselorDashboardController extends Controller
{
    public function index()
    {
        return view('counselor.dashboard');
    }
}
