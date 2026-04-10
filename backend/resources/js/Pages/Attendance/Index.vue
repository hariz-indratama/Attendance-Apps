<template>
    <AppLayout>
        <div class="space-y-6">
            <!-- Page Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Attendance</h1>
                    <p class="text-sm text-slate-500">Track and manage employee attendance records</p>
                </div>
                <Button variant="outline" @click="exportData">
                    <Download class="w-4 h-4" />
                    Export
                </Button>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                <Card class="relative overflow-hidden hover:shadow-md transition-shadow">
                    <CardContent class="p-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl bg-emerald-100 flex items-center justify-center">
                                <CheckCircle class="w-6 h-6 text-emerald-600" />
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-slate-900">{{ stats.present }}</p>
                                <p class="text-xs text-slate-500">Present</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card class="relative overflow-hidden hover:shadow-md transition-shadow">
                    <CardContent class="p-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl bg-amber-100 flex items-center justify-center">
                                <Clock class="w-6 h-6 text-amber-600" />
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-slate-900">{{ stats.late }}</p>
                                <p class="text-xs text-slate-500">Late</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card class="relative overflow-hidden hover:shadow-md transition-shadow">
                    <CardContent class="p-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl bg-red-100 flex items-center justify-center">
                                <XCircle class="w-6 h-6 text-red-600" />
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-slate-900">{{ stats.absent }}</p>
                                <p class="text-xs text-slate-500">Absent</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card class="relative overflow-hidden hover:shadow-md transition-shadow">
                    <CardContent class="p-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center">
                                <FileText class="w-6 h-6 text-blue-600" />
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-slate-900">{{ stats.permission }}</p>
                                <p class="text-xs text-slate-500">Permission</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card class="relative overflow-hidden hover:shadow-md transition-shadow">
                    <CardContent class="p-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl bg-violet-100 flex items-center justify-center">
                                <Timer class="w-6 h-6 text-violet-600" />
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-slate-900">{{ stats.avg_hours }}h</p>
                                <p class="text-xs text-slate-500">Avg Hours</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card class="relative overflow-hidden hover:shadow-md transition-shadow">
                    <CardContent class="p-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl bg-teal-100 flex items-center justify-center">
                                <Users class="w-6 h-6 text-teal-600" />
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-slate-900">{{ stats.total }}</p>
                                <p class="text-xs text-slate-500">Total Records</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Filters Section -->
            <Card>
                <CardContent class="p-4">
                    <div class="flex flex-col lg:flex-row gap-4">
                        <div class="flex flex-wrap gap-3 flex-1">
                            <div class="flex flex-col gap-1">
                                <label class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Date</label>
                                <Input
                                    type="date"
                                    v-model="filters.date"
                                    class="w-36"
                                    @change="applyFilters"
                                />
                            </div>

                            <div class="flex flex-col gap-1">
                                <label class="text-xs font-semibold text-slate-500 uppercase tracking-wide">From</label>
                                <Input
                                    type="date"
                                    v-model="filters.date_from"
                                    class="w-36"
                                    @change="applyFilters"
                                />
                            </div>

                            <div class="flex flex-col gap-1">
                                <label class="text-xs font-semibold text-slate-500 uppercase tracking-wide">To</label>
                                <Input
                                    type="date"
                                    v-model="filters.date_to"
                                    class="w-36"
                                    @change="applyFilters"
                                />
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-3">
                            <select v-model="filters.user_id" class="h-10 px-3 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" @change="applyFilters">
                                <option value="">All Employees</option>
                                <option v-for="emp in employees" :key="emp.id" :value="emp.id">
                                    {{ emp.name }} ({{ emp.employee_id }})
                                </option>
                            </select>

                            <select v-model="filters.status" class="h-10 px-3 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" @change="applyFilters">
                                <option value="">All Status</option>
                                <option value="hadir">Present (On Time)</option>
                                <option value="terlambat">Late</option>
                                <option value="alpha">Absent</option>
                                <option value="izin">Permission</option>
                                <option value="sakit">Sick</option>
                                <option value="cuti">Leave</option>
                                <option value="onsite">On Site</option>
                            </select>

                            <select v-model="filters.shift_id" class="h-10 px-3 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" @change="applyFilters">
                                <option value="">All Shifts</option>
                                <option v-for="shift in shifts" :key="shift.id" :value="shift.id">
                                    {{ shift.name }}
                                </option>
                            </select>

                            <select v-model="filters.location_id" class="h-10 px-3 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" @change="applyFilters">
                                <option value="">All Locations</option>
                                <option v-for="loc in locations" :key="loc.id" :value="loc.id">
                                    {{ loc.name }}
                                </option>
                            </select>

                            <Button variant="ghost" size="sm" @click="clearFilters" v-if="hasFilters" class="text-slate-500 hover:text-red-500 hover:border-red-200">
                                <X class="w-4 h-4" />
                                Clear
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Attendance Table -->
            <Card>
                <Table v-if="attendances.data.length > 0">
                    <TableHeader>
                        <TableRow>
                            <TableHead @click="sortBy('date')" class="cursor-pointer select-none">
                                <div class="flex items-center gap-1">
                                    Date
                                    <ArrowUpDown class="w-3 h-3" :class="{ 'rotate-180': filters.sortBy === 'date' && filters.sortDir === 'desc' }" />
                                </div>
                            </TableHead>
                            <TableHead>Employee</TableHead>
                            <TableHead>Shift</TableHead>
                            <TableHead>Clock In</TableHead>
                            <TableHead>Clock Out</TableHead>
                            <TableHead>Duration</TableHead>
                            <TableHead>Location</TableHead>
                            <TableHead @click="sortBy('status')" class="cursor-pointer select-none">
                                <div class="flex items-center gap-1">
                                    Status
                                    <ArrowUpDown class="w-3 h-3" :class="{ 'rotate-180': filters.sortBy === 'status' && filters.sortDir === 'desc' }" />
                                </div>
                            </TableHead>
                            <TableHead>Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="att in attendances.data" :key="att.id" class="hover:bg-slate-50">
                            <TableCell>
                                <div class="flex flex-col items-center w-12">
                                    <span class="text-lg font-bold text-slate-900">{{ formatDate(att.date, 'DD') }}</span>
                                    <span class="text-xs text-slate-500 uppercase">{{ formatDate(att.date, 'MMM') }}</span>
                                </div>
                            </TableCell>
                            <TableCell>
                                <div class="flex items-center gap-3" v-if="att.user">
                                    <Avatar :class="getAvatarClass(att.user.name)">
                                        {{ getInitials(att.user.name) }}
                                    </Avatar>
                                    <div class="flex flex-col">
                                        <span class="text-sm font-semibold text-slate-900">{{ att.user.name }}</span>
                                        <span class="text-xs text-slate-500">{{ att.user.employee_id }}</span>
                                    </div>
                                </div>
                                <span v-else class="text-slate-400">-</span>
                            </TableCell>
                            <TableCell>
                                <span v-if="att.shift" class="inline-flex px-2 py-1 rounded-md bg-slate-100 text-xs font-medium text-slate-600">
                                    {{ att.shift.name }}
                                </span>
                                <span v-else class="text-slate-400">-</span>
                            </TableCell>
                            <TableCell>
                                <span v-if="att.clock_in_time" class="font-mono text-sm text-slate-900">{{ att.clock_in_time }}</span>
                                <span v-else class="text-slate-400">-</span>
                            </TableCell>
                            <TableCell>
                                <span v-if="att.clock_out_time" class="font-mono text-sm text-slate-900">{{ att.clock_out_time }}</span>
                                <span v-else class="text-slate-400">-</span>
                            </TableCell>
                            <TableCell>
                                <span v-if="getDuration(att)" class="inline-flex px-2 py-1 rounded-md bg-green-50 text-xs font-medium text-green-700">
                                    {{ getDuration(att) }}
                                </span>
                                <span v-else class="text-slate-400">-</span>
                            </TableCell>
                            <TableCell>
                                <span v-if="att.location" class="text-sm text-slate-600">{{ att.location.name }}</span>
                                <span v-else class="text-slate-400">-</span>
                            </TableCell>
                            <TableCell>
                                <Badge :variant="getStatusVariant(att.status)">
                                    {{ formatStatus(att.status) }}
                                </Badge>
                            </TableCell>
                            <TableCell>
                                <div class="flex gap-1">
                                    <Button variant="ghost" size="icon" class="h-8 w-8" @click="viewDetail(att)" title="View Details">
                                        <Eye class="w-4 h-4 text-blue-600" />
                                    </Button>
                                    <Button variant="ghost" size="icon" class="h-8 w-8" @click="openEditModal(att)" title="Edit">
                                        <Pencil class="w-4 h-4 text-amber-600" />
                                    </Button>
                                    <Button variant="ghost" size="icon" class="h-8 w-8" @click="confirmDelete(att)" title="Delete">
                                        <Trash2 class="w-4 h-4 text-red-600" />
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>

                <!-- Empty State -->
                <div v-if="attendances.data.length === 0" class="flex flex-col items-center justify-center py-16">
                    <Calendar class="w-16 h-16 text-slate-300 mb-4" />
                    <h3 class="text-lg font-semibold text-slate-600 mb-2">No attendance records found</h3>
                    <p class="text-sm text-slate-400">Try adjusting your filters or date range</p>
                </div>

                <!-- Pagination -->
                <div v-if="attendances.last_page > 1" class="flex items-center justify-between px-4 py-3 border-t border-gray-200">
                    <p class="text-sm text-slate-500">
                        Showing {{ attendances.from }} to {{ attendances.to }} of {{ attendances.total }} entries
                    </p>
                    <div class="flex gap-1">
                        <Button
                            variant="outline"
                            size="icon"
                            class="h-8 w-8"
                            :disabled="attendances.current_page === 1"
                            @click="changePage(attendances.current_page - 1)"
                        >
                            <ChevronLeft class="w-4 h-4" />
                        </Button>
                        <Button
                            v-for="page in visiblePages"
                            :key="page"
                            variant="outline"
                            size="icon"
                            :class="page === attendances.current_page ? 'h-8 w-8 bg-slate-900 text-white border-slate-900' : 'h-8 w-8'"
                            @click="changePage(page)"
                            :disabled="page === '...'"
                        >
                            {{ page }}
                        </Button>
                        <Button
                            variant="outline"
                            size="icon"
                            class="h-8 w-8"
                            :disabled="attendances.current_page === attendances.last_page"
                            @click="changePage(attendances.current_page + 1)"
                        >
                            <ChevronRight class="w-4 h-4" />
                        </Button>
                    </div>
                </div>
            </Card>
        </div>

        <!-- Detail Modal -->
        <Dialog v-model:open="showDetailModal">
            <DialogContent class="max-w-lg">
                <DialogHeader>
                    <DialogTitle>Attendance Details</DialogTitle>
                </DialogHeader>
                <div class="space-y-4" v-if="selectedAttendance">
                    <!-- Employee Info -->
                    <div class="flex items-center gap-4 pb-4 border-b">
                        <Avatar :class="getAvatarClass(selectedAttendance.user?.name)" class="w-16 h-16 text-xl">
                            {{ getInitials(selectedAttendance.user?.name) }}
                        </Avatar>
                        <div>
                            <h3 class="text-lg font-semibold text-slate-900">{{ selectedAttendance.user?.name }}</h3>
                            <p class="text-sm text-slate-500">{{ selectedAttendance.user?.employee_id }}</p>
                            <Badge :variant="getStatusVariant(selectedAttendance.status)" class="mt-1">
                                {{ formatStatus(selectedAttendance.status) }}
                            </Badge>
                        </div>
                    </div>

                    <!-- Stats -->
                    <div class="grid grid-cols-3 gap-3">
                        <div class="flex flex-col">
                            <span class="text-xs text-slate-500 uppercase">Date</span>
                            <span class="text-sm font-semibold text-slate-900">{{ formatFullDate(selectedAttendance.date) }}</span>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-xs text-slate-500 uppercase">Shift</span>
                            <span class="text-sm font-semibold text-slate-900">{{ selectedAttendance.shift?.name || 'Not assigned' }}</span>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-xs text-slate-500 uppercase">Location</span>
                            <span class="text-sm font-semibold text-slate-900">{{ selectedAttendance.location?.name || 'Not assigned' }}</span>
                        </div>
                    </div>

                    <!-- Time Details -->
                    <div class="grid grid-cols-3 gap-3">
                        <div class="bg-slate-50 rounded-lg p-3 text-center">
                            <div class="flex items-center justify-center gap-1 text-xs text-slate-500 uppercase mb-1">
                                <Clock class="w-3 h-3" /> Clock In
                            </div>
                            <p class="text-lg font-bold text-slate-900">{{ selectedAttendance.clock_in_time || '-' }}</p>
                            <p v-if="selectedAttendance.clock_in_latitude" class="text-[10px] text-slate-400 font-mono">
                                {{ selectedAttendance.clock_in_latitude }}, {{ selectedAttendance.clock_in_longitude }}
                            </p>
                        </div>
                        <div class="bg-slate-50 rounded-lg p-3 text-center">
                            <div class="flex items-center justify-center gap-1 text-xs text-slate-500 uppercase mb-1">
                                <Clock class="w-3 h-3" /> Clock Out
                            </div>
                            <p class="text-lg font-bold text-slate-900">{{ selectedAttendance.clock_out_time || '-' }}</p>
                            <p v-if="selectedAttendance.clock_out_latitude" class="text-[10px] text-slate-400 font-mono">
                                {{ selectedAttendance.clock_out_latitude }}, {{ selectedAttendance.clock_out_longitude }}
                            </p>
                        </div>
                        <div class="bg-green-50 rounded-lg p-3 text-center">
                            <div class="flex items-center justify-center gap-1 text-xs text-slate-500 uppercase mb-1">
                                <Timer class="w-3 h-3" /> Duration
                            </div>
                            <p class="text-lg font-bold text-slate-900">{{ getDuration(selectedAttendance) || '-' }}</p>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div v-if="selectedAttendance.notes" class="pt-3 border-t">
                        <span class="text-xs text-slate-500 uppercase block mb-1">Notes</span>
                        <p class="text-sm text-slate-600">{{ selectedAttendance.notes }}</p>
                    </div>
                </div>
            </DialogContent>
        </Dialog>

        <!-- Edit Modal -->
        <Dialog v-model:open="showEditModal">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Edit Attendance</DialogTitle>
                </DialogHeader>
                <form @submit.prevent="submitEdit" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex flex-col gap-2">
                            <label class="text-sm font-medium text-slate-700">Clock In Time</label>
                            <Input type="time" v-model="editForm.clock_in_time" />
                        </div>

                        <div class="flex flex-col gap-2">
                            <label class="text-sm font-medium text-slate-700">Clock Out Time</label>
                            <Input type="time" v-model="editForm.clock_out_time" />
                        </div>

                        <div class="col-span-2 flex flex-col gap-2">
                            <label class="text-sm font-medium text-slate-700">Status</label>
                            <select v-model="editForm.status" class="h-10 px-3 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="hadir">Present (On Time)</option>
                                <option value="terlambat">Late</option>
                                <option value="alpha">Absent</option>
                                <option value="izin">Permission</option>
                                <option value="sakit">Sick</option>
                                <option value="cuti">Leave</option>
                                <option value="onsite">On Site</option>
                            </select>
                        </div>

                        <div class="col-span-2 flex flex-col gap-2">
                            <label class="text-sm font-medium text-slate-700">Notes</label>
                            <textarea v-model="editForm.notes" class="flex w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" rows="3" placeholder="Add notes..."></textarea>
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 pt-4">
                        <Button type="button" variant="outline" @click="showEditModal = false">Cancel</Button>
                        <Button type="submit" :loading="submitting">
                            {{ submitting ? 'Saving...' : 'Save Changes' }}
                        </Button>
                    </div>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Delete Confirmation Modal -->
        <Dialog v-model:open="showDeleteModal">
            <DialogContent class="max-w-sm">
                <DialogHeader>
                    <DialogTitle>Confirm Delete</DialogTitle>
                </DialogHeader>
                <p class="text-sm text-slate-600 text-center py-2">
                    Are you sure you want to delete this attendance record?
                    <br />This action cannot be undone.
                </p>
                <div class="flex justify-end gap-2 pt-2">
                    <Button variant="outline" @click="showDeleteModal = false">Cancel</Button>
                    <Button variant="destructive" :loading="deleting" @click="deleteAttendance">
                        {{ deleting ? 'Deleting...' : 'Delete' }}
                    </Button>
                </div>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useToast } from '@/composables/useToast';
import type { Attendance, User, Shift, Location, Stats, Filters, PaginatedData, AttendanceStatusValue } from '@/types';
import { ATTENDANCE_STATUS_LABELS, ATTENDANCE_STATUS_VARIANTS } from '@/types';

// UI Components
import {
    Button,
    Card,
    CardContent,
    Input,
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
} from '@/components/ui';

// Lucide Icons
import {
    Download,
    CheckCircle,
    Clock,
    XCircle,
    FileText,
    Timer,
    Users,
    X,
    ArrowUpDown,
    Eye,
    Pencil,
    Trash2,
    Calendar,
    ChevronLeft,
    ChevronRight,
} from 'lucide-vue-next';

const { success, error } = useToast();

interface Props {
    attendances: PaginatedData<Attendance>;
    employees: User[];
    shifts: Shift[];
    locations: Location[];
    stats: Stats;
    filters: Filters;
}

const props = defineProps<Props>();

// Filters
interface FilterState {
    date: string;
    date_from: string;
    date_to: string;
    user_id: string | number;
    status: string;
    shift_id: string | number;
    location_id: string | number;
    sortBy: string;
    sortDir: string;
}

const filters = ref<FilterState>({
    date: props.filters?.date || '',
    date_from: props.filters?.date_from || '',
    date_to: props.filters?.date_to || '',
    user_id: props.filters?.user_id || '',
    status: props.filters?.status || '',
    shift_id: props.filters?.shift_id || '',
    location_id: props.filters?.location_id || '',
    sortBy: props.filters?.sortBy || 'date',
    sortDir: props.filters?.sortDir || 'desc',
});

const hasFilters = computed(() => {
    return filters.value.date || filters.value.date_from || filters.value.date_to ||
           filters.value.user_id || filters.value.status || filters.value.shift_id ||
           filters.value.location_id;
});

// Modal states
const showDetailModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const selectedAttendance = ref(null);
const editForm = ref({
    clock_in_time: '',
    clock_out_time: '',
    status: '',
    notes: '',
});
const submitting = ref(false);
const deleting = ref(false);
const attendanceToDelete = ref(null);

// Computed
const visiblePages = computed(() => {
    const current = props.attendances.current_page;
    const last = props.attendances.last_page;
    const delta = 2;
    const range = [];
    for (let i = Math.max(2, current - delta); i <= Math.min(last - 1, current + delta); i++) {
        range.push(i);
    }
    if (current - delta > 2) range.unshift('...');
    if (current + delta < last - 1) range.push('...');
    range.unshift(1);
    if (last > 1) range.push(last);
    return range;
});

// Methods
function getInitials(name: string | undefined): string {
    if (!name) return '';
    return name.split(' ').map((n: string) => n[0]).join('').toUpperCase().slice(0, 2);
}

function getAvatarClass(name: string | undefined): string {
    const colors = [
        'bg-gradient-to-br from-blue-500 to-blue-600',
        'bg-gradient-to-br from-purple-500 to-purple-600',
        'bg-gradient-to-br from-emerald-500 to-emerald-600',
        'bg-gradient-to-br from-amber-500 to-amber-600',
        'bg-gradient-to-br from-pink-500 to-pink-600',
        'bg-gradient-to-br from-teal-500 to-teal-600',
    ];
    const index = name?.charCodeAt(0) % colors.length || 0;
    return colors[index];
}

function formatDate(date: string | undefined, format: string): string {
    if (!date) return '';
    const d = new Date(date);
    if (format === 'DD') return d.getDate().toString();
    if (format === 'MMM') return d.toLocaleDateString('en-US', { month: 'short' });
    return d.toLocaleDateString('en-US', { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' });
}

function formatFullDate(date: string | undefined): string {
    if (!date) return '';
    return new Date(date).toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

function formatStatus(status: string): string {
    return ATTENDANCE_STATUS_LABELS[status as AttendanceStatusValue] || status;
}

function getStatusVariant(status: string): 'default' | 'secondary' | 'destructive' | 'success' | 'warning' | 'outline' {
    return ATTENDANCE_STATUS_VARIANTS[status as AttendanceStatusValue] || 'default';
}

function getDuration(att: Attendance | null): string | null {
    if (!att || !att.clock_in_time || !att.clock_out_time) return null;

    const [inH, inM] = att.clock_in_time.split(':').map(Number);
    const [outH, outM] = att.clock_out_time.split(':').map(Number);

    const inMinutes = inH * 60 + inM;
    let outMinutes = outH * 60 + outM;

    // Handle overnight shift
    if (outMinutes < inMinutes) {
        outMinutes += 24 * 60;
    }

    const diff = outMinutes - inMinutes;
    const hours = Math.floor(diff / 60);
    const minutes = diff % 60;

    return `${hours}h ${minutes}m`;
}

function applyFilters() {
    router.get(window.route('attendance.index'), {
        date: filters.value.date,
        date_from: filters.value.date_from,
        date_to: filters.value.date_to,
        user_id: filters.value.user_id,
        status: filters.value.status,
        shift_id: filters.value.shift_id,
        location_id: filters.value.location_id,
        sortBy: filters.value.sortBy,
        sortDir: filters.value.sortDir,
    }, { replace: true });
}

function clearFilters() {
    filters.value = {
        date: '',
        date_from: '',
        date_to: '',
        user_id: '',
        status: '',
        shift_id: '',
        location_id: '',
        sortBy: 'date',
        sortDir: 'desc',
    };
    applyFilters();
}

function sortBy(column: string) {
    if (filters.value.sortBy === column) {
        filters.value.sortDir = filters.value.sortDir === 'asc' ? 'desc' : 'asc';
    } else {
        filters.value.sortBy = column;
        filters.value.sortDir = 'desc';
    }
    applyFilters();
}

function changePage(page: number | string) {
    if (page === '...') return;
    router.get(window.route('attendance.index'), {
        ...props.filters,
        page: page,
    }, { replace: true });
}

function viewDetail(att: Attendance) {
    selectedAttendance.value = att;
    showDetailModal.value = true;
}

function openEditModal(att: Attendance) {
    selectedAttendance.value = att;
    editForm.value = {
        clock_in_time: att.clock_in_time || '',
        clock_out_time: att.clock_out_time || '',
        status: att.status,
        notes: att.notes || '',
    };
    showEditModal.value = true;
}

function submitEdit() {
    submitting.value = true;
    const id = (selectedAttendance.value as Attendance).id;
    router.put(window.route('attendance.update', { id }), editForm.value, {
        onSuccess: () => {
            showEditModal.value = false;
            submitting.value = false;
            success('Attendance updated successfully');
        },
        onError: () => {
            submitting.value = false;
            error('Failed to update attendance');
        },
    });
}

function confirmDelete(att: Attendance) {
    attendanceToDelete.value = att;
    showDeleteModal.value = true;
}

function deleteAttendance() {
    deleting.value = true;
    router.delete(window.route('attendance.destroy', attendanceToDelete.value?.id), {
        onSuccess: () => {
            showDeleteModal.value = false;
            deleting.value = false;
            success('Attendance deleted successfully');
        },
    });
}

function exportData() {
    alert('Export feature coming soon!');
}

// Reset filters when component mounts
watch(() => props.filters, (newFilters) => {
    if (newFilters) {
        filters.value = {
            date: newFilters.date || '',
            date_from: newFilters.date_from || '',
            date_to: newFilters.date_to || '',
            user_id: newFilters.user_id || '',
            status: newFilters.status || '',
            shift_id: newFilters.shift_id || '',
            location_id: newFilters.location_id || '',
            sortBy: newFilters.sortBy || 'date',
            sortDir: newFilters.sortDir || 'desc',
        };
    }
}, { immediate: true });
</script>
