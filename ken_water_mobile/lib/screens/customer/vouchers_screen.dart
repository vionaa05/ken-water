import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:google_fonts/google_fonts.dart';
import '../../constants/app_colors.dart';
import '../../services/api_service.dart';
import 'customer_main_screen.dart';

class VouchersScreen extends StatefulWidget {
  const VouchersScreen({super.key});

  @override
  State<VouchersScreen> createState() => _VouchersScreenState();
}

class _VouchersScreenState extends State<VouchersScreen> {
  List<dynamic> _vouchers = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadVouchers();
  }

  Future<void> _loadVouchers() async {
    setState(() => _isLoading = true);
    final res = await ApiService.getVouchers();
    if (mounted) {
      setState(() {
        _vouchers = res['vouchers'] ?? [];
        _isLoading = false;
      });
    }
  }

  List<dynamic> get _activeVouchers =>
      _vouchers.where((v) => v['is_active'] == true).toList();

  List<dynamic> get _usedOrExpiredVouchers =>
      _vouchers.where((v) => v['is_active'] != true).toList();

  void _useVoucher() {
    Navigator.pop(context);
    // Switch to Order tab (tab index 1)
    context.findAncestorStateOfType<CustomerMainScreenState>()?.switchTab(1);
  }

  @override
  Widget build(BuildContext context) {
    return DefaultTabController(
      length: 2,
      child: Scaffold(
        backgroundColor: AppColors.background,
        appBar: AppBar(
          backgroundColor: Colors.white,
          surfaceTintColor: Colors.white,
          elevation: 0.5,
          shadowColor: const Color(0x0A000000),
          leading: IconButton(
            icon: const Icon(Icons.arrow_back, color: AppColors.textPrimary),
            onPressed: () => Navigator.pop(context),
          ),
          title: Text(
            'Voucher Saya',
            style: GoogleFonts.inter(
              fontWeight: FontWeight.bold,
              fontSize: 18,
              color: AppColors.textPrimary,
            ),
          ),
          actions: [
            IconButton(
              icon: const Icon(Icons.refresh, color: AppColors.textSecondary),
              tooltip: 'Segarkan',
              onPressed: _loadVouchers,
            ),
          ],
          bottom: TabBar(
            labelColor: AppColors.primary,
            unselectedLabelColor: AppColors.textSecondary,
            indicatorColor: AppColors.primary,
            indicatorWeight: 3,
            tabs: [
              Tab(
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    const Text('Voucher Aktif', style: TextStyle(fontWeight: FontWeight.w600, fontSize: 13)),
                    if (_activeVouchers.isNotEmpty) ...[
                      const SizedBox(width: 6),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                        decoration: BoxDecoration(
                          color: AppColors.success,
                          borderRadius: BorderRadius.circular(10),
                        ),
                        child: Text(
                          '${_activeVouchers.length}',
                          style: const TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.bold),
                        ),
                      ),
                    ],
                  ],
                ),
              ),
              const Tab(
                child: Text('Riwayat / Terpakai', style: TextStyle(fontWeight: FontWeight.w600, fontSize: 13)),
              ),
            ],
          ),
        ),
        body: _isLoading
            ? const Center(child: CircularProgressIndicator(color: AppColors.primary))
            : TabBarView(
                children: [
                  _buildVoucherList(_activeVouchers, isActiveTab: true),
                  _buildVoucherList(_usedOrExpiredVouchers, isActiveTab: false),
                ],
              ),
      ),
    );
  }

  Widget _buildVoucherList(List<dynamic> list, {required bool isActiveTab}) {
    if (list.isEmpty) {
      return RefreshIndicator(
        onRefresh: _loadVouchers,
        color: AppColors.primary,
        child: ListView(
          children: [
            const SizedBox(height: 100),
            Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Container(
                    padding: const EdgeInsets.all(20),
                    decoration: BoxDecoration(
                      color: AppColors.primary.withOpacity(0.08),
                      shape: BoxShape.circle,
                    ),
                    child: Icon(
                      isActiveTab ? Icons.confirmation_number_outlined : Icons.history_rounded,
                      size: 48,
                      color: AppColors.textMuted,
                    ),
                  ),
                  const SizedBox(height: 16),
                  Text(
                    isActiveTab ? 'Belum Ada Voucher Aktif' : 'Belum Ada Riwayat Voucher',
                    style: GoogleFonts.inter(
                      fontSize: 15,
                      fontWeight: FontWeight.bold,
                      color: AppColors.textPrimary,
                    ),
                  ),
                  const SizedBox(height: 6),
                  Text(
                    isActiveTab
                        ? 'Voucher promo yang dibagikan admin akan muncul di sini.'
                        : 'Voucher yang telah Anda pakai akan tercatat di sini.',
                    textAlign: TextAlign.center,
                    style: GoogleFonts.inter(fontSize: 12, color: AppColors.textSecondary),
                  ),
                ],
              ),
            ),
          ],
        ),
      );
    }

    return RefreshIndicator(
      onRefresh: _loadVouchers,
      color: AppColors.primary,
      child: ListView.separated(
        padding: const EdgeInsets.all(16),
        itemCount: list.length,
        separatorBuilder: (_, __) => const SizedBox(height: 12),
        itemBuilder: (ctx, i) => _buildVoucherCard(list[i], isActiveTab: isActiveTab),
      ),
    );
  }

  Widget _buildVoucherCard(Map v, {required bool isActiveTab}) {
    final name = v['name'] ?? 'Voucher Diskon';
    final code = v['code'] ?? '-';
    final discountType = v['discount_type'] ?? 'nominal';
    final discountValue = (v['discount_value'] is num)
        ? v['discount_value']
        : double.tryParse(v['discount_value'].toString()) ?? 0;
    final expiredAt = v['expired_at'];
    final isUsed = v['is_used'] == true;
    final description = v['description'];

    final discountText = discountType == 'persen'
        ? 'Diskon ${discountValue.toInt()}%'
        : 'Diskon Rp ${discountValue.toInt()}';

    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(
          color: isActiveTab ? AppColors.success.withOpacity(0.4) : const Color(0xFFE2E8F0),
          width: isActiveTab ? 1.5 : 1,
        ),
        boxShadow: const [
          BoxShadow(
            color: Color(0x06000000),
            blurRadius: 8,
            offset: Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        children: [
          Padding(
            padding: const EdgeInsets.all(16),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Discount Icon / Badge
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: isActiveTab ? const Color(0xFFECFDF5) : const Color(0xFFF1F5F9),
                    borderRadius: BorderRadius.circular(14),
                  ),
                  child: Icon(
                    Icons.confirmation_number_rounded,
                    color: isActiveTab ? const Color(0xFF059669) : AppColors.textMuted,
                    size: 28,
                  ),
                ),
                const SizedBox(width: 14),

                // Details
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Expanded(
                            child: Text(
                              name,
                              style: GoogleFonts.inter(
                                fontWeight: FontWeight.bold,
                                fontSize: 15,
                                color: AppColors.textPrimary,
                              ),
                            ),
                          ),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                            decoration: BoxDecoration(
                              color: isActiveTab
                                  ? AppColors.success.withOpacity(0.12)
                                  : const Color(0xFFF1F5F9),
                              borderRadius: BorderRadius.circular(6),
                            ),
                            child: Text(
                              isActiveTab ? 'Aktif' : (isUsed ? 'Terpakai' : 'Kedaluwarsa'),
                              style: GoogleFonts.inter(
                                fontSize: 11,
                                fontWeight: FontWeight.w600,
                                color: isActiveTab ? AppColors.success : AppColors.textMuted,
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 4),
                      Text(
                        discountText,
                        style: GoogleFonts.inter(
                          color: isActiveTab ? const Color(0xFF059669) : AppColors.textSecondary,
                          fontWeight: FontWeight.bold,
                          fontSize: 14,
                        ),
                      ),
                      if (description != null && description.toString().isNotEmpty) ...[
                        const SizedBox(height: 4),
                        Text(
                          description.toString(),
                          style: GoogleFonts.inter(fontSize: 12, color: AppColors.textSecondary),
                        ),
                      ],
                      const SizedBox(height: 8),

                      // Code + Copy button
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                        decoration: BoxDecoration(
                          color: const Color(0xFFF8FAFC),
                          borderRadius: BorderRadius.circular(8),
                          border: Border.all(color: const Color(0xFFE2E8F0)),
                        ),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Text(
                              code,
                              style: GoogleFonts.jetBrainsMono(
                                fontWeight: FontWeight.bold,
                                fontSize: 12,
                                color: AppColors.primary,
                              ),
                            ),
                            const SizedBox(width: 6),
                            InkWell(
                              onTap: () {
                                Clipboard.setData(ClipboardData(text: code));
                                ScaffoldMessenger.of(context).showSnackBar(
                                  SnackBar(
                                    content: Text('Kode voucher "$code" disalin!'),
                                    duration: const Duration(seconds: 1),
                                    behavior: SnackBarBehavior.floating,
                                  ),
                                );
                              },
                              child: const Icon(Icons.copy_rounded, size: 14, color: AppColors.textSecondary),
                            ),
                          ],
                        ),
                      ),

                      if (expiredAt != null) ...[
                        const SizedBox(height: 6),
                        Text(
                          'Berlaku s/d $expiredAt',
                          style: GoogleFonts.inter(fontSize: 11, color: AppColors.textMuted),
                        ),
                      ],
                    ],
                  ),
                ),
              ],
            ),
          ),

          if (isActiveTab) ...[
            const Divider(height: 1, color: Color(0xFFF1F5F9)),
            InkWell(
              onTap: _useVoucher,
              borderRadius: const BorderRadius.vertical(bottom: Radius.circular(16)),
              child: Container(
                width: double.infinity,
                padding: const EdgeInsets.symmetric(vertical: 12),
                alignment: Alignment.center,
                decoration: const BoxDecoration(
                  color: Color(0xFFF0FDF4),
                  borderRadius: BorderRadius.vertical(bottom: Radius.circular(16)),
                ),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    const Icon(Icons.shopping_bag_outlined, color: Color(0xFF16A34A), size: 16),
                    const SizedBox(width: 6),
                    Text(
                      'Gunakan Saat Pesan Galon',
                      style: GoogleFonts.inter(
                        fontSize: 13,
                        fontWeight: FontWeight.bold,
                        color: const Color(0xFF16A34A),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ],
        ],
      ),
    );
  }
}
