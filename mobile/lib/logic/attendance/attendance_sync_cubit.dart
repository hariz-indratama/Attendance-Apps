import 'dart:async';
import 'package:connectivity_plus/connectivity_plus.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../../data/repositories/attendance_repository.dart';

abstract class AttendanceSyncState {}

class AttendanceSyncInitial extends AttendanceSyncState {}

class AttendanceSyncInProgress extends AttendanceSyncState {
  final int pendingCount;
  AttendanceSyncInProgress(this.pendingCount);
}

class AttendanceSyncDone extends AttendanceSyncState {
  final int syncedCount;
  AttendanceSyncDone(this.syncedCount);
}

class AttendanceSyncError extends AttendanceSyncState {
  final String message;
  AttendanceSyncError(this.message);
}

class AttendanceSyncCubit extends Cubit<AttendanceSyncState> {
  final AttendanceRepository _repository;
  final Connectivity _connectivity;
  StreamSubscription<List<ConnectivityResult>>? _connectivitySubscription;

  AttendanceSyncCubit({
    required AttendanceRepository repository,
    Connectivity? connectivity,
  })  : _repository = repository,
        _connectivity = connectivity ?? Connectivity(),
        super(AttendanceSyncInitial());

  void startListening() {
    _connectivitySubscription = _connectivity.onConnectivityChanged.listen((result) {
      if (!result.contains(ConnectivityResult.none)) {
        syncPending();
      }
    });
  }

  Future<void> syncPending() async {
    emit(AttendanceSyncInProgress(await _repository.getUnsyncedCount()));
    try {
      final synced = await _repository.syncPendingAttendances();
      emit(AttendanceSyncDone(synced));
    } catch (e) {
      emit(AttendanceSyncError(e.toString()));
    }
  }

  @override
  Future<void> close() {
    _connectivitySubscription?.cancel();
    return super.close();
  }
}
