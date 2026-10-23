class ApiConstants {
  // Default fallback base URL:
  // - 10.0.2.2 untuk Android Emulator
  // - Ganti dengan IP LAN PC (misal: 192.168.1.15) saat menggunakan HP fisik via WiFi
  static String baseUrl = 'http://192.168.1.15:8000/api';

  static void setBaseUrl(String url) {
    if (url.endsWith('/')) {
      url = url.substring(0, url.length - 1);
    }
    if (!url.endsWith('/api')) {
      url = '$url/api';
    }
    baseUrl = url;
  }

  // Auth
  static String get login => '$baseUrl/login';
  static String get register => '$baseUrl/register';
  static String get logout => '$baseUrl/logout';
  static String get me => '$baseUrl/me';

  // Customer
  static String get customerMemberCard => '$baseUrl/customer/member-card';
  static String get customerOrders => '$baseUrl/customer/orders';
  static String get customerCreateOrder => '$baseUrl/customer/orders';
  static String get customerProfile => '$baseUrl/customer/profile';
  static String get customerRewards => '$baseUrl/customer/rewards';
  static String customerRedeemReward(int id) => '$baseUrl/customer/rewards/$id/redeem';
  static String get customerVouchers => '$baseUrl/customer/vouchers';
  static String get customerComplaints => '$baseUrl/customer/complaints';
  static String customerComplaintDetail(int id) => '$baseUrl/customer/complaints/$id';
  static String get customerNotifications => '$baseUrl/customer/notifications';
  static String customerMarkNotifRead(int id) => '$baseUrl/customer/notifications/$id/read';
  static String get customerMarkAllNotifRead => '$baseUrl/customer/notifications/read-all';

  // Admin
  static String get adminDashboard => '$baseUrl/admin/dashboard';
  static String get adminSearchCustomer => '$baseUrl/admin/customers/search';
  static String get adminCustomers => '$baseUrl/admin/customers';
  static String get adminTransactions => '$baseUrl/admin/transactions';
  static String get adminOrders => '$baseUrl/admin/orders';
  static String adminUpdateOrderStatus(int id) => '$baseUrl/admin/orders/$id/status';
  static String get adminComplaints => '$baseUrl/admin/complaints';
  static String adminUpdateComplaintStatus(int id) => '$baseUrl/admin/complaints/$id/status';
  static String get adminRedemptions => '$baseUrl/admin/redemptions';
  static String adminValidateRedemption(int id) => '$baseUrl/admin/redemptions/$id/validate';
  static String adminCancelRedemption(int id) => '$baseUrl/admin/redemptions/$id/cancel';
  static String get adminRewards => '$baseUrl/admin/rewards';
  static String adminRewardDetail(int id) => '$baseUrl/admin/rewards/$id';
  static String get adminSegmentation => '$baseUrl/admin/segmentation';
}
