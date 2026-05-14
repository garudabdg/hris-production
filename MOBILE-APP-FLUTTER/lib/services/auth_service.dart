import 'dart:convert';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import '../core/constants/app_constants.dart';
import '../models/user.dart';
import 'api_service.dart';

class AuthService {
  final ApiService _api = ApiService();
  final FlutterSecureStorage _storage = const FlutterSecureStorage();

  Future<Map<String, dynamic>> login(String nik, String password) async {
    try {
      final response = await _api.post('/karyawan/login', data: {
        'nik': nik,
        'password': password,
      });

      if (response.statusCode == 200) {
        final data = response.data;
        
        if (data['success'] == true) {
          final token = data['data']['token'];
          final user = User.fromJson(data['data']['user']);
          
          await _storage.write(key: AppConstants.keyToken, value: token);
          await _storage.write(
            key: AppConstants.keyUser,
            value: jsonEncode(user.toJson()),
          );

          return {'success': true, 'user': user};
        }
      }

      return {
        'success': false,
        'message': response.data['message'] ?? 'Login gagal'
      };
    } catch (e) {
      return {'success': false, 'message': 'Terjadi kesalahan: $e'};
    }
  }

  Future<void> logout() async {
    try {
      await _api.post('/karyawan/logout');
    } catch (e) {
      // Ignore error, tetap logout lokal
    }

    await _storage.delete(key: AppConstants.keyToken);
    await _storage.delete(key: AppConstants.keyUser);
  }

  Future<bool> isLoggedIn() async {
    final token = await _storage.read(key: AppConstants.keyToken);
    return token != null;
  }

  Future<User?> getCurrentUser() async {
    final userJson = await _storage.read(key: AppConstants.keyUser);
    if (userJson != null) {
      return User.fromJson(jsonDecode(userJson));
    }
    return null;
  }

  Future<String?> getToken() async {
    return await _storage.read(key: AppConstants.keyToken);
  }
}
