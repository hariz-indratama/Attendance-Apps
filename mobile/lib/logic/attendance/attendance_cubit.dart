import 'package:flutter_bloc/flutter_bloc.dart';

import '../../data/repositories/attendance_repository.dart';
import 'attendance_state.dart';

class AttendanceCubit extends Cubit<AttendanceState> {
  final AttendanceRepository _attendanceRepository;

  AttendanceCubit({required AttendanceRepository attendanceRepository})
      : _attendanceRepository = attendanceRepository,
        super(const AttendanceInitial());

  Future<void> loadTodayAttendance() async {
    emit(const AttendanceLoading());

    try {
      final response = await _attendanceRepository.getTodayAttendance();

      if (response.containsKey('attendance')) {
        final attendance = response['attendance'] as Map<String, dynamic>?;
        final schedule = response['schedule'] as Map<String, dynamic>?;
        _emitLoaded(attendance, schedule);
      } else {
        emit(const AttendanceLoaded());
      }
    } catch (e) {
      emit(AttendanceError(e.toString()));
    }
  }

  Future<void> clockIn({
    required double latitude,
    required double longitude,
    String? photoPath,
  }) async {
    emit(const AttendanceLoading());

    try {
      final response = await _attendanceRepository.clockIn(
        latitude: latitude,
        longitude: longitude,
        photoPath: photoPath,
      );

      if (response.containsKey('message')) {
        final message = response['message'] as String;
        final isWithinRadius = response['is_within_radius'] as bool? ?? true;
        final newAttendance = response['attendance'] as Map<String, dynamic>?;
        final newSchedule = response['schedule'] as Map<String, dynamic>?;

        emit(AttendanceActionSuccess(
          message: message,
          isWithinRadius: isWithinRadius,
        ));

        if (newAttendance != null) {
          _emitLoaded(newAttendance, newSchedule);
        } else {
          await loadTodayAttendance();
        }
      } else {
        emit(AttendanceError(response['message']?.toString() ?? 'Clock in failed'));
      }
    } catch (e) {
      emit(AttendanceError(e.toString()));
    }
  }

  Future<void> clockOut({
    required double latitude,
    required double longitude,
  }) async {
    emit(const AttendanceLoading());

    try {
      final response = await _attendanceRepository.clockOut(
        latitude: latitude,
        longitude: longitude,
      );

      if (response.containsKey('message')) {
        final message = response['message'] as String;
        final isWithinRadius = response['is_within_radius'] as bool? ?? true;
        final newAttendance = response['attendance'] as Map<String, dynamic>?;
        final newSchedule = response['schedule'] as Map<String, dynamic>?;

        emit(AttendanceActionSuccess(
          message: message,
          isWithinRadius: isWithinRadius,
        ));

        if (newAttendance != null) {
          _emitLoaded(newAttendance, newSchedule);
        } else {
          await loadTodayAttendance();
        }
      } else {
        emit(AttendanceError(response['message']?.toString() ?? 'Clock out failed'));
      }
    } catch (e) {
      emit(AttendanceError(e.toString()));
    }
  }

  Future<void> resetTodayAttendance() async {
    try {
      final response = await _attendanceRepository.resetTodayAttendance();

      if (response['success'] == true) {
        emit(AttendanceActionSuccess(
          message: response['message'] as String? ?? 'Attendance reset successfully',
          isWithinRadius: true,
        ));
        await loadTodayAttendance();
      } else {
        emit(AttendanceError(response['message']?.toString() ?? 'Failed to reset'));
      }
    } catch (e) {
      emit(AttendanceError(e.toString()));
    }
  }

  void _emitLoaded(Map<String, dynamic>? attendance, Map<String, dynamic>? schedule) {
    final isCheckedIn = attendance?['clock_in_time'] != null;
    final isCheckedOut = attendance?['clock_out_time'] != null;

    String? checkInTime;
    String? checkOutTime;
    String? workDuration;

    if (attendance != null) {
      if (attendance['clock_in_time'] != null) {
        checkInTime = _formatTime(attendance['clock_in_time']);
      }
      if (attendance['clock_out_time'] != null) {
        checkOutTime = _formatTime(attendance['clock_out_time']);
        workDuration = _calculateDuration(
          attendance['clock_in_time'],
          attendance['clock_out_time'],
        );
      }
    }

    emit(AttendanceLoaded(
      isCheckedIn: isCheckedIn,
      isCheckedOut: isCheckedOut,
      checkInTime: checkInTime,
      checkOutTime: checkOutTime,
      workDuration: workDuration,
      status: attendance?['status'] as String?,
      attendance: attendance,
      schedule: schedule,
    ));
  }

  String _formatTime(dynamic time) {
    if (time == null) return '--:--';
    if (time is String) {
      final parts = time.split(':');
      if (parts.length >= 2) {
        return '${parts[0]}:${parts[1]}';
      }
    }
    return time.toString();
  }

  String _calculateDuration(dynamic clockIn, dynamic clockOut) {
    if (clockIn == null || clockOut == null) return '0h 0m';

    try {
      final inParts = clockIn.toString().split(':');
      final outParts = clockOut.toString().split(':');

      if (inParts.length < 2 || outParts.length < 2) return '0h 0m';

      final inMinutes = int.parse(inParts[0]) * 60 + int.parse(inParts[1]);
      final outMinutes = int.parse(outParts[0]) * 60 + int.parse(outParts[1]);

      final diffMinutes = outMinutes - inMinutes;
      final hours = diffMinutes ~/ 60;
      final minutes = diffMinutes % 60;

      return '${hours}h ${minutes}m';
    } catch (e) {
      return '0h 0m';
    }
  }
}
