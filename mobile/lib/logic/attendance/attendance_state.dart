abstract class AttendanceState {
  const AttendanceState();
}

class AttendanceInitial extends AttendanceState {
  const AttendanceInitial();
}

class AttendanceLoading extends AttendanceState {
  const AttendanceLoading();
}

class AttendanceLoaded extends AttendanceState {
  final bool isCheckedIn;
  final bool isCheckedOut;
  final String? checkInTime;
  final String? checkOutTime;
  final String? workDuration;
  final String? status;
  final Map<String, dynamic>? attendance;
  final Map<String, dynamic>? schedule;

  const AttendanceLoaded({
    this.isCheckedIn = false,
    this.isCheckedOut = false,
    this.checkInTime,
    this.checkOutTime,
    this.workDuration,
    this.status,
    this.attendance,
    this.schedule,
  });

  AttendanceLoaded copyWith({
    bool? isCheckedIn,
    bool? isCheckedOut,
    String? checkInTime,
    String? checkOutTime,
    String? workDuration,
    String? status,
    Map<String, dynamic>? attendance,
    Map<String, dynamic>? schedule,
  }) {
    return AttendanceLoaded(
      isCheckedIn: isCheckedIn ?? this.isCheckedIn,
      isCheckedOut: isCheckedOut ?? this.isCheckedOut,
      checkInTime: checkInTime ?? this.checkInTime,
      checkOutTime: checkOutTime ?? this.checkOutTime,
      workDuration: workDuration ?? this.workDuration,
      status: status ?? this.status,
      attendance: attendance ?? this.attendance,
      schedule: schedule ?? this.schedule,
    );
  }
}

class AttendanceError extends AttendanceState {
  final String message;

  const AttendanceError(this.message);
}

class AttendanceActionSuccess extends AttendanceState {
  final String message;
  final bool isWithinRadius;

  const AttendanceActionSuccess({
    required this.message,
    this.isWithinRadius = true,
  });
}
