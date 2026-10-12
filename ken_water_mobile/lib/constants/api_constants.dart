class ApiConstants {
  // Default fallback base URL:
  // - 10.0.2.2 untuk Android Emulator
  // - Ganti dengan IP LAN PC (misal: 192.168.1.15) saat menggunakan HP fisik via WiFi
  static String baseUrl = 'http://192.168.1.3:8000/api';

  static void setBaseUrl(String url) {
    if (url.endsWith('/')) {
      url = url.substring(0, url.length - 1);
    }
    if (!url.endsWith('/api')) {
      url = '$url/api';
    }
    baseUrl = url;
  }

  // Endpoints
  static String get login => '$baseUrl/login';
  static String get logout => '$baseUrl/logout';
  static String get me => '$baseUrl/me';

  // Customer
  static String get customerMemberCard => '$baseUrl/customer/member-card';
  static String get customerOrders => '$baseUrl/customer/orders';

  // Admin
  static String get adminDashboard => '$baseUrl/admin/dashboard';
  static String get adminSearchCustomer => '$baseUrl/admin/customers/search';
  static String get adminTransactions => '$baseUrl/admin/transactions';
  static String get adminOrders => '$baseUrl/admin/orders';
}
