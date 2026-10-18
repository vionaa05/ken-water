import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:google_fonts/google_fonts.dart';
import '../../../constants/app_colors.dart';
import '../../../services/api_service.dart';

class RewardsTab extends StatefulWidget {
  const RewardsTab({super.key});

  @override
  State<RewardsTab> createState() => _RewardsTabState();
}

class _RewardsTabState extends State<RewardsTab> {
  List<dynamic> _rewards = [];
  List<dynamic> _redemptions = [];
  List<dynamic> _vouchers = [];
  int _userPoints = 0;
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    setState(() => _isLoading = true);
    final rewardRes = await ApiService.getRewards();
    final voucherRes = await ApiService.getVouchers();
    if (mounted) {
      setState(() {
        _rewards = rewardRes['rewards'] ?? [];
        _redemptions = rewardRes['redemptions'] ?? [];
        _userPoints = rewardRes['user_points'] ?? 0;
        _vouchers = voucherRes['vouchers'] ?? [];
        _isLoading = false;
      });
    }
  }

  List<dynamic> get _pendingRedemptions =>
      _redemptions.where((r) => (r['status'] ?? '') == 'pending').toList();

  List<dynamic> get _completedRedemptions =>
      _redemptions
          .where((r) =>
              (r['status'] ?? '') == 'validated' ||
              (r['status'] ?? '') == 'cancelled')
          .toList();

  Future<void> _confirmRedeem(Map reward) async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        backgroundColor: Colors.white,
        title: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: AppColors.warning.withOpacity(0.1),
                borderRadius: BorderRadius.circular(10),
              ),
              child: const Icon(Icons.card_giftcard_rounded,
                  color: AppColors.warning, size: 22),
            ),
            const SizedBox(width: 12),
            Text('Tukar Reward?',
                style: GoogleFonts.inter(
                    fontWeight: FontWeight.bold, fontSize: 17)),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color: const Color(0xFFFFF7ED),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: const Color(0xFFFED7AA)),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(reward['name'] ?? '',
                      style: GoogleFonts.inter(
                          fontWeight: FontWeight.w700, fontSize: 15)),
                  const SizedBox(height: 6),
                  Row(
                    children: [
                      const Icon(Icons.stars_rounded,
                          color: AppColors.warning, size: 16),
                      const SizedBox(width: 4),
                      Text('${reward['points_required']} poin',
                          style: GoogleFonts.inter(
                              color: AppColors.warning,
                              fontWeight: FontWeight.bold,
                              fontSize: 14)),
                    ],
                  ),
                ],
              ),
            ),
            const SizedBox(height: 14),
            _buildInfoRow('Saldo saat ini', '$_userPoints poin',
                AppColors.textPrimary),
            const SizedBox(height: 6),
            _buildInfoRow('Akan dikurangi', '-${reward['points_required']} poin',
                AppColors.error),
            const Divider(height: 16),
            _buildInfoRow(
                'Sisa poin',
                '${_userPoints - (reward['points_required'] as int)} poin',
                AppColors.success),
          ],
        ),
        actions: [
          TextButton(
              onPressed: () => Navigator.pop(ctx, false),
              child: Text('Batal',
                  style: GoogleFonts.inter(color: AppColors.textSecondary))),
          ElevatedButton(
            onPressed: () => Navigator.pop(ctx, true),
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.warning,
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12)),
              padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
            ),
            child: Text('Tukar Sekarang',
                style: GoogleFonts.inter(fontWeight: FontWeight.bold)),
          ),
        ],
      ),
    );

    if (confirm == true) {
      // Show loading
      if (!mounted) return;
      showDialog(
        context: context,
        barrierDismissible: false,
        builder: (ctx) => const Center(
          child: CircularProgressIndicator(color: AppColors.warning),
        ),
      );

      final result = await ApiService.redeemReward(reward['id']);

      if (!mounted) return;
      Navigator.of(context).pop(); // close loading

      if (result['success'] == true) {
        // Show success dialog with redemption code
        final code = result['redemption_code'] ?? '';
        await showDialog(
          context: context,
          builder: (ctx) => AlertDialog(
            shape:
                RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
            content: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Container(
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: AppColors.success.withOpacity(0.1),
                    shape: BoxShape.circle,
                  ),
                  child: const Icon(Icons.check_circle_rounded,
                      color: AppColors.success, size: 48),
                ),
                const SizedBox(height: 16),
                Text('Penukaran Berhasil!',
                    style: GoogleFonts.inter(
                        fontWeight: FontWeight.bold, fontSize: 18)),
                const SizedBox(height: 8),
                Text('Tunjukkan kode ini ke admin depot:',
                    style: GoogleFonts.inter(
                        color: AppColors.textSecondary, fontSize: 13)),
                const SizedBox(height: 14),
                if (code.isNotEmpty)
                  GestureDetector(
                    onTap: () {
                      Clipboard.setData(ClipboardData(text: code));
                      ScaffoldMessenger.of(context).showSnackBar(
                        const SnackBar(
                          content: Text('Kode disalin ke clipboard!'),
                          behavior: SnackBarBehavior.floating,
                          backgroundColor: AppColors.success,
                        ),
                      );
                    },
                    child: Container(
                      width: double.infinity,
                      padding: const EdgeInsets.symmetric(
                          vertical: 14, horizontal: 16),
                      decoration: BoxDecoration(
                        color: const Color(0xFFF1F5F9),
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: const Color(0xFFCBD5E1)),
                      ),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Text(code,
                              style: GoogleFonts.jetBrainsMono(
                                fontSize: 22,
                                fontWeight: FontWeight.bold,
                                letterSpacing: 3,
                                color: AppColors.textPrimary,
                              )),
                          const SizedBox(width: 8),
                          const Icon(Icons.copy_rounded,
                              size: 18, color: AppColors.textSecondary),
                        ],
                      ),
                    ),
                  ),
                const SizedBox(height: 12),
                Text(
                  'Sisa poin: ${result['remaining_points']} poin',
                  style: GoogleFonts.inter(
                      color: AppColors.textMuted, fontSize: 12),
                ),
              ],
            ),
            actions: [
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  onPressed: () => Navigator.pop(ctx),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppColors.primary,
                    foregroundColor: Colors.white,
                    shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12)),
                    padding: const EdgeInsets.symmetric(vertical: 14),
                  ),
                  child: Text('Mengerti',
                      style: GoogleFonts.inter(fontWeight: FontWeight.bold)),
                ),
              ),
            ],
          ),
        );

        setState(
            () => _userPoints = result['remaining_points'] ?? _userPoints);
        _loadData();
      } else {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(
          content: Text(result['message'] ?? 'Gagal menukar reward'),
          backgroundColor: AppColors.error,
          behavior: SnackBarBehavior.floating,
        ));
      }
    }
  }

  Widget _buildInfoRow(String label, String value, Color valueColor) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(label,
            style:
                GoogleFonts.inter(color: AppColors.textSecondary, fontSize: 13)),
        Text(value,
            style: GoogleFonts.inter(
                color: valueColor, fontWeight: FontWeight.w600, fontSize: 13)),
      ],
    );
  }

  @override
  Widget build(BuildContext context) {
    return DefaultTabController(
      length: 3,
      child: Scaffold(
        backgroundColor: AppColors.background,
        appBar: AppBar(
          backgroundColor: Colors.white,
          surfaceTintColor: Colors.white,
          elevation: 0,
          shadowColor: Colors.black12,
          automaticallyImplyLeading: false,
          title: Text('Rewards & Voucher',
              style: GoogleFonts.inter(
                  fontWeight: FontWeight.bold,
                  fontSize: 18,
                  color: AppColors.textPrimary)),
          bottom: PreferredSize(
            preferredSize: const Size.fromHeight(48),
            child: Container(
              decoration: const BoxDecoration(
                border: Border(
                  bottom: BorderSide(color: Color(0xFFE2E8F0), width: 1),
                ),
              ),
              child: TabBar(
                labelColor: AppColors.primary,
                unselectedLabelColor: AppColors.textSecondary,
                indicatorColor: AppColors.primary,
                indicatorWeight: 3,
                labelPadding: EdgeInsets.zero,
                tabs: [
                  Tab(
                      child: Text('Katalog',
                          style: GoogleFonts.inter(
                              fontWeight: FontWeight.w600, fontSize: 13))),
                  Tab(
                      child: Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Text('Riwayat',
                          style: GoogleFonts.inter(
                              fontWeight: FontWeight.w600, fontSize: 13)),
                      if (_pendingRedemptions.isNotEmpty)
                        Container(
                          margin: const EdgeInsets.only(left: 6),
                          padding: const EdgeInsets.symmetric(
                              horizontal: 6, vertical: 2),
                          decoration: BoxDecoration(
                            color: AppColors.error,
                            borderRadius: BorderRadius.circular(10),
                          ),
                          child: Text('${_pendingRedemptions.length}',
                              style: GoogleFonts.inter(
                                  color: Colors.white,
                                  fontSize: 10,
                                  fontWeight: FontWeight.bold)),
                        ),
                    ],
                  )),
                  Tab(
                      child: Text('Voucher',
                          style: GoogleFonts.inter(
                              fontWeight: FontWeight.w600, fontSize: 13))),
                ],
              ),
            ),
          ),
        ),
        body: _isLoading
            ? const Center(
                child: CircularProgressIndicator(color: AppColors.primary))
            : TabBarView(
                children: [
                  _buildCatalogTab(),
                  _buildRedemptionHistoryTab(),
                  _buildVoucherTab(),
                ],
              ),
      ),
    );
  }

  // ═══════════════════════════════════════════
  //  TAB 1: KATALOG REWARD
  // ═══════════════════════════════════════════
  Widget _buildCatalogTab() {
    return RefreshIndicator(
      onRefresh: _loadData,
      color: AppColors.primary,
      child: CustomScrollView(
        slivers: [
          // Points Balance Card
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.fromLTRB(16, 16, 16, 8),
              child: Container(
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                  gradient: const LinearGradient(
                    colors: [Color(0xFFD97706), Color(0xFFF59E0B)],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  ),
                  borderRadius: BorderRadius.circular(18),
                  boxShadow: [
                    BoxShadow(
                      color: const Color(0xFFF59E0B).withOpacity(0.3),
                      blurRadius: 16,
                      offset: const Offset(0, 6),
                    ),
                  ],
                ),
                child: Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: Colors.white.withOpacity(0.2),
                        borderRadius: BorderRadius.circular(14),
                      ),
                      child: const Icon(Icons.stars_rounded,
                          color: Colors.white, size: 30),
                    ),
                    const SizedBox(width: 16),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text('Saldo Poin Kamu',
                              style: GoogleFonts.inter(
                                  color: Colors.white70, fontSize: 13)),
                          const SizedBox(height: 2),
                          Text('$_userPoints Poin',
                              style: GoogleFonts.inter(
                                color: Colors.white,
                                fontSize: 26,
                                fontWeight: FontWeight.bold,
                              )),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),

          // Info text
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
              child: Text(
                'Tukarkan poin kamu dengan hadiah menarik',
                style: GoogleFonts.inter(
                    color: AppColors.textSecondary, fontSize: 13),
              ),
            ),
          ),

          // Reward Grid
          if (_rewards.isEmpty)
            SliverFillRemaining(
              child: _buildEmpty(
                  'Belum ada reward tersedia', Icons.card_giftcard_outlined),
            )
          else
            SliverPadding(
              padding: const EdgeInsets.fromLTRB(16, 0, 16, 24),
              sliver: SliverGrid(
                gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                  crossAxisCount: 2,
                  crossAxisSpacing: 12,
                  mainAxisSpacing: 12,
                  childAspectRatio: 0.82,
                ),
                delegate: SliverChildBuilderDelegate(
                  (ctx, index) => _buildRewardCard(_rewards[index]),
                  childCount: _rewards.length,
                ),
              ),
            ),
        ],
      ),
    );
  }

  // ═══════════════════════════════════════════
  //  TAB 2: RIWAYAT PENUKARAN (SAMA DENGAN WEB)
  // ═══════════════════════════════════════════
  Widget _buildRedemptionHistoryTab() {
    return RefreshIndicator(
      onRefresh: _loadData,
      color: AppColors.primary,
      child: _redemptions.isEmpty
          ? ListView(
              children: [
                const SizedBox(height: 100),
                _buildEmpty('Belum ada riwayat penukaran',
                    Icons.history_rounded),
              ],
            )
          : ListView(
              padding: const EdgeInsets.all(16),
              children: [
                // ── Active / Pending Redemptions ──
                if (_pendingRedemptions.isNotEmpty) ...[
                  _buildSectionHeader(
                      'Kode Penukaran Aktif', Icons.qr_code_2_rounded,
                      badgeCount: _pendingRedemptions.length),
                  const SizedBox(height: 10),
                  ..._pendingRedemptions
                      .map((r) => _buildPendingRedemptionCard(r)),
                  const SizedBox(height: 24),
                ],

                // ── Completed Redemptions ──
                _buildSectionHeader(
                    'Riwayat Selesai', Icons.checklist_rounded),
                const SizedBox(height: 10),
                if (_completedRedemptions.isEmpty)
                  Container(
                    padding: const EdgeInsets.all(20),
                    decoration: BoxDecoration(
                      color: const Color(0xFFF8FAFC),
                      borderRadius: BorderRadius.circular(14),
                      border: Border.all(color: const Color(0xFFE2E8F0)),
                    ),
                    child: Text(
                      'Belum ada riwayat penukaran selesai.',
                      textAlign: TextAlign.center,
                      style: GoogleFonts.inter(
                          color: AppColors.textSecondary, fontSize: 13),
                    ),
                  )
                else
                  ..._completedRedemptions
                      .map((r) => _buildCompletedRedemptionCard(r)),
              ],
            ),
    );
  }

  Widget _buildSectionHeader(String title, IconData icon,
      {int badgeCount = 0}) {
    return Row(
      children: [
        Icon(icon, color: AppColors.primary, size: 20),
        const SizedBox(width: 8),
        Text(title,
            style: GoogleFonts.inter(
                fontWeight: FontWeight.bold,
                fontSize: 15,
                color: AppColors.textPrimary)),
        if (badgeCount > 0) ...[
          const SizedBox(width: 8),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
            decoration: BoxDecoration(
              color: AppColors.warning.withOpacity(0.15),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Text('$badgeCount',
                style: GoogleFonts.inter(
                    color: AppColors.warning,
                    fontSize: 12,
                    fontWeight: FontWeight.bold)),
          ),
        ],
      ],
    );
  }

  Widget _buildPendingRedemptionCard(Map r) {
    final code = r['redemption_code'] ?? '';
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFFFDE68A), width: 1.5),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFFF59E0B).withOpacity(0.08),
            blurRadius: 12,
            offset: const Offset(0, 3),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header with yellow left border
          Container(
            padding: const EdgeInsets.all(14),
            decoration: const BoxDecoration(
              color: Color(0xFFFFFBEB),
              borderRadius: BorderRadius.only(
                topLeft: Radius.circular(14),
                topRight: Radius.circular(14),
              ),
            ),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(r['reward_name'] ?? 'Reward',
                          style: GoogleFonts.inter(
                              fontWeight: FontWeight.bold, fontSize: 14)),
                      const SizedBox(height: 2),
                      Text(r['created_at'] ?? '',
                          style: GoogleFonts.inter(
                              color: AppColors.textMuted, fontSize: 11)),
                    ],
                  ),
                ),
                Container(
                  padding:
                      const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    color: AppColors.warning.withOpacity(0.15),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text('Menunggu Validasi',
                      style: GoogleFonts.inter(
                          color: AppColors.warning,
                          fontSize: 11,
                          fontWeight: FontWeight.bold)),
                ),
              ],
            ),
          ),

          // Redemption Code
          Padding(
            padding: const EdgeInsets.all(14),
            child: Column(
              children: [
                Text('KODE PENUKARAN',
                    style: GoogleFonts.inter(
                        color: AppColors.textMuted,
                        fontSize: 10,
                        fontWeight: FontWeight.w600,
                        letterSpacing: 1.5)),
                const SizedBox(height: 8),
                GestureDetector(
                  onTap: () {
                    Clipboard.setData(ClipboardData(text: code));
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(
                        content: Text('Kode disalin!'),
                        behavior: SnackBarBehavior.floating,
                        backgroundColor: AppColors.success,
                        duration: Duration(seconds: 2),
                      ),
                    );
                  },
                  child: Container(
                    width: double.infinity,
                    padding: const EdgeInsets.symmetric(
                        vertical: 14, horizontal: 16),
                    decoration: BoxDecoration(
                      color: const Color(0xFFF8FAFC),
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: const Color(0xFFE2E8F0)),
                    ),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Text(code,
                            style: GoogleFonts.jetBrainsMono(
                              fontSize: 22,
                              fontWeight: FontWeight.bold,
                              letterSpacing: 4,
                              color: AppColors.textPrimary,
                            )),
                        const SizedBox(width: 8),
                        Icon(Icons.copy_rounded,
                            size: 18,
                            color: AppColors.textSecondary.withOpacity(0.6)),
                      ],
                    ),
                  ),
                ),
                const SizedBox(height: 10),
                Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    const Icon(Icons.info_outline_rounded,
                        size: 14, color: AppColors.textMuted),
                    const SizedBox(width: 6),
                    Text(
                      'Tunjukkan kode ini ke admin depot',
                      style: GoogleFonts.inter(
                          color: AppColors.textMuted, fontSize: 12),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildCompletedRedemptionCard(Map r) {
    final isValidated = (r['status'] ?? '') == 'validated';
    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.03),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(
              color: isValidated
                  ? AppColors.success.withOpacity(0.1)
                  : AppColors.error.withOpacity(0.1),
              borderRadius: BorderRadius.circular(12),
            ),
            child: Icon(
              isValidated
                  ? Icons.check_circle_rounded
                  : Icons.cancel_rounded,
              color: isValidated ? AppColors.success : AppColors.error,
              size: 22,
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(r['reward_name'] ?? 'Reward',
                    style: GoogleFonts.inter(
                        fontWeight: FontWeight.w600, fontSize: 14)),
                const SizedBox(height: 2),
                Text(r['created_at'] ?? '',
                    style: GoogleFonts.inter(
                        color: AppColors.textMuted, fontSize: 11)),
              ],
            ),
          ),
          Column(
            crossAxisAlignment: CrossAxisAlignment.end,
            children: [
              Container(
                padding:
                    const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: isValidated
                      ? AppColors.success.withOpacity(0.1)
                      : AppColors.error.withOpacity(0.1),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Text(
                  isValidated ? 'Selesai' : 'Dibatalkan',
                  style: GoogleFonts.inter(
                    color: isValidated ? AppColors.success : AppColors.error,
                    fontSize: 11,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ),
              const SizedBox(height: 4),
              Text('-${r['points_used'] ?? 0} Poin',
                  style: GoogleFonts.inter(
                      color: AppColors.textSecondary,
                      fontSize: 12,
                      fontWeight: FontWeight.w600)),
            ],
          ),
        ],
      ),
    );
  }

  // ═══════════════════════════════════════════
  //  TAB 3: VOUCHER SAYA
  // ═══════════════════════════════════════════
  Widget _buildVoucherTab() {
    return RefreshIndicator(
      onRefresh: _loadData,
      color: AppColors.primary,
      child: _vouchers.isEmpty
          ? ListView(
              children: [
                const SizedBox(height: 100),
                _buildEmpty(
                    'Belum ada voucher', Icons.confirmation_number_outlined),
              ],
            )
          : ListView.separated(
              padding: const EdgeInsets.all(16),
              itemCount: _vouchers.length,
              separatorBuilder: (_, __) => const SizedBox(height: 12),
              itemBuilder: (ctx, i) => _buildVoucherCard(_vouchers[i]),
            ),
    );
  }

  // ═══════════════════════════════════════════
  //  CARDS
  // ═══════════════════════════════════════════
  Widget _buildRewardCard(Map reward) {
    final canRedeem = reward['can_redeem'] == true;
    final stock = reward['stock'] as int? ?? 0;
    return GestureDetector(
      onTap: canRedeem ? () => _confirmRedeem(reward) : null,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
          boxShadow: [
            BoxShadow(
              color: canRedeem
                  ? const Color(0xFFF59E0B).withOpacity(0.08)
                  : Colors.black.withOpacity(0.04),
              blurRadius: 12,
              offset: const Offset(0, 3),
            ),
          ],
          border: Border.all(
            color: canRedeem
                ? AppColors.warning.withOpacity(0.3)
                : const Color(0xFFE2E8F0),
          ),
        ),
        child: Stack(
          children: [
            Padding(
              padding: const EdgeInsets.all(14),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: canRedeem
                          ? AppColors.warning.withOpacity(0.1)
                          : AppColors.surfaceMuted,
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Icon(Icons.card_giftcard_rounded,
                        color: canRedeem
                            ? AppColors.warning
                            : AppColors.textMuted,
                        size: 28),
                  ),
                  const SizedBox(height: 10),
                  Text(reward['name'] ?? '',
                      style: GoogleFonts.inter(
                          fontWeight: FontWeight.bold,
                          fontSize: 13,
                          color: AppColors.textPrimary),
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis),
                  const SizedBox(height: 4),
                  Text(reward['description'] ?? '',
                      style: GoogleFonts.inter(
                          fontSize: 11, color: AppColors.textSecondary),
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis),
                  const Spacer(),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text('${reward['points_required']} poin',
                              style: GoogleFonts.inter(
                                color: AppColors.warning,
                                fontWeight: FontWeight.bold,
                                fontSize: 13,
                              )),
                          Text('Stok: $stock',
                              style: GoogleFonts.inter(
                                  fontSize: 10,
                                  color: AppColors.textMuted)),
                        ],
                      ),
                      if (canRedeem)
                        Container(
                          padding: const EdgeInsets.symmetric(
                              horizontal: 10, vertical: 5),
                          decoration: BoxDecoration(
                            gradient: const LinearGradient(
                              colors: [
                                Color(0xFFD97706),
                                Color(0xFFF59E0B)
                              ],
                            ),
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: Text('Tukar',
                              style: GoogleFonts.inter(
                                color: Colors.white,
                                fontSize: 11,
                                fontWeight: FontWeight.bold,
                              )),
                        ),
                    ],
                  ),
                ],
              ),
            ),
            if (!canRedeem)
              Positioned.fill(
                child: Container(
                  decoration: BoxDecoration(
                    color: Colors.white.withOpacity(0.5),
                    borderRadius: BorderRadius.circular(16),
                  ),
                ),
              ),
          ],
        ),
      ),
    );
  }

  Widget _buildVoucherCard(Map v) {
    final isUsed = v['is_used'] == true;
    return AnimatedOpacity(
      duration: const Duration(milliseconds: 200),
      opacity: isUsed ? 0.6 : 1.0,
      child: Container(
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
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
            // Left colored strip
            Container(
              width: 80,
              height: 100,
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  colors: isUsed
                      ? [const Color(0xFF94A3B8), const Color(0xFF64748B)]
                      : [AppColors.success, const Color(0xFF059669)],
                  begin: Alignment.topCenter,
                  end: Alignment.bottomCenter,
                ),
                borderRadius: const BorderRadius.only(
                  topLeft: Radius.circular(16),
                  bottomLeft: Radius.circular(16),
                ),
              ),
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(
                    isUsed
                        ? Icons.check_circle_rounded
                        : Icons.confirmation_number_rounded,
                    color: Colors.white,
                    size: 28,
                  ),
                  const SizedBox(height: 4),
                  Text(
                    isUsed ? 'Digunakan' : 'Aktif',
                    style: GoogleFonts.inter(
                        color: Colors.white,
                        fontSize: 11,
                        fontWeight: FontWeight.w600),
                  ),
                ],
              ),
            ),
            const SizedBox(width: 14),
            Expanded(
              child: Padding(
                padding:
                    const EdgeInsets.symmetric(vertical: 14, horizontal: 4),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(v['name'] ?? 'Voucher',
                        style: GoogleFonts.inter(
                            fontWeight: FontWeight.bold, fontSize: 14)),
                    const SizedBox(height: 4),
                    Text(
                      v['discount_type'] == 'persen'
                          ? 'Diskon ${v['discount_value']}%'
                          : 'Diskon Rp ${(v['discount_value'] as num).toStringAsFixed(0)}',
                      style: GoogleFonts.inter(
                          color: AppColors.success,
                          fontWeight: FontWeight.w600,
                          fontSize: 13),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      'Kode: ${v['code']}',
                      style: GoogleFonts.jetBrainsMono(
                          fontSize: 12, color: AppColors.textSecondary),
                    ),
                    if (v['expired_at'] != null)
                      Text(
                        'Berlaku s/d ${v['expired_at']}',
                        style: GoogleFonts.inter(
                            fontSize: 11, color: AppColors.textMuted),
                      ),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildEmpty(String msg, IconData icon) {
    return Center(
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Container(
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              color: AppColors.primary.withOpacity(0.05),
              shape: BoxShape.circle,
            ),
            child: Icon(icon, size: 48, color: AppColors.textMuted),
          ),
          const SizedBox(height: 16),
          Text(msg,
              style: GoogleFonts.inter(
                  color: AppColors.textSecondary, fontSize: 15)),
        ],
      ),
    );
  }
}
