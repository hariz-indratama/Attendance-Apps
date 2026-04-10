import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:hive_flutter/hive_flutter.dart';
import 'package:workmanager/workmanager.dart';

import 'core/di/injection.dart';
import 'logic/auth/auth_cubit.dart';
import 'logic/attendance/attendance_cubit.dart';
import 'logic/attendance/attendance_sync_cubit.dart';
import 'logic/history/history_cubit.dart';
import 'logic/notification/notification_cubit.dart';
import 'routes/app_router.dart';

const String _bgTaskName = 'attendanceSyncTask';

@pragma('vm:entry-point')
void _callbackDispatcher() {
  Workmanager().executeTask((task, inputData) async {
    if (task == _bgTaskName) {
      // Background sync handled by AttendanceSyncCubit when app wakes
      return true;
    }
    return false;
  });
}

void main() async {
  WidgetsFlutterBinding.ensureInitialized();

  // Initialize Hive for offline attendance cache
  await Hive.initFlutter();

  // Register background sync task (runs every 15 minutes when online)
  await Workmanager().registerPeriodicTask(
    _bgTaskName,
    _bgTaskName,
    frequency: const Duration(minutes: 15),
    constraints: Constraints(
      networkType: NetworkType.connected,
    ),
  );

  // Setup dependency injection (includes Hive adapter registration)
  await setupDependencies();

  runApp(const MyApp());
}

class MyApp extends StatelessWidget {
  const MyApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MultiBlocProvider(
      providers: [
        BlocProvider<AuthCubit>(
          create: (context) => getIt<AuthCubit>(),
        ),
        BlocProvider<AttendanceCubit>(
          create: (context) => getIt<AttendanceCubit>(),
        ),
        BlocProvider<AttendanceSyncCubit>(
          create: (context) => getIt<AttendanceSyncCubit>(),
        ),
        BlocProvider<HistoryCubit>(
          create: (context) => getIt<HistoryCubit>(),
        ),
        BlocProvider<NotificationCubit>(
          create: (context) => getIt<NotificationCubit>(),
        ),
      ],
      child: MaterialApp.router(
        title: 'Attendance App',
        debugShowCheckedModeBanner: false,
        theme: ThemeData(
          useMaterial3: true,
          colorScheme: ColorScheme.fromSeed(
            seedColor: const Color(0xFF4CAF50),
            brightness: Brightness.light,
          ),
        ),
        routerConfig: appRouter,
      ),
    );
  }
}
