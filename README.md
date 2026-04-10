# Attendance App

Employee attendance tracking system with GPS-based clock in/out, Laravel 12 API (v1), Vue 3 + Inertia admin dashboard, and Flutter mobile app.

## Tech Stack

### Backend
- **Laravel 12** — PHP Framework
- **Vue 3 + Inertia.js** — Admin Dashboard
- **Laravel Sanctum** — API Authentication
- **PostgreSQL** — Database
- **PHP 8.2+**

### Mobile
- **Flutter 3.x** — Cross-platform mobile app
- **flutter_bloc** — State management
- **Dio** — HTTP client
- **go_router** — Navigation
- **geolocator** — GPS location
- **Hive** — Offline local cache
- **workmanager** — Background sync

## Project Structure

```
attendance_app/
├── backend/              # Laravel 12 + Vue 3 + Inertia
│   ├── app/
│   │   ├── Http/Controllers/Api/   # API v1 controllers
│   │   ├── Http/Controllers/        # Web controllers
│   │   ├── Models/
│   │   └── Services/                # Business logic (GeoService, etc.)
│   ├── database/migrations/
│   ├── resources/js/                 # Vue 3 frontend
│   │   ├── Pages/                   # Inertia pages
│   │   ├── Components/              # Vue components
│   │   └── composables/
│   └── routes/
│       ├── api.php                  # /api/v1/* routes
│       └── web.php                  # Web routes
├── mobile/               # Flutter Application
│   └── lib/
│       ├── core/                    # DI, network, theme
│       ├── data/                    # Models, datasources, repositories
│       ├── logic/                   # BLoC/Cubit state management
│       ├── presentation/             # Screens & widgets
│       └── routes/                  # App router
├── docs/superpowers/    # Implementation plans
├── SPEC.md             # Project specification
├── structure.md        # Architecture guidance
└── improvement.md       # Priority issue tracker
```

## Setup

### Prerequisites

- PHP 8.2+ with Composer
- Node.js 18+ with npm
- Flutter 3.x
- PostgreSQL 8.0+
- Git

### Backend

```bash
cd backend

composer install
cp .env.example .env
php artisan key:generate

# Configure database in .env:
# DB_CONNECTION=pgsql
# DB_HOST=127.0.0.1
# DB_PORT=5432
# DB_DATABASE=attendance_app

php artisan migrate
php artisan db:seed  # Optional: seed demo data

npm install
npm run dev  # Vite dev server

php artisan serve  # API on :8000
```

### Mobile

```bash
cd mobile

flutter pub get
flutter run
```

## API (v1)

All endpoints prefixed with `/api/v1/`. Authentication via `Authorization: Bearer <token>` header.

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | /api/v1/auth/login | Login |
| POST | /api/v1/auth/register | Register |
| POST | /api/v1/auth/logout | Logout |
| GET | /api/v1/auth/me | Current user |
| POST | /api/v1/auth/refresh | Refresh token |
| POST | /api/v1/attendance/clock-in | Clock in with GPS + selfie |
| POST | /api/v1/attendance/clock-out | Clock out with GPS |
| POST | /api/v1/attendance/sync | Sync offline queue |
| GET | /api/v1/attendance/today | Today's attendance |
| GET | /api/v1/attendance/history | Paginated history |
| GET | /api/v1/shifts | List shifts |
| GET | /api/v1/locations | List locations |
| GET | /api/v1/notifications | Notifications |

## Features

### Implemented
- GPS-based attendance with geo-fencing (hard radius block)
- Clock-in/out with selfie photo capture
- Offline-first mobile with Hive cache + background sync
- Time-based greeting on home screen
- Device fingerprinting on token creation
- Token auto-refresh on 401
- Hardened geo-fencing with `GeoService`
- User name from API response (not email split)
- API versioning (`/api/v1/`)
- Password hashing on registration
- Clock-in preserves existing clock-out data
- GPS accuracy warning (>50m threshold)

### Admin Dashboard (Vue + Inertia)
- Attendance management with filters and pagination
- Employee management
- Shift & location configuration
- Payroll overview
- Report exports

## Documentation

| File | Purpose |
|-------|---------|
| `SPEC.md` | Full project specification |
| `structure.md` | Architecture guidance & pro tips |
| `improvement.md` | Prioritized issue tracker |
| `docs/superpowers/plans/` | Implementation plans |

## Architecture Standards

See `SPEC.md` for full tech stack, data models, and API contract.

---

*Last updated: 2026-04-10*
