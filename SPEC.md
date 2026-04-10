# Attendance App - Project Specification

## 1. Project Overview

**Project Name:** Attendance App
**Type:** Employee Attendance System (Mobile + Web Admin)
**Core Functionality:** GPS-based employee attendance tracking with clock-in/out, location validation, and admin dashboard

## 2. Technology Stack

### Backend
| Component | Technology | Version |
|-----------|------------|---------|
| Framework | Laravel | 12.x |
| Admin Panel | Vue 3 + Inertia | latest |
| PHP | PHP | 8.2+ |
| Database | PostgreSQL | 8.0+ |
| Authentication | Laravel Sanctum | 4.x |
| Frontend Build | Vite | 6.x |

### Mobile (Flutter)
| Component | Technology | Version |
|-----------|------------|---------|
| Framework | Flutter | 3.x |
| Language | Dart | 3.x |
| State Management | flutter_bloc | ^8.1.0 |
| Networking | dio | ^5.4.0 |
| Dependency Injection | get_it | ^7.6.0 |
| Routing | go_router | ^14.0.0 |
| Local Storage | shared_preferences | ^2.2.0 |
| Location | geolocator | ^12.0.0 |
| Image Picker | image_picker | ^1.0.0 |
| JSON Serialization | json_annotation | ^4.8.0 |
| Code Generation | build_runner, json_serializable, freezed | latest |

## 3. Architecture

### Backend (Laravel + Vue + Inertia)
```
backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/Api # API endpoints (Mobile)
│   │   ├── Controllers/Auth # Web Auth controllers
│   │   └── Requests        # Form validation
│   ├── Models              # Eloquent models
│   ├── Services            # Business logic
│   └── Providers           # Service providers
├── resources/
│   └── js/
│       ├── Pages/          # Inertia pages (Vue)
│       ├── Layouts/        # Vue layouts
│       └── Components/     # Vue components
├── database/
│   ├── migrations          # Database schemas
│   └── seeders             # Initial data
└── routes/
    ├── api.php             # Mobile API routes
    ├── web.php             # Web routes (Inertia)
    └── auth.php            # Authentication routes
```

### Mobile (Flutter)
```
mobile/
├── lib/
│   ├── core/               # Constants, theme, network, utils, widgets
│   ├── data/               # Models & API sources
│   ├── logic/              # BLoC/Cubit state management
│   ├── presentation/       # Screens & widgets
│   └── routes/             # App router
└── pubspec.yaml
```

## 4. Core Features

### Mobile App
- [ ] User Authentication (Login/Logout)
- [ ] GPS Location Tracking
- [ ] Clock In / Clock Out
- [ ] Attendance History
- [ ] Profile Management
- [ ] Offline Capability (cache)

### Admin Dashboard (Vue + Inertia)
- [ ] User Management (CRUD)
- [ ] Attendance Reports
- [ ] Location Configuration
- [ ] Daily Statistics Dashboard
- [ ] Monthly Reports Export
- [ ] Real-time Statistics

### API Features
- [ ] JWT Authentication via Sanctum
- [ ] Geo-fencing (radius validation)
- [ ] Attendance CRUD
- [ ] Photo upload with compression
- [ ] Attendance history with pagination

## 5. Database Schema (Planned)

### users
- id, name, email, password, role, photo, created_at, updated_at

### attendances
- id, user_id, latitude, longitude, photo, clock_in, clock_out, status, notes, created_at

### locations
- id, name, latitude, longitude, radius_meters, is_active, created_at

### shifts
- id, name, start_time, end_time, created_at

## 6. API Endpoints (Planned)

### Authentication
- POST /api/auth/login
- POST /api/auth/logout
- GET /api/auth/user

### Attendance
- POST /api/attendance/clock-in
- POST /api/attendance/clock-out
- GET /api/attendance/history
- GET /api/attendance/today

### Locations
- GET /api/locations
- GET /api/locations/validate

---

*Last Updated: 2026-03-13*
