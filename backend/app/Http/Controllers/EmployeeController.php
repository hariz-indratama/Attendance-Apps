<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Shift;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class EmployeeController extends Controller
{
    // Position prefix mapping for employee ID generation
    private array $positionPrefixes = [
        'manager' => 'MGR',
        'director' => 'DIR',
        'supervisor' => 'SPV',
        'engineer' => 'ENG',
        'developer' => 'DEV',
        'designer' => 'DSG',
        'operator' => 'OPR',
        'accountant' => 'ACC',
        'hr' => 'HR',
        'admin' => 'ADM',
        'staff' => 'STF',
        'assistant' => 'AST',
        'intern' => 'INT',
        'cook' => 'COK',
        'cashier' => 'CASH',
        'waiter' => 'WAIT',
        'security' => 'SEC',
        'cleaner' => 'CLN',
        'driver' => 'DRV',
        'technician' => 'TECH',
    ];

    /**
     * Generate employee ID based on position with auto-increment.
     */
    private function generateEmployeeId(string $position): string
    {
        $prefix = 'EMP';
        $positionLower = strtolower($position);

        foreach ($this->positionPrefixes as $key => $value) {
            if (str_contains($positionLower, $key)) {
                $prefix = $value;
                break;
            }
        }

        // Get the last employee ID with this position prefix
        $lastEmployee = User::where('employee_id', 'like', "MSS{$prefix}%")
            ->orderByDesc('employee_id')
            ->first();

        $nextNumber = 1;
        if ($lastEmployee) {
            // Extract the numeric part from the last employee ID (e.g., MSSDEV001 -> 001)
            $lastNumber = (int) substr($lastEmployee->employee_id, -3);
            $nextNumber = $lastNumber + 1;
        }

        $number = str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        return "MSS{$prefix}{$number}";
    }
    /**
     * Display a listing of employees.
     */
    public function index(Request $request)
    {
        $query = User::query()->with(['shift', 'location']);

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('position', 'like', "%{$search}%")
                  ->orWhere('department', 'like', "%{$search}%");
            });
        }

        // Filter by department
        if ($request->has('department') && $request->department) {
            $query->where('department', $request->department);
        }

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('is_active', $request->status === 'active');
        }

        // Filter by shift
        if ($request->has('shift_id') && $request->shift_id) {
            $query->where('shift_id', $request->shift_id);
        }

        // Sort
        $sortBy = $request->get('sortBy', 'created_at');
        $sortDir = $request->get('sortDir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        // Pagination
        $perPage = $request->get('perPage', 10);
        $employees = $query->paginate($perPage);

        // Get unique departments for filter (static + dynamic)
        $staticDepartments = ['HR', 'IT', 'Operations', 'Finance', 'Marketing', 'Sales', 'Production'];
        $dbDepartments = User::distinct()->pluck('department')->filter()->values();
        $departments = $staticDepartments + $dbDepartments->toArray();

        // Get unique positions for filter (static + dynamic)
        $staticPositions = ['Manager', 'Director', 'Supervisor', 'Developer', 'Designer', 'Operator', 'Engineer', 'Accountant', 'HR', 'Admin', 'Staff', 'Assistant', 'Intern', 'Cook', 'Cashier', 'Waiter', 'Security', 'Cleaner', 'Driver', 'Technician'];
        $dbPositions = User::distinct()->pluck('position')->filter()->values();
        $positions = $staticPositions + $dbPositions->toArray();

        // Get shifts and locations for form
        $shifts = Shift::where('is_active', true)->get();
        $locations = Location::where('is_active', true)->get();

        return Inertia::render('Employee/Index', [
            'employees' => $employees,
            'departments' => $departments,
            'positions' => $positions,
            'shifts' => $shifts,
            'locations' => $locations,
            'filters' => $request->only(['search', 'department', 'status', 'shift_id', 'sortBy', 'sortDir', 'perPage']),
        ]);
    }

    /**
     * Store a newly created employee.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|regex:/^(\+62|62|0)[0-9]{9,11}$/',
            'position' => 'required|string|max:100',
            'department' => 'required|string|max:100',
            'shift_id' => 'nullable|exists:shifts,id',
            'location_id' => 'nullable|exists:locations,id',
            'role' => 'required|in:employee,manager,admin',
            'is_active' => 'boolean',
        ]);

        // Auto-generate employee_id based on position
        $validated['employee_id'] = $this->generateEmployeeId($validated['position']);
        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()->route('employees.index')
            ->with('success', 'Employee created successfully.');
    }

    /**
     * Display the specified employee.
     */
    public function show(User $employee)
    {
        $employee->load(['shift', 'location', 'attendances', 'schedules', 'overtimes']);

        return Inertia::render('Employee/Show', [
            'employee' => $employee,
        ]);
    }

    /**
     * Update the specified employee.
     */
    public function update(Request $request, User $employee)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $employee->id,
            'phone' => 'nullable|string|regex:/^(\+62|62|0)[0-9]{9,11}$/',
            'position' => 'required|string|max:100',
            'department' => 'required|string|max:100',
            'shift_id' => 'nullable|exists:shifts,id',
            'location_id' => 'nullable|exists:locations,id',
            'role' => 'required|in:employee,manager,admin',
            'is_active' => 'boolean',
        ]);

        // Handle password update
        if ($request->has('password') && $request->password) {
            $request->validate([
                'password' => 'string|min:8|confirmed',
            ]);
            $validated['password'] = Hash::make($request->password);
        }

        // Regenerate employee_id only if position changed
        if ($employee->position !== $validated['position']) {
            $validated['employee_id'] = $this->generateEmployeeId($validated['position']);
        }

        $employee->update($validated);

        return redirect()->route('employees.index')
            ->with('success', 'Employee updated successfully.');
    }

    /**
     * Remove the specified employee.
     */
    public function destroy(User $employee)
    {
        // Prevent deleting own account
        if (Auth::id() === $employee->id) {
            return redirect()->route('employees.index')
                ->with('error', 'You cannot delete your own account.');
        }

        $employee->delete();

        return redirect()->route('employees.index')
            ->with('success', 'Employee deleted successfully.');
    }

    /**
     * Toggle employee active status.
     */
    public function toggleStatus(User $employee)
    {
        // Prevent deactivating own account
        if (Auth::id() === $employee->id) {
            return response()->json([
                'error' => 'You cannot deactivate your own account.'
            ], 422);
        }

        $employee->update(['is_active' => !$employee->is_active]);

        return response()->json([
            'success' => true,
            'is_active' => $employee->is_active,
        ]);
    }

    /**
     * Get employee statistics.
     */
    public function statistics()
    {
        $totalEmployees = User::count();
        $activeEmployees = User::where('is_active', true)->count();
        $inactiveEmployees = User::where('is_active', false)->count();
        $byDepartment = User::select('department')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('department')
            ->get();
        $byRole = User::select('role')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('role')
            ->get();

        return response()->json([
            'total' => $totalEmployees,
            'active' => $activeEmployees,
            'inactive' => $inactiveEmployees,
            'by_department' => $byDepartment,
            'by_role' => $byRole,
        ]);
    }
}
