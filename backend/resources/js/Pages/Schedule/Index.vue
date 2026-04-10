<template>
    <AppLayout>
        <div class="space-y-6 p-6">
            <!-- Page Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Schedules</h1>
                    <p class="text-sm text-slate-500">Manage employee work schedules and shifts</p>
                </div>
                <div class="flex gap-3">
                    <Button variant="outline" @click="openBulkModal">
                        <LayoutGrid class="w-4 h-4 mr-2" />
                        Bulk Create
                    </Button>
                    <Button @click="openCreateModal">
                        <Plus class="w-4 h-4 mr-2" />
                        Add Schedule
                    </Button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
                <Card class="relative overflow-hidden">
                    <CardContent class="p-5">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center">
                                <Calendar class="w-5 h-5 text-indigo-600" />
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-slate-900">{{ stats.total }}</p>
                                <p class="text-xs text-slate-500">Total Schedules</p>
                            </div>
                        </div>
                        <div class="absolute -right-3 -bottom-3 w-16 h-16 rounded-full bg-indigo-600 opacity-10"></div>
                    </CardContent>
                </Card>

                <Card class="relative overflow-hidden">
                    <CardContent class="p-5">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center">
                                <CheckCircle class="w-5 h-5 text-emerald-600" />
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-slate-900">{{ stats.active }}</p>
                                <p class="text-xs text-slate-500">Active</p>
                            </div>
                        </div>
                        <div class="absolute -right-3 -bottom-3 w-16 h-16 rounded-full bg-emerald-600 opacity-10"></div>
                    </CardContent>
                </Card>

                <Card class="relative overflow-hidden">
                    <CardContent class="p-5">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center">
                                <XCircle class="w-5 h-5 text-red-600" />
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-slate-900">{{ stats.inactive }}</p>
                                <p class="text-xs text-slate-500">Inactive</p>
                            </div>
                        </div>
                        <div class="absolute -right-3 -bottom-3 w-16 h-16 rounded-full bg-red-600 opacity-10"></div>
                    </CardContent>
                </Card>

                <Card class="relative overflow-hidden">
                    <CardContent class="p-5">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-orange-100 flex items-center justify-center">
                                <Clock class="w-5 h-5 text-orange-600" />
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-slate-900">{{ stats.this_week }}</p>
                                <p class="text-xs text-slate-500">This Week</p>
                            </div>
                        </div>
                        <div class="absolute -right-3 -bottom-3 w-16 h-16 rounded-full bg-orange-600 opacity-10"></div>
                    </CardContent>
                </Card>

                <Card class="relative overflow-hidden">
                    <CardContent class="p-5">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center">
                                <ChevronRight class="w-5 h-5 text-green-600" />
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-slate-900">{{ stats.next_week }}</p>
                                <p class="text-xs text-slate-500">Next Week</p>
                            </div>
                        </div>
                        <div class="absolute -right-3 -bottom-3 w-16 h-16 rounded-full bg-green-600 opacity-10"></div>
                    </CardContent>
                </Card>
            </div>

            <!-- Filters Section -->
            <Card>
                <CardContent class="p-4">
                    <div class="flex flex-col lg:flex-row gap-4">
                        <div class="flex flex-wrap gap-3 flex-1">
                            <div class="flex flex-col gap-1">
                                <Label class="text-xs font-medium text-slate-500">Date</Label>
                                <Input
                                    type="date"
                                    v-model="filters.date"
                                    class="w-40"
                                    @change="applyFilters"
                                />
                            </div>
                            <div class="flex flex-col gap-1">
                                <Label class="text-xs font-medium text-slate-500">From</Label>
                                <Input
                                    type="date"
                                    v-model="filters.date_from"
                                    class="w-40"
                                    @change="applyFilters"
                                />
                            </div>
                            <div class="flex flex-col gap-1">
                                <Label class="text-xs font-medium text-slate-500">To</Label>
                                <Input
                                    type="date"
                                    v-model="filters.date_to"
                                    class="w-40"
                                    @change="applyFilters"
                                />
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-3">
                            <select v-model="filters.user_id" class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50" @change="applyFilters">
                                <option value="">All Employees</option>
                                <option v-for="emp in employees" :key="emp.id" :value="emp.id">
                                    {{ emp.name }} ({{ emp.employee_id }})
                                </option>
                            </select>
                            <select v-model="filters.shift_id" class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50" @change="applyFilters">
                                <option value="">All Shifts</option>
                                <option v-for="shift in shifts" :key="shift.id" :value="shift.id">
                                    {{ shift.name }}
                                </option>
                            </select>
                            <select v-model="filters.location_id" class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50" @change="applyFilters">
                                <option value="">All Locations</option>
                                <option v-for="loc in locations" :key="loc.id" :value="loc.id">
                                    {{ loc.name }}
                                </option>
                            </select>
                            <select v-model="filters.is_active" class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50" @change="applyFilters">
                                <option value="">All Status</option>
                                <option value="true">Active</option>
                                <option value="false">Inactive</option>
                            </select>
                            <Button variant="ghost" size="sm" @click="clearFilters" v-if="hasFilters" class="text-red-600 hover:text-red-700 hover:bg-red-50">
                                <X class="w-4 h-4 mr-1" />
                                Clear
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Schedule Table -->
            <Card>
                <CardContent class="p-0">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead class="cursor-pointer" @click="sortBy('date')">
                                    <div class="flex items-center gap-1">
                                        Date
                                        <ArrowUpDown class="w-3 h-3" :class="{ 'rotate-180': filters.sortBy === 'date' && filters.sortDir === 'desc' }" />
                                    </div>
                                </TableHead>
                                <TableHead>Employee</TableHead>
                                <TableHead>Shift</TableHead>
                                <TableHead>Time</TableHead>
                                <TableHead>Location</TableHead>
                                <TableHead class="cursor-pointer" @click="sortBy('is_active')">
                                    <div class="flex items-center gap-1">
                                        Status
                                        <ArrowUpDown class="w-3 h-3" :class="{ 'rotate-180': filters.sortBy === 'is_active' && filters.sortDir === 'desc' }" />
                                    </div>
                                </TableHead>
                                <TableHead>Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="schedule in schedules.data" :key="schedule.id">
                                <TableCell>
                                    <div class="flex flex-col items-center min-w-12">
                                        <span class="text-lg font-bold text-slate-900">{{ formatDate(schedule.date, 'DD') }}</span>
                                        <span class="text-xs font-semibold text-slate-500 uppercase">{{ formatDate(schedule.date, 'MMM') }}</span>
                                        <span class="text-[10px] text-slate-400">{{ formatDate(schedule.date, 'YYYY') }}</span>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div class="flex items-center gap-3" v-if="schedule.user">
                                        <Avatar :class="getAvatarClass(schedule.user.name)">
                                            <AvatarFallback>{{ getInitials(schedule.user.name) }}</AvatarFallback>
                                        </Avatar>
                                        <div class="flex flex-col">
                                            <span class="text-sm font-medium text-slate-900">{{ schedule.user.name }}</span>
                                            <span class="text-xs text-slate-500">{{ schedule.user.employee_id }}</span>
                                        </div>
                                    </div>
                                    <span v-else class="text-slate-400 text-sm">-</span>
                                </TableCell>
                                <TableCell>
                                    <Badge variant="secondary" v-if="schedule.shift">{{ schedule.shift.name }}</Badge>
                                    <span v-else class="text-slate-400 text-sm">-</span>
                                </TableCell>
                                <TableCell>
                                    <div class="flex items-center gap-1" v-if="schedule.shift">
                                        <span class="text-sm font-medium text-slate-700">{{ schedule.shift.start_time }}</span>
                                        <span class="text-slate-400">-</span>
                                        <span class="text-sm font-medium text-slate-700">{{ schedule.shift.end_time }}</span>
                                    </div>
                                    <span v-else class="text-slate-400 text-sm">-</span>
                                </TableCell>
                                <TableCell>
                                    <span class="text-sm text-slate-700" v-if="schedule.location">{{ schedule.location.name }}</span>
                                    <span v-else class="text-slate-400 text-sm">-</span>
                                </TableCell>
                                <TableCell>
                                    <button
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium transition-colors"
                                        :class="schedule.is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700'"
                                        @click="toggleStatus(schedule)"
                                        :title="schedule.is_active ? 'Active' : 'Inactive'"
                                    >
                                        <span class="w-2 h-2 rounded-full" :class="schedule.is_active ? 'bg-emerald-500' : 'bg-red-500'"></span>
                                        {{ schedule.is_active ? 'Active' : 'Inactive' }}
                                    </button>
                                </TableCell>
                                <TableCell>
                                    <div class="flex items-center gap-2">
                                        <Button variant="ghost" size="icon" class="h-8 w-8" @click="openEditModal(schedule)" title="Edit">
                                            <Pencil class="w-4 h-4 text-indigo-600" />
                                        </Button>
                                        <Button variant="ghost" size="icon" class="h-8 w-8" @click="confirmDelete(schedule)" title="Delete">
                                            <Trash2 class="w-4 h-4 text-red-600" />
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>

                    <!-- Empty State -->
                    <div class="flex flex-col items-center justify-center py-16" v-if="schedules.data.length === 0">
                        <Calendar class="w-16 h-16 text-slate-300 mb-4" />
                        <h3 class="text-lg font-semibold text-slate-700 mb-2">No schedules found</h3>
                        <p class="text-sm text-slate-500">Try adjusting your filters or create a new schedule</p>
                    </div>

                    <!-- Pagination -->
                    <div class="flex items-center justify-between px-4 py-3 border-t" v-if="schedules.last_page > 1">
                        <div class="text-sm text-slate-500">
                            Showing {{ schedules.from }} to {{ schedules.to }} of {{ schedules.total }} entries
                        </div>
                        <div class="flex items-center gap-1">
                            <Button
                                variant="outline"
                                size="icon"
                                class="h-8 w-8"
                                :disabled="schedules.current_page === 1"
                                @click="changePage(schedules.current_page - 1)"
                            >
                                <ChevronLeft class="w-4 h-4" />
                            </Button>
                            <Button
                                v-for="page in visiblePages"
                                :key="page"
                                variant="outline"
                                size="icon"
                                class="h-8 w-8"
                                :class="page === schedules.current_page ? 'bg-indigo-600 text-white border-indigo-600 hover:bg-indigo-700 hover:border-indigo-700' : ''"
                                @click="changePage(page)"
                                :disabled="page === '...'"
                            >
                                {{ page }}
                            </Button>
                            <Button
                                variant="outline"
                                size="icon"
                                class="h-8 w-8"
                                :disabled="schedules.current_page === schedules.last_page"
                                @click="changePage(schedules.current_page + 1)"
                            >
                                <ChevronRight class="w-4 h-4" />
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Create/Edit Modal -->
            <Dialog v-model:open="showModal">
                <DialogContent class="sm:max-w-130">
                    <DialogHeader>
                        <DialogTitle>{{ isEditing ? 'Edit Schedule' : 'Add Schedule' }}</DialogTitle>
                        <DialogDescription>Fill in the schedule details below.</DialogDescription>
                    </DialogHeader>
                    <form @submit.prevent="submitForm">
                        <div class="grid gap-4 py-4">
                            <div class="grid gap-2">
                                <Label for="employee">Employee</Label>
                                <select v-model="form.user_id" id="employee" class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50" required>
                                    <option value="">Select Employee</option>
                                    <option v-for="emp in employees" :key="emp.id" :value="emp.id">
                                        {{ emp.name }} ({{ emp.employee_id }})
                                    </option>
                                </select>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="grid gap-2">
                                    <Label for="date">Date</Label>
                                    <Input id="date" type="date" v-model="form.date" required />
                                </div>
                                <div class="grid gap-2">
                                    <Label for="shift">Shift</Label>
                                    <select v-model="form.shift_id" id="shift" class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50" required>
                                        <option value="">Select Shift</option>
                                        <option v-for="shift in shifts" :key="shift.id" :value="shift.id">
                                            {{ shift.name }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="grid gap-2">
                                <Label for="location">Location</Label>
                                <select v-model="form.location_id" id="location" class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50" required>
                                    <option value="">Select Location</option>
                                    <option v-for="loc in locations" :key="loc.id" :value="loc.id">
                                        {{ loc.name }}
                                    </option>
                                </select>
                            </div>

                            <div class="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    id="is_active"
                                    v-model="form.is_active"
                                    class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600"
                                />
                                <Label for="is_active" class="text-sm font-normal">Active</Label>
                            </div>
                        </div>
                        <div class="flex justify-end gap-3">
                            <Button type="button" variant="outline" @click="closeModal">Cancel</Button>
                            <Button type="submit" :disabled="submitting">
                                {{ submitting ? 'Saving...' : (isEditing ? 'Update' : 'Create') }}
                            </Button>
                        </div>
                    </form>
                </DialogContent>
            </Dialog>

            <!-- Bulk Create Modal -->
            <Dialog v-model:open="showBulkModal">
                <DialogContent class="sm:max-w-130">
                    <DialogHeader>
                        <DialogTitle>Bulk Create Schedules</DialogTitle>
                        <DialogDescription>Create schedules for multiple employees at once.</DialogDescription>
                    </DialogHeader>
                    <form @submit.prevent="submitBulk">
                        <div class="grid gap-4 py-4">
                            <div class="grid gap-2">
                                <Label>Employees</Label>
                                <div class="border rounded-md max-h-48 overflow-y-auto p-2 space-y-2">
                                    <div v-for="emp in employees" :key="emp.id" class="flex items-center gap-2">
                                        <input
                                            type="checkbox"
                                            :id="'emp-' + emp.id"
                                            :value="emp.id"
                                            v-model="bulkForm.user_ids"
                                            class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600"
                                        />
                                        <Label :for="'emp-' + emp.id" class="text-sm font-normal cursor-pointer">
                                            {{ emp.name }} ({{ emp.employee_id }})
                                        </Label>
                                    </div>
                                </div>
                                <p class="text-xs text-slate-500" v-if="bulkForm.user_ids.length > 0">
                                    {{ bulkForm.user_ids.length }} employee(s) selected
                                </p>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="grid gap-2">
                                    <Label for="start_date">Start Date</Label>
                                    <Input id="start_date" type="date" v-model="bulkForm.start_date" required />
                                </div>
                                <div class="grid gap-2">
                                    <Label for="end_date">End Date</Label>
                                    <Input id="end_date" type="date" v-model="bulkForm.end_date" required />
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="grid gap-2">
                                    <Label for="bulk_shift">Shift</Label>
                                    <select v-model="bulkForm.shift_id" id="bulk_shift" class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50" required>
                                        <option value="">Select Shift</option>
                                        <option v-for="shift in shifts" :key="shift.id" :value="shift.id">
                                            {{ shift.name }}
                                        </option>
                                    </select>
                                </div>
                                <div class="grid gap-2">
                                    <Label for="bulk_location">Location</Label>
                                    <select v-model="bulkForm.location_id" id="bulk_location" class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50" required>
                                        <option value="">Select Location</option>
                                        <option v-for="loc in locations" :key="loc.id" :value="loc.id">
                                            {{ loc.name }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="rounded-md bg-amber-50 p-3 text-sm text-amber-800">
                                Note: Schedules will only be created on weekdays (Monday - Friday). Existing schedules will be skipped.
                            </div>
                        </div>
                        <div class="flex justify-end gap-3">
                            <Button type="button" variant="outline" @click="showBulkModal = false">Cancel</Button>
                            <Button type="submit" :disabled="submittingBulk || bulkForm.user_ids.length === 0">
                                {{ submittingBulk ? 'Creating...' : 'Create Schedules' }}
                            </Button>
                        </div>
                    </form>
                </DialogContent>
            </Dialog>

            <!-- Delete Confirmation Modal -->
            <Dialog v-model:open="showDeleteModal">
                <DialogContent class="sm:max-w-100">
                    <DialogHeader>
                        <DialogTitle>Confirm Delete</DialogTitle>
                        <DialogDescription>Are you sure you want to delete this schedule? This action cannot be undone.</DialogDescription>
                    </DialogHeader>
                    <div class="flex justify-end gap-3 py-4">
                        <Button variant="outline" @click="showDeleteModal = false">Cancel</Button>
                        <Button variant="destructive" @click="deleteSchedule" :disabled="deleting">
                            {{ deleting ? 'Deleting...' : 'Delete' }}
                        </Button>
                    </div>
                </DialogContent>
            </Dialog>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useToast } from '@/composables/useToast';

// Shadcn-vue components
import {
    Button,
    Input,
    Label,
    Card,
    CardContent,
    Badge,
    Avatar,
    AvatarFallback,
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

// Icons
import {
    Calendar,
    CheckCircle,
    XCircle,
    Clock,
    ChevronRight,
    Plus,
    LayoutGrid,
    X,
    Pencil,
    Trash2,
    ChevronLeft,
    ArrowUpDown,
} from 'lucide-vue-next';

const { success, error } = useToast();

const props = defineProps<{
    schedules: {
        data: Array<{
            id: number;
            date: string;
            user_id: number;
            shift_id: number;
            location_id: number;
            is_active: boolean;
            user?: { id: number; name: string; employee_id: string };
            shift?: { id: number; name: string; start_time: string; end_time: string };
            location?: { id: number; name: string };
        }>;
        current_page: number;
        last_page: number;
        from: number;
        to: number;
        total: number;
    };
    employees: Array<{ id: number; name: string; employee_id: string }>;
    shifts: Array<{ id: number; name: string; start_time: string; end_time: string }>;
    locations: Array<{ id: number; name: string }>;
    stats: { total: number; active: number; inactive: number; this_week: number; next_week: number };
    filters?: Record<string, any>;
}>();

// Filters
const filters = ref({
    date: props.filters?.date || '',
    date_from: props.filters?.date_from || '',
    date_to: props.filters?.date_to || '',
    user_id: props.filters?.user_id || '',
    shift_id: props.filters?.shift_id || '',
    location_id: props.filters?.location_id || '',
    is_active: props.filters?.is_active || '',
    sortBy: props.filters?.sortBy || 'date',
    sortDir: props.filters?.sortDir || 'desc',
});

const hasFilters = computed(() => {
    return filters.value.date || filters.value.date_from || filters.value.date_to ||
           filters.value.user_id || filters.value.shift_id || filters.value.location_id ||
           filters.value.is_active !== '';
});

// Modal states
const showModal = ref(false);
const showBulkModal = ref(false);
const showDeleteModal = ref(false);
const isEditing = ref(false);
const selectedSchedule = ref(null);
const submitting = ref(false);
const submittingBulk = ref(false);
const deleting = ref(false);
const scheduleToDelete = ref(null);

const form = ref({
    user_id: '',
    shift_id: '',
    location_id: '',
    date: '',
    is_active: true,
});

const bulkForm = ref({
    user_ids: [],
    shift_id: '',
    location_id: '',
    start_date: '',
    end_date: '',
});

// Computed
const visiblePages = computed(() => {
    const current = props.schedules.current_page;
    const last = props.schedules.last_page;
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
function getInitials(name: string) {
    if (!name) return '';
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
}

function getAvatarClass(name: string) {
    const colors = ['bg-blue-500', 'bg-purple-500', 'bg-green-500', 'bg-orange-500', 'bg-pink-500', 'bg-teal-500'];
    const index = name?.charCodeAt(0) % colors.length || 0;
    return colors[index];
}

function formatDate(date: string, format: string) {
    if (!date) return '';
    const d = new Date(date);
    if (format === 'DD') return d.getDate();
    if (format === 'MMM') return d.toLocaleDateString('en-US', { month: 'short' });
    if (format === 'YYYY') return d.getFullYear();
    return d.toLocaleDateString('en-US', { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' });
}

function applyFilters() {
    router.get(window.route('schedules.index'), {
        date: filters.value.date,
        date_from: filters.value.date_from,
        date_to: filters.value.date_to,
        user_id: filters.value.user_id,
        shift_id: filters.value.shift_id,
        location_id: filters.value.location_id,
        is_active: filters.value.is_active,
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
        shift_id: '',
        location_id: '',
        is_active: '',
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
    router.get(window.route('schedules.index'), {
        ...props.filters,
        page: page,
    }, { replace: true });
}

function openCreateModal() {
    isEditing.value = false;
    form.value = {
        user_id: '',
        shift_id: '',
        location_id: '',
        date: new Date().toISOString().split('T')[0],
        is_active: true,
    };
    showModal.value = true;
}

function openEditModal(schedule: any) {
    isEditing.value = true;
    selectedSchedule.value = schedule;
    form.value = {
        user_id: schedule.user_id,
        shift_id: schedule.shift_id,
        location_id: schedule.location_id,
        date: schedule.date,
        is_active: schedule.is_active,
    };
    showModal.value = true;
}

function openBulkModal() {
    bulkForm.value = {
        user_ids: [],
        shift_id: '',
        location_id: '',
        start_date: '',
        end_date: '',
    };
    showBulkModal.value = true;
}

function closeModal() {
    showModal.value = false;
    selectedSchedule.value = null;
}

function submitForm() {
    submitting.value = true;

    if (isEditing.value) {
        router.put(window.route('schedules.update', selectedSchedule.value.id), form.value, {
            onSuccess: () => {
                success('Schedule updated successfully');
                closeModal();
            },
            onError: (err: any) => {
                error(err.message || 'Failed to update schedule');
            },
            onFinish: () => {
                submitting.value = false;
            },
        });
    } else {
        router.post(window.route('schedules.store'), form.value, {
            onSuccess: () => {
                success('Schedule created successfully');
                closeModal();
            },
            onError: (err: any) => {
                error(err.message || 'Failed to create schedule');
            },
            onFinish: () => {
                submitting.value = false;
            },
        });
    }
}

function submitBulk() {
    submittingBulk.value = true;

    router.post(window.route('schedules.bulk'), bulkForm.value, {
        onSuccess: () => {
            success('Schedules created successfully');
            showBulkModal.value = false;
        },
        onError: (err: any) => {
            error(err.message || 'Failed to create schedules');
        },
        onFinish: () => {
            submittingBulk.value = false;
        },
    });
}

function toggleStatus(schedule: any) {
    router.patch(window.route('schedules.toggle-status', schedule.id), {}, {
        onSuccess: () => {
            success('Schedule status updated');
        },
        onError: () => {
            error('Failed to update status');
        },
    });
}

function confirmDelete(schedule: any) {
    scheduleToDelete.value = schedule;
    showDeleteModal.value = true;
}

function deleteSchedule() {
    deleting.value = true;

    router.delete(window.route('schedules.destroy', scheduleToDelete.value.id), {
        onSuccess: () => {
            success('Schedule deleted successfully');
            showDeleteModal.value = false;
        },
        onError: () => {
            error('Failed to delete schedule');
        },
        onFinish: () => {
            deleting.value = false;
        },
    });
}
</script>
