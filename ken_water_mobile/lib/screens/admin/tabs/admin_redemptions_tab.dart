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
    ]);
    if (mounted) {
      setState(() {
        _redemptions = results[0] as List<dynamic>;
        _rewards = results[1] as List<dynamic>;
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
      length: 2,
      child: Scaffold(
        backgroundColor: AppColors.background,
        appBar: AppBar(
          backgroundColor: Colors.white,
          surfaceTintColor: Colors.white,
          elevation: 0.5,
          shadowColor: const Color(0x0A000000),
          title: Text('Reward Management',
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
            labelColor: AppColors.primary,
            unselectedLabelColor: AppColors.textSecondary,
            indicatorColor: AppColors.primary,
            indicatorWeight: 3,
            tabs: [
              Tab(
                  child: Text('Penukaran',
                      style: GoogleFonts.inter(fontWeight: FontWeight.w600))),
              Tab(
                  child: Text('Katalog Reward',
                      style: GoogleFonts.inter(fontWeight: FontWeight.w600))),
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
}
