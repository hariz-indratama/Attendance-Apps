// User types
export interface User {
  id: number;
  name: string;
  email: string;
  employee_id?: string;
  phone?: string;
  position?: string;
  department?: string;
  role?: string;
  avatar?: string;
  is_active?: boolean;
  shift?: Shift;
  location?: Location;
  basic_salary?: number;
  hourly_rate?: number;
  bank_name?: string;
  bank_account?: string;
  bank_account_name?: string;
  allowances?: Allowance[];
  deductions?: Deduction[];
  [key: string]: any;
}

export interface Allowance {
  name: string;
  amount: number;
}

export interface Deduction {
  name: string;
  amount: number;
}

// Shift types
export interface Shift {
  id: number;
  name: string;
  start_time: string;
  end_time: string;
  break_start?: string;
  break_end?: string;
  is_night_shift?: boolean;
}

// Location types
export interface Location {
  id: number;
  name: string;
  address?: string;
  latitude?: number;
  longitude?: number;
  radius?: number;
}

// Attendance types
export type AttendanceStatusValue = 'hadir' | 'terlambat' | 'alpha' | 'sakit' | 'izin' | 'cuti' | 'onsite' | 'pending';

export const ATTENDANCE_STATUS_LABELS: Record<AttendanceStatusValue, string> = {
  hadir: 'Present',
  terlambat: 'Late',
  alpha: 'Absent',
  izin: 'Permission',
  sakit: 'Sick',
  cuti: 'Leave',
  onsite: 'On Site',
  pending: 'Pending',
};

export type AttendanceStatusVariant = 'default' | 'secondary' | 'destructive' | 'success' | 'warning' | 'outline';

export const ATTENDANCE_STATUS_VARIANTS: Record<AttendanceStatusValue, AttendanceStatusVariant> = {
  hadir: 'success',
  terlambat: 'warning',
  alpha: 'destructive',
  izin: 'default',
  sakit: 'secondary',
  cuti: 'secondary',
  onsite: 'default',
  pending: 'secondary',
};

export interface Attendance {
  id: number;
  user_id: number;
  date: string;
  status: 'hadir' | 'terlambat' | 'alpha' | 'sakit' | 'izin';
  check_in_time?: string;
  check_in_latitude?: number;
  check_in_longitude?: number;
  check_out_time?: string;
  check_out_latitude?: number;
  check_out_longitude?: number;
  notes?: string;
  user?: User;
  shift?: Shift;
  location?: Location;
  // Aliases used in some views
  clock_in_time?: string;
  clock_in_latitude?: number;
  clock_in_longitude?: number;
  clock_out_time?: string;
  clock_out_latitude?: number;
  clock_out_longitude?: number;
}

// Overtime types
export interface Overtime {
  id: number;
  user_id: number;
  date: string;
  start_time: string;
  end_time: string;
  hours?: number;
  status: 'pending' | 'approved' | 'rejected';
  reason?: string;
  user?: User;
}

// Schedule types
export interface Schedule {
  id: number;
  user_id: number;
  date: string;
  shift_id: number;
  shift?: Shift;
  status: 'active' | 'inactive';
  user?: User;
}

// Pagination types
export interface PaginatedData<T> {
  data: T[];
  current_page: number;
  last_page: number;
  total: number;
  from: number;
  to: number;
  per_page?: number;
}

// Flash messages
export interface Flash {
  success?: string;
  error?: string;
  warning?: string;
  info?: string;
}

// Form errors
export interface Errors {
  [key: string]: string | undefined;
}

// Payroll types
export interface PayrollData {
  id: number;
  employee_id: string;
  name: string;
  position?: string;
  department?: string;
  basic_salary: number;
  work_days: number;
  present_days: number;
  absent_days: number;
  late_days: number;
  overtime_hours: number;
  overtime_pay: number;
  allowances: Allowance[];
  total_allowances: number;
  deductions: Deduction[];
  absent_deduction: number;
  late_deduction: number;
  total_deductions: number;
  gross_salary: number;
  net_salary: number;
}

export interface PayrollSummary {
  total_employees: number;
  total_basic_salary: number;
  total_allowances: number;
  total_deductions: number;
  total_overtime: number;
  total_net_salary: number;
}

// Stats types
export interface Stats {
  present: number;
  absent: number;
  late: number;
  leave: number;
  permission?: number;
  avg_hours?: number;
  total?: number;
  [key: string]: any;
}

// Filter types
export interface Filters {
  month?: string;
  year?: string;
  status?: string;
  search?: string;
  per_page?: number;
  page?: number;
  date?: string;
  date_from?: string;
  date_to?: string;
  user_id?: string | number;
  shift_id?: string | number;
  location_id?: string | number;
  sortBy?: string;
  sortDir?: string;
  department?: string;
  [key: string]: any;
}
