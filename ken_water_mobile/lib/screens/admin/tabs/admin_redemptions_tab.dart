import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../../../constants/app_colors.dart';
import '../../../services/api_service.dart';

class AdminRedemptionsTab extends StatefulWidget {
  const AdminRedemptionsTab({super.key});

  @override
  State<AdminRedemptionsTab> createState() => _AdminRedemptionsTabState();
}

class _AdminRedemptionsTabState extends State<AdminRedemptionsTab> {
  List<dynamic> _redemptions = [];
  List<dynamic> _rewards = [];
  List<dynamic> _vouchers = [];
  List<dynamic> _promos = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadAll();
  }

  Future<void> _loadAll() async {
    setState(() => _isLoading = true);
    final results = await Future.wait([
      ApiService.getAdminRedemptions(),
      ApiService.getAdminRewards(),
      ApiService.getAdminVouchers(),
      ApiService.getAdminPromos(),
    ]);
    if (mounted) {
      setState(() {
        _redemptions = results[0] as List<dynamic>;
        _rewards = results[1] as List<dynamic>;
        final voucherRes = results[2] as Map<String, dynamic>;
        _vouchers = voucherRes['vouchers'] ?? [];
        final promoRes = results[3] as Map<String, dynamic>;
        _promos = promoRes['promos'] ?? [];
        _isLoading = false;
      });
    }
  }

  // ─── Redemption Actions ────────────────────────

  Future<void> _validateRedemption(int id, String code) async {
    final confirm = await _showConfirmDialog(
      title: 'Validasi & Serahkan',
      message: 'Apakah Anda sudah menyerahkan hadiah ke pelanggan?\n\nKode: $code',
      confirmText: 'Ya, Validasi',
      confirmColor: AppColors.success,
      icon: Icons.check_circle_outline_rounded,
    );

    if (confirm == true) {
      _showLoadingDialog();
      final res = await ApiService.adminValidateRedemption(id, 'validated');
      if (!mounted) return;
      Navigator.pop(context); // close loading
      _showResultSnackbar(res);
      if (res['success'] == true) _loadAll();
    }
  }

  Future<void> _cancelRedemption(int id, String code) async {
    final confirm = await _showConfirmDialog(
      title: 'Batalkan Penukaran',
      message:
          'Yakin ingin membatalkan penukaran ini?\n\nPoin akan otomatis dikembalikan ke akun pelanggan.\n\nKode: $code',
      confirmText: 'Ya, Batalkan',
      confirmColor: AppColors.error,
      icon: Icons.cancel_outlined,
    );

    if (confirm == true) {
      _showLoadingDialog();
      final res = await ApiService.adminCancelRedemption(id);
      if (!mounted) return;
      Navigator.pop(context); // close loading
      _showResultSnackbar(res);
      if (res['success'] == true) _loadAll();
    }
  }

  // ─── Reward CRUD Actions ───────────────────────

  void _showRewardForm({Map? existing}) {
    final isEdit = existing != null;
    final nameCtrl = TextEditingController(text: existing?['name'] ?? '');
    final descCtrl =
        TextEditingController(text: existing?['description'] ?? '');
    final pointsCtrl = TextEditingController(
        text: existing != null ? '${existing['points_cost']}' : '');
    final stockCtrl = TextEditingController(
        text: existing != null && existing['stock'] != null
            ? '${existing['stock']}'
            : '');
    bool isActive = existing != null ? (existing['is_active'] ?? true) : true;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) => StatefulBuilder(
        builder: (ctx2, setModalState) => Padding(
          padding: EdgeInsets.fromLTRB(
              20, 16, 20, MediaQuery.of(ctx2).viewInsets.bottom + 20),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Handle
              Center(
                child: Container(
                  width: 40,
                  height: 4,
                  margin: const EdgeInsets.only(bottom: 16),
                  decoration: BoxDecoration(
                    color: const Color(0xFFCBD5E1),
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
              ),
              Text(
                isEdit ? 'Edit Reward' : 'Tambah Reward Baru',
                style: GoogleFonts.inter(
                  color: AppColors.textPrimary,
                  fontWeight: FontWeight.bold,
                  fontSize: 18,
                ),
              ),
              const SizedBox(height: 20),
              _buildTextField(nameCtrl, 'Nama Reward', Icons.card_giftcard),
              const SizedBox(height: 12),
              _buildTextField(descCtrl, 'Deskripsi (opsional)', Icons.description,
                  maxLines: 2),
              const SizedBox(height: 12),
              Row(
                children: [
                  Expanded(
                    child: _buildTextField(
                        pointsCtrl, 'Biaya Poin', Icons.stars_rounded,
                        isNumber: true),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: _buildTextField(
                        stockCtrl, 'Stok (opsional)', Icons.inventory_2,
                        isNumber: true),
                  ),
                ],
              ),
              const SizedBox(height: 14),
              // Active toggle
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                decoration: BoxDecoration(
                  color: const Color(0xFFF8FAFC),
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: const Color(0xFFE2E8F0)),
                ),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text('Status Aktif',
                        style: GoogleFonts.inter(
                            color: AppColors.textPrimary, fontSize: 14, fontWeight: FontWeight.w500)),
                    Switch(
                      value: isActive,
                      onChanged: (v) => setModalState(() => isActive = v),
                      activeColor: AppColors.primary,
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 20),
              Row(
                children: [
                  if (isEdit)
                    Expanded(
                      child: OutlinedButton.icon(
                        onPressed: () async {
                          Navigator.pop(ctx);
                          await _deleteReward(existing['id']);
                        },
                        icon: const Icon(Icons.delete_outline, size: 18),
                        label: const Text('Hapus'),
                        style: OutlinedButton.styleFrom(
                          foregroundColor: AppColors.error,
                          side: const BorderSide(color: AppColors.error),
                          padding: const EdgeInsets.symmetric(vertical: 14),
                          shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(12)),
                        ),
                      ),
                    ),
                  if (isEdit) const SizedBox(width: 12),
                  Expanded(
                    flex: isEdit ? 2 : 1,
                    child: ElevatedButton.icon(
                      onPressed: () async {
                        if (nameCtrl.text.trim().isEmpty ||
                            pointsCtrl.text.trim().isEmpty) {
                          ScaffoldMessenger.of(context).showSnackBar(
                            const SnackBar(
                              content: Text('Nama dan biaya poin wajib diisi'),
                              backgroundColor: AppColors.error,
                              behavior: SnackBarBehavior.floating,
                            ),
                          );
                          return;
                        }
                        Navigator.pop(ctx);
                        if (isEdit) {
                          await _updateReward(
                            existing['id'],
                            nameCtrl.text.trim(),
                            descCtrl.text.trim(),
                            int.tryParse(pointsCtrl.text) ?? 0,
                            int.tryParse(stockCtrl.text),
                            isActive,
                          );
                        } else {
                          await _createReward(
                            nameCtrl.text.trim(),
                            descCtrl.text.trim(),
                            int.tryParse(pointsCtrl.text) ?? 0,
                            int.tryParse(stockCtrl.text),
                            isActive,
                          );
                        }
                      },
                      icon: Icon(isEdit ? Icons.save : Icons.add, size: 18),
                      label: Text(isEdit ? 'Simpan' : 'Tambah Reward'),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppColors.primary,
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(12)),
                        elevation: 0,
                      ),
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildTextField(
      TextEditingController ctrl, String hint, IconData icon,
      {int maxLines = 1, bool isNumber = false}) {
    return TextField(
      controller: ctrl,
      maxLines: maxLines,
      keyboardType: isNumber ? TextInputType.number : TextInputType.text,
      style: GoogleFonts.inter(color: AppColors.textPrimary, fontSize: 14),
      decoration: InputDecoration(
        hintText: hint,
        hintStyle: GoogleFonts.inter(color: AppColors.textMuted, fontSize: 14),
        prefixIcon: Icon(icon, color: AppColors.textSecondary, size: 20),
        filled: true,
        fillColor: const Color(0xFFF8FAFC),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: Color(0xFFE2E8F0)),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: Color(0xFFE2E8F0)),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: AppColors.primary, width: 2),
        ),
        contentPadding:
            const EdgeInsets.symmetric(horizontal: 14, vertical: 14),
      ),
    );
  }

  Future<void> _createReward(String name, String desc, int points, int? stock,
      bool isActive) async {
    _showLoadingDialog();
    final res = await ApiService.adminCreateReward(
      name: name,
      description: desc.isNotEmpty ? desc : null,
      pointsCost: points,
      stock: stock,
      isActive: isActive,
    );
    if (!mounted) return;
    Navigator.pop(context);
    _showResultSnackbar(res);
    if (res['success'] == true) _loadAll();
  }

  Future<void> _updateReward(int id, String name, String desc, int points,
      int? stock, bool isActive) async {
    _showLoadingDialog();
    final updates = <String, dynamic>{
      'name': name,
      'points_cost': points,
      'is_active': isActive,
    };
    if (desc.isNotEmpty) updates['description'] = desc;
    if (stock != null) updates['stock'] = stock;

    final res = await ApiService.adminUpdateReward(id, updates);
    if (!mounted) return;
    Navigator.pop(context);
    _showResultSnackbar(res);
    if (res['success'] == true) _loadAll();
  }

  Future<void> _deleteReward(int id) async {
    final confirm = await _showConfirmDialog(
      title: 'Hapus Reward',
      message: 'Reward ini akan dihapus dari katalog.',
      confirmText: 'Ya, Hapus',
      confirmColor: AppColors.error,
      icon: Icons.delete_outline,
    );
    if (confirm != true) return;

    _showLoadingDialog();
    final res = await ApiService.adminDeleteReward(id);
    if (!mounted) return;
    Navigator.pop(context);
    _showResultSnackbar(res);
    if (res['success'] == true) _loadAll();
  }

  // ─── Helpers ───────────────────────────────────

  void _showLoadingDialog() {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (_) => const Center(
          child: CircularProgressIndicator(color: AppColors.primary)),
    );
  }

  void _showResultSnackbar(Map res) {
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(
      content: Text(res['message'] ?? ''),
      backgroundColor:
          res['success'] == true ? AppColors.success : AppColors.error,
      behavior: SnackBarBehavior.floating,
    ));
  }

  Future<bool?> _showConfirmDialog({
    required String title,
    required String message,
    required String confirmText,
    required Color confirmColor,
    required IconData icon,
  }) {
    return showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        backgroundColor: Colors.white,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(18)),
        title: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: confirmColor.withOpacity(0.15),
                borderRadius: BorderRadius.circular(10),
              ),
              child: Icon(icon, color: confirmColor, size: 22),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Text(title,
                  style: GoogleFonts.inter(
                      fontWeight: FontWeight.bold,
                      color: AppColors.textPrimary,
                      fontSize: 16)),
            ),
          ],
        ),
        content: Text(message,
            style: GoogleFonts.inter(color: AppColors.textSecondary, fontSize: 14)),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: Text('Batal',
                style: GoogleFonts.inter(color: AppColors.textSecondary)),
          ),
          ElevatedButton(
            onPressed: () => Navigator.pop(ctx, true),
            style: ElevatedButton.styleFrom(
              backgroundColor: confirmColor,
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(10)),
              padding:
                  const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
              elevation: 0,
            ),
            child: Text(confirmText,
                style: GoogleFonts.inter(fontWeight: FontWeight.bold)),
          ),
        ],
      ),
    );
  }

  // ─── BUILD ─────────────────────────────────────

  @override
  Widget build(BuildContext context) {
    return DefaultTabController(
      length: 4,
      child: Scaffold(
        backgroundColor: AppColors.background,
        appBar: AppBar(
          backgroundColor: Colors.white,
          surfaceTintColor: Colors.white,
          elevation: 0.5,
          shadowColor: const Color(0x0A000000),
          title: Text('Reward & Promo',
              style: GoogleFonts.inter(
                  fontWeight: FontWeight.bold,
                  fontSize: 18,
                  color: AppColors.textPrimary)),
          actions: [
            IconButton(
              icon: const Icon(Icons.refresh, color: AppColors.textSecondary),
              onPressed: _loadAll,
            ),
          ],
          bottom: TabBar(
            isScrollable: true,
            tabAlignment: TabAlignment.start,
            labelColor: AppColors.primary,
            unselectedLabelColor: AppColors.textSecondary,
            indicatorColor: AppColors.primary,
            indicatorWeight: 3,
            tabs: [
              Tab(
                  child: Text('Penukaran',
                      style: GoogleFonts.inter(fontWeight: FontWeight.w600, fontSize: 13))),
              Tab(
                  child: Text('Katalog Reward',
                      style: GoogleFonts.inter(fontWeight: FontWeight.w600, fontSize: 13))),
              Tab(
                  child: Text('Kelola Voucher',
                      style: GoogleFonts.inter(fontWeight: FontWeight.w600, fontSize: 13))),
              Tab(
                  child: Text('Promo Personal',
                      style: GoogleFonts.inter(fontWeight: FontWeight.w600, fontSize: 13))),
            ],
          ),
        ),
        body: _isLoading
            ? const Center(
                child: CircularProgressIndicator(color: AppColors.primary))
            : TabBarView(
                children: [
                  _buildRedemptionsView(),
                  _buildRewardsCatalogView(),
                  _buildVouchersView(),
                  _buildPromosView(),
                ],
              ),
      ),
    );
  }

  // ═══════════════════════════════════════════
  //  VIEW 1: VALIDASI PENUKARAN (MATCH WEB ADMIN)
  // ═══════════════════════════════════════════
  Widget _buildRedemptionsView() {
    final pending =
        _redemptions.where((r) => r['status'] == 'pending').toList();
    final validated =
        _redemptions.where((r) => r['status'] == 'validated').toList();
    final cancelled =
        _redemptions.where((r) => r['status'] == 'cancelled').toList();

    return RefreshIndicator(
      onRefresh: _loadAll,
      color: AppColors.primary,
      backgroundColor: Colors.white,
      child: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          // ── Summary Cards ──
          Row(
            children: [
              _buildSummaryCard(
                  'Menunggu', pending.length, const Color(0xFFD97706)),
              const SizedBox(width: 10),
              _buildSummaryCard(
                  'Divalidasi', validated.length, const Color(0xFF16A34A)),
              const SizedBox(width: 10),
              _buildSummaryCard(
                  'Dibatalkan', cancelled.length, const Color(0xFFDC2626)),
            ],
          ),
          const SizedBox(height: 20),

          // ── Pending Section ──
          if (pending.isNotEmpty) ...[
            _buildSectionTitle('Menunggu Validasi', Icons.pending_actions,
                badge: pending.length),
            const SizedBox(height: 10),
            ...pending.map((r) => _buildRedemptionCard(r)),
            const SizedBox(height: 24),
          ],

          // ── Validated Section ──
          if (validated.isNotEmpty) ...[
            _buildSectionTitle(
                'Sudah Divalidasi', Icons.check_circle_outline),
            const SizedBox(height: 10),
            ...validated.map((r) => _buildRedemptionCard(r)),
            const SizedBox(height: 24),
          ],

          // ── Cancelled Section ──
          if (cancelled.isNotEmpty) ...[
            _buildSectionTitle('Dibatalkan', Icons.cancel_outlined),
            const SizedBox(height: 10),
            ...cancelled.map((r) => _buildRedemptionCard(r)),
          ],

          // Empty state
          if (_redemptions.isEmpty)
            Padding(
              padding: const EdgeInsets.only(top: 60),
              child: Column(
                children: [
                  const Icon(Icons.card_giftcard,
                      size: 48, color: Color(0xFFCBD5E1)),
                  const SizedBox(height: 12),
                  Text('Belum ada penukaran reward',
                      style: GoogleFonts.inter(color: AppColors.textSecondary)),
                ],
              ),
            ),
        ],
      ),
    );
  }

  Widget _buildSummaryCard(String label, int count, Color color) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(14),
          border: Border.all(color: color.withOpacity(0.3)),
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
            Text('$count',
                style: GoogleFonts.inter(
                    color: color,
                    fontSize: 24,
                    fontWeight: FontWeight.bold)),
            const SizedBox(height: 2),
            Text(label,
                style: GoogleFonts.inter(
                    color: AppColors.textSecondary, fontSize: 11),
                textAlign: TextAlign.center),
          ],
        ),
      ),
    );
  }

  Widget _buildSectionTitle(String title, IconData icon, {int badge = 0}) {
    return Row(
      children: [
        Icon(icon, color: AppColors.primary, size: 20),
        const SizedBox(width: 8),
        Text(title,
            style: GoogleFonts.inter(
                color: AppColors.textPrimary,
                fontWeight: FontWeight.bold,
                fontSize: 15)),
        if (badge > 0) ...[
          const SizedBox(width: 8),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
            decoration: BoxDecoration(
              color: const Color(0xFFFEF3C7),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Text('$badge',
                style: GoogleFonts.inter(
                    color: const Color(0xFFD97706),
                    fontSize: 12,
                    fontWeight: FontWeight.bold)),
          ),
        ],
      ],
    );
  }

  Widget _buildRedemptionCard(Map r) {
    final id = r['id'] is int
        ? r['id']
        : int.tryParse(r['id'].toString()) ?? 0;
    final code = r['redemption_code'] ?? 'VCH-XXXX';
    final rewardTitle = r['reward_name'] ?? 'Reward Item';
    final customerName = r['customer_name'] ?? 'Pelanggan';
    final customerPhone = r['customer_phone'] ?? '';
    final points = r['points_used'] ?? 0;
    final status = (r['status'] ?? 'pending').toString().toLowerCase();
    final createdAt = r['created_at'] ?? '-';
    final isPending = status == 'pending';

    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(
          color: isPending
              ? const Color(0xFFF59E0B)
              : const Color(0xFFE2E8F0),
          width: isPending ? 1.5 : 1,
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
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header: Code + Status
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Container(
                padding:
                    const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                decoration: BoxDecoration(
                  color: const Color(0xFFF1F5F9),
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(color: const Color(0xFFCBD5E1)),
                ),
                child: Text(
                  code,
                  style: GoogleFonts.jetBrainsMono(
                    fontWeight: FontWeight.bold,
                    fontSize: 13,
                    color: AppColors.textPrimary,
                    letterSpacing: 1.2,
                  ),
                ),
              ),
              _buildStatusChip(status),
            ],
          ),
          const SizedBox(height: 12),

          // Reward name
          Text(rewardTitle,
              style: GoogleFonts.inter(
                  fontWeight: FontWeight.bold,
                  fontSize: 16,
                  color: AppColors.textPrimary)),
          const SizedBox(height: 6),

          // Customer info
          Row(
            children: [
              const Icon(Icons.person_outline, size: 14, color: AppColors.textSecondary),
              const SizedBox(width: 4),
              Text(customerName,
                  style: GoogleFonts.inter(
                      fontSize: 12, color: AppColors.textSecondary)),
              if (customerPhone.isNotEmpty) ...[
                Text(' • ', style: GoogleFonts.inter(color: AppColors.textMuted)),
                Text(customerPhone,
                    style: GoogleFonts.inter(
                        fontSize: 12, color: AppColors.textSecondary)),
              ],
            ],
          ),
          const SizedBox(height: 4),

          // Points & Date
          Row(
            children: [
              Icon(Icons.stars_rounded,
                  size: 14,
                  color: const Color(0xFFF59E0B)),
              const SizedBox(width: 4),
              Text('$points Poin',
                  style: GoogleFonts.inter(
                    fontSize: 12,
                    color: const Color(0xFFD97706),
                    fontWeight: FontWeight.w600,
                  )),
              const Spacer(),
              Text(createdAt,
                  style:
                      GoogleFonts.inter(fontSize: 11, color: AppColors.textMuted)),
            ],
          ),

          // Action Buttons (matching web admin: Validasi + Batalkan)
          if (isPending) ...[
            const SizedBox(height: 14),
            Row(
              children: [
                // Validasi & Serahkan
                Expanded(
                  flex: 3,
                  child: ElevatedButton.icon(
                    icon: const Icon(Icons.check_circle_outline, size: 18),
                    label: const Text('Validasi & Serahkan'),
                    onPressed: () => _validateRedemption(id, code),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppColors.success,
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(vertical: 12),
                      shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(10)),
                      elevation: 0,
                      textStyle: GoogleFonts.inter(
                          fontWeight: FontWeight.bold, fontSize: 13),
                    ),
                  ),
                ),
                const SizedBox(width: 10),
                // Batalkan
                Expanded(
                  flex: 2,
                  child: OutlinedButton.icon(
                    icon: const Icon(Icons.cancel_outlined, size: 18),
                    label: const Text('Batalkan'),
                    onPressed: () => _cancelRedemption(id, code),
                    style: OutlinedButton.styleFrom(
                      foregroundColor: AppColors.error,
                      side: BorderSide(
                          color: AppColors.error.withOpacity(0.5)),
                      padding: const EdgeInsets.symmetric(vertical: 12),
                      shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(10)),
                      textStyle: GoogleFonts.inter(
                          fontWeight: FontWeight.bold, fontSize: 13),
                    ),
                  ),
                ),
              ],
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildStatusChip(String status) {
    Color bg;
    Color fg;
    String label;

    switch (status) {
      case 'validated':
        bg = const Color(0xFFDCFCE7);
        fg = const Color(0xFF16A34A);
        label = 'Divalidasi';
        break;
      case 'cancelled':
        bg = const Color(0xFFFEE2E2);
        fg = const Color(0xFFDC2626);
        label = 'Dibatalkan';
        break;
      case 'pending':
      default:
        bg = const Color(0xFFFEF3C7);
        fg = const Color(0xFFD97706);
        label = 'Menunggu';
        break;
    }

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: bg,
        borderRadius: BorderRadius.circular(12),
      ),
      child: Text(label,
          style: GoogleFonts.inter(
              color: fg, fontSize: 11, fontWeight: FontWeight.bold)),
    );
  }

  // ═══════════════════════════════════════════
  //  VIEW 2: KATALOG REWARD (CRUD - MATCH WEB ADMIN)
  // ═══════════════════════════════════════════
  Widget _buildRewardsCatalogView() {
    return Stack(
      children: [
        RefreshIndicator(
          onRefresh: _loadAll,
          color: AppColors.primary,
          backgroundColor: Colors.white,
          child: _rewards.isEmpty
              ? ListView(
                  children: [
                    const SizedBox(height: 100),
                    Column(
                      children: [
                        const Icon(Icons.card_giftcard,
                            size: 48, color: Color(0xFFCBD5E1)),
                        const SizedBox(height: 12),
                        Text('Belum ada reward di katalog',
                            style:
                                GoogleFonts.inter(color: AppColors.textSecondary)),
                        const SizedBox(height: 16),
                        ElevatedButton.icon(
                          onPressed: () => _showRewardForm(),
                          icon: const Icon(Icons.add, size: 18),
                          label: const Text('Tambah Reward Pertama'),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: AppColors.primary,
                            foregroundColor: Colors.white,
                            shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(12)),
                            padding: const EdgeInsets.symmetric(
                                horizontal: 20, vertical: 12),
                            elevation: 0,
                          ),
                        ),
                      ],
                    ),
                  ],
                )
              : ListView.separated(
                  padding: const EdgeInsets.fromLTRB(16, 16, 16, 80),
                  itemCount: _rewards.length,
                  separatorBuilder: (_, __) => const SizedBox(height: 12),
                  itemBuilder: (ctx, i) => _buildRewardManageCard(_rewards[i]),
                ),
        ),
        // FAB
        Positioned(
          bottom: 20,
          right: 20,
          child: FloatingActionButton.extended(
            onPressed: () => _showRewardForm(),
            icon: const Icon(Icons.add_rounded),
            label: Text('Tambah',
                style: GoogleFonts.inter(fontWeight: FontWeight.bold)),
            backgroundColor: AppColors.primary,
            foregroundColor: Colors.white,
          ),
        ),
      ],
    );
  }

  Widget _buildRewardManageCard(Map r) {
    final isActive = r['is_active'] == true;
    return GestureDetector(
      onTap: () => _showRewardForm(existing: r),
      child: Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(
            color: isActive
                ? const Color(0xFFE2E8F0)
                : const Color(0xFFF1F5F9),
          ),
          boxShadow: const [
            BoxShadow(
              color: Color(0x06000000),
              blurRadius: 8,
              offset: Offset(0, 2),
            ),
          ],
        ),
        child: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: isActive
                    ? const Color(0xFFFEF3C7)
                    : const Color(0xFFF1F5F9),
                borderRadius: BorderRadius.circular(12),
              ),
              child: Icon(
                Icons.card_giftcard_rounded,
                color: isActive
                    ? const Color(0xFFD97706)
                    : AppColors.textMuted,
                size: 24,
              ),
            ),
            const SizedBox(width: 14),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Expanded(
                        child: Text(r['name'] ?? '',
                            style: GoogleFonts.inter(
                              color: AppColors.textPrimary,
                              fontWeight: FontWeight.bold,
                              fontSize: 14,
                            )),
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(
                            horizontal: 8, vertical: 3),
                        decoration: BoxDecoration(
                          color: isActive
                              ? const Color(0xFFDCFCE7)
                              : const Color(0xFFF1F5F9),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Text(
                          isActive ? 'Aktif' : 'Nonaktif',
                          style: GoogleFonts.inter(
                            color: isActive
                                ? const Color(0xFF16A34A)
                                : AppColors.textMuted,
                            fontSize: 10,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ),
                    ],
                  ),
                  if (r['description'] != null &&
                      r['description'].toString().isNotEmpty) ...[
                    const SizedBox(height: 4),
                    Text(r['description'],
                        style: GoogleFonts.inter(
                            color: AppColors.textSecondary, fontSize: 12),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis),
                  ],
                  const SizedBox(height: 6),
                  Row(
                    children: [
                      const Icon(Icons.stars_rounded,
                          size: 14,
                          color: Color(0xFFF59E0B)),
                      const SizedBox(width: 4),
                      Text('${r['points_cost']} Poin',
                          style: GoogleFonts.inter(
                            color: const Color(0xFFD97706),
                            fontWeight: FontWeight.bold,
                            fontSize: 12,
                          )),
                      const SizedBox(width: 16),
                      const Icon(Icons.inventory_2_outlined,
                          size: 14, color: AppColors.textMuted),
                      const SizedBox(width: 4),
                      Text(
                        r['stock'] != null
                            ? 'Stok: ${r['stock']}'
                            : 'Unlimited',
                        style: GoogleFonts.inter(
                            color: AppColors.textSecondary, fontSize: 12),
                      ),
                    ],
                  ),
                ],
              ),
            ),
            const SizedBox(width: 8),
            const Icon(Icons.chevron_right, color: AppColors.textMuted, size: 20),
          ],
        ),
      ),
    );
  }

  // ═══════════════════════════════════════════
  //  VIEW 3: KELOLA VOUCHER (MATCH WEB ADMIN)
  // ═══════════════════════════════════════════
  Widget _buildVouchersView() {
    return Scaffold(
      backgroundColor: Colors.transparent,
      floatingActionButton: FloatingActionButton.extended(
        backgroundColor: AppColors.primary,
        foregroundColor: Colors.white,
        icon: const Icon(Icons.add_rounded),
        label: Text('Buat Voucher Baru', style: GoogleFonts.inter(fontWeight: FontWeight.bold)),
        onPressed: _showCreateVoucherDialog,
      ),
      body: RefreshIndicator(
        onRefresh: _loadAll,
        color: AppColors.primary,
        backgroundColor: Colors.white,
        child: _vouchers.isEmpty
            ? Center(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(Icons.confirmation_number_outlined, size: 48, color: AppColors.textMuted.withOpacity(0.5)),
                    const SizedBox(height: 12),
                    Text('Belum ada voucher.', style: GoogleFonts.inter(color: AppColors.textSecondary, fontSize: 14)),
                  ],
                ),
              )
            : ListView.builder(
                padding: const EdgeInsets.fromLTRB(16, 16, 16, 80),
                itemCount: _vouchers.length,
                itemBuilder: (ctx, i) {
                  final v = _vouchers[i];
                  final isNominal = (v['discount_type'] ?? 'nominal') == 'nominal';
                  final discVal = isNominal
                      ? 'Rp ${(v['discount_value'] ?? 0).toInt()}'
                      : '${(v['discount_value'] ?? 0).toInt()}%';

                  return Container(
                    margin: const EdgeInsets.only(bottom: 12),
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(16),
                      border: Border.all(color: const Color(0xFFE2E8F0)),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withOpacity(0.02),
                          blurRadius: 8,
                          offset: const Offset(0, 2),
                        ),
                      ],
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                              decoration: BoxDecoration(
                                color: const Color(0xFFEFF6FF),
                                borderRadius: BorderRadius.circular(8),
                                border: Border.all(color: const Color(0xFFBFDBFE)),
                              ),
                              child: Text(
                                v['code'] ?? '',
                                style: GoogleFonts.jetBrainsMono(
                                  fontWeight: FontWeight.bold,
                                  color: AppColors.primary,
                                  fontSize: 13,
                                  letterSpacing: 1,
                                ),
                              ),
                            ),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                              decoration: BoxDecoration(
                                color: const Color(0xFFFEF3C7),
                                borderRadius: BorderRadius.circular(8),
                              ),
                              child: Text(
                                'Diskon $discVal',
                                style: GoogleFonts.inter(
                                  color: const Color(0xFFB45309),
                                  fontWeight: FontWeight.bold,
                                  fontSize: 12,
                                ),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 10),
                        Text(
                          v['name'] ?? '',
                          style: GoogleFonts.inter(fontWeight: FontWeight.bold, fontSize: 15, color: AppColors.textPrimary),
                        ),
                        if (v['description'] != null && v['description'].toString().isNotEmpty) ...[
                          const SizedBox(height: 4),
                          Text(
                            v['description'],
                            style: GoogleFonts.inter(color: AppColors.textSecondary, fontSize: 12),
                          ),
                        ],
                        const SizedBox(height: 12),
                        Row(
                          children: [
                            const Icon(Icons.date_range_outlined, size: 14, color: AppColors.textMuted),
                            const SizedBox(width: 4),
                            Text(
                              'Berlaku: ${v['valid_from'] ?? '-'} s/d ${v['valid_until'] ?? '-'}',
                              style: GoogleFonts.inter(color: AppColors.textMuted, fontSize: 11),
                            ),
                          ],
                        ),
                        const SizedBox(height: 14),
                        Row(
                          children: [
                            Expanded(
                              child: OutlinedButton.icon(
                                icon: const Icon(Icons.send_rounded, size: 16),
                                label: Text('Bagikan ke Pelanggan', style: GoogleFonts.inter(fontWeight: FontWeight.w600, fontSize: 13)),
                                onPressed: () => _showAssignVoucherDialog(v),
                                style: OutlinedButton.styleFrom(
                                  foregroundColor: AppColors.primary,
                                  side: const BorderSide(color: AppColors.primary),
                                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                                  padding: const EdgeInsets.symmetric(vertical: 10),
                                ),
                              ),
                            ),
                            const SizedBox(width: 8),
                            IconButton(
                              icon: const Icon(Icons.delete_outline, color: AppColors.error),
                              tooltip: 'Hapus Voucher',
                              onPressed: () => _deleteVoucher(v),
                            ),
                          ],
                        ),
                      ],
                    ),
                  );
                },
              ),
      ),
    );
  }

  Future<void> _deleteVoucher(Map v) async {
    final name = v['name'] ?? v['code'] ?? 'Voucher';
    final confirm = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Hapus Voucher'),
        content: Text('Yakin ingin menghapus voucher "$name"?'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Batal')),
          ElevatedButton(
            onPressed: () => Navigator.pop(ctx, true),
            style: ElevatedButton.styleFrom(backgroundColor: AppColors.error),
            child: const Text('Hapus', style: TextStyle(color: Colors.white)),
          ),
        ],
      ),
    );

    if (confirm == true) {
      final id = v['id'] is int ? v['id'] : int.tryParse(v['id'].toString()) ?? 0;
      final res = await ApiService.adminDeleteVoucher(id);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(res['message'] ?? 'Voucher berhasil dihapus'),
            backgroundColor: res['success'] == true ? AppColors.success : AppColors.error,
          ),
        );
        _loadAll();
      }
    }
  }

  void _showCreateVoucherDialog() {
    final codeCtrl = TextEditingController();
    final nameCtrl = TextEditingController();
    final descCtrl = TextEditingController();
    final discValueCtrl = TextEditingController();
    final minPurchaseCtrl = TextEditingController(text: '0');
    final validFromCtrl = TextEditingController(text: DateTime.now().toString().substring(0, 10));
    final validUntilCtrl = TextEditingController(
      text: DateTime.now().add(const Duration(days: 30)).toString().substring(0, 10),
    );
    String discountType = 'nominal';
    bool isSaving = false;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(24))),
      builder: (ctx) {
        return StatefulBuilder(
          builder: (context, setModalState) {
            return Padding(
              padding: EdgeInsets.only(
                left: 20,
                right: 20,
                top: 20,
                bottom: MediaQuery.of(context).viewInsets.bottom + 24,
              ),
              child: SingleChildScrollView(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    Center(
                      child: Container(
                        width: 40,
                        height: 4,
                        margin: const EdgeInsets.only(bottom: 16),
                        decoration: BoxDecoration(color: const Color(0xFFCBD5E1), borderRadius: BorderRadius.circular(2)),
                      ),
                    ),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text('Buat Voucher Baru', style: GoogleFonts.inter(fontSize: 18, fontWeight: FontWeight.bold)),
                        IconButton(icon: const Icon(Icons.close), onPressed: () => Navigator.pop(ctx)),
                      ],
                    ),
                    const SizedBox(height: 16),
                    TextField(
                      controller: codeCtrl,
                      textCapitalization: TextCapitalization.characters,
                      decoration: const InputDecoration(
                        labelText: 'Kode Voucher (Contoh: HEMAT5K) *',
                        prefixIcon: Icon(Icons.confirmation_number_outlined),
                        border: OutlineInputBorder(borderRadius: BorderRadius.all(Radius.circular(12))),
                        contentPadding: EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                      ),
                    ),
                    const SizedBox(height: 12),
                    TextField(
                      controller: nameCtrl,
                      decoration: const InputDecoration(
                        labelText: 'Nama Voucher (Contoh: Diskon Pelanggan Baru) *',
                        prefixIcon: Icon(Icons.label_outline),
                        border: OutlineInputBorder(borderRadius: BorderRadius.all(Radius.circular(12))),
                        contentPadding: EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                      ),
                    ),
                    const SizedBox(height: 12),
                    Row(
                      children: [
                        Expanded(
                          child: DropdownButtonFormField<String>(
                            value: discountType,
                            decoration: const InputDecoration(
                              labelText: 'Tipe Diskon',
                              border: OutlineInputBorder(borderRadius: BorderRadius.all(Radius.circular(12))),
                              contentPadding: EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                            ),
                            items: const [
                              DropdownMenuItem(value: 'nominal', child: Text('Nominal (Rp)')),
                              DropdownMenuItem(value: 'persen', child: Text('Persentase (%)')),
                            ],
                            onChanged: (val) {
                              if (val != null) setModalState(() => discountType = val);
                            },
                          ),
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                          child: TextField(
                            controller: discValueCtrl,
                            keyboardType: TextInputType.number,
                            decoration: InputDecoration(
                              labelText: 'Nilai Diskon *',
                              prefixText: discountType == 'nominal' ? 'Rp ' : '',
                              suffixText: discountType == 'persen' ? '%' : '',
                              border: const OutlineInputBorder(borderRadius: BorderRadius.all(Radius.circular(12))),
                              contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),
                    TextField(
                      controller: minPurchaseCtrl,
                      keyboardType: TextInputType.number,
                      decoration: const InputDecoration(
                        labelText: 'Minimal Belanja (Rp)',
                        prefixText: 'Rp ',
                        border: OutlineInputBorder(borderRadius: BorderRadius.all(Radius.circular(12))),
                        contentPadding: EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                      ),
                    ),
                    const SizedBox(height: 12),
                    Row(
                      children: [
                        Expanded(
                          child: TextField(
                            controller: validFromCtrl,
                            decoration: const InputDecoration(
                              labelText: 'Mulai Berlaku',
                              hintText: 'YYYY-MM-DD',
                              border: OutlineInputBorder(borderRadius: BorderRadius.all(Radius.circular(12))),
                              contentPadding: EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                            ),
                          ),
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                          child: TextField(
                            controller: validUntilCtrl,
                            decoration: const InputDecoration(
                              labelText: 'Kedaluwarsa',
                              hintText: 'YYYY-MM-DD',
                              border: OutlineInputBorder(borderRadius: BorderRadius.all(Radius.circular(12))),
                              contentPadding: EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 24),
                    ElevatedButton(
                      onPressed: isSaving
                          ? null
                          : () async {
                              final code = codeCtrl.text.trim();
                              final name = nameCtrl.text.trim();
                              final discVal = double.tryParse(discValueCtrl.text) ?? 0;
                              if (code.isEmpty || name.isEmpty || discVal <= 0) {
                                ScaffoldMessenger.of(context).showSnackBar(
                                  const SnackBar(
                                    content: Text('Harap lengkapi kode, nama, dan nilai diskon'),
                                    backgroundColor: AppColors.error,
                                    behavior: SnackBarBehavior.floating,
                                  ),
                                );
                                return;
                              }

                              setModalState(() => isSaving = true);
                              final res = await ApiService.adminCreateVoucher(
                                code: code,
                                name: name,
                                description: descCtrl.text.trim().isNotEmpty ? descCtrl.text.trim() : null,
                                discountType: discountType,
                                discountValue: discVal,
                                minPurchase: double.tryParse(minPurchaseCtrl.text),
                                validFrom: validFromCtrl.text,
                                validUntil: validUntilCtrl.text,
                              );
                              setModalState(() => isSaving = false);

                              if (!mounted) return;
                              if (res['success'] == true) {
                                Navigator.pop(ctx);
                                ScaffoldMessenger.of(context).showSnackBar(
                                  const SnackBar(
                                    content: Text('Voucher baru berhasil dibuat!'),
                                    backgroundColor: AppColors.success,
                                    behavior: SnackBarBehavior.floating,
                                  ),
                                );
                                _loadAll();
                              } else {
                                ScaffoldMessenger.of(context).showSnackBar(
                                  SnackBar(
                                    content: Text(res['message'] ?? 'Gagal membuat voucher'),
                                    backgroundColor: AppColors.error,
                                    behavior: SnackBarBehavior.floating,
                                  ),
                                );
                              }
                            },
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppColors.primary,
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        elevation: 0,
                      ),
                      child: isSaving
                          ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                          : Text('Simpan Voucher', style: GoogleFonts.inter(fontWeight: FontWeight.bold, fontSize: 15)),
                    ),
                  ],
                ),
              ),
            );
          },
        );
      },
    );
  }

  void _showAssignVoucherDialog(Map voucher) async {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (_) => const Center(child: CircularProgressIndicator(color: AppColors.primary)),
    );

    final customers = await ApiService.getAdminCustomers();
    if (!mounted) return;
    Navigator.pop(context); // close loader

    final selectedIds = <int>{};
    bool isAssigning = false;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(24))),
      builder: (ctx) {
        return StatefulBuilder(
          builder: (context, setModalState) {
            return Container(
              constraints: BoxConstraints(maxHeight: MediaQuery.of(context).size.height * 0.75),
              padding: const EdgeInsets.all(20),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  Center(
                    child: Container(
                      width: 40,
                      height: 4,
                      margin: const EdgeInsets.only(bottom: 14),
                      decoration: BoxDecoration(color: const Color(0xFFCBD5E1), borderRadius: BorderRadius.circular(2)),
                    ),
                  ),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text('Bagikan Voucher', style: GoogleFonts.inter(fontSize: 16, fontWeight: FontWeight.bold)),
                            Text(voucher['name'] ?? '', style: GoogleFonts.inter(fontSize: 13, color: AppColors.primary)),
                          ],
                        ),
                      ),
                      TextButton(
                        onPressed: () {
                          setModalState(() {
                            if (selectedIds.length == customers.length) {
                              selectedIds.clear();
                            } else {
                              for (var c in customers) {
                                if (c['id'] != null) selectedIds.add(c['id'] as int);
                              }
                            }
                          });
                        },
                        child: Text(
                          selectedIds.length == customers.length ? 'Batal Semua' : 'Pilih Semua',
                          style: GoogleFonts.inter(fontSize: 12, fontWeight: FontWeight.w600),
                        ),
                      ),
                    ],
                  ),
                  const Divider(height: 20),
                  Expanded(
                    child: customers.isEmpty
                        ? const Center(child: Text('Tidak ada pelanggan aktif.'))
                        : ListView.builder(
                            itemCount: customers.length,
                            itemBuilder: (ctx, i) {
                              final c = customers[i];
                              final id = c['id'] as int?;
                              if (id == null) return const SizedBox();
                              final isSelected = selectedIds.contains(id);

                              return CheckboxListTile(
                                value: isSelected,
                                activeColor: AppColors.primary,
                                title: Text(c['name'] ?? 'Pelanggan', style: GoogleFonts.inter(fontWeight: FontWeight.w600, fontSize: 14)),
                                subtitle: Text('${c['customer_id'] ?? ''} • ${c['phone'] ?? ''}', style: GoogleFonts.inter(fontSize: 12)),
                                onChanged: (val) {
                                  setModalState(() {
                                    if (val == true) {
                                      selectedIds.add(id);
                                    } else {
                                      selectedIds.remove(id);
                                    }
                                  });
                                },
                              );
                            },
                          ),
                  ),
                  const SizedBox(height: 14),
                  ElevatedButton(
                    onPressed: isAssigning || selectedIds.isEmpty
                        ? null
                        : () async {
                            setModalState(() => isAssigning = true);
                            final res = await ApiService.adminAssignVoucher(voucher['id'], selectedIds.toList());
                            setModalState(() => isAssigning = false);

                            if (!mounted) return;
                            if (res['success'] == true) {
                              Navigator.pop(ctx);
                              ScaffoldMessenger.of(context).showSnackBar(
                                SnackBar(
                                  content: Text('Voucher berhasil dibagikan ke ${res['assigned_count']} pelanggan!'),
                                  backgroundColor: AppColors.success,
                                  behavior: SnackBarBehavior.floating,
                                ),
                              );
                            } else {
                              ScaffoldMessenger.of(context).showSnackBar(
                                SnackBar(
                                  content: Text(res['message'] ?? 'Gagal membagikan voucher'),
                                  backgroundColor: AppColors.error,
                                  behavior: SnackBarBehavior.floating,
                                ),
                              );
                            }
                          },
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppColors.primary,
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(vertical: 14),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      elevation: 0,
                    ),
                    child: isAssigning
                        ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                        : Text('Kirim ke ${selectedIds.length} Pelanggan', style: GoogleFonts.inter(fontWeight: FontWeight.bold)),
                  ),
                ],
              ),
            );
          },
        );
      },
    );
  }

  // ═══════════════════════════════════════════
  //  VIEW 4: MANAJEMEN PROMO PERSONAL
  // ═══════════════════════════════════════════
  Widget _buildPromosView() {
    return RefreshIndicator(
      onRefresh: _loadAll,
      color: AppColors.primary,
      child: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                'Daftar Promo Personal',
                style: GoogleFonts.inter(
                  fontSize: 16,
                  fontWeight: FontWeight.bold,
                  color: AppColors.textPrimary,
                ),
              ),
              ElevatedButton.icon(
                onPressed: _showCreatePromoModal,
                icon: const Icon(Icons.add, size: 16),
                label: Text('Buat Promo',
                    style: GoogleFonts.inter(fontWeight: FontWeight.bold, fontSize: 13)),
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppColors.primary,
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                  elevation: 0,
                ),
              ),
            ],
          ),
          const SizedBox(height: 14),
          if (_promos.isEmpty)
            Container(
              padding: const EdgeInsets.symmetric(vertical: 40, horizontal: 20),
              alignment: Alignment.center,
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: const Color(0xFFE2E8F0)),
              ),
              child: Column(
                children: [
                  const Icon(Icons.discount_outlined, color: AppColors.textMuted, size: 48),
                  const SizedBox(height: 12),
                  Text(
                    'Belum Ada Promo Personal',
                    style: GoogleFonts.inter(fontWeight: FontWeight.bold, fontSize: 15, color: AppColors.textPrimary),
                  ),
                  const SizedBox(height: 6),
                  Text(
                    'Berikan promo atau potongan khusus langsung ke pelanggan pilihan Anda.',
                    textAlign: TextAlign.center,
                    style: GoogleFonts.inter(fontSize: 13, color: AppColors.textSecondary),
                  ),
                  const SizedBox(height: 16),
                  ElevatedButton.icon(
                    onPressed: _showCreatePromoModal,
                    icon: const Icon(Icons.add, size: 16),
                    label: const Text('Buat Promo Sekarang'),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppColors.primary,
                      foregroundColor: Colors.white,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                    ),
                  ),
                ],
              ),
            )
          else
            ListView.separated(
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              itemCount: _promos.length,
              separatorBuilder: (_, __) => const SizedBox(height: 12),
              itemBuilder: (ctx, i) => _buildPromoCard(_promos[i]),
            ),
          const SizedBox(height: 20),
        ],
      ),
    );
  }

  Widget _buildPromoCard(Map p) {
    final name = p['name'] ?? 'Promo Personal';
    final customerName = p['customer_name'] ?? '-';
    final customerId = p['customer_id'] ?? '';
    final discountType = p['discount_type'] ?? 'nominal';
    final discountValue = (p['discount_value'] is num) ? p['discount_value'] : double.tryParse(p['discount_value'].toString()) ?? 0;
    final validFrom = p['valid_from'] ?? '-';
    final validUntil = p['valid_until'] ?? '-';
    final isUsed = p['is_used'] == true;
    final isActive = p['is_active'] == true;
    final description = p['description'];

    final statusText = isUsed ? 'Sudah Digunakan' : (isActive ? 'Aktif' : 'Non-aktif');
    final statusColor = isUsed ? Colors.grey : (isActive ? AppColors.success : AppColors.error);

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(
          color: isActive && !isUsed ? AppColors.primary.withOpacity(0.3) : const Color(0xFFE2E8F0),
          width: isActive && !isUsed ? 1.5 : 1,
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
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      name,
                      style: GoogleFonts.inter(
                        fontSize: 15,
                        fontWeight: FontWeight.bold,
                        color: AppColors.textPrimary,
                      ),
                    ),
                    const SizedBox(height: 3),
                    Row(
                      children: [
                        const Icon(Icons.person_outline, size: 14, color: AppColors.textSecondary),
                        const SizedBox(width: 4),
                        Text(
                          '$customerName ($customerId)',
                          style: GoogleFonts.inter(fontSize: 12, color: AppColors.textSecondary),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                decoration: BoxDecoration(
                  color: statusColor.withOpacity(0.12),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Text(
                  statusText,
                  style: GoogleFonts.inter(
                    fontSize: 11,
                    fontWeight: FontWeight.w600,
                    color: statusColor,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
            decoration: BoxDecoration(
              color: const Color(0xFFF8FAFC),
              borderRadius: BorderRadius.circular(10),
              border: Border.all(color: const Color(0xFFE2E8F0)),
            ),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Row(
                  children: [
                    const Icon(Icons.confirmation_number_outlined, color: AppColors.primary, size: 18),
                    const SizedBox(width: 8),
                    Text(
                      discountType == 'persen' ? 'Diskon ${discountValue.toInt()}%' : 'Potongan Rp ${discountValue.toInt()}',
                      style: GoogleFonts.inter(
                        fontWeight: FontWeight.bold,
                        fontSize: 14,
                        color: AppColors.primary,
                      ),
                    ),
                  ],
                ),
                Text(
                  'Hingga $validUntil',
                  style: GoogleFonts.inter(
                    fontSize: 11,
                    color: AppColors.textMuted,
                  ),
                ),
              ],
            ),
          ),
          if (description != null && description.toString().isNotEmpty) ...[
            const SizedBox(height: 8),
            Text(
              description,
              style: GoogleFonts.inter(fontSize: 12, color: AppColors.textSecondary, fontStyle: FontStyle.italic),
            ),
          ],
          const SizedBox(height: 8),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                'Periode: $validFrom s/d $validUntil',
                style: GoogleFonts.inter(fontSize: 11, color: AppColors.textMuted),
              ),
              if (!isUsed)
                InkWell(
                  onTap: () async {
                    final promoId = p['id'] is int ? p['id'] : int.tryParse(p['id'].toString()) ?? 0;
                    final res = await ApiService.adminTogglePromo(promoId);
                    if (mounted) {
                      ScaffoldMessenger.of(context).showSnackBar(
                        SnackBar(
                          content: Text(res['message'] ?? 'Status promo berhasil diperbarui'),
                          backgroundColor: res['success'] == true ? AppColors.success : AppColors.error,
                          behavior: SnackBarBehavior.floating,
                        ),
                      );
                      _loadAll();
                    }
                  },
                  borderRadius: BorderRadius.circular(8),
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                    decoration: BoxDecoration(
                      color: isActive ? const Color(0xFFFEF2F2) : const Color(0xFFF0FDF4),
                      borderRadius: BorderRadius.circular(8),
                      border: Border.all(
                        color: isActive ? const Color(0xFFFECACA) : const Color(0xFFBBF7D0),
                      ),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(
                          isActive ? Icons.block_flipped : Icons.check_circle_rounded,
                          size: 14,
                          color: isActive ? AppColors.error : AppColors.success,
                        ),
                        const SizedBox(width: 4),
                        Text(
                          isActive ? 'Nonaktifkan' : 'Aktifkan',
                          style: GoogleFonts.inter(
                            fontSize: 11,
                            fontWeight: FontWeight.bold,
                            color: isActive ? AppColors.error : AppColors.success,
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              const SizedBox(width: 8),
              InkWell(
                onTap: () async {
                  final confirm = await showDialog<bool>(
                    context: context,
                    builder: (ctx) => AlertDialog(
                      title: const Text('Hapus Promo'),
                      content: Text('Yakin ingin menghapus promo "$name"?'),
                      actions: [
                        TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Batal')),
                        ElevatedButton(
                          onPressed: () => Navigator.pop(ctx, true),
                          style: ElevatedButton.styleFrom(backgroundColor: AppColors.error),
                          child: const Text('Hapus', style: TextStyle(color: Colors.white)),
                        ),
                      ],
                    ),
                  );
                  if (confirm == true) {
                    final promoId = p['id'] is int ? p['id'] : int.tryParse(p['id'].toString()) ?? 0;
                    final res = await ApiService.adminDeletePromo(promoId);
                    if (mounted) {
                      ScaffoldMessenger.of(context).showSnackBar(
                        SnackBar(
                          content: Text(res['message'] ?? 'Promo berhasil dihapus'),
                          backgroundColor: res['success'] == true ? AppColors.success : AppColors.error,
                        ),
                      );
                      _loadAll();
                    }
                  }
                },
                borderRadius: BorderRadius.circular(8),
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(
                    color: const Color(0xFFFEF2F2),
                    borderRadius: BorderRadius.circular(8),
                    border: Border.all(color: const Color(0xFFFECACA)),
                  ),
                  child: const Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(Icons.delete_outline, size: 14, color: AppColors.error),
                      SizedBox(width: 4),
                      Text('Hapus', style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: AppColors.error)),
                    ],
                  ),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  void _showCreatePromoModal() async {
    _showLoadingDialog();
    final res = await ApiService.getAdminCustomers();
    if (!mounted) return;
    Navigator.pop(context); // close loading

    final customers = res;
    if (customers.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Tidak ada data pelanggan untuk diberikan promo.')),
      );
      return;
    }

    final nameCtrl = TextEditingController();
    final descCtrl = TextEditingController();
    final valueCtrl = TextEditingController();
    String discountType = 'nominal';
    int? selectedUserId;
    String selectedCustomerName = '';
    bool isSubmitting = false;

    DateTime validFrom = DateTime.now();
    DateTime validUntil = DateTime.now().add(const Duration(days: 14));

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (ctx) {
        return StatefulBuilder(
          builder: (context, setModalState) {
            final validFromStr = '${validFrom.year}-${validFrom.month.toString().padLeft(2, '0')}-${validFrom.day.toString().padLeft(2, '0')}';
            final validUntilStr = '${validUntil.year}-${validUntil.month.toString().padLeft(2, '0')}-${validUntil.day.toString().padLeft(2, '0')}';

            return Padding(
              padding: EdgeInsets.only(
                left: 20,
                right: 20,
                top: 20,
                bottom: MediaQuery.of(context).viewInsets.bottom + 20,
              ),
              child: SingleChildScrollView(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text(
                          'Buat Promo Personal',
                          style: GoogleFonts.inter(fontSize: 17, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
                        ),
                        IconButton(
                          icon: const Icon(Icons.close, color: AppColors.textSecondary),
                          onPressed: () => Navigator.pop(ctx),
                        ),
                      ],
                    ),
                    const Divider(height: 16),
                    const SizedBox(height: 8),

                    // Pilih Pelanggan
                    Text('Pilih Pelanggan Target *',
                        style: GoogleFonts.inter(fontWeight: FontWeight.w600, fontSize: 13, color: AppColors.textPrimary)),
                    const SizedBox(height: 6),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 12),
                      decoration: BoxDecoration(
                        border: Border.all(color: const Color(0xFFCBD5E1)),
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: DropdownButtonHideUnderline(
                        child: DropdownButton<int>(
                          isExpanded: true,
                          hint: Text('Pilih pelanggan', style: GoogleFonts.inter(fontSize: 13, color: AppColors.textMuted)),
                          value: selectedUserId,
                          items: customers.map<DropdownMenuItem<int>>((c) {
                            final int id = c['id'] is int ? c['id'] : int.tryParse(c['id'].toString()) ?? 0;
                            return DropdownMenuItem<int>(
                              value: id,
                              child: Text('${c['name']} (${c['customer_id'] ?? '-'})',
                                  style: GoogleFonts.inter(fontSize: 13, color: AppColors.textPrimary)),
                            );
                          }).toList(),
                          onChanged: (val) {
                            setModalState(() {
                              selectedUserId = val;
                              final c = customers.firstWhere((x) => x['id'] == val, orElse: () => {});
                              selectedCustomerName = c['name'] ?? '';
                            });
                          },
                        ),
                      ),
                    ),
                    const SizedBox(height: 14),

                    // Nama Promo
                    TextField(
                      controller: nameCtrl,
                      decoration: const InputDecoration(
                        labelText: 'Nama Promo *',
                        hintText: 'Misal: Promo Ulang Tahun 10%',
                        border: OutlineInputBorder(borderRadius: BorderRadius.all(Radius.circular(10))),
                        contentPadding: EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                      ),
                    ),
                    const SizedBox(height: 14),

                    // Tipe Diskon
                    Text('Tipe Diskon *',
                        style: GoogleFonts.inter(fontWeight: FontWeight.w600, fontSize: 13, color: AppColors.textPrimary)),
                    const SizedBox(height: 6),
                    Row(
                      children: [
                        Expanded(
                          child: InkWell(
                            onTap: () => setModalState(() => discountType = 'nominal'),
                            borderRadius: BorderRadius.circular(8),
                            child: Container(
                              padding: const EdgeInsets.symmetric(vertical: 10),
                              decoration: BoxDecoration(
                                color: discountType == 'nominal' ? AppColors.primary.withOpacity(0.12) : const Color(0xFFF1F5F9),
                                borderRadius: BorderRadius.circular(8),
                                border: Border.all(
                                  color: discountType == 'nominal' ? AppColors.primary : const Color(0xFFCBD5E1),
                                ),
                              ),
                              alignment: Alignment.center,
                              child: Text('Nominal (Rp)',
                                  style: GoogleFonts.inter(
                                      fontWeight: FontWeight.w600,
                                      color: discountType == 'nominal' ? AppColors.primary : AppColors.textSecondary)),
                            ),
                          ),
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                          child: InkWell(
                            onTap: () => setModalState(() => discountType = 'persen'),
                            borderRadius: BorderRadius.circular(8),
                            child: Container(
                              padding: const EdgeInsets.symmetric(vertical: 10),
                              decoration: BoxDecoration(
                                color: discountType == 'persen' ? AppColors.primary.withOpacity(0.12) : const Color(0xFFF1F5F9),
                                borderRadius: BorderRadius.circular(8),
                                border: Border.all(
                                  color: discountType == 'persen' ? AppColors.primary : const Color(0xFFCBD5E1),
                                ),
                              ),
                              alignment: Alignment.center,
                              child: Text('Persentase (%)',
                                  style: GoogleFonts.inter(
                                      fontWeight: FontWeight.w600,
                                      color: discountType == 'persen' ? AppColors.primary : AppColors.textSecondary)),
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 14),

                    // Nilai Diskon
                    TextField(
                      controller: valueCtrl,
                      keyboardType: TextInputType.number,
                      decoration: InputDecoration(
                        labelText: discountType == 'persen' ? 'Besar Diskon (%) *' : 'Nominal Potongan (Rp) *',
                        prefixText: discountType == 'nominal' ? 'Rp ' : null,
                        suffixText: discountType == 'persen' ? '%' : null,
                        border: const OutlineInputBorder(borderRadius: BorderRadius.all(Radius.circular(10))),
                        contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                      ),
                    ),
                    const SizedBox(height: 14),

                    // Periode Mulai & Selesai
                    Row(
                      children: [
                        Expanded(
                          child: InkWell(
                            onTap: () async {
                              final picked = await showDatePicker(
                                context: context,
                                initialDate: validFrom,
                                firstDate: DateTime.now().subtract(const Duration(days: 1)),
                                lastDate: DateTime.now().add(const Duration(days: 365)),
                              );
                              if (picked != null) {
                                setModalState(() => validFrom = picked);
                              }
                            },
                            child: Container(
                              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
                              decoration: BoxDecoration(
                                border: Border.all(color: const Color(0xFFCBD5E1)),
                                borderRadius: BorderRadius.circular(10),
                              ),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text('Berlaku Mulai', style: GoogleFonts.inter(fontSize: 11, color: AppColors.textMuted)),
                                  const SizedBox(height: 2),
                                  Text(validFromStr, style: GoogleFonts.inter(fontWeight: FontWeight.w600, fontSize: 13)),
                                ],
                              ),
                            ),
                          ),
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                          child: InkWell(
                            onTap: () async {
                              final picked = await showDatePicker(
                                context: context,
                                initialDate: validUntil,
                                firstDate: validFrom,
                                lastDate: DateTime.now().add(const Duration(days: 365)),
                              );
                              if (picked != null) {
                                setModalState(() => validUntil = picked);
                              }
                            },
                            child: Container(
                              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
                              decoration: BoxDecoration(
                                border: Border.all(color: const Color(0xFFCBD5E1)),
                                borderRadius: BorderRadius.circular(10),
                              ),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text('Berlaku Sampai', style: GoogleFonts.inter(fontSize: 11, color: AppColors.textMuted)),
                                  const SizedBox(height: 2),
                                  Text(validUntilStr, style: GoogleFonts.inter(fontWeight: FontWeight.w600, fontSize: 13)),
                                ],
                              ),
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 14),

                    // Keterangan
                    TextField(
                      controller: descCtrl,
                      maxLines: 2,
                      decoration: const InputDecoration(
                        labelText: 'Keterangan (Opsional)',
                        hintText: 'Misal: Diskon loyalitas pelanggan setia',
                        border: OutlineInputBorder(borderRadius: BorderRadius.all(Radius.circular(10))),
                        contentPadding: EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                      ),
                    ),
                    const SizedBox(height: 20),

                    // Submit Button
                    ElevatedButton(
                      onPressed: isSubmitting
                          ? null
                          : () async {
                              if (selectedUserId == null) {
                                ScaffoldMessenger.of(context).showSnackBar(
                                  const SnackBar(content: Text('Pilih pelanggan target terlebih dahulu')),
                                );
                                return;
                              }
                              final name = nameCtrl.text.trim();
                              if (name.isEmpty) {
                                ScaffoldMessenger.of(context).showSnackBar(
                                  const SnackBar(content: Text('Nama promo tidak boleh kosong')),
                                );
                                return;
                              }
                              final val = double.tryParse(valueCtrl.text.trim());
                              if (val == null || val <= 0) {
                                ScaffoldMessenger.of(context).showSnackBar(
                                  const SnackBar(content: Text('Masukkan nilai diskon yang valid')),
                                );
                                return;
                              }

                              setModalState(() => isSubmitting = true);
                              final res = await ApiService.adminCreatePromo(
                                userId: selectedUserId!,
                                name: name,
                                description: descCtrl.text.trim().isNotEmpty ? descCtrl.text.trim() : null,
                                discountType: discountType,
                                discountValue: val,
                                validFrom: validFromStr,
                                validUntil: validUntilStr,
                              );
                              setModalState(() => isSubmitting = false);

                              if (!mounted) return;
                              if (res['success'] == true) {
                                Navigator.pop(ctx);
                                ScaffoldMessenger.of(context).showSnackBar(
                                  SnackBar(
                                    content: Text('Promo untuk $selectedCustomerName berhasil dibuat!'),
                                    backgroundColor: AppColors.success,
                                    behavior: SnackBarBehavior.floating,
                                  ),
                                );
                                _loadAll();
                              } else {
                                ScaffoldMessenger.of(context).showSnackBar(
                                  SnackBar(
                                    content: Text(res['message'] ?? 'Gagal membuat promo'),
                                    backgroundColor: AppColors.error,
                                    behavior: SnackBarBehavior.floating,
                                  ),
                                );
                              }
                            },
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppColors.primary,
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        elevation: 0,
                      ),
                      child: Text('Simpan & Berikan Promo',
                          style: GoogleFonts.inter(fontWeight: FontWeight.bold, fontSize: 14)),
                    ),
                  ],
                ),
              ),
            );
          },
        );
      },
    );
  }
}
