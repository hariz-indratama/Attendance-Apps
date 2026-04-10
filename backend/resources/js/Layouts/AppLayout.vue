<template>
    <div class="flex min-h-screen bg-gray-50">
        <Toast />

        <!-- Sidebar -->
        <aside class="w-64 bg-gradient-to-b from-blue-800 to-blue-900 fixed h-screen flex flex-col">
            <div class="p-6 border-b border-white/10">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white/15 rounded-lg flex items-center justify-center">
                        <svg viewBox="0 0 40 40" fill="none" class="w-6 h-6 text-white">
                            <rect x="4" y="8" width="32" height="24" rx="4" stroke="currentColor" stroke-width="2.5"/>
                            <path d="M4 16H36" stroke="currentColor" stroke-width="2.5"/>
                            <circle cx="20" cy="28" r="4" stroke="currentColor" stroke-width="2.5"/>
                            <circle cx="8" cy="28" r="2" fill="currentColor"/>
                            <circle cx="32" cy="28" r="2" fill="currentColor"/>
                        </svg>
                    </div>
                    <span class="text-white font-bold text-lg tracking-tight">Attendance Pro</span>
                </div>
            </div>

            <nav class="flex-1 p-3 flex flex-col gap-1">
                <Link
                    v-for="item in navItems"
                    :key="item.name"
                    :href="item.href"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl text-white/70 text-sm font-medium transition-all duration-200 hover:bg-white/10 hover:text-white"
                    :class="{ 'bg-white/15 text-white': item.active }"
                >
                    <component :is="item.icon" v-if="item.icon" class="w-5 h-5" />
                    <span>{{ item.name }}</span>
                </Link>
            </nav>

            <div class="p-4 border-t border-white/10">
                <DropdownMenu v-slot="{ isOpen, close }">
                    <DropdownMenuTrigger as-child>
                        <button type="button" class="w-full flex items-center gap-3 p-2 rounded-xl hover:bg-white/10 cursor-pointer transition-colors outline-none">
                            <Avatar size="md">{{ userInitials }}</Avatar>
                            <div class="flex-1 text-left">
                                <div class="text-white text-sm font-medium">{{ user?.name || 'User' }}</div>
                                <div class="text-white/60 text-xs">{{ user?.role === 'admin' ? 'Administrator' : 'Employee' }}</div>
                            </div>
                            <ChevronDown class="w-4 h-4 text-white/60" />
                        </button>
                    </DropdownMenuTrigger>
                    <div v-if="isOpen" class="absolute left-0 mb-2 w-56 rounded-xl border bg-white p-1 shadow-lg z-50">
                        <DropdownMenuLabel>{{ user?.name || 'User' }}</DropdownMenuLabel>
                        <DropdownMenuLabel class="text-xs text-gray-500 font-normal">{{ user?.email || '' }}</DropdownMenuLabel>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem as-child>
                            <Link href="/profile" class="flex items-center gap-2 cursor-pointer" @click="close()">
                                <UserIcon class="w-4 h-4" />
                                Profile
                            </Link>
                        </DropdownMenuItem>
                        <DropdownMenuItem as-child>
                            <Link href="/settings" class="flex items-center gap-2 cursor-pointer" @click="close()">
                                <Settings class="w-4 h-4" />
                                Settings
                            </Link>
                        </DropdownMenuItem>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem as-child>
                            <Link href="/logout" method="post" as="button" class="flex items-center gap-2 w-full text-red-600 cursor-pointer" @click="close()">
                                <LogOut class="w-4 h-4" />
                                Logout
                            </Link>
                        </DropdownMenuItem>
                    </div>
                </DropdownMenu>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 ml-64 flex flex-col">
            <!-- Top Header -->
            <header class="h-16 bg-white border-b border-gray-200 px-8 flex items-center justify-between sticky top-0 z-40">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Dashboard</h1>
                    <span class="text-sm text-gray-500">{{ currentDate }}</span>
                </div>
                <div class="flex items-center gap-4">
                    <div class="relative">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                        <Input
                            type="text"
                            placeholder="Search..."
                            class="pl-10 w-72 bg-gray-50 border-gray-200"
                        />
                    </div>
                    <Button variant="ghost" size="icon" class="relative">
                        <Bell class="w-5 h-5 text-gray-600" />
                        <span class="absolute top-1 right-1 w-4 h-4 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center">3</span>
                    </Button>
                    <DropdownMenu v-slot="{ isOpen, close }">
                        <DropdownMenuTrigger as-child>
                            <button type="button" class="flex items-center gap-2 p-1.5 rounded-xl hover:bg-gray-100 cursor-pointer transition-colors outline-none">
                                <Avatar size="md">{{ userInitials }}</Avatar>
                                <ChevronDown class="w-4 h-4 text-gray-500" />
                            </button>
                        </DropdownMenuTrigger>
                        <div v-if="isOpen" class="absolute right-0 mt-2 w-56 rounded-xl border bg-white p-1 shadow-lg z-50">
                            <DropdownMenuLabel>{{ user?.name || 'User' }}</DropdownMenuLabel>
                            <DropdownMenuLabel class="text-xs text-gray-500 font-normal">{{ user?.email || '' }}</DropdownMenuLabel>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem as-child>
                                <Link href="/profile" class="flex items-center gap-2 cursor-pointer" @click="close()">
                                    <UserIcon class="w-4 h-4" />
                                    Profile
                                </Link>
                            </DropdownMenuItem>
                            <DropdownMenuItem as-child>
                                <Link href="/settings" class="flex items-center gap-2 cursor-pointer" @click="close()">
                                    <Settings class="w-4 h-4" />
                                    Settings
                                </Link>
                            </DropdownMenuItem>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem as-child>
                                <Link href="/logout" method="post" as="button" class="flex items-center gap-2 w-full text-red-600 cursor-pointer" @click="close()">
                                    <LogOut class="w-4 h-4" />
                                    Logout
                                </Link>
                            </DropdownMenuItem>
                        </div>
                    </DropdownMenu>
                </div>
            </header>

            <!-- Main Content Area -->
            <main class="flex-1 p-8">
                <slot />
            </main>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Toast from '@/components/ui/Toast.vue';
import type { User } from '@/types';
import {
    LayoutDashboard,
    Users,
    Clock,
    Calendar,
    DollarSign,
    FileText,
    Settings,
    Search,
    Bell,
    ChevronDown,
    User as UserIcon,
    LogOut
} from 'lucide-vue-next';
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import Avatar from '@/components/ui/Avatar.vue';
import DropdownMenu from '@/components/ui/DropdownMenu.vue';
import DropdownMenuTrigger from '@/components/ui/DropdownMenuTrigger.vue';
import DropdownMenuItem from '@/components/ui/DropdownMenuItem.vue';
import DropdownMenuLabel from '@/components/ui/DropdownMenuLabel.vue';
import DropdownMenuSeparator from '@/components/ui/DropdownMenuSeparator.vue';

interface NavItem {
    name: string;
    href: string;
    active: boolean;
    icon: typeof LayoutDashboard;
}

interface AuthUser {
    user: User | null;
}

const page = usePage();

const currentRoute = computed(() => page.url);

// Current date
const currentDate = computed(() => {
    const date = new Date();
    return date.toLocaleDateString('en-US', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });
});

// Get authenticated user
const user = computed(() => {
    const auth = page.props.auth as AuthUser | undefined;
    return auth?.user || null;
});

// Get user initials
const userInitials = computed((): string => {
    if (user.value?.name) {
        return user.value.name.split(' ').map((n: string) => n[0]).join('').toUpperCase().slice(0, 2);
    }
    return 'U';
});

const navItems = computed((): NavItem[] => [
    {
        name: 'Dashboard',
        href: '/dashboard',
        active: currentRoute.value.startsWith('/dashboard') && !currentRoute.value.startsWith('/employees'),
        icon: LayoutDashboard,
    },
    {
        name: 'Employees',
        href: '/employees',
        active: currentRoute.value.startsWith('/employees'),
        icon: Users,
    },
    {
        name: 'Attendance',
        href: '/attendance',
        active: currentRoute.value.startsWith('/attendance'),
        icon: Clock,
    },
    {
        name: 'Schedule',
        href: '/schedules',
        active: currentRoute.value.startsWith('/schedules'),
        icon: Calendar,
    },
    {
        name: 'Payroll',
        href: '/payroll',
        active: currentRoute.value.startsWith('/payroll'),
        icon: DollarSign,
    },
    {
        name: 'Reports',
        href: '/reports',
        active: currentRoute.value.startsWith('/reports'),
        icon: FileText,
    },
    {
        name: 'Settings',
        href: '/settings',
        active: currentRoute.value.startsWith('/settings'),
        icon: Settings,
    }
]);
</script>
