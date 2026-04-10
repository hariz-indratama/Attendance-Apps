import 'package:dio/dio.dart';
import '../../core/network/api_client.dart';

class AttendanceRemoteDataSource {
  final Dio _dio;

  AttendanceRemoteDataSource({required Dio dio}) : _dio = dio;

  Map<String, dynamic> _handleError(Object e, String fallbackMessage) {
    if (e is DioException) {
      if (e.response != null) {
        return e.response?.data as Map<String, dynamic>? ??
            {'message': fallbackMessage};
      }
      return {'message': 'Network error: ${e.message}'};
    }
    return {'message': 'An error occurred: $e'};
  }

  /// Get today's attendance status
  Future<Map<String, dynamic>> getTodayAttendance() async {
    try {
      final response = await _dio.get('${ApiConfig.basePath}/attendance/today');
      return response.data as Map<String, dynamic>;
    } catch (e) {
      return _handleError(e, 'Failed to get attendance');
    }
  }

  /// Clock in
  Future<Map<String, dynamic>> clockIn({
    required double latitude,
    required double longitude,
    String? photoPath,
  }) async {
    try {
      final formData = FormData.fromMap({
        'latitude': latitude,
        'longitude': longitude,
        if (photoPath != null)
          'photo': await MultipartFile.fromFile(
            photoPath,
            filename: 'selfie_${DateTime.now().millisecondsSinceEpoch}.jpg',
          ),
      });

      final response = await _dio.post(
        '${ApiConfig.basePath}/attendance/clock-in',
        data: formData,
        options: Options(
          headers: {
            'Content-Type': 'multipart/form-data',
          },
        ),
      );
      return response.data as Map<String, dynamic>;
    } catch (e) {
      return _handleError(e, 'Clock in failed');
    }
  }

  /// Clock out
  Future<Map<String, dynamic>> clockOut({
    required double latitude,
    required double longitude,
  }) async {
    try {
      final response = await _dio.post(
        '${ApiConfig.basePath}/attendance/clock-out',
        data: {
          'latitude': latitude,
          'longitude': longitude,
        },
      );
      return response.data as Map<String, dynamic>;
    } catch (e) {
      return _handleError(e, 'Clock out failed');
    }
  }

  /// Get attendance history
  Future<Map<String, dynamic>> getHistory({
    int? month,
    int? year,
    String? status,
    int perPage = 15,
  }) async {
    try {
      final response = await _dio.get(
        '${ApiConfig.basePath}/attendance/history',
        queryParameters: {
          if (month != null) 'month': month,
          if (year != null) 'year': year,
          if (status != null) 'status': status,
          'per_page': perPage,
        },
      );
      return response.data as Map<String, dynamic>;
    } catch (e) {
      return _handleError(e, 'Failed to get history');
    }
  }

  /// Reset today's attendance for testing
  Future<Map<String, dynamic>> resetTodayAttendance() async {
    try {
      final response = await _dio.post('${ApiConfig.basePath}/attendance/reset-today');
      return response.data as Map<String, dynamic>;
    } catch (e) {
      return _handleError(e, 'Failed to reset attendance');
    }
  }

  /// Sync an offline-queued attendance record idempotently via the server.
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
        '${ApiConfig.basePath}/attendance/sync',
        data: formData,
        options: Options(
          headers: {'Content-Type': 'multipart/form-data'},
        ),
      );
      return response.data as Map<String, dynamic>;
    } catch (e) {
      return _handleError(e, 'Sync failed');
    }
  }
}
