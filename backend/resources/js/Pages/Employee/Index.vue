<template>
    <AppLayout>
        <div class="space-y-6">
            <!-- Page Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Employees</h1>
                    <p class="text-sm text-gray-500 mt-1">Manage your team members and their information</p>
                </div>
                <Button @click="openModal('create')">
                    <Plus class="w-4 h-4" />
                    Add Employee
                </Button>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <Card>
                    <CardContent class="p-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                                <Users class="w-5 h-5 text-blue-600" />
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-gray-900">{{ employees.total }}</p>
                                <p class="text-sm text-gray-500">Total Employees</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="p-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center">
                                <UserCheck class="w-5 h-5 text-green-600" />
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-gray-900">{{ activeCount }}</p>
                                <p class="text-sm text-gray-500">Active</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="p-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center">
                                <UserX class="w-5 h-5 text-gray-600" />
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-gray-900">{{ inactiveCount }}</p>
                                <p class="text-sm text-gray-500">Inactive</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Filters & Search -->
            <Card>
                <CardContent class="p-4">
                    <div class="flex flex-col sm:flex-row gap-4">
                        <div class="relative flex-1">
                            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                            <Input
                                v-model="searchQuery"
                                placeholder="Search employees..."
                                class="pl-10"
                                @input="debouncedSearch"
                            />
                        </div>
                        <div class="flex gap-2">
                            <select
                                v-model="selectedDepartment"
                                class="h-10 px-3 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                @change="applyFilters"
                            >
                                <option value="">All Departments</option>
                                <option v-for="dept in departments" :key="dept" :value="dept">{{ dept }}</option>
                            </select>
                            <select
                                v-model="selectedStatus"
                                class="h-10 px-3 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                @change="applyFilters"
                            >
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                            <select
                                v-model="selectedShift"
                                class="h-10 px-3 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                @change="applyFilters"
                            >
                                <option value="">All Shifts</option>
                                <option v-for="shift in shifts" :key="shift.id" :value="shift.id">{{ shift.name }}</option>
                            </select>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Employee Table -->
            <Card>
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead class="cursor-pointer" @click="sortBy('name')">
                                <div class="flex items-center gap-1">
                                    Employee
                                    <ArrowUpDown class="w-3 h-3" v-if="sortByColumn !== 'name'" />
                                    <ArrowUp class="w-3 h-3" v-else-if="sortDir === 'asc'" />
                                    <ArrowDown class="w-3 h-3" v-else />
                                </div>
                            </TableHead>
                            <TableHead class="cursor-pointer" @click="sortBy('employee_id')">
                                <div class="flex items-center gap-1">
                                    ID
                                    <ArrowUpDown class="w-3 h-3" v-if="sortByColumn !== 'employee_id'" />
                                </div>
                            </TableHead>
                            <TableHead>Department</TableHead>
                            <TableHead>Position</TableHead>
                            <TableHead>Shift</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead>Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="employee in employees.data" :key="employee.id">
                            <TableCell>
                                <div class="flex items-center gap-3">
                                    <Avatar :class="getAvatarClass(employee.name)">
                                        {{ getInitials(employee.name) }}
                                    </Avatar>
                                    <div>
                                        <p class="font-medium text-gray-900">{{ employee.name }}</p>
                                        <p class="text-sm text-gray-500">{{ employee.email }}</p>
                                    </div>
                                </div>
                            </TableCell>
                            <TableCell>
                                <span class="font-mono text-sm">{{ employee.employee_id }}</span>
                            </TableCell>
                            <TableCell>
                                <Badge variant="secondary">{{ employee.department }}</Badge>
                            </TableCell>
                            <TableCell>{{ employee.position }}</TableCell>
                            <TableCell>
                                <Badge v-if="employee.shift" variant="outline">{{ employee.shift.name }}</Badge>
                                <span v-else class="text-gray-400">-</span>
                            </TableCell>
                            <TableCell>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input
                                        type="checkbox"
                                        :checked="employee.is_active"
                                        class="sr-only peer"
                                        @change="toggleStatus(employee)"
                                    >
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </TableCell>
                            <TableCell>
                                <div class="flex items-center gap-1">
                                    <Button variant="ghost" size="icon" @click="viewEmployee(employee)">
                                        <Eye class="w-4 h-4" />
                                    </Button>
                                    <Button variant="ghost" size="icon" @click="openModal('edit', employee)">
                                        <Pencil class="w-4 h-4" />
                                    </Button>
                                    <Button variant="ghost" size="icon" @click="confirmDelete(employee)">
                                        <Trash2 class="w-4 h-4 text-red-500" />
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>

                <!-- Pagination -->
                <div v-if="employees.last_page > 1" class="flex items-center justify-between p-4 border-t">
                    <p class="text-sm text-gray-500">
                        Showing {{ employees.from }} to {{ employees.to }} of {{ employees.total }} entries
                    </p>
                    <div class="flex items-center gap-1">
                        <Button
                            variant="outline"
                            size="icon"
                            :disabled="employees.current_page === 1"
                            @click="changePage(employees.current_page - 1)"
                        >
                            <ChevronLeft class="w-4 h-4" />
                        </Button>
                        <Button
                            v-for="page in visiblePages"
                            :key="page"
                            :variant="page === employees.current_page ? 'default' : 'outline'"
                            size="icon"
                            @click="changePage(page as number)"
                            :disabled="page === '...'"
                        >
                            {{ page }}
                        </Button>
                        <Button
                            variant="outline"
                            size="icon"
                            :disabled="employees.current_page === employees.last_page"
                            @click="changePage(employees.current_page + 1)"
                        >
                            <ChevronRight class="w-4 h-4" />
                        </Button>
                    </div>
                </div>
            </Card>
        </div>

        <!-- Create/Edit Modal -->
        <Dialog :open="showModal" @update:open="showModal = $event">
            <DialogContent class="max-w-2xl max-h-[90vh] overflow-y-auto">
                <DialogHeader>
                    <DialogTitle>
                        {{ modalMode === 'create' ? 'Add New Employee' : modalMode === 'edit' ? 'Edit Employee' : 'Employee Details' }}
                    </DialogTitle>
                    <DialogDescription v-if="modalMode === 'view'">
                        View employee information
                    </DialogDescription>
                </DialogHeader>

                <!-- View Mode -->
                <div v-if="modalMode === 'view' && selectedEmployee" class="space-y-6">
                    <div class="flex items-center gap-4">
                        <Avatar size="lg" :class="getAvatarClass(selectedEmployee.name)">
                            {{ getInitials(selectedEmployee.name) }}
                        </Avatar>
                        <div>
                            <h3 class="text-lg font-semibold">{{ selectedEmployee.name }}</h3>
                            <p class="text-gray-500">{{ selectedEmployee.position }}</p>
                            <Badge :variant="selectedEmployee.is_active ? 'success' : 'secondary'">
                                {{ selectedEmployee.is_active ? 'Active' : 'Inactive' }}
                            </Badge>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="text-gray-500">Employee ID</p>
                            <p class="font-medium">{{ selectedEmployee.employee_id }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Email</p>
                            <p class="font-medium">{{ selectedEmployee.email }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Phone</p>
                            <p class="font-medium">{{ selectedEmployee.phone || '-' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Department</p>
                            <p class="font-medium">{{ selectedEmployee.department }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Position</p>
                            <p class="font-medium">{{ selectedEmployee.position }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Shift</p>
                            <p class="font-medium">{{ selectedEmployee.shift?.name || 'Not assigned' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Location</p>
                            <p class="font-medium">{{ selectedEmployee.location?.name || 'Not assigned' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Role</p>
                            <p class="font-medium capitalize">{{ selectedEmployee.role }}</p>
                        </div>
                    </div>
                </div>

                <!-- Form Mode -->
                <form v-else @submit.prevent="submitForm" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label for="name">Full Name</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                placeholder="Enter full name"
                                :class="formErrors.name ? 'border-red-500' : ''"
                            />
                            <p v-if="formErrors.name" class="text-xs text-red-500">{{ formErrors.name[0] }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="email">Email</Label>
                            <Input
                                id="email"
                                type="email"
                                v-model="form.email"
                                placeholder="Enter email address"
                                :class="formErrors.email ? 'border-red-500' : ''"
                            />
                            <p v-if="formErrors.email" class="text-xs text-red-500">{{ formErrors.email[0] }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="employee_id">Employee ID</Label>
                            <Input
                                id="employee_id"
                                v-model="form.employee_id"
                                placeholder="Auto-generated"
                                disabled
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="phone">Phone</Label>
                            <div class="flex">
                                <span class="inline-flex items-center px-3 rounded-l-lg border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">+62</span>
                                <Input
                                    id="phone"
                                    v-model="form.phone"
                                    placeholder="8123456789"
                                    class="rounded-l-none"
                                />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="position">Position</Label>
                            <select
                                id="position"
                                v-model="form.position"
                                class="flex h-10 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                                <option value="">Select position</option>
                                <option v-for="pos in positions" :key="pos" :value="pos">{{ pos }}</option>
                            </select>
                            <p v-if="formErrors.position" class="text-xs text-red-500">{{ formErrors.position[0] }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="department">Department</Label>
                            <Input
                                id="department"
                                v-model="form.department"
                                placeholder="Enter department"
                                list="departments-list"
                                :class="formErrors.department ? 'border-red-500' : ''"
                            />
                            <datalist id="departments-list">
                                <option v-for="dept in departments" :key="dept" :value="dept" />
                            </datalist>
                            <p v-if="formErrors.department" class="text-xs text-red-500">{{ formErrors.department[0] }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="shift_id">Shift</Label>
                            <select
                                id="shift_id"
                                v-model="form.shift_id"
                                class="flex h-10 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                                <option value="">Select shift</option>
                                <option v-for="shift in shifts" :key="shift.id" :value="shift.id">
                                    {{ shift.name }} ({{ shift.start_time }} - {{ shift.end_time }})
                                </option>
                            </select>
                        </div>

                        <div class="space-y-2">
                            <Label for="location_id">Location</Label>
                            <select
                                id="location_id"
                                v-model="form.location_id"
                                class="flex h-10 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                                <option value="">Select location</option>
                                <option v-for="location in locations" :key="location.id" :value="location.id">
                                    {{ location.name }}
                                </option>
                            </select>
                        </div>

                        <div class="space-y-2">
                            <Label for="role">Role</Label>
                            <select
                                id="role"
                                v-model="form.role"
                                class="flex h-10 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                                <option value="employee">Employee</option>
                                <option value="manager">Manager</option>
                                <option value="admin">Admin</option>
                            </select>
                            <p v-if="formErrors.role" class="text-xs text-red-500">{{ formErrors.role[0] }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label>Status</Label>
                            <div class="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    id="is_active"
                                    v-model="form.is_active"
                                    class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                >
                                <Label for="is_active" class="font-normal">Active</Label>
                            </div>
                        </div>

                        <div v-if="modalMode === 'create'" class="space-y-2">
                            <Label for="password">Password</Label>
                            <Input
                                id="password"
                                type="password"
                                v-model="form.password"
                                placeholder="Enter password"
                                :class="formErrors.password ? 'border-red-500' : ''"
                            />
                            <p v-if="formErrors.password" class="text-xs text-red-500">{{ formErrors.password[0] }}</p>
                        </div>

                        <div v-if="modalMode === 'create'" class="space-y-2">
                            <Label for="password_confirmation">Confirm Password</Label>
                            <Input
                                id="password_confirmation"
                                type="password"
                                v-model="form.password_confirmation"
                                placeholder="Confirm password"
                            />
                        </div>

                        <div v-if="modalMode === 'edit'" class="space-y-2 sm:col-span-2">
                            <Label for="new_password">New Password (leave blank to keep current)</Label>
                            <Input
                                id="new_password"
                                type="password"
                                v-model="form.password"
                                placeholder="Enter new password"
                            />
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <Button type="button" variant="outline" @click="showModal = false">Cancel</Button>
                        <Button type="submit" :disabled="submitting">
                            <Loader2 v-if="submitting" class="w-4 h-4 animate-spin" />
                            {{ modalMode === 'create' ? 'Create Employee' : 'Update Employee' }}
                        </Button>
                    </div>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Delete Confirmation Modal -->
        <Dialog :open="showDeleteModal" @update:open="showDeleteModal = $event">
            <DialogContent class="max-w-md">
                <DialogHeader>
                    <DialogTitle>Confirm Delete</DialogTitle>
                    <DialogDescription>
                        Are you sure you want to delete this employee? This action cannot be undone.
                    </DialogDescription>
                </DialogHeader>
                <div class="py-4">
                    <p class="text-gray-600">
                        You are about to delete <strong>{{ employeeToDelete?.name }}</strong>.
                    </p>
                </div>
                <div class="flex justify-end gap-3">
                    <Button variant="outline" @click="showDeleteModal = false">Cancel</Button>
                    <Button variant="destructive" :disabled="deleting" @click="deleteEmployee">
                        <Loader2 v-if="deleting" class="w-4 h-4 animate-spin" />
                        Delete
                    </Button>
                </div>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useToast } from '@/composables/useToast';
import type { User, Shift, Location, PaginatedData, Errors } from '@/types';
import {
    Plus, Search, Users, UserCheck, UserX, Eye, Pencil, Trash2,
    ArrowUpDown, ArrowUp, ArrowDown, ChevronLeft, ChevronRight, Loader2
} from 'lucide-vue-next';
import {
    Button,
    Input,
    Label,
    Card,
    CardContent,
    Badge,
    Avatar,
    Table,
    TableHeader,
    TableBody,
    TableRow,
    TableHead,
    TableCell,
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
} from '@/components/ui';

const { success, error } = useToast();

interface Props {
    employees: PaginatedData<User>;
    departments: string[];
    positions: string[];
    shifts: Shift[];
    locations: Location[];
    filters: Record<string, any>;
}

const props = defineProps<Props>();

interface EmployeeForm {
    name: string;
    email: string;
    employee_id: string;
    phone: string;
    position: string;
    department: string;
    shift_id: string | number;
    location_id: string | number;
    role: string;
    is_active: boolean;
    password: string;
    password_confirmation: string;
}

const searchQuery = ref(props.filters?.search || '');
const selectedDepartment = ref(props.filters?.department || '');
const selectedStatus = ref(props.filters?.status || '');
const selectedShift = ref(props.filters?.shift_id || '');
const sortByColumn = ref(props.filters?.sortBy || 'created_at');
const sortDir = ref(props.filters?.sortDir || 'desc');

const showModal = ref(false);
const modalMode = ref<'create' | 'edit' | 'view'>('create');
const selectedEmployee = ref<User | null>(null);
const formErrors = ref<Errors>({});
const submitting = ref(false);

const showDeleteModal = ref(false);
const employeeToDelete = ref<User | null>(null);
const deleting = ref(false);

const form = ref<EmployeeForm>({
    name: '',
    email: '',
    employee_id: '',
    phone: '',
    position: '',
    department: '',
    shift_id: '',
    location_id: '',
    role: 'employee',
    is_active: true,
    password: '',
    password_confirmation: '',
});

const activeCount = computed(() => props.employees.data?.filter(e => e.is_active).length || 0);
const inactiveCount = computed(() => props.employees.data?.filter(e => !e.is_active).length || 0);

const visiblePages = computed((): (number | string)[] => {
    const current = props.employees.current_page;
    const last = props.employees.last_page;
    const delta = 2;
    const range: (number | string)[] = [];
    for (let i = Math.max(2, current - delta); i <= Math.min(last - 1, current + delta); i++) {
        range.push(i);
    }
    if (current - delta > 2) range.unshift('...');
    if (current + delta < last - 1) range.push('...');
    range.unshift(1);
    if (last > 1) range.push(last);
    return range;
});

function getInitials(name: string): string {
    if (!name) return '';
    return name.split(' ').map((n: string) => n[0]).join('').toUpperCase().slice(0, 2);
}

function getAvatarClass(name: string): string {
    const colors = ['bg-blue-500', 'bg-purple-500', 'bg-green-500', 'bg-orange-500', 'bg-pink-500', 'bg-teal-500'];
    const index = name?.charCodeAt(0) % colors.length || 0;
    return colors[index];
}

let debounceTimer: ReturnType<typeof setTimeout> | null = null;
function debouncedSearch() {
    clearTimeout(debounceTimer!);
    debounceTimer = setTimeout(() => {
        applyFilters();
    }, 300);
}

function applyFilters() {
    router.get(window.route('employees.index'), {
        search: searchQuery.value,
        department: selectedDepartment.value,
        status: selectedStatus.value,
        shift_id: selectedShift.value,
        sortBy: sortByColumn.value,
        sortDir: sortDir.value,
    }, { replace: true });
}

function sortBy(column: string) {
    if (sortByColumn.value === column) {
        sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortByColumn.value = column;
        sortDir.value = 'asc';
    }
    applyFilters();
}

function changePage(page: number | string) {
    if (page === '...') return;
    router.get(window.route('employees.index'), {
        ...props.filters,
        page: page as number,
    }, { replace: true });
}

function openModal(mode: 'create' | 'edit' | 'view', employee: User | null = null) {
    modalMode.value = mode;
    formErrors.value = {};

    if (mode === 'edit' && employee) {
        selectedEmployee.value = employee;
        form.value = {
            name: employee.name,
            email: employee.email,
            employee_id: employee.employee_id || '',
            phone: formatPhoneForDisplay((employee as any).phone || ''),
            position: employee.position || '',
            department: employee.department || '',
            shift_id: (employee as any).shift_id || '',
            location_id: (employee as any).location_id || '',
            role: employee.role || 'employee',
            is_active: employee.is_active ?? true,
            password: '',
            password_confirmation: '',
        };
    } else if (mode === 'create') {
        selectedEmployee.value = null;
        form.value = {
            name: '',
            email: '',
            employee_id: '',
            phone: '',
            position: '',
            department: '',
            shift_id: '',
            location_id: '',
            role: 'employee',
            is_active: true,
            password: '',
            password_confirmation: '',
        };
    } else if (mode === 'view' && employee) {
        selectedEmployee.value = employee;
    }

    showModal.value = true;
}

function viewEmployee(employee: User) {
    openModal('view', employee);
}

function formatPhoneForStorage(phone: string): string {
    if (!phone) return '';
    let digits = phone.replace(/^(\+62|62|0)/, '');
    return '62' + digits;
}

function formatPhoneForDisplay(phone: string): string {
    if (!phone) return '';
    if (phone.startsWith('+62')) return phone;
    if (phone.startsWith('62')) return '+' + phone;
    if (phone.startsWith('0')) return '+62' + phone.substring(1);
    return '+62' + phone;
}

function submitForm() {
    submitting.value = true;
    formErrors.value = {};

    const formData = { ...form.value };
    formData.phone = formatPhoneForStorage(formData.phone);

    if (modalMode.value === 'create') {
        router.post(window.route('employees.store'), formData as any, {
            onError: (errors: Errors) => {
                formErrors.value = errors;
                submitting.value = false;
                error('Failed to create employee');
            },
            onSuccess: () => {
                showModal.value = false;
                submitting.value = false;
                success('Employee created successfully');
            },
        });
    } else if (selectedEmployee.value && selectedEmployee.value.id) {
        router.put(window.route('employees.update', selectedEmployee.value.id), formData as any, {
            onError: (errors: Errors) => {
                formErrors.value = errors;
                submitting.value = false;
                error('Failed to update employee');
            },
            onSuccess: () => {
                showModal.value = false;
                submitting.value = false;
                success('Employee updated successfully');
            },
        });
    }
}

function toggleStatus(employee: User) {
    router.put(window.route('employees.update', employee.id), {
        ...employee,
        is_active: !employee.is_active,
    } as any, {
        onSuccess: () => {
            success('Employee status updated');
        },
        onError: () => {
            error('Failed to update status');
        },
    });
}

function confirmDelete(employee: User) {
    employeeToDelete.value = employee;
    showDeleteModal.value = true;
}

function deleteEmployee() {
    if (!employeeToDelete.value) return;

    deleting.value = true;
    router.delete(window.route('employees.destroy', employeeToDelete.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false;
            deleting.value = false;
            employeeToDelete.value = null;
            success('Employee deleted successfully');
        },
        onError: () => {
            deleting.value = false;
            error('Failed to delete employee');
        },
    });
}
</script>
