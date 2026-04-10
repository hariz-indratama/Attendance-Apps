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
