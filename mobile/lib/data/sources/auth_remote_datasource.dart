import 'package:dio/dio.dart';
import '../../core/network/api_client.dart';

class AuthRemoteDataSource {
  final Dio _dio;

  AuthRemoteDataSource({required Dio dio}) : _dio = dio;

  Future<Map<String, dynamic>> login({
    required String email,
    required String password,
  }) async {
    try {
      final response = await _dio.post(
        '${ApiConfig.basePath}/auth/login',
        data: {
          'email': email,
          'password': password,
        },
      );

      return response.data as Map<String, dynamic>;
    } on DioException catch (e) {
      if (e.response != null) {
        return e.response?.data as Map<String, dynamic>? ??
            {'message': 'Login failed'};
      }
      return {'message': 'Network error: ${e.message}'};
    } catch (e) {
      return {'message': 'An error occurred: $e'};
    }
  }

  Future<Map<String, dynamic>> register({
    required String name,
    required String email,
    required String password,
    required String passwordConfirmation,
  }) async {
    try {
      final response = await _dio.post(
        '${ApiConfig.basePath}/auth/register',
        data: {
          'name': name,
          'email': email,
          'password': password,
          'password_confirmation': passwordConfirmation,
        },
      );

      return response.data as Map<String, dynamic>;
    } on DioException catch (e) {
      if (e.response != null) {
        return e.response?.data as Map<String, dynamic>? ??
            {'message': 'Registration failed'};
      }
      return {'message': 'Network error: ${e.message}'};
    } catch (e) {
      return {'message': 'An error occurred: $e'};
    }
  }

  Future<Map<String, dynamic>> logout() async {
    try {
      final response = await _dio.post('${ApiConfig.basePath}/auth/logout');
      return response.data as Map<String, dynamic>;
    } on DioException catch (e) {
      if (e.response != null) {
        return e.response?.data as Map<String, dynamic>? ??
            {'message': 'Logout failed'};
      }
      return {'message': 'Network error: ${e.message}'};
    } catch (e) {
      return {'message': 'An error occurred: $e'};
    }
  }

  Future<Map<String, dynamic>> getCurrentUser() async {
    try {
      final response = await _dio.get('${ApiConfig.basePath}/auth/me');
      return response.data as Map<String, dynamic>;
    } on DioException catch (e) {
      if (e.response != null) {
        return e.response?.data as Map<String, dynamic>? ??
            {'message': 'Failed to get user'};
      }
      return {'message': 'Network error: ${e.message}'};
    } catch (e) {
      return {'message': 'An error occurred: $e'};
    }
  }

  Future<Map<String, dynamic>> forgotPassword({
    required String email,
  }) async {
    try {
      final response = await _dio.post(
        '${ApiConfig.basePath}/auth/forgot-password',
        data: {
          'email': email,
        },
      );

      return response.data as Map<String, dynamic>;
    } on DioException catch (e) {
      if (e.response != null) {
        return e.response?.data as Map<String, dynamic>? ??
            {'message': 'Failed to send reset email'};
      }
      return {'message': 'Network error: ${e.message}'};
    } catch (e) {
      return {'message': 'An error occurred: $e'};
    }
  }

  Future<Map<String, dynamic>> resetPassword({
    required String email,
    required String token,
    required String password,
    required String passwordConfirmation,
  }) async {
    try {
      final response = await _dio.post(
        '${ApiConfig.basePath}/auth/reset-password',
        data: {
          'email': email,
          'token': token,
          'password': password,
          'password_confirmation': passwordConfirmation,
        },
      );

      return response.data as Map<String, dynamic>;
    } on DioException catch (e) {
      if (e.response != null) {
        return e.response?.data as Map<String, dynamic>? ??
            {'message': 'Failed to reset password'};
      }
      return {'message': 'Network error: ${e.message}'};
    } catch (e) {
      return {'message': 'An error occurred: $e'};
    }
  }
}
