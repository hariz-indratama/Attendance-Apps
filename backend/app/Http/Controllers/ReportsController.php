<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportsController extends Controller
{
    /**
     * Display the reports page.
     */
    public function index()
    {
        return Inertia::render('Reports/Index');
    }

    /**
     * Generate attendance report.
     */
    public function attendance(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        // Logic for attendance report generation
        return back()->with('success', 'Attendance report generated');
    }

    /**
     * Generate payroll report.
     */
    public function payroll(Request $request)
    {
        $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:2030',
        ]);

        // Logic for payroll report generation
        return back()->with('success', 'Payroll report generated');
    }
}