<template>
    <AppLayout>
        <div class="space-y-6 max-w-6xl mx-auto">
            <!-- Page Header -->
            <div class="space-y-1">
                <h1 class="text-2xl font-bold text-gray-900">Reports</h1>
                <p class="text-sm text-gray-500">Generate and view various reports</p>
            </div>

            <!-- Reports Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Attendance Report -->
                <Card class="hover:shadow-md transition-shadow">
                    <CardContent class="flex items-center gap-4 p-4">
                        <div class="w-14 h-14 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center flex-shrink-0">
                            <Users class="w-6 h-6" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-base font-semibold text-gray-900">Attendance Report</h3>
                            <p class="text-sm text-gray-500">View employee attendance records and summaries</p>
                        </div>
                        <Button variant="outline" size="sm" @click="openModal('attendance')">
                            Generate
                        </Button>
                    </CardContent>
                </Card>

                <!-- Payroll Report -->
                <Card class="hover:shadow-md transition-shadow">
                    <CardContent class="flex items-center gap-4 p-4">
                        <div class="w-14 h-14 rounded-lg bg-green-50 text-green-600 flex items-center justify-center flex-shrink-0">
                            <DollarSign class="w-6 h-6" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-base font-semibold text-gray-900">Payroll Report</h3>
                            <p class="text-sm text-gray-500">View payroll summaries and salary details</p>
                        </div>
                        <Button variant="outline" size="sm" @click="openModal('payroll')">
                            Generate
                        </Button>
                    </CardContent>
                </Card>

                <!-- Overtime Report -->
                <Card class="hover:shadow-md transition-shadow">
                    <CardContent class="flex items-center gap-4 p-4">
                        <div class="w-14 h-14 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center flex-shrink-0">
                            <Clock class="w-6 h-6" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-base font-semibold text-gray-900">Overtime Report</h3>
                            <p class="text-sm text-gray-500">View employee overtime hours and compensation</p>
                        </div>
                        <Button variant="outline" size="sm" @click="openModal('overtime')">
                            Generate
                        </Button>
                    </CardContent>
                </Card>

                <!-- Summary Report -->
                <Card class="hover:shadow-md transition-shadow">
                    <CardContent class="flex items-center gap-4 p-4">
                        <div class="w-14 h-14 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center flex-shrink-0">
                            <FileText class="w-6 h-6" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-base font-semibold text-gray-900">Summary Report</h3>
                            <p class="text-sm text-gray-500">View comprehensive monthly and yearly summaries</p>
                        </div>
                        <Button variant="outline" size="sm" @click="openModal('summary')">
                            Generate
                        </Button>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { Users, DollarSign, Clock, FileText } from 'lucide-vue-next';
import { Card, CardContent, Button } from '@/components/ui';

const openModal = (type: string) => {
    // TODO: Implement modal for report generation
    console.log('Open modal for:', type);
};
</script>
