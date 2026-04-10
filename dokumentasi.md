# Dokumentasi Framework & Library

Project ini menggunakan arsitektur **Laravel + Vue 3 + Inertia.js** untuk admin dashboard.

---

## Backend (Laravel 12)

### Laravel Framework
- **Version**: 12.0
- **PHP**: ^8.2
- **Website**: https://laravel.com

#### Package Penting:
| Package | Versi | Deskripsi |
|---------|-------|-----------|
| laravel/framework | ^12.0 | Core framework |
| laravel/sanctum | ^4.0 | Authentication untuk API |
| inertiajs/inertia-laravel | * | Server-side rendering untuk Inertia |
| tightenco/ziggy | * | Route sharing ke frontend |

---

## Frontend (Vue 3 + Inertia)

### Core Dependencies

#### Vue.js 3
- **Version**: ^3.5.13
- **Website**: https://vuejs.org
- **Dokumentasi**: https://vuejs.org/guide/

```javascript
// Contoh Component
import { ref, computed } from 'vue';

const count = ref(0);
const doubled = computed(() => count.value * 2);
```

#### Inertia.js
- **Version**: ^2.0.3 (@inertiajs/vue3)
- **Website**: https://inertiajs.com
- **Dokumentasi**: https://inertiajs.com/responses

```javascript
// Routing dengan Inertia
import { router } from '@inertiajs/vue3';

router.get('/employees', { search: 'john' });
router.post('/employees', formData);
router.put('/employees/1', formData);
router.delete('/employees/1');
```

#### Ziggy JS
- **Version**: ^2.6.2
- **Website**: https://github.com/tighten/ziggy
- **Fungsi**: Mengakses route Laravel dari JavaScript

```javascript
// Penggunaan
window.route('employees.index');        // /employees
window.route('employees.show', { id: 1 }); // /employees/1
```

#### VueUse Core
- **Version**: ^14.2.1
- **Website**: https://vueuse.org
- **Dokumentasi**: https://vueuse.org/functions.html

```javascript
// Contoh penggunaan
import { useLocalStorage, useNow } from '@vueuse/core';

const theme = useLocalStorage('theme', 'dark');
const now = useNow();
```

---

## Styling & Build

### Tailwind CSS 4
- **Version**: ^4.0.0
- **Website**: https://tailwindcss.com
- **Dokumentasi**: https://tailwindcss.com/docs

#### Plugin:
- @tailwindcss/vite: ^4.0.0

```html
<!-- Contoh Tailwind -->
<div class="flex items-center justify-between p-4 bg-white rounded-lg shadow">
    <h1 class="text-xl font-bold text-gray-800">Title</h1>
</div>
```

### Vite
- **Version**: ^6.0.0
- **Website**: https://vitejs.dev
- **Dokumentasi**: https://vitejs.dev/guide/

```bash
# Commands
npm run dev     # Development server
npm run build   # Production build
```

### Vue Plugin Vite
- **Version**: ^5.2.1
- **Fungsi**: Vue 3 support untuk Vite

---

## TypeScript

### TypeScript
- **Version**: ^5.9.3
- **Website**: https://www.typescriptlang.org
- **Dokumentasi**: https://www.typescriptlang.org/docs/

```typescript
// Interface contoh
interface User {
    id: number;
    name: string;
    email: string;
    role?: string;
}

const user: User = {
    id: 1,
    name: 'John',
    email: 'john@example.com'
};
```

### Vue TSC
- **Version**: ^3.2.5
- **Fungsi**: Type checking untuk Vue component

---

## HTTP Client

### Axios
- **Version**: ^1.11.0
- **Website**: https://axios-http.com
- **Dokumentasi**: https://axios-http.com/docs/intro

```javascript
import axios from 'axios';

axios.get('/api/employees')
    .then(response => console.log(response.data))
    .catch(error => console.error(error));
```

---

## Development Tools

### Type Definitions
- **@types/node**: ^25.5.0
- **@vitejs/plugin-vue**: ^5.2.1

### Other Tools
- **concurrently**: ^9.0.1 - Run multiple commands
- **laravel-vite-plugin**: ^1.0.0 - Laravel Vite integration

---

## Struktur Project

```
/backend
├── /app              # Laravel application code
│   ├── /Http/Controllers
│   ├── /Models
│   └── /Services
├── /resources
│   └── /js           # Vue 3 frontend
│       ├── /Pages        # Inertia Pages
│       ├── /Layouts      # Vue Layouts
│       ├── /Components   # Vue Components
│       ├── /composables   # Vue Composables
│       └── /types        # TypeScript types
├── /routes           # Laravel routes
└── /vendor           # Composer dependencies
```

---

## Command Reference

```bash
# Install dependencies
composer install
npm install

# Development
npm run dev              # Start Vite dev server
php artisan serve        # Start Laravel server

# Production
npm run build            # Build assets

# Laravel commands
php artisan migrate      # Run migrations
php artisan db:seed      # Seed database
php artisan make:model   # Create model
php artisan make:controller # Create controller
```

---

## Authentication

Project menggunakan **Laravel Sanctum** untuk API authentication.

- Login menggunakan session-based auth (web)
- API tokens untuk mobile app

---

## Catatan Penting

1. **Route Parameters**: Saat menggunakan route di Inertia, gunakan object:
   ```javascript
   // Salah
   router.put('/employees/1', data);

   // Benar
   router.put(window.route('employees.update', { id: 1 }), data);
   ```

2. **Tailwind 4**: Menggunakan CSS-first configuration, tidak ada tailwind.config.js

3. **TypeScript**:尽量使用 TypeScript interfaces untuk type safety

---

# Feature Project - Aplikasi Absensi

## Overview

Project ini adalah **Sistem Manajemen Kehadiran Karyawan** (Employee Attendance Management System) yang dibangun dengan:
- **Backend**: Laravel 12 (API + Admin Dashboard)
- **Frontend**: Vue 3 + Inertia.js + TypeScript
- **Mobile**: Flutter (rencana)
- **Styling**: Tailwind CSS 4

---

## Fitur Saat Ini (Sudah Diimplementasikan)

### 1. Authentication & Authorization
- [x] Login/Logout
- [x] Reset Password
- [x] Role-based Access Control (Admin, Manager, Employee)
- [x] Session Management

### 2. Dashboard
- [x] Statistik Kehadiran (Hadir, Terlambat, Alpha, Izin)
- [x] Data Mingguan (Chart)
- [x] Aktivitas Terbaru
- [x] Jadwal Mendatang

### 3. Manajemen Karyawan (Employee)
- [x] Daftar Karyawan dengan Pagination
- [x] Tambah Karyawan Baru
- [x] Edit Informasi Karyawan
- [x] Hapus Karyawan
- [x] Toggle Status Aktif/Nonaktif
- [x] Filter: Department, Status, Shift
- [x] Search Karyawan
- [x] Sort Kolom
- [x] Auto-generate Employee ID berdasarkan Position
- [x] View Detail Karyawan

### 4. Manajemen Absensi (Attendance)
- [x] Daftar Absensi dengan Pagination
- [x] Filter: Tanggal, Status, Karyawan, Shift, Lokasi
- [x] Search Absensi
- [x] Edit Absensi
- [x] Hapus Absensi
- [x] Statistik Kehadiran
- [x] Chart Kehadiran

### 5. Manajemen Payroll (Gaji)
- [x] Daftar Payroll Karyawan
- [x] Detail Gaji Karyawan
- [x] Edit Gaji Pokok
- [x] Konfigurasi Tunjangan & Potongan
- [x] Perhitungan Gaji Otomatis

### 6. Manajemen Jadwal (Schedule)
- [x] Daftar Jadwal
- [x] Tambah Jadwal Tunggal
- [x] Tambah Jadwal Massal (Bulk)
- [x] Edit Jadwal
- [x] Hapus Jadwal
- [x] Toggle Status Aktif/Nonaktif
- [x] Filter: Tanggal, Karyawan

### 7. Laporan (Reports)
- [x] Laporan Kehadiran
- [x] Laporan Payroll
- [x] Export Data

### 8. Pengaturan (Settings)
- [x] Informasi Perusahaan
- [x] Konfigurasi Jam Kerja
- [x] Pengaturan Absensi
- [x] Pengaturan Notifikasi

### 9. Profil User
- [x] Lihat Profil
- [x] Edit Profil
- [x] Upload Avatar
- [x] Ganti Password
- [x] Informasi Bank

---

## API untuk Mobile App

### Endpoint yang Tersedia:
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| POST | `/api/auth/register` | Registrasi user baru |
| POST | `/api/auth/login` | Login |
| POST | `/api/auth/logout` | Logout |
| GET | `/api/auth/me` | Get current user |
| PUT | `/api/auth/profile` | Update profile |
| GET | `/api/attendance/today` | Absensi hari ini |
| POST | `/api/attendance/clock-in` | Clock In |
| POST | `/api/attendance/clock-out` | Clock Out |
| GET | `/api/attendance/history` | Riwayat absensi |
| GET | `/api/overtime` | Data lembur |
| POST | `/api/overtime` | Ajukan lembur |
| GET | `/api/schedule/today` | Jadwal hari ini |
| GET | `/api/shifts` | Daftar shift |
| GET | `/api/locations` | Daftar lokasi |
| GET | `/api/notifications` | Notifikasi |

---

## Pengembangan yang Sedang Berlangsung

### 1. Session Timeout (30 menit)
- [x] Backend: Konfigurasi session lifetime
- [ ] Frontend: Auto logout indicator
- [ ] Frontend: Warning sebelum logout

### 2. Upload Avatar
- [x] Backend: Compression & save image
- [ ] Frontend: Cache busting
- [ ] Debug file storage

### 3. Pagination Semua Table
- [x] Employee
- [x] Attendance
- [x] Schedule
- [ ] Payroll
- [ ] Reports

### 4. TypeScript Migration
- [x] Utama files (Login, Dashboard, Employee, Attendance)
- [x] Type definitions
- [ ] Remaining pages (Schedule, Payroll, Reports, Settings)

---

## Rencana Pengembangan Mendatang

### Priority 1: Mobile App (Flutter)

**Fitur Penting:**
- [ ] Clock In/Clock Out dengan GPS
- [ ] Geo-fencing Validation
- [ ] Riwayat Absensi
- [ ] Pengajuan Izin/Lembur
- [ ] Notifikasi Push
- [ ] Profile Management

**Tech Stack:**
- Flutter 3.x
- Dio (HTTP Client)
- GetX/Bloc (State Management)
- Freezed (Data Models)
- Geolocator (GPS)
- Flutter Local Notifications

### Priority 2: Fitur Admin Dashboard

- [ ] Export ke PDF/Excel
- [ ] Dashboard Interaktif dengan Chart.js
- [ ] Real-time Notifications
- [ ] Activity Log
- [ ] Audit Trail
- [ ] Backup & Restore Database

### Priority 3: Fitur Lanjutan

- [ ] Approval Workflow (Izin, Lembur, Sakit)
- [ ] Calendar View untuk Jadwal
- [ ] Employee Self-Service Portal
- [ ] Announcement/News Board
- [ ] Asset Management
- [ ] Leave Management (Cuti)
- [ ] Recruitment Module (Rekrutmen)

### Priority 4: Integrasi

- [ ] Email Notifications
- [ ] SMS Notifications (Twilio)
- [ ] WhatsApp Integration
- [ ] Slack/Teams Integration
- [ ] Biometric Integration (Fingerprint)

### Priority 5: Advanced Features

- [ ] AI-based Attendance Analysis
- [ ] Predict迟到 (Late Arrival Prediction)
- [ ] Auto Scheduling
- [ ] Performance Metrics
- [ ] Custom Reports Builder
- [ ] Multi-branch Support

---

## Struktur Database

### Models:
- **User** - Data karyawan
- **Attendance** - Riwayat absensi
- **Schedule** - Jadwal kerja
- **Shift** - Definisi shift
- **Location** - Lokasi absensi (GPS)
- **Overtime** - Data lembur
- **Notification** - Notifikasi

### Relationships:
```
User (1) ─── (N) Attendance
User (1) ─── (N) Schedule
User (1) ─── (N) Overtime
Shift (1) ─── (N) Schedule
Location (1) ─── (N) Attendance
```

---

## Roadmap

```
Q1 2026:
├── ✅ Admin Dashboard (Basic)
├── ✅ Employee Management
├── ✅ Attendance Management
├── ✅ Payroll Management
├── ✅ Schedule Management
├── ✅ Reports
├── 🔄 Mobile App (Flutter) - Clock In/Out
└── 🔄 Session Timeout

Q2 2026:
├── Mobile App - Full Features
├── Approval Workflow
├── Leave Management
├── Push Notifications
└── Email/SMS Integration

Q3 2026:
├── Advanced Analytics
├── AI Features
├── Multi-branch Support
└── Custom Reports

Q4 2026:
├── Performance Review
├── Training Module
└── Full System Integration
```

---

## Kontribusi

1. Fork repository
2. Create feature branch (`git checkout -b feature/feature-name`)
3. Commit changes (`git commit -m 'Add feature'`)
4. Push to branch (`git push origin feature/feature-name`)
5. Create Pull Request

---

## Lisensi

MIT License
