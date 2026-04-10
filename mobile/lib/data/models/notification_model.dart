class NotificationModel {
  final String id;
  final String title;
  final String message;
  final String type;
  final String time;
  final bool isRead;

  const NotificationModel({
    required this.id,
    required this.title,
    required this.message,
    required this.type,
    required this.time,
    this.isRead = false,
  });

  factory NotificationModel.fromJson(Map<String, dynamic> json) {
    // Handle Laravel notification format
    // data column contains: {title, message, type}
    // read_at column indicates if read
    final data = json['data'];
    String title = '';
    String message = '';
    String type = 'general';

    if (data is Map) {
      title = data['title'] ?? '';
      message = data['message'] ?? '';
      type = data['type'] ?? 'general';
    }

    // Check read_at for isRead
    final readAt = json['read_at'];
    final isRead = readAt != null;

    return NotificationModel(
      id: json['id']?.toString() ?? '',
      title: title,
      message: message,
      type: type,
      time: _formatTimeAgo(json['created_at']),
      isRead: isRead,
    );
  }

  NotificationModel copyWith({
    String? id,
    String? title,
    String? message,
    String? type,
    String? time,
    bool? isRead,
  }) {
    return NotificationModel(
      id: id ?? this.id,
      title: title ?? this.title,
      message: message ?? this.message,
      type: type ?? this.type,
      time: time ?? this.time,
      isRead: isRead ?? this.isRead,
    );
  }

  static String _formatTimeAgo(dynamic createdAt) {
    if (createdAt == null) return 'Just now';
    try {
      final dateTime = DateTime.parse(createdAt.toString());
      final now = DateTime.now();
      final difference = now.difference(dateTime);

      if (difference.inDays > 0) {
        return '${difference.inDays}d ago';
      } else if (difference.inHours > 0) {
        return '${difference.inHours}h ago';
      } else if (difference.inMinutes > 0) {
        return '${difference.inMinutes}m ago';
      } else {
        return 'Just now';
      }
    } catch (e) {
      return 'Just now';
    }
  }
}
