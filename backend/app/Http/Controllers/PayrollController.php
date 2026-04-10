<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Attendance;
use App\Models\Overtime;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class PayrollController extends Controller
{
    /**
     * Display the payroll page.
     */
    public function index(Request $request)
    {
        $month = $request->get('month', Carbon::now()->format('Y-m'));
        $year = (int) substr($month, 0, 4);
        $monthNum = (int) substr($month, 5, 2);
        $perPage = $request->get('per_page', 10);

        $startDate = Carbon::createFromDate($year, $monthNum, 1);
        $endDate = $startDate->copy()->endOfMonth();

        // Get paginated employees
        $employees = User::with(['shift', 'location'])
            ->where('is_active', true)
            ->orderBy('name')
            ->paginate($perPage);

        // Calculate payroll for each employee
        $payrollData = $employees->map(function ($employee) use ($startDate, $endDate) {
            return $this->calculatePayroll($employee, $startDate, $endDate);
        });

        // Summary statistics (calculate from all employees for accurate totals)
        $allEmployees = User::with(['shift', 'location'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        $allPayrollData = $allEmployees->map(function ($employee) use ($startDate, $endDate) {
            return $this->calculatePayroll($employee, $startDate, $endDate);
        });
        $summary = $this->getSummary($allPayrollData);

        // Get available months (last 12 months)
        $availableMonths = $this->getAvailableMonths();

        return Inertia::render('Payroll/Index', [
            'payroll_data' => $payrollData,
            'summary' => $summary,
            'selected_month' => $month,
            'available_months' => $availableMonths,
            'filters' => $request->only(['month', 'per_page']),
        ]);
    }

    /**
     * Calculate payroll for a single employee.
     */
    private function calculatePayroll($employee, Carbon $startDate, Carbon $endDate)
    {
        // Get attendance records for the month
        $attendances = Attendance::where('user_id', $employee->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        // Calculate work days
        $totalDays = $startDate->diffInDays($endDate) + 1;
        $workDays = $attendances->whereIn('status', ['hadir', 'terlambat'])->count();
        $presentDays = $attendances->whereIn('status', ['hadir', 'terlambat'])->count();
        $absentDays = $attendances->where('status', 'alpha')->count();
        $lateDays = $attendances->where('status', 'terlambat')->count();

        // Get overtime hours (calculated from start_time and end_time)
        $overtimes = Overtime::where('user_id', $employee->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->where('status', 'approved')
            ->get();

        $overtimeHours = $overtimes->sum(function ($overtime) {
            $start = strtotime($overtime->start_time);
            $end = strtotime($overtime->end_time);
            return round(($end - $start) / 3600, 2);
        });

        // Calculate salary components
        $basicSalary = (float) ($employee->basic_salary ?? 0);
        $hourlyRate = (float) ($employee->hourly_rate ?? ($basicSalary / 176)); // Default: 176 hours/month

        // Allowances
        $allowances = $employee->allowances ?? [];
        $totalAllowances = array_sum(array_column($allowances, 'amount'));

        // Deductions
        $deductions = $employee->deductions ?? [];
        $totalDeductions = array_sum(array_column($deductions, 'amount'));

        // Calculate overtime pay
        $overtimePay = $overtimeHours * $hourlyRate * 1.5; // 1.5x overtime rate

        // Calculate absent deduction
        $absentDeduction = $absentDays * ($basicSalary / $totalDays);

        // Calculate late deduction (if applicable)
        $lateDeduction = $lateDays * ($hourlyRate * 2); // 2 hours deduction per late

        // Total salary
        $grossSalary = $basicSalary + $totalAllowances + $overtimePay;
        $totalDeductions += $absentDeduction + $lateDeduction;
        $netSalary = $grossSalary - $totalDeductions;

        return [
            'id' => $employee->id,
            'employee_id' => $employee->employee_id,
            'name' => $employee->name,
            'position' => $employee->position,
            'department' => $employee->department,
            'basic_salary' => $basicSalary,
            'work_days' => $workDays,
            'present_days' => $presentDays,
            'absent_days' => $absentDays,
            'late_days' => $lateDays,
            'overtime_hours' => $overtimeHours,
            'overtime_pay' => round($overtimePay, 2),
            'allowances' => $allowances,
            'total_allowances' => round($totalAllowances, 2),
            'deductions' => $deductions,
            'absent_deduction' => round($absentDeduction, 2),
            'late_deduction' => round($lateDeduction, 2),
            'total_deductions' => round($totalDeductions, 2),
            'gross_salary' => round($grossSalary, 2),
            'net_salary' => round($netSalary, 2),
        ];
    }

    /**
     * Get summary statistics.
     */
    private function getSummary($payrollData)
    {
        $totalEmployees = $payrollData->count();
        $totalBasicSalary = $payrollData->sum('basic_salary');
        $totalAllowances = $payrollData->sum('total_allowances');
        $totalDeductions = $payrollData->sum('total_deductions');
        $totalOvertime = $payrollData->sum('overtime_pay');
        $totalNetSalary = $payrollData->sum('net_salary');

        return [
            'total_employees' => $totalEmployees,
            'total_basic_salary' => round($totalBasicSalary, 2),
            'total_allowances' => round($totalAllowances, 2),
            'total_deductions' => round($totalDeductions, 2),
            'total_overtime' => round($totalOvertime, 2),
            'total_net_salary' => round($totalNetSalary, 2),
        ];
    }

    /**
     * Get available months for filtering.
     */
    private function getAvailableMonths()
    {
        $months = [];
        for ($i = 0; $i < 12; $i++) {
            $date = Carbon::now()->subMonths($i);
            $months[] = [
                'value' => $date->format('Y-m'),
                'label' => $date->format('F Y'),
            ];
        }
        return $months;
    }

    /**
     * Export payroll data.
     */
    public function export(Request $request)
    {
        // This can be expanded for PDF/Excel export
        return response()->json(['message' => 'Export feature coming soon']);
    }

    /**
     * Display salary details for a specific employee.
     */
    public function show(Request $request, User $employee)
    {
        $month = $request->get('month', Carbon::now()->format('Y-m'));
        $year = (int) substr($month, 0, 4);
        $monthNum = (int) substr($month, 5, 2);
        $perPage = $request->get('per_page', 15);
        $attPage = $request->get('att_page', 1);
        $otPage = $request->get('ot_page', 1);

        $startDate = Carbon::createFromDate($year, $monthNum, 1);
        $endDate = $startDate->copy()->endOfMonth();

        // Get employee with relations
        $employee->load(['shift', 'location']);

        // Calculate payroll for this employee
        $payrollData = $this->calculatePayroll($employee, $startDate, $endDate);

        // Get paginated attendance details for the month
        $attendances = Attendance::where('user_id', $employee->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'desc')
            ->paginate($perPage, ['*'], 'att_page', $attPage);

        // Get paginated overtime records
        $overtimes = Overtime::where('user_id', $employee->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->where('status', 'approved')
            ->orderBy('date', 'desc')
            ->paginate($perPage, ['*'], 'ot_page', $otPage);

        // Get available months for navigation
        $availableMonths = $this->getAvailableMonths();

        return Inertia::render('Payroll/SalaryDetails', [
            'employee' => $employee,
            'payroll' => $payrollData,
            'attendances' => $attendances,
            'overtimes' => $overtimes,
            'selected_month' => $month,
            'available_months' => $availableMonths,
            'filters' => $request->only(['month', 'per_page', 'att_page', 'ot_page']),
        ]);
    }

    /**
     * Show the form for editing salary.
     */
    public function edit(User $employee)
    {
        $employee->load(['shift', 'location']);

        return Inertia::render('Payroll/SalaryEdit', [
            'employee' => $employee,
        ]);
    }

    /**
     * Update salary information for an employee.
     */
    public function update(Request $request, User $employee)
    {
        $validated = $request->validate([
            'basic_salary' => 'required|numeric|min:0',
            'hourly_rate' => 'nullable|numeric|min:0',
            'bank_name' => 'nullable|string|max:255',
            'bank_account' => 'nullable|string|max:255',
            'bank_account_name' => 'nullable|string|max:255',
            'allowances' => 'nullable|array',
            'allowances.*.name' => 'required|string|max:255',
            'allowances.*.amount' => 'required|numeric|min:0',
            'deductions' => 'nullable|array',
            'deductions.*.name' => 'required|string|max:255',
            'deductions.*.amount' => 'required|numeric|min:0',
        ]);

        $employee->update($validated);

        return redirect()->route('payroll.show', $employee->id)
            ->with('success', 'Salary information updated successfully');
    }
}
