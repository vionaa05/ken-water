import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../../constants/app_colors.dart';
import '../../services/api_service.dart';

class AdminCampaignsScreen extends StatefulWidget {
  const AdminCampaignsScreen({super.key});

  @override
  State<AdminCampaignsScreen> createState() => _AdminCampaignsScreenState();
}

class _AdminCampaignsScreenState extends State<AdminCampaignsScreen> {
  List<dynamic> _campaigns = [];
  List<dynamic> _vouchers = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    setState(() => _isLoading = true);
    final results = await Future.wait([
      ApiService.getAdminCampaigns(),
      ApiService.getAdminVouchers(),
    ]);

    if (mounted) {
      setState(() {
        final cRes = results[0];
        _campaigns = cRes['campaigns'] ?? [];
        final vRes = results[1];
        _vouchers = vRes['vouchers'] ?? [];
        _isLoading = false;
      });
    }
  }

  Color _getTypeColor(String type) {
    switch (type.toLowerCase()) {
      case 'promo_baru':
        return AppColors.info;
      case 'promo_loyal':
        return const Color(0xFFD97706);
      case 'ulang_tahun':
        return const Color(0xFF8B5CF6);
      case 'ajakan_kembali':
        return const Color(0xFFEA580C);
      default:
        return AppColors.primary;
    }
  }

  IconData _getTypeIcon(String type) {
    switch (type.toLowerCase()) {
      case 'promo_baru':
        return Icons.campaign_rounded;
      case 'promo_loyal':
        return Icons.star_rounded;
      case 'ulang_tahun':
        return Icons.cake_rounded;
      case 'ajakan_kembali':
        return Icons.replay_rounded;
      default:
        return Icons.send_rounded;
    }
  }

  Future<void> _deleteCampaign(int id, String name) async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: Row(
          children: [
            const Icon(Icons.delete_outline, color: AppColors.error),
            const SizedBox(width: 8),
            Text('Hapus Kampanye', style: GoogleFonts.inter(fontWeight: FontWeight.bold, fontSize: 16)),
          ],
        ),
        content: Text('Yakin ingin menghapus kampanye "$name"? Tindakan ini tidak dapat dibatalkan.',
            style: GoogleFonts.inter(fontSize: 13, color: AppColors.textSecondary)),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Batal')),
          ElevatedButton(
            onPressed: () => Navigator.pop(ctx, true),
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.error,
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
            ),
            child: const Text('Hapus'),
          ),
        ],
      ),
    );

    if (confirm == true) {
      final res = await ApiService.adminDeleteCampaign(id);
      if (!mounted) return;
      if (res['success'] == true) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(res['message'] ?? 'Kampanye berhasil dihapus'),
            backgroundColor: AppColors.success,
            behavior: SnackBarBehavior.floating,
          ),
        );
        _loadData();
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(res['message'] ?? 'Gagal menghapus kampanye'),
            backgroundColor: AppColors.error,
            behavior: SnackBarBehavior.floating,
          ),
        );
      }
    }
  }

  void _showCreateCampaignModal() {
    final nameCtrl = TextEditingController();
    final messageCtrl = TextEditingController(
      text: 'Halo Kak {nama}, Ken Water punya penawaran spesial isi ulang galon untuk Kakak! Pesan sekarang yuk!',
    );
    String selectedType = 'promo_baru';
    int? selectedVoucherId;
    List<String> selectedSegments = ['semua'];

    DateTime periodStart = DateTime.now();
    DateTime periodEnd = DateTime.now().add(const Duration(days: 7));
    bool isSubmitting = false;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (ctx) {
        return StatefulBuilder(
          builder: (context, setModalState) {
            final startStr =
                '${periodStart.year}-${periodStart.month.toString().padLeft(2, '0')}-${periodStart.day.toString().padLeft(2, '0')}';
            final endStr =
                '${periodEnd.year}-${periodEnd.month.toString().padLeft(2, '0')}-${periodEnd.day.toString().padLeft(2, '0')}';

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
                        Row(
                          children: [
                            Container(
                              padding: const EdgeInsets.all(8),
                              decoration: BoxDecoration(
                                color: const Color(0xFFDCFCE7),
                                borderRadius: BorderRadius.circular(10),
                              ),
                              child: const Icon(Icons.campaign, color: Color(0xFF16A34A), size: 22),
                            ),
                            const SizedBox(width: 10),
                            Text(
                              'Buat Kampanye WhatsApp',
                              style: GoogleFonts.inter(
                                fontSize: 16,
                                fontWeight: FontWeight.bold,
                                color: AppColors.textPrimary,
                              ),
                            ),
                          ],
                        ),
                        IconButton(
                          icon: const Icon(Icons.close, color: AppColors.textSecondary),
                          onPressed: () => Navigator.pop(ctx),
                        ),
                      ],
                    ),
                    const Divider(height: 20),

                    // Nama Kampanye
                    TextField(
                      controller: nameCtrl,
                      decoration: const InputDecoration(
                        labelText: 'Nama Kampanye *',
                        hintText: 'Misal: Promo Weekend Hemat Galon',
                        border: OutlineInputBorder(borderRadius: BorderRadius.all(Radius.circular(10))),
                        contentPadding: EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                      ),
                    ),
                    const SizedBox(height: 14),

                    // Tipe Kampanye
                    Text('Tipe Kampanye CRM *',
                        style: GoogleFonts.inter(fontWeight: FontWeight.w600, fontSize: 13, color: AppColors.textPrimary)),
                    const SizedBox(height: 6),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 12),
                      decoration: BoxDecoration(
                        border: Border.all(color: const Color(0xFFCBD5E1)),
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: DropdownButtonHideUnderline(
                        child: DropdownButton<String>(
                          isExpanded: true,
                          value: selectedType,
                          items: const [
                            DropdownMenuItem(value: 'promo_baru', child: Text('Promo Pelanggan Baru')),
                            DropdownMenuItem(value: 'promo_loyal', child: Text('Promo Member Loyal (Silver/Gold)')),
                            DropdownMenuItem(value: 'ulang_tahun', child: Text('Ucapan & Promo Ulang Tahun')),
                            DropdownMenuItem(value: 'ajakan_kembali', child: Text('Reaktivasi / Churn (Pelanggan Tidak Aktif)')),
                          ],
                          onChanged: (val) {
                            if (val != null) {
                              setModalState(() {
                                selectedType = val;
                                if (val == 'ajakan_kembali') {
                                  selectedSegments = ['tidak_aktif'];
                                  messageCtrl.text =
                                      'Halo Kak {nama}, sudah lama tidak pesan air di Ken Water nih. Kami ada diskon khusus untuk Kakak!';
                                } else if (val == 'ulang_tahun') {
                                  selectedSegments = ['semua'];
                                  messageCtrl.text =
                                      'Selamat Ulang Tahun Kak {nama}! 🎉 Nikmati promo spesial dari Ken Water hari ini!';
                                } else if (val == 'promo_loyal') {
                                  selectedSegments = ['loyal'];
                                  messageCtrl.text =
                                      'Terima kasih telah setia bersama Ken Water Kak {nama}! Dapatkan bonus khusus member loyal hari ini.';
                                }
                              });
                            }
                          },
                        ),
                      ),
                    ),
                    const SizedBox(height: 14),

                    // Target Segmen
                    Text('Target Segmen Pelanggan *',
                        style: GoogleFonts.inter(fontWeight: FontWeight.w600, fontSize: 13, color: AppColors.textPrimary)),
                    const SizedBox(height: 6),
                    Wrap(
                      spacing: 8,
                      runSpacing: 8,
                      children: [
                        _buildSegmentChip('Semua Pelanggan', 'semua', selectedSegments, (v) {
                          setModalState(() {
                            selectedSegments = ['semua'];
                          });
                        }),
                        _buildSegmentChip('Pelanggan Loyal', 'loyal', selectedSegments, (v) {
                          setModalState(() {
                            selectedSegments.remove('semua');
                            if (selectedSegments.contains('loyal')) {
                              selectedSegments.remove('loyal');
                            } else {
                              selectedSegments.add('loyal');
                            }
                            if (selectedSegments.isEmpty) selectedSegments = ['semua'];
                          });
                        }),
                        _buildSegmentChip('Tidak Aktif (Churn)', 'tidak_aktif', selectedSegments, (v) {
                          setModalState(() {
                            selectedSegments.remove('semua');
                            if (selectedSegments.contains('tidak_aktif')) {
                              selectedSegments.remove('tidak_aktif');
                            } else {
                              selectedSegments.add('tidak_aktif');
                            }
                            if (selectedSegments.isEmpty) selectedSegments = ['semua'];
                          });
                        }),
                      ],
                    ),
                    const SizedBox(height: 14),

                    // Periode Kampanye
                    Row(
                      children: [
                        Expanded(
                          child: InkWell(
                            onTap: () async {
                              final picked = await showDatePicker(
                                context: context,
                                initialDate: periodStart,
                                firstDate: DateTime.now().subtract(const Duration(days: 1)),
                                lastDate: DateTime.now().add(const Duration(days: 365)),
                              );
                              if (picked != null) {
                                setModalState(() => periodStart = picked);
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
                                  Text('Mulai', style: GoogleFonts.inter(fontSize: 11, color: AppColors.textMuted)),
                                  const SizedBox(height: 2),
                                  Text(startStr, style: GoogleFonts.inter(fontWeight: FontWeight.w600, fontSize: 13)),
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
                                initialDate: periodEnd,
                                firstDate: periodStart,
                                lastDate: DateTime.now().add(const Duration(days: 365)),
                              );
                              if (picked != null) {
                                setModalState(() => periodEnd = picked);
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
                                  Text('Berakhir', style: GoogleFonts.inter(fontSize: 11, color: AppColors.textMuted)),
                                  const SizedBox(height: 2),
                                  Text(endStr, style: GoogleFonts.inter(fontWeight: FontWeight.w600, fontSize: 13)),
                                ],
                              ),
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 14),

                    // Lampirkan Voucher (Opsional)
                    Text('Lampirkan Voucher (Opsional)',
                        style: GoogleFonts.inter(fontWeight: FontWeight.w600, fontSize: 13, color: AppColors.textPrimary)),
                    const SizedBox(height: 6),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 12),
                      decoration: BoxDecoration(
                        border: Border.all(color: const Color(0xFFCBD5E1)),
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: DropdownButtonHideUnderline(
                        child: DropdownButton<int?>(
                          isExpanded: true,
                          hint: Text('Tanpa voucher', style: GoogleFonts.inter(fontSize: 13, color: AppColors.textMuted)),
                          value: selectedVoucherId,
                          items: [
                            const DropdownMenuItem<int?>(
                              value: null,
                              child: Text('Tanpa voucher'),
                            ),
                            ..._vouchers.map<DropdownMenuItem<int?>>((v) {
                              final int id = v['id'] is int ? v['id'] : int.tryParse(v['id'].toString()) ?? 0;
                              return DropdownMenuItem<int?>(
                                value: id,
                                child: Text('${v['name']} (${v['code'] ?? '-'})'),
                              );
                            }),
                          ],
                          onChanged: (val) => setModalState(() => selectedVoucherId = val),
                        ),
                      ),
                    ),
                    const SizedBox(height: 14),

                    // Template Pesan WhatsApp
                    Text('Template Pesan WhatsApp *',
                        style: GoogleFonts.inter(fontWeight: FontWeight.w600, fontSize: 13, color: AppColors.textPrimary)),
                    const SizedBox(height: 4),
                    Text('Gunakan tag {nama} untuk memanggil nama pelanggan otomatis.',
                        style: GoogleFonts.inter(fontSize: 11, color: AppColors.textMuted)),
                    const SizedBox(height: 6),
                    TextField(
                      controller: messageCtrl,
                      maxLines: 4,
                      decoration: const InputDecoration(
                        hintText: 'Tulis pesan broadcast WhatsApp...',
                        border: OutlineInputBorder(borderRadius: BorderRadius.all(Radius.circular(10))),
                        contentPadding: EdgeInsets.all(12),
                      ),
                    ),
                    const SizedBox(height: 20),

                    // Tombol Submit
                    ElevatedButton.icon(
                      onPressed: isSubmitting
                          ? null
                          : () async {
                              final name = nameCtrl.text.trim();
                              if (name.isEmpty) {
                                ScaffoldMessenger.of(context).showSnackBar(
                                  const SnackBar(content: Text('Nama kampanye wajib diisi')),
                                );
                                return;
                              }
                              final msg = messageCtrl.text.trim();
                              if (msg.isEmpty) {
                                ScaffoldMessenger.of(context).showSnackBar(
                                  const SnackBar(content: Text('Template pesan wajib diisi')),
                                );
                                return;
                              }

                              setModalState(() => isSubmitting = true);
                              final res = await ApiService.adminCreateCampaign(
                                name: name,
                                type: selectedType,
                                targetSegment: selectedSegments,
                                periodStart: startStr,
                                periodEnd: endStr,
                                messageTemplate: msg,
                                voucherId: selectedVoucherId,
                              );
                              setModalState(() => isSubmitting = false);

                              if (!mounted) return;
                              if (res['success'] == true) {
                                Navigator.pop(ctx);
                                ScaffoldMessenger.of(context).showSnackBar(
                                  SnackBar(
                                    content: Text(res['message'] ?? 'Kampanye WhatsApp berhasil dibuat!'),
                                    backgroundColor: AppColors.success,
                                    behavior: SnackBarBehavior.floating,
                                  ),
                                );
                                _loadData();
                              } else {
                                ScaffoldMessenger.of(context).showSnackBar(
                                  SnackBar(
                                    content: Text(res['message'] ?? 'Gagal membuat kampanye'),
                                    backgroundColor: AppColors.error,
                                    behavior: SnackBarBehavior.floating,
                                  ),
                                );
                              }
                            },
                      icon: isSubmitting
                          ? const SizedBox(
                              width: 18,
                              height: 18,
                              child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                            )
                          : const Icon(Icons.send_rounded, size: 18),
                      label: Text(
                        isSubmitting ? 'Memproses...' : 'Simpan & Jadwalkan Kampanye',
                        style: GoogleFonts.inter(fontWeight: FontWeight.bold, fontSize: 14),
                      ),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF16A34A),
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        elevation: 0,
                      ),
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

  Widget _buildSegmentChip(String label, String value, List<String> selected, ValueChanged<bool> onSelected) {
    final isSelected = selected.contains(value);
    return FilterChip(
      label: Text(label, style: GoogleFonts.inter(fontSize: 12, fontWeight: isSelected ? FontWeight.bold : FontWeight.normal)),
      selected: isSelected,
      onSelected: onSelected,
      selectedColor: AppColors.primary.withOpacity(0.18),
      checkmarkColor: AppColors.primary,
      backgroundColor: const Color(0xFFF1F5F9),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
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
          'Kampanye WhatsApp CRM',
          style: GoogleFonts.inter(fontWeight: FontWeight.bold, fontSize: 17, color: AppColors.textPrimary),
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh, color: AppColors.textSecondary),
            tooltip: 'Segarkan',
            onPressed: _loadData,
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: _showCreateCampaignModal,
        backgroundColor: const Color(0xFF16A34A),
        icon: const Icon(Icons.add, color: Colors.white),
        label: Text('Kampanye Baru', style: GoogleFonts.inter(fontWeight: FontWeight.bold, color: Colors.white)),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator(color: AppColors.primary))
          : RefreshIndicator(
              onRefresh: _loadData,
              color: AppColors.primary,
              child: ListView(
                padding: const EdgeInsets.all(16),
                children: [
                  // Banner Header
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
                          child: const Icon(Icons.campaign, color: Colors.white, size: 28),
                        ),
                        const SizedBox(width: 14),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                'Broadcast & CRM WhatsApp',
                                style: GoogleFonts.inter(
                                  fontWeight: FontWeight.bold,
                                  fontSize: 16,
                                  color: Colors.white,
                                ),
                              ),
                              const SizedBox(height: 3),
                              Text(
                                'Kirim promosi, reminder, & reaktivasi ke target segmen pelanggan.',
                                style: GoogleFonts.inter(
                                  fontSize: 12,
                                  color: Colors.white.withOpacity(0.85),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),

                  const SizedBox(height: 16),

                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(
                        'Daftar Kampanye (${_campaigns.length})',
                        style: GoogleFonts.inter(
                          fontWeight: FontWeight.bold,
                          fontSize: 15,
                          color: AppColors.textPrimary,
                        ),
                      ),
                      Text(
                        'Total: ${_campaigns.length} kampanye',
                        style: GoogleFonts.inter(fontSize: 12, color: AppColors.textSecondary),
                      ),
                    ],
                  ),

                  const SizedBox(height: 12),

                  if (_campaigns.isEmpty)
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
                          const Icon(Icons.mark_chat_unread_outlined, color: AppColors.textMuted, size: 48),
                          const SizedBox(height: 12),
                          Text(
                            'Belum Ada Kampanye WhatsApp',
                            style: GoogleFonts.inter(fontWeight: FontWeight.bold, fontSize: 15, color: AppColors.textPrimary),
                          ),
                          const SizedBox(height: 6),
                          Text(
                            'Buat kampanye broadcast untuk menjangkau pelanggan aktif maupun yang hampir churn.',
                            textAlign: TextAlign.center,
                            style: GoogleFonts.inter(fontSize: 13, color: AppColors.textSecondary),
                          ),
                          const SizedBox(height: 16),
                          ElevatedButton.icon(
                            onPressed: _showCreateCampaignModal,
                            icon: const Icon(Icons.add, size: 16),
                            label: const Text('Buat Kampanye Pertama'),
                            style: ElevatedButton.styleFrom(
                              backgroundColor: const Color(0xFF16A34A),
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
                      itemCount: _campaigns.length,
                      separatorBuilder: (_, __) => const SizedBox(height: 12),
                      itemBuilder: (ctx, i) {
                        final c = _campaigns[i];
                        final id = c['id'] is int ? c['id'] : int.tryParse(c['id'].toString()) ?? 0;
                        final name = c['name'] ?? 'Kampanye';
                        final type = c['type'] ?? 'promo_baru';
                        final typeLabel = c['type_label'] ?? type;
                        final targetCount = c['target_count'] ?? 0;
                        final sentCount = c['sent_count'] ?? 0;
                        final template = c['message_template'] ?? '';
                        final periodStart = c['period_start'] ?? '-';
                        final periodEnd = c['period_end'] ?? '-';
                        final voucherName = c['voucher_name'];
                        final typeColor = _getTypeColor(type);
                        final typeIcon = _getTypeIcon(type);

                        return Container(
                          padding: const EdgeInsets.all(16),
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
                                crossAxisAlignment: CrossAxisAlignment.start,
                                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                children: [
                                  Expanded(
                                    child: Row(
                                      children: [
                                        Container(
                                          padding: const EdgeInsets.all(8),
                                          decoration: BoxDecoration(
                                            color: typeColor.withOpacity(0.12),
                                            borderRadius: BorderRadius.circular(10),
                                          ),
                                          child: Icon(typeIcon, color: typeColor, size: 20),
                                        ),
                                        const SizedBox(width: 10),
                                        Expanded(
                                          child: Column(
                                            crossAxisAlignment: CrossAxisAlignment.start,
                                            children: [
                                              Text(
                                                name,
                                                style: GoogleFonts.inter(
                                                  fontWeight: FontWeight.bold,
                                                  fontSize: 15,
                                                  color: AppColors.textPrimary,
                                                ),
                                              ),
                                              const SizedBox(height: 2),
                                              Container(
                                                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                                                decoration: BoxDecoration(
                                                  color: typeColor.withOpacity(0.1),
                                                  borderRadius: BorderRadius.circular(6),
                                                ),
                                                child: Text(
                                                  typeLabel,
                                                  style: GoogleFonts.inter(
                                                    fontSize: 11,
                                                    fontWeight: FontWeight.w600,
                                                    color: typeColor,
                                                  ),
                                                ),
                                              ),
                                            ],
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                  IconButton(
                                    icon: const Icon(Icons.delete_outline, color: AppColors.error, size: 20),
                                    tooltip: 'Hapus Kampanye',
                                    onPressed: () => _deleteCampaign(id, name),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 12),

                              // Info Stats Targets
                              Container(
                                padding: const EdgeInsets.all(10),
                                decoration: BoxDecoration(
                                  color: const Color(0xFFF8FAFC),
                                  borderRadius: BorderRadius.circular(10),
                                  border: Border.all(color: const Color(0xFFE2E8F0)),
                                ),
                                child: Row(
                                  children: [
                                    Expanded(
                                      child: Row(
                                        children: [
                                          const Icon(Icons.people_outline, size: 16, color: AppColors.textSecondary),
                                          const SizedBox(width: 6),
                                          Text(
                                            'Target: $targetCount pelanggan',
                                            style: GoogleFonts.inter(fontSize: 12, fontWeight: FontWeight.w600),
                                          ),
                                        ],
                                      ),
                                    ),
                                    Row(
                                      children: [
                                        const Icon(Icons.send_outlined, size: 14, color: AppColors.textMuted),
                                        const SizedBox(width: 4),
                                        Text(
                                          'Terkirim: $sentCount',
                                          style: GoogleFonts.inter(fontSize: 11, color: AppColors.textMuted),
                                        ),
                                      ],
                                    ),
                                  ],
                                ),
                              ),

                              if (voucherName != null) ...[
                                const SizedBox(height: 8),
                                Row(
                                  children: [
                                    const Icon(Icons.confirmation_number_outlined, size: 14, color: AppColors.primary),
                                    const SizedBox(width: 6),
                                    Text('Voucher: $voucherName',
                                        style: GoogleFonts.inter(fontSize: 12, color: AppColors.primary, fontWeight: FontWeight.w500)),
                                  ],
                                ),
                              ],

                              const SizedBox(height: 10),

                              // Message Preview
                              Container(
                                padding: const EdgeInsets.all(10),
                                width: double.infinity,
                                decoration: BoxDecoration(
                                  color: const Color(0xFFF1F5F9),
                                  borderRadius: BorderRadius.circular(8),
                                ),
                                child: Text(
                                  '"$template"',
                                  style: GoogleFonts.inter(
                                    fontSize: 12,
                                    fontStyle: FontStyle.italic,
                                    color: AppColors.textSecondary,
                                  ),
                                ),
                              ),

                              const SizedBox(height: 8),
                              Row(
                                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                children: [
                                  Text(
                                    'Periode: $periodStart - $periodEnd',
                                    style: GoogleFonts.inter(fontSize: 11, color: AppColors.textMuted),
                                  ),
                                  Text(
                                    c['created_at'] ?? '',
                                    style: GoogleFonts.inter(fontSize: 11, color: AppColors.textMuted),
                                  ),
                                ],
                              ),
                            ],
                          ),
                        );
                      },
                    ),

                  const SizedBox(height: 80),
                ],
              ),
            ),
    );
  }
}
