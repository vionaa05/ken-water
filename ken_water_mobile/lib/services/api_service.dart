import 'dart:convert';
import 'package:http/http.dart' as http;
import '../constants/api_constants.dart';
import '../models/user_model.dart';
import '../models/order_model.dart';
import 'storage_service.dart';

class ApiService {
  static Map<String, String> _getHeaders({bool withAuth = true}) {
    final headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    };
    if (withAuth) {
      final token = StorageService.getToken();
      if (token != null && token.isNotEmpty) {
        headers['Authorization'] = 'Bearer $token';
      }
    }
    return headers;
  }

  // ==========================================
  // AUTHENTICATION
  // ==========================================

  static Future<Map<String, dynamic>> login(String loginInput, String password) async {
    try {
      final response = await http.post(
        Uri.parse(ApiConstants.login),
        headers: _getHeaders(withAuth: false),
        body: jsonEncode({
          'login': loginInput.trim(),
          'password': password,
        }),
      ).timeout(const Duration(seconds: 10));

      final data = jsonDecode(response.body);

      if (response.statusCode == 200 && data['success'] == true) {
        final token = data['token'];
        final user = UserModel.fromJson(data['user']);
        
        await StorageService.saveToken(token);
        await StorageService.saveUser(user);

        return {
          'success': true,
          'message': data['message'] ?? 'Login berhasil',
          'user': user,
        };
      } else {
        return {
          'success': false,
          'message': data['message'] ?? 'Login gagal. Periksa kembali kredensial Anda.',
        };
      }
    } catch (e) {
      return {
        'success': false,
        'message': 'Gagal terhubung ke server: $e\nPastikan server aktif dan IP PC benar.',
      };
    }
  }

  static Future<void> logout() async {
    try {
      await http.post(
        Uri.parse(ApiConstants.logout),
        headers: _getHeaders(),
      ).timeout(const Duration(seconds: 5));
    } catch (_) {
      // Abaikan jika server tidak merespon saat logout
    } finally {
      await StorageService.clear();
    }
  }

  // ==========================================
  // CUSTOMER / PELANGGAN
  // ==========================================

  static Future<Map<String, dynamic>> getCustomerMemberCard() async {
    try {
      final response = await http.get(
        Uri.parse(ApiConstants.customerMemberCard),
        headers: _getHeaders(),
      ).timeout(const Duration(seconds: 10));

      final data = jsonDecode(response.body);
      if (response.statusCode == 200 && data['success'] == true) {
        return {
          'success': true,
          'card': data['card'],
        };
      }
      return {'success': false, 'message': data['message'] ?? 'Gagal memuat kartu member'};
    } catch (e) {
      return {'success': false, 'message': 'Gagal terhubung ke server: $e'};
    }
  }

  static Future<List<OrderModel>> getCustomerOrders() async {
    try {
      final response = await http.get(
        Uri.parse(ApiConstants.customerOrders),
        headers: _getHeaders(),
      ).timeout(const Duration(seconds: 10));

      final data = jsonDecode(response.body);
      if (response.statusCode == 200 && data['success'] == true) {
        final List ordersJson = data['orders'] ?? [];
        return ordersJson.map((item) => OrderModel.fromJson(item)).toList();
      }
      return [];
    } catch (_) {
      return [];
    }
  }

  // ==========================================
  // ADMIN / KASIR
  // ==========================================

  static Future<Map<String, dynamic>> getAdminDashboard() async {
    try {
      final response = await http.get(
        Uri.parse(ApiConstants.adminDashboard),
        headers: _getHeaders(),
      ).timeout(const Duration(seconds: 10));

      final data = jsonDecode(response.body);
      if (response.statusCode == 200 && data['success'] == true) {
        return {
          'success': true,
          'summary': data['summary'],
          'recent_orders': (data['recent_orders'] as List? ?? [])
              .map((item) => OrderModel.fromJson(item))
              .toList(),
        };
      }
      return {'success': false, 'message': data['message'] ?? 'Gagal memuat dashboard'};
    } catch (e) {
      return {'success': false, 'message': 'Gagal terhubung ke server: $e'};
    }
  }

  static Future<Map<String, dynamic>> adminSearchCustomer(String query) async {
    try {
      final uri = Uri.parse(ApiConstants.adminSearchCustomer).replace(
        queryParameters: {'query': query.trim()},
      );
      final response = await http.get(uri, headers: _getHeaders()).timeout(const Duration(seconds: 8));

      final data = jsonDecode(response.body);
      if (response.statusCode == 200 && data['success'] == true) {
        return {
          'success': true,
          'customer': UserModel.fromJson(data['customer']),
        };
      }
      return {'success': false, 'message': data['message'] ?? 'Pelanggan tidak ditemukan'};
    } catch (e) {
      return {'success': false, 'message': 'Gagal mencari pelanggan: $e'};
    }
  }

  static Future<Map<String, dynamic>> adminCreateTransaction({
    required String customerId,
    required int gallonQty,
    required String paymentMethod,
    String? notes,
  }) async {
    try {
      final response = await http.post(
        Uri.parse(ApiConstants.adminTransactions),
        headers: _getHeaders(),
        body: jsonEncode({
          'customer_id': customerId,
          'gallon_qty': gallonQty,
          'payment_method': paymentMethod,
          'notes': notes,
        }),
      ).timeout(const Duration(seconds: 10));

      final data = jsonDecode(response.body);
      if (response.statusCode == 200 && data['success'] == true) {
        return {
          'success': true,
          'message': data['message'],
          'order': data['order'],
        };
      }
      return {'success': false, 'message': data['message'] ?? 'Gagal memproses transaksi'};
    } catch (e) {
      return {'success': false, 'message': 'Gagal terhubung ke server: $e'};
    }
  }

  static Future<List<OrderModel>> getAdminOrders() async {
    try {
      final response = await http.get(
        Uri.parse(ApiConstants.adminOrders),
        headers: _getHeaders(),
      ).timeout(const Duration(seconds: 10));

      final data = jsonDecode(response.body);
      if (response.statusCode == 200 && data['success'] == true) {
        final List ordersJson = data['orders'] ?? [];
        return ordersJson.map((item) => OrderModel.fromJson(item)).toList();
      }
      return [];
    } catch (_) {
      return [];
    }
  }
}
