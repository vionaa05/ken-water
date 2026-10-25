import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../../../constants/app_colors.dart';
import '../../../services/api_service.dart';

class ComplaintsTab extends StatefulWidget {
  const ComplaintsTab({super.key});

  @override
  State<ComplaintsTab> createState() => _ComplaintsTabState();
}

class _ComplaintsTabState extends State<ComplaintsTab> {
  List<dynamic> _complaints = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadComplaints();
  }

  Future<void> _loadComplaints() async {
    setState(() => _isLoading = true);
    final data = await ApiService.getComplaints();
    if (mounted) {
      setState(() {
        _complaints = data;
        _isLoading = false;
      });
    }
  }

  void _showNewComplaintSheet() {
    final descController = TextEditingController();
    final formKey = GlobalKey<FormState>();
    bool isSubmitting = false;
    String? selectedCategory;

    final categories = [
      'Kualitas Air',
      'Kondisi Galon',
      'Pengiriman',
      'Layanan Kasir / Kurir',
      'Lainnya',
    ];

    final categoryDescriptions = {
      'Kualitas Air': 'Rasa, Bau, Kejernihan',
      'Kondisi Galon': 'Bocor, Kotor, Tutup Rusak',
      'Pengiriman': 'Terlambat, Salah Alamat/Jadwal',
      'Layanan Kasir / Kurir': 'Pelayanan Staff / Kurir',
      'Lainnya': 'Kendala lainnya',
    };

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => StatefulBuilder(
        builder: (context, setSheet) => Container(
          padding: EdgeInsets.only(
            left: 20,
            right: 20,
            top: 20,
            bottom: MediaQuery.of(context).viewInsets.bottom + 24,
          ),
          decoration: const BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
          ),
          child: Form(
            key: formKey,
            child: SingleChildScrollView(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Center(
                    child: Container(
                      width: 40,
                      height: 4,
                      decoration: BoxDecoration(
                        color: const Color(0xFFE2E8F0),
                        borderRadius: BorderRadius.circular(2),
                      ),
                    ),
                  ),
                  const SizedBox(height: 20),
                  Text('Ajukan Keluhan',
                      style: GoogleFonts.inter(
                          fontSize: 18, fontWeight: FontWeight.bold)),
                  const SizedBox(height: 4),
                  Text('Kami akan segera menindaklanjuti keluhan Anda',
                      style: GoogleFonts.inter(
                          fontSize: 13, color: AppColors.textSecondary)),
                  const SizedBox(height: 20),
                  // Dropdown Kategori Keluhan (sama seperti web)
                  DropdownButtonFormField<String>(
                    value: selectedCategory,
                    decoration: InputDecoration(
                      labelText: 'Kategori Kendala *',
                      border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12)),
                      prefixIcon:
                          const Icon(Icons.category_rounded, color: AppColors.primary),
                      filled: true,
                      fillColor: AppColors.surfaceMuted,
                    ),
                    items: categories.map((cat) {
                      return DropdownMenuItem<String>(
                        value: cat,
                        child: Text(
                          '$cat (${categoryDescriptions[cat]})',
                          style: GoogleFonts.inter(fontSize: 13),
                          overflow: TextOverflow.ellipsis,
                        ),
                      );
                    }).toList(),
                    onChanged: (val) {
                      setSheet(() => selectedCategory = val);
                    },
                    validator: (v) =>
                        v == null || v.isEmpty ? 'Pilih kategori kendala' : null,
                    hint: Text('-- Pilih Kategori Kendala --',
                        style: GoogleFonts.inter(
                            fontSize: 13, color: AppColors.textMuted)),
                    isExpanded: true,
                  ),
                  const SizedBox(height: 14),
                  TextFormField(
                    controller: descController,
                    maxLines: 4,
                    decoration: InputDecoration(
                      labelText: 'Detail Keluhan *',
                      hintText: 'Jelaskan secara singkat kendala yang Anda alami\n(misal: nomor pesanan, waktu kejadian, dll)',
                      border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12)),
                      prefixIcon: const Padding(
                        padding: EdgeInsets.only(bottom: 64),
                        child: Icon(Icons.description_outlined,
                            color: AppColors.primary),
                      ),
                      alignLabelWithHint: true,
                      filled: true,
                      fillColor: AppColors.surfaceMuted,
                    ),
                    validator: (v) =>
                        v!.length < 10 ? 'Deskripsi minimal 10 karakter' : null,
                  ),
                  const SizedBox(height: 20),
                  SizedBox(
                    width: double.infinity,
                    height: 50,
                    child: ElevatedButton(
                      onPressed: isSubmitting
                          ? null
                          : () async {
                              if (!formKey.currentState!.validate()) return;
                              setSheet(() => isSubmitting = true);
                              final result = await ApiService.createComplaint(
                                subject: selectedCategory!,
                                description: descController.text,
                                category: selectedCategory,
                              );
                              setSheet(() => isSubmitting = false);
                              if (!mounted) return;
                              Navigator.pop(ctx);
                              ScaffoldMessenger.of(context).showSnackBar(SnackBar(
                                content: Text(result['message'] ?? ''),
                                backgroundColor: result['success'] == true
                                    ? AppColors.success
                                    : AppColors.error,
                                behavior: SnackBarBehavior.floating,
                              ));
                              if (result['success'] == true) _loadComplaints();
                            },
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppColors.primary,
                      shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(12)),
                    ),
                    child: isSubmitting
                        ? const CircularProgressIndicator(
                            color: Colors.white, strokeWidth: 2)
                        : Text('Kirim Keluhan',
                            style: GoogleFonts.inter(
                                color: Colors.white,
                                fontWeight: FontWeight.bold,
                                fontSize: 15)),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    ),
  );
}

  Color _getStatusColor(String status) {
    switch (status) {
      case 'selesai': return AppColors.success;
      case 'diproses': return AppColors.info;
      case 'ditolak': return AppColors.error;
      default: return AppColors.warning;
    }
  }

  IconData _getStatusIcon(String status) {
    switch (status) {
      case 'selesai': return Icons.check_circle_rounded;
      case 'diproses': return Icons.pending_rounded;
      case 'ditolak': return Icons.cancel_rounded;
      default: return Icons.hourglass_empty_rounded;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0.5,
        automaticallyImplyLeading: false,
        title: Text('Keluhan Saya',
            style: GoogleFonts.inter(
                fontWeight: FontWeight.bold,
                fontSize: 18,
                color: AppColors.textPrimary)),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded, color: AppColors.textSecondary),
            onPressed: _loadComplaints,
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: _showNewComplaintSheet,
        backgroundColor: AppColors.primary,
        icon: const Icon(Icons.add_rounded, color: Colors.white),
        label: Text('Ajukan Keluhan',
            style: GoogleFonts.inter(
                color: Colors.white, fontWeight: FontWeight.bold)),
      ),
      body: _isLoading
          ? const Center(
              child: CircularProgressIndicator(color: AppColors.primary))
          : RefreshIndicator(
              onRefresh: _loadComplaints,
              child: _complaints.isEmpty
                  ? ListView(
                      children: [
                        SizedBox(
                          height: MediaQuery.of(context).size.height * 0.6,
                          child: Center(
                            child: Column(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Icon(Icons.headset_mic_outlined,
                                    size: 60, color: AppColors.textMuted),
                                const SizedBox(height: 16),
                                Text('Tidak ada keluhan',
                                    style: GoogleFonts.inter(
                                      fontSize: 16,
                                      fontWeight: FontWeight.w600,
                                      color: AppColors.textSecondary,
                                    )),
                                const SizedBox(height: 8),
                                Text('Tekan tombol di bawah untuk mengajukan keluhan',
                                    style: GoogleFonts.inter(
                                        color: AppColors.textMuted,
                                        fontSize: 13),
                                    textAlign: TextAlign.center),
                              ],
                            ),
                          ),
                        ),
                      ],
                    )
                  : ListView.separated(
                      padding: const EdgeInsets.fromLTRB(16, 16, 16, 100),
                      itemCount: _complaints.length,
                      separatorBuilder: (_, __) =>
                          const SizedBox(height: 12),
                      itemBuilder: (ctx, i) {
                        final c = _complaints[i];
                        final status = c['status'] ?? 'menunggu';
                        final statusColor = _getStatusColor(status);
                        return GestureDetector(
                          onTap: () => _showComplaintDetail(c),
                          child: Container(
                            padding: const EdgeInsets.all(16),
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
                                Container(
                                  padding: const EdgeInsets.all(10),
                                  decoration: BoxDecoration(
                                    color: statusColor.withOpacity(0.1),
                                    borderRadius: BorderRadius.circular(12),
                                  ),
                                  child: Icon(_getStatusIcon(status),
                                      color: statusColor, size: 24),
                                ),
                                const SizedBox(width: 12),
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment:
                                        CrossAxisAlignment.start,
                                    children: [
                                      Text(c['subject'] ?? '',
                                          style: GoogleFonts.inter(
                                              fontWeight: FontWeight.bold,
                                              fontSize: 14),
                                          maxLines: 1,
                                          overflow: TextOverflow.ellipsis),
                                      const SizedBox(height: 4),
                                      Text(c['description'] ?? '',
                                          style: GoogleFonts.inter(
                                              fontSize: 12,
                                              color:
                                                  AppColors.textSecondary),
                                          maxLines: 2,
                                          overflow: TextOverflow.ellipsis),
                                      const SizedBox(height: 6),
                                      Text(c['created_at'] ?? '',
                                          style: GoogleFonts.inter(
                                              fontSize: 11,
                                              color: AppColors.textMuted)),
                                    ],
                                  ),
                                ),
                                const SizedBox(width: 8),
                                Container(
                                  padding: const EdgeInsets.symmetric(
                                      horizontal: 8, vertical: 4),
                                  decoration: BoxDecoration(
                                    color: statusColor.withOpacity(0.1),
                                    borderRadius: BorderRadius.circular(8),
                                  ),
                                  child: Text(
                                    c['status_label'] ?? status,
                                    style: GoogleFonts.inter(
                                      color: statusColor,
                                      fontSize: 11,
                                      fontWeight: FontWeight.bold,
                                    ),
                                  ),
                                ),
                              ],
                            ),
                          ),
                        );
                      },
                    ),
            ),
    );
  }

  void _showComplaintDetail(Map c) {
    final status = c['status'] ?? 'menunggu';
    final statusColor = _getStatusColor(status);
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => Container(
        padding: const EdgeInsets.all(24),
        decoration: const BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Center(
              child: Container(
                width: 40,
                height: 4,
                decoration: BoxDecoration(
                  color: const Color(0xFFE2E8F0),
                  borderRadius: BorderRadius.circular(2),
                ),
              ),
            ),
            const SizedBox(height: 20),
            Row(
              children: [
                Expanded(
                  child: Text(c['subject'] ?? '',
                      style: GoogleFonts.inter(
                          fontSize: 17, fontWeight: FontWeight.bold)),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(
                      horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    color: statusColor.withOpacity(0.1),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text(c['status_label'] ?? status,
                      style: GoogleFonts.inter(
                        color: statusColor,
                        fontSize: 12,
                        fontWeight: FontWeight.bold,
                      )),
                ),
              ],
            ),
            const SizedBox(height: 4),
            Text(c['created_at'] ?? '',
                style:
                    GoogleFonts.inter(fontSize: 12, color: AppColors.textMuted)),
            const Divider(height: 24),
            Text('Deskripsi',
                style: GoogleFonts.inter(
                    fontWeight: FontWeight.bold, fontSize: 13,
                    color: AppColors.textSecondary)),
            const SizedBox(height: 6),
            Text(c['description'] ?? '',
                style: GoogleFonts.inter(
                    fontSize: 14, color: AppColors.textPrimary,
                    height: 1.5)),
            if (c['admin_notes'] != null && c['admin_notes'].toString().isNotEmpty) ...[
              const Divider(height: 24),
              Text('Respons Admin',
                  style: GoogleFonts.inter(
                      fontWeight: FontWeight.bold, fontSize: 13,
                      color: AppColors.textSecondary)),
              const SizedBox(height: 6),
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: AppColors.primaryLight,
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Text(c['admin_notes'],
                    style: GoogleFonts.inter(
                        fontSize: 14, color: AppColors.primaryDark,
                        height: 1.5)),
              ),
            ],
            const SizedBox(height: 24),
          ],
        ),
      ),
    );
  }
}
