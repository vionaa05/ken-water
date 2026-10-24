import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:intl/intl.dart';
import '../../../constants/app_colors.dart';
import '../../../services/api_service.dart';

class AdminReportsScreen extends StatefulWidget {
  const AdminReportsScreen({super.key});

  @override
  State<AdminReportsScreen> createState() => _AdminReportsScreenState();
}

class _AdminReportsScreenState extends State<AdminReportsScreen> {
  Map<String, dynamic> _summary = {};
  List<dynamic> _dailyTrend = [];
  List<dynamic> _topCustomers = [];
  bool _isLoading = true;

  final currencyFormatter = NumberFormat.currency(
    locale: 'id_ID', symbol: 'Rp ', decimalDigits: 0,
  );

  @override
  void initState() {
    super.initState();
    _loadReports();
  }

  Future<void> _loadReports() async {
    setState(() => _isLoading = true);
    final res = await ApiService.getAdminReports();
    if (mounted) {
      setState(() {
        _isLoading = false;
        if (res['success'] == true) {
          _summary = res['summary'] ?? {};
          _dailyTrend = res['daily_trend'] ?? [];
          _topCustomers = res['top_customers'] ?? [];
        }
      });
    }
  }

  String _percentChange(num current, num last) {
    if (last == 0) return current > 0 ? '+100%' : '0%';
    final pct = ((current - last) / last * 100).toStringAsFixed(1);
    return current >= last ? '+$pct%' : '$pct%';
  }

  Color _percentColor(num current, num last) =>
      current >= last ? AppColors.success : AppColors.error;

  Widget _buildSummaryCard(String title, String value, String subtitle,
      IconData icon, Color color, {String? changeText, Color? changeColor}) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.05), blurRadius: 8, offset: const Offset(0, 2))],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(color: color.withOpacity(0.12), borderRadius: BorderRadius.circular(8)),
                child: Icon(icon, color: color, size: 18),
              ),
              const SizedBox(width: 8),
              Expanded(child: Text(title, style: GoogleFonts.inter(fontSize: 12, color: AppColors.textSecondary))),
            ],
          ),
          const SizedBox(height: 10),
          Text(value, style: GoogleFonts.inter(fontSize: 20, fontWeight: FontWeight.bold, color: AppColors.textPrimary)),
          const SizedBox(height: 4),
          Row(
            children: [
              Expanded(child: Text(subtitle, style: GoogleFonts.inter(fontSize: 11, color: AppColors.textMuted))),
              if (changeText != null)
                Text(changeText, style: GoogleFonts.inter(fontSize: 11, fontWeight: FontWeight.bold, color: changeColor ?? AppColors.success)),
            ],
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final thisRev  = (_summary['this_month_revenue']  as num?)?.toDouble() ?? 0;
    final lastRev  = (_summary['last_month_revenue']  as num?)?.toDouble() ?? 0;
    final thisGal  = (_summary['this_month_gallons']  as num?)?.toInt() ?? 0;
    final lastGal  = (_summary['last_month_gallons']  as num?)?.toInt() ?? 0;
    final thisOrd  = (_summary['this_month_orders']   as num?)?.toInt() ?? 0;
    final lastOrd  = (_summary['last_month_orders']   as num?)?.toInt() ?? 0;
    final onlineOrd= (_summary['online_orders']       as num?)?.toInt() ?? 0;
    final kasirOrd = (_summary['kasir_orders']        as num?)?.toInt() ?? 0;
    final totalOrd = onlineOrd + kasirOrd;

    final maxGal = _dailyTrend.isEmpty ? 1 :
        _dailyTrend.map((d) => (d['gallons'] as num?)?.toInt() ?? 0).reduce((a, b) => a > b ? a : b);

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0.5,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_rounded, size: 20, color: AppColors.textPrimary),
          onPressed: () => Navigator.pop(context),
        ),
        title: Text('Laporan Penjualan',
            style: GoogleFonts.inter(fontWeight: FontWeight.bold, fontSize: 18, color: AppColors.textPrimary)),
        actions: [
          IconButton(icon: const Icon(Icons.refresh_rounded, color: AppColors.primary), onPressed: _loadReports),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator(color: AppColors.primary))
          : RefreshIndicator(
              onRefresh: _loadReports,
              color: AppColors.primary,
              child: SingleChildScrollView(
                physics: const AlwaysScrollableScrollPhysics(),
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Header bulan ini
                    Container(
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(
                        gradient: const LinearGradient(
                          colors: [AppColors.primary, AppColors.accent],
                          begin: Alignment.topLeft, end: Alignment.bottomRight,
                        ),
                        borderRadius: BorderRadius.circular(16),
                      ),
                      child: Row(
                        children: [
                          const Icon(Icons.bar_chart_rounded, color: Colors.white, size: 28),
                          const SizedBox(width: 12),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text('Pendapatan Bulan Ini', style: GoogleFonts.inter(color: Colors.white70, fontSize: 12)),
                                Text(currencyFormatter.format(thisRev),
                                    style: GoogleFonts.inter(color: Colors.white, fontSize: 22, fontWeight: FontWeight.bold)),
                                Text('$thisOrd pesanan · $thisGal galon',
                                    style: GoogleFonts.inter(color: Colors.white70, fontSize: 12)),
                              ],
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 16),

                    // Grid Ringkasan
                    Text('Perbandingan vs Bulan Lalu',
                        style: GoogleFonts.inter(fontSize: 14, fontWeight: FontWeight.bold, color: AppColors.textPrimary)),
                    const SizedBox(height: 10),
                    GridView.count(
                      crossAxisCount: 2, shrinkWrap: true,
                      physics: const NeverScrollableScrollPhysics(),
                      crossAxisSpacing: 10, mainAxisSpacing: 10, childAspectRatio: 1.5,
                      children: [
                        _buildSummaryCard('Pendapatan', currencyFormatter.format(thisRev),
                          'Bln lalu: ${currencyFormatter.format(lastRev)}',
                          Icons.payments_rounded, AppColors.primary,
                          changeText: _percentChange(thisRev, lastRev), changeColor: _percentColor(thisRev, lastRev)),
                        _buildSummaryCard('Total Galon', '$thisGal Galon',
                          'Bln lalu: $lastGal galon',
                          Icons.water_drop_rounded, AppColors.info,
                          changeText: _percentChange(thisGal, lastGal), changeColor: _percentColor(thisGal, lastGal)),
                        _buildSummaryCard('Pesanan Selesai', '$thisOrd Pesanan',
                          'Bln lalu: $lastOrd pesanan',
                          Icons.check_circle_outline_rounded, AppColors.success,
                          changeText: _percentChange(thisOrd, lastOrd), changeColor: _percentColor(thisOrd, lastOrd)),
                        _buildSummaryCard('Sumber Pesanan',
                          totalOrd > 0 ? '${(onlineOrd / totalOrd * 100).toStringAsFixed(0)}% Online' : '-',
                          'Online: $onlineOrd · Kasir: $kasirOrd',
                          Icons.device_hub_rounded, AppColors.warning),
                      ],
                    ),
                    const SizedBox(height: 20),

                    // Tren 7 Hari
                    Text('Tren Galon 7 Hari Terakhir',
                        style: GoogleFonts.inter(fontSize: 14, fontWeight: FontWeight.bold, color: AppColors.textPrimary)),
                    const SizedBox(height: 10),
                    Container(
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(
                        color: Colors.white, borderRadius: BorderRadius.circular(14),
                        boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.05), blurRadius: 8, offset: const Offset(0, 2))],
                      ),
                      child: _dailyTrend.isEmpty
                          ? const Center(child: Padding(
                              padding: EdgeInsets.all(16),
                              child: Text('Belum ada data', style: TextStyle(color: AppColors.textMuted)),
                            ))
                          : SizedBox(
                              height: 140,
                              child: Row(
                                crossAxisAlignment: CrossAxisAlignment.end,
                                children: _dailyTrend.map((day) {
                                  final gallons = (day['gallons'] as num?)?.toInt() ?? 0;
                                  final barH = maxGal > 0 ? (gallons / maxGal * 100).clamp(4.0, 100.0) : 4.0;
                                  return Expanded(
                                    child: Column(
                                      mainAxisAlignment: MainAxisAlignment.end,
                                      children: [
                                        if (gallons > 0)
                                          Text('$gallons',
                                              style: GoogleFonts.inter(fontSize: 9, color: AppColors.primary, fontWeight: FontWeight.bold)),
                                        const SizedBox(height: 3),
                                        Container(
                                          margin: const EdgeInsets.symmetric(horizontal: 3),
                                          height: barH,
                                          decoration: BoxDecoration(
                                            gradient: const LinearGradient(
                                              colors: [AppColors.primary, AppColors.accent],
                                              begin: Alignment.bottomCenter, end: Alignment.topCenter,
                                            ),
                                            borderRadius: BorderRadius.circular(4),
                                          ),
                                        ),
                                        const SizedBox(height: 6),
                                        Text(day['date'] ?? '',
                                            style: GoogleFonts.inter(fontSize: 9, color: AppColors.textMuted),
                                            textAlign: TextAlign.center),
                                      ],
                                    ),
                                  );
                                }).toList(),
                              ),
                            ),
                    ),
                    const SizedBox(height: 20),

                    // Top Pelanggan
                    Text('Top 5 Pelanggan Bulan Ini',
                        style: GoogleFonts.inter(fontSize: 14, fontWeight: FontWeight.bold, color: AppColors.textPrimary)),
                    const SizedBox(height: 10),
                    if (_topCustomers.isEmpty)
                      Container(
                        padding: const EdgeInsets.symmetric(vertical: 24),
                        alignment: Alignment.center,
                        child: Text('Belum ada data', style: GoogleFonts.inter(color: AppColors.textMuted)),
                      )
                    else
                      Container(
                        decoration: BoxDecoration(
                          color: Colors.white, borderRadius: BorderRadius.circular(14),
                          boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.05), blurRadius: 8, offset: const Offset(0, 2))],
                        ),
                        child: ListView.separated(
                          shrinkWrap: true, physics: const NeverScrollableScrollPhysics(),
                          itemCount: _topCustomers.length,
                          separatorBuilder: (_, __) => const Divider(height: 1, indent: 56),
                          itemBuilder: (ctx, i) {
                            final c = _topCustomers[i];
                            final medals = ['🥇', '🥈', '🥉', '4️⃣', '5️⃣'];
                            final level = c['level'] ?? 'bronze';
                            final levelColor = level == 'gold'
                                ? const Color(0xFFD97706)
                                : level == 'silver' ? const Color(0xFF64748B) : AppColors.primary;
                            return Padding(
                              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                              child: Row(
                                children: [
                                  Text(i < medals.length ? medals[i] : '${i+1}', style: const TextStyle(fontSize: 22)),
                                  const SizedBox(width: 12),
                                  Expanded(
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        Text(c['name'] ?? '-',
                                            style: GoogleFonts.inter(fontWeight: FontWeight.w600, fontSize: 13),
                                            maxLines: 1, overflow: TextOverflow.ellipsis),
                                        Row(
                                          children: [
                                            Text(c['customer_id'] ?? '', style: GoogleFonts.inter(fontSize: 11, color: AppColors.textMuted)),
                                            const SizedBox(width: 6),
                                            Container(
                                              padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 1),
                                              decoration: BoxDecoration(
                                                color: levelColor.withOpacity(0.12), borderRadius: BorderRadius.circular(4),
                                              ),
                                              child: Text(level.toUpperCase(),
                                                  style: GoogleFonts.inter(fontSize: 9, fontWeight: FontWeight.bold, color: levelColor)),
                                            ),
                                          ],
                                        ),
                                      ],
                                    ),
                                  ),
                                  Column(
                                    crossAxisAlignment: CrossAxisAlignment.end,
                                    children: [
                                      Text(currencyFormatter.format(c['total_spent'] ?? 0),
                                          style: GoogleFonts.inter(fontWeight: FontWeight.bold, fontSize: 13, color: AppColors.primaryDark)),
                                      Text('${c['total_gallons'] ?? 0} galon · ${c['order_count'] ?? 0}x',
                                          style: GoogleFonts.inter(fontSize: 11, color: AppColors.textMuted)),
                                    ],
                                  ),
                                ],
                              ),
                            );
                          },
                        ),
                      ),
                    const SizedBox(height: 24),
                  ],
                ),
              ),
            ),
    );
  }
}
