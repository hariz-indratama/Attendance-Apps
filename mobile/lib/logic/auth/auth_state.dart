import 'package:equatable/equatable.dart';

abstract class AuthState extends Equatable {
  const AuthState();

  @override
  List<Object?> get props => [];
}

class AuthInitial extends AuthState {}

class AuthLoading extends AuthState {}

class AuthAuthenticated extends AuthState {
  final String token;
  final String? userName;

  const AuthAuthenticated({
    required this.token,
    this.userName,
  });

  @override
  List<Object?> get props => [token, userName];
}

class AuthUnauthenticated extends AuthState {}

class AuthError extends AuthState {
  final String message;

  const AuthError(this.message);

  @override
  List<Object?> get props => [message];
}

class AuthForgotPasswordLoading extends AuthState {}

class AuthForgotPasswordSuccess extends AuthState {
  final String message;

  const AuthForgotPasswordSuccess(this.message);

  @override
  List<Object?> get props => [message];
}

class AuthResetPasswordLoading extends AuthState {}

class AuthResetPasswordSuccess extends AuthState {
  final String message;

  const AuthResetPasswordSuccess(this.message);

  @override
  List<Object?> get props => [message];
}
