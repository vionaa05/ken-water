import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:qr_flutter/qr_flutter.dart';
import 'package:intl/intl.dart';
import '../../../constants/app_colors.dart';
import '../../../models/user_model.dart';
import '../../../models/order_model.dart';
import '../../../services/api_service.dart';
import '../../../services/storage_service.dart';
import '../../login_screen.dart';
import '../customer_main_screen.dart';

class HomeTab extends StatefulWidget {
  const HomeTab({super.key});

  @override
  State<HomeTab> createState() => _HomeTabState();
}

class _HomeTabState extends State<HomeTab> {
  UserModel? _user;
  Map<String, dynamic>? _cardData;
  List<OrderModel> _orders = [];
  bool _isLoading = true;

  final currencyFormatter =
      NumberFormat.currency(locale: 'id_ID', symbol: 'Rp ', decimalDigits: 0);

  @override
  void initState() {
    super.initState();
    _user = StorageService.getUser();
    _loadData();
  }

  Future<void> _loadData() async {
    setState(() => _isLoading = true);
    final cardResult = await ApiService.getCustomerMemberCard();
    final ordersList = await ApiService.getCustomerOrders();
    if (mounted) {
      setState(() {
        if (cardResult['success'] == true) _cardData = cardResult['card'];
        _orders = ordersList;
        _isLoading = false;
      });
    }
  }

  Future<void> _logout() async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Text('Konfirmasi Logout'),
        content: const Text('Apakah Anda yakin ingin keluar?'),
        actions: [
          TextButton(
              onPressed: () => Navigator.pop(ctx, false),
              child: const Text('Batal')),
          TextButton(
            onPressed: () => Navigator.pop(ctx, true),
            style: TextButton.styleFrom(foregroundColor: AppColors.error),
            child: const Text('Keluar'),
          ),
        ],
      ),
    );
    if (confirm == true) {
      await ApiService.logout();
      if (mounted) {
        Navigator.pushReplacement(
            context, MaterialPageRoute(builder: (_) => const LoginScreen()));
      }
    }
  }

  List<Color> _getTierGradient(String level) {
    switch (level.toLowerCase()) {
      case 'gold':
        return const [Color(0xFF78350F), Color(0xFFD97706), Color(0xFFF59E0B)];
      case 'silver':
        return const [Color(0xFF1E3A5F), Color(0xFF334155), Color(0xFF64748B)];
      case 'bronze':
      default:
        return const [Color(0xFF0284C7), Color(0xFF0369A1), Color(0xFF0C4A6E)];
    }
  }

  String _getTierEmoji(String level) {
    switch (level.toLowerCase()) {
      case 'gold': return '🥇';
      case 'silver': return '🥈';
      default: return '🥉';
    }
  }

  Color _getStatusColor(String status) {
    switch (status.toLowerCase()) {
      case 'selesai': return AppColors.success;
      case 'dikirim': return AppColors.info;
      case 'diproses': return AppColors.warning;
      case 'dipesan': return AppColors.accent;
      case 'dibatalkan': return AppColors.error;
      default: return AppColors.textSecondary;
    }
  }

  @override
  Widget build(BuildContext context) {
    final level = (_cardData?['loyalty_level'] ?? _user?.loyaltyLevel ?? 'bronze').toString();
    final points = _cardData?['points'] ?? _user?.points ?? 0;
    final progress = _cardData?['progress'];
    final customerId = _cardData?['customer_id'] ?? _user?.customerId ?? 'KW-MEMBER';
    final name = _cardData?['name'] ?? _user?.name ?? 'Pelanggan';
    final status = _cardData?['status'] ?? _user?.status ?? 'aktif';

    return Scaffold(
      backgroundColor: AppColors.background,
      body: RefreshIndicator(
        onRefresh: _loadData,
        color: AppColors.primary,
        child: CustomScrollView(
          slivers: [
            // App Bar
            SliverAppBar(
              expandedHeight: 0,
              floating: true,
              snap: true,
              backgroundColor: Colors.white,
              elevation: 0.5,
              title: Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(7),
                    decoration: BoxDecoration(
                      gradient: const LinearGradient(
                          colors: [AppColors.primary, AppColors.accent]),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: const Icon(Icons.water_drop,
                        color: Colors.white, size: 18),
                  ),
                  const SizedBox(width: 10),
                  Text('Ken Water',
                      style: GoogleFonts.inter(
                        fontSize: 18,
                        fontWeight: FontWeight.bold,
                        color: AppColors.textPrimary,
                      )),
                ],
              ),
              actions: [
                IconButton(
                  icon: const Icon(Icons.logout_rounded,
                      color: AppColors.textSecondary),
                  onPressed: _logout,
                ),
              ],
            ),

            if (_isLoading)
              const SliverFillRemaining(
                child: Center(
                    child: CircularProgressIndicator(color: AppColors.primary)),
              )
            else
              SliverToBoxAdapter(
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // ===================
                      // MEMBER CARD
                      // ===================
                      Container(
                        width: double.infinity,
                        decoration: BoxDecoration(
                          gradient: LinearGradient(
                            colors: _getTierGradient(level),
                            begin: Alignment.topLeft,
                            end: Alignment.bottomRight,
                          ),
                          borderRadius: BorderRadius.circular(20),
                          boxShadow: [
                            BoxShadow(
                              color: _getTierGradient(level)[0].withOpacity(0.4),
                              blurRadius: 20,
                              offset: const Offset(0, 8),
                            ),
                          ],
                        ),
                        child: Stack(
                          children: [
                            // Decorative circles
                            Positioned(
                              right: -20,
                              top: -20,
                              child: Container(
                                width: 120,
                                height: 120,
                                decoration: BoxDecoration(
                                  shape: BoxShape.circle,
                                  color: Colors.white.withOpacity(0.08),
                                ),
                              ),
                            ),
                            Positioned(
                              right: 30,
                              bottom: -30,
                              child: Container(
                                width: 80,
                                height: 80,
                                decoration: BoxDecoration(
                                  shape: BoxShape.circle,
                                  color: Colors.white.withOpacity(0.06),
                                ),
                              ),
                            ),
                            Padding(
                              padding: const EdgeInsets.all(20),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Row(
                                    mainAxisAlignment:
                                        MainAxisAlignment.spaceBetween,
                                    children: [
                                      Row(
                                        children: [
                                          const Icon(Icons.water_drop,
                                              color: Colors.white70, size: 18),
                                          const SizedBox(width: 6),
                                          Text('Ken Water',
                                              style: GoogleFonts.inter(
                                                color: Colors.white70,
                                                fontSize: 13,
                                                letterSpacing: 1,
                                              )),
                                        ],
                                      ),
                                      Container(
                                        padding: const EdgeInsets.symmetric(
                                            horizontal: 10, vertical: 4),
                                        decoration: BoxDecoration(
                                          color: Colors.white.withOpacity(0.2),
                                          borderRadius: BorderRadius.circular(20),
                                        ),
                                        child: Text(
                                          '${_getTierEmoji(level)} ${level.toUpperCase()}',
                                          style: GoogleFonts.inter(
                                            color: Colors.white,
                                            fontSize: 12,
                                            fontWeight: FontWeight.bold,
                                          ),
                                        ),
                                      ),
                                    ],
                                  ),
                                  const SizedBox(height: 20),
                                  Row(
                                    mainAxisAlignment:
                                        MainAxisAlignment.spaceBetween,
                                    crossAxisAlignment: CrossAxisAlignment.end,
                                    children: [
                                      Expanded(
                                        child: Column(
                                          crossAxisAlignment:
                                              CrossAxisAlignment.start,
                                          children: [
                                            Text(name,
                                                style: GoogleFonts.inter(
                                                  color: Colors.white,
                                                  fontSize: 18,
                                                  fontWeight: FontWeight.bold,
                                                ),
                                                maxLines: 1,
                                                overflow:
                                                    TextOverflow.ellipsis),
                                            const SizedBox(height: 4),
                                            Text(customerId,
                                                style: GoogleFonts.jetBrainsMono(
                                                  color: Colors.white70,
                                                  fontSize: 13,
                                                  letterSpacing: 1.5,
                                                )),
                                            const SizedBox(height: 12),
                                            Row(
                                              children: [
                                                const Icon(Icons.stars_rounded,
                                                    color: Colors.white,
                                                    size: 18),
                                                const SizedBox(width: 6),
                                                Text(
                                                  '$points Poin',
                                                  style: GoogleFonts.inter(
                                                    color: Colors.white,
                                                    fontWeight: FontWeight.w700,
                                                    fontSize: 16,
                                                  ),
                                                ),
                                              ],
                                            ),
                                            const SizedBox(height: 6),
                                            Container(
                                              padding: const EdgeInsets.symmetric(
                                                  horizontal: 8, vertical: 3),
                                              decoration: BoxDecoration(
                                                color: status == 'aktif'
                                                    ? Colors.greenAccent
                                                        .withOpacity(0.25)
                                                    : Colors.redAccent
                                                        .withOpacity(0.25),
                                                borderRadius:
                                                    BorderRadius.circular(6),
                                              ),
                                              child: Text(
                                                status == 'aktif'
                                                    ? '● Aktif'
                                                    : '● Tidak Aktif',
                                                style: GoogleFonts.inter(
                                                  color: status == 'aktif'
                                                      ? Colors.greenAccent
                                                      : Colors.redAccent,
                                                  fontSize: 11,
                                                  fontWeight: FontWeight.w600,
                                                ),
                                              ),
                                            ),
                                          ],
                                        ),
                                      ),
                                      Container(
                                        padding: const EdgeInsets.all(6),
                                        decoration: BoxDecoration(
                                          color: Colors.white,
                                          borderRadius:
                                              BorderRadius.circular(10),
                                        ),
                                        child: QrImageView(
                                          data: customerId,
                                          version: QrVersions.auto,
                                          size: 70,
                                        ),
                                      ),
                                    ],
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ),
                      ),

                      // ===================
                      // LOYALTY PROGRESS
                      // ===================
                      if (progress != null) ...[
                        const SizedBox(height: 16),
                        Container(
                          padding: const EdgeInsets.all(16),
                          decoration: BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(14),
                            border: Border.all(
                                color: const Color(0xFFE2E8F0)),
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Row(
                                mainAxisAlignment:
                                    MainAxisAlignment.spaceBetween,
                                children: [
                                  Text('Progres Loyalitas',
                                      style: GoogleFonts.inter(
                                          fontWeight: FontWeight.bold,
                                          fontSize: 14)),
                                  if (progress['next_level'] != null)
                                    Container(
                                      padding: const EdgeInsets.symmetric(
                                          horizontal: 8, vertical: 3),
                                      decoration: BoxDecoration(
                                        color: AppColors.primaryLight,
                                        borderRadius: BorderRadius.circular(6),
                                      ),
                                      child: Text(
                                        'Menuju ${progress['next_level']?.toString().toUpperCase() ?? ''}',
                                        style: GoogleFonts.inter(
                                          fontSize: 11,
                                          color: AppColors.primary,
                                          fontWeight: FontWeight.w600,
                                        ),
                                      ),
                                    )
                                  else
                                    Text('🎉 Level Maksimal',
                                        style: GoogleFonts.inter(
                                          fontSize: 12,
                                          color: AppColors.success,
                                          fontWeight: FontWeight.bold,
                                        )),
                                ],
                              ),
                              const SizedBox(height: 10),
                              ClipRRect(
                                borderRadius: BorderRadius.circular(6),
                                child: LinearProgressIndicator(
                                  value: ((progress['percentage'] ?? 0) as num) / 100,
                                  backgroundColor: const Color(0xFFF1F5F9),
                                  valueColor:
                                      const AlwaysStoppedAnimation<Color>(
                                          AppColors.primary),
                                  minHeight: 8,
                                ),
                              ),
                              const SizedBox(height: 6),
                              Row(
                                mainAxisAlignment:
                                    MainAxisAlignment.spaceBetween,
                                children: [
                                  Text('$points Poin',
                                      style: GoogleFonts.inter(
                                          fontSize: 12,
                                          color: AppColors.textSecondary)),
                                  if ((progress['needed'] ?? 0) > 0)
                                    Text(
                                        'Kurang ${progress['needed']} poin lagi',
                                        style: GoogleFonts.inter(
                                            fontSize: 12,
                                            color: AppColors.textSecondary)),
                                ],
                              ),
                            ],
                          ),
                        ),
                      ],

                      const SizedBox(height: 20),

                      // ===================
                      // QUICK ACTIONS
                      // ===================
                      Text('Layanan Cepat',
                          style: GoogleFonts.inter(
                              fontSize: 16,
                              fontWeight: FontWeight.bold,
                              color: AppColors.textPrimary)),
                      const SizedBox(height: 12),
                      Row(
                        children: [
                          _buildQuickAction(
                            icon: Icons.water_drop_rounded,
                            label: 'Pesan Galon',
                            color: AppColors.primary,
                            gradient: const [
                              Color(0xFF0284C7),
                              Color(0xFF0EA5E9)
                            ],
                            onTap: () {
                              context.findAncestorStateOfType<CustomerMainScreenState>()?.switchTab(1);
                            },
                          ),
                          const SizedBox(width: 12),
                          _buildQuickAction(
                            icon: Icons.star_rounded,
                            label: 'Tukar Poin',
                            color: AppColors.warning,
                            gradient: const [
                              Color(0xFFD97706),
                              Color(0xFFF59E0B)
                            ],
                            onTap: () {
                              context.findAncestorStateOfType<CustomerMainScreenState>()?.switchTab(2);
                            },
                          ),
                          const SizedBox(width: 12),
                          _buildQuickAction(
                            icon: Icons.confirmation_number_rounded,
                            label: 'Voucher',
                            color: AppColors.success,
                            gradient: const [
                              Color(0xFF059669),
                              Color(0xFF10B981)
                            ],
                            onTap: () {
                              context.findAncestorStateOfType<CustomerMainScreenState>()?.switchTab(2);
                            },
                          ),
                        ],
                      ),

                      const SizedBox(height: 24),

                      // ===================
                      // RIWAYAT TRANSAKSI
                      // ===================
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Text('Riwayat Transaksi',
                              style: GoogleFonts.inter(
                                  fontSize: 16,
                                  fontWeight: FontWeight.bold,
                                  color: AppColors.textPrimary)),
                          if (_orders.isNotEmpty)
                            Text('${_orders.length} transaksi',
                                style: GoogleFonts.inter(
                                    fontSize: 12,
                                    color: AppColors.textSecondary)),
                        ],
                      ),
                      const SizedBox(height: 12),

                      if (_orders.isEmpty)
                        Container(
                          padding: const EdgeInsets.symmetric(vertical: 40),
                          alignment: Alignment.center,
                          child: Column(
                            children: [
                              Icon(Icons.receipt_long_outlined,
                                  size: 52, color: AppColors.textMuted),
                              const SizedBox(height: 12),
                              Text('Belum ada riwayat transaksi',
                                  style: GoogleFonts.inter(
                                      color: AppColors.textSecondary)),
                            ],
                          ),
                        )
                      else
                        ListView.separated(
                          shrinkWrap: true,
                          physics: const NeverScrollableScrollPhysics(),
                          itemCount: _orders.length,
                          separatorBuilder: (_, __) =>
                              const SizedBox(height: 10),
                          itemBuilder: (ctx, index) {
                            final item = _orders[index];
                            final statusColor = _getStatusColor(item.status);
                            return Container(
                              padding: const EdgeInsets.all(14),
                              decoration: BoxDecoration(
                                color: Colors.white,
                                borderRadius: BorderRadius.circular(14),
                                boxShadow: [
                                  BoxShadow(
                                    color: Colors.black.withOpacity(0.04),
                                    blurRadius: 10,
                                    offset: const Offset(0, 2),
                                  ),
                                ],
                              ),
                              child: Row(
                                children: [
                                  Container(
                                    padding: const EdgeInsets.all(10),
                                    decoration: BoxDecoration(
                                      color:
                                          AppColors.primary.withOpacity(0.1),
                                      borderRadius: BorderRadius.circular(12),
                                    ),
                                    child: const Icon(
                                        Icons.water_drop_rounded,
                                        color: AppColors.primary,
                                        size: 22),
                                  ),
                                  const SizedBox(width: 12),
                                  Expanded(
                                    child: Column(
                                      crossAxisAlignment:
                                          CrossAxisAlignment.start,
                                      children: [
                                        Text(
                                            '${item.gallonQty} Galon Air',
                                            style: GoogleFonts.inter(
                                                fontWeight: FontWeight.w600,
                                                fontSize: 14,
                                                color:
                                                    AppColors.textPrimary)),
                                        const SizedBox(height: 3),
                                        Text(item.orderDate,
                                            style: GoogleFonts.inter(
                                                fontSize: 12,
                                                color:
                                                    AppColors.textSecondary)),
                                        if (item.deliveryMethod != null)
                                          Text(
                                            item.deliveryMethod == 'antar'
                                                ? '🛵 Diantar'
                                                : '🏪 Ambil Sendiri',
                                            style: GoogleFonts.inter(
                                                fontSize: 11,
                                                color:
                                                    AppColors.textMuted),
                                          ),
                                      ],
                                    ),
                                  ),
                                  Column(
                                    crossAxisAlignment:
                                        CrossAxisAlignment.end,
                                    children: [
                                      Text(
                                        currencyFormatter
                                            .format(item.totalPrice),
                                        style: GoogleFonts.inter(
                                            fontWeight: FontWeight.bold,
                                            fontSize: 14,
                                            color: AppColors.primaryDark),
                                      ),
                                      const SizedBox(height: 6),
                                      Container(
                                        padding: const EdgeInsets.symmetric(
                                            horizontal: 8, vertical: 3),
                                        decoration: BoxDecoration(
                                          color: statusColor.withOpacity(0.12),
                                          borderRadius:
                                              BorderRadius.circular(6),
                                        ),
                                        child: Text(
                                          item.statusLabel,
                                          style: GoogleFonts.inter(
                                            fontSize: 11,
                                            fontWeight: FontWeight.w600,
                                            color: statusColor,
                                          ),
                                        ),
                                      ),
                                    ],
                                  ),
                                ],
                              ),
                            );
                          },
                        ),
                      const SizedBox(height: 20),
                    ],
                  ),
                ),
              ),
          ],
        ),
      ),
    );
  }

  Widget _buildQuickAction({
    required IconData icon,
    required String label,
    required Color color,
    required List<Color> gradient,
    required VoidCallback onTap,
  }) {
    return Expanded(
      child: GestureDetector(
        onTap: onTap,
        child: Container(
          padding: const EdgeInsets.symmetric(vertical: 16),
          decoration: BoxDecoration(
            gradient: LinearGradient(colors: gradient),
            borderRadius: BorderRadius.circular(14),
            boxShadow: [
              BoxShadow(
                color: color.withOpacity(0.3),
                blurRadius: 8,
                offset: const Offset(0, 4),
              ),
            ],
          ),
          child: Column(
            children: [
              Icon(icon, color: Colors.white, size: 24),
              const SizedBox(height: 6),
              Text(label,
                  style: GoogleFonts.inter(
                    color: Colors.white,
                    fontSize: 11,
                    fontWeight: FontWeight.w600,
                  ),
                  textAlign: TextAlign.center),
            ],
          ),
        ),
      ),
    );
  }
}
