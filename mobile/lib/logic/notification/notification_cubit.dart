import 'package:flutter_bloc/flutter_bloc.dart';

import '../../data/sources/notification_remote_datasource.dart';
import 'notification_state.dart';

class NotificationCubit extends Cubit<NotificationState> {
  final NotificationRemoteDataSource _notificationDataSource;

  NotificationCubit({required NotificationRemoteDataSource notificationDataSource})
      : _notificationDataSource = notificationDataSource,
        super(const NotificationInitial());

  Future<void> loadNotifications() async {
    emit(const NotificationLoading());

    try {
      final notifications = await _notificationDataSource.getNotifications();
      final unreadCount = notifications.where((n) => !n.isRead).length;
      emit(NotificationLoaded(
        notifications: notifications,
        unreadCount: unreadCount,
      ));
    } catch (e) {
      emit(NotificationError(e.toString()));
    }
  }

  Future<void> markAsRead(String notificationId) async {
    final currentState = state;
    if (currentState is NotificationLoaded) {
      await _notificationDataSource.markAsRead(notificationId);

      final updatedNotifications = currentState.notifications.map((n) {
        if (n.id == notificationId) {
          return n.copyWith(isRead: true);
        }
        return n;
      }).toList();

      final unreadCount = updatedNotifications.where((n) => !n.isRead).length;

      emit(NotificationLoaded(
        notifications: updatedNotifications,
        unreadCount: unreadCount,
      ));
    }
  }

  Future<void> markAllAsRead() async {
    final currentState = state;
    if (currentState is NotificationLoaded) {
      await _notificationDataSource.markAllAsRead();

      final updatedNotifications = currentState.notifications
          .map((n) => n.copyWith(isRead: true))
          .toList();

      emit(NotificationLoaded(
        notifications: updatedNotifications,
        unreadCount: 0,
      ));
    }
  }

  Future<int> getUnreadCount() async {
    return await _notificationDataSource.getUnreadCount();
  }
}
