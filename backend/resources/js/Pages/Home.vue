<template>
    <div class="min-h-screen flex items-center justify-center bg-linear-to-br from-blue-500 to-purple-600">
        <div class="max-w-md w-full p-8 bg-white rounded-xl shadow-2xl text-center">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Attendance App</h1>
            <p class="text-gray-600 mb-6">Employee Attendance System</p>

            <div class="space-y-4">
                <Link
                    href="/login"
                    class="block w-full py-3 px-4 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium"
                >
                    Sign In
                </Link>
                <Link
                    href="/register"
                    class="block w-full py-3 px-4 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors font-medium"
                >
                    Register
                </Link>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
</script>
