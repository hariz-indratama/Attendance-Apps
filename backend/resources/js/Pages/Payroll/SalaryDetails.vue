<template>
    <AppLayout>
        <div class="salary-details">
            <!-- Header -->
            <div class="page-header">
                <div class="header-left">
                    <Link :href="route('payroll.index')" class="back-link">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M19 12H5M12 19l-7-7 7-7"/>
                        </svg>
                        Back to Payroll
                    </Link>
                    <h1 class="page-title">Salary Details</h1>
                    <p class="page-subtitle">View detailed salary information for {{ employee.name }}</p>
                </div>
                <div class="header-actions">
                    <div class="month-selector">
                        <select v-model="selectedMonth" @change="changeMonth" class="month-select">
                            <option v-for="month in availableMonths" :key="month.value" :value="month.value">
                                {{ month.label }}
                            </option>
                        </select>
                    </div>
                    <Link :href="`/payroll/employee/${employee.id}/edit`" class="edit-btn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                        </svg>
                        Edit Salary
                    </Link>
                </div>
            </div>

            <!-- Employee Info Card -->
            <div class="employee-card">
                <div class="employee-main">
                    <div class="employee-avatar">
                        {{ getInitials(employee.name) }}
                    </div>
                    <div class="employee-info">
                        <h2 class="employee-name">{{ employee.name }}</h2>
                        <p class="employee-meta">
                            <span v-if="employee.employee_id">ID: {{ employee.employee_id }}</span>
                            <span v-if="employee.position">{{ employee.position }}</span>
                            <span v-if="employee.department">{{ employee.department }}</span>
                        </p>
                    </div>
                </div>
                <div class="employee-badges">
                    <span v-if="employee.shift" class="badge shift">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <polyline points="12 6 12 12 16 14"/>
                        </svg>
                        {{ employee.shift.name }}
                    </span>
                    <span v-if="employee.location" class="badge location">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                            <circle cx="12" cy="10" r="3"/>
                        </svg>
                        {{ employee.location.name }}
                    </span>
                </div>
            </div>

            <!-- Salary Summary Cards -->
            <div class="summary-grid">
                <div class="summary-card">
                    <div class="summary-icon blue">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="12" y1="1" x2="12" y2="23"/>
                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                        </svg>
                    </div>
                    <div class="summary-info">
                        <span class="summary-label">Basic Salary</span>
                        <span class="summary-value">${{ formatNumber(payroll.basic_salary) }}</span>
                    </div>
                </div>

                <div class="summary-card">
                    <div class="summary-icon green">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <polyline points="12 6 12 12 16 14"/>
                        </svg>
                    </div>
                    <div class="summary-info">
                        <span class="summary-label">Overtime Pay</span>
                        <span class="summary-value">${{ formatNumber(payroll.overtime_pay) }}</span>
                        <span class="summary-sub">{{ payroll.overtime_hours }} hours</span>
                    </div>
                </div>

                <div class="summary-card">
                    <div class="summary-icon yellow">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                        </svg>
                    </div>
                    <div class="summary-info">
                        <span class="summary-label">Allowances</span>
                        <span class="summary-value">+${{ formatNumber(payroll.total_allowances) }}</span>
                    </div>
                </div>

                <div class="summary-card">
                    <div class="summary-icon red">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="15" y1="9" x2="9" y2="15"/>
                            <line x1="9" y1="9" x2="15" y2="15"/>
                        </svg>
                    </div>
                    <div class="summary-info">
                        <span class="summary-label">Deductions</span>
                        <span class="summary-value">-${{ formatNumber(payroll.total_deductions) }}</span>
                    </div>
                </div>

                <div class="summary-card highlight">
                    <div class="summary-icon white">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/>
                            <line x1="1" y1="10" x2="23" y2="10"/>
                        </svg>
                    </div>
                    <div class="summary-info">
                        <span class="summary-label">Net Salary</span>
                        <span class="summary-value">${{ formatNumber(payroll.net_salary) }}</span>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="content-grid">
                <!-- Salary Breakdown -->
                <div class="card breakdown-card">
                    <h3 class="card-title">Salary Breakdown</h3>

                    <div class="breakdown-section">
                        <h4 class="section-title">Earnings</h4>
                        <div class="breakdown-list">
                            <div class="breakdown-item">
                                <span class="item-label">Basic Salary</span>
                                <span class="item-value">${{ formatNumber(payroll.basic_salary) }}</span>
                            </div>
                            <div class="breakdown-item">
                                <span class="item-label">Overtime ({{ payroll.overtime_hours }}h x 1.5)</span>
                                <span class="item-value">+${{ formatNumber(payroll.overtime_pay) }}</span>
                            </div>
                            <div v-for="(allowance, index) in payroll.allowances" :key="'allowance-'+index" class="breakdown-item">
                                <span class="item-label">{{ allowance.name }}</span>
                                <span class="item-value">+${{ formatNumber(allowance.amount) }}</span>
                            </div>
                        </div>
                        <div class="breakdown-total">
                            <span class="total-label">Total Earnings</span>
                            <span class="total-value positive">${{ formatNumber(payroll.gross_salary) }}</span>
                        </div>
                    </div>

                    <div class="breakdown-section">
                        <h4 class="section-title">Deductions</h4>
                        <div class="breakdown-list">
                            <div v-if="payroll.absent_days > 0" class="breakdown-item">
                                <span class="item-label">Absent ({{ payroll.absent_days }} days)</span>
                                <span class="item-value negative">-${{ formatNumber(payroll.absent_deduction) }}</span>
                            </div>
                            <div v-if="payroll.late_days > 0" class="breakdown-item">
                                <span class="item-label">Late ({{ payroll.late_days }} times)</span>
                                <span class="item-value negative">-${{ formatNumber(payroll.late_deduction) }}</span>
                            </div>
                            <div v-for="(deduction, index) in payroll.deductions" :key="'deduction-'+index" class="breakdown-item">
                                <span class="item-label">{{ deduction.name }}</span>
                                <span class="item-value negative">-${{ formatNumber(deduction.amount) }}</span>
                            </div>
                        </div>
                        <div class="breakdown-total">
                            <span class="total-label">Total Deductions</span>
                            <span class="total-value negative">-${{ formatNumber(payroll.total_deductions) }}</span>
                        </div>
                    </div>

                    <div class="net-total">
                        <span class="net-label">Net Salary</span>
                        <span class="net-value">${{ formatNumber(payroll.net_salary) }}</span>
                    </div>
                </div>

                <!-- Work Summary -->
                <div class="card work-card">
                    <h3 class="card-title">Work Summary</h3>
                    <div class="work-stats">
                        <div class="work-stat">
                            <div class="stat-value present">{{ payroll.present_days }}</div>
                            <div class="stat-label">Present</div>
                        </div>
                        <div class="work-stat">
                            <div class="stat-value absent">{{ payroll.absent_days }}</div>
                            <div class="stat-label">Absent</div>
                        </div>
                        <div class="work-stat">
                            <div class="stat-value late">{{ payroll.late_days }}</div>
                            <div class="stat-label">Late</div>
                        </div>
                        <div class="work-stat">
                            <div class="stat-value total">{{ payroll.work_days }}</div>
                            <div class="stat-label">Total Days</div>
                        </div>
                    </div>
                </div>

                <!-- Bank Information -->
                <div class="card bank-card">
                    <h3 class="card-title">Bank Information</h3>
                    <div class="bank-details">
                        <div class="bank-item">
                            <span class="bank-label">Bank Name</span>
                            <span class="bank-value">{{ employee.bank_name || '-' }}</span>
                        </div>
                        <div class="bank-item">
                            <span class="bank-label">Account Number</span>
                            <span class="bank-value">{{ employee.bank_account || '-' }}</span>
                        </div>
                        <div class="bank-item">
                            <span class="bank-label">Account Name</span>
                            <span class="bank-value">{{ employee.bank_account_name || '-' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Attendance History -->
                <div class="card attendance-card">
                    <h3 class="card-title">Attendance History</h3>
                    <div class="table-container">
                        <table class="attendance-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Check In</th>
                                    <th>Check Out</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="attendance in attendances.data" :key="attendance.id">
                                    <td>{{ formatDate(attendance.date) }}</td>
                                    <td>
                                        <span :class="['status-badge', attendance.status]">
                                            {{ formatStatus(attendance.status) }}
                                        </span>
                                    </td>
                                    <td>{{ attendance.check_in_time || '-' }}</td>
                                    <td>{{ attendance.check_out_time || '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
                        <!-- Attendance Pagination -->
                        <div class="pagination-wrapper" v-if="attendances.last_page > 1">
                            <div class="pagination-info">
                                Showing {{ attendances.from }} to {{ attendances.to }} of {{ attendances.total }} entries
                            </div>
                            <div class="pagination-controls">
                                <button class="page-btn" :disabled="attendances.current_page === 1" @click="changePage('attendance', attendances.current_page - 1)">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="15 18 9 12 15 6"></polyline>
                                    </svg>
                                    Prev
                                </button>
                                <button v-for="page in getVisiblePages(attendances)" :key="page" class="page-btn" :class="{ active: page === attendances.current_page }" @click="changePage('attendance', page)">
                                    {{ page }}
                                </button>
                                <button class="page-btn" :disabled="attendances.current_page === attendances.last_page" @click="changePage('attendance', attendances.current_page + 1)">
                                    Next
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="9 18 15 12 9 6"></polyline>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div v-if="attendances.data.length === 0" class="empty-state">
                            No attendance records found for this month.
                        </div>
                    </div>
                </div>

                <!-- Overtime History Section -->
                <div class="card">
                    <h3 class="card-title">Overtime History</h3>
                    <div class="table-container">
                        <table class="detail-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Start Time</th>
                                    <th>End Time</th>
                                    <th>Hours</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="overtime in overtimes.data" :key="overtime.id">
                                    <td>{{ formatDate(overtime.date) }}</td>
                                    <td>{{ overtime.start_time || '-' }}</td>
                                    <td>{{ overtime.end_time || '-' }}</td>
                                    <td>{{ overtime.hours || '-' }}</td>
                                    <td>
                                        <span :class="['status-badge', overtime.status]">
                                            {{ overtime.status || 'approved' }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <!-- Overtime Pagination -->
                        <div class="pagination-wrapper" v-if="overtimes.last_page > 1">
                            <div class="pagination-info">
                                Showing {{ overtimes.from }} to {{ overtimes.to }} of {{ overtimes.total }} entries
                            </div>
                            <div class="pagination-controls">
                                <button class="page-btn" :disabled="overtimes.current_page === 1" @click="changePage('overtime', overtimes.current_page - 1)">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="15 18 9 12 15 6"></polyline>
                                    </svg>
                                    Prev
                                </button>
                                <button v-for="page in getVisiblePages(overtimes)" :key="page" class="page-btn" :class="{ active: page === overtimes.current_page }" @click="changePage('overtime', page)">
                                    {{ page }}
                                </button>
                                <button class="page-btn" :disabled="overtimes.current_page === overtimes.last_page" @click="changePage('overtime', overtimes.current_page + 1)">
                                    Next
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="9 18 15 12 9 6"></polyline>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div v-if="overtimes.data.length === 0" class="empty-state">
                            No overtime records found for this month.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { usePage, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const page = usePage();

const employee = computed(() => page.props.employee);
const payroll = computed(() => page.props.payroll || {});
const attendances = computed(() => page.props.attendances || {
    data: [],
    current_page: 1,
    last_page: 1,
    total: 0,
    from: 0,
    to: 0
});
const overtimes = computed(() => page.props.overtimes || {
    data: [],
    current_page: 1,
    last_page: 1,
    total: 0,
    from: 0,
    to: 0
});
const availableMonths = computed(() => page.props.available_months || []);
const selectedMonth = ref(page.props.selected_month || '');

const formatNumber = (num) => {
    return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(num || 0);
};

const getInitials = (name) => {
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        weekday: 'short',
        month: 'short',
        day: 'numeric'
    });
};

const formatStatus = (status) => {
    const statusMap = {
        'hadir': 'Present',
        'terlambat': 'Late',
        'alpha': 'Absent',
        'sakit': 'Sick',
        'izin': 'Permission'
    };
    return statusMap[status] || status;
};

const changeMonth = () => {
    window.location.href = `/payroll/employee/${employee.value.id}?month=${selectedMonth.value}`;
};

// Pagination functions
const changePage = (type, page) => {
    if (page === '...') return;
    const url = new URL(window.location.href);
    url.searchParams.set('page', page);
    if (type === 'attendance') {
        url.searchParams.set('att_page', page);
    } else if (type === 'overtime') {
        url.searchParams.set('ot_page', page);
    }
    window.location.href = url.toString();
};

const getVisiblePages = (paginatedData) => {
    const current = paginatedData.current_page;
    const last = paginatedData.last_page;
    const delta = 2;
    const range = [];
    for (let i = Math.max(2, current - delta); i <= Math.min(last - 1, current + delta); i++) {
        range.push(i);
    }
    if (current - delta > 2) {
        range.unshift('...');
    }
    if (current + delta < last - 1) {
        range.push('...');
    }
    range.unshift(1);
    if (last > 1) {
        range.push(last);
    }
    return range;
};

const route = (name, params = {}) => {
    // Simple route helper
    const routes = {
        'payroll.index': '/payroll'
    };
    let url = routes[name] || '/';
    if (params.id) {
        url = url.replace('{employee}', params.id);
    }
    return url;
};
</script>

<style scoped>
.salary-details {
    animation: fadeIn 0.4s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Header */
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 2rem;
}

.header-left {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.back-link {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: #6B7280;
    text-decoration: none;
    font-size: 0.875rem;
    transition: color 0.2s;
}

.back-link:hover {
    color: #2563EB;
}

.back-link svg {
    width: 16px;
    height: 16px;
}

.page-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1F2937;
    margin-bottom: 0.25rem;
}

.page-subtitle {
    font-size: 0.9rem;
    color: #6B7280;
}

.header-actions {
    display: flex;
    gap: 1rem;
}

.month-select {
    padding: 0.625rem 1rem;
    border: 1px solid #E5E7EB;
    border-radius: 10px;
    font-size: 0.9rem;
    color: #1F2937;
    background: white;
    cursor: pointer;
    outline: none;
}

.month-select:focus {
    border-color: #2563EB;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.edit-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 1rem;
    background: #2563EB;
    color: white;
    text-decoration: none;
    border-radius: 10px;
    font-size: 0.9rem;
    font-weight: 500;
    transition: background 0.2s;
}

.edit-btn:hover {
    background: #1D4ED8;
}

.edit-btn svg {
    width: 18px;
    height: 18px;
}

/* Employee Card */
.employee-card {
    background: white;
    border-radius: 16px;
    padding: 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.employee-main {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.employee-avatar {
    width: 56px;
    height: 56px;
    border-radius: 14px;
    background: linear-gradient(135deg, #2563EB 0%, #0EA5E9 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    font-weight: 600;
}

.employee-info {
    display: flex;
    flex-direction: column;
}

.employee-name {
    font-size: 1.25rem;
    font-weight: 600;
    color: #1F2937;
}

.employee-meta {
    display: flex;
    gap: 1rem;
    font-size: 0.875rem;
    color: #6B7280;
}

.employee-badges {
    display: flex;
    gap: 0.75rem;
}

.badge {
    display: flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.375rem 0.75rem;
    border-radius: 8px;
    font-size: 0.8rem;
    font-weight: 500;
}

.badge svg {
    width: 14px;
    height: 14px;
}

.badge.shift {
    background: rgba(139, 92, 246, 0.1);
    color: #8B5CF6;
}

.badge.location {
    background: rgba(16, 185, 129, 0.1);
    color: #10B981;
}

/* Summary Grid */
.summary-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.summary-card {
    background: white;
    border-radius: 16px;
    padding: 1.25rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
}

.summary-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.summary-card.highlight {
    background: linear-gradient(135deg, #2563EB 0%, #7C3AED 100%);
}

.summary-card.highlight .summary-label,
.summary-card.highlight .summary-value,
.summary-card.highlight .summary-sub {
    color: white;
}

.summary-card.highlight .summary-icon {
    background: rgba(255, 255, 255, 0.2);
    color: white;
}

.summary-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.summary-icon svg {
    width: 24px;
    height: 24px;
}

.summary-icon.blue {
    background: rgba(37, 99, 235, 0.1);
    color: #2563EB;
}

.summary-icon.green {
    background: rgba(16, 185, 129, 0.1);
    color: #10B981;
}

.summary-icon.yellow {
    background: rgba(245, 158, 11, 0.1);
    color: #F59E0B;
}

.summary-icon.red {
    background: rgba(239, 68, 68, 0.1);
    color: #EF4444;
}

.summary-icon.white {
    background: rgba(255, 255, 255, 0.2);
    color: white;
}

.summary-info {
    display: flex;
    flex-direction: column;
}

.summary-label {
    font-size: 0.8rem;
    color: #6B7280;
    margin-bottom: 0.25rem;
}

.summary-value {
    font-size: 1.25rem;
    font-weight: 700;
    color: #1F2937;
}

.summary-sub {
    font-size: 0.75rem;
    color: #6B7280;
}

/* Content Grid */
.content-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
}

.card {
    background: white;
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.breakdown-card {
    grid-column: span 2;
}

.card-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #1F2937;
    margin-bottom: 1.5rem;
}

/* Breakdown */
.breakdown-section {
    margin-bottom: 1.5rem;
}

.section-title {
    font-size: 0.85rem;
    font-weight: 600;
    color: #6B7280;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 1rem;
}

.breakdown-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.breakdown-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem;
    background: #F9FAFB;
    border-radius: 8px;
}

.item-label {
    font-size: 0.9rem;
    color: #4B5563;
}

.item-value {
    font-size: 0.9rem;
    font-weight: 600;
    color: #1F2937;
}

.item-value.positive {
    color: #10B981;
}

.item-value.negative {
    color: #EF4444;
}

.breakdown-total {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    margin-top: 0.75rem;
    border-radius: 8px;
    background: #F3F4F6;
}

.total-label {
    font-weight: 600;
    color: #4B5563;
}

.total-value {
    font-weight: 700;
    font-size: 1.1rem;
}

.total-value.positive {
    color: #10B981;
}

.total-value.negative {
    color: #EF4444;
}

.net-total {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.25rem;
    margin-top: 1rem;
    border-radius: 12px;
    background: linear-gradient(135deg, #2563EB 0%, #7C3AED 100%);
}

.net-label {
    font-size: 1rem;
    font-weight: 600;
    color: white;
}

.net-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: white;
}

/* Work Stats */
.work-stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
}

.work-stat {
    text-align: center;
    padding: 1rem;
    background: #F9FAFB;
    border-radius: 12px;
}

.stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 0.25rem;
}

.stat-value.present {
    color: #10B981;
}

.stat-value.absent {
    color: #EF4444;
}

.stat-value.late {
    color: #F59E0B;
}

.stat-value.total {
    color: #2563EB;
}

.stat-label {
    font-size: 0.8rem;
    color: #6B7280;
}

/* Bank Details */
.bank-details {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.bank-item {
    display: flex;
    justify-content: space-between;
    padding: 0.75rem;
    background: #F9FAFB;
    border-radius: 8px;
}

.bank-label {
    font-size: 0.9rem;
    color: #6B7280;
}

.bank-value {
    font-size: 0.9rem;
    font-weight: 500;
    color: #1F2937;
}

/* Attendance Table */
.attendance-card {
    grid-column: span 2;
}

.table-container {
    overflow-x: auto;
}

.attendance-table {
    width: 100%;
    border-collapse: collapse;
}

.attendance-table th,
.attendance-table td {
    padding: 0.75rem 1rem;
    text-align: left;
    border-bottom: 1px solid #E5E7EB;
}

.attendance-table th {
    font-size: 0.8rem;
    font-weight: 600;
    color: #6B7280;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    background: #F9FAFB;
}

.attendance-table tbody tr:hover {
    background: #F9FAFB;
}

.status-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 500;
}

.status-badge.hadir {
    background: rgba(16, 185, 129, 0.1);
    color: #10B981;
}

.status-badge.terlambat {
    background: rgba(245, 158, 11, 0.1);
    color: #F59E0B;
}

.status-badge.alpha {
    background: rgba(239, 68, 68, 0.1);
    color: #EF4444;
}

.status-badge.sakit {
    background: rgba(139, 92, 246, 0.1);
    color: #8B5CF6;
}

.status-badge.izin {
    background: rgba(59, 130, 246, 0.1);
    color: #3B82F6;
}

.empty-state {
    text-align: center;
    padding: 2rem;
    color: #6B7280;
}

/* Responsive */
@media (max-width: 1200px) {
    .summary-grid {
        grid-template-columns: repeat(3, 1fr);
    }

    .content-grid {
        grid-template-columns: 1fr;
    }

    .breakdown-card,
    .attendance-card {
        grid-column: span 1;
    }
}

@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        gap: 1rem;
    }

    .header-actions {
        width: 100%;
        flex-direction: column;
    }

    .employee-card {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }

    .summary-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .work-stats {
        grid-template-columns: repeat(2, 1fr);
    }
}

.pagination-wrapper {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 0;
    border-top: 1px solid #e5e7eb;
    margin-top: 16px;
}

.pagination-info {
    color: #6b7280;
    font-size: 14px;
}

.pagination-controls {
    display: flex;
    gap: 4px;
}

.page-btn {
    display: flex;
    align-items: center;
    gap: 4px;
    padding: 8px 12px;
    border: 1px solid #d1d5db;
    background: #fff;
    color: #374151;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
    transition: all 0.2s;
}

.page-btn:hover:not(:disabled) {
    background: #f3f4f6;
    border-color: #9ca3af;
}

.page-btn.active {
    background: #3b82f6;
    border-color: #3b82f6;
    color: #fff;
}

.page-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.page-btn svg {
    width: 16px;
    height: 16px;
}
</style>
