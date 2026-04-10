import 'package:hive/hive.dart';

part 'attendance_cache_model.g.dart';

@HiveType(typeId: 0)
class AttendanceCacheModel extends HiveObject {
  // id is nullable — server ID is only assigned after sync
  @HiveField(0)
  int? id;

  @HiveField(1)
  late int userId;

  @HiveField(2)
  late String date;

  @HiveField(3)
  String? clockInTime;

  @HiveField(4)
  String? clockOutTime;

  @HiveField(5)
  String? status;

  @HiveField(6)
  double? clockInLatitude;

  @HiveField(7)
  double? clockInLongitude;

  @HiveField(8)
  double? clockOutLatitude;

  @HiveField(9)
  double? clockOutLongitude;

  @HiveField(10)
  String? clockInPhoto;

  @HiveField(11)
  String? clockOutPhoto;

  @HiveField(12)
  String? notes;

  @HiveField(13)
  late bool isSynced;

  @HiveField(14)
  late DateTime createdAt;

  AttendanceCacheModel();

  factory AttendanceCacheModel.fromJson(Map<String, dynamic> json, {bool synced = true}) {
    return AttendanceCacheModel()
      ..id = json['id'] as int?
      ..userId = json['user_id'] ?? 0
      ..date = json['date'] ?? ''
      ..clockInTime = json['clock_in_time']
      ..clockOutTime = json['clock_out_time']
      ..status = json['status']
      ..clockInLatitude = (json['clock_in_latitude'] as num?)?.toDouble()
      ..clockInLongitude = (json['clock_in_longitude'] as num?)?.toDouble()
      ..clockOutLatitude = (json['clock_out_latitude'] as num?)?.toDouble()
      ..clockOutLongitude = (json['clock_out_longitude'] as num?)?.toDouble()
      ..clockInPhoto = json['clock_in_photo']
      ..clockOutPhoto = json['clock_out_photo']
      ..notes = json['notes']
      ..isSynced = synced
      ..createdAt = DateTime.now();
  }

  Map<String, dynamic> toJson() => {
    'id': id,
    'user_id': userId,
    'date': date,
    'clock_in_time': clockInTime,
    'clock_out_time': clockOutTime,
    'status': status,
    'clock_in_latitude': clockInLatitude,
    'clock_in_longitude': clockInLongitude,
    'clock_out_latitude': clockOutLatitude,
    'clock_out_longitude': clockOutLongitude,
    'clock_in_photo': clockInPhoto,
    'clock_out_photo': clockOutPhoto,
    'notes': notes,
  };
}
