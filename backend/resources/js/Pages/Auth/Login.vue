<template>
    <div class="auth-container">
        <Toast />
        <!-- Animated Background -->
        <div class="bg-pattern"></div>
        <div class="bg-gradient"></div>
        <div class="floating-shapes">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="shape shape-3"></div>
        </div>

        <div class="content-wrapper">
            <!-- Left Side - Branding -->
            <div class="brand-section">
                <div class="brand-content">
                    <div class="logo-wrapper">
                        <div class="logo">
                            <svg viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect x="4" y="8" width="32" height="24" rx="4" stroke="currentColor" stroke-width="2.5"/>
                                <path d="M4 16H36" stroke="currentColor" stroke-width="2.5"/>
                                <circle cx="20" cy="28" r="4" stroke="currentColor" stroke-width="2.5"/>
                                <circle cx="8" cy="28" r="2" fill="currentColor"/>
                                <circle cx="32" cy="28" r="2" fill="currentColor"/>
                            </svg>
                        </div>
                    </div>
                    <h1 class="brand-title">Attendance Pro</h1>
                    <p class="brand-subtitle">Smart employee attendance tracking with GPS precision</p>

                    <div class="features-list">
                        <div class="feature-item" v-for="(feature, index) in features" :key="index" :style="{ animationDelay: `${0.8 + index * 0.15}s` }">
                            <div class="feature-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                            </div>
                            <span>{{ feature }}</span>
                        </div>
                    </div>
                </div>

                <div class="illustration">
                    <div class="phone-mockup">
                        <div class="phone-screen">
                            <div class="time-display">09:41</div>
                            <div class="status-card">
                                <div class="status-icon success">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                    </svg>
                                </div>
                                <div class="status-text">
                                    <span class="status-label">Checked In</span>
                                    <span class="status-time">8:30 AM</span>
                                </div>
                            </div>
                            <div class="check-button">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side - Form -->
            <div class="form-section">
                <div class="form-card">
                    <div class="form-header">
                        <h2 class="form-title">Welcome Back</h2>
                        <p class="form-subtitle">Sign in to continue to your dashboard</p>
                    </div>

                    <form @submit.prevent="handleSubmit" class="auth-form" autocomplete="off">
                        <div class="form-fields">
                            <div class="input-group" :class="{ 'has-error': errors.email, 'is-active': form.email }">
                                <div class="input-icon">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                        <polyline points="22,6 12,13 2,6"></polyline>
                                    </svg>
                                </div>
                                <input
                                    id="login-email"
                                    v-model="form.email"
                                    type="email"
                                    autocomplete="off"
                                    required
                                    placeholder=" "
                                    readonly
                                    @focus="removeReadonly($event); focusedField = 'email'"
                                    @blur="focusedField = null"
                                />
                                <label for="login-email">Email Address</label>
                                <div class="input-line"></div>
                                <span v-if="errors.email" class="error-message">{{ errors.email }}</span>
                            </div>

                            <div class="input-group" :class="{ 'has-error': errors.password, 'is-active': form.password }">
                                <div class="input-icon">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                    </svg>
                                </div>
                                <input
                                    id="login-password"
                                    v-model="form.password"
                                    :type="showPassword ? 'text' : 'password'"
                                    autocomplete="off"
                                    required
                                    placeholder=" "
                                    readonly
                                    @focus="removeReadonly($event); focusedField = 'password'"
                                    @blur="focusedField = null"
                                />
                                <label for="login-password">Password</label>
                                <button type="button" class="toggle-password" @click="showPassword = !showPassword">
                                    <svg v-if="showPassword" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                        <line x1="1" y1="1" x2="23" y2="23"></line>
                                    </svg>
                                    <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </button>
                                <div class="input-line"></div>
                                <span v-if="errors.password" class="error-message">{{ errors.password }}</span>
                            </div>
                        </div>

                        <div class="form-options">
                            <label class="checkbox-wrapper">
                                <input type="checkbox" v-model="form.remember" />
                                <span class="checkmark"></span>
                                <span class="checkbox-label">Remember me</span>
                            </label>
                            <Link href="/forgot-password" class="forgot-link">Forgot password?</Link>
                        </div>

                        <button type="submit" class="submit-btn" :disabled="processing">
                            <span class="btn-text">{{ processing ? 'Please wait...' : 'Sign In' }}</span>
                            <span class="btn-icon">
                                <svg v-if="!processing" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                    <polyline points="12 5 19 12 12 19"></polyline>
                                </svg>
                                <div v-else class="spinner"></div>
                            </span>
                        </button>
                    </form>

                    <div class="form-footer">
                        <p>Don't have an account? <button type="button" @click="navigateToRegister" class="link-btn">Register</button></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, reactive, watch, onMounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import Toast from '@/components/ui/Toast.vue';
import { useToast } from '@/composables/useToast';
import type { Flash } from '@/types';

const { success, error } = useToast();

const navigateToRegister = () => {
    window.location.href = '/register';
};

interface Props {
    errors?: Record<string, string>;
    flash?: Flash;
}

const props = withDefaults(defineProps<Props>(), {
    errors: () => ({}),
    flash: () => ({}),
});

const processing = ref(false);
const showPassword = ref(false);
const focusedField = ref<string | null>(null);

// Use plain reactive instead of useForm
const form = reactive({
    email: '',
    password: '',
    remember: false,
});

const errors = reactive<Record<string, string>>({});

// Reset form completely
const resetForm = () => {
    form.email = '';
    form.password = '';
    form.remember = false;
    Object.keys(errors).forEach(key => delete errors[key]);
};

// Remove readonly on focus to prevent autofill
const removeReadonly = (event: Event) => {
    const target = event.target as HTMLInputElement;
    target.removeAttribute('readonly');
};

onMounted(() => {
    resetForm();

    // Check if redirected due to session expiry
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('reason') === 'session_expired') {
        error('Session expired. Please login again.');
        // Clean URL
        window.history.replaceState({}, document.title, '/login');
    }
});

watch(() => props.flash, (flash) => {
    if (flash?.success) {
        success(flash.success);
    }
    if (flash?.error) {
        error(flash.error);
    }
}, { immediate: true });

const features: string[] = [
    'GPS Location Tracking',
    'Real-time Attendance',
    'Easy Reporting',
];

const handleSubmit = async () => {
    processing.value = true;
    errors.email = '';
    errors.password = '';

    try {
        const response = await fetch('/login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify({
                email: form.email,
                password: form.password,
                remember: form.remember,
            }),
        });

        const data = await response.json();

        if (!response.ok) {
            if (data.errors) {
                Object.assign(errors, data.errors);
            }
            if (data.message) {
                error(data.message);
            }
            processing.value = false;
            return;
        }

        // Success - show toast and redirect via Inertia
        const userName = data.user?.name || 'User';
        success(`Login berhasil! Selamat datang, ${userName}.`);
        setTimeout(() => {
            router.visit(data.redirect || '/dashboard');
        }, 500);
    } catch (err) {
        error('An error occurred. Please try again.');
        processing.value = false;
    }
};
</script>

<style scoped>
/* Disable browser autofill styles */
input:-webkit-autofill,
input:-webkit-autofill:hover,
input:-webkit-autofill:focus,
textarea:-webkit-autofill,
textarea:-webkit-autofill:hover,
textarea:-webkit-autofill:focus,
select:-webkit-autofill,
select:-webkit-autofill:hover,
select:-webkit-autofill:focus {
    -webkit-text-fill-color: #111827 !important;
    -webkit-box-shadow: 0 0 0px 1000px #FFFFFF inset !important;
    transition: background-color 5000s ease-in-out 0s !important;
}

.auth-container {
    --primary: #2563EB;
    --primary-dark: #1E40AF;
    --primary-light: #60A5FA;
    --secondary: #0EA5E9;
    --dark: #1F2937;
    --gray: #6B7280;
    --light: #F9FAFB;
    --white: #FFFFFF;
    --error: #EF4444;
    --gradient-start: #2563EB;
    --gradient-end: #0EA5E9;
}

.auth-container {
    min-height: 100vh;
    font-family: 'Plus Jakarta Sans', sans-serif;
    position: relative;
    overflow: hidden;
    background: #F1F5F9;
}

.bg-pattern {
    position: absolute;
    inset: 0;
    background-image:
        radial-gradient(circle at 25% 25%, rgba(37, 99, 235, 0.2) 0%, transparent 50%),
        radial-gradient(circle at 75% 75%, rgba(14, 165, 233, 0.15) 0%, transparent 50%);
}

.bg-gradient {
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(30, 64, 175, 0.3) 0%, rgba(37, 99, 235, 0.3) 100%);
}

.floating-shapes {
    position: absolute;
    inset: 0;
    overflow: hidden;
}

.shape {
    position: absolute;
    border-radius: 50%;
    opacity: 0.1;
    animation: float 20s ease-in-out infinite;
}

.shape-1 {
    width: 400px;
    height: 400px;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    top: -100px;
    right: -100px;
    animation-delay: 0s;
    filter: blur(60px);
}

.shape-2 {
    width: 300px;
    height: 300px;
    background: linear-gradient(135deg, var(--secondary) 0%, #38BDF8 100%);
    bottom: -50px;
    left: -50px;
    animation-delay: -5s;
    filter: blur(50px);
}

.shape-3 {
    width: 200px;
    height: 200px;
    background: linear-gradient(135deg, var(--gradient-end) 0%, #22D3EE 100%);
    top: 50%;
    left: 30%;
    animation-delay: -10s;
    filter: blur(40px);
}

@keyframes float {
    0%, 100% { transform: translate(0, 0) rotate(0deg); }
    33% { transform: translate(30px, -30px) rotate(10deg); }
    66% { transform: translate(-20px, 20px) rotate(-5deg); }
}

.content-wrapper {
    position: relative;
    z-index: 10;
    display: grid;
    grid-template-columns: 1fr 1fr;
    min-height: 100vh;
}

@media (max-width: 1024px) {
    .content-wrapper {
        grid-template-columns: 1fr;
    }
    .brand-section {
        display: none;
    }
    .form-section {
        padding: 1.5rem;
        justify-content: center;
        align-items: center;
    }
    .form-card {
        margin-top: 0;
        max-width: 100%;
    }
}

.brand-section {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    padding: 3rem;
    position: relative;
    background: linear-gradient(135deg, #1E40AF 0%, #2563EB 50%, #0EA5E9 100%);
    overflow: hidden;
}

.brand-section::before {
    content: '';
    position: absolute;
    inset: 0;
    background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}

.brand-content {
    position: relative;
    z-index: 2;
    text-align: center;
    animation: fadeInUp 0.8s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px) scale(0.98);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

@keyframes fadeInScale {
    from {
        opacity: 0;
        transform: scale(0.95);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

@keyframes shimmer {
    0% { background-position: -200% 0; }
    100% { background-position: 200% 0; }
}

.logo-wrapper {
    display: inline-flex;
    margin-bottom: 1.5rem;
}

.logo {
    width: 80px;
    height: 80px;
    background: rgba(255, 255, 255, 0.15);
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    animation: pulse 3s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% { box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.3); }
    50% { box-shadow: 0 0 0 20px rgba(255, 255, 255, 0); }
}

.logo svg {
    width: 48px;
    height: 48px;
    color: white;
}

.brand-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: white;
    margin-bottom: 0.75rem;
    letter-spacing: -0.02em;
}

.brand-subtitle {
    font-size: 1.1rem;
    color: rgba(255, 255, 255, 0.8);
    margin-bottom: 2.5rem;
    max-width: 400px;
}

.features-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    text-align: left;
}

.feature-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    color: rgba(255, 255, 255, 0.9);
    font-size: 0.95rem;
    opacity: 0;
    animation: fadeInUp 0.6s ease-out forwards;
}

.feature-icon {
    width: 24px;
    height: 24px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.feature-icon svg {
    width: 14px;
    height: 14px;
}

.illustration {
    position: relative;
    z-index: 2;
    margin-top: 3rem;
    animation: fadeInUp 1s ease-out 0.5s backwards;
}

.phone-mockup {
    width: 200px;
    height: 400px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 32px;
    padding: 12px;
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.3);
}

.phone-screen {
    background: linear-gradient(180deg, #1E40AF 0%, #1E3A8A 100%);
    border-radius: 24px;
    height: 100%;
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1.5rem;
}

.time-display {
    font-size: 2rem;
    font-weight: 600;
    color: white;
    letter-spacing: -0.02em;
}

.status-card {
    background: rgba(16, 185, 129, 0.15);
    border: 1px solid rgba(16, 185, 129, 0.3);
    border-radius: 16px;
    padding: 1rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    width: 100%;
    animation: slideIn 0.5s ease-out 1s backwards;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateX(-20px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.status-icon {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.status-icon.success {
    background: var(--secondary);
}

.status-icon svg {
    width: 20px;
    height: 20px;
    color: white;
}

.status-text {
    display: flex;
    flex-direction: column;
}

.status-label {
    font-size: 0.875rem;
    font-weight: 600;
    color: white;
}

.status-time {
    font-size: 0.75rem;
    color: rgba(255, 255, 255, 0.6);
}

.check-button {
    width: 64px;
    height: 64px;
    background: linear-gradient(135deg, var(--primary) 0%, var(--gradient-end) 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-top: auto;
    box-shadow: 0 10px 30px rgba(79, 70, 229, 0.4);
    animation: bounce 2s ease-in-out infinite 1.5s;
}

@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}

.check-button svg {
    width: 28px;
    height: 28px;
    color: white;
}

.form-section {
    display: flex;
    align-items: flex-start;
    justify-content: center;
    padding: 2rem 2rem 2rem 4rem;
    background: #F8FAFC !important;
    position: relative;
    z-index: 5;
    min-height: 100vh;
}

.form-card {
    width: 100%;
    max-width: 420px;
    background: #FFFFFF !important;
    border-radius: 20px;
    padding: 2rem 2.5rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08), 0 0 0 1px rgba(0, 0, 0, 0.04);
    margin-top: 5vh;
    animation: fadeInScale 0.8s cubic-bezier(0.16, 1, 0.3, 1);
}

.form-header {
    margin-bottom: 1.5rem;
    animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) 0.1s backwards;
}

.form-fields {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

.form-fields > *:nth-child(1) {
    animation: fadeInUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) 0.2s backwards;
}

.form-fields > *:nth-child(2) {
    animation: fadeInUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) 0.3s backwards;
}

.form-options {
    animation: fadeInUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) 0.4s backwards;
}

.submit-btn {
    animation: fadeInUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) 0.5s backwards;
}

.form-title {
    font-size: 1.75rem;
    font-weight: 700;
    color: #0F172A;
    margin-bottom: 0.5rem;
    letter-spacing: -0.02em;
}

.form-subtitle {
    color: #64748B;
    font-size: 0.95rem;
}

.input-group {
    position: relative;
}

.input-icon {
    position: absolute;
    left: 0;
    top: 50%;
    transform: translateY(-50%);
    width: 20px;
    height: 20px;
    color: #64748B;
    transition: color 0.3s ease;
    z-index: 1;
}

.input-group input {
    width: 100%;
    padding: 1rem 0 0.5rem 2rem;
    border: none;
    border-bottom: 2px solid #D1D5DB;
    background: transparent;
    font-size: 1rem;
    font-family: inherit;
    color: #111827;
    transition: border-color 0.3s ease;
    outline: none;
}

.input-group input:focus {
    border-color: var(--primary);
}

.input-group label {
    position: absolute;
    left: 2rem;
    top: 50%;
    transform: translateY(-50%);
    color: #64748B;
    font-size: 1rem;
    pointer-events: none;
    transition: all 0.3s ease;
}

.input-group.is-active label,
.input-group input:focus + label,
.input-group input:not(:placeholder-shown) + label {
    top: 0.25rem;
    transform: translateY(0);
    font-size: 0.75rem;
    color: var(--primary);
}

.input-group.is-active .input-icon,
.input-group input:focus ~ .input-icon {
    color: var(--primary);
}

.input-line {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 0;
    height: 2px;
    background: linear-gradient(90deg, var(--primary) 0%, var(--gradient-end) 100%);
    transition: width 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}

.input-group input:focus ~ .input-line {
    width: 100%;
}

.input-group.has-error input {
    border-color: var(--error);
}

.input-group.has-error label {
    color: var(--error);
}

.toggle-password {
    position: absolute;
    right: 0;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    cursor: pointer;
    padding: 0;
    color: #64748B;
    transition: color 0.3s ease;
}

.toggle-password:hover {
    color: var(--primary);
}

.toggle-password svg {
    width: 20px;
    height: 20px;
}

.error-message {
    display: block;
    color: var(--error);
    font-size: 0.75rem;
    margin-top: 0.25rem;
}

.form-options {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.checkbox-wrapper {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
    font-size: 0.875rem;
    color: #475569;
}

.checkbox-wrapper input {
    display: none;
}

.checkmark {
    width: 18px;
    height: 18px;
    border: 2px solid #D1D5DB;
    border-radius: 4px;
    position: relative;
    transition: all 0.2s ease;
}

.checkbox-wrapper input:checked + .checkmark {
    background: linear-gradient(135deg, var(--primary) 0%, var(--gradient-end) 100%);
    border-color: var(--primary);
}

.checkbox-wrapper input:checked + .checkmark::after {
    content: '';
    position: absolute;
    left: 5px;
    top: 2px;
    width: 4px;
    height: 8px;
    border: solid white;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
}

.forgot-link {
    font-size: 0.875rem;
    color: var(--primary);
    text-decoration: none;
    font-weight: 500;
    transition: color 0.2s ease;
}

.forgot-link:hover {
    color: var(--primary-dark);
}

.form-card .submit-btn,
div.form-card button.submit-btn {
    width: 100%;
    padding: 1rem 1.5rem;
    background: linear-gradient(135deg, #2563EB 0%, #0EA5E9 100%) !important;
    color: white !important;
    border: none;
    border-radius: 12px;
    font-size: 1rem;
    font-weight: 600;
    font-family: inherit;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    box-shadow: 0 4px 14px rgba(37, 99, 235, 0.35), 0 0 0 0 rgba(37, 99, 235, 0.3);
    position: relative;
    overflow: hidden;
}

.submit-btn::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    background-size: 200% 100%;
    opacity: 0;
    transition: opacity 0.3s;
}

.submit-btn:hover:not(:disabled) {
    transform: translateY(-3px);
    box-shadow: 0 12px 28px rgba(37, 99, 235, 0.45), 0 0 0 0 rgba(37, 99, 235, 0.3);
}

.submit-btn:hover::before {
    opacity: 1;
    animation: shimmer 2s infinite;
}

.submit-btn:active:not(:disabled) {
    transform: translateY(-1px);
}

.submit-btn:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

.btn-icon svg {
    width: 20px;
    height: 20px;
}

.spinner {
    width: 20px;
    height: 20px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.form-footer {
    margin-top: 1.5rem;
    text-align: center;
    color: #64748B;
    font-size: 0.875rem;
    animation: fadeInUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) 0.6s backwards;
}

.link-btn {
    background: none;
    border: none;
    color: var(--primary);
    font-weight: 600;
    cursor: pointer;
    font-size: inherit;
    font-family: inherit;
    transition: color 0.2s ease;
    text-decoration: none;
}

.link-btn:hover {
    color: var(--primary-dark);
}

@media (max-width: 640px) {
    .form-section {
        padding: 1rem;
    }
    .form-card {
        padding: 1.5rem;
        border-radius: 16px;
    }
    .form-title {
        font-size: 1.5rem;
    }
    .input-group input {
        padding: 0.875rem 0 0.5rem 2rem;
    }
    .input-group label {
        font-size: 0.9rem;
    }
}
</style>
