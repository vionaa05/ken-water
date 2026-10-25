import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:intl/intl.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../constants/app_colors.dart';
import '../../constants/api_constants.dart';
import '../../services/api_service.dart';

class AdminReportsScreen extends StatefulWidget {
  const AdminReportsScreen({super.key});

  @override
  State<AdminReportsScreen> createState() => _AdminReportsScreenState();
}

class _AdminReportsScreenState extends State<AdminReportsScreen>
    with SingleTickerProviderStateMixin {
  int _selectedMonth = DateTime.now().month;
  int _selectedYear = DateTime.now().year;
  List<int> _availableYears = [DateTime.now().year];
  String _periodLabel = '';

  Map<String, dynamic> _summary = {};
  List<dynamic> _orders = [];
  List<dynamic> _topCustomers = [];
  bool _isLoading = true;

  late TabController _tabController;
  String _orderSearchQuery = '';

  final currencyFormatter = NumberFormat.currency(
    locale: 'id_ID',
    symbol: 'Rp ',
    decimalDigits: 0,
  );

  final List<String> _monthNames = const [
    'Januari',
    'Februari',
    'Maret',
    'April',
    'Mei',
    'Juni',
    'Juli',
    'Agustus',
    'September',
    'Oktober',
    'November',
    'Desember',
  ];

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this);
    _loadReports();
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  Future<void> _loadReports() async {
    setState(() => _isLoading = true);
    final res = await ApiService.getAdminReports(
      month: _selectedMonth,
      year: _selectedYear,
    );

    if (mounted) {
      setState(() {
        _isLoading = false;
        if (res['success'] == true) {
          _periodLabel = res['period_label'] ??
              '${_monthNames[_selectedMonth - 1]} $_selectedYear';
          _selectedMonth = res['month'] ?? _selectedMonth;
          _selectedYear = res['year'] ?? _selectedYear;
          _availableYears = (res['years'] as List<int>?) ?? [_selectedYear];
          if (!_availableYears.contains(_selectedYear)) {
            _availableYears.add(_selectedYear);
          }
          _availableYears.sort((a, b) => b.compareTo(a));

          _summary = res['summary'] ?? {};
          _orders = res['orders'] ?? [];
          _topCustomers = res['top_customers'] ?? [];
        }
      });
    }
  }

  void _previousMonth() {
    setState(() {
      if (_selectedMonth == 1) {
        _selectedMonth = 12;
        _selectedYear -= 1;
      } else {
        _selectedMonth -= 1;
      }
    });
    _loadReports();
  }

  void _nextMonth() {
    setState(() {
      if (_selectedMonth == 12) {
        _selectedMonth = 1;
        _selectedYear += 1;
      } else {
        _selectedMonth += 1;
      }
    });
    _loadReports();
  }

  /// Buka halaman cetak laporan web portal di browser
  Future<void> _printReport() async {
    // Ambil base URL tanpa /api
    final base = ApiConstants.baseUrl.replaceAll(RegExp(r'/api$'), '');
    final rawUrl = '$base/admin/reports?print=1&month=$_selectedMonth&year=$_selectedYear';
    final uri = Uri.parse(rawUrl);
    if (await canLaunchUrl(uri)) {
      await launchUrl(uri, mode: LaunchMode.externalApplication);
    } else {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
              'Tidak dapat membuka browser. URL: $rawUrl',
              style: GoogleFonts.inter(fontSize: 13),
            ),
            backgroundColor: AppColors.error,
          ),
        );
      }
    }
  }

  /// Salin ringkasan laporan ke clipboard
  Future<void> _shareReport() async {
    final totalRev = (_summary['total_revenue'] as num?)?.toDouble() ?? 0;
    final totalGal = (_summary['total_gallons'] as num?)?.toInt() ?? 0;
    final totalOrd = (_summary['total_orders'] as num?)?.toInt() ?? 0;
    final onlineOrd = (_summary['online_orders'] as num?)?.toInt() ?? 0;
    final kasirOrd = (_summary['kasir_orders'] as num?)?.toInt() ?? 0;

    final label = _periodLabel.isNotEmpty
        ? _periodLabel
        : '${_monthNames[_selectedMonth - 1]} $_selectedYear';

    final text = '''
📊 LAPORAN PENJUALAN — $label

💰 Total Pendapatan : ${currencyFormatter.format(totalRev)}
📦 Total Pesanan    : $totalOrd order
💧 Total Galon      : $totalGal galon
🏪 Kasir Depot      : $kasirOrd order
📱 Online           : $onlineOrd order

(Diekspor dari Aplikasi Ken Water)''';

    await Clipboard.setData(ClipboardData(text: text));
    if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            'Ringkasan laporan disalin ke clipboard ✓',
            style: GoogleFonts.inter(fontSize: 13),
          ),
          backgroundColor: AppColors.success,
          duration: const Duration(seconds: 2),
        ),
      );
    }
  }

  String _percentChange(num current, num last) {
    if (last == 0) return current > 0 ? '+100%' : '0%';
    final pct = ((current - last) / last * 100).toStringAsFixed(1);
    return current >= last ? '+$pct%' : '$pct%';
  }

  Color _percentColor(num current, num last) =>
      current >= last ? AppColors.success : AppColors.error;

  Widget _buildSummaryItem({
    required String title,
    required String value,
    required String subtitle,
    required IconData icon,
    required Color color,
    String? badgeText,
    Color? badgeColor,
  }) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: const Color(0xFFE2E8F0)),
        boxShadow: const [
          BoxShadow(
            color: Color(0x04000000),
            blurRadius: 6,
            offset: Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(7),
                decoration: BoxDecoration(
                  color: color.withOpacity(0.12),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Icon(icon, color: color, size: 16),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: Text(
                  title,
                  style: GoogleFonts.inter(
                    fontSize: 12,
                    fontWeight: FontWeight.w600,
                    color: AppColors.textSecondary,
                  ),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
              ),
              if (badgeText != null)
                Container(
                  padding:
                      const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                  decoration: BoxDecoration(
                    color: (badgeColor ?? AppColors.success).withOpacity(0.12),
                    borderRadius: BorderRadius.circular(6),
                  ),
                  child: Text(
                    badgeText,
                    style: GoogleFonts.inter(
                      fontSize: 10,
                      fontWeight: FontWeight.bold,
                      color: badgeColor ?? AppColors.success,
                    ),
                  ),
                ),
            ],
          ),
          const SizedBox(height: 8),
          Text(
            value,
            style: GoogleFonts.inter(
              fontSize: 17,
              fontWeight: FontWeight.bold,
              color: AppColors.textPrimary,
            ),
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
          ),
          const SizedBox(height: 3),
          Text(
            subtitle,
            style: GoogleFonts.inter(
              fontSize: 11,
              color: AppColors.textMuted,
            ),
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final totalRev = (_summary['total_revenue'] as num?)?.toDouble() ?? 0;
    final lastRev = (_summary['last_revenue'] as num?)?.toDouble() ?? 0;
    final totalGal = (_summary['total_gallons'] as num?)?.toInt() ?? 0;
    final lastGal = (_summary['last_gallons'] as num?)?.toInt() ?? 0;
    final totalOrd = (_summary['total_orders'] as num?)?.toInt() ?? 0;
    final lastOrd = (_summary['last_orders'] as num?)?.toInt() ?? 0;
    final onlineOrd = (_summary['online_orders'] as num?)?.toInt() ?? 0;
    final kasirOrd = (_summary['kasir_orders'] as num?)?.toInt() ?? 0;
    final avgOrd = (_summary['avg_order'] as num?)?.toDouble() ?? 0;

    final filteredOrders = _orders.where((o) {
      if (_orderSearchQuery.isEmpty) return true;
      final q = _orderSearchQuery.toLowerCase();
      final num = (o['order_number'] ?? '').toString().toLowerCase();
      final name = (o['customer_name'] ?? '').toString().toLowerCase();
      final cid = (o['customer_id'] ?? '').toString().toLowerCase();
      return num.contains(q) || name.contains(q) || cid.contains(q);
    }).toList();

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: Colors.white,
        surfaceTintColor: Colors.white,
        elevation: 0.5,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_rounded,
              size: 20, color: AppColors.textPrimary),
          onPressed: () => Navigator.pop(context),
        ),
        title: Text(
          'Laporan Penjualan',
          style: GoogleFonts.inter(
            fontWeight: FontWeight.bold,
            fontSize: 17,
            color: AppColors.textPrimary,
          ),
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.share_outlined, color: AppColors.textSecondary),
            tooltip: 'Salin Ringkasan ke Clipboard',
            onPressed: _isLoading ? null : _shareReport,
          ),
          IconButton(
            icon: const Icon(Icons.print_rounded, color: AppColors.primary),
            tooltip: 'Cetak Laporan',
            onPressed: _isLoading ? null : _printReport,
          ),
          IconButton(
            icon: const Icon(Icons.refresh_rounded, color: AppColors.primary),
            tooltip: 'Segarkan Data',
            onPressed: _loadReports,
          ),
        ],
      ),
      body: _isLoading
          ? const Center(
              child: CircularProgressIndicator(color: AppColors.primary))
          : RefreshIndicator(
              onRefresh: _loadReports,
              color: AppColors.primary,
              child: ListView(
                padding: const EdgeInsets.all(16),
                children: [
                  // Filter Pemilih Bulan & Tahun (Sama dengan Fitur Web)
                  Container(
                    padding:
                        const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(16),
                      border: Border.all(color: const Color(0xFFE2E8F0)),
                      boxShadow: const [
                        BoxShadow(
                          color: Color(0x06000000),
                          blurRadius: 8,
                          offset: Offset(0, 2),
                        ),
                      ],
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text(
                              'Periode Laporan',
                              style: GoogleFonts.inter(
                                fontSize: 13,
                                fontWeight: FontWeight.bold,
                                color: AppColors.textPrimary,
                              ),
                            ),
                            Row(
                              children: [
                                IconButton(
                                  icon: const Icon(Icons.chevron_left_rounded,
                                      size: 24),
                                  padding: EdgeInsets.zero,
                                  constraints: const BoxConstraints(),
                                  tooltip: 'Bulan Sebelumnya',
                                  onPressed: _previousMonth,
                                ),
                                const SizedBox(width: 8),
                                Text(
                                  _periodLabel.isNotEmpty
                                      ? _periodLabel
                                      : '${_monthNames[_selectedMonth - 1]} $_selectedYear',
                                  style: GoogleFonts.inter(
                                    fontSize: 13,
                                    fontWeight: FontWeight.bold,
                                    color: AppColors.primary,
                                  ),
                                ),
                                const SizedBox(width: 8),
                                IconButton(
                                  icon: const Icon(Icons.chevron_right_rounded,
                                      size: 24),
                                  padding: EdgeInsets.zero,
                                  constraints: const BoxConstraints(),
                                  tooltip: 'Bulan Berikutnya',
                                  onPressed: _nextMonth,
                                ),
                              ],
                            ),
                          ],
                        ),
                        const Divider(height: 18),
                        Row(
                          children: [
                            // Dropdown Bulan
                            Expanded(
                              flex: 3,
                              child: Container(
                                padding:
                                    const EdgeInsets.symmetric(horizontal: 10),
                                decoration: BoxDecoration(
                                  border: Border.all(
                                      color: const Color(0xFFCBD5E1)),
                                  borderRadius: BorderRadius.circular(10),
                                ),
                                child: DropdownButtonHideUnderline(
                                  child: DropdownButton<int>(
                                    isExpanded: true,
                                    value: _selectedMonth,
                                    items: List.generate(12, (index) {
                                      final m = index + 1;
                                      return DropdownMenuItem(
                                        value: m,
                                        child: Text(
                                          _monthNames[index],
                                          style: GoogleFonts.inter(fontSize: 13),
                                        ),
                                      );
                                    }),
                                    onChanged: (val) {
                                      if (val != null) {
                                        setState(() => _selectedMonth = val);
                                        _loadReports();
                                      }
                                    },
                                  ),
                                ),
                              ),
                            ),
                            const SizedBox(width: 10),

                            // Dropdown Tahun
                            Expanded(
                              flex: 2,
                              child: Container(
                                padding:
                                    const EdgeInsets.symmetric(horizontal: 10),
                                decoration: BoxDecoration(
                                  border: Border.all(
                                      color: const Color(0xFFCBD5E1)),
                                  borderRadius: BorderRadius.circular(10),
                                ),
                                child: DropdownButtonHideUnderline(
                                  child: DropdownButton<int>(
                                    isExpanded: true,
                                    value: _selectedYear,
                                    items: _availableYears.map((y) {
                                      return DropdownMenuItem(
                                        value: y,
                                        child: Text(
                                          '$y',
                                          style: GoogleFonts.inter(fontSize: 13),
                                        ),
                                      );
                                    }).toList(),
                                    onChanged: (val) {
                                      if (val != null) {
                                        setState(() => _selectedYear = val);
                                        _loadReports();
                                      }
                                    },
                                  ),
                                ),
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),

                  const SizedBox(height: 16),

                  // Banner Pendapatan Utama
                  Container(
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      gradient: const LinearGradient(
                        colors: [Color(0xFF0F766E), Color(0xFF14B8A6)],
                        begin: Alignment.topLeft,
                        end: Alignment.bottomRight,
                      ),
                      borderRadius: BorderRadius.circular(16),
                      boxShadow: [
                        BoxShadow(
                          color: const Color(0xFF0F766E).withOpacity(0.25),
                          blurRadius: 10,
                          offset: const Offset(0, 4),
                        ),
                      ],
                    ),
                    child: Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.all(12),
                          decoration: BoxDecoration(
                            color: Colors.white.withOpacity(0.2),
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: const Icon(Icons.account_balance_wallet_rounded,
                              color: Colors.white, size: 28),
                        ),
                        const SizedBox(width: 14),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                'Total Pendapatan Periode',
                                style: GoogleFonts.inter(
                                  color: Colors.white.withOpacity(0.85),
                                  fontSize: 12,
                                ),
                              ),
                              const SizedBox(height: 2),
                              Row(
                                children: [
                                  Text(
                                    currencyFormatter.format(totalRev),
                                    style: GoogleFonts.inter(
                                      color: Colors.white,
                                      fontSize: 22,
                                      fontWeight: FontWeight.bold,
                                    ),
                                  ),
                                  const SizedBox(width: 8),
                                  Container(
                                    padding: const EdgeInsets.symmetric(
                                        horizontal: 6, vertical: 2),
                                    decoration: BoxDecoration(
                                      color: Colors.white.withOpacity(0.2),
                                      borderRadius: BorderRadius.circular(6),
                                    ),
                                    child: Text(
                                      _percentChange(totalRev, lastRev),
                                      style: GoogleFonts.inter(
                                        color: Colors.white,
                                        fontSize: 10,
                                        fontWeight: FontWeight.bold,
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 2),
                              Text(
                                '$totalOrd pesanan selesai · $totalGal galon terjual',
                                style: GoogleFonts.inter(
                                  color: Colors.white.withOpacity(0.85),
                                  fontSize: 12,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),

                  const SizedBox(height: 16),

                  // Ringkasan KPI (No Overlap - Linear Row 2x2 Layout)
                  Row(
                    children: [
                      Expanded(
                        child: _buildSummaryItem(
                          title: 'Total Galon',
                          value: '$totalGal Galon',
                          subtitle: 'Bln lalu: $lastGal galon',
                          icon: Icons.water_drop_rounded,
                          color: AppColors.info,
                          badgeText: _percentChange(totalGal, lastGal),
                          badgeColor: _percentColor(totalGal, lastGal),
                        ),
                      ),
                      const SizedBox(width: 10),
                      Expanded(
                        child: _buildSummaryItem(
                          title: 'Pesanan Selesai',
                          value: '$totalOrd Order',
                          subtitle: 'Bln lalu: $lastOrd order',
                          icon: Icons.check_circle_rounded,
                          color: AppColors.success,
                          badgeText: _percentChange(totalOrd, lastOrd),
                          badgeColor: _percentColor(totalOrd, lastOrd),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 10),
                  Row(
                    children: [
                      Expanded(
                        child: _buildSummaryItem(
                          title: 'Sumber Pesanan',
                          value: '$kasirOrd Kasir · $onlineOrd Online',
                          subtitle: totalOrd > 0
                              ? '${(kasirOrd / totalOrd * 100).toStringAsFixed(0)}% Kasir Depot'
                              : '0% Kasir',
                          icon: Icons.storefront_rounded,
                          color: const Color(0xFFF59E0B),
                        ),
                      ),
                      const SizedBox(width: 10),
                      Expanded(
                        child: _buildSummaryItem(
                          title: 'Rata-rata Order',
                          value: currencyFormatter.format(avgOrd),
                          subtitle: 'Nilai belanja per transaksi',
                          icon: Icons.calculate_rounded,
                          color: const Color(0xFF8B5CF6),
                        ),
                      ),
                    ],
                  ),

                  const SizedBox(height: 20),

                  // Tab Bar Rincian: Daftar Pesanan Selesai & Top Pelanggan
                  Container(
                    decoration: BoxDecoration(
                      color: const Color(0xFFE2E8F0),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    padding: const EdgeInsets.all(3),
                    child: TabBar(
                      controller: _tabController,
                      indicatorSize: TabBarIndicatorSize.tab,
                      indicator: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(10),
                        boxShadow: const [
                          BoxShadow(
                            color: Color(0x0A000000),
                            blurRadius: 4,
                            offset: Offset(0, 1),
                          ),
                        ],
                      ),
                      labelColor: AppColors.primary,
                      unselectedLabelColor: AppColors.textSecondary,
                      labelStyle: GoogleFonts.inter(
                          fontWeight: FontWeight.bold, fontSize: 13),
                      tabs: [
                        Tab(text: 'Daftar Pesanan (${_orders.length})'),
                        const Tab(text: 'Top 5 Pelanggan'),
                      ],
                    ),
                  ),

                  const SizedBox(height: 14),

                  // Konten Tab
                  AnimatedBuilder(
                    animation: _tabController,
                    builder: (ctx, _) {
                      if (_tabController.index == 0) {
                        return _buildOrdersList(filteredOrders);
                      } else {
                        return _buildTopCustomersList();
                      }
                    },
                  ),

                  const SizedBox(height: 40),
                ],
              ),
            ),
    );
  }

  Widget _buildOrdersList(List<dynamic> filteredOrders) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // Search Pesanan
        TextField(
          onChanged: (val) => setState(() => _orderSearchQuery = val),
          decoration: InputDecoration(
            hintText: 'Cari nomor order, nama pelanggan...',
            hintStyle: GoogleFonts.inter(fontSize: 13),
            prefixIcon: const Icon(Icons.search, size: 20),
            filled: true,
            fillColor: Colors.white,
            contentPadding:
                const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
            border: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: const BorderSide(color: Color(0xFFCBD5E1)),
            ),
            enabledBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: const BorderSide(color: Color(0xFFCBD5E1)),
            ),
          ),
        ),
        const SizedBox(height: 12),

        if (filteredOrders.isEmpty)
          Container(
            padding: const EdgeInsets.symmetric(vertical: 36, horizontal: 20),
            alignment: Alignment.center,
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(14),
            ),
            child: Column(
              children: [
                const Icon(Icons.receipt_long_outlined,
                    size: 40, color: AppColors.textMuted),
                const SizedBox(height: 8),
                Text(
                  _orderSearchQuery.isEmpty
                      ? 'Tidak ada transaksi selesai pada periode ini'
                      : 'Pesanan tidak ditemukan',
                  style: GoogleFonts.inter(
                      fontSize: 13, color: AppColors.textSecondary),
                ),
              ],
            ),
          )
        else
          ListView.separated(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            itemCount: filteredOrders.length,
            separatorBuilder: (_, __) => const SizedBox(height: 10),
            itemBuilder: (ctx, i) {
              final o = filteredOrders[i];
              final orderNum = o['order_number'] ?? '#ORD';
              final date = o['date'] ?? '-';
              final name = o['customer_name'] ?? 'Pelanggan';
              final cid = o['customer_id'] ?? '-';
              final qty = o['gallon_qty'] ?? 0;
              final price = (o['total_price'] as num?)?.toDouble() ?? 0;
              final source = o['source'] ?? 'online';
              final isKasir = source == 'kasir' || source == 'langsung';
              final payment = (o['payment_method'] ?? 'tunai').toString().toUpperCase();

              return Container(
                padding: const EdgeInsets.all(14),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(color: const Color(0xFFE2E8F0)),
                  boxShadow: const [
                    BoxShadow(
                      color: Color(0x04000000),
                      blurRadius: 6,
                      offset: Offset(0, 2),
                    ),
                  ],
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Row(
                          children: [
                            Text(
                              orderNum,
                              style: GoogleFonts.inter(
                                fontWeight: FontWeight.bold,
                                fontSize: 13,
                                color: AppColors.primary,
                              ),
                            ),
                            const SizedBox(width: 8),
                            Container(
                              padding: const EdgeInsets.symmetric(
                                  horizontal: 6, vertical: 2),
                              decoration: BoxDecoration(
                                color: isKasir
                                    ? const Color(0xFFFEF3C7)
                                    : const Color(0xFFE0F2FE),
                                borderRadius: BorderRadius.circular(4),
                              ),
                              child: Text(
                                isKasir ? 'KASIR DEPOT' : 'ONLINE',
                                style: GoogleFonts.inter(
                                  fontSize: 9,
                                  fontWeight: FontWeight.bold,
                                  color: isKasir
                                      ? const Color(0xFFB45309)
                                      : const Color(0xFF0369A1),
                                ),
                              ),
                            ),
                          ],
                        ),
                        Text(
                          date,
                          style: GoogleFonts.inter(
                            fontSize: 11,
                            color: AppColors.textMuted,
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 8),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      crossAxisAlignment: CrossAxisAlignment.end,
                      children: [
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                name,
                                style: GoogleFonts.inter(
                                  fontWeight: FontWeight.w600,
                                  fontSize: 14,
                                  color: AppColors.textPrimary,
                                ),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                              const SizedBox(height: 2),
                              Text(
                                '$cid · Bayar: $payment',
                                style: GoogleFonts.inter(
                                  fontSize: 12,
                                  color: AppColors.textSecondary,
                                ),
                              ),
                            ],
                          ),
                        ),
                        Column(
                          crossAxisAlignment: CrossAxisAlignment.end,
                          children: [
                            Text(
                              currencyFormatter.format(price),
                              style: GoogleFonts.inter(
                                fontWeight: FontWeight.bold,
                                fontSize: 14,
                                color: AppColors.textPrimary,
                              ),
                            ),
                            Text(
                              '$qty Galon',
                              style: GoogleFonts.inter(
                                fontSize: 12,
                                fontWeight: FontWeight.w500,
                                color: const Color(0xFF16A34A),
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ],
                ),
              );
            },
          ),
      ],
    );
  }

  Widget _buildTopCustomersList() {
    if (_topCustomers.isEmpty) {
      return Container(
        padding: const EdgeInsets.symmetric(vertical: 36, horizontal: 20),
        alignment: Alignment.center,
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(14),
        ),
        child: Text(
          'Belum ada data pelanggan di periode ini',
          style: GoogleFonts.inter(fontSize: 13, color: AppColors.textSecondary),
        ),
      );
    }

    final medals = ['🥇', '🥈', '🥉', '4️⃣', '5️⃣'];

    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFFE2E8F0)),
        boxShadow: const [
          BoxShadow(
            color: Color(0x05000000),
            blurRadius: 8,
            offset: Offset(0, 2),
          ),
        ],
      ),
      child: ListView.separated(
        shrinkWrap: true,
        physics: const NeverScrollableScrollPhysics(),
        itemCount: _topCustomers.length,
        separatorBuilder: (_, __) =>
            const Divider(height: 1, indent: 56, color: Color(0xFFF1F5F9)),
        itemBuilder: (ctx, i) {
          final c = _topCustomers[i];
          final level = c['level'] ?? 'bronze';
          final levelColor = level == 'gold'
              ? const Color(0xFFD97706)
              : level == 'silver'
                  ? const Color(0xFF64748B)
                  : const Color(0xFFB45309);
          final spent = (c['total_spent'] as num?)?.toDouble() ?? 0;
          final gallons = c['total_gallons'] ?? 0;
          final ordersCount = c['order_count'] ?? 0;

          return Padding(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
            child: Row(
              children: [
                Text(
                  i < medals.length ? medals[i] : '${i + 1}',
                  style: const TextStyle(fontSize: 22),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Flexible(
                            child: Text(
                              c['name'] ?? '-',
                              style: GoogleFonts.inter(
                                fontWeight: FontWeight.bold,
                                fontSize: 13,
                                color: AppColors.textPrimary,
                              ),
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                          const SizedBox(width: 6),
                          Container(
                            padding: const EdgeInsets.symmetric(
                                horizontal: 6, vertical: 1),
                            decoration: BoxDecoration(
                              color: levelColor.withOpacity(0.12),
                              borderRadius: BorderRadius.circular(4),
                            ),
                            child: Text(
                              level.toString().toUpperCase(),
                              style: GoogleFonts.inter(
                                fontSize: 9,
                                fontWeight: FontWeight.bold,
                                color: levelColor,
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 2),
                      Text(
                        c['customer_id'] ?? '',
                        style: GoogleFonts.inter(
                          fontSize: 11,
                          color: AppColors.textMuted,
                        ),
                      ),
                    ],
                  ),
                ),
                Column(
                  crossAxisAlignment: CrossAxisAlignment.end,
                  children: [
                    Text(
                      currencyFormatter.format(spent),
                      style: GoogleFonts.inter(
                        fontWeight: FontWeight.bold,
                        fontSize: 13,
                        color: AppColors.primary,
                      ),
                    ),
                    Text(
                      '$gallons galon · ${ordersCount}x order',
                      style: GoogleFonts.inter(
                        fontSize: 11,
                        color: AppColors.textMuted,
                      ),
                    ),
                  ],
                ),
              ],
            ),
          );
        },
      ),
    );
  }
}
