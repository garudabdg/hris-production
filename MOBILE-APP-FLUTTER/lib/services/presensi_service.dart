import 'package:dio/dio.dart';
import '../models/presensi.dart';
import 'api_service.dart';

class PresensiService {
  final ApiService _api = ApiService();

  Future<Map<String, dynamic>> getInfo() async {
    try {
      final response = await _api.get('/karyawan/presensi/info');
      if (response.statusCode == 200) {
        return response.data;
      }
      throw Exception('Gagal mengambil info presensi');
    } catch (e) {
      rethrow;
    }
  }

  Future<List<Presensi>> getPresensi({String? bulan, String? tahun}) async {
    try {
      final response = await _api.get('/karyawan/presensi', queryParameters: {
        if (bulan != null) 'bulan': bulan,
        if (tahun != null) 'tahun': tahun,
      });

      if (response.statusCode == 200) {
        final List data = response.data['data'] ?? [];
        return data.map((json) => Presensi.fromJson(json)).toList();
      }
      return [];
    } catch (e) {
      return [];
    }
  }

  Future<Map<String, dynamic>> submitPresensi({
    required String latitude,
    required String longitude,
    required String foto,
    String? type, // 'in' atau 'out'
  }) async {
    try {
      final formData = FormData.fromMap({
        'latitude': latitude,
        'longitude': longitude,
        'foto': MultipartFile.fromBytes(
          foto.codeUnits,
          filename: 'presensi_${DateTime.now().millisecondsSinceEpoch}.jpg',
        ),
        if (type != null) 'type': type,
      });

      final response = await _api.postFormData('/karyawan/presensi', formData);

      if (response.statusCode == 200 || response.statusCode == 201) {
        return {
          'success': true,
          'message': response.data['message'] ?? 'Presensi berhasil',
          'data': response.data['data'],
        };
      }

      return {
        'success': false,
        'message': response.data['message'] ?? 'Presensi gagal',
      };
    } catch (e) {
      return {
        'success': false,
        'message': 'Terjadi kesalahan: $e',
      };
    }
  }
}
