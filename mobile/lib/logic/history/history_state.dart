import 'package:flutter/foundation.dart';

@immutable
class HistoryState {
  final bool isLoading;
  final List<AttendanceHistoryItem> attendances;
  final int totalHadir;
  final int totalIzin;
  final int totalAlfa;
  final int totalLibur;
  final String? error;
  final int currentPage;
  final bool hasMore;

  const HistoryState({
    this.isLoading = false,
    this.attendances = const [],
    this.totalHadir = 0,
    this.totalIzin = 0,
    this.totalAlfa = 0,
    this.totalLibur = 0,
    this.error,
    this.currentPage = 1,
    this.hasMore = true,
  });

  HistoryState copyWith({
    bool? isLoading,
    List<AttendanceHistoryItem>? attendances,
    int? totalHadir,
    int? totalIzin,
    int? totalAlfa,
    int? totalLibur,
    String? error,
    int? currentPage,
    bool? hasMore,
  }) {
    return HistoryState(
      isLoading: isLoading ?? this.isLoading,
      attendances: attendances ?? this.attendances,
      totalHadir: totalHadir ?? this.totalHadir,
      totalIzin: totalIzin ?? this.totalIzin,
      totalAlfa: totalAlfa ?? this.totalAlfa,
      totalLibur: totalLibur ?? this.totalLibur,
      error: error,
      currentPage: currentPage ?? this.currentPage,
      hasMore: hasMore ?? this.hasMore,
    );
  }
}

class AttendanceHistoryItem {
  final int id;
  final String date;
  final String dayName;
  final String? checkInTime;
  final String? checkOutTime;
  final String status;
  final String? workDuration;

  AttendanceHistoryItem({
    required this.id,
    required this.date,
    required this.dayName,
    this.checkInTime,
    this.checkOutTime,
    required this.status,
    this.workDuration,
  });

  factory AttendanceHistoryItem.fromJson(Map<String, dynamic> json) {
    final dateStr = json['date'] as String? ?? '';
    final DateTime? dateTime = DateTime.tryParse(dateStr);
    final dayNames = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

    String formatTime(dynamic time) {
      if (time == null) return '-';
      if (time is String) {
        final parts = time.split(':');
        if (parts.length >= 2) {
          return '${parts[0]}:${parts[1]}';
        }
      }
      return time.toString();
    }

    return AttendanceHistoryItem(
      id: json['id'] as int,
      date: dateTime != null ? dateTime.day.toString().padLeft(2, '0') : '',
      dayName: dateTime != null ? dayNames[dateTime.weekday - 1] : '',
      checkInTime: json['clock_in_time'] != null ? formatTime(json['clock_in_time']) : null,
      checkOutTime: json['clock_out_time'] != null ? formatTime(json['clock_out_time']) : null,
      status: json['status'] as String? ?? 'pending',
      workDuration: json['work_duration'] as String?,
    );
  }
}
