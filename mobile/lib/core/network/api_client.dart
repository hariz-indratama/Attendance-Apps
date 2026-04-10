import 'dart:io';
import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';

abstract class ApiConfig {
  // Update this constant each time you start ngrok
  // Alternatively store in SharedPreferences or env variable for team use
  static const String _defaultNgrokUrl = 'https://pok-desinential-walton.ngrok-free.dev';
  static const String _emulatorUrl = 'http://10.0.2.2:8000';
  static const String _simulatorUrl = 'http://localhost:8000';
  /// API version prefix — must match the backend route group prefix
  static const String basePath = '/api/v1';

  static String get baseUrl {
    if (kIsWeb) return _defaultNgrokUrl;

    if (Platform.isAndroid) {
      final isEmulator = Platform.environment['ANDROID_EMULATOR'] == 'true' ||
          Platform.environment['ANDROID_SDK_ROOT']?.contains('emulator') == true;
      return isEmulator ? _emulatorUrl : _defaultNgrokUrl;
    }

    if (Platform.isIOS) {
      final isSimulator = Platform.environment['SIMULATOR_DEVICE_NAME'] != null;
      return isSimulator ? _simulatorUrl : _defaultNgrokUrl;
    }

    return _defaultNgrokUrl;
  }
}

class ApiClient {
  late final Dio _dio;

  ApiClient() {
    _dio = Dio(
      BaseOptions(
        baseUrl: ApiConfig.baseUrl,
        connectTimeout: const Duration(seconds: 30),
        receiveTimeout: const Duration(seconds: 30),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
      ),
    );

    debugPrint('API Base URL: ${ApiConfig.baseUrl}');

    _dio.interceptors.add(
      InterceptorsWrapper(
        onRequest: (options, handler) async {
          final prefs = await SharedPreferences.getInstance();
          final token = prefs.getString('auth_token');
          if (token != null && token.isNotEmpty) {
            options.headers['Authorization'] = 'Bearer $token';
            debugPrint('Token added to request: ${token.substring(0, 10)}...');
          } else {
            debugPrint('WARNING: No token found!');
          }
          debugPrint('Request: ${options.method} ${options.uri}');
          debugPrint('Headers: ${options.headers}');
          return handler.next(options);
        },
        onError: (error, handler) async {
          if (error.response?.statusCode == 401) {
            try {
              final refreshResponse = await _dio.post('/api/auth/refresh');
              if (refreshResponse.statusCode == 200) {
                final opts = error.requestOptions;
                return handler.resolve(await _dio.fetch(opts));
              }
            } catch (_) {
              // Refresh failed — pass error through
            }
          }
          debugPrint('API Error: ${error.message}');
          debugPrint('Error response: ${error.response?.data}');
          debugPrint('Error status: ${error.response?.statusCode}');
          return handler.next(error);
        },
      ),
    );
  }

  Dio get dio => _dio;
}
