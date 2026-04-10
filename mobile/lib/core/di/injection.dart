import 'package:get_it/get_it.dart';
import 'package:dio/dio.dart';
import 'package:hive_flutter/hive_flutter.dart';

import '../network/api_client.dart';
import '../../data/models/attendance_cache_model.dart';
import '../../data/sources/auth_remote_datasource.dart';
import '../../data/sources/attendance_remote_datasource.dart';
import '../../data/sources/attendance_cache_datasource.dart';
import '../../data/sources/notification_remote_datasource.dart';
import '../../data/repositories/attendance_repository.dart';
import '../../logic/auth/auth_cubit.dart';
import '../../logic/attendance/attendance_cubit.dart';
import '../../logic/history/history_cubit.dart';
import '../../logic/notification/notification_cubit.dart';

final getIt = GetIt.instance;

Future<void> setupDependencies() async {
  // Hive initialization
  await Hive.initFlutter();
  Hive.registerAdapter(AttendanceCacheModelAdapter());

  // Network
  final apiClient = ApiClient();
  getIt.registerSingleton<ApiClient>(apiClient);
  getIt.registerSingleton<Dio>(apiClient.dio);

  // Data Sources
  getIt.registerSingleton<AuthRemoteDataSource>(
    AuthRemoteDataSource(dio: getIt<Dio>()),
  );
  getIt.registerSingleton<AttendanceRemoteDataSource>(
    AttendanceRemoteDataSource(dio: getIt<Dio>()),
  );
  getIt.registerSingleton<NotificationRemoteDataSource>(
    NotificationRemoteDataSource(dio: getIt<Dio>()),
  );

  // Cache and Repository
  final attendanceCacheDataSource = AttendanceCacheDataSource();
  final attendanceRepository = AttendanceRepository(
    remoteDataSource: getIt<AttendanceRemoteDataSource>(),
    cacheDataSource: attendanceCacheDataSource,
  );
  getIt.registerSingleton<AttendanceCacheDataSource>(attendanceCacheDataSource);
  getIt.registerSingleton<AttendanceRepository>(attendanceRepository);

  // Cubits
  getIt.registerFactory<AuthCubit>(
    () => AuthCubit(authDataSource: getIt<AuthRemoteDataSource>()),
  );
  // AttendanceCubit refactored to use repository in Task 12
  getIt.registerFactory<AttendanceCubit>(
    () => AttendanceCubit(attendanceRepository: getIt<AttendanceRepository>()),
  );
  getIt.registerFactory<HistoryCubit>(
    () => HistoryCubit(attendanceDataSource: getIt<AttendanceRemoteDataSource>()),
  );
  getIt.registerFactory<NotificationCubit>(
    () => NotificationCubit(notificationDataSource: getIt<NotificationRemoteDataSource>()),
  );
}
