# Improvement Analysis - Attendance App

Document ini berisi daftar peningkatan yang perlu dilakukan pada project Attendance App. Issue dikelompokkan berdasarkan severity.

---

## Prioritas & Urutan Perbaikan

```
Priority 1 (Critical):  Password hash | Ngrok URL | updateOrCreate | Geo-fencing
Priority 2 (High):      GPS validation | Greeting | User name | Token refresh
Priority 3 (Medium):    Route mismatch | Logout API | Duration calc | Cast type
                        getCurrentUser | Offline | Reset middleware | Pagination
Priority 4 (Low):       Sisa issue lainnya
```

---

## 🔴 Critical

### 1. Password Tidak di-Hash Saat Register

**File:** `backend/app/Http/Controllers/Api/AuthController.php:27-31`

```php
// ❌ SEKARANG - password disimpan plaintext!
$user = User::create([
    'name' => $request->name,
    'email' => $request->email,
    'password' => $request->password,  // ← belum di-hash
]);
```

Model `User.php` sudah pakai `casts: ['password' => 'hashed']`, tapi cast Laravel hanya生效 saat `update/fill`. Saat `create` dengan `$fillable`, `password` perlu di-hash manual. Tambahkan use statement dan hash manual:

```php
use Illuminate\Support\Facades\Hash;

$user = User::create([
    'name' => $request->name,
    'email' => $request->email,
    'password' => Hash::make($request->password),  // ← hash manual
]);
```

---

### 2. Ngrok URL Hardcoded di Mobile

**File:** `mobile/lib/core/network/api_client.dart:8`

```dart
static const String _ngrokUrl = 'https://pok-desinential-walton.ngrok-free.dev';
```

- URL ini sudah expired/tidak valid (dari masa ngrok free)
- Setiap developer harus edit manual di source code
- Tidak ada mekanisme config yang fleksibel

**Solusi:** Gunakan SharedPreferences untuk menyimpan URL, atau至少 buat constante di tempat yang jelas dengan komentar dokumentasi:

```dart
// ⚠️ GANTI DENGAN URL NGROK ANDA SETIAP KALI JALANKAN
// Untuk development, simpan di .env atau SharedPreferences
static String get baseUrl {
  return _ngrokUrl; // <- update manual di sini
}
```

---

### 3. UpdateOrCreate Clock-In Menghapus Data Clock-Out

**File:** `backend/app/Http/Controllers/Api/AttendanceController.php:104-119`

```php
// ❌ SEKARANG - jika sudah ada record (sudah clock-out), semua field di-overwrite
$attendance = Attendance::updateOrCreate(
    ['user_id' => $user->id, 'date' => $today],
    [
        'clock_in_time' => $now->format('H:i:s'),
        'clock_in_latitude' => $request->latitude,
        // ... semua field clock_in
    ]
);
```

Jika user clock-in → clock-out → reset → clock-in lagi, **semua data clock-out hilang**. Gunakan conditional update:

```php
$attendance = Attendance::firstOrCreate(
    ['user_id' => $user->id, 'date' => $today],
    [
        'clock_in_time' => $now->format('H:i:s'),
        'clock_in_latitude' => $request->latitude,
        'clock_in_longitude' => $request->longitude,
        'clock_in_photo' => $request->hasFile('photo') ? $request->file('photo')->store('photos') : null,
        'schedule_id' => $schedule?->id,
        'shift_id' => $schedule?->shift_id ?? $user->shift_id,
        'location_id' => $location?->id,
        'status' => $status,
    ]
);

// Atau jika record sudah ada, hanya update field yang belum ada
if (!$attendance->clock_in_time) {
    $attendance->update([
        'clock_in_time' => $now->format('H:i:s'),
        'clock_in_latitude' => $request->latitude,
        'clock_in_longitude' => $request->longitude,
        'clock_in_photo' => $request->hasFile('photo') ? $request->file('photo')->store('photos') : null,
        'schedule_id' => $schedule?->id,
        'shift_id' => $schedule?->shift_id ?? $user->shift_id,
        'location_id' => $location?->id,
        'status' => $status,
    ]);
}
```

---

### 4. Geo-fencing Membiarkan Clock-In Luar Radius

**File:** `backend/app/Http/Controllers/Api/AttendanceController.php:121-127`

```php
return response()->json([
    'message' => $isWithinRadius ? 'Clock in successful'
        : 'Clock in successful but you are outside the office location',
    // ← TETAPI data tetep disimpan! Tidak ada enforcement
]);
```

Jika business requirement butuh **hard block** di luar radius, endpoint harus return error 400. Jika memang dibolehkan, minimal flag `is_within_radius` perlu disimpan ke DB:

```php
// Opsi A: Hard block
if (!$isWithinRadius) {
    return response()->json([
        'message' => 'You are outside the office location. Clock in rejected.',
        'distance' => $distance,
        'radius_used' => $radiusUsed,
    ], 400);
}

// Opsi B: Soft block - simpan flag
$attendance = Attendance::updateOrCreate(...);
$attendance->update(['is_within_radius' => $isWithinRadius]);
```

---

## 🟠 High Priority

### 5. Mobile Tidak Validasi Akurasi GPS

**File:** `mobile/lib/presentation/screens/clock_in_screen.dart`

Tidak ada pengecekan akurasi sebelum submit. Akurasi 500m+ tetap di-accept:

```dart
// ❌ SEKARANG - tidak ada validasi akurasi
context.read<AttendanceCubit>().clockIn(
  latitude: _currentPosition!.latitude,
  longitude: _currentPosition!.longitude,
  // ← _currentPosition!.accuracy tidak dicek
);
```

Tambahkan warning atau blocking jika akurasi di bawah threshold:

```dart
const double _minAccuracy = 50.0; // meter

Future<void> _submitClockIn() async {
  if (_currentPosition == null) {
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(
        content: Text('Please wait for location to load'),
        backgroundColor: Colors.orange,
      ),
    );
    return;
  }

  if (_currentPosition!.accuracy > _minAccuracy) {
    final proceed = await _showAccuracyWarning();
    if (!proceed) return;
  }

  // ... submit
}

Future<bool> _showAccuracyWarning() async {
  return await showDialog<bool>(
    context: context,
    builder: (context) => AlertDialog(
      title: const Text('Akurasi Rendah'),
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

---

### 6. Greeting Selalu "Good Morning"

**File:** `mobile/lib/presentation/screens/home_screen.dart:151`

```dart
// ❌ SEKARANG - selalu morning
Text('Good Morning, 👋', ...)

// ✅ PERBAIKI
final hour = DateTime.now().hour;
String greeting = hour < 12 ? 'Good Morning'
    : hour < 17 ? 'Good Afternoon'
    : 'Good Evening';
```

---

### 7. User Name dari Email Split, Bukan dari API

**File:** `mobile/lib/logic/auth/auth_cubit.dart:31`

```dart
// ❌ SEKARANG - name tidak disimpan dari response API
emit(AuthAuthenticated(token: token, userName: email.split('@').first));
```

Response API (`AuthController.php:81-86`) sudah return `user` object lengkap. Simpan dan gunakan name tersebut:

```dart
final user = response['user'] as Map<String, dynamic>;
final userName = user['name'] as String?;
emit(AuthAuthenticated(token: token, userName: userName ?? email.split('@').first));
```

---

### 8. Tidak Ada Token Refresh Mechanism

**File:** `mobile/lib/logic/auth/auth_cubit.dart`

Token Sanctum punya expiration (`config('sanctum.expiration')`), tapi:
- Tidak ada auto-refresh sebelum token expired
- Tidak ada retry logic ketika token expired (401)
- `refresh` endpoint ada di backend tapi tidak dipakai di mobile

**Solusi:** Tambahkan token interceptor dengan retry logic:

```dart
// Di api_client.dart, tambahkan retry interceptor
_dio.interceptors.add(
  InterceptorsWrapper(
    onError: (error, handler) async {
      if (error.response?.statusCode == 401) {
        // Token expired - try refresh
        try {
          final refreshResponse = await _dio.post('/api/auth/refresh');
          // Update token di storage
          // Retry original request
          return handler.resolve(refreshResponse);
        } catch (e) {
          // Refresh failed - logout
          // emit AuthUnauthenticated
        }
      }
      return handler.next(error);
    },
  ),
);
```

---

## 🟡 Medium Priority

### 9. Route Salah di getCurrentUser

**File:** `mobile/lib/data/sources/auth_remote_datasource.dart:76`

```dart
// ❌ SALAH
final response = await _dio.get('/api/auth/user');

// ✅ BENAR (sesuaikan dengan backend)
final response = await _dio.get('/api/auth/me');
```

Pastikan route `'/api/auth/me'` sudah ada di backend route atau gunakan route yang benar.

---

### 10. logout() di Mobile Tidak Panggil API

**File:** `mobile/lib/logic/auth/auth_cubit.dart:84-89`

```dart
// ❌ SEKARANG - hanya hapus local, tidak revoke token ke server
Future<void> logout() async {
    await prefs.remove('auth_token');
    await prefs.remove('user_email');
    emit(AuthUnauthenticated());
}
```

Panggil API logout untuk revoke token di server:

```dart
Future<void> logout() async {
    try {
        await _authDataSource.logout();
    } catch (_) {
        // Tetap logout local meskipun API gagal
    } finally {
        final prefs = await SharedPreferences.getInstance();
        await prefs.remove('auth_token');
        await prefs.remove('user_email');
        emit(AuthUnauthenticated());
    }
}
```

---

### 11. Duration Dihitung Ulang di Mobile

**File:** `mobile/lib/logic/attendance/attendance_cubit.dart:36-39`

```dart
// ❌ SEKARANG - mobile hitung sendiri
workDuration = _calculateDuration(
    attendance['clock_in_time'],
    attendance['clock_out_time'],
);
```

Backend `Attendance` model sudah punya accessor `$appends['work_duration']`. Cukup load dari response API:

```dart
// Pastikan backend menambahkan 'work_duration' ke response
final workDuration = attendance['work_duration'] as String?;
```

Pastikan `Attendance` model di backend menggunakan `$appends`:

```php
// Di backend/app/Models/Attendance.php
protected $appends = ['work_duration'];
```

---

### 12. Attendance Cast datetime Ambiguous

**File:** `backend/app/Models/Attendance.php:33`

```php
// ⚠️ Ambiguous - 'datetime:H:i:s' tidak jelas artinya
'clock_in_time' => 'datetime:H:i:s',
```

Jika menyimpan hanya waktu (bukan datetime penuh), sebaiknya gunakan string biasa atau cast ke `date_format` yang lebih tepat:

```php
// Opsi A: Simpan sebagai string H:i:s (recommended untuk time-only)
protected $casts = [
    'date' => 'date',
    'clock_in_time' => 'datetime:H:i:s',  // biarkan, tapi perlu dicek parsing-nya
];

// Opsi B: Manual accessor
protected $casts = [
    'date' => 'date',
];

public function getClockInTimeAttribute($value): ?string {
    return $value ? substr($value, 0, 8) : null; // ambil "HH:ii:ss"
}
```

---

### 13. getCurrentUser Tidak Pernah Dipanggil

**File:** `mobile/lib/logic/auth/auth_cubit.dart`

Setelah login berhasil, tidak ada pemanggilan `getCurrentUser()` untuk拿到 user profile lengkap. state `AuthAuthenticated` hanya punya `token` dan `userName` yang di-split dari email.

Tambahkan di `login()` setelah response sukses:

```dart
// Setelah emit AuthAuthenticated, fetch user profile
try {
    final userResponse = await _authDataSource.getCurrentUser();
    if (userResponse.containsKey('user')) {
        final user = userResponse['user'] as Map<String, dynamic>;
        final name = user['name'] as String?;
        emit(AuthAuthenticated(
            token: token,
            userName: name ?? email.split('@').first,
        ));
    }
} catch (_) {
    // proceed tanpa update name
}
```

---

### 14. Tidak Ada Offline Handling

**File:** `mobile/lib/logic/attendance/attendance_cubit.dart`

Tidak ada cached data saat offline:
- Attendance history tidak di-cache
- Tidak ada `Connectivity` check
- Jika API gagal, user tidak punya fallback

**Solusi:** Gunakan `connectivity_plus` dan cache attendance terakhir:

```dart
import 'package:connectivity_plus/connectivity_plus.dart';

// Di AttendanceCubit
final connectivity = await Connectivity().checkConnectivity();
if (connectivity.contains(ConnectivityResult.none)) {
    // Load dari cache
    return;
}

// Cache attendance saat fetch berhasil
await _cacheAttendance(response);
```

---

### 15. Reset Attendance Endpoint Tanpa Proteksi

**File:** `backend/routes/api.php:30`

```php
Route::post('/attendance/reset-today', [AttendanceController::class, 'resetToday'])
    ->middleware('auth:sanctum'); // ← tidak ada role check
```

Karyawan biasa bisa reset attendance mereka sendiri. Untuk production:

```php
// Opsi A: Hilangkan endpoint di production
Route::post('/attendance/reset-today', [AttendanceController::class, 'resetToday'])
    ->middleware(['auth:sanctum', 'role:admin']);

// Opsi B: Nonaktifkan jika APP_ENV=production
if (app()->environment('local', 'development')) {
    Route::post('/attendance/reset-today', ...);
}
```

---

### 16. Inconsistent API Response Format

**File:** `backend/app/Http/Controllers/Api/AttendanceController.php`

Beberapa endpoint return custom JSON, `history` return paginated Laravel response. Gunakan **API Resource** class Laravel untuk konsistensi:

```bash
php artisan make:resource AttendanceResource
php artisan make:resource AttendanceCollection
```

```php
// Di controller
use App\Http\Resources\AttendanceResource;

return AttendanceResource::collection($attendances);
// Atau single
return new AttendanceResource($attendance);
```

---

## 🟢 Low Priority / Good-to-Have

### 17. Izin Remote DataSource Belum Ada

**File:** `mobile/lib/logic/izin/` (tapi tidak ada `IzinRemoteDataSource`)

`screens/izin_screen.dart` dan `logic/izin/` sudah ada tapi `IzinRemoteDataSource` belum dibuat.

**Solusi:** Buat data source dan hubungkan dengan existing cubit:

```dart
// mobile/lib/data/sources/izin_remote_datasource.dart
class IzinRemoteDataSource {
  final Dio _dio;
  IzinRemoteDataSource({required Dio dio}) : _dio = dio;

  Future<Map<String, dynamic>> submitIzin({
    required String type,
    required String startDate,
    required String endDate,
    required String reason,
  }) async {
    // ... POST /api/izin
  }

  Future<Map<String, dynamic>> getIzinList() async {
    // ... GET /api/izin
  }
}
```

---

### 18. has_attendance Check Redundant

**File:** `backend/app/Http/Controllers/Api/AttendanceController.php:58-60`

```php
// ❌ REDUNDANT - updateOrCreate sudah menangani ini
$existingAttendance = Attendance::where('user_id', $user->id)
    ->where('date', $today)
    ->first();

if ($existingAttendance && $existingAttendance->clock_in_time) {
    return response()->json(['message' => 'Already clocked in'], 400);
}
```

Hapus `$existingAttendance` check karena `updateOrCreate` sudah handle existence.

---

### 19. Notification Model API Response Belum Dimanfaatkan

**File:** `mobile/lib/data/` (ada `notification_remote_datasource.dart` tapi tidak ada model notification)

Buat `Notification` model untuk type safety:

```dart
// mobile/lib/data/models/notification_model.dart
import 'package:freezed_annotation/freezed_annotation.dart';
part 'notification_model.freezed.dart';
part 'notification_model.g.dart';

@freezed
class NotificationModel with _$NotificationModel {
  const factory NotificationModel({
    required int id,
    required String title,
    required String message,
    required String type,
    required bool isRead,
    DateTime? createdAt,
  }) = _NotificationModel;

  factory NotificationModel.fromJson(Map<String, dynamic> json) =>
      _$NotificationModelFromJson(json);
}
```

---

### 20. Emulator Detection Tidak Reliable

**File:** `mobile/lib/core/network/api_client.dart:20-21`

```dart
// ⚠️ SDK_ROOT tidak selalu ada
final isEmulator = Platform.environment['ANDROID_EMULATOR'] == 'true' ||
    Platform.environment['ANDROID_SDK_ROOT']?.contains('emulator') == true;
```

Gunakan cara yang lebih reliable:

```dart
static String get baseUrl {
  if (kIsWeb) return _ngrokUrl;
  if (Platform.isAndroid) return _ngrokUrl;  // default ke ngrok
  if (Platform.isIOS) {
    final isSimulator = Platform.environment['SIMULATOR_DEVICE_NAME'] != null;
    return isSimulator ? _simulatorUrl : _ngrokUrl;
  }
  return _ngrokUrl;
}
```

---

### 21. Loading State Menimpa Semua Operations

**File:** `mobile/lib/logic/attendance/attendance_cubit.dart`

```dart
// ⚠️ AttendanceLoading menimpa semua state, termasuk success state
emit(const AttendanceLoading());
```

Gunakan `addSuccess` / `addError` state yang tidak menimpa operation-specific state, atau gunakan named loading states:

```dart
class AttendanceState {
  // ...
  const AttendanceState({
    this.isClockingIn = false,
    this.isClockingOut = false,
    // ...
  });

  final bool isClockingIn;
  final bool isClockingOut;
}
```

---

### 22. Duplicate AuthController (Namespace Conflict)

**File:** `backend/app/Http/Controllers/`

Ada dua `AuthController` dengan namespace berbeda:
- `App\Http\Controllers\Auth\AuthController` (Breeze default - web auth)
- `App\Http\Controllers\Api\AuthController` (API auth)

Ini bisa membingungkan. Sebaiknya rename API controller:

```
App\Http\Controllers\Api\AuthController  →  App\Http\Controllers\Api\AuthApiController
App\Http\Controllers\Api\AttendanceController  →  App\Http\Controllers\Api\AttendanceApiController
```

---

### 23. Tidak Ada Rate Limiting Visible

**File:** Backend secara umum

Tidak ada feedback ke mobile ketika rate limit tercapai. Tambahkan custom handler:

```php
// Di bootstrap/app.php atau Exception Handler
use Illuminate\Http\Request;

->withExceptions(function (Exceptions $exceptions) {
    $exceptions->render(function (ThrottleRequestsException $e, Request $request) {
        return response()->json([
            'message' => 'Too many attempts. Please try again later.',
            'retry_after' => $e->getHeaders()['Retry-After'] ?? 60,
        ], 429);
    });
});
```

---

### 24. Route Organization - Shifts & Locations di ScheduleController

**File:** `backend/routes/api.php:44-45`

```php
// ⚠️ Tidak intuitif
Route::get('/shifts', [ScheduleController::class, 'shifts']);
Route::get('/locations', [ScheduleController::class, 'locations']);
```

Pindahkan ke controller yang lebih tepat:

```php
Route::get('/shifts', [ShiftController::class, 'index']);
Route::get('/locations', [LocationController::class, 'index']);

// Atau jika mau simpel, buat Api/ReferenceController
Route::get('/shifts', [ReferenceController::class, 'shifts']);
Route::get('/locations', [ReferenceController::class, 'locations']);
```

---

## Checklist Perbaikan

### Priority 1 - Critical
- [ ] Fix password hash di register (`AuthController.php`)
- [ ] Update ngrok URL configuration (`api_client.dart`)
- [ ] Fix updateOrCreate clock-in (`AttendanceController.php`)
- [ ] Tentukan geo-fencing enforcement policy (`AttendanceController.php`)

### Priority 2 - High
- [ ] Tambahkan GPS accuracy validation (`clock_in_screen.dart`)
- [ ] Fix greeting berdasarkan waktu (`home_screen.dart`)
- [ ] Gunakan user name dari API response (`auth_cubit.dart`)
- [ ] Implement token refresh mechanism (`api_client.dart` / `auth_cubit.dart`)

### Priority 3 - Medium
- [ ] Fix route `/api/auth/user` → `/api/auth/me` (`auth_remote_datasource.dart`)
- [ ] Panggil API logout saat logout (`auth_cubit.dart`)
- [ ] Gunakan work_duration dari backend (`attendance_cubit.dart`)
- [ ] Review datetime cast di `Attendance.php`
- [ ] Panggil getCurrentUser setelah login (`auth_cubit.dart`)
- [ ] Tambahkan offline handling (`attendance_cubit.dart`)
- [ ] Proteksi reset endpoint untuk production (`api.php`)
- [ ] Gunakan API Resource class Laravel

### Priority 4 - Low
- [ ] Buat `IzinRemoteDataSource`
- [ ] Hapus redundant has_attendance check
- [ ] Buat notification model dengan freezed
- [ ] Perbaiki emulator detection
- [ ] Perbaiki loading state management
- [ ] Rename duplicate AuthController
- [ ] Tambahkan rate limiting feedback
- [ ] Reorganize routes ke controller yang tepat

---

_Dokumen ini dibuat berdasarkan analisis codebase tanggal 2026-03-25_
