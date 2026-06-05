class AppConstants {
  // API
  static const String baseUrl = String.fromEnvironment(
    'API_BASE_URL',
    // Bisa juga menggunakan: 'https://hris.didimax.id/api'
    defaultValue: 'https://hris.didimax.online/api',
  );
  
  // Storage Keys
  static const String keyToken = 'auth_token';
  static const String keyUser = 'user_data';
  static const String keyRememberMe = 'remember_me';
  static const String keyDeviceId = 'device_id';
  
  // App
  static const String appName = 'HRIS Didimax';
  static const String appVersion = '1.0.0';
  
  // Presensi
  static const int maxDistanceMeters = 100; // radius presensi
  static const int photoQuality = 80;
  
  // Cache
  static const Duration cacheTimeout = Duration(minutes: 5);
}
