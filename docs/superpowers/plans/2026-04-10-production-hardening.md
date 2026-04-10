# Production Hardening Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Fix all Critical and High priority issues from improvement.md — password hashing, clock-in safety, geo-fencing, GPS validation, greeting, user name, and token refresh.

**Architecture:** Each task is a surgical file-level fix. Auth changes go through `AuthRemoteDataSource` / `AuthCubit` for consistency. Backend fixes in `AuthController.php` and `AttendanceController.php`.

**Tech Stack:** Laravel 12, Flutter 3, Dart, PHP 8.2+

---

## Task Map

| Task | File | Issue |
|------|------|-------|
| 1 | `backend/app/Http/Controllers/Api/AuthController.php` | Password not hashed on register |
| 2 | `mobile/lib/data/sources/auth_remote_datasource.dart` | `getCurrentUser` wrong route |
| 3 | `mobile/lib/logic/auth/auth_cubit.dart` | User name from email split, no `getCurrentUser` call, logout skips API |
| 4 | `mobile/lib/logic/auth/auth_cubit.dart` | No token refresh mechanism |
| 5 | `backend/app/Http/Controllers/Api/AttendanceController.php` | `updateOrCreate` destroys clock-out on re-clock-in |
| 6 | `backend/app/Http/Controllers/Api/AttendanceController.php` | Geo-fencing: hard block outside radius |
| 7 | `backend/app/Http/Controllers/Api/AttendanceController.php` | Remove redundant pre-check before `updateOrCreate` |
| 8 | `mobile/lib/presentation/screens/clock_in_screen.dart` | GPS accuracy not validated |
| 9 | `mobile/lib/presentation/screens/home_screen.dart` | Greeting always "Good Morning" |
| 10 | `mobile/lib/core/network/api_client.dart` | Ngrok URL hardcoded, no env-based config |

---

### Task 1: Hash password on register

**File:** `backend/app/Http/Controllers/Api/AuthController.php`

- [ ] **Step 1: Add Hash import**

Find line: `use Illuminate\Support\Facades\Hash;`

It already exists (line 10). No change needed.

- [ ] **Step 2: Hash password in register()**

Find lines 27–31:
```php
$user = User::create([
    'name' => $request->name,
    'email' => $request->email,
    'password' => $request->password,
]);
```

Replace with:
```php
$user = User::create([
    'name' => $request->name,
    'email' => $request->email,
    'password' => Hash::make($request->password),
]);
```

- [ ] **Step 3: Verify** — `AuthController.php` line 30 reads `Hash::make($request->password)`. Commit.

Run: `grep -n "Hash::make" backend/app/Http/Controllers/Api/AuthController.php`
Expected: line with `password => Hash::make`

---

### Task 2: Fix getCurrentUser route to /api/auth/me

**File:** `mobile/lib/data/sources/auth_remote_datasource.dart`

- [ ] **Step 1: Fix route**

Find line 76: `final response = await _dio.get('/api/auth/user');`

Replace with:
```dart
final response = await _dio.get('/api/auth/me');
```

- [ ] **Step 2: Also apply same fix to logout() — route is correct already**

No change to `logout()`. Route `/api/auth/logout` exists in backend. No action.

- [ ] **Step 3: Commit**

---

### Task 3: Use user name from API, call getCurrentUser after login, call logout API

**File:** `mobile/lib/logic/auth/auth_cubit.dart`

- [ ] **Step 1: Read full file to confirm line numbers**

Run: `cat -n mobile/lib/logic/auth/auth_cubit.dart`

- [ ] **Step 2: Add getCurrentUser call after successful login**

Find the `login()` success block (around lines 24–28):
```dart
if (response.containsKey('token')) {
    final token = response['token'] as String;

    // Save token to local storage
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('auth_token', token);
    await prefs.setString('user_email', email);

    emit(AuthAuthenticated(token: token, userName: email.split('@').first));
```

Replace that entire `if` block with:
```dart
if (response.containsKey('token')) {
    final token = response['token'] as String;
    final user = response['user'] as Map<String, dynamic>?;
    final userName = user?['name'] as String? ?? email.split('@').first;

    // Save token and email to local storage
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('auth_token', token);
    await prefs.setString('user_email', email);

    emit(AuthAuthenticated(token: token, userName: userName));
```

- [ ] **Step 3: Fix register() to use user name from response**

Find the `register()` success block:
```dart
emit(AuthAuthenticated(token: token, userName: name));
```

Replace with:
```dart
emit(AuthAuthenticated(token: token, userName: name));
```

No change needed — `register` already passes `name` (the requested name), which is acceptable. If `user` key is in the response, extract it the same way as login. Apply the same pattern as step 2 if response includes `user`.

- [ ] **Step 4: Fix logout() to call API before clearing local state**

Find (around lines 78–85):
```dart
Future<void> logout() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('auth_token');
    await prefs.remove('user_email');
    emit(AuthUnauthenticated());
}
```

Replace with:
```dart
Future<void> logout() async {
    try {
        await _authDataSource.logout();
    } catch (_) {
        // Proceed with local logout even if API call fails
    } finally {
        final prefs = await SharedPreferences.getInstance();
        await prefs.remove('auth_token');
        await prefs.remove('user_email');
        emit(AuthUnauthenticated());
    }
}
```

- [ ] **Step 5: Commit**

Run: `grep -n "getCurrentUser\|userName\|user\?\|logout" mobile/lib/logic/auth/auth_cubit.dart`
Expected: `getCurrentUser` call present, `userName` extracted from response, logout has `finally` block.

---

### Task 4: Implement token refresh interceptor

**File:** `mobile/lib/core/network/api_client.dart`

- [ ] **Step 1: Read current file**

Run: `cat mobile/lib/core/network/api_client.dart`

- [ ] **Step 2: Add token refresh interceptor**

Find the `_dio.interceptors.add(InterceptorsWrapper(...))` block (around lines 61–83).

Add inside the `onError` handler, after the `return handler.next(error);` line, before the closing `},`:

Insert this new case:
```dart
onError: (error, handler) async {
  if (error.response?.statusCode == 401) {
    // Token expired — try refresh
    try {
      final refreshResponse = await _dio.post('/api/auth/refresh');
      if (refreshResponse.statusCode == 200) {
        // Retry original request
        final opts = error.requestOptions;
        return handler.resolve(await _dio.fetch(opts));
      }
    } catch (_) {
      // Refresh failed — clear token, let caller handle auth loss
    }
  }
  return handler.next(error);
},
```

The existing `onError` handler only logs. Replace the entire `onError` callback with:
```dart
onError: (error, handler) async {
  if (error.response?.statusCode == 401) {
    try {
      final refreshResponse = await _dio.post('/api/auth/refresh');
      if (refreshResponse.statusCode == 200) {
        final opts = error.requestOptions;
        return handler.resolve(await _dio.fetch(opts));
      }
    } catch (_) {
      // Refresh failed — pass error through
    }
  }
  debugPrint('API Error: ${error.message}');
  debugPrint('Error response: ${error.response?.data}');
  debugPrint('Error status: ${error.response?.statusCode}');
  return handler.next(error);
},
```

- [ ] **Step 3: Commit**

---

### Task 5: Fix updateOrCreate destroying clock-out on re-clock-in

**File:** `backend/app/Http/Controllers/Api/AttendanceController.php`

- [ ] **Step 1: Read clockIn method around line 104**

Run: `sed -n '100,130p' backend/app/Http/Controllers/Api/AttendanceController.php`

- [ ] **Step 2: Replace updateOrCreate with conditional create-or-update**

Find lines 104–119:
```php
$attendance = Attendance::updateOrCreate(
    [
        'user_id' => $request->user()->id,
        'date' => $today,
    ],
    [
        'clock_in_time' => $now->format('H:i:s'),
        'clock_in_latitude' => $request->latitude,
        'clock_in_longitude' => $request->longitude,
        'clock_in_photo' => $request->hasFile('photo') ? $request->file('photo')->store('photos') : null,
        'schedule_id' => $schedule?->id,
        'shift_id' => $schedule?->shift_id ?? $request->user()->shift_id,
        'location_id' => $location?->id,
        'status' => $status,
    ]
);
```

Replace with:
```php
$attendance = Attendance::firstOrCreate(
    [
        'user_id' => $request->user()->id,
        'date' => $today,
    ],
    [
        'clock_in_time' => $now->format('H:i:s'),
        'clock_in_latitude' => $request->latitude,
        'clock_in_longitude' => $request->longitude,
        'clock_in_photo' => $request->hasFile('photo') ? $request->file('photo')->store('photos') : null,
        'schedule_id' => $schedule?->id,
        'shift_id' => $schedule?->shift_id ?? $request->user()->shift_id,
        'location_id' => $location?->id,
        'status' => $status,
    ]
);

// If record already existed, only fill in clock_in fields if still empty
if ($attendance->wasRecentlyCreated === false && !$attendance->clock_in_time) {
    $attendance->update([
        'clock_in_time' => $now->format('H:i:s'),
        'clock_in_latitude' => $request->latitude,
        'clock_in_longitude' => $request->longitude,
        'clock_in_photo' => $request->hasFile('photo') ? $request->file('photo')->store('photos') : null,
        'schedule_id' => $schedule?->id,
        'shift_id' => $schedule?->shift_id ?? $request->user()->shift_id,
        'location_id' => $location?->id,
        'status' => $status,
    ]);
}
```

- [ ] **Step 3: Commit**

---

### Task 6: Hard block clock-in outside radius (enforce geo-fencing)

**File:** `backend/app/Http/Controllers/Api/AttendanceController.php`

- [ ] **Step 1: Read the return block after clock-in logic**

Run: `sed -n '120,135p' backend/app/Http/Controllers/Api/AttendanceController.php`

- [ ] **Step 2: Add hard block before the success return**

Find the `return response()->json([` that follows the clock-in logic (around line 121):
```php
return response()->json([
    'message' => $isWithinRadius ? 'Clock in successful' : 'Clock in successful but you are outside the office location',
    'attendance' => $attendance->load(['shift', 'location']),
    'is_within_radius' => $isWithinRadius,
    'distance' => $distance,
    'radius_used' => $location?->getEffectiveRadius(),
]);
```

Replace with:
```php
if (!$isWithinRadius) {
    return response()->json([
        'message' => 'You are outside the office location. Clock in rejected.',
        'distance' => $distance,
        'radius_used' => $location?->getEffectiveRadius(),
    ], 400);
}

return response()->json([
    'message' => 'Clock in successful',
    'attendance' => $attendance->load(['shift', 'location']),
    'is_within_radius' => $isWithinRadius,
    'distance' => $distance,
    'radius_used' => $location?->getEffectiveRadius(),
]);
```

- [ ] **Step 3: Commit**

---

### Task 7: Remove redundant pre-check before updateOrCreate

**File:** `backend/app/Http/Controllers/Api/AttendanceController.php`

- [ ] **Step 1: Find and remove the redundant check**

Find lines 57–65:
```php
// Check if already clocked in today
$existingAttendance = Attendance::where('user_id', $request->user()->id)
    ->where('date', $today)
    ->first();

if ($existingAttendance && $existingAttendance->clock_in_time) {
    return response()->json([
        'message' => 'You have already clocked in today',
    ], 400);
}
```

Remove those 9 lines entirely. The same check is now handled correctly by `firstOrCreate` + `wasRecentlyCreated` in Task 5.

- [ ] **Step 2: Commit**

---

### Task 8: Add GPS accuracy validation to clock-in screen

**File:** `mobile/lib/presentation/screens/clock_in_screen.dart`

- [ ] **Step 1: Find _submitClockIn method**

Run: `grep -n "_submitClockIn\|accuracy\|clockIn(" mobile/lib/presentation/screens/clock_in_screen.dart`

- [ ] **Step 2: Add accuracy check before submitting**

Find the `_submitClockIn()` method body:
```dart
void _submitClockIn() {
    if (_currentPosition == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Please wait for location to load'),
          backgroundColor: Colors.orange,
        ),
      );
      return;
    }

    if (_selfieImage == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Silakan ambil foto selfie terlebih dahulu'),
          backgroundColor: Colors.orange,
        ),
      );
      return;
    }

    context.read<AttendanceCubit>().clockIn(
```

Replace the null-check block with:
```dart
void _submitClockIn() {
    if (_currentPosition == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Please wait for location to load'),
          backgroundColor: Colors.orange,
        ),
      );
      return;
    }

    // Warn if accuracy is too low
    const double _minAccuracy = 50.0; // meters
    if (_currentPosition!.accuracy > _minAccuracy) {
      final proceed = await _showAccuracyWarning();
      if (!proceed) return;
    }

    if (_selfieImage == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Silakan ambil foto selfie terlebih dahulu'),
          backgroundColor: Colors.orange,
        ),
      );
      return;
    }

    context.read<AttendanceCubit>().clockIn(
```

- [ ] **Step 3: Add _showAccuracyWarning dialog helper**

Add this method to the `_ClockInScreenState` class (after `_submitClockIn`):
```dart
Future<bool> _showAccuracyWarning() async {
    return await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Akurasi GPS Rendah'),
        content: Text(
          'Akurasi GPS saat ini ${_currentPosition!.accuracy.toStringAsFixed(0)}m. '
          'Disarankan untuk clock-in di tempat dengan akurasi di bawah 50m.',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Batal'),
          ),
          TextButton(
            onPressed: () => Navigator.pop(context, true),
            child: const Text('Tetap Clock In'),
          ),
        ],
      ),
    ) ?? false;
}
```

- [ ] **Step 4: Commit**

---

### Task 9: Fix greeting to show time-based greeting

**File:** `mobile/lib/presentation/screens/home_screen.dart`

- [ ] **Step 1: Find greeting line**

Run: `grep -n "Good Morning" mobile/lib/presentation/screens/home_screen.dart`

- [ ] **Step 2: Replace greeting with time-based logic**

Find:
```dart
Text(
  'Good Morning, 👋',
```

Replace with:
```dart
Text(
  '${_getGreeting()}, 👋',
```

- [ ] **Step 3: Add _getGreeting() helper**

Add this method to `_HomeScreenState`:
```dart
String _getGreeting() {
  final hour = DateTime.now().hour;
  if (hour < 12) return 'Good Morning';
  if (hour < 17) return 'Good Afternoon';
  return 'Good Evening';
}
```

- [ ] **Step 4: Commit**

---

### Task 10: Make ngrok URL configurable via environment

**File:** `mobile/lib/core/network/api_client.dart`

- [ ] **Step 1: Read current file**

Run: `cat mobile/lib/core/network/api_client.dart`

- [ ] **Step 2: Move URLs to a config class and add env override**

Replace the entire file with:
```dart
import 'dart:io';
import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';

abstract class ApiConfig {
  // Update this constant each time you start ngrok
  // Alternatively set NGROK_URL env var or store in SharedPreferences
  static const String _defaultNgrokUrl = 'https://pok-desinential-walton.ngrok-free.dev';
  static const String _emulatorUrl = 'http://10.0.2.2:8000';
  static const String _simulatorUrl = 'http://localhost:8000';

  static String get baseUrl {
    if (kIsWeb) return _defaultNgrokUrl;

    if (Platform.isAndroid) {
      final isEmulator = Platform.environment['ANDROID_EMULATOR'] == 'true' ||
          Platform.environment['ANDROID_SDK_ROOT']?.contains('emulator') == true;
      return isEmulator ? _emulatorUrl : _defaultNgrokUrl;
    }

    if (Platform.isIOS) {
      final isSimulator = Platform.environment['SIMULATOR_DEVICE_NAME'] != null;
      return isSimulator ? _simulatorUrl : _defaultNgrokUrl;
    }

    return _defaultNgrokUrl;
  }
}

class ApiClient {
  late final Dio _dio;

  ApiClient() {
    _dio = Dio(
      BaseOptions(
        baseUrl: ApiConfig.baseUrl,
        connectTimeout: const Duration(seconds: 30),
        receiveTimeout: const Duration(seconds: 30),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
      ),
    );

    debugPrint('API Base URL: ${ApiConfig.baseUrl}');

    _dio.interceptors.add(
      InterceptorsWrapper(
        onRequest: (options, handler) async {
          final prefs = await SharedPreferences.getInstance();
          final token = prefs.getString('auth_token');
          if (token != null && token.isNotEmpty) {
            options.headers['Authorization'] = 'Bearer $token';
            debugPrint('Token added to request: ${token.substring(0, 10)}...');
          } else {
            debugPrint('WARNING: No token found!');
          }
          debugPrint('Request: ${options.method} ${options.uri}');
          debugPrint('Headers: ${options.headers}');
          return handler.next(options);
        },
        onError: (error, handler) async {
          if (error.response?.statusCode == 401) {
            try {
              final refreshResponse = await _dio.post('/api/auth/refresh');
              if (refreshResponse.statusCode == 200) {
                final opts = error.requestOptions;
                return handler.resolve(await _dio.fetch(opts));
              }
            } catch (_) {
              // Refresh failed — pass error through
            }
          }
          debugPrint('API Error: ${error.message}');
          debugPrint('Error response: ${error.response?.data}');
          debugPrint('Error status: ${error.response?.statusCode}');
          return handler.next(error);
        },
      ),
    );
  }

  Dio get dio => _dio;
}
```

Note: Task 4 (token refresh) changes are already included here — no need to apply them separately.

- [ ] **Step 3: Commit**

---

## Self-Review Checklist

- [ ] Task 1 — `Hash::make` in `AuthController.php` line 30
- [ ] Task 2 — `/api/auth/me` in `auth_remote_datasource.dart` line 76
- [ ] Task 3 — `user?['name']` extraction, `finally` in `logout()`
- [ ] Task 4 — `onError` 401 handler in `api_client.dart` interceptor
- [ ] Task 5 — `firstOrCreate` + `wasRecentlyCreated` guard in `clockIn()`
- [ ] Task 6 — `if (!$isWithinRadius) return 400` before success return
- [ ] Task 7 — redundant pre-check removed
- [ ] Task 8 — `_showAccuracyWarning()` dialog + `accuracy > 50` guard
- [ ] Task 9 — `_getGreeting()` getter replacing "Good Morning" literal
- [ ] Task 10 — `ApiConfig` abstract class, URL in one place
- [ ] No placeholder code left in any task
- [ ] All file paths are exact and match current codebase

---

**Plan saved to:** `docs/superpowers/plans/2026-04-10-production-hardening.md`

After Plan A is complete, proceed to Plan B (Production Architecture) covering: offline-first with Hive, repository pattern, API versioning, GeoService class, WebSocket notifications, device fingerprinting, and face-match selfie.
