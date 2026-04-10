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
        await _cacheDataSource.cacheTodayAttendance(response);
        return response;
      } catch (_) {
        // Fall through to cache
      }
    }

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

    final now = DateTime.now();
    final cached = AttendanceCacheModel()
      ..id = null
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

  /// Sync all unsynced cached attendances via the idempotent /sync endpoint.
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

  /// Reset today's attendance (admin/testing only — always hits API).
  Future<Map<String, dynamic>> resetTodayAttendance() async {
    final response = await _remoteDataSource.resetTodayAttendance();
    await _cacheDataSource.cacheTodayAttendance({'attendance': null, 'schedule': null});
    return response;
  }

  String _formatDate(DateTime dt) {
    return '${dt.year}-${dt.month.toString().padLeft(2, '0')}-${dt.day.toString().padLeft(2, '0')}';
  }
}
