<template>
    <AppLayout>
        <div class="dashboard-content">
        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon blue">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                </div>
                <div class="stat-info">
                    <span class="stat-label">Total Employees</span>
                    <span class="stat-value">{{ stats.total_employees }}</span>
                    <span class="stat-change positive">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
                            <polyline points="17 6 23 6 23 12"></polyline>
                        </svg>
                        +12%
                    </span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon green">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                </div>
                <div class="stat-info">
                    <span class="stat-label">Present Today</span>
                    <span class="stat-value">{{ stats.present_today }}</span>
                    <span class="stat-change positive">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="23 6 13.55 15.5 8.5 10.5 1 18"></polyline>
                            <polyline points="17 6 23 6 23 12"></polyline>
                        </svg>
                        +8%
                    </span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon yellow">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                    </svg>
                </div>
                <div class="stat-info">
                    <span class="stat-label">On Leave</span>
                    <span class="stat-value">{{ stats.on_leave }}</span>
                    <span class="stat-change neutral">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        0%
                    </span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon red">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="15" y1="9" x2="9" y2="15"></line>
                        <line x1="9" y1="9" x2="15" y2="15"></line>
                    </svg>
                </div>
                <div class="stat-info">
                    <span class="stat-label">Absent Today</span>
                    <span class="stat-value">{{ stats.absent_today }}</span>
                    <span class="stat-change negative">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="23 18 13.5 8.5 8.5 13.5 1 6"></polyline>
                            <polyline points="17 18 23 18 23 12"></polyline>
                        </svg>
                        -3%
                    </span>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="content-grid">
            <!-- Attendance Overview -->
            <div class="card attendance-card">
                <div class="card-header">
                    <h3 class="card-title">Attendance Overview</h3>
                    <div class="period-selector">
                        <button class="period-btn active">This Week</button>
                        <button class="period-btn">This Month</button>
                    </div>
                </div>

                <div class="chart-container">
                    <div class="chart">
                        <div class="chart-bars">
                            <div class="bar-group" v-for="(day, index) in weekData" :key="index">
                                <div class="bar present" :style="{ height: day.present + '%' }">
                                    <span class="bar-value">{{ day.present }}%</span>
                                </div>
                                <div class="bar leave" :style="{ height: day.leave + '%' }"></div>
                                <div class="bar absent" :style="{ height: day.absent + '%' }"></div>
                            </div>
                        </div>
                        <div class="chart-labels">
                            <span v-for="(day, index) in weekData" :key="index">{{ day.label }}</span>
                        </div>
                    </div>

                    <div class="chart-legend">
                        <div class="legend-item">
                            <span class="legend-dot present"></span>
                            <span>Present</span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-dot leave"></span>
                            <span>On Leave</span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-dot absent"></span>
                            <span>Absent</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="card activity-card">
                <div class="card-header">
                    <h3 class="card-title">Recent Activity</h3>
                    <button class="view-all-btn">View All</button>
                </div>

                <div class="activity-list">
                    <div class="activity-item" v-for="(activity, index) in recentActivities" :key="index">
                        <div class="activity-avatar" :class="activity.type">
                            <span>{{ activity.initials }}</span>
                        </div>
                        <div class="activity-info">
                            <span class="activity-message">{{ activity.message }}</span>
                        </div>
                        <div class="activity-time">
                            <span>{{ activity.time }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upcoming Events -->
            <div class="card events-card">
                <div class="card-header">
                    <h3 class="card-title">Upcoming Events</h3>
                    <button class="view-all-btn">View All</button>
                </div>

                <div class="events-list">
                    <div class="event-item" v-for="(event, index) in upcomingEvents" :key="index">
                        <div class="event-indicator" :class="event.type"></div>
                        <div class="event-info">
                            <span class="event-name">{{ event.name }}</span>
                            <span class="event-time">{{ event.date }}, {{ event.time }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>  
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { usePage } from '@inertiajs/vue3';
import type { Stats } from '@/types';

interface DashboardStats extends Stats {
    total_employees?: number;
    present_today?: number;
    on_leave?: number;
    absent_today?: number;
}

interface WeekDataItem {
    label: string;
    present: number;
    absent: number;
    leave: number;
}

interface Activity {
    id: number;
    type: string;
    message: string;
    initials: string;
    time: string;
}

const page = usePage();

// Get data from controller
const stats = (page.props.stats as DashboardStats) || {
    total_employees: 0,
    present_today: 0,
    on_leave: 0,
    absent_today: 0
};

const weekData = (page.props.week_data as WeekDataItem[]) || [];
const recentActivities = (page.props.recent_activities as Activity[]) || [];
const upcomingEvents = (page.props.upcoming_schedules as any[]) || [];
</script>

<style scoped>
.dashboard-content {
    animation: fadeIn 0.4s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    border-radius: 16px;
    padding: 1.5rem;
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    animation: slideUp 0.5s ease-out backwards;
}

.stat-card:nth-child(1) { animation-delay: 0.1s; }
.stat-card:nth-child(2) { animation-delay: 0.2s; }
.stat-card:nth-child(3) { animation-delay: 0.3s; }
.stat-card:nth-child(4) { animation-delay: 0.4s; }

@keyframes slideUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.stat-icon svg {
    width: 24px;
    height: 24px;
}

.stat-icon.blue {
    background: rgba(37, 99, 235, 0.1);
    color: #2563EB;
}

.stat-icon.green {
    background: rgba(16, 185, 129, 0.1);
    color: #10B981;
}

.stat-icon.yellow {
    background: rgba(245, 158, 11, 0.1);
    color: #F59E0B;
}

.stat-icon.red {
    background: rgba(239, 68, 68, 0.1);
    color: #EF4444;
}

.stat-info {
    display: flex;
    flex-direction: column;
}

.stat-label {
    font-size: 0.85rem;
    color: #6B7280;
    margin-bottom: 0.25rem;
}

.stat-value {
    font-size: 1.75rem;
    font-weight: 700;
    color: #1F2937;
    letter-spacing: -0.02em;
}

.stat-change {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    font-size: 0.8rem;
    font-weight: 600;
    margin-top: 0.25rem;
}

.stat-change svg {
    width: 14px;
    height: 14px;
}

.stat-change.positive {
    color: #10B981;
}

.stat-change.negative {
    color: #EF4444;
}

.stat-change.neutral {
    color: #6B7280;
}

/* Content Grid */
.content-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 1.5rem;
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
}

.view-all-btn {
    font-size: 0.85rem;
    color: #2563EB;
    background: none;
    border: none;
    cursor: pointer;
    font-weight: 500;
}

.view-all-btn:hover {
    text-decoration: underline;
}

/* Period Selector */
.period-selector {
    display: flex;
    gap: 0.5rem;
    background: #F3F4F6;
    padding: 0.25rem;
    border-radius: 8px;
}

.period-btn {
    padding: 0.5rem 1rem;
    border: none;
    background: transparent;
    border-radius: 6px;
    font-size: 0.85rem;
    font-weight: 500;
    color: #6B7280;
    cursor: pointer;
    transition: all 0.2s;
}

.period-btn.active {
    background: white;
    color: #1F2937;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.period-btn:hover:not(.active) {
    color: #1F2937;
}

/* Chart */
.chart-container {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.chart {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.chart-bars {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    height: 200px;
    padding: 0 0.5rem;
}

.bar-group {
    display: flex;
    gap: 4px;
    align-items: flex-end;
    height: 100%;
    width: 100%;
    max-width: 40px;
}

.bar {
    width: 100%;
    border-radius: 4px 4px 0 0;
    position: relative;
    min-height: 4px;
    transition: all 0.3s ease;
}

.bar.present {
    background: linear-gradient(180deg, #2563EB 0%, #3B82F6 100%);
}

.bar.leave {
    background: linear-gradient(180deg, #F59E0B 0%, #FBBF24 100%);
}

.bar.absent {
    background: linear-gradient(180deg, #EF4444 0%, #F87171 100%);
}

.bar-value {
    position: absolute;
    top: -24px;
    left: 50%;
    transform: translateX(-50%);
    font-size: 0.7rem;
    font-weight: 600;
    color: #6B7280;
    opacity: 0;
    transition: opacity 0.2s;
}

.bar-group:hover .bar-value {
    opacity: 1;
}

.chart-labels {
    display: flex;
    justify-content: space-between;
    padding: 0 0.5rem;
}

.chart-labels span {
    font-size: 0.8rem;
    color: #6B7280;
    width: 100%;
    max-width: 40px;
    text-align: center;
}

.chart-legend {
    display: flex;
    justify-content: center;
    gap: 1.5rem;
    padding-top: 1rem;
    border-top: 1px solid #E5E7EB;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.8rem;
    color: #6B7280;
}

.legend-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
}

.legend-dot.present {
    background: #2563EB;
}

.legend-dot.leave {
    background: #F59E0B;
}

.legend-dot.absent {
    background: #EF4444;
}

/* Activity Card */
.activity-card {
    grid-row: span 2;
}

.activity-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.activity-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem;
    border-radius: 10px;
    transition: background 0.2s;
}

.activity-item:hover {
    background: #F9FAFB;
}

.activity-avatar {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    font-weight: 600;
    color: white;
}

.activity-avatar.in {
    background: linear-gradient(135deg, #10B981 0%, #34D399 100%);
}

.activity-avatar.out {
    background: linear-gradient(135deg, #6366F1 0%, #818CF8 100%);
}

.activity-info {
    flex: 1;
    display: flex;
    flex-direction: column;
}

.activity-name {
    font-size: 0.9rem;
    font-weight: 600;
    color: #1F2937;
}

.activity-action {
    font-size: 0.8rem;
    color: #6B7280;
}

.activity-time {
    font-size: 0.75rem;
    color: #9CA3AF;
}

/* Events Card */
.events-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.event-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem;
    border-radius: 10px;
    transition: background 0.2s;
}

.event-item:hover {
    background: #F9FAFB;
}

.event-indicator {
    width: 4px;
    height: 40px;
    border-radius: 2px;
}

.event-indicator.meeting {
    background: #2563EB;
}

.event-indicator.review {
    background: #8B5CF6;
}

.event-indicator.holiday {
    background: #EF4444;
}

.event-info {
    display: flex;
    flex-direction: column;
}

.event-name {
    font-size: 0.9rem;
    font-weight: 600;
    color: #1F2937;
}

.event-time {
    font-size: 0.8rem;
    color: #6B7280;
}

/* Responsive */
@media (max-width: 1200px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .content-grid {
        grid-template-columns: 1fr;
    }

    .activity-card {
        grid-row: auto;
    }
}

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
}
</style>
