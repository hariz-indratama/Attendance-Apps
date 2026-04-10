<template>
    <AppLayout>
        <div class="space-y-6">
            <!-- Page Header -->
            <div>
                <h1 class="text-2xl font-bold text-gray-900">My Profile</h1>
                <p class="text-sm text-gray-500 mt-1">Manage your personal information and account settings</p>
            </div>

            <!-- Profile Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-[300px_1fr] gap-6">
                <!-- Profile Sidebar -->
                <Card class="h-fit">
                    <CardContent class="pt-6">
                        <!-- Avatar Section -->
                        <div class="flex flex-col items-center pb-6 border-b border-gray-200 mb-6">
                            <div class="relative mb-2" @click="triggerAvatarUpload">
                                <div v-if="avatarPreview || user?.avatar" class="relative w-24 h-24 rounded-full overflow-hidden group cursor-pointer">
                                    <img
                                        :src="avatarPreview || avatarUrl"
                                        alt="Avatar"
                                        class="w-full h-full object-cover"
                                    />
                                    <div class="absolute inset-0 bg-blue-600/80 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity rounded-full">
                                        <Camera class="w-8 h-8 text-white" />
                                    </div>
                                </div>
                                <div v-else class="relative cursor-pointer" :class="avatarClass">
                                    <div class="w-24 h-24 rounded-full flex items-center justify-center text-3xl font-bold text-white shadow-lg transition-transform hover:scale-105">
                                        <User v-if="!userInitials" class="w-10 h-10 opacity-70" />
                                        <span v-else>{{ userInitials }}</span>
                                    </div>
                                </div>
                                <div v-if="!user?.avatar" class="absolute bottom-0 right-0 w-8 h-8 bg-gradient-to-r from-blue-600 to-sky-500 rounded-full flex items-center justify-center border-3 border-white shadow-md transition-transform hover:scale-115">
                                    <Camera class="w-4 h-4 text-white" />
                                </div>
                                <button
                                    v-if="user?.avatar"
                                    type="button"
                                    class="absolute -top-1 -right-1 w-7 h-7 bg-red-500 border-2 border-white rounded-full flex items-center justify-center cursor-pointer transition-all hover:bg-red-600 hover:scale-115 z-10"
                                    @click.stop="removeAvatar"
                                    title="Remove avatar"
                                >
                                    <X class="w-3 h-3 text-white" />
                                </button>
                            </div>
                            <input
                                ref="avatarInput"
                                type="file"
                                accept="image/*"
                                class="hidden"
                                @change="handleAvatarChange"
                            />
                            <p class="text-xs text-gray-400 mt-2 hover:text-gray-600 transition-colors cursor-pointer" @click="triggerAvatarUpload">Click to upload</p>
                            <div class="text-center mt-3">
                                <h3 class="text-lg font-semibold text-gray-900">{{ user?.name }}</h3>
                                <span class="inline-block mt-1 text-sm text-gray-500 bg-gray-100 px-3 py-1 rounded-full">
                                    {{ formattedRole }}
                                </span>
                            </div>
                        </div>

                        <!-- Quick Stats -->
                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-500">Employee ID</span>
                                <span class="text-sm font-medium text-gray-900">{{ user?.employee_id || '-' }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-500">Department</span>
                                <span class="text-sm font-medium text-gray-900">{{ user?.department || '-' }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-500">Position</span>
                                <span class="text-sm font-medium text-gray-900">{{ user?.position || '-' }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-500">Status</span>
                                <Badge :variant="user?.is_active ? 'success' : 'destructive'">
                                    {{ user?.is_active ? 'Active' : 'Inactive' }}
                                </Badge>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Profile Content -->
                <Card>
                    <CardContent class="p-6">
                        <!-- Tabs -->
                        <div class="flex gap-2 mb-6 pb-4 border-b border-gray-200 overflow-x-auto">
                            <button
                                v-for="tab in tabs"
                                :key="tab.id"
                                class="flex items-center gap-2 px-4 py-3 rounded-lg text-sm font-medium transition-all whitespace-nowrap"
                                :class="activeTab === tab.id
                                    ? 'bg-blue-50 text-blue-600'
                                    : 'text-gray-500 hover:bg-gray-100 hover:text-gray-900'"
                                @click="activeTab = tab.id"
                            >
                                <component :is="tab.icon" class="w-4 h-4" />
                                {{ tab.label }}
                            </button>
                        </div>

                        <!-- Tab Contents -->
                        <div class="bg-gray-50 rounded-xl p-6">
                            <!-- Personal Information -->
                            <div v-if="activeTab === 'personal'" class="space-y-6">
                                <div>
                                    <h2 class="text-xl font-semibold text-gray-900">Personal Information</h2>
                                    <p class="text-sm text-gray-500 mt-1">Update your personal details</p>
                                </div>

                                <div class="bg-white rounded-xl p-6 shadow-sm">
                                    <form @submit.prevent="savePersonalInfo" class="space-y-4">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div class="space-y-2">
                                                <Label for="name">Full Name</Label>
                                                <Input
                                                    id="name"
                                                    v-model="personalForm.name"
                                                    type="text"
                                                    placeholder="Enter your name"
                                                />
                                            </div>
                                            <div class="space-y-2">
                                                <Label for="email">Email Address</Label>
                                                <Input
                                                    id="email"
                                                    v-model="personalForm.email"
                                                    type="email"
                                                    placeholder="your@email.com"
                                                />
                                            </div>
                                            <div class="space-y-2">
                                                <Label for="phone">Phone Number</Label>
                                                <Input
                                                    id="phone"
                                                    v-model="personalForm.phone"
                                                    type="text"
                                                    placeholder="+1 234 567 8900"
                                                />
                                            </div>
                                            <div class="space-y-2">
                                                <Label for="position">Position</Label>
                                                <Input
                                                    id="position"
                                                    v-model="personalForm.position"
                                                    type="text"
                                                    placeholder="Your position"
                                                />
                                            </div>
                                            <div class="space-y-2 md:col-span-2">
                                                <Label for="department">Department</Label>
                                                <Input
                                                    id="department"
                                                    v-model="personalForm.department"
                                                    type="text"
                                                    placeholder="Your department"
                                                />
                                            </div>
                                        </div>

                                        <div class="flex justify-end pt-4 border-t border-gray-200">
                                            <Button type="submit" :disabled="personalProcessing">
                                                <Loader2 v-if="personalProcessing" class="w-4 h-4 mr-2 animate-spin" />
                                                {{ personalProcessing ? 'Saving...' : 'Save Changes' }}
                                            </Button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <!-- Change Password -->
                            <div v-if="activeTab === 'password'" class="space-y-6">
                                <div>
                                    <h2 class="text-xl font-semibold text-gray-900">Change Password</h2>
                                    <p class="text-sm text-gray-500 mt-1">Update your account password</p>
                                </div>

                                <div class="bg-white rounded-xl p-6 shadow-sm">
                                    <form @submit.prevent="savePassword" class="space-y-4">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div class="space-y-2 md:col-span-2">
                                                <Label for="current_password">Current Password</Label>
                                                <Input
                                                    id="current_password"
                                                    v-model="passwordForm.current_password"
                                                    type="password"
                                                    placeholder="Enter current password"
                                                />
                                                <p v-if="errors.current_password" class="text-sm text-red-500">{{ errors.current_password }}</p>
                                            </div>
                                            <div class="space-y-2">
                                                <Label for="password">New Password</Label>
                                                <Input
                                                    id="password"
                                                    v-model="passwordForm.password"
                                                    type="password"
                                                    placeholder="Enter new password"
                                                />
                                                <p v-if="errors.password" class="text-sm text-red-500">{{ errors.password }}</p>
                                            </div>
                                            <div class="space-y-2">
                                                <Label for="password_confirmation">Confirm New Password</Label>
                                                <Input
                                                    id="password_confirmation"
                                                    v-model="passwordForm.password_confirmation"
                                                    type="password"
                                                    placeholder="Confirm new password"
                                                />
                                            </div>
                                        </div>

                                        <div class="flex justify-end pt-4 border-t border-gray-200">
                                            <Button type="submit" :disabled="passwordProcessing">
                                                <Loader2 v-if="passwordProcessing" class="w-4 h-4 mr-2 animate-spin" />
                                                {{ passwordProcessing ? 'Updating...' : 'Update Password' }}
                                            </Button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <!-- Bank Account -->
                            <div v-if="activeTab === 'bank'" class="space-y-6">
                                <div>
                                    <h2 class="text-xl font-semibold text-gray-900">Bank Account</h2>
                                    <p class="text-sm text-gray-500 mt-1">Manage your payroll information</p>
                                </div>

                                <div class="bg-white rounded-xl p-6 shadow-sm">
                                    <form @submit.prevent="saveBankInfo" class="space-y-4">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div class="space-y-2">
                                                <Label for="bank_name">Bank Name</Label>
                                                <Input
                                                    id="bank_name"
                                                    v-model="bankForm.bank_name"
                                                    type="text"
                                                    placeholder="e.g., Bank of America"
                                                />
                                            </div>
                                            <div class="space-y-2">
                                                <Label for="bank_account">Account Number</Label>
                                                <Input
                                                    id="bank_account"
                                                    v-model="bankForm.bank_account"
                                                    type="text"
                                                    placeholder="Enter account number"
                                                />
                                            </div>
                                            <div class="space-y-2 md:col-span-2">
                                                <Label for="bank_account_name">Account Holder Name</Label>
                                                <Input
                                                    id="bank_account_name"
                                                    v-model="bankForm.bank_account_name"
                                                    type="text"
                                                    placeholder="Name as per bank account"
                                                />
                                            </div>
                                        </div>

                                        <div class="flex justify-end pt-4 border-t border-gray-200">
                                            <Button type="submit" :disabled="bankProcessing">
                                                <Loader2 v-if="bankProcessing" class="w-4 h-4 mr-2 animate-spin" />
                                                {{ bankProcessing ? 'Saving...' : 'Save Changes' }}
                                            </Button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, watch, h } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useToast } from '@/composables/useToast';
import {
    Button,
    Input,
    Label,
    Card,
    CardContent,
    Badge,
} from '@/components/ui';
import { User, Camera, X, Loader2, Lock, Building2 } from 'lucide-vue-next';

const { success, error } = useToast();

const props = defineProps({
    user: Object,
    flash: Object,
    errors: Object,
});

const activeTab = ref('personal');

const UserIcon = h(User);
const LockIcon = h(Lock);
const BuildingIcon = h(Building2);

const tabs = [
    {
        id: 'personal',
        label: 'Personal',
        icon: UserIcon,
    },
    {
        id: 'password',
        label: 'Password',
        icon: LockIcon,
    },
    {
        id: 'bank',
        label: 'Bank Account',
        icon: BuildingIcon,
    },
];

// Computed
const userInitials = computed(() => {
    if (props.user?.name) {
        return props.user.name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
    }
    return 'U';
});

const formattedRole = computed(() => {
    const roles = {
        admin: 'Administrator',
        manager: 'Manager',
        employee: 'Employee',
    };
    return roles[props.user?.role] || 'Employee';
});

const avatarClass = computed(() => {
    const colors = ['bg-gradient-to-br from-blue-500 to-blue-700', 'bg-gradient-to-br from-purple-500 to-purple-700', 'bg-gradient-to-br from-emerald-500 to-emerald-700', 'bg-gradient-to-br from-amber-500 to-amber-700', 'bg-gradient-to-br from-pink-500 to-pink-700'];
    const index = props.user?.name?.charCodeAt(0) % colors.length || 0;
    return colors[index];
});

const avatarUrl = computed(() => {
    if (props.user?.avatar) {
        const timestamp = props.user.updated_at ? new Date(props.user.updated_at).getTime() : Date.now();
        return `/storage/${props.user.avatar}?v=${timestamp}`;
    }
    return null;
});

const avatarPreview = ref(null);
const avatarInput = ref(null);

const triggerAvatarUpload = () => {
    avatarInput.value?.click();
};

const handleAvatarChange = (event) => {
    const file = event.target.files[0];
    if (!file) return;

    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!allowedTypes.includes(file.type)) {
        error('Please select a valid image file (JPG, PNG, GIF, or WebP)');
        return;
    }

    if (file.size > 5 * 1024 * 1024) {
        error('Image size must be less than 5MB');
        return;
    }

    const reader = new FileReader();
    reader.onload = (e) => {
        avatarPreview.value = e.target.result;
    };
    reader.readAsDataURL(file);

    uploadAvatar(file);
};

const uploadAvatar = (file) => {
    const formData = new FormData();
    formData.append('avatar', file);

    success('Uploading avatar...');

    axios.post('/profile/avatar', formData, {
        headers: {
            'Content-Type': 'multipart/form-data',
        },
    })
    .then((response) => {
        success('Avatar updated successfully');
        avatarPreview.value = null;
        router.reload({ only: ['user'] });
    })
    .catch((err) => {
        error(err.response?.data?.message || 'Failed to upload avatar');
        avatarPreview.value = null;
    });
};

const removeAvatar = () => {
    if (confirm('Are you sure you want to remove your avatar?')) {
        axios.delete('/profile/avatar')
            .then((response) => {
                success('Avatar removed successfully');
                router.reload({ only: ['user'] });
            })
            .catch((err) => {
                error(err.response?.data?.message || 'Failed to remove avatar');
            });
    }
};

// Personal form
const personalForm = useForm({
    name: props.user?.name || '',
    email: props.user?.email || '',
    phone: props.user?.phone || '',
    position: props.user?.position || '',
    department: props.user?.department || '',
});

const personalProcessing = ref(false);

const savePersonalInfo = () => {
    personalProcessing.value = true;
    personalForm.put('/profile', {
        onSuccess: () => {
            success('Personal information updated successfully');
            personalProcessing.value = false;
        },
        onError: () => {
            error('Failed to update personal information');
            personalProcessing.value = false;
        },
    });
};

// Password form
const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const passwordProcessing = ref(false);

const savePassword = () => {
    passwordProcessing.value = true;
    passwordForm.put('/profile/password', {
        onSuccess: () => {
            success('Password changed successfully');
            passwordForm.current_password = '';
            passwordForm.password = '';
            passwordForm.password_confirmation = '';
            passwordProcessing.value = false;
        },
        onError: () => {
            error('Failed to change password');
            passwordProcessing.value = false;
        },
    });
};

// Bank form
const bankForm = useForm({
    bank_name: props.user?.bank_name || '',
    bank_account: props.user?.bank_account || '',
    bank_account_name: props.user?.bank_account_name || '',
});

const bankProcessing = ref(false);

const saveBankInfo = () => {
    bankProcessing.value = true;
    bankForm.put('/profile/bank', {
        onSuccess: () => {
            success('Bank information updated successfully');
            bankProcessing.value = false;
        },
        onError: () => {
            error('Failed to update bank information');
            bankProcessing.value = false;
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
