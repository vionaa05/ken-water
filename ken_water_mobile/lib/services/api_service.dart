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
    int? voucherId,
  }) async {
    try {
      final body = <String, dynamic>{
        'gallon_qty': gallonQty,
        'delivery_method': deliveryMethod,
        'delivery_address': deliveryAddress,
        'payment_method': paymentMethod,
      };
      if (notes != null && notes.isNotEmpty) body['notes'] = notes;
      if (voucherId != null) body['voucher_id'] = voucherId;

      final response = await http
          .post(
            Uri.parse(ApiConstants.customerCreateOrder),
            headers: _getHeaders(),
            body: jsonEncode(body),
          )
          .timeout(const Duration(seconds: 10));

      final data = jsonDecode(response.body);
      if ((response.statusCode == 200 || response.statusCode == 201) &&
          data['success'] == true) {
        return {'success': true, 'message': data['message'], 'order': data['order']};
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

  static Future<Map<String, dynamic>> changePassword({
    required String currentPassword,
    required String newPassword,
    required String passwordConfirmation,
  }) async {
    try {
      final response = await http
          .put(
            Uri.parse(ApiConstants.customerChangePassword),
            headers: _getHeaders(),
            body: jsonEncode({
              'current_password': currentPassword,
              'password': newPassword,
              'password_confirmation': passwordConfirmation,
            }),
          )
          .timeout(const Duration(seconds: 10));

      final data = jsonDecode(response.body);
      return {
        'success': data['success'] ?? false,
        'message': data['message'] ?? 'Gagal mengubah kata sandi',
      };
    } catch (e) {
      return {'success': false, 'message': 'Gagal terhubung ke server: $e'};
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

  static Future<bool> markNotificationRead(int id) async {
    try {
      final response = await http
          .post(
            Uri.parse(ApiConstants.customerMarkNotifRead(id)),
            headers: _getHeaders(),
          )
          .timeout(const Duration(seconds: 8));

      final data = jsonDecode(response.body);
      return data['success'] == true;
    } catch (_) {
      return false;
    }
  }

  static Future<bool> markAllNotificationsRead() async {
    try {
      final response = await http
          .post(
            Uri.parse(ApiConstants.customerMarkAllNotifRead),
            headers: _getHeaders(),
          )
          .timeout(const Duration(seconds: 8));

      final data = jsonDecode(response.body);
      return data['success'] == true;
    } catch (_) {
      return false;
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

  static Future<Map<String, dynamic>> getAdminSegmentation() async {
    try {
      final response = await http
          .get(
            Uri.parse(ApiConstants.adminSegmentation),
            headers: _getHeaders(),
          )
          .timeout(const Duration(seconds: 12));

      final data = jsonDecode(response.body);
      if (response.statusCode == 200 && data['success'] == true) {
        return {
          'success': true,
          'customers': data['customers'] ?? [],
          'chart_segment_data': data['chart_segment_data'] ?? [],
          'chart_level_data': data['chart_level_data'] ?? [],
          'summary': data['summary'] ?? {},
        };
      }
      return {'success': false, 'customers': [], 'message': data['message'] ?? 'Gagal memuat segmentasi'};
    } catch (e) {
      return {'success': false, 'customers': [], 'message': 'Gagal terhubung ke server: $e'};
    }
  }

  // ==========================================
  // CUSTOMER REACTION
  // ==========================================

  static Future<Map<String, dynamic>> reactivateAccount() async {
    try {
      final response = await http
          .post(
            Uri.parse(ApiConstants.customerReactivate),
            headers: _getHeaders(),
          )
          .timeout(const Duration(seconds: 15));

      final data = jsonDecode(response.body);
      return {
        'success': data['success'] ?? false,
        'message': data['message'] ?? 'Gagal mengaktifkan akun',
        'user': data['user'],
      };
    } catch (e) {
      return {'success': false, 'message': 'Gagal terhubung ke server: $e'};
    }
  }

  // ==========================================
  // ADMIN CUSTOMER CRUD
  // ==========================================

  static Future<Map<String, dynamic>> adminCreateCustomer({
    required String name,
    required String phone,
    required String address,
    String? birthDate,
    required String password,
  }) async {
    try {
      final body = {
        'name': name.trim(),
        'phone': phone.trim(),
        'address': address.trim(),
        'password': password,
      };
      if (birthDate != null && birthDate.isNotEmpty) {
        body['birth_date'] = birthDate;
      }

      final response = await http
          .post(
            Uri.parse(ApiConstants.adminCustomers),
            headers: _getHeaders(),
            body: jsonEncode(body),
          )
          .timeout(const Duration(seconds: 12));

      final data = jsonDecode(response.body);
      return {
        'success': data['success'] ?? false,
        'message': data['message'] ?? 'Gagal menambahkan pelanggan',
        'customer': data['customer'],
      };
    } catch (e) {
      return {'success': false, 'message': 'Gagal terhubung ke server: $e'};
    }
  }

  static Future<Map<String, dynamic>> adminUpdateCustomer(
    int id, {
    required String name,
    required String phone,
    required String address,
    String? birthDate,
    String? status,
    String? password,
  }) async {
    try {
      final body = <String, dynamic>{
        'name': name.trim(),
        'phone': phone.trim(),
        'address': address.trim(),
      };
      if (birthDate != null && birthDate.isNotEmpty) body['birth_date'] = birthDate;
      if (status != null) body['status'] = status;
      if (password != null && password.isNotEmpty) body['password'] = password;

      final response = await http
          .put(
            Uri.parse(ApiConstants.adminCustomerDetail(id)),
            headers: _getHeaders(),
            body: jsonEncode(body),
          )
          .timeout(const Duration(seconds: 12));

      final data = jsonDecode(response.body);
      return {
        'success': data['success'] ?? false,
        'message': data['message'] ?? 'Gagal memperbarui pelanggan',
        'customer': data['customer'],
      };
    } catch (e) {
      return {'success': false, 'message': 'Gagal terhubung ke server: $e'};
    }
  }

  static Future<Map<String, dynamic>> adminDeleteCustomer(int id) async {
    try {
      final response = await http
          .delete(
            Uri.parse(ApiConstants.adminCustomerDetail(id)),
            headers: _getHeaders(),
          )
          .timeout(const Duration(seconds: 10));

      final data = jsonDecode(response.body);
      return {
        'success': data['success'] ?? false,
        'message': data['message'] ?? 'Gagal menghapus pelanggan',
      };
    } catch (e) {
      return {'success': false, 'message': 'Gagal terhubung ke server: $e'};
    }
  }

  // ==========================================
  // ADMIN SETTINGS
  // ==========================================

  static Future<Map<String, dynamic>> getAdminSettings() async {
    try {
      final response = await http
          .get(
            Uri.parse(ApiConstants.adminSettings),
            headers: _getHeaders(),
          )
          .timeout(const Duration(seconds: 10));

      final data = jsonDecode(response.body);
      if (response.statusCode == 200 && data['success'] == true) {
        return {'success': true, 'settings': data['settings']};
      }
      return {'success': false, 'message': data['message'] ?? 'Gagal memuat pengaturan'};
    } catch (e) {
      return {'success': false, 'message': 'Gagal terhubung ke server: $e'};
    }
  }

  static Future<Map<String, dynamic>> saveAdminSettings({
    required double pricePerGallon,
    required int pointsPerGallon,
    required int inactiveDays,
    required int silverThreshold,
    required int goldThreshold,
  }) async {
    try {
      final response = await http
          .post(
            Uri.parse(ApiConstants.adminSettings),
            headers: _getHeaders(),
            body: jsonEncode({
              'price_per_gallon': pricePerGallon,
              'points_per_gallon': pointsPerGallon,
              'inactive_days': inactiveDays,
              'silver_threshold': silverThreshold,
              'gold_threshold': goldThreshold,
            }),
          )
          .timeout(const Duration(seconds: 10));

      final data = jsonDecode(response.body);
      return {
        'success': data['success'] ?? false,
        'message': data['message'] ?? 'Gagal menyimpan pengaturan',
      };
    } catch (e) {
      return {'success': false, 'message': 'Gagal terhubung ke server: $e'};
    }
  }

  // ==========================================
  // ADMIN VOUCHERS
  // ==========================================

  static Future<Map<String, dynamic>> getAdminVouchers() async {
    try {
      final response = await http
          .get(
            Uri.parse(ApiConstants.adminVouchers),
            headers: _getHeaders(),
          )
          .timeout(const Duration(seconds: 10));

      final data = jsonDecode(response.body);
      if (response.statusCode == 200 && data['success'] == true) {
        return {'success': true, 'vouchers': data['vouchers'] ?? []};
      }
      return {'success': false, 'vouchers': [], 'message': data['message'] ?? 'Gagal memuat voucher'};
    } catch (e) {
      return {'success': false, 'vouchers': [], 'message': 'Gagal terhubung ke server: $e'};
    }
  }

  static Future<Map<String, dynamic>> adminCreateVoucher({
    required String code,
    required String name,
    String? description,
    required String discountType,
    required double discountValue,
    double? minPurchase,
    required String validFrom,
    required String validUntil,
    String usageType = 'sekali',
    int? maxUsage,
  }) async {
    try {
      final body = <String, dynamic>{
        'code': code.trim().toUpperCase(),
        'name': name.trim(),
        'discount_type': discountType,
        'discount_value': discountValue,
        'valid_from': validFrom,
        'valid_until': validUntil,
        'usage_type': usageType,
        'is_active': true,
      };
      if (description != null) body['description'] = description;
      if (minPurchase != null) body['min_purchase'] = minPurchase;
      if (maxUsage != null) body['max_usage'] = maxUsage;

      final response = await http
          .post(
            Uri.parse(ApiConstants.adminVouchers),
            headers: _getHeaders(),
            body: jsonEncode(body),
          )
          .timeout(const Duration(seconds: 10));

      final data = jsonDecode(response.body);
      return {
        'success': data['success'] ?? false,
        'message': data['message'] ?? 'Gagal membuat voucher',
      };
    } catch (e) {
      return {'success': false, 'message': 'Gagal terhubung ke server: $e'};
    }
  }

  static Future<Map<String, dynamic>> adminAssignVoucher(
      int voucherId, List<int> userIds) async {
    try {
      final response = await http
          .post(
            Uri.parse(ApiConstants.adminAssignVoucher(voucherId)),
            headers: _getHeaders(),
            body: jsonEncode({'user_ids': userIds}),
          )
          .timeout(const Duration(seconds: 12));

      final data = jsonDecode(response.body);
      return {
        'success': data['success'] ?? false,
        'message': data['message'] ?? 'Gagal membagikan voucher',
        'assigned_count': data['assigned_count'] ?? 0,
      };
    } catch (e) {
      return {'success': false, 'message': 'Gagal terhubung ke server: $e'};
    }
  }

  // ==========================================
  // ADMIN PROMOS
  // ==========================================

  static Future<Map<String, dynamic>> getAdminPromos() async {
    try {
      final response = await http
          .get(
            Uri.parse(ApiConstants.adminPromos),
            headers: _getHeaders(),
          )
          .timeout(const Duration(seconds: 10));

      final data = jsonDecode(response.body);
      if (response.statusCode == 200 && data['success'] == true) {
        return {'success': true, 'promos': data['promos'] ?? []};
      }
      return {'success': false, 'promos': [], 'message': data['message'] ?? 'Gagal memuat promo'};
    } catch (e) {
      return {'success': false, 'promos': [], 'message': 'Gagal terhubung ke server: $e'};
    }
  }

  static Future<Map<String, dynamic>> adminCreatePromo({
    required int userId,
    required String name,
    String? description,
    required String discountType,
    required double discountValue,
    required String validFrom,
    required String validUntil,
  }) async {
    try {
      final body = <String, dynamic>{
        'user_id': userId,
        'name': name.trim(),
        'discount_type': discountType,
        'discount_value': discountValue,
        'valid_from': validFrom,
        'valid_until': validUntil,
      };
      if (description != null) body['description'] = description;

      final response = await http
          .post(
            Uri.parse(ApiConstants.adminPromos),
            headers: _getHeaders(),
            body: jsonEncode(body),
          )
          .timeout(const Duration(seconds: 10));

      final data = jsonDecode(response.body);
      return {
        'success': data['success'] ?? false,
        'message': data['message'] ?? 'Gagal membuat promo',
      };
    } catch (e) {
      return {'success': false, 'message': 'Gagal terhubung ke server: $e'};
    }
  }

  // ==========================================
  // ADMIN CAMPAIGNS (WHATSAPP CRM)
  // ==========================================

  static Future<Map<String, dynamic>> getAdminCampaigns() async {
    try {
      final response = await http
          .get(
            Uri.parse(ApiConstants.adminCampaigns),
            headers: _getHeaders(),
          )
          .timeout(const Duration(seconds: 10));

      final data = jsonDecode(response.body);
      if (response.statusCode == 200 && data['success'] == true) {
        return {'success': true, 'campaigns': data['campaigns'] ?? []};
      }
      return {'success': false, 'campaigns': [], 'message': data['message'] ?? 'Gagal memuat kampanye'};
    } catch (e) {
      return {'success': false, 'campaigns': [], 'message': 'Gagal terhubung ke server: $e'};
    }
  }

  static Future<Map<String, dynamic>> adminCreateCampaign({
    required String name,
    required String type,
    required List<String> targetSegment,
    required String periodStart,
    required String periodEnd,
    required String messageTemplate,
    int? voucherId,
  }) async {
    try {
      final body = <String, dynamic>{
        'name': name.trim(),
        'type': type,
        'target_segment': targetSegment,
        'period_start': periodStart,
        'period_end': periodEnd,
        'message_template': messageTemplate,
      };
      if (voucherId != null) body['voucher_id'] = voucherId;

      final response = await http
          .post(
            Uri.parse(ApiConstants.adminCampaigns),
            headers: _getHeaders(),
            body: jsonEncode(body),
          )
          .timeout(const Duration(seconds: 12));

      final data = jsonDecode(response.body);
      return {
        'success': data['success'] ?? false,
        'message': data['message'] ?? 'Gagal membuat kampanye',
      };
    } catch (e) {
      return {'success': false, 'message': 'Gagal terhubung ke server: $e'};
    }
  }

  static Future<Map<String, dynamic>> adminDeleteCampaign(int id) async {
    try {
      final response = await http
          .delete(
            Uri.parse(ApiConstants.adminCampaignDetail(id)),
            headers: _getHeaders(),
          )
          .timeout(const Duration(seconds: 10));

      final data = jsonDecode(response.body);
      return {
        'success': data['success'] ?? false,
        'message': data['message'] ?? 'Gagal menghapus kampanye',
      };
    } catch (e) {
      return {'success': false, 'message': 'Gagal terhubung ke server: $e'};
    }
  }
}
