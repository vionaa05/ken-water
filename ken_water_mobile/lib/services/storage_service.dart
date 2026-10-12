import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';
import '../models/user_model.dart';
import '../constants/api_constants.dart';

class StorageService {
  static const String _tokenKey = 'auth_token';
  static const String _userKey = 'user_data';
  static const String _baseUrlKey = 'custom_base_url';

  static SharedPreferences? _prefs;

  static Future<void> init() async {
    _prefs ??= await SharedPreferences.getInstance();
    
    // Muat custom base url jika ada tersimpan
    final savedBaseUrl = _prefs?.getString(_baseUrlKey);
    if (savedBaseUrl != null && savedBaseUrl.isNotEmpty) {
      ApiConstants.setBaseUrl(savedBaseUrl);
    }
  }

  static Future<void> saveToken(String token) async {
    await _prefs?.setString(_tokenKey, token);
  }

  static String? getToken() {
    return _prefs?.getString(_tokenKey);
  }

  static Future<void> saveUser(UserModel user) async {
    final userJson = jsonEncode(user.toJson());
    await _prefs?.setString(_userKey, userJson);
  }

  static UserModel? getUser() {
    final userString = _prefs?.getString(_userKey);
    if (userString == null) return null;
    try {
      final userMap = jsonDecode(userString);
      return UserModel.fromJson(userMap);
    } catch (_) {
      return null;
    }
  }

  static Future<void> saveBaseUrl(String url) async {
    await _prefs?.setString(_baseUrlKey, url);
    ApiConstants.setBaseUrl(url);
  }

  static String getBaseUrl() {
    return _prefs?.getString(_baseUrlKey) ?? ApiConstants.baseUrl;
  }

  static bool isLoggedIn() {
    final token = getToken();
    return token != null && token.isNotEmpty;
  }

  static Future<void> clear() async {
    await _prefs?.remove(_tokenKey);
    await _prefs?.remove(_userKey);
  }
}
