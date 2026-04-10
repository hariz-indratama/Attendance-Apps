import 'package:dio/dio.dart';
import '../models/notification_model.dart';

class NotificationRemoteDataSource {
  final Dio _dio;

  NotificationRemoteDataSource({required Dio dio}) : _dio = dio;

  /// Get all notifications
  Future<List<NotificationModel>> getNotifications() async {
    try {
      final response = await _dio.get('/api/notifications');
      final data = response.data;

      if (data is Map && data.containsKey('data')) {
        final list = data['data'] as List;
        return list.map((json) => NotificationModel.fromJson(json)).toList();
      } else if (data is List) {
        return data.map((json) => NotificationModel.fromJson(json)).toList();
      }
      return [];
    } on DioException catch (e) {
      if (e.response != null) {
        throw Exception(e.response?.data?['message'] ?? 'Failed to get notifications');
      }
      throw Exception('Network error: ${e.message}');
    } catch (e) {
      throw Exception('An error occurred: $e');
    }
  }

  /// Mark notification as read
  Future<bool> markAsRead(String notificationId) async {
    try {
      await _dio.put('/api/notifications/$notificationId/read');
      return true;
    } on DioException {
      return false;
    }
  }

  /// Mark all notifications as read
  Future<bool> markAllAsRead() async {
    try {
      await _dio.put('/api/notifications/read-all');
      return true;
    } on DioException {
      return false;
    }
  }

  /// Get unread notification count
  Future<int> getUnreadCount() async {
    try {
      final response = await _dio.get('/api/notifications/unread-count');
      return response.data['count'] ?? 0;
    } on DioException {
      return 0;
    }
  }
}
