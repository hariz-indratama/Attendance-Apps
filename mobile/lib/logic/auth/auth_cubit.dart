import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../../data/sources/auth_remote_datasource.dart';
import 'auth_state.dart';

class AuthCubit extends Cubit<AuthState> {
  final AuthRemoteDataSource _authDataSource;

  AuthCubit({required AuthRemoteDataSource authDataSource})
      : _authDataSource = authDataSource,
        super(AuthInitial());

  Future<void> login({
    required String email,
    required String password,
  }) async {
    emit(AuthLoading());

    try {
      final response = await _authDataSource.login(email: email, password: password);

      if (response.containsKey('token')) {
        final token = response['token'] as String;
        final user = response['user'] as Map<String, dynamic>?;
        final userName = user?['name'] as String? ?? email.split('@').first;

        // Save token and email to local storage
        final prefs = await SharedPreferences.getInstance();
        await prefs.setString('auth_token', token);
        await prefs.setString('user_email', email);

        emit(AuthAuthenticated(token: token, userName: userName));
      } else {
        emit(AuthError(response['message']?.toString() ?? 'Login failed'));
      }
    } catch (e) {
      emit(AuthError(e.toString()));
    }
  }

  Future<void> register({
    required String name,
    required String email,
    required String password,
    required String passwordConfirmation,
  }) async {
    emit(AuthLoading());

    try {
      final response = await _authDataSource.register(
        name: name,
        email: email,
        password: password,
        passwordConfirmation: passwordConfirmation,
      );

      if (response.containsKey('token')) {
        final token = response['token'] as String;

        // Save token to local storage
        final prefs = await SharedPreferences.getInstance();
        await prefs.setString('auth_token', token);
        await prefs.setString('user_email', email);

        emit(AuthAuthenticated(token: token, userName: name));
      } else {
        emit(AuthError(response['message']?.toString() ?? 'Registration failed'));
      }
    } catch (e) {
      emit(AuthError(e.toString()));
    }
  }

  Future<void> checkAuthStatus() async {
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('auth_token');

    if (token != null && token.isNotEmpty) {
      emit(AuthAuthenticated(token: token));
    } else {
      emit(AuthUnauthenticated());
    }
  }

  Future<void> logout() async {
    try {
      await _authDataSource.logout();
    } catch (_) {
      // Proceed with local logout even if API call fails
    } finally {
      final prefs = await SharedPreferences.getInstance();
      await prefs.remove('auth_token');
      await prefs.remove('user_email');
      emit(AuthUnauthenticated());
    }
  }

  Future<void> forgotPassword({
    required String email,
  }) async {
    emit(AuthForgotPasswordLoading());

    try {
      final response = await _authDataSource.forgotPassword(email: email);

      if (response.containsKey('message')) {
        emit(AuthForgotPasswordSuccess(response['message'] as String));
      } else {
        emit(AuthForgotPasswordSuccess('Reset link sent to your email'));
      }
    } catch (e) {
      emit(AuthError(e.toString()));
    }
  }

  Future<void> resetPassword({
    required String email,
    required String token,
    required String password,
    required String passwordConfirmation,
  }) async {
    emit(AuthResetPasswordLoading());

    try {
      final response = await _authDataSource.resetPassword(
        email: email,
        token: token,
        password: password,
        passwordConfirmation: passwordConfirmation,
      );

      if (response.containsKey('message')) {
        emit(AuthResetPasswordSuccess(response['message'] as String));
      } else {
        emit(AuthResetPasswordSuccess('Password reset successfully'));
      }
    } catch (e) {
      emit(AuthError(e.toString()));
    }
  }
}
