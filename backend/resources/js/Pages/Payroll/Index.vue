 <template>
    <AppLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Payroll</h1>
                    <p class="text-sm text-gray-500 mt-1">Manage employee salaries and payroll</p>
                </div>
                <div class="flex items-center gap-3">
                    <select
                        v-model="selectedMonth"
                        @change="changeMonth"
                        class="flex h-10 items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <option v-for="month in availableMonths" :key="month.value" :value="month.value">
                            {{ month.label }}
                        </option>
                    </select>
                    <Button class="bg-blue-600 hover:bg-blue-700">
                        <Download class="w-4 h-4 mr-2" />
                        Export
                    </Button>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
                <!-- Total Employees -->
                <Card>
                    <CardContent class="flex items-center gap-3 p-4">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 text-blue-600">
                            <Users class="w-5 h-5" />
                        </div>
                        <div class="flex flex-col">
                            <span class="text-xs text-gray-500">Total Employees</span>
                            <span class="text-xl font-bold text-gray-900">{{ summary.total_employees }}</span>
                        </div>
                    </CardContent>
                </Card>

                <!-- Total Basic Salary -->
                <Card>
                    <CardContent class="flex items-center gap-3 p-4">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-100 text-green-600">
                            <DollarSign class="w-5 h-5" />
                        </div>
                        <div class="flex flex-col">
                            <span class="text-xs text-gray-500">Total Basic Salary</span>
                            <span class="text-xl font-bold text-gray-900">${{ formatNumber(summary.total_basic_salary) }}</span>
                        </div>
                    </CardContent>
                </Card>

                <!-- Total Allowances -->
                <Card>
                    <CardContent class="flex items-center gap-3 p-4">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-yellow-100 text-yellow-600">
                            <Star class="w-5 h-5" />
                        </div>
                        <div class="flex flex-col">
                            <span class="text-xs text-gray-500">Total Allowances</span>
                            <span class="text-xl font-bold text-gray-900">${{ formatNumber(summary.total_allowances) }}</span>
                        </div>
                    </CardContent>
                </Card>

                <!-- Total Deductions -->
                <Card>
                    <CardContent class="flex items-center gap-3 p-4">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-red-100 text-red-600">
                            <MinusCircle class="w-5 h-5" />
                        </div>
                        <div class="flex flex-col">
                            <span class="text-xs text-gray-500">Total Deductions</span>
                            <span class="text-xl font-bold text-gray-900">${{ formatNumber(summary.total_deductions) }}</span>
                        </div>
                    </CardContent>
                </Card>

                <!-- Total Net Salary (Highlighted) -->
                <Card class="bg-gradient-to-br from-blue-600 to-purple-600 border-none">
                    <CardContent class="flex items-center gap-3 p-4">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-white/20 text-white">
                            <CreditCard class="w-5 h-5" />
                        </div>
                        <div class="flex flex-col">
                            <span class="text-xs text-white/80">Total Net Salary</span>
                            <span class="text-xl font-bold text-white">${{ formatNumber(summary.total_net_salary) }}</span>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Payroll Table Card -->
            <Card>
                <CardHeader class="flex flex-row items-center justify-between pb-2">
                    <CardTitle>Payroll Details</CardTitle>
                    <span class="text-sm text-gray-500">{{ payrollData.total }} employees</span>
                </CardHeader>
                <CardContent>
                    <div class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Employee</TableHead>
                                    <TableHead>Department</TableHead>
                                    <TableHead>Work Days</TableHead>
                                    <TableHead>Basic Salary</TableHead>
                                    <TableHead>Overtime</TableHead>
                                    <TableHead>Allowances</TableHead>
                                    <TableHead>Deductions</TableHead>
                                    <TableHead>Net Salary</TableHead>
                                    <TableHead class="w-[50px]">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="employee in payrollData.data" :key="employee.id">
                                    <TableCell>
                                        <div class="flex items-center gap-3">
                                            <Avatar class="h-10 w-10 bg-gradient-to-br from-blue-600 to-cyan-500">
                                                <AvatarFallback class="text-white text-sm font-medium">
                                                    {{ getInitials(employee.name) }}
                                                </AvatarFallback>
                                            </Avatar>
                                            <div class="flex flex-col">
                                                <span class="font-medium text-gray-900">{{ employee.name }}</span>
                                                <span class="text-xs text-gray-500">{{ employee.employee_id || '-' }}</span>
                                            </div>
                                        </div>
                                    </TableCell>
                                    <TableCell>{{ employee.department || '-' }}</TableCell>
                                    <TableCell>
                                        <div class="flex items-center gap-1 text-sm">
                                            <span class="text-green-600 font-semibold">{{ employee.present_days }}</span>
                                            <span class="text-gray-400">/</span>
                                            <span class="text-gray-900">{{ employee.work_days }}</span>
                                            <span v-if="employee.absent_days > 0" class="text-red-500 text-xs" title="Absent days">
                                                (-{{ employee.absent_days }})
                                            </span>
                                        </div>
                                    </TableCell>
                                    <TableCell class="font-semibold text-gray-900">${{ formatNumber(employee.basic_salary) }}</TableCell>
                                    <TableCell>
                                        <div class="flex flex-col">
                                            <span class="text-sm">{{ employee.overtime_hours }}h</span>
                                            <span class="text-xs text-green-600">+${{ formatNumber(employee.overtime_pay) }}</span>
                                        </div>
                                    </TableCell>
                                    <TableCell class="text-green-600 font-medium">+${{ formatNumber(employee.total_allowances) }}</TableCell>
                                    <TableCell class="text-red-600 font-medium">-${{ formatNumber(employee.total_deductions) }}</TableCell>
                                    <TableCell class="font-bold text-blue-600">${{ formatNumber(employee.net_salary) }}</TableCell>
                                    <TableCell>
                                        <DropdownMenu v-slot="{ isOpen, close }">
                                            <DropdownMenuTrigger as-child>
                                                <Button variant="ghost" size="icon" class="h-8 w-8">
                                                    <MoreHorizontal class="h-4 w-4" />
                                                </Button>
                                            </DropdownMenuTrigger>
                                            <div v-if="isOpen" class="absolute right-0 mt-2 w-48 rounded-xl border bg-white p-1 shadow-lg z-50">
                                                <DropdownMenuItem class="flex items-center gap-2 cursor-pointer" @click="viewDetails(employee); close()">
                                                    <Eye class="mr-2 h-4 w-4" />
                                                    View Details
                                                </DropdownMenuItem>
                                                <DropdownMenuItem class="flex items-center gap-2 cursor-pointer" @click="editEmployee(employee); close()">
                                                    <Pencil class="mr-2 h-4 w-4" />
                                                    Edit Salary
                                                </DropdownMenuItem>
                                            </div>
                                        </DropdownMenu>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                            <tfoot>
                                <TableRow class="bg-gray-50">
                                    <TableCell colspan="3" class="font-semibold">Total</TableCell>
                                    <TableCell class="font-semibold text-gray-900">${{ formatNumber(summary.total_basic_salary) }}</TableCell>
                                    <TableCell class="font-semibold">${{ formatNumber(summary.total_overtime) }}</TableCell>
                                    <TableCell class="font-semibold text-green-600">+${{ formatNumber(summary.total_allowances) }}</TableCell>
                                    <TableCell class="font-semibold text-red-600">-${{ formatNumber(summary.total_deductions) }}</TableCell>
                                    <TableCell class="font-semibold text-blue-600">${{ formatNumber(summary.total_net_salary) }}</TableCell>
                                    <TableCell></TableCell>
                                </TableRow>
                            </tfoot>
                        </Table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="payrollData.last_page > 1" class="flex items-center justify-between mt-4 pt-4 border-t">
                        <span class="text-sm text-gray-500">
                            Showing {{ payrollData.from }} to {{ payrollData.to }} of {{ payrollData.total }} entries
                        </span>
                        <div class="flex items-center gap-1">
                            <Button
                                variant="outline"
                                size="sm"
                                :disabled="payrollData.current_page === 1"
                                @click="changePage(payrollData.current_page - 1)"
                            >
                                <ChevronLeft class="h-4 w-4" />
                                Prev
                            </Button>
                            <Button
                                v-for="page in visiblePages"
                                :key="page"
                                variant="outline"
                                size="sm"
                                :class="page === payrollData.current_page ? 'bg-blue-600 text-white border-blue-600 hover:bg-blue-700 hover:border-blue-700' : ''"
                                :disabled="page === '...'"
                                @click="changePage(page)"
                            >
                                {{ page }}
                            </Button>
                            <Button
                                variant="outline"
                                size="sm"
                                :disabled="payrollData.current_page === payrollData.last_page"
                                @click="changePage(payrollData.current_page + 1)"
                            >
                                Next
                                <ChevronRight class="h-4 w-4" />
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

import {
    Button,
    Card,
    CardHeader,
    CardTitle,
    CardContent,
    Table,
    TableHeader,
    TableBody,
    TableRow,
    TableHead,
    TableCell,
    Avatar,
    AvatarFallback,
    DropdownMenu,
    DropdownMenuTrigger,
    DropdownMenuItem,
} from '@/components/ui';

import {
    Download,
    Users,
    DollarSign,
    Star,
    MinusCircle,
    CreditCard,
    MoreHorizontal,
    Eye,
    Pencil,
    ChevronLeft,
    ChevronRight
} from 'lucide-vue-next';

// Types
interface MonthOption {
    value: string;
    label: string;
}

interface Employee {
    id: number;
    name: string;
    employee_id: string | null;
    department: string | null;
    present_days: number;
    work_days: number;
    absent_days: number;
    basic_salary: number;
    overtime_hours: number;
    overtime_pay: number;
    total_allowances: number;
    total_deductions: number;
    net_salary: number;
}

interface PayrollData {
    data: Employee[];
    current_page: number;
    last_page: number;
    total: number;
    from: number;
    to: number;
}

interface Summary {
    total_employees: number;
    total_basic_salary: number;
    total_allowances: number;
    total_deductions: number;
    total_overtime: number;
    total_net_salary: number;
}

const page = usePage();

const payrollData = computed(() => (page.props.payroll_data as PayrollData) || {
    data: [],
    current_page: 1,
    last_page: 1,
    total: 0,
    from: 0,
    to: 0
});

const summary = computed(() => (page.props.summary as Summary) || {
    total_employees: 0,
    total_basic_salary: 0,
    total_allowances: 0,
    total_deductions: 0,
    total_overtime: 0,
    total_net_salary: 0
});

const availableMonths = computed(() => (page.props.available_months as MonthOption[]) || []);
const selectedMonth = ref(page.props.selected_month as string || '');

const formatNumber = (num: number) => {
    return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(num || 0);
};

const getInitials = (name: string) => {
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
};

const changeMonth = () => {
    window.location.href = `/payroll?month=${selectedMonth.value}`;
};

// Pagination
const changePage = (pageNum: number | string) => {
    if (pageNum === '...') return;
    const url = new URL(window.location.href);
    url.searchParams.set('page', String(pageNum));
    window.location.href = url.toString();
};

const visiblePages = computed(() => {
    const current = payrollData.value.current_page;
    const last = payrollData.value.last_page;
    const delta = 2;
    const range: (number | string)[] = [];
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
});

const viewDetails = (employee: Employee) => {
    window.location.href = `/payroll/employee/${employee.id}?month=${selectedMonth.value}`;
};

const editEmployee = (employee: Employee) => {
    window.location.href = `/payroll/employee/${employee.id}/edit`;
};
</script>
