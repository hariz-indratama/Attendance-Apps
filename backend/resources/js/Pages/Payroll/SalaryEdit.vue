<template>
    <AppLayout>
        <div class="salary-edit">
            <!-- Header -->
            <div class="page-header">
                <div class="header-left">
                    <Link :href="`/payroll/employee/${employee.id}`" class="back-link">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M19 12H5M12 19l-7-7 7-7"/>
                        </svg>
                        Back to Details
                    </Link>
                    <h1 class="page-title">Edit Salary</h1>
                    <p class="page-subtitle">Update salary information for {{ employee.name }}</p>
                </div>
            </div>

            <!-- Employee Summary -->
            <div class="employee-summary">
                <div class="employee-avatar">
                    {{ getInitials(employee.name) }}
                </div>
                <div class="employee-info">
                    <h2 class="employee-name">{{ employee.name }}</h2>
                    <p class="employee-meta">
                        <span v-if="employee.employee_id">ID: {{ employee.employee_id }}</span>
                        <span v-if="employee.position">{{ employee.position }}</span>
                        <span v-if="employee.department">{{ employee.department }}</span>
                    </p>
                </div>
            </div>

            <form @submit.prevent="submitForm">
                <!-- Main Form Grid -->
                <div class="form-grid">
                    <!-- Basic Salary -->
                    <div class="card">
                        <h3 class="card-title">Salary Information</h3>

                        <div class="form-group">
                            <label for="basic_salary" class="form-label">Basic Salary <span class="required">*</span></label>
                            <div class="input-wrapper">
                                <span class="input-prefix">$</span>
                                <input
                                    id="basic_salary"
                                    v-model="form.basic_salary"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="form-input"
                                    placeholder="0.00"
                                />
                            </div>
                            <p v-if="form.errors.basic_salary" class="form-error">{{ form.errors.basic_salary }}</p>
                        </div>

                        <div class="form-group">
                            <label for="hourly_rate" class="form-label">Hourly Rate</label>
                            <div class="input-wrapper">
                                <span class="input-prefix">$</span>
                                <input
                                    id="hourly_rate"
                                    v-model="form.hourly_rate"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="form-input"
                                    placeholder="Auto-calculated if empty"
                                />
                            </div>
                            <p class="form-hint">Leave empty to auto-calculate from basic salary (176 hours/month)</p>
                        </div>
                    </div>

                    <!-- Bank Information -->
                    <div class="card">
                        <h3 class="card-title">Bank Information</h3>

                        <div class="form-group">
                            <label for="bank_name" class="form-label">Bank Name</label>
                            <input
                                id="bank_name"
                                v-model="form.bank_name"
                                type="text"
                                class="form-input"
                                placeholder="e.g., BCA, Mandiri, BRI"
                            />
                        </div>

                        <div class="form-group">
                            <label for="bank_account" class="form-label">Account Number</label>
                            <input
                                id="bank_account"
                                v-model="form.bank_account"
                                type="text"
                                class="form-input"
                                placeholder="e.g., 1234567890"
                            />
                        </div>

                        <div class="form-group">
                            <label for="bank_account_name" class="form-label">Account Name</label>
                            <input
                                id="bank_account_name"
                                v-model="form.bank_account_name"
                                type="text"
                                class="form-input"
                                placeholder="Must match bank account holder"
                            />
                        </div>
                    </div>

                    <!-- Allowances -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Allowances</h3>
                            <button type="button" @click="addAllowance" class="add-btn">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="12" y1="5" x2="12" y2="19"/>
                                    <line x1="5" y1="12" x2="19" y2="12"/>
                                </svg>
                                Add
                            </button>
                        </div>

                        <div v-if="form.allowances.length === 0" class="empty-message">
                            No allowances added. Click "Add" to add an allowance.
                        </div>

                        <div v-for="(allowance, index) in form.allowances" :key="'allowance-'+index" class="item-row">
                            <div class="item-inputs">
                                <input
                                    v-model="allowance.name"
                                    type="text"
                                    class="form-input"
                                    placeholder="Allowance name"
                                />
                                <div class="input-wrapper">
                                    <span class="input-prefix">$</span>
                                    <input
                                        v-model="allowance.amount"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        class="form-input"
                                        placeholder="0.00"
                                    />
                                </div>
                            </div>
                            <button type="button" @click="removeAllowance(index)" class="remove-btn">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="18" y1="6" x2="6" y2="18"/>
                                    <line x1="6" y1="6" x2="18" y2="18"/>
                                </svg>
                            </button>
                        </div>

                        <div v-if="form.allowances.length > 0" class="total-row">
                            <span class="total-label">Total Allowances</span>
                            <span class="total-value">+${{ formatNumber(totalAllowances) }}</span>
                        </div>
                    </div>

                    <!-- Deductions -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Deductions</h3>
                            <button type="button" @click="addDeduction" class="add-btn">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="12" y1="5" x2="12" y2="19"/>
                                    <line x1="5" y1="12" x2="19" y2="12"/>
                                </svg>
                                Add
                            </button>
                        </div>

                        <div v-if="form.deductions.length === 0" class="empty-message">
                            No deductions added. Click "Add" to add a deduction.
                        </div>

                        <div v-for="(deduction, index) in form.deductions" :key="'deduction-'+index" class="item-row">
                            <div class="item-inputs">
                                <input
                                    v-model="deduction.name"
                                    type="text"
                                    class="form-input"
                                    placeholder="Deduction name"
                                />
                                <div class="input-wrapper">
                                    <span class="input-prefix">$</span>
                                    <input
                                        v-model="deduction.amount"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        class="form-input"
                                        placeholder="0.00"
                                    />
                                </div>
                            </div>
                            <button type="button" @click="removeDeduction(index)" class="remove-btn">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="18" y1="6" x2="6" y2="18"/>
                                    <line x1="6" y1="6" x2="18" y2="18"/>
                                </svg>
                            </button>
                        </div>

                        <div v-if="form.deductions.length > 0" class="total-row">
                            <span class="total-label">Total Deductions</span>
                            <span class="total-value negative">-${{ formatNumber(totalDeductions) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="form-actions">
                    <Link :href="`/payroll/employee/${employee.id}`" class="cancel-btn">
                        Cancel
                    </Link>
                    <button type="submit" class="submit-btn" :disabled="form.processing">
                        <span v-if="form.processing" class="spinner"></span>
                        {{ form.processing ? 'Saving...' : 'Save Changes' }}
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { usePage, Link } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useToast } from '@/composables/useToast';

const { success, error } = useToast();

const page = usePage();

const employee = computed(() => page.props.employee);

// Use Inertia's useForm for automatic CSRF handling
const form = useForm({
    basic_salary: employee.value.basic_salary || 0,
    hourly_rate: employee.value.hourly_rate || null,
    bank_name: employee.value.bank_name || '',
    bank_account: employee.value.bank_account || '',
    bank_account_name: employee.value.bank_account_name || '',
    allowances: employee.value.allowances || [],
    deductions: employee.value.deductions || [],
});

const totalAllowances = computed(() => {
    return form.allowances.reduce((sum, item) => sum + (parseFloat(item.amount) || 0), 0);
});

const totalDeductions = computed(() => {
    return form.deductions.reduce((sum, item) => sum + (parseFloat(item.amount) || 0), 0);
});

const getInitials = (name) => {
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
};

const formatNumber = (num) => {
    return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(num || 0);
};

const addAllowance = () => {
    form.allowances.push({ name: '', amount: 0 });
};

const removeAllowance = (index) => {
    form.allowances.splice(index, 1);
};

const addDeduction = () => {
    form.deductions.push({ name: '', amount: 0 });
};

const removeDeduction = (index) => {
    form.deductions.splice(index, 1);
};

const submitForm = () => {
    form.put(`/payroll/employee/${employee.value.id}`, {
        onSuccess: () => {
            success('Salary updated successfully');
        },
        onError: () => {
            error('Failed to update salary');
        },
    });
};
</script>

<style scoped>
.salary-edit {
    animation: fadeIn 0.4s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Header */
.page-header {
    margin-bottom: 2rem;
}

.header-left {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.back-link {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: #6B7280;
    text-decoration: none;
    font-size: 0.875rem;
    transition: color 0.2s;
}

.back-link:hover {
    color: #2563EB;
}

.back-link svg {
    width: 16px;
    height: 16px;
}

.page-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1F2937;
    margin-bottom: 0.25rem;
}

.page-subtitle {
    font-size: 0.9rem;
    color: #6B7280;
}

/* Employee Summary */
.employee-summary {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.25rem;
    background: white;
    border-radius: 16px;
    margin-bottom: 2rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.employee-avatar {
    width: 56px;
    height: 56px;
    border-radius: 14px;
    background: linear-gradient(135deg, #2563EB 0%, #0EA5E9 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    font-weight: 600;
}

.employee-info {
    display: flex;
    flex-direction: column;
}

.employee-name {
    font-size: 1.25rem;
    font-weight: 600;
    color: #1F2937;
}

.employee-meta {
    display: flex;
    gap: 1rem;
    font-size: 0.875rem;
    color: #6B7280;
}

/* Form Grid */
.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.card {
    background: white;
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.card-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #1F2937;
    margin-bottom: 1.5rem;
}

.card-header .card-title {
    margin-bottom: 0;
}

/* Form Elements */
.form-group {
    margin-bottom: 1.25rem;
}

.form-label {
    display: block;
    font-size: 0.875rem;
    font-weight: 500;
    color: #374151;
    margin-bottom: 0.5rem;
}

.required {
    color: #EF4444;
}

.input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.input-prefix {
    position: absolute;
    left: 1rem;
    color: #6B7280;
    font-weight: 500;
}

.form-input {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 1px solid #E5E7EB;
    border-radius: 10px;
    font-size: 0.9rem;
    color: #1F2937;
    background: white;
    outline: none;
    transition: all 0.2s;
}

.input-wrapper .form-input {
    padding-left: 2rem;
}

.form-input:focus {
    border-color: #2563EB;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.form-input::placeholder {
    color: #9CA3AF;
}

.form-hint {
    font-size: 0.75rem;
    color: #6B7280;
    margin-top: 0.375rem;
}

.form-error {
    font-size: 0.75rem;
    color: #EF4444;
    margin-top: 0.375rem;
}

/* Add Button */
.add-btn {
    display: flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.5rem 0.75rem;
    background: rgba(37, 99, 235, 0.1);
    color: #2563EB;
    border: none;
    border-radius: 8px;
    font-size: 0.8rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}

.add-btn:hover {
    background: rgba(37, 99, 235, 0.2);
}

.add-btn svg {
    width: 14px;
    height: 14px;
}

/* Item Row */
.item-row {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.75rem;
}

.item-inputs {
    display: flex;
    gap: 0.75rem;
    flex: 1;
}

.item-inputs .form-input:first-child {
    flex: 1;
}

.item-inputs .input-wrapper {
    width: 120px;
}

.remove-btn {
    width: 36px;
    height: 36px;
    border: none;
    background: rgba(239, 68, 68, 0.1);
    color: #EF4444;
    border-radius: 8px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.remove-btn:hover {
    background: rgba(239, 68, 68, 0.2);
}

.remove-btn svg {
    width: 16px;
    height: 16px;
}

.empty-message {
    text-align: center;
    padding: 1.5rem;
    color: #6B7280;
    font-size: 0.875rem;
}

.total-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    margin-top: 1rem;
    background: #F3F4F6;
    border-radius: 10px;
}

.total-label {
    font-weight: 600;
    color: #4B5563;
}

.total-value {
    font-size: 1.1rem;
    font-weight: 700;
    color: #10B981;
}

.total-value.negative {
    color: #EF4444;
}

/* Form Actions */
.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
    padding-top: 1.5rem;
    border-top: 1px solid #E5E7EB;
}

.cancel-btn {
    padding: 0.75rem 1.5rem;
    background: white;
    color: #4B5563;
    border: 1px solid #E5E7EB;
    border-radius: 10px;
    font-size: 0.9rem;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.2s;
}

.cancel-btn:hover {
    background: #F9FAFB;
    border-color: #D1D5DB;
}

.submit-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    background: #2563EB;
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 0.9rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}

.submit-btn:hover:not(:disabled) {
    background: #1D4ED8;
}

.submit-btn:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

.spinner {
    width: 16px;
    height: 16px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Responsive */
@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr;
    }

    .item-inputs {
        flex-direction: column;
    }

    .item-inputs .input-wrapper {
        width: 100%;
    }

    .form-actions {
        flex-direction: column;
    }

    .cancel-btn,
    .submit-btn {
        width: 100%;
        justify-content: center;
    }
}
</style>
