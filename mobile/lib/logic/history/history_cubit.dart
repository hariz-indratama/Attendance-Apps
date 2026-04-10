import 'package:flutter_bloc/flutter_bloc.dart';

import '../../data/sources/attendance_remote_datasource.dart';
import 'history_state.dart';

class HistoryCubit extends Cubit<HistoryState> {
  final AttendanceRemoteDataSource _attendanceDataSource;

  HistoryCubit({required AttendanceRemoteDataSource attendanceDataSource})
      : _attendanceDataSource = attendanceDataSource,
        super(const HistoryState());

  Future<void> loadHistory({
    int? month,
    int? year,
    bool refresh = false,
  }) async {
    if (refresh) {
      emit(state.copyWith(isLoading: true, attendances: []));
    } else if (state.isLoading) {
      return;
    }

    emit(state.copyWith(isLoading: true));

    try {
      final response = await _attendanceDataSource.getHistory(
        month: month,
        year: year,
        perPage: 31, // Get all days of month
      );

      if (response.containsKey('data')) {
        final data = response['data'] as List<dynamic>;
        final attendances = data
            .map((item) => AttendanceHistoryItem.fromJson(item as Map<String, dynamic>))
            .toList();

        // Calculate summary
        int hadir = 0;
        int izin = 0;
        int alfa = 0;
        int libur = 0;

        for (final item in attendances) {
          switch (item.status.toLowerCase()) {
            case 'hadir':
            case 'present':
              hadir++;
              break;
            case 'izin':
            case 'leave':
              izin++;
              break;
            case 'alfa':
            case 'absent':
              alfa++;
              break;
            case 'libur':
            case 'holiday':
              libur++;
              break;
          }
        }

        emit(state.copyWith(
          isLoading: false,
          attendances: attendances,
          totalHadir: hadir,
          totalIzin: izin,
          totalAlfa: alfa,
          totalLibur: libur,
          hasMore: false,
        ));
      } else {
        emit(state.copyWith(
          isLoading: false,
          error: response['message']?.toString() ?? 'Failed to load history',
        ));
      }
    } catch (e) {
      emit(state.copyWith(
        isLoading: false,
        error: e.toString(),
      ));
    }
  }
}
