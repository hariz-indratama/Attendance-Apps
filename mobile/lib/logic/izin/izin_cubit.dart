import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:dio/dio.dart';
import 'izin_state.dart';
import '../../core/network/api_client.dart';

class IzinCubit extends Cubit<IzinState> {
  IzinCubit() : super(const IzinInitial());

  // Mock izin types
  static const List<Map<String, String>> izinTypes = [
    {'id': 'sakit', 'label': 'Sakit'},
    {'id': 'cuti', 'label': 'Cuti'},
    {'id': 'izin', 'label': 'Izin'},
    {'id': 'dinas_luar', 'label': 'Dinas Luar'},
  ];

  Future<void> submitIzin({
    required String type,
    required String reason,
    required DateTime startDate,
    required DateTime endDate,
    String? documentPath,
  }) async {
    emit(const IzinSubmitting());

    try {
      // Simulate API call delay
      await Future.delayed(const Duration(seconds: 1));

      // Mock success - in production, this would call API with document
      // In production, you would upload the document and send the path to backend
      emit(const IzinSuccess('Pengajuan izin berhasil! Menunggu persetujuan.'));
    } catch (e) {
      emit(IzinError(e.toString()));
    }
  }

  void loadIzinHistory() {
    emit(const IzinLoading());

    try {
      // Mock data - in production, this would fetch from API
      final mockList = [
        {
          'id': 1,
          'type': 'Sakit',
          'reason': 'Demam',
          'start_date': '2024-01-15',
          'end_date': '2024-01-16',
          'status': 'approved',
        },
        {
          'id': 2,
          'type': 'Cuti',
          'reason': 'Liburan keluarga',
          'start_date': '2024-02-01',
          'end_date': '2024-02-03',
          'status': 'pending',
        },
      ];

      emit(IzinLoaded(izinList: mockList));
    } catch (e) {
      emit(IzinError(e.toString()));
    }
  }

  void resetState() {
    emit(const IzinInitial());
  }

  Future<void> submitLembur({
    required DateTime date,
    required String startTime,
    required String endTime,
    required String reason,
    String? documentPath,
  }) async {
    emit(const IzinSubmitting());

    try {
      final apiClient = ApiClient();

      final formData = FormData.fromMap({
        'date': date.toIso8601String().split('T')[0],
        'start_time': startTime,
        'end_time': endTime,
        'reason': reason,
        if (documentPath != null)
          'document': await MultipartFile.fromFile(
            documentPath,
            filename: documentPath.split('/').last,
          ),
      });

      final response = await apiClient.dio.post(
        '/api/overtime',
        data: formData,
      );

      if (response.statusCode == 200 || response.statusCode == 201) {
        emit(const IzinSuccess('Pengajuan lembur berhasil dikirim!'));
      } else {
        emit(IzinError('Gagal mengirim pengajuan lembur'));
      }
    } on DioException catch (e) {
      final message = e.response?.data?['message'] ?? e.message ?? 'Terjadi kesalahan';
      emit(IzinError(message));
    } catch (e) {
      emit(IzinError(e.toString()));
    }
  }
}
