import { ref, onMounted, onUnmounted } from 'vue';

interface SessionConfig {
    warningBeforeExpiry?: number; // milliseconds before expiry to show warning
    checkInterval?: number; // interval to check session in milliseconds
}

const isAuthenticated = ref(false);
const sessionExpiry = ref<Date | null>(null);
const showSessionWarning = ref(false);
let checkInterval: ReturnType<typeof setInterval> | null = null;

export function useSession(config: SessionConfig = {}) {
    const { warningBeforeExpiry = 60000, checkInterval: interval = 30000 } = config;

    const startSessionMonitoring = (expiryTime: Date | string) => {
        sessionExpiry.value = typeof expiryTime === 'string' ? new Date(expiryTime) : expiryTime;
        isAuthenticated.value = true;

        if (checkInterval) {
            clearInterval(checkInterval);
        }

        checkInterval = setInterval(() => {
            checkSessionStatus();
        }, interval);
    };

    const checkSessionStatus = () => {
        if (!sessionExpiry.value) return;

        const now = new Date();
        const timeUntilExpiry = sessionExpiry.value.getTime() - now.getTime();

        if (timeUntilExpiry <= 0) {
            // Session expired
            handleSessionExpired();
        } else if (timeUntilExpiry <= warningBeforeExpiry) {
            // Show warning
            showSessionWarning.value = true;
        }
    };

    const handleSessionExpired = () => {
        isAuthenticated.value = false;
        showSessionWarning.value = false;

        if (checkInterval) {
            clearInterval(checkInterval);
            checkInterval = null;
        }

        // Show notification and redirect to login
        const Toast = window.Toast || null;
        if (Toast) {
            Toast.error('Session expired. Please login again.');
        }

        // Delay redirect to show notification
        setTimeout(() => {
            window.location.href = '/login?reason=session_expired';
        }, 1500);
    };

    const clearSession = () => {
        isAuthenticated.value = false;
        sessionExpiry.value = null;
        showSessionWarning.value = false;

        if (checkInterval) {
            clearInterval(checkInterval);
            checkInterval = null;
        }
    };

    const refreshSession = () => {
        // Call the server to refresh session
        fetch('/api/auth/refresh', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'Accept': 'application/json',
            },
        })
        .then(response => {
            if (response.ok) {
                return response.json();
            }
            throw new Error('Session refresh failed');
        })
        .then(data => {
            if (data.expires_at) {
                startSessionMonitoring(data.expires_at);
                showSessionWarning.value = false;
            }
        })
        .catch(() => {
            handleSessionExpired();
        });
    };

    onUnmounted(() => {
        if (checkInterval) {
            clearInterval(checkInterval);
        }
    });

    return {
        isAuthenticated,
        sessionExpiry,
        showSessionWarning,
        startSessionMonitoring,
        clearSession,
        refreshSession,
    };
}

// Setup global error handler for 401 responses
export function setupSessionInterceptor() {
    const originalFetch = window.fetch;

    window.fetch = async (...args) => {
        const response = await originalFetch(...args);

        // If 401 unauthorized, redirect to login
        if (response.status === 401) {
            const Toast = window.Toast || null;
            if (Toast) {
                Toast.error('Session expired. Please login again.');
            }

            // Small delay for user to see notification
            setTimeout(() => {
                window.location.href = '/login?reason=session_expired';
            }, 500);
        }

        return response;
    };
}

// Export singleton state for sharing across components
export const sessionState = {
    isAuthenticated,
    sessionExpiry,
    showSessionWarning,
};

// Initialize session from server on app load
export const initSession = async () => {
    try {
        const response = await fetch('/api/auth/session', {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'Accept': 'application/json',
            },
        });

        if (response.ok) {
            const data = await response.json();
            if (data.expires_at) {
                sessionExpiry.value = new Date(data.expires_at);
                isAuthenticated.value = true;
                return data;
            }
        }
    } catch (error) {
        console.error('Failed to initialize session:', error);
    }
    return null;
};
