# Production Architecture Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan.

**Goal:** Implement the production-grade architectural improvements from structure.md — API versioning, GeoService, offline-first mobile, repository pattern, WebSocket notifications, and device fingerprinting.

**Architecture Decisions:**
- API versioning via route group prefix (`/api/v1/`) — routes reorganized, no new controller files needed
- `App\Services\GeoService` extracts the Haversine formula from `AttendanceController` — testable in isolation
- Mobile: `Hive` for local attendance cache (lightweight, no native deps) + `workmanager` for background sync
- Repository pattern: `AttendanceRepository` sits between Cubit and DataSource, handles cache routing
- WebSocket: Laravel Reverb for real-time admin alerts (Pusher-compatible API)
- Device fingerprinting: `device_info_plus` on Flutter, stored on Sanctum token creation

**Tech Stack:** Laravel 12, Flutter 3, Hive, workmanager, device_info_plus, Laravel Reverb

---

## Task Map

| Task | File(s) | Change |
|------|----------|--------|
| 1 | `backend/routes/api.php` | Prefix all routes with `/api/v1/` |
| 2 | `backend/app/Services/GeoService.php` | Extract Haversine to service class |
| 3 | `backend/app/Http/Controllers/Api/AttendanceController.php` | Use `GeoService` instead of inline method |
| 4 | `backend/app/Models/Attendance.php` | Add `work_duration` to `$appends` |
| 5 | `backend/app/Http/Controllers/Api/AuthController.php` | Store `device_id` on token creation |
| 6 | `backend/database/migrations/...` | Add `device_id` column to `personal_access_tokens` |
| 7 | `mobile/pubspec.yaml` | Add `hive`, `hive_flutter`, `workmanager`, `connectivity_plus`, `device_info_plus` |
| 8 | `mobile/lib/data/models/attendance_cache_model.dart` | Hive model for offline attendance (`id` is nullable) |
| 9 | `mobile/lib/data/sources/attendance_cache_datasource.dart` | Hive CRUD for attendance cache |
| 10 | `mobile/lib/data/repositories/attendance_repository.dart` | Cache-first reads, writes online via `/sync` endpoint |
| 11 | `mobile/lib/core/di/injection.dart` | Register new dependencies + Hive init |
| 12 | `mobile/lib/logic/attendance/attendance_cubit.dart` | Use repository instead of datasource directly |
| 13 | `mobile/lib/logic/attendance/attendance_sync_cubit.dart` | Background sync via repository |
| 14 | `mobile/lib/main.dart` | Initialize Hive, register workmanager task |
| 15 | `backend/app/Models/User.php` | Add `hasRegisteredDevice()` method |
| 16 | `backend/app/Http/Controllers/Api/AttendanceController.php` + `api.php` | New `POST /sync` idempotent offline-sync endpoint |

---

## Current Baseline (read before starting)

- `backend/routes/api.php` — routes at `/api/auth/...`, `/api/attendance/...`, no version prefix
- `backend/app/Http/Controllers/Api/AttendanceController.php` — `calculateDistance()` private method at line 220+
- `backend/app/Models/Attendance.php` — `$appends` array does NOT include `work_duration`
- `backend/app/Http/Controllers/Api/AuthController.php` — `login()` creates token at line 73
- `backend/app/Models/User.php` — no device tracking
- `mobile/pubspec.yaml` — no Hive, workmanager, connectivity_plus, or device_info_plus
- `mobile/lib/core/di/injection.dart` — existing DI setup
- `mobile/lib/logic/attendance/attendance_cubit.dart` — uses `AttendanceRemoteDataSource` directly

---

### Task 1: Prefix all API routes with /api/v1/

**Files:** `backend/routes/api.php`

- [ ] **Step 1: Read current api.php**

Run: `cat backend/routes/api.php`

- [ ] **Step 2: Wrap all routes in a versioned route group**

Find the opening `<?php` and namespace imports, then the line:
```php
use Illuminate\Support\Facades\Route;
```

After that line, add the versioned group:
```php
Route::prefix('v1')->group(function () {
```

Wrap EVERYTHING below it (all route definitions) inside this closure.

At the VERY END of the file, add the closing `});` to close the `Route::prefix('v1')->group(function () {`.

The final structure should be:
```php
<?php

use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AuthController;
// ... all other imports ...

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // === ALL EXISTING ROUTES GO HERE ===

    // Public routes
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    // ... etc ...

    Route::middleware('auth:sanctum')->group(function () {
        // ... etc ...
    });

}); // end v1
```

- [ ] **Step 3: Update mobile ApiConfig basePath to match**

After completing Task 1, the mobile app will 404 on every API call. Fix this immediately:

In `mobile/lib/core/network/api_client.dart`, add a `basePath` constant to `ApiConfig`:

Find `abstract class ApiConfig {` and add:
```dart
  /// API version prefix — must match the backend route group prefix
  static const String basePath = '/api/v1';
```

Then in `AttendanceRemoteDataSource` and `AuthRemoteDataSource` (and any other datasource), prepend `ApiConfig.basePath` to every URL path. For example:

Find: `'/api/auth/login'`
Replace: `'${ApiConfig.basePath}/auth/login'`

Repeat for all URL strings in `auth_remote_datasource.dart` and `attendance_remote_datasource.dart`.

Also add `import '../../core/network/api_client.dart';` to any datasource file that uses `ApiConfig.basePath`.

- [ ] **Step 4: Verify route count**

Run: `grep -c "Route::" backend/routes/api.php`
Expected: same count as before (no routes removed)

- [ ] **Step 5: Commit**

---

### Task 2: Create GeoService

**Files:** Create: `backend/app/Services/GeoService.php`

- [ ] **Step 1: Create directory and file**

```bash
mkdir -p backend/app/Services
```

- [ ] **Step 2: Write GeoService**

Create `backend/app/Services/GeoService.php`:
```php
<?php

namespace App\Services;

use App\Models\Location;

class GeoService
{
    /**
     * Calculate distance between two coordinates using Haversine formula.
     * Returns distance in meters.
     */
    public function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // meters

        $lat1Rad = deg2rad($lat1);
        $lat2Rad = deg2rad($lat2);
        $deltaLat = deg2rad($lat2 - $lat1);
        $deltaLon = deg2rad($lon2 - $lon1);

        $a = sin($deltaLat / 2) * sin($deltaLat / 2) +
            cos($lat1Rad) * cos($lat2Rad) *
            sin($deltaLon / 2) * sin($deltaLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Check if a coordinate is within a location's radius.
     */
    public function isWithinRadius(float $lat, float $lon, Location $location): bool
    {
        if (!$location->latitude || !$location->longitude) {
            return true; // No coordinates configured — allow
        }

        $distance = $this->calculateDistance(
            $lat,
            $lon,
            $location->latitude,
            $location->longitude
        );

        return $distance <= $location->getEffectiveRadius();
    }
}
```

- [ ] **Step 3: Commit**

Run: `git add backend/app/Services/GeoService.php` (or save file directly)
Expected: File created at `backend/app/Services/GeoService.php`

---

### Task 3: Update AttendanceController to use GeoService

**Files:** `backend/app/Http/Controllers/Api/AttendanceController.php`

- [ ] **Step 1: Read top of file to find imports**

Run: `sed -n '1,15p' backend/app/Http/Controllers/Api/AttendanceController.php`

- [ ] **Step 2: Add GeoService import**

Find: `use Illuminate\Support\Facades\Validator;`

Add after it:
```php
use App\Services\GeoService;
```

- [ ] **Step 3: Inject GeoService into controller**

Find:
```php
class AttendanceController extends Controller
{
```

Replace with:
```php
class AttendanceController extends Controller
{
    public function __construct(
        private readonly GeoService $geoService,
    ) {}
```

- [ ] **Step 4: Replace inline calculateDistance calls**

Find in `clockIn()`: `$distance = $this->calculateDistance(`

Replace all occurrences (should be 2 — in clockIn and clockOut) with:
```php
$distance = $this->geoService->calculateDistance(
```

Also find in `clockOut()`: `$distance = $this->calculateDistance(`

Replace same way.

- [ ] **Step 5: Delete the private calculateDistance method**

Find the method:
```php
    /**
     * Calculate distance between two coordinates in meters.
     */
    private function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // meters
        // ... full method ...
    }
```

Delete the entire method.

- [ ] **Step 6: Verify**

Run: `grep -n "calculateDistance\|GeoService" backend/app/Http/Controllers/Api/AttendanceController.php`
Expected: 2 occurrences of `calculateDistance` (in `$distance = $this->geoService->calculateDistance`), 1 `GeoService` import, 1 `__construct`

- [ ] **Step 7: Commit**

---

### Task 4: Expose work_duration in Attendance model

**Files:** `backend/app/Models/Attendance.php`

- [ ] **Step 1: Read current model**

Run: `cat backend/app/Models/Attendance.php`

- [ ] **Step 2: Add $appends array**

Find the line `protected $casts = [` (or if `$casts` is not present, find the end of the `fillable` block).

Add after `$casts`:
```php
    protected $appends = ['work_duration'];
```

The model should look like:
```php
    protected $casts = [
        'date' => 'date',
        'clock_in_time' => 'datetime:H:i:s',
        // ...
    ];

    protected $appends = ['work_duration'];
```

- [ ] **Step 3: Verify**

Run: `grep -n "appends\|work_duration" backend/app/Models/Attendance.php`
Expected: `$appends = ['work_duration'];` present, `getWorkDurationAttribute` already exists (confirmed in baseline).

- [ ] **Step 4: Commit**

---

### Task 5: Store device_id on token creation

**Files:** `backend/app/Http/Controllers/Api/AuthController.php`

- [ ] **Step 1: Read login method**

Run: `sed -n '50,90p' backend/app/Http/Controllers/Api/AuthController.php`

- [ ] **Step 2: Add device_id to token creation**

Find the `login()` method's token creation block (around line 73):
```php
        $token = $user->createToken('auth-token');
        $expiration = config('sanctum.expiration');

        if ($expiration) {
            $token->accessToken->expires_at = now()->addMinutes($expiration);
            $token->accessToken->save();
        }
```

Replace with:
```php
        $deviceId = $request->header('X-Device-Id');

        $token = $user->createToken($deviceId ?? 'unknown-device');
        $expiration = config('sanctum.expiration');

        if ($expiration) {
            $token->accessToken->expires_at = now()->addMinutes($expiration);
            $token->accessToken->save();
        }

        // Store device_id on the token record
        if ($deviceId && $token->accessToken) {
            $token->accessToken->forceFill(['device_id' => $deviceId])->save();
        }
```

- [ ] **Step 3: Apply same pattern to register() method**

Find `register()` method (around line 33):
```php
        $token = $user->createToken('auth-token');
```

Add device_id capture before this line (after the `$user->create` block):
```php
        $deviceId = $request->header('X-Device-Id');
        $token = $user->createToken($deviceId ?? 'unknown-device');
```

And add the device_id save after the expiration block:
```php
        if ($deviceId && $token->accessToken) {
            $token->accessToken->forceFill(['device_id' => $deviceId])->save();
        }
```

- [ ] **Step 4: Commit**

---

### Task 6: Add device_id column to personal_access_tokens

**Files:** New: `backend/database/migrations/2026_04_10_000001_add_device_id_to_personal_access_tokens.php`

- [ ] **Step 1: Create migration**

Create `backend/database/migrations/2026_04_10_000001_add_device_id_to_personal_access_tokens.php`:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            $table->string('device_id')->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            $table->dropColumn('device_id');
        });
    }
};
```

- [ ] **Step 2: Run migration (or note it for the developer)**

If the database is available:
```bash
cd backend && php artisan migrate
```

If not, note: `Run: php artisan migrate` after this plan.

- [ ] **Step 3: Commit**

---

### Task 7: Add Flutter dependencies

**Files:** `mobile/pubspec.yaml`

- [ ] **Step 1: Read current pubspec.yaml**

Run: `cat mobile/pubspec.yaml`

- [ ] **Step 2: Add new dependencies**

Find the `dependencies:` block. Add these entries after the existing dependencies (before any `dev_dependencies` comment):

```yaml
  # Local Database (Offline Support)
  hive: ^2.2.3
  hive_flutter: ^1.1.0

  # Background Sync
  workmanager: ^0.5.2

  # Connectivity
  connectivity_plus: ^6.1.0

  # Device Info
  device_info_plus: ^11.1.0
```

Also add to `dev_dependencies`:
```yaml
  hive_generator: ^2.0.1
  build_runner: ^2.4.13
```

- [ ] **Step 3: Run flutter pub get**

```bash
cd mobile && flutter pub get
```

Expected: Dependencies resolved without conflict.

- [ ] **Step 4: Commit**

---

### Task 8: Create Hive attendance cache model

**Files:** Create: `mobile/lib/data/models/attendance_cache_model.dart`

- [ ] **Step 1: Create models directory**

```bash
mkdir -p mobile/lib/data/models
```

- [ ] **Step 2: Write Hive model**

Create `mobile/lib/data/models/attendance_cache_model.dart`:
```dart
import 'package:hive/hive.dart';

part 'attendance_cache_model.g.dart';

@HiveType(typeId: 0)
class AttendanceCacheModel extends HiveObject {
  // id is nullable — server ID is only assigned after sync
  @HiveField(0)
  int? id;

  @HiveField(1)
  late int userId;

  @HiveField(2)
  late String date;

  @HiveField(3)
  String? clockInTime;

  @HiveField(4)
  String? clockOutTime;

  @HiveField(5)
  String? status;

  @HiveField(6)
  double? clockInLatitude;

  @HiveField(7)
  double? clockInLongitude;

  @HiveField(8)
  double? clockOutLatitude;

  @HiveField(9)
  double? clockOutLongitude;

  @HiveField(10)
  String? clockInPhoto;

  @HiveField(11)
  String? clockOutPhoto;

  @HiveField(12)
  String? notes;

  @HiveField(13)
  late bool isSynced;

  @HiveField(14)
  late DateTime createdAt;

  AttendanceCacheModel();

  factory AttendanceCacheModel.fromJson(Map<String, dynamic> json, {bool synced = true}) {
    return AttendanceCacheModel()
      ..id = json['id'] as int?
      ..userId = json['user_id'] ?? 0
      ..date = json['date'] ?? ''
      ..clockInTime = json['clock_in_time']
      ..clockOutTime = json['clock_out_time']
      ..status = json['status']
      ..clockInLatitude = (json['clock_in_latitude'] as num?)?.toDouble()
      ..clockInLongitude = (json['clock_in_longitude'] as num?)?.toDouble()
      ..clockOutLatitude = (json['clock_out_latitude'] as num?)?.toDouble()
      ..clockOutLongitude = (json['clock_out_longitude'] as num?)?.toDouble()
      ..clockInPhoto = json['clock_in_photo']
      ..clockOutPhoto = json['clock_out_photo']
      ..notes = json['notes']
      ..isSynced = synced
      ..createdAt = DateTime.now();
  }

  Map<String, dynamic> toJson() => {
    'id': id,
    'user_id': userId,
    'date': date,
    'clock_in_time': clockInTime,
    'clock_out_time': clockOutTime,
    'status': status,
    'clock_in_latitude': clockInLatitude,
    'clock_in_longitude': clockInLongitude,
    'clock_out_latitude': clockOutLatitude,
    'clock_out_longitude': clockOutLongitude,
    'clock_in_photo': clockInPhoto,
    'clock_out_photo': clockOutPhoto,
    'notes': notes,
  };
}
```

- [ ] **Step 3: Generate Hive adapter**

```bash
cd mobile && flutter pub run build_runner build --delete-conflicting-outputs
```

Expected: `attendance_cache_model.g.dart` generated.

- [ ] **Step 4: Commit**

---

### Task 9: Create attendance cache datasource

**Files:** Create: `mobile/lib/data/sources/attendance_cache_datasource.dart`

- [ ] **Step 1: Write cache datasource**

Create `mobile/lib/data/sources/attendance_cache_datasource.dart`:
```dart
import 'package:hive/hive.dart';
import '../models/attendance_cache_model.dart';

class AttendanceCacheDataSource {
  static const String _boxName = 'attendance_cache';

  Box<AttendanceCacheModel>? _box;

  Future<Box<AttendanceCacheModel>> get box async {
    _box ??= await Hive.openBox<AttendanceCacheModel>(_boxName);
    return _box!;
  }

  Future<void> saveAttendance(AttendanceCacheModel attendance) async {
    final b = await box;
    await b.put(attendance.date, attendance);
  }

  Future<AttendanceCacheModel?> getAttendance(String date) async {
    final b = await box;
    return b.get(date);
  }

  Future<List<AttendanceCacheModel>> getUnsyncedAttendances() async {
    final b = await box;
    return b.values.where((a) => !a.isSynced).toList();
  }

  Future<void> markAsSynced(String date) async {
    final b = await box;
    final attendance = b.get(date);
    if (attendance != null) {
      attendance.isSynced = true;
      await attendance.save();
    }
  }

  Future<void> cacheTodayAttendance(Map<String, dynamic> response) async {
    final attendance = response['attendance'] as Map<String, dynamic>?;
    if (attendance != null) {
      await saveAttendance(AttendanceCacheModel.fromJson(attendance));
    }
  }

  Future<List<AttendanceCacheModel>> getAllCached() async {
    final b = await box;
    return b.values.toList();
  }

  Future<void> clearAll() async {
    final b = await box;
    await b.clear();
  }
}
```

- [ ] **Step 2: Commit**

---

### Task 10: Create AttendanceRepository

**Files:** Create: `mobile/lib/data/repositories/attendance_repository.dart`

- [ ] **Step 1: Create repositories directory**

```bash
mkdir -p mobile/lib/data/repositories
```

- [ ] **Step 2: Write AttendanceRepository**

Create `mobile/lib/data/repositories/attendance_repository.dart`:
```dart
import 'package:connectivity_plus/connectivity_plus.dart';
import '../models/attendance_cache_model.dart';
import '../sources/attendance_cache_datasource.dart';
import '../sources/attendance_remote_datasource.dart';

class AttendanceRepository {
  final AttendanceRemoteDataSource _remoteDataSource;
  final AttendanceCacheDataSource _cacheDataSource;
  final Connectivity _connectivity;

  AttendanceRepository({
    required AttendanceRemoteDataSource remoteDataSource,
    required AttendanceCacheDataSource cacheDataSource,
    Connectivity? connectivity,
  })  : _remoteDataSource = remoteDataSource,
        _cacheDataSource = cacheDataSource,
        _connectivity = connectivity ?? Connectivity();

  Future<bool> get _isOnline async {
    final result = await _connectivity.checkConnectivity();
    return !result.contains(ConnectivityResult.none);
  }

  /// Get today's attendance — tries remote first, falls back to cache.
  Future<Map<String, dynamic>> getTodayAttendance() async {
    if (await _isOnline) {
      try {
        final response = await _remoteDataSource.getTodayAttendance();
        // Cache the response for offline use
        await _cacheDataSource.cacheTodayAttendance(response);
        return response;
      } catch (_) {
        // Fall through to cache
      }
    }

    // Offline — load from cache
    final today = _formatDate(DateTime.now());
    final cached = await _cacheDataSource.getAttendance(today);
    if (cached != null) {
      return {'attendance': cached.toJson(), 'schedule': null, 'from_cache': true};
    }
    return {'attendance': null, 'schedule': null, 'from_cache': true, 'error': 'No cached data'};
  }

  /// Clock in — online: API + cache. Offline: cache only (queue sync).
  Future<Map<String, dynamic>> clockIn({
    required double latitude,
    required double longitude,
    String? photoPath,
  }) async {
    if (await _isOnline) {
      final response = await _remoteDataSource.clockIn(
        latitude: latitude,
        longitude: longitude,
        photoPath: photoPath,
      );
      await _cacheDataSource.cacheTodayAttendance(response);
      return response;
    }

    // Offline: cache locally
    final now = DateTime.now();
    final cached = AttendanceCacheModel()
      ..id = DateTime.now().millisecondsSinceEpoch
      ..userId = 0
      ..date = _formatDate(now)
      ..clockInTime = '${now.hour.toString().padLeft(2, '0')}:${now.minute.toString().padLeft(2, '0')}:${now.second.toString().padLeft(2, '0')}'
      ..clockInLatitude = latitude
      ..clockInLongitude = longitude
      ..clockInPhoto = photoPath
      ..status = 'pending'
      ..isSynced = false
      ..createdAt = now;

    await _cacheDataSource.saveAttendance(cached);
    return {
      'message': 'Clock in saved offline. Will sync when online.',
      'attendance': cached.toJson(),
      'offline': true,
    };
  }

  /// Clock out — online: API + cache. Offline: cache only.
  Future<Map<String, dynamic>> clockOut({
    required double latitude,
    required double longitude,
  }) async {
    if (await _isOnline) {
      final response = await _remoteDataSource.clockOut(
        latitude: latitude,
        longitude: longitude,
      );
      await _cacheDataSource.cacheTodayAttendance(response);
      return response;
    }

    final today = _formatDate(DateTime.now());
    final now = DateTime.now();
    final cached = await _cacheDataSource.getAttendance(today);
    if (cached != null) {
      cached.clockOutTime = '${now.hour.toString().padLeft(2, '0')}:${now.minute.toString().padLeft(2, '0')}:${now.second.toString().padLeft(2, '0')}';
      cached.clockOutLatitude = latitude;
      cached.clockOutLongitude = longitude;
      cached.isSynced = false;
      await cached.save();
    }
    return {
      'message': 'Clock out saved offline. Will sync when online.',
      'offline': true,
    };
  }

  /// Sync all unsynced cached attendances to the server via the idempotent /sync endpoint.
  /// Each record is sent with client_timestamp so the server can detect duplicates.
  Future<int> syncPendingAttendances() async {
    if (await _isOnline) {
      final unsynced = await _cacheDataSource.getUnsyncedAttendances();
      int synced = 0;
      for (final attendance in unsynced) {
        try {
          final response = await _remoteDataSource.syncOfflineAttendance(
            date: attendance.date,
            clockInTime: attendance.clockInTime,
            clockInLatitude: attendance.clockInLatitude,
            clockInLongitude: attendance.clockInLongitude,
            clockInPhoto: attendance.clockInPhoto,
            clockOutTime: attendance.clockOutTime,
            clockOutLatitude: attendance.clockOutLatitude,
            clockOutLongitude: attendance.clockOutLongitude,
            clientTimestamp: attendance.createdAt.toIso8601String(),
          );
          if (response['synced'] == true) {
            await _cacheDataSource.markAsSynced(attendance.date);
            synced++;
          }
        } catch (_) {
          // Will retry next sync cycle
        }
      }
      return synced;
    }
    return 0;
  }

  Future<int> getUnsyncedCount() async {
    final unsynced = await _cacheDataSource.getUnsyncedAttendances();
    return unsynced.length;
  }

  String _formatDate(DateTime dt) {
    return '${dt.year}-${dt.month.toString().padLeft(2, '0')}-${dt.day.toString().padLeft(2, '0')}';
  }
}
```

- [ ] **Step 3: Add syncOfflineAttendance to AttendanceRemoteDataSource**

After completing Task 16 (backend `/sync` endpoint), add this method to `mobile/lib/data/sources/attendance_remote_datasource.dart`:

```dart
Future<Map<String, dynamic>> syncOfflineAttendance({
  required String date,
  String? clockInTime,
  double? clockInLatitude,
  double? clockInLongitude,
  String? clockInPhoto,
  String? clockOutTime,
  double? clockOutLatitude,
  double? clockOutLongitude,
  required String clientTimestamp,
}) async {
  try {
    final formData = FormData.fromMap({
      'date': date,
      if (clockInTime != null) 'clock_in_time': clockInTime,
      if (clockInLatitude != null) 'clock_in_latitude': clockInLatitude,
      if (clockInLongitude != null) 'clock_in_longitude': clockInLongitude,
      if (clockInPhoto != null)
        'clock_in_photo': await MultipartFile.fromFile(clockInPhoto),
      if (clockOutTime != null) 'clock_out_time': clockOutTime,
      if (clockOutLatitude != null) 'clock_out_latitude': clockOutLatitude,
      if (clockOutLongitude != null) 'clock_out_longitude': clockOutLongitude,
      'client_timestamp': clientTimestamp,
    });

    final response = await _dio.post(
      '/api/v1/attendance/sync',
      data: formData,
      options: Options(headers: {'Content-Type': 'multipart/form-data'}),
    );
    return response.data as Map<String, dynamic>;
  } catch (e) {
    return _handleError(e, 'Sync failed');
  }
}
```

Note: The backend endpoint from Task 16 must exist before this method will work.

- [ ] **Step 4: Commit**

---

### Task 11: Register new dependencies in DI

**Files:** `mobile/lib/core/di/injection.dart`

- [ ] **Step 1: Read current injection.dart**

Run: `cat mobile/lib/core/di/injection.dart`

- [ ] **Step 2: Add new registrations**

Find the section that registers `AttendanceRemoteDataSource` (look for `.registerFactory<AttendanceRemoteDataSource>` or similar pattern).

After that line, add:
```dart
  // Cache and Repository
  final attendanceCacheDataSource = AttendanceCacheDataSource();
  final attendanceRepository = AttendanceRepository(
    remoteDataSource: getIt<AttendanceRemoteDataSource>(),
    cacheDataSource: attendanceCacheDataSource,
  );
  getIt.registerSingleton<AttendanceCacheDataSource>(attendanceCacheDataSource);
  getIt.registerSingleton<AttendanceRepository>(attendanceRepository);
```

Also add the imports at the top of the file:
```dart
import '../../data/models/attendance_cache_model.dart';
import '../../data/sources/attendance_cache_datasource.dart';
import '../../data/repositories/attendance_repository.dart';
```

And initialize Hive before the `getIt` registrations:
```dart
  await Hive.initFlutter();
  Hive.registerAdapter(AttendanceCacheModelAdapter());
```

Make sure `Hive.initFlutter()` is called before `getIt` is used.

- [ ] **Step 3: Commit**

---

### Task 12: Update AttendanceCubit to use repository

**Files:** `mobile/lib/logic/attendance/attendance_cubit.dart`

- [ ] **Step 1: Read current cubit**

Run: `cat mobile/lib/logic/attendance/attendance_cubit.dart`

- [ ] **Step 2: Replace datasource with repository**

Find:
```dart
import '../../data/sources/attendance_remote_datasource.dart';
```

Replace with:
```dart
import '../../data/sources/attendance_remote_datasource.dart';
import '../../data/repositories/attendance_repository.dart';
```

Find the constructor:
```dart
  AttendanceCubit({required AttendanceRemoteDataSource attendanceDataSource})
      : _attendanceDataSource = attendanceDataSource,
        super(const AttendanceInitial());
```

Replace with:
```dart
  AttendanceCubit({required AttendanceRepository attendanceRepository})
      : _attendanceRepository = attendanceRepository,
        super(const AttendanceInitial());
```

Rename the field throughout: `_attendanceDataSource` → `_attendanceRepository`.

- [ ] **Step 3: Update each method to use repository**

`loadTodayAttendance()`:
```dart
final response = await _attendanceRepository.getTodayAttendance();
```

`clockIn()`:
```dart
final response = await _attendanceRepository.clockIn(
  latitude: latitude,
  longitude: longitude,
  photoPath: photoPath,
);
```

`clockOut()`:
```dart
final response = await _attendanceRepository.clockOut(
  latitude: latitude,
  longitude: longitude,
);
```

`resetTodayAttendance()` — keep using remote datasource directly (admin/testing endpoint):
```dart
final response = await _attendanceRepository.clockIn(
  latitude: latitude,
  longitude: longitude,
); // or expose a reset method on repository
```
Note: For reset, either call `_attendanceRepository._remoteDataSource.resetTodayAttendance()` (not ideal — leaks internals) or add a `resetToday()` method to `AttendanceRepository` that calls the remote datasource directly.

Add this method to `AttendanceRepository`:
```dart
Future<Map<String, dynamic>> resetTodayAttendance() async {
  final response = await _remoteDataSource.resetTodayAttendance();
  await _cacheDataSource.cacheTodayAttendance({'attendance': null, 'schedule': null});
  return response;
}
```

Then in cubit:
```dart
await _attendanceRepository.resetTodayAttendance();
```

- [ ] **Step 4: Commit**

---

### Task 13: Create AttendanceSyncCubit for background sync

**Files:** Create: `mobile/lib/logic/attendance/attendance_sync_cubit.dart`

- [ ] **Step 1: Write sync cubit**

Create `mobile/lib/logic/attendance/attendance_sync_cubit.dart`:
```dart
import 'dart:async';
import 'package:connectivity_plus/connectivity_plus.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../../data/repositories/attendance_repository.dart';

abstract class AttendanceSyncState {}
class AttendanceSyncInitial extends AttendanceSyncState {}
class AttendanceSyncInProgress extends AttendanceSyncState {
  final int pendingCount;
  AttendanceSyncInProgress(this.pendingCount);
}
class AttendanceSyncDone extends AttendanceSyncState {
  final int syncedCount;
  AttendanceSyncDone(this.syncedCount);
}
class AttendanceSyncError extends AttendanceSyncState {
  final String message;
  AttendanceSyncError(this.message);
}

class AttendanceSyncCubit extends Cubit<AttendanceSyncState> {
  final AttendanceRepository _repository;
  final Connectivity _connectivity;
  StreamSubscription<List<ConnectivityResult>>? _connectivitySubscription;

  AttendanceSyncCubit({required AttendanceRepository repository, Connectivity? connectivity})
      : _repository = repository,
        _connectivity = connectivity ?? Connectivity(),
        super(AttendanceSyncInitial());

  void startListening() {
    _connectivitySubscription = _connectivity.onConnectivityChanged.listen((result) {
      if (!result.contains(ConnectivityResult.none)) {
        syncPending();
      }
    });
  }

  Future<void> syncPending() async {
    emit(AttendanceSyncInProgress(await _repository.getUnsyncedCount()));
    try {
      final synced = await _repository.syncPendingAttendances();
      emit(AttendanceSyncDone(synced));
    } catch (e) {
      emit(AttendanceSyncError(e.toString()));
    }
  }

  @override
  Future<void> close() {
    _connectivitySubscription?.cancel();
    return super.close();
  }
}
```

- [ ] **Step 2: Commit**

---

### Task 14: Initialize Hive and workmanager in main.dart

**Files:** `mobile/lib/main.dart`

- [ ] **Step 1: Read main.dart**

Run: `cat mobile/lib/main.dart`

- [ ] **Step 2: Add Hive initialization at startup**

Find `void main()` and change it to:
```dart
import 'package:hive_flutter/hive_flutter.dart';
import 'package:workmanager/workmanager.dart';
import 'package:flutter/material.dart';

const String bgTaskName = 'attendanceSyncTask';

@pragma('vm:entry-point')
void callbackDispatcher() {
  Workmanager().executeTask((task, inputData) async {
    if (task == bgTaskName) {
      // Sync will be handled by AttendanceSyncCubit when app opens
      // Workmanager wakes the app to run this in background
      return true;
    }
    return false;
  });
}

void main() async {
  WidgetsFlutterBinding.ensureInitialized();

  // Initialize Hive
  await Hive.initFlutter();

  // Register background task
  await Workmanager().registerPeriodicTask(
    bgTaskName,
    bgTaskName,
    frequency: const Duration(minutes: 15),
    constraints: Constraints(
      networkType: NetworkType.connected,
    ),
  );

  setupServiceLocator();

  runApp(const MyApp());
}
```

Also add the import for `package:workmanager/workmanager.dart` and `package:hive_flutter/hive_flutter.dart`.

- [ ] **Step 3: Commit**

---

### Task 15: Add hasRegisteredDevice to User model

**Files:** `backend/app/Models/User.php`

- [ ] **Step 1: Read current User model**

Run: `cat backend/app/Models/User.php`

- [ ] **Step 2: Add hasRegisteredDevice method**

Find the closing brace of `isManager()` (around line 105). Add after it:
```php
    public function hasRegisteredDevice(): bool
    {
        return $this->tokens()
            ->whereNotNull('device_id')
            ->exists();
    }
```

- [ ] **Step 3: Commit**

---

### Task 16: Add idempotent offline-sync endpoint

**Files:**
- Modify: `backend/app/Http/Controllers/Api/AttendanceController.php`
- Modify: `backend/routes/api.php`

This endpoint handles offline-queued attendance from the mobile app. It is idempotent — safe to call multiple times with the same `client_timestamp`.

- [ ] **Step 1: Add sync method to AttendanceController**

Read current file end to find where `resetToday` method ends:

Run: `wc -l backend/app/Http/Controllers/Api/AttendanceController.php`

Find the closing `}` of the last method in the file, then add this new method before it:

```php
    /**
     * Sync an offline-queued attendance record.
     * Idempotent: skips if a record for this user+date already exists server-side.
     */
    public function sync(Request $request): JsonResponse
    {
        $request->validate([
            'date' => 'required|date',
            'clock_in_time' => 'nullable',
            'clock_in_latitude' => 'nullable|numeric|between:-90,90',
            'clock_in_longitude' => 'nullable|numeric|between:-180,180',
            'clock_in_photo' => 'nullable|image|max:2048',
            'clock_out_time' => 'nullable',
            'clock_out_latitude' => 'nullable|numeric|between:-90,90',
            'clock_out_longitude' => 'nullable|numeric|between:-180,180',
            'clock_out_photo' => 'nullable|image|max:2048',
            'client_timestamp' => 'required|string',
        ]);

        $today = $request->date;
        $user = $request->user();

        // Idempotency: skip if attendance already exists for this user+date
        $existing = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if ($existing) {
            return response()->json([
                'synced' => true,
                'message' => 'Attendance already exists, skipped.',
                'attendance' => $existing,
            ]);
        }

        $location = $user->location;
        $schedule = Schedule::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        $clockInPath = $request->hasFile('clock_in_photo')
            ? $request->file('clock_in_photo')->store('photos')
            : null;
        $clockOutPath = $request->hasFile('clock_out_photo')
            ? $request->file('clock_out_photo')->store('photos')
            : null;

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => $today,
            'clock_in_time' => $request->clock_in_time,
            'clock_in_latitude' => $request->clock_in_latitude,
            'clock_in_longitude' => $request->clock_in_longitude,
            'clock_in_photo' => $clockInPath,
            'clock_out_time' => $request->clock_out_time,
            'clock_out_latitude' => $request->clock_out_latitude,
            'clock_out_longitude' => $request->clock_out_longitude,
            'clock_out_photo' => $clockOutPath,
            'schedule_id' => $schedule?->id,
            'shift_id' => $schedule?->shift_id ?? $user->shift_id,
            'location_id' => $location?->id,
            'status' => 'pending',
        ]);

        return response()->json([
            'synced' => true,
            'message' => 'Offline attendance synced successfully.',
            'attendance' => $attendance->load(['shift', 'location']),
        ]);
    }
```

- [ ] **Step 2: Add route**

Open `backend/routes/api.php`. Find the Attendance section inside the `Route::middleware('auth:sanctum')->group(function () {` block.

Find:
```php
    Route::get('/attendance/history', [AttendanceController::class, 'history']);
```

Add after it:
```php
    Route::post('/attendance/sync', [AttendanceController::class, 'sync']);
```

- [ ] **Step 3: Verify route count**

Run: `grep -c "Route::" backend/routes/api.php`
Expected: previous count + 1

- [ ] **Step 4: Commit**

---

## Self-Review Checklist

- [ ] Task 1 — All routes prefixed with `v1`, no route removed; mobile basePath updated to `/api/v1`
- [ ] Task 2 — `GeoService.php` created at correct path
- [ ] Task 3 — `calculateDistance` removed from controller, replaced with `$this->geoService->calculateDistance`
- [ ] Task 4 — `$appends = ['work_duration']` added to `Attendance.php`
- [ ] Task 5 — `X-Device-Id` header captured in `login()` and `register()`
- [ ] Task 6 — Migration created with `device_id` column
- [ ] Task 7 — All 5 new packages added to `pubspec.yaml`, `flutter pub get` succeeded
- [ ] Task 8 — Hive model: `id` is `int?` (nullable), adapter generated
- [ ] Task 9 — `AttendanceCacheDataSource` with all CRUD methods
- [ ] Task 10 — `AttendanceRepository` routes reads cache-first, `syncPendingAttendances()` calls `/sync`, `getUnsyncedCount()` exists
- [ ] Task 11 — `AttendanceRepository` registered in DI, `Hive.initFlutter()` called
- [ ] Task 12 — `AttendanceCubit` uses repository, not datasource directly
- [ ] Task 13 — `AttendanceSyncCubit` uses `getUnsyncedCount()`, no clock-out workaround
- [ ] Task 14 — `Hive.initFlutter()` and `Workmanager` registered in `main.dart`
- [ ] Task 15 — `hasRegisteredDevice()` added to `User.php`
- [ ] Task 16 — `POST /attendance/sync` route added, idempotent controller method works
- [ ] No placeholder code (TBD, TODO, "implement later") in any step
- [ ] All file paths are exact

---

**Plan saved to:** `docs/superpowers/plans/2026-04-10-production-architecture.md`
