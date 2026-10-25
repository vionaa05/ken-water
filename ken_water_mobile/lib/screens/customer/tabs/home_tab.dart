import 'dart:io';
import 'dart:ui' as ui;
import 'package:flutter/material.dart';
import 'package:flutter/rendering.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:open_file/open_file.dart';
import 'package:flutter/services.dart';
import 'package:path_provider/path_provider.dart';
import 'package:qr_flutter/qr_flutter.dart';
import 'package:intl/intl.dart';
import '../../../constants/app_colors.dart';
import '../../../models/user_model.dart';
import '../../../models/order_model.dart';
import '../../../services/api_service.dart';
import '../../../services/storage_service.dart';
import '../../login_screen.dart';
import '../customer_main_screen.dart';
import '../notifications_screen.dart';
import '../vouchers_screen.dart';
import 'order_tab.dart';

class HomeTab extends StatefulWidget {
  const HomeTab({super.key});

  @override
  State<HomeTab> createState() => _HomeTabState();
}

class _HomeTabState extends State<HomeTab> {
  UserModel? _user;
  Map<String, dynamic>? _cardData;
  List<OrderModel> _orders = [];
  List<dynamic> _activePromos = [];
  int _unreadNotifs = 0;
  bool _isLoading = true;
  bool _isDownloading = false;
  final GlobalKey _cardRepaintKey = GlobalKey();

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
    final notifResult = await ApiService.getNotifications();
    final promosList = await ApiService.getCustomerPromos();
    if (mounted) {
      setState(() {
        if (cardResult['success'] == true) _cardData = cardResult['card'];
        _orders = ordersList;
        _unreadNotifs = notifResult['unread_count'] ?? 0;
        _activePromos = promosList;
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

  Future<void> _downloadCard() async {
    if (_isDownloading) return;
    setState(() => _isDownloading = true);
    try {
      final boundary = _cardRepaintKey.currentContext
          ?.findRenderObject() as RenderRepaintBoundary?;
      if (boundary == null) throw Exception('Widget tidak ditemukan');

      final image = await boundary.toImage(pixelRatio: 3.0);
      final byteData =
          await image.toByteData(format: ui.ImageByteFormat.png);
      if (byteData == null) throw Exception('Gagal mengkonversi gambar');

      final bytes = byteData.buffer.asUint8List();
      final fileName =
          'kartu_member_ken_water_${DateTime.now().millisecondsSinceEpoch}.png';

      // Simpan langsung ke Galeri Foto HP
      bool savedToGallery = false;
      if (Platform.isAndroid) {
        try {
          const channel = MethodChannel('com.kenwater.ken_water_mobile/media');
          await channel.invokeMethod('saveImageToGallery', {
            'bytes': bytes,
            'filename': fileName,
          });
          savedToGallery = true;
        } catch (e) {
          debugPrint('MediaStore save error: $e');
        }
      }

      final dir = await getApplicationDocumentsDirectory();
      final file = File('${dir.path}/$fileName');
      await file.writeAsBytes(bytes);

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Row(
              children: [
                const Icon(Icons.photo_library_rounded, color: Colors.white, size: 22),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'Kartu Berhasil Disimpan!',
                        style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                      ),
                      Text(
                        savedToGallery
                            ? 'Tersimpan ke Galeri HP (Pictures/Ken Water)'
                            : 'Tersimpan ke Dokumen: $fileName',
                        style: const TextStyle(fontSize: 11),
                      ),
                    ],
                  ),
                ),
              ],
            ),
            backgroundColor: AppColors.success,
            behavior: SnackBarBehavior.floating,
            shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(12)),
            duration: const Duration(seconds: 4),
            action: SnackBarAction(
              label: 'Buka',
              textColor: Colors.white,
              onPressed: () => OpenFile.open(file.path),
            ),
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Row(
              children: [
                const Icon(Icons.error_outline, color: Colors.white, size: 20),
                const SizedBox(width: 10),
                Text('Gagal mengunduh: ${e.toString()}',
                    style: const TextStyle(fontSize: 12)),
              ],
            ),
            backgroundColor: AppColors.error,
            behavior: SnackBarBehavior.floating,
            shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(12)),
          ),
        );
      }
    } finally {
      if (mounted) setState(() => _isDownloading = false);
    }
  }

  Future<void> _showReactivationConfirmDialog() async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: Row(
          children: [
            const Icon(Icons.restart_alt_rounded, color: AppColors.primary),
            const SizedBox(width: 8),
            Text('Reaktivasi Akun', style: GoogleFonts.inter(fontWeight: FontWeight.bold, fontSize: 18)),
          ],
        ),
        content: const Text(
          'Dengan mengaktifkan kembali akun, Anda setuju untuk memulai kembali keanggotaan Anda di Ken Water.\n\nApakah Anda yakin ingin melanjutkan?',
          style: TextStyle(fontSize: 13, height: 1.4),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: const Text('Batal'),
          ),
          ElevatedButton(
            onPressed: () => Navigator.pop(ctx, true),
            style: ElevatedButton.styleFrom(backgroundColor: AppColors.primary, foregroundColor: Colors.white),
            child: const Text('Ya, Reaktivasi'),
          ),
        ],
      ),
    );

    if (confirm == true) {
      if (!mounted) return;
      showDialog(
        context: context,
        barrierDismissible: false,
        builder: (ctx) => const Center(child: CircularProgressIndicator(color: AppColors.primary)),
      );

      final res = await ApiService.reactivateAccount();
      if (!mounted) return;
      Navigator.pop(context); // close loader

      if (res['success'] == true) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(res['message'] ?? 'Akun berhasil diaktifkan kembali!'),
            backgroundColor: AppColors.success,
            behavior: SnackBarBehavior.floating,
          ),
        );
        _loadData();
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(res['message'] ?? 'Gagal mengaktifkan akun'),
            backgroundColor: AppColors.error,
            behavior: SnackBarBehavior.floating,
          ),
        );
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

  void _showOrderDetailModal(OrderModel order) {
    final statusSteps = ['dipesan', 'diproses', 'dikirim', 'selesai'];
    final statusLabels = ['Dipesan', 'Diproses', 'Diantar', 'Selesai'];
    final statusIcons = [
      Icons.receipt_long_rounded,
      Icons.hourglass_top_rounded,
      Icons.local_shipping_rounded,
      Icons.check_circle_rounded,
    ];
    final currentIdx = statusSteps.indexOf(order.status.toLowerCase());
    final isCancelled = order.status.toLowerCase() == 'dibatalkan';

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) {
        return SafeArea(
          child: Container(
            padding: const EdgeInsets.fromLTRB(20, 12, 20, 24),
            constraints: BoxConstraints(
              maxHeight: MediaQuery.of(context).size.height * 0.85,
            ),
            child: SingleChildScrollView(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Handle bar
                  Center(
                    child: Container(
                      width: 40, height: 4,
                      decoration: BoxDecoration(
                        color: Colors.grey.shade300,
                        borderRadius: BorderRadius.circular(2),
                      ),
                    ),
                  ),
                  const SizedBox(height: 16),

                  // Header
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text('Detail Pesanan',
                              style: GoogleFonts.inter(
                                  fontSize: 18, fontWeight: FontWeight.bold)),
                          const SizedBox(height: 2),
                          Text(order.orderNumber,
                              style: GoogleFonts.inter(
                                  fontSize: 13, color: AppColors.textMuted)),
                        ],
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(
                            horizontal: 10, vertical: 5),
                        decoration: BoxDecoration(
                          color: _getStatusColor(order.status).withOpacity(0.12),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Text(order.statusLabel,
                            style: GoogleFonts.inter(
                              fontSize: 12,
                              fontWeight: FontWeight.bold,
                              color: _getStatusColor(order.status),
                            )),
                      ),
                    ],
                  ),
                  const SizedBox(height: 20),

                  // Status Timeline
                  if (!isCancelled) ...[
                    Text('Status Pesanan',
                        style: GoogleFonts.inter(
                            fontSize: 14, fontWeight: FontWeight.bold)),
                    const SizedBox(height: 12),
                    ...List.generate(statusSteps.length, (i) {
                      final isActive = i <= currentIdx;
                      final isCurrent = i == currentIdx;
                      final isLast = i == statusSteps.length - 1;
                      return Row(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Column(
                            children: [
                              Container(
                                width: 32, height: 32,
                                decoration: BoxDecoration(
                                  color: isActive
                                      ? (isCurrent
                                          ? AppColors.primary
                                          : AppColors.success)
                                      : Colors.grey.shade200,
                                  shape: BoxShape.circle,
                                ),
                                child: Icon(statusIcons[i],
                                    size: 16,
                                    color: isActive
                                        ? Colors.white
                                        : Colors.grey.shade400),
                              ),
                              if (!isLast)
                                Container(
                                  width: 2, height: 28,
                                  color: isActive
                                      ? AppColors.success
                                      : Colors.grey.shade200,
                                ),
                            ],
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: Padding(
                              padding: EdgeInsets.only(
                                  bottom: isLast ? 0 : 12, top: 6),
                              child: Text(statusLabels[i],
                                  style: GoogleFonts.inter(
                                    fontSize: 13,
                                    fontWeight: isCurrent
                                        ? FontWeight.bold
                                        : FontWeight.normal,
                                    color: isActive
                                        ? AppColors.textPrimary
                                        : AppColors.textMuted,
                                  )),
                            ),
                          ),
                        ],
                      );
                    }),
                    const Divider(height: 28),
                  ] else ...[
                    Container(
                      width: double.infinity,
                      padding: const EdgeInsets.all(14),
                      decoration: BoxDecoration(
                        color: AppColors.error.withOpacity(0.08),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Row(
                        children: [
                          const Icon(Icons.cancel_rounded,
                              color: AppColors.error, size: 22),
                          const SizedBox(width: 10),
                          Expanded(
                            child: Text('Pesanan ini telah dibatalkan',
                                style: GoogleFonts.inter(
                                    color: AppColors.error,
                                    fontWeight: FontWeight.w600,
                                    fontSize: 13)),
                          ),
                        ],
                      ),
                    ),
                    const Divider(height: 28),
                  ],

                  // Rincian Pesanan
                  Text('Rincian Pesanan',
                      style: GoogleFonts.inter(
                          fontSize: 14, fontWeight: FontWeight.bold)),
                  const SizedBox(height: 12),
                  Container(
                    padding: const EdgeInsets.all(14),
                    decoration: BoxDecoration(
                      color: AppColors.primaryLight,
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(
                          color: AppColors.primary.withOpacity(0.15)),
                    ),
                    child: Column(
                      children: [
                        _detailRow('Jumlah', '${order.gallonQty} Galon'),
                        const SizedBox(height: 8),
                        _detailRow('Harga Satuan',
                            currencyFormatter.format(order.unitPrice)),
                        const SizedBox(height: 8),
                        _detailRow(
                            'Subtotal',
                            currencyFormatter
                                .format(order.unitPrice * order.gallonQty)),
                        const SizedBox(height: 8),
                        const Divider(height: 1),
                        const SizedBox(height: 8),
                        _detailRow('Total Bayar',
                            currencyFormatter.format(order.totalPrice),
                            isBold: true),
                      ],
                    ),
                  ),
                  const SizedBox(height: 16),

                  // Info Pengiriman
                  Text('Informasi Pengiriman',
                      style: GoogleFonts.inter(
                          fontSize: 14, fontWeight: FontWeight.bold)),
                  const SizedBox(height: 12),
                  Container(
                    padding: const EdgeInsets.all(14),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: const Color(0xFFE2E8F0)),
                    ),
                    child: Column(
                      children: [
                        _detailRow('Metode',
                            order.deliveryMethod == 'antar'
                                ? '🛵 Diantar ke Rumah'
                                : '🏪 Ambil Sendiri'),
                        if (order.deliveryAddress != null &&
                            order.deliveryAddress!.isNotEmpty) ...[
                          const SizedBox(height: 8),
                          _detailRow('Alamat', order.deliveryAddress!),
                        ],
                        const SizedBox(height: 8),
                        _detailRow('Pembayaran',
                            order.paymentMethod == 'tunai'
                                ? '💵 Tunai'
                                : '🏦 Transfer'),
                        const SizedBox(height: 8),
                        _detailRow('Tanggal', order.orderDate),
                        if (order.completedAt != null) ...[
                          const SizedBox(height: 8),
                          _detailRow('Selesai', order.completedAt!),
                        ],
                        if (order.processorName != null) ...[
                          const SizedBox(height: 8),
                          _detailRow('Diproses Oleh', order.processorName!),
                        ],
                        if (order.source != null) ...[
                          const SizedBox(height: 8),
                          _detailRow('Sumber',
                              order.source == 'online'
                                  ? '📱 Online (App)'
                                  : '🏪 Offline (Kasir)'),
                        ],
                      ],
                    ),
                  ),

                  // Catatan
                  if (order.notes != null && order.notes!.isNotEmpty) ...[
                    const SizedBox(height: 16),
                    Text('Catatan',
                        style: GoogleFonts.inter(
                            fontSize: 14, fontWeight: FontWeight.bold)),
                    const SizedBox(height: 8),
                    Container(
                      width: double.infinity,
                      padding: const EdgeInsets.all(14),
                      decoration: BoxDecoration(
                        color: const Color(0xFFFFFBEB),
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: const Color(0xFFFDE68A)),
                      ),
                      child: Row(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Icon(Icons.sticky_note_2_outlined,
                              size: 18, color: Color(0xFFD97706)),
                          const SizedBox(width: 10),
                          Expanded(
                            child: Text(order.notes!,
                                style: GoogleFonts.inter(
                                    fontSize: 13,
                                    color: const Color(0xFF92400E),
                                    height: 1.4)),
                          ),
                        ],
                      ),
                    ),
                  ],

                  const SizedBox(height: 20),
                  SizedBox(
                    width: double.infinity,
                    child: ElevatedButton(
                      onPressed: () => Navigator.pop(ctx),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppColors.primary,
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(12)),
                      ),
                      child: Text('Tutup',
                          style: GoogleFonts.inter(
                              fontSize: 14,
                              fontWeight: FontWeight.bold,
                              color: Colors.white)),
                    ),
                  ),
                ],
              ),
            ),
          ),
        );
      },
    );
  }

  Widget _detailRow(String label, String value, {bool isBold = false}) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(label,
            style: GoogleFonts.inter(
                fontSize: 12, color: AppColors.textSecondary)),
        const SizedBox(width: 12),
        Flexible(
          child: Text(value,
              textAlign: TextAlign.end,
              style: GoogleFonts.inter(
                fontSize: 12,
                fontWeight: isBold ? FontWeight.bold : FontWeight.w600,
                color: isBold ? AppColors.primary : AppColors.textPrimary,
              )),
        ),
      ],
    );
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
                  tooltip: 'Notifikasi',
                  icon: Stack(
                    clipBehavior: Clip.none,
                    children: [
                      const Icon(Icons.notifications_outlined,
                          color: AppColors.textPrimary, size: 24),
                      if (_unreadNotifs > 0)
                        Positioned(
                          right: -3,
                          top: -3,
                          child: Container(
                            padding: const EdgeInsets.all(3),
                            decoration: const BoxDecoration(
                              color: AppColors.error,
                              shape: BoxShape.circle,
                            ),
                            constraints: const BoxConstraints(
                              minWidth: 16,
                              minHeight: 16,
                            ),
                            child: Text(
                              '$_unreadNotifs',
                              style: const TextStyle(
                                color: Colors.white,
                                fontSize: 9,
                                fontWeight: FontWeight.bold,
                              ),
                              textAlign: TextAlign.center,
                            ),
                          ),
                        ),
                    ],
                  ),
                  onPressed: () {
                    Navigator.push(
                      context,
                      MaterialPageRoute(
                          builder: (_) => const NotificationsScreen()),
                    ).then((_) => _loadData());
                  },
                ),
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
                      // BANNER REAKTIVASI (Jika Tidak Aktif)
                      // ===================
                      if (status == 'tidak_aktif')
                        Container(
                          margin: const EdgeInsets.only(bottom: 16),
                          padding: const EdgeInsets.all(16),
                          decoration: BoxDecoration(
                            color: const Color(0xFFFEF2F2),
                            borderRadius: BorderRadius.circular(16),
                            border: Border.all(color: const Color(0xFFFCA5A5)),
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Row(
                                children: [
                                  const Icon(Icons.warning_amber_rounded,
                                      color: AppColors.error, size: 24),
                                  const SizedBox(width: 8),
                                  Text(
                                    'Akun Pelanggan Tidak Aktif',
                                    style: GoogleFonts.inter(
                                      fontWeight: FontWeight.bold,
                                      fontSize: 15,
                                      color: AppColors.error,
                                    ),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 8),
                              Text(
                                'Akun Anda dinonaktifkan sementara karena tidak ada transaksi dalam jangka waktu tertentu. Silakan reaktivasi akun Anda untuk dapat memesan kembali.',
                                style: GoogleFonts.inter(
                                  fontSize: 12,
                                  color: AppColors.textSecondary,
                                  height: 1.4,
                                ),
                              ),
                              const SizedBox(height: 12),
                              SizedBox(
                                width: double.infinity,
                                child: ElevatedButton.icon(
                                  icon: const Icon(Icons.restart_alt_rounded, size: 18),
                                  label: const Text('Reaktivasi Akun Sekarang',
                                      style: TextStyle(fontWeight: FontWeight.bold)),
                                  onPressed: _showReactivationConfirmDialog,
                                  style: ElevatedButton.styleFrom(
                                    backgroundColor: AppColors.error,
                                    foregroundColor: Colors.white,
                                    shape: RoundedRectangleBorder(
                                      borderRadius: BorderRadius.circular(10),
                                    ),
                                    padding: const EdgeInsets.symmetric(vertical: 12),
                                    elevation: 0,
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ),

                      // ===================
                      // MEMBER CARD
                      // ===================
                      RepaintBoundary(
                        key: _cardRepaintKey,
                        child: Container(
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
                        ), // closes Container (child of RepaintBoundary)
                      ), // end RepaintBoundary


                      // Tombol Unduh Kartu
                      const SizedBox(height: 12),
                      SizedBox(
                        width: double.infinity,
                        child: OutlinedButton.icon(
                          onPressed: _isDownloading ? null : _downloadCard,
                          style: OutlinedButton.styleFrom(
                            padding: const EdgeInsets.symmetric(vertical: 12),
                            side: const BorderSide(
                                color: AppColors.primary, width: 1.5),
                            shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(12)),
                            foregroundColor: AppColors.primary,
                          ),
                          icon: _isDownloading
                              ? const SizedBox(
                                  width: 16,
                                  height: 16,
                                  child: CircularProgressIndicator(
                                    strokeWidth: 2,
                                    color: AppColors.primary,
                                  ),
                                )
                              : const Icon(Icons.download_rounded, size: 18),
                          label: Text(
                            _isDownloading
                                ? 'Mengunduh...'
                                : 'Unduh Kartu Member',
                            style: GoogleFonts.inter(
                              fontWeight: FontWeight.w600,
                              fontSize: 14,
                            ),
                          ),
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
                      // PROMO PERSONAL AKTIF
                      // ===================
                      if (_activePromos.isNotEmpty) ...[
                        Text('Promo Spesial Untuk Anda',
                            style: GoogleFonts.inter(
                                fontSize: 16,
                                fontWeight: FontWeight.bold,
                                color: AppColors.textPrimary)),
                        const SizedBox(height: 10),
                        ..._activePromos.map((promo) {
                          final isPercent = promo['discount_type'] == 'persen';
                          final val = (promo['discount_value'] as num).toDouble();
                          final discountText = isPercent
                              ? '${val.toStringAsFixed(0)}% OFF'
                              : 'Diskon ${NumberFormat.currency(locale: "id_ID", symbol: "Rp ", decimalDigits: 0).format(val)}';
                          return Container(
                            margin: const EdgeInsets.only(bottom: 10),
                            decoration: BoxDecoration(
                              gradient: const LinearGradient(
                                colors: [Color(0xFF7C3AED), Color(0xFF9333EA)],
                                begin: Alignment.topLeft,
                                end: Alignment.bottomRight,
                              ),
                              borderRadius: BorderRadius.circular(14),
                              boxShadow: [
                                BoxShadow(
                                  color: const Color(0xFF7C3AED).withOpacity(0.3),
                                  blurRadius: 10,
                                  offset: const Offset(0, 4),
                                ),
                              ],
                            ),
                            child: Material(
                              color: Colors.transparent,
                              child: InkWell(
                                borderRadius: BorderRadius.circular(14),
                                onTap: () {
                                  OrderTab.pendingPromoToSelect = promo;
                                  context
                                      .findAncestorStateOfType<
                                          CustomerMainScreenState>()
                                      ?.switchTab(1);
                                  ScaffoldMessenger.of(context).showSnackBar(
                                    SnackBar(
                                      content: Text(
                                          'Promo "${promo['name']}" berhasil dipilih untuk pemesanan!'),
                                      backgroundColor:
                                          const Color(0xFF7C3AED),
                                      behavior: SnackBarBehavior.floating,
                                      duration: const Duration(seconds: 2),
                                    ),
                                  );
                                },
                                child: Padding(
                                  padding: const EdgeInsets.all(14),
                                  child: Column(
                                    children: [
                                      Row(
                                        children: [
                                          Container(
                                            padding: const EdgeInsets.all(10),
                                            decoration: BoxDecoration(
                                              color: Colors.white
                                                  .withOpacity(0.2),
                                              borderRadius:
                                                  BorderRadius.circular(10),
                                            ),
                                            child: const Icon(
                                                Icons.stars_rounded,
                                                color: Colors.white,
                                                size: 24),
                                          ),
                                          const SizedBox(width: 12),
                                          Expanded(
                                            child: Column(
                                              crossAxisAlignment:
                                                  CrossAxisAlignment.start,
                                              children: [
                                                Text(
                                                    promo['name'] ??
                                                        'Promo Spesial',
                                                    style: GoogleFonts.inter(
                                                        color: Colors.white,
                                                        fontWeight:
                                                            FontWeight.bold,
                                                        fontSize: 14),
                                                    maxLines: 1,
                                                    overflow:
                                                        TextOverflow.ellipsis),
                                                if ((promo['description'] ??
                                                        '')
                                                    .toString()
                                                    .isNotEmpty)
                                                  Text(promo['description'],
                                                      style: GoogleFonts.inter(
                                                          color: Colors.white70,
                                                          fontSize: 11),
                                                      maxLines: 1,
                                                      overflow: TextOverflow
                                                          .ellipsis),
                                                const SizedBox(height: 4),
                                                Text(
                                                    'Berlaku s/d ${promo['valid_until'] ?? '-'}',
                                                    style: GoogleFonts.inter(
                                                        color: Colors.white60,
                                                        fontSize: 11)),
                                              ],
                                            ),
                                          ),
                                          const SizedBox(width: 8),
                                          Container(
                                            padding: const EdgeInsets.symmetric(
                                                horizontal: 10, vertical: 6),
                                            decoration: BoxDecoration(
                                              color: Colors.white,
                                              borderRadius:
                                                  BorderRadius.circular(8),
                                            ),
                                            child: Text(discountText,
                                                style: GoogleFonts.inter(
                                                    color:
                                                        const Color(0xFF7C3AED),
                                                    fontWeight: FontWeight.bold,
                                                    fontSize: 12)),
                                          ),
                                        ],
                                      ),
                                      const SizedBox(height: 10),
                                      Container(
                                        padding: const EdgeInsets.symmetric(
                                            horizontal: 10, vertical: 6),
                                        decoration: BoxDecoration(
                                          color: Colors.white.withOpacity(0.15),
                                          borderRadius:
                                              BorderRadius.circular(8),
                                        ),
                                        child: Row(
                                          mainAxisAlignment:
                                              MainAxisAlignment.center,
                                          children: [
                                            Text(
                                              'Gunakan untuk Pesan Galon',
                                              style: GoogleFonts.inter(
                                                  color: Colors.white,
                                                  fontSize: 11,
                                                  fontWeight: FontWeight.w600),
                                            ),
                                            const SizedBox(width: 6),
                                            const Icon(
                                                Icons.arrow_forward_rounded,
                                                color: Colors.white,
                                                size: 14),
                                          ],
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                              ),
                            ),
                          );
                        }),
                        const SizedBox(height: 10),
                      ],

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
                              Navigator.push(
                                context,
                                MaterialPageRoute(builder: (_) => const VouchersScreen()),
                              );
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
                              const Icon(Icons.receipt_long_outlined,
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
                            return Material(
                              color: Colors.white,
                              borderRadius: BorderRadius.circular(14),
                              child: InkWell(
                                onTap: () => _showOrderDetailModal(item),
                                borderRadius: BorderRadius.circular(14),
                                child: Container(
                                  padding: const EdgeInsets.all(14),
                                  decoration: BoxDecoration(
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
                            ),
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
