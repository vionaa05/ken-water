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

  static Future<Map<String, dynamic>> login(
      String loginInput, String password) async {
    try {
      final response = await http
          .post(
            Uri.parse(ApiConstants.login),
            headers: _getHeaders(withAuth: false),
            body: jsonEncode({
              'login': loginInput.trim(),
              'password': password,
            }),
          )
          .timeout(const Duration(seconds: 15));

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
          'message': data['message'] ??
              'Login gagal. Periksa kembali kredensial Anda.',
        };
      }
    } catch (e) {
      return {
        'success': false,
        'message':
            'Gagal terhubung ke server.\nPastikan server aktif dan IP benar.',
      };
    }
  }

  static Future<Map<String, dynamic>> register({
    required String name,
    required String phone,
    String? email,
    required String address,
    required String password,
    required String passwordConfirmation,
  }) async {
    try {
      final response = await http
          .post(
            Uri.parse(ApiConstants.register),
            headers: _getHeaders(withAuth: false),
            body: jsonEncode({
              'name': name.trim(),
              'phone': phone.trim(),
              'email': email?.trim(),
              'address': address.trim(),
              'password': password,
              'password_confirmation': passwordConfirmation,
            }),
          )
          .timeout(const Duration(seconds: 15));

      final data = jsonDecode(response.body);

      if ((response.statusCode == 200 || response.statusCode == 201) &&
          data['success'] == true) {
        final token = data['token'];
        final user = UserModel.fromJson(data['user']);

        await StorageService.saveToken(token);
        await StorageService.saveUser(user);

        return {
          'success': true,
          'message': data['message'] ?? 'Registrasi berhasil',
          'user': user,
        };
      } else {
        return {
          'success': false,
          'message': data['message'] ?? 'Registrasi gagal.',
        };
      }
    } catch (e) {
      return {
        'success': false,
        'message': 'Gagal terhubung ke server.',
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
    } finally {
      await StorageService.clear();
    }
  }

  // ==========================================
  // CUSTOMER
  // ==========================================

  static Future<Map<String, dynamic>> getCustomerMemberCard() async {
    try {
      final response = await http.get(
        Uri.parse(ApiConstants.customerMemberCard),
        headers: _getHeaders(),
      ).timeout(const Duration(seconds: 10));

      final data = jsonDecode(response.body);
      if (response.statusCode == 200 && data['success'] == true) {
        return {'success': true, 'card': data['card']};
      }
      return {
        'success': false,
        'message': data['message'] ?? 'Gagal memuat kartu member'
      };
    } catch (e) {
      return {'success': false, 'message': 'Gagal terhubung ke server'};
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

  static Future<Map<String, dynamic>> createOrder({
    required int gallonQty,
    required String deliveryMethod,
    String? deliveryAddress,
    required String paymentMethod,
    String? notes,
  }) async {
    try {
      final response = await http
          .post(
            Uri.parse(ApiConstants.customerCreateOrder),
            headers: _getHeaders(),
            body: jsonEncode({
              'gallon_qty': gallonQty,
              'delivery_method': deliveryMethod,
              'delivery_address': deliveryAddress,
              'payment_method': paymentMethod,
              'notes': notes,
            }),
          )
          .timeout(const Duration(seconds: 10));

      final data = jsonDecode(response.body);
      if ((response.statusCode == 200 || response.statusCode == 201) &&
          data['success'] == true) {
        return {'success': true, 'message': data['message']};
      }
      return {
        'success': false,
        'message': data['message'] ?? 'Gagal membuat pesanan'
      };
    } catch (e) {
      return {'success': false, 'message': 'Gagal terhubung ke server'};
    }
  }

  static Future<Map<String, dynamic>> updateProfile({
    String? name,
    String? phone,
    String? address,
    String? password,
    String? passwordConfirmation,
  }) async {
    try {
      final body = <String, dynamic>{};
      if (name != null) body['name'] = name;
      if (phone != null) body['phone'] = phone;
      if (address != null) body['address'] = address;
      if (password != null) {
        body['password'] = password;
        body['password_confirmation'] = passwordConfirmation ?? password;
      }

      final response = await http
          .put(
            Uri.parse(ApiConstants.customerProfile),
            headers: _getHeaders(),
            body: jsonEncode(body),
          )
          .timeout(const Duration(seconds: 10));

      final data = jsonDecode(response.body);
      if (response.statusCode == 200 && data['success'] == true) {
        final user = UserModel.fromJson(data['user']);
        await StorageService.saveUser(user);
        return {'success': true, 'message': data['message'], 'user': user};
      }
      return {
        'success': false,
        'message': data['message'] ?? 'Gagal memperbarui profil'
      };
    } catch (e) {
      return {'success': false, 'message': 'Gagal terhubung ke server'};
    }
  }

  static Future<Map<String, dynamic>> getRewards() async {
    try {
      final response = await http.get(
        Uri.parse(ApiConstants.customerRewards),
        headers: _getHeaders(),
      ).timeout(const Duration(seconds: 10));

      final data = jsonDecode(response.body);
      if (response.statusCode == 200 && data['success'] == true) {
        return {
          'success': true,
          'rewards': data['rewards'] ?? [],
          'user_points': data['user_points'] ?? 0,
        };
      }
      return {'success': false, 'rewards': [], 'user_points': 0};
    } catch (_) {
      return {'success': false, 'rewards': [], 'user_points': 0};
    }
  }

  static Future<Map<String, dynamic>> redeemReward(int rewardId) async {
    try {
      final response = await http
          .post(
            Uri.parse(ApiConstants.customerRedeemReward(rewardId)),
            headers: _getHeaders(),
          )
          .timeout(const Duration(seconds: 10));

      final data = jsonDecode(response.body);
      return {
        'success': data['success'] ?? false,
        'message': data['message'] ?? '',
        'remaining_points': data['remaining_points'] ?? 0,
      };
    } catch (e) {
      return {'success': false, 'message': 'Gagal terhubung ke server'};
    }
  }

  static Future<Map<String, dynamic>> getVouchers() async {
    try {
      final response = await http.get(
        Uri.parse(ApiConstants.customerVouchers),
        headers: _getHeaders(),
      ).timeout(const Duration(seconds: 10));

      final data = jsonDecode(response.body);
      if (response.statusCode == 200 && data['success'] == true) {
        return {'success': true, 'vouchers': data['vouchers'] ?? []};
      }
      return {'success': false, 'vouchers': []};
    } catch (_) {
      return {'success': false, 'vouchers': []};
    }
  }

  static Future<List<dynamic>> getComplaints() async {
    try {
      final response = await http.get(
        Uri.parse(ApiConstants.customerComplaints),
        headers: _getHeaders(),
      ).timeout(const Duration(seconds: 10));

      final data = jsonDecode(response.body);
      if (response.statusCode == 200 && data['success'] == true) {
        return data['complaints'] ?? [];
      }
      return [];
    } catch (_) {
      return [];
    }
  }

  static Future<Map<String, dynamic>> createComplaint({
    required String subject,
    required String description,
    String? category,
  }) async {
    try {
      final response = await http
          .post(
            Uri.parse(ApiConstants.customerComplaints),
            headers: _getHeaders(),
            body: jsonEncode({
              'subject': subject,
              'category': category ?? subject,
              'description': description,
            }),
          )
          .timeout(const Duration(seconds: 10));

      final data = jsonDecode(response.body);
      return {
        'success': data['success'] ?? false,
        'message': data['message'] ?? '',
      };
    } catch (e) {
      return {'success': false, 'message': 'Gagal terhubung ke server'};
    }
  }

  static Future<Map<String, dynamic>> getNotifications() async {
    try {
      final response = await http.get(
        Uri.parse(ApiConstants.customerNotifications),
        headers: _getHeaders(),
      ).timeout(const Duration(seconds: 10));

      final data = jsonDecode(response.body);
      if (response.statusCode == 200 && data['success'] == true) {
        return {
          'success': true,
          'notifications': data['notifications'] ?? [],
          'unread_count': data['unread_count'] ?? 0,
        };
      }
      return {'success': false, 'notifications': [], 'unread_count': 0};
    } catch (_) {
      return {'success': false, 'notifications': [], 'unread_count': 0};
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
      return {
        'success': false,
        'message': data['message'] ?? 'Gagal memuat dashboard'
      };
    } catch (e) {
      return {'success': false, 'message': 'Gagal terhubung ke server: $e'};
    }
  }

  static Future<Map<String, dynamic>> adminSearchCustomer(String query) async {
    try {
      final uri = Uri.parse(ApiConstants.adminSearchCustomer).replace(
        queryParameters: {'query': query.trim()},
      );
      final response =
          await http.get(uri, headers: _getHeaders()).timeout(const Duration(seconds: 8));

      final data = jsonDecode(response.body);
      if (response.statusCode == 200 && data['success'] == true) {
        return {
          'success': true,
          'customer': UserModel.fromJson(data['customer']),
        };
      }
      return {
        'success': false,
        'message': data['message'] ?? 'Pelanggan tidak ditemukan'
      };
    } catch (e) {
      return {'success': false, 'message': 'Gagal mencari pelanggan: $e'};
    }
  }

  static Future<List<dynamic>> getAdminCustomers({String search = ''}) async {
    try {
      final uri = Uri.parse(ApiConstants.adminCustomers).replace(
        queryParameters: search.isNotEmpty ? {'search': search} : null,
      );
      final response =
          await http.get(uri, headers: _getHeaders()).timeout(const Duration(seconds: 10));

      final data = jsonDecode(response.body);
      if (response.statusCode == 200 && data['success'] == true) {
        return data['customers'] ?? [];
      }
      return [];
    } catch (_) {
      return [];
    }
  }

  static Future<Map<String, dynamic>> adminCreateTransaction({
    required String customerId,
    required int gallonQty,
    required String paymentMethod,
    String? notes,
  }) async {
    try {
      final response = await http
          .post(
            Uri.parse(ApiConstants.adminTransactions),
            headers: _getHeaders(),
            body: jsonEncode({
              'customer_id': customerId,
              'gallon_qty': gallonQty,
              'payment_method': paymentMethod,
              'notes': notes,
            }),
          )
          .timeout(const Duration(seconds: 10));

      final data = jsonDecode(response.body);
      if (response.statusCode == 200 && data['success'] == true) {
        return {
          'success': true,
          'message': data['message'],
          'order': data['order'],
        };
      }
      return {
        'success': false,
        'message': data['message'] ?? 'Gagal memproses transaksi'
      };
    } catch (e) {
      return {'success': false, 'message': 'Gagal terhubung ke server: $e'};
    }
  }

  static Future<List<OrderModel>> getAdminOrders() async {
    final res = await getAdminOrdersData();
    return (res['orders'] as List<OrderModel>?) ?? [];
  }

  static Future<Map<String, dynamic>> getAdminOrdersData() async {
    try {
      final response = await http.get(
        Uri.parse(ApiConstants.adminOrders),
        headers: _getHeaders(),
      ).timeout(const Duration(seconds: 10));

      final data = jsonDecode(response.body);
      if (response.statusCode == 200 && data['success'] == true) {
        final List ordersJson = data['orders'] ?? [];
        final orders = ordersJson.map((item) => OrderModel.fromJson(item)).toList();
        return {
          'success': true,
          'orders': orders,
          'status_counts': data['status_counts'] ?? {},
        };
      }
      return {'success': false, 'orders': <OrderModel>[], 'status_counts': {}};
    } catch (_) {
      return {'success': false, 'orders': <OrderModel>[], 'status_counts': {}};
    }
  }

  static Future<Map<String, dynamic>> adminUpdateOrderStatus(
      int orderId, String status) async {
    try {
      final response = await http
          .patch(
            Uri.parse(ApiConstants.adminUpdateOrderStatus(orderId)),
            headers: _getHeaders(),
            body: jsonEncode({'status': status}),
          )
          .timeout(const Duration(seconds: 10));

      final data = jsonDecode(response.body);
      return {
        'success': data['success'] ?? false,
        'message': data['message'] ?? '',
      };
    } catch (e) {
      return {'success': false, 'message': 'Gagal terhubung ke server'};
    }
  }

  static Future<List<dynamic>> getAdminComplaints() async {
    try {
      final response = await http.get(
        Uri.parse(ApiConstants.adminComplaints),
        headers: _getHeaders(),
      ).timeout(const Duration(seconds: 10));

      final data = jsonDecode(response.body);
      if (response.statusCode == 200 && data['success'] == true) {
        return data['complaints'] ?? [];
      }
      return [];
    } catch (_) {
      return [];
    }
  }

  static Future<Map<String, dynamic>> adminUpdateComplaintStatus(
      int id, String status, {String? adminNotes}) async {
    try {
      final body = <String, dynamic>{'status': status};
      if (adminNotes != null) body['admin_notes'] = adminNotes;

      final response = await http
          .patch(
            Uri.parse(ApiConstants.adminUpdateComplaintStatus(id)),
            headers: _getHeaders(),
            body: jsonEncode(body),
          )
          .timeout(const Duration(seconds: 10));

      final data = jsonDecode(response.body);
      return {
        'success': data['success'] ?? false,
        'message': data['message'] ?? '',
      };
    } catch (e) {
      return {'success': false, 'message': 'Gagal terhubung ke server'};
    }
  }

  static Future<List<dynamic>> getAdminRedemptions() async {
    try {
      final response = await http.get(
        Uri.parse(ApiConstants.adminRedemptions),
        headers: _getHeaders(),
      ).timeout(const Duration(seconds: 10));

      final data = jsonDecode(response.body);
      if (response.statusCode == 200 && data['success'] == true) {
        return data['redemptions'] ?? [];
      }
      return [];
    } catch (_) {
      return [];
    }
  }

  static Future<Map<String, dynamic>> adminValidateRedemption(
      int id, String status) async {
    try {
      final response = await http
          .patch(
            Uri.parse(ApiConstants.adminValidateRedemption(id)),
            headers: _getHeaders(),
            body: jsonEncode({'status': status}),
          )
          .timeout(const Duration(seconds: 10));

      final data = jsonDecode(response.body);
      return {
        'success': data['success'] ?? false,
        'message': data['message'] ?? '',
      };
    } catch (e) {
      return {'success': false, 'message': 'Gagal terhubung ke server'};
    }
  }

  static Future<Map<String, dynamic>> adminCancelRedemption(int id) async {
    try {
      final response = await http
          .patch(
            Uri.parse(ApiConstants.adminCancelRedemption(id)),
            headers: _getHeaders(),
            body: jsonEncode({}),
          )
          .timeout(const Duration(seconds: 10));

      final data = jsonDecode(response.body);
      return {
        'success': data['success'] ?? false,
        'message': data['message'] ?? '',
      };
    } catch (e) {
      return {'success': false, 'message': 'Gagal terhubung ke server'};
    }
  }

  // ==========================================
  // ADMIN REWARD MANAGEMENT
  // ==========================================

  static Future<List<dynamic>> getAdminRewards() async {
    try {
      final response = await http.get(
        Uri.parse(ApiConstants.adminRewards),
        headers: _getHeaders(),
      ).timeout(const Duration(seconds: 10));

      final data = jsonDecode(response.body);
      if (response.statusCode == 200 && data['success'] == true) {
        return data['rewards'] ?? [];
      }
      return [];
    } catch (_) {
      return [];
    }
  }

  static Future<Map<String, dynamic>> adminCreateReward({
    required String name,
    String? description,
    required int pointsCost,
    int? stock,
    bool isActive = true,
  }) async {
    try {
      final body = <String, dynamic>{
        'name': name,
        'points_cost': pointsCost,
        'is_active': isActive,
      };
      if (description != null) body['description'] = description;
      if (stock != null) body['stock'] = stock;

      final response = await http
          .post(
            Uri.parse(ApiConstants.adminRewards),
            headers: _getHeaders(),
            body: jsonEncode(body),
          )
          .timeout(const Duration(seconds: 10));

      final data = jsonDecode(response.body);
      return {
        'success': data['success'] ?? false,
        'message': data['message'] ?? '',
      };
    } catch (e) {
      return {'success': false, 'message': 'Gagal terhubung ke server'};
    }
  }

  static Future<Map<String, dynamic>> adminUpdateReward(
      int id, Map<String, dynamic> updates) async {
    try {
      final response = await http
          .put(
            Uri.parse(ApiConstants.adminRewardDetail(id)),
            headers: _getHeaders(),
            body: jsonEncode(updates),
          )
          .timeout(const Duration(seconds: 10));

      final data = jsonDecode(response.body);
      return {
        'success': data['success'] ?? false,
        'message': data['message'] ?? '',
      };
    } catch (e) {
      return {'success': false, 'message': 'Gagal terhubung ke server'};
    }
  }

  static Future<Map<String, dynamic>> adminDeleteReward(int id) async {
    try {
      final response = await http
          .delete(
            Uri.parse(ApiConstants.adminRewardDetail(id)),
            headers: _getHeaders(),
          )
          .timeout(const Duration(seconds: 10));

      final data = jsonDecode(response.body);
      return {
        'success': data['success'] ?? false,
        'message': data['message'] ?? '',
      };
    } catch (e) {
      return {'success': false, 'message': 'Gagal terhubung ke server'};
    }
  }
}
