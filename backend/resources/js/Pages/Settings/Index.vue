<template>
    <AppLayout>
        <div class="max-w-6xl mx-auto space-y-6">
            <!-- Page Header -->
            <div class="space-y-1">
                <h1 class="text-2xl font-bold text-gray-900">Settings</h1>
                <p class="text-sm text-gray-500">Manage your application settings and preferences</p>
            </div>

            <!-- Settings Layout -->
            <div class="grid grid-cols-1 md:grid-cols-[240px_1fr] gap-6">
                <!-- Sidebar Tabs -->
                <div class="flex flex-row md:flex-col gap-1 overflow-x-auto md:overflow-visible pb-2 md:pb-0">
                    <button
                        v-for="tab in tabs"
                        :key="tab.id"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition-all duration-200 text-left whitespace-nowrap"
                        :class="{ 'bg-blue-50 text-blue-600': activeTab === tab.id }"
                        @click="activeTab = tab.id"
                    >
                        <component :is="tab.icon" class="w-5 h-5 shrink-0" />
                        <span>{{ tab.label }}</span>
                    </button>
                </div>

                <!-- Content Area -->
                <div class="bg-gray-50 rounded-2xl p-6">
                    <!-- Company Settings -->
                    <div v-if="activeTab === 'company'" class="animate-in fade-in slide-in-from-bottom-2 duration-300">
                        <Card>
                            <CardHeader>
                                <CardTitle>Company Profile</CardTitle>
                                <CardDescription>Basic information about your company</CardDescription>
                            </CardHeader>
                            <CardContent>
                                <form @submit.prevent="saveCompanySettings" class="space-y-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="space-y-2">
                                            <Label for="company_name">Company Name</Label>
                                            <Input
                                                id="company_name"
                                                v-model="companyForm.company_name"
                                                type="text"
                                                placeholder="Enter company name"
                                            />
                                        </div>
                                        <div class="space-y-2">
                                            <Label for="company_email">Company Email</Label>
                                            <Input
                                                id="company_email"
                                                v-model="companyForm.company_email"
                                                type="email"
                                                placeholder="company@example.com"
                                            />
                                        </div>
                                        <div class="space-y-2">
                                            <Label for="company_phone">Phone Number</Label>
                                            <Input
                                                id="company_phone"
                                                v-model="companyForm.company_phone"
                                                type="text"
                                                placeholder="+1 234 567 8900"
                                            />
                                        </div>
                                        <div class="md:col-span-2 space-y-2">
                                            <Label for="company_address">Address</Label>
                                            <textarea
                                                id="company_address"
                                                v-model="companyForm.company_address"
                                                class="flex min-h-20 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:cursor-not-allowed disabled:opacity-50"
                                                placeholder="Enter company address"
                                                rows="3"
                                            ></textarea>
                                        </div>
                                    </div>

                                    <div class="flex justify-end pt-4 border-t border-gray-200">
                                        <Button type="submit" :disabled="companyProcessing">
                                            <Loader2 v-if="companyProcessing" class="w-4 h-4 mr-2 animate-spin" />
                                            {{ companyProcessing ? 'Saving...' : 'Save Changes' }}
                                        </Button>
                                    </div>
                                </form>
                            </CardContent>
                        </Card>
                    </div>

                    <!-- Work Hours Settings -->
                    <div v-if="activeTab === 'work-hours'" class="animate-in fade-in slide-in-from-bottom-2 duration-300">
                        <Card>
                            <CardHeader>
                                <CardTitle>Work Hours</CardTitle>
                                <CardDescription>Configure working hours and attendance rules</CardDescription>
                            </CardHeader>
                            <CardContent>
                                <form @submit.prevent="saveWorkHoursSettings" class="space-y-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="space-y-2">
                                            <Label for="work_start_time">Work Start Time</Label>
                                            <div class="relative">
                                                <Input
                                                    id="work_start_time"
                                                    v-model="workHoursForm.work_start_time"
                                                    type="time"
                                                    class="pl-10"
                                                />
                                                <Clock class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                                            </div>
                                        </div>
                                        <div class="space-y-2">
                                            <Label for="work_end_time">Work End Time</Label>
                                            <div class="relative">
                                                <Input
                                                    id="work_end_time"
                                                    v-model="workHoursForm.work_end_time"
                                                    type="time"
                                                    class="pl-10"
                                                />
                                                <Clock class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                                            </div>
                                        </div>
                                        <div class="space-y-2">
                                            <Label for="grace_period">Grace Period (minutes)</Label>
                                            <Input
                                                id="grace_period"
                                                v-model="workHoursForm.grace_period_minutes"
                                                type="number"
                                                min="0"
                                                max="60"
                                            />
                                            <p class="text-xs text-gray-500">Allow employees to clock in late without being marked as late</p>
                                        </div>
                                        <div class="space-y-2">
                                            <Label for="late_threshold">Late Threshold (minutes)</Label>
                                            <Input
                                                id="late_threshold"
                                                v-model="workHoursForm.late_threshold_minutes"
                                                type="number"
                                                min="0"
                                                max="120"
                                            />
                                            <p class="text-xs text-gray-500">Minutes after start time to mark as late</p>
                                        </div>
                                    </div>

                                    <div class="flex justify-end pt-4 border-t border-gray-200">
                                        <Button type="submit" :disabled="workHoursProcessing">
                                            <Loader2 v-if="workHoursProcessing" class="w-4 h-4 mr-2 animate-spin" />
                                            {{ workHoursProcessing ? 'Saving...' : 'Save Changes' }}
                                        </Button>
                                    </div>
                                </form>
                            </CardContent>
                        </Card>
                    </div>

                    <!-- Attendance Settings -->
                    <div v-if="activeTab === 'attendance'" class="animate-in fade-in slide-in-from-bottom-2 duration-300">
                        <Card>
                            <CardHeader>
                                <CardTitle>Attendance Settings</CardTitle>
                                <CardDescription>Configure attendance tracking and validation rules</CardDescription>
                            </CardHeader>
                            <CardContent>
                                <form @submit.prevent="saveAttendanceSettings" class="space-y-4">
                                    <div class="space-y-4">
                                        <div class="space-y-2 max-w-xs">
                                            <Label for="max_radius">GPS Radius (meters)</Label>
                                            <Input
                                                id="max_radius"
                                                v-model="attendanceForm.max_radius_meters"
                                                type="number"
                                                min="10"
                                                max="5000"
                                            />
                                            <p class="text-xs text-gray-500">Maximum distance from office to clock in</p>
                                        </div>

                                        <div class="space-y-3 pt-2">
                                            <div class="flex items-center justify-between p-4 bg-white rounded-xl border border-gray-200">
                                                <div class="space-y-0.5">
                                                    <Label class="text-sm font-medium text-gray-900">Require Photo</Label>
                                                    <p class="text-xs text-gray-500">Employee must take photo when clocking in/out</p>
                                                </div>
                                                <button
                                                    type="button"
                                                    class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                                                    :class="attendanceForm.require_photo ? 'bg-blue-600' : 'bg-gray-200'"
                                                    @click="attendanceForm.require_photo = !attendanceForm.require_photo"
                                                >
                                                    <span
                                                        class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                                        :class="attendanceForm.require_photo ? 'translate-x-5' : 'translate-x-0'"
                                                    />
                                                </button>
                                            </div>

                                            <div class="flex items-center justify-between p-4 bg-white rounded-xl border border-gray-200">
                                                <div class="space-y-0.5">
                                                    <Label class="text-sm font-medium text-gray-900">Require Location</Label>
                                                    <p class="text-xs text-gray-500">Employee must provide location when clocking in/out</p>
                                                </div>
                                                <button
                                                    type="button"
                                                    class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                                                    :class="attendanceForm.require_location ? 'bg-blue-600' : 'bg-gray-200'"
                                                    @click="attendanceForm.require_location = !attendanceForm.require_location"
                                                >
                                                    <span
                                                        class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                                        :class="attendanceForm.require_location ? 'translate-x-5' : 'translate-x-0'"
                                                    />
                                                </button>
                                            </div>

                                            <div class="flex items-center justify-between p-4 bg-white rounded-xl border border-gray-200">
                                                <div class="space-y-0.5">
                                                    <Label class="text-sm font-medium text-gray-900">Allow Overtime</Label>
                                                    <p class="text-xs text-gray-500">Allow employees to work beyond scheduled hours</p>
                                                </div>
                                                <button
                                                    type="button"
                                                    class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                                                    :class="attendanceForm.allow_overtime ? 'bg-blue-600' : 'bg-gray-200'"
                                                    @click="attendanceForm.allow_overtime = !attendanceForm.allow_overtime"
                                                >
                                                    <span
                                                        class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                                        :class="attendanceForm.allow_overtime ? 'translate-x-5' : 'translate-x-0'"
                                                    />
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex justify-end pt-4 border-t border-gray-200">
                                        <Button type="submit" :disabled="attendanceProcessing">
                                            <Loader2 v-if="attendanceProcessing" class="w-4 h-4 mr-2 animate-spin" />
                                            {{ attendanceProcessing ? 'Saving...' : 'Save Changes' }}
                                        </Button>
                                    </div>
                                </form>
                            </CardContent>
                        </Card>
                    </div>

                    <!-- Notification Settings -->
                    <div v-if="activeTab === 'notifications'" class="animate-in fade-in slide-in-from-bottom-2 duration-300">
                        <Card>
                            <CardHeader>
                                <CardTitle>Notifications</CardTitle>
                                <CardDescription>Manage email and system notifications</CardDescription>
                            </CardHeader>
                            <CardContent>
                                <form @submit.prevent="saveNotificationSettings" class="space-y-4">
                                    <div class="space-y-3">
                                        <div class="flex items-center justify-between p-4 bg-white rounded-xl border border-gray-200 shadow-sm">
                                            <div class="space-y-0.5">
                                                <Label class="text-sm font-medium text-gray-900">Email Notifications</Label>
                                                <p class="text-xs text-gray-500">Receive important updates via email</p>
                                            </div>
                                            <button
                                                type="button"
                                                class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                                                :class="notificationForm.email_notifications ? 'bg-blue-600' : 'bg-gray-200'"
                                                @click="notificationForm.email_notifications = !notificationForm.email_notifications"
                                            >
                                                <span
                                                    class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                                    :class="notificationForm.email_notifications ? 'translate-x-5' : 'translate-x-0'"
                                                />
                                            </button>
                                        </div>

                                        <div class="flex items-center justify-between p-4 bg-white rounded-xl border border-gray-200 shadow-sm">
                                            <div class="space-y-0.5">
                                                <Label class="text-sm font-medium text-gray-900">Attendance Alerts</Label>
                                                <p class="text-xs text-gray-500">Get notified about attendance issues</p>
                                            </div>
                                            <button
                                                type="button"
                                                class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                                                :class="notificationForm.attendance_alerts ? 'bg-blue-600' : 'bg-gray-200'"
                                                @click="notificationForm.attendance_alerts = !notificationForm.attendance_alerts"
                                            >
                                                <span
                                                    class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                                    :class="notificationForm.attendance_alerts ? 'translate-x-5' : 'translate-x-0'"
                                                />
                                            </button>
                                        </div>

                                        <div class="flex items-center justify-between p-4 bg-white rounded-xl border border-gray-200 shadow-sm">
                                            <div class="space-y-0.5">
                                                <Label class="text-sm font-medium text-gray-900">Payroll Notifications</Label>
                                                <p class="text-xs text-gray-500">Updates about payroll processing</p>
                                            </div>
                                            <button
                                                type="button"
                                                class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                                                :class="notificationForm.payroll_notifications ? 'bg-blue-600' : 'bg-gray-200'"
                                                @click="notificationForm.payroll_notifications = !notificationForm.payroll_notifications"
                                            >
                                                <span
                                                    class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                                    :class="notificationForm.payroll_notifications ? 'translate-x-5' : 'translate-x-0'"
                                                />
                                            </button>
                                        </div>

                                        <div class="flex items-center justify-between p-4 bg-white rounded-xl border border-gray-200 shadow-sm">
                                            <div class="space-y-0.5">
                                                <Label class="text-sm font-medium text-gray-900">Schedule Reminders</Label>
                                                <p class="text-xs text-gray-500">Reminders for upcoming schedules</p>
                                            </div>
                                            <button
                                                type="button"
                                                class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                                                :class="notificationForm.schedule_reminders ? 'bg-blue-600' : 'bg-gray-200'"
                                                @click="notificationForm.schedule_reminders = !notificationForm.schedule_reminders"
                                            >
                                                <span
                                                    class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                                    :class="notificationForm.schedule_reminders ? 'translate-x-5' : 'translate-x-0'"
                                                />
                                            </button>
                                        </div>
                                    </div>

                                    <div class="flex justify-end pt-4 border-t border-gray-200">
                                        <Button type="submit" :disabled="notificationProcessing">
                                            <Loader2 v-if="notificationProcessing" class="w-4 h-4 mr-2 animate-spin" />
                                            {{ notificationProcessing ? 'Saving...' : 'Save Changes' }}
                                        </Button>
                                    </div>
                                </form>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useToast } from '@/composables/useToast';
import {
    Button,
    Input,
    Label,
    Card,
    CardHeader,
    CardContent,
    CardTitle,
    CardDescription,
} from '@/components/ui';
import { Loader2, Clock, Building2, Users, Bell } from 'lucide-vue-next';

const { success, error } = useToast();

const props = defineProps({
    flash: Object,
});

const activeTab = ref('company');

const tabs = [
    {
        id: 'company',
        label: 'Company',
        icon: Building2,
    },
    {
        id: 'work-hours',
        label: 'Work Hours',
        icon: Clock,
    },
    {
        id: 'attendance',
        label: 'Attendance',
        icon: Users,
    },
    {
        id: 'notifications',
        label: 'Notifications',
        icon: Bell,
    },
];

// Company form
const companyForm = useForm({
    company_name: 'Attendance Pro',
    company_email: 'admin@attendancepro.com',
    company_phone: '',
    company_address: '',
});

const companyProcessing = ref(false);

const saveCompanySettings = () => {
    companyProcessing.value = true;
    companyForm.put('/settings/company', {
        onSuccess: () => {
            success('Company settings saved successfully');
            companyProcessing.value = false;
        },
        onError: () => {
            error('Failed to save company settings');
            companyProcessing.value = false;
        },
    });
};

// Work Hours form
const workHoursForm = useForm({
    work_start_time: '09:00',
    work_end_time: '18:00',
    grace_period_minutes: 15,
    late_threshold_minutes: 30,
});

const workHoursProcessing = ref(false);

const saveWorkHoursSettings = () => {
    workHoursProcessing.value = true;
    workHoursForm.put('/settings/work-hours', {
        onSuccess: () => {
            success('Work hours settings saved successfully');
            workHoursProcessing.value = false;
        },
        onError: () => {
            error('Failed to save work hours settings');
            workHoursProcessing.value = false;
        },
    });
};

// Attendance form
const attendanceForm = useForm({
    max_radius_meters: 500,
    require_photo: true,
    require_location: true,
    allow_overtime: true,
});

const attendanceProcessing = ref(false);

const saveAttendanceSettings = () => {
    attendanceProcessing.value = true;
    attendanceForm.put('/settings/attendance', {
        onSuccess: () => {
            success('Attendance settings saved successfully');
            attendanceProcessing.value = false;
        },
        onError: () => {
            error('Failed to save attendance settings');
            attendanceProcessing.value = false;
        },
    });
};

// Notification form
const notificationForm = useForm({
    email_notifications: true,
    attendance_alerts: true,
    payroll_notifications: true,
    schedule_reminders: true,
});

const notificationProcessing = ref(false);

const saveNotificationSettings = () => {
    notificationProcessing.value = true;
    notificationForm.put('/settings/notifications', {
        onSuccess: () => {
            success('Notification settings saved successfully');
            notificationProcessing.value = false;
        },
        onError: () => {
            error('Failed to save notification settings');
            notificationProcessing.value = false;
        },
    });
};

// Handle flash messages
watch(() => props.flash, (flash) => {
    if (flash?.success) {
        success(flash.success);
    }
    if (flash?.error) {
        error(flash.error);
    }
}, { immediate: true });
</script>
