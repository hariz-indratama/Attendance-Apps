// GENERATED CODE - DO NOT MODIFY BY HAND
// This file was manually created to avoid build_runner dependency during plan execution.
// In production, run `flutter pub run build_runner build` to regenerate.

part of 'attendance_cache_model.dart';

class AttendanceCacheModelAdapter extends TypeAdapter<AttendanceCacheModel> {
  @override
  final int typeId = 0;

  @override
  AttendanceCacheModel read(BinaryReader reader) {
    final numOfFields = reader.readByte();
    final fields = <int, dynamic>{
      for (int i = 0; i < numOfFields; i++) reader.readByte(): reader.read(),
    };
    return AttendanceCacheModel()
      ..id = fields[0] as int?
      ..userId = fields[1] as int
      ..date = fields[2] as String
      ..clockInTime = fields[3] as String?
      ..clockOutTime = fields[4] as String?
      ..status = fields[5] as String?
      ..clockInLatitude = fields[6] as double?
      ..clockInLongitude = fields[7] as double?
      ..clockOutLatitude = fields[8] as double?
      ..clockOutLongitude = fields[9] as double?
      ..clockInPhoto = fields[10] as String?
      ..clockOutPhoto = fields[11] as String?
      ..notes = fields[12] as String?
      ..isSynced = fields[13] as bool
      ..createdAt = fields[14] as DateTime;
  }

  @override
  void write(BinaryWriter writer, AttendanceCacheModel obj) {
    writer
      ..writeByte(15)
      ..writeByte(0)
      ..write(obj.id)
      ..writeByte(1)
      ..write(obj.userId)
      ..writeByte(2)
      ..write(obj.date)
      ..writeByte(3)
      ..write(obj.clockInTime)
      ..writeByte(4)
      ..write(obj.clockOutTime)
      ..writeByte(5)
      ..write(obj.status)
      ..writeByte(6)
      ..write(obj.clockInLatitude)
      ..writeByte(7)
      ..write(obj.clockInLongitude)
      ..writeByte(8)
      ..write(obj.clockOutLatitude)
      ..writeByte(9)
      ..write(obj.clockOutLongitude)
      ..writeByte(10)
      ..write(obj.clockInPhoto)
      ..writeByte(11)
      ..write(obj.clockOutPhoto)
      ..writeByte(12)
      ..write(obj.notes)
      ..writeByte(13)
      ..write(obj.isSynced)
      ..writeByte(14)
      ..write(obj.createdAt);
  }

  @override
  int get hashCode => typeId.hashCode;

  @override
  bool operator ==(Object other) =>
      identical(this, other) ||
      other is AttendanceCacheModelAdapter &&
          runtimeType == other.runtimeType &&
          typeId == other.typeId;
}
