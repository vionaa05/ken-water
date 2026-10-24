import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../../constants/app_colors.dart';
import '../../../models/order_model.dart';
import '../../../services/api_service.dart';

class AdminOrdersTab extends StatefulWidget {
  const AdminOrdersTab({super.key});

  @override
  State<AdminOrdersTab> createState() => _AdminOrdersTabState();
}

class _AdminOrdersTabState extends State<AdminOrdersTab> {
  List<OrderModel> _allOrders = [];
  Map<String, dynamic> _statusCounts = {};
  bool _isLoading = true;
  String _selectedFilter = 'all'; // all, dipesan, diproses, diantar, siap_diambil, selesai, dibatalkan
  String _searchQuery = '';
  final TextEditingController _searchController = TextEditingController();

  final currencyFormatter = NumberFormat.currency(
    locale: 'id_ID',
    symbol: 'Rp ',
    decimalDigits: 0,
  );

  @override
  void initState() {
    super.initState();
    _loadOrders();
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _loadOrders() async {
    setState(() => _isLoading = true);
    final data = await ApiService.getAdminOrdersData();
    if (mounted) {
      setState(() {
        _allOrders = (data['orders'] as List<OrderModel>?) ?? [];
        _statusCounts = (data['status_counts'] as Map<String, dynamic>?) ?? {};
        _isLoading = false;
      });
    }
  }

  List<OrderModel> get _filteredOrders {
    return _allOrders.where((order) {
      final normalizedStatus = _normalizeStatus(order.status);
      final matchesFilter = _selectedFilter == 'all' ||
          normalizedStatus == _selectedFilter;
      final query = _searchQuery.toLowerCase();
      final matchesSearch = query.isEmpty ||
          (order.orderNumber.toLowerCase().contains(query)) ||
          (order.customerName?.toLowerCase().contains(query) ?? false) ||
          (order.customerPhone?.toLowerCase().contains(query) ?? false) ||
          (order.customerId?.toLowerCase().contains(query) ?? false) ||
          (order.deliveryAddress?.toLowerCase().contains(query) ?? false);
      return matchesFilter && matchesSearch;
    }).toList();
  }

  String _normalizeStatus(String status) {
    switch (status.toLowerCase()) {
      case 'pending':
        return 'dipesan';
      case 'confirmed':
        return 'diproses';
      case 'delivered':
      case 'completed':
        return 'selesai';
      case 'cancelled':
        return 'dibatalkan';
      default:
        return status.toLowerCase();
    }
  }

  Future<void> _updateStatus(OrderModel order, String newStatus) async {
    final res = await ApiService.adminUpdateOrderStatus(order.id, newStatus);
    if (!mounted) return;

    if (res['success'] == true) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(res['message'] ?? 'Status pesanan berhasil diperbarui'),
          backgroundColor: AppColors.success,
          behavior: SnackBarBehavior.floating,
        ),
      );
      _loadOrders();
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(res['message'] ?? 'Gagal mengubah status'),
          backgroundColor: AppColors.error,
          behavior: SnackBarBehavior.floating,
        ),
      );
    }
  }

  Future<void> _confirmCancelOrder(OrderModel order) async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        backgroundColor: Colors.white,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Text('Batalkan Pesanan?', style: TextStyle(color: AppColors.textPrimary, fontWeight: FontWeight.bold)),
        content: Text(
          'Yakin ingin membatalkan pesanan #${order.orderNumber} milik ${order.customerName ?? 'pelanggan'}?',
          style: const TextStyle(color: AppColors.textSecondary),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: const Text('Kembali', style: TextStyle(color: AppColors.textSecondary)),
          ),
          ElevatedButton(
            onPressed: () => Navigator.pop(ctx, true),
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.error,
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
            ),
            child: const Text('Ya, Batalkan'),
          ),
        ],
      ),
    );

    if (confirm == true) {
      _updateStatus(order, 'dibatalkan');
    }
  }

  Future<void> _openWhatsApp(String phone, String orderNumber) async {
    String cleanPhone = phone.replaceAll(RegExp(r'[^0-9]'), '');
    if (cleanPhone.startsWith('0')) {
      cleanPhone = '62${cleanPhone.substring(1)}';
    }
    final uri = Uri.parse('https://wa.me/$cleanPhone?text=Halo,%20kami%20dari%20Ken%20Water%20mengenai%20pesanan%20%23$orderNumber');
    try {
      if (await canLaunchUrl(uri)) {
        await launchUrl(uri, mode: LaunchMode.externalApplication);
      }
    } catch (_) {}
  }

  void _showOrderActionModal(OrderModel order) {
    final currentStatus = _normalizeStatus(order.status);
    final isDelivery = (order.deliveryMethod == 'antar' || order.deliveryMethod == 'delivery');

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) {
        return SingleChildScrollView(
          child: Padding(
            padding: EdgeInsets.only(
              left: 20,
              right: 20,
              top: 20,
              bottom: MediaQuery.of(context).viewInsets.bottom + 24,
            ),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
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
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Pesanan #${order.orderNumber}',
                          style: const TextStyle(
                            fontSize: 18,
                            fontWeight: FontWeight.bold,
                            color: AppColors.textPrimary,
                          ),
                        ),
                        const SizedBox(height: 2),
                        Text(
                          order.orderDate,
                          style: const TextStyle(fontSize: 12, color: AppColors.textSecondary),
                        ),
                      ],
                    ),
                    _buildStatusBadge(order.status),
                  ],
                ),
                const Divider(color: Color(0xFFE2E8F0), height: 28),

                // Info Pelanggan
                Container(
                  padding: const EdgeInsets.all(14),
                  decoration: BoxDecoration(
                    color: const Color(0xFFF8FAFC),
                    borderRadius: BorderRadius.circular(14),
                    border: Border.all(color: const Color(0xFFE2E8F0)),
                  ),
                  child: Row(
                    children: [
                      CircleAvatar(
                        radius: 20,
                        backgroundColor: AppColors.primaryLight,
                        child: Text(
                          (order.customerName?.isNotEmpty ?? false)
                              ? order.customerName![0].toUpperCase()
                              : 'U',
                          style: const TextStyle(fontWeight: FontWeight.bold, color: AppColors.primary),
                        ),
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
                                    order.customerName ?? 'Pelanggan',
                                    style: const TextStyle(
                                      fontWeight: FontWeight.bold,
                                      color: AppColors.textPrimary,
                                      fontSize: 14,
                                    ),
                                    maxLines: 1,
                                    overflow: TextOverflow.ellipsis,
                                  ),
                                ),
                                const SizedBox(width: 6),
                                if (order.customerLevel != null)
                                  _buildMiniLevelBadge(order.customerLevel!),
                              ],
                            ),
                            const SizedBox(height: 2),
                            Text(
                              '${order.customerId ?? '-'} • ${order.customerPhone ?? '-'}',
                              style: const TextStyle(fontSize: 12, color: AppColors.textSecondary),
                            ),
                          ],
                        ),
                      ),
                      if (order.customerPhone != null && order.customerPhone!.isNotEmpty) ...[
                        const SizedBox(width: 8),
                        IconButton(
                          tooltip: 'Hubungi via WhatsApp',
                          style: IconButton.styleFrom(
                            backgroundColor: const Color(0xFFDCFCE7),
                            padding: const EdgeInsets.all(8),
                          ),
                          icon: const Icon(Icons.chat, color: Color(0xFF16A34A), size: 18),
                          onPressed: () => _openWhatsApp(order.customerPhone!, order.orderNumber),
                        ),
                      ],
                    ],
                  ),
                ),

                const SizedBox(height: 16),

                // Rincian Pesanan
                _buildModalDetailRow('Jumlah Galon', '${order.gallonQty} Galon'),
                if (order.unitPrice > 0) ...[
                  const SizedBox(height: 8),
                  _buildModalDetailRow('Harga Satuan', '${currencyFormatter.format(order.unitPrice)} / galon'),
                ],
                const SizedBox(height: 8),
                _buildModalDetailRow('Total Pembayaran', currencyFormatter.format(order.totalPrice), isHighlight: true),
                const SizedBox(height: 8),
                _buildModalDetailRow(
                  'Metode Pengiriman',
                  isDelivery ? '📦 Antar ke Rumah' : '🏪 Ambil Sendiri di Depot',
                ),
                const SizedBox(height: 8),
                _buildModalDetailRow(
                  'Metode Pembayaran',
                  (order.paymentMethod ?? 'Tunai').toUpperCase(),
                ),

                if (order.deliveryAddress != null && order.deliveryAddress!.isNotEmpty) ...[
                  const SizedBox(height: 8),
                  _buildModalDetailRow('Alamat Pengantaran', order.deliveryAddress!),
                ],
                if (order.notes != null && order.notes!.isNotEmpty) ...[
                  const SizedBox(height: 8),
                  _buildModalDetailRow('Catatan Pelanggan', order.notes!, isItalic: true),
                ],
                if (order.processorName != null && order.processorName!.isNotEmpty) ...[
                  const SizedBox(height: 8),
                  _buildModalDetailRow('Diproses Oleh', order.processorName!),
                ],
                if (order.completedAt != null && order.completedAt!.isNotEmpty) ...[
                  const SizedBox(height: 8),
                  _buildModalDetailRow('Waktu Selesai', order.completedAt!),
                ],

                const SizedBox(height: 24),

                // Tombol Aksi Status Sesuai Alur Web
                const Text(
                  'Perbarui Status Pesanan:',
                  style: TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.bold,
                    color: AppColors.textSecondary,
                  ),
                ),
                const SizedBox(height: 12),

                // 1. Dipesan -> Proses atau Batalkan
                if (currentStatus == 'dipesan') ...[
                  Row(
                    children: [
                      Expanded(
                        flex: 2,
                        child: ElevatedButton.icon(
                          icon: const Icon(Icons.check_circle_outline, size: 18),
                          label: const Text('✅ Proses Sekarang'),
                          onPressed: () {
                            Navigator.pop(ctx);
                            _updateStatus(order, 'diproses');
                          },
                          style: ElevatedButton.styleFrom(
                            backgroundColor: const Color(0xFF2563EB),
                            foregroundColor: Colors.white,
                            padding: const EdgeInsets.symmetric(vertical: 14),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                            elevation: 0,
                          ),
                        ),
                      ),
                      const SizedBox(width: 8),
                      Expanded(
                        child: OutlinedButton.icon(
                          icon: const Icon(Icons.cancel_outlined, size: 16),
                          label: const Text('Batalkan'),
                          onPressed: () {
                            Navigator.pop(ctx);
                            _confirmCancelOrder(order);
                          },
                          style: OutlinedButton.styleFrom(
                            foregroundColor: AppColors.error,
                            side: const BorderSide(color: AppColors.error),
                            padding: const EdgeInsets.symmetric(vertical: 14),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                          ),
                        ),
                      ),
                    ],
                  ),
                ]
                // 2. Diproses -> Tandai Diantar (jika delivery) / Siap Diambil (jika pickup) atau Batalkan
                else if (currentStatus == 'diproses') ...[
                  Row(
                    children: [
                      Expanded(
                        flex: 2,
                        child: ElevatedButton.icon(
                          icon: Icon(isDelivery ? Icons.local_shipping : Icons.store, size: 18),
                          label: Text(isDelivery ? '🚚 Tandai Diantar' : '✔ Siap Diambil'),
                          onPressed: () {
                            Navigator.pop(ctx);
                            _updateStatus(order, isDelivery ? 'diantar' : 'siap_diambil');
                          },
                          style: ElevatedButton.styleFrom(
                            backgroundColor: const Color(0xFF9333EA),
                            foregroundColor: Colors.white,
                            padding: const EdgeInsets.symmetric(vertical: 14),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                            elevation: 0,
                          ),
                        ),
                      ),
                      const SizedBox(width: 8),
                      Expanded(
                        child: OutlinedButton.icon(
                          icon: const Icon(Icons.cancel_outlined, size: 16),
                          label: const Text('Batalkan'),
                          onPressed: () {
                            Navigator.pop(ctx);
                            _confirmCancelOrder(order);
                          },
                          style: OutlinedButton.styleFrom(
                            foregroundColor: AppColors.error,
                            side: const BorderSide(color: AppColors.error),
                            padding: const EdgeInsets.symmetric(vertical: 14),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                          ),
                        ),
                      ),
                    ],
                  ),
                ]
                // 3. Diantar atau Siap Diambil -> Tandai Selesai (tambah poin pelanggan) atau Batalkan
                else if (currentStatus == 'diantar' || currentStatus == 'siap_diambil') ...[
                  Row(
                    children: [
                      Expanded(
                        flex: 2,
                        child: ElevatedButton.icon(
                          icon: const Icon(Icons.celebration, size: 18),
                          label: const Text('🎉 Tandai Selesai & Beri Poin'),
                          onPressed: () {
                            Navigator.pop(ctx);
                            _updateStatus(order, 'selesai');
                          },
                          style: ElevatedButton.styleFrom(
                            backgroundColor: AppColors.success,
                            foregroundColor: Colors.white,
                            padding: const EdgeInsets.symmetric(vertical: 14),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                            elevation: 0,
                          ),
                        ),
                      ),
                      const SizedBox(width: 8),
                      Expanded(
                        child: OutlinedButton.icon(
                          icon: const Icon(Icons.cancel_outlined, size: 16),
                          label: const Text('Batalkan'),
                          onPressed: () {
                            Navigator.pop(ctx);
                            _confirmCancelOrder(order);
                          },
                          style: OutlinedButton.styleFrom(
                            foregroundColor: AppColors.error,
                            side: const BorderSide(color: AppColors.error),
                            padding: const EdgeInsets.symmetric(vertical: 14),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                          ),
                        ),
                      ),
                    ],
                  ),
                ]
                // 4. Selesai
                else if (currentStatus == 'selesai') ...[
                  Container(
                    padding: const EdgeInsets.all(14),
                    decoration: BoxDecoration(
                      color: const Color(0xFFF0FDF4),
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: const Color(0xFFBBF7D0)),
                    ),
                    child: const Row(
                      children: [
                        Icon(Icons.check_circle, color: Color(0xFF16A34A), size: 20),
                        SizedBox(width: 10),
                        Expanded(
                          child: Text(
                            'Pesanan ini telah selesai dan poin telah dikreditkan ke pelanggan.',
                            style: TextStyle(color: Color(0xFF15803D), fontSize: 13, fontWeight: FontWeight.w500),
                          ),
                        ),
                      ],
                    ),
                  ),
                ]
                // 5. Dibatalkan
                else if (currentStatus == 'dibatalkan') ...[
                  Container(
                    padding: const EdgeInsets.all(14),
                    decoration: BoxDecoration(
                      color: const Color(0xFFFEF2F2),
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: const Color(0xFFFECACA)),
                    ),
                    child: const Row(
                      children: [
                        Icon(Icons.cancel, color: Color(0xFFDC2626), size: 20),
                        SizedBox(width: 10),
                        Expanded(
                          child: Text(
                            'Pesanan ini telah dibatalkan.',
                            style: TextStyle(color: Color(0xFFDC2626), fontSize: 13, fontWeight: FontWeight.w500),
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ],
            ),
          ),
        );
      },
    );
  }

  Widget _buildModalDetailRow(String label, String value, {bool isHighlight = false, bool isItalic = false}) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(label, style: const TextStyle(color: AppColors.textSecondary, fontSize: 13)),
        const SizedBox(width: 14),
        Expanded(
          child: Text(
            value,
            textAlign: TextAlign.end,
            style: TextStyle(
              fontSize: 13,
              fontWeight: isHighlight ? FontWeight.bold : FontWeight.w600,
              color: isHighlight ? AppColors.primary : AppColors.textPrimary,
              fontStyle: isItalic ? FontStyle.italic : FontStyle.normal,
            ),
          ),
        ),
      ],
    );
  }

  @override
  Widget build(BuildContext context) {
    final filtered = _filteredOrders;

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: Colors.white,
        surfaceTintColor: Colors.white,
        elevation: 0.5,
        shadowColor: const Color(0x0A000000),
        title: const Text(
          'Pesanan Masuk (Online)',
          style: TextStyle(
            fontWeight: FontWeight.bold,
            fontSize: 18,
            color: AppColors.textPrimary,
          ),
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh, color: AppColors.textSecondary),
            onPressed: _loadOrders,
          ),
        ],
      ),
      body: Column(
        children: [
          // 1. Status Summary Cards (Seperti di Web)
          Container(
            color: Colors.white,
            padding: const EdgeInsets.fromLTRB(16, 12, 16, 12),
            child: Row(
              children: [
                Expanded(
                  child: _buildSummaryCard(
                    title: 'Dipesan',
                    count: _statusCounts['dipesan'] ?? 0,
                    color: const Color(0xFFD97706),
                    keyFilter: 'dipesan',
                  ),
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: _buildSummaryCard(
                    title: 'Diproses',
                    count: _statusCounts['diproses'] ?? 0,
                    color: const Color(0xFF2563EB),
                    keyFilter: 'diproses',
                  ),
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: _buildSummaryCard(
                    title: 'Diantar',
                    count: _statusCounts['diantar'] ?? 0,
                    color: const Color(0xFF7C3AED),
                    keyFilter: 'diantar',
                  ),
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: _buildSummaryCard(
                    title: 'Siap Diambil',
                    count: _statusCounts['siap_diambil'] ?? 0,
                    color: const Color(0xFFEA580C),
                    keyFilter: 'siap_diambil',
                  ),
                ),
              ],
            ),
          ),

          // 2. Search Box & Filter Chips
          Container(
            color: Colors.white,
            padding: const EdgeInsets.fromLTRB(16, 0, 16, 14),
            child: Column(
              children: [
                TextField(
                  controller: _searchController,
                  style: const TextStyle(color: AppColors.textPrimary),
                  onChanged: (val) => setState(() => _searchQuery = val),
                  decoration: InputDecoration(
                    hintText: 'Cari nama, no HP, order...',
                    hintStyle: const TextStyle(color: AppColors.textMuted),
                    prefixIcon: const Icon(Icons.search, color: AppColors.textSecondary),
                    suffixIcon: _searchQuery.isNotEmpty
                        ? IconButton(
                            icon: const Icon(Icons.clear, color: AppColors.textSecondary),
                            onPressed: () {
                              _searchController.clear();
                              setState(() => _searchQuery = '');
                            },
                          )
                        : null,
                    filled: true,
                    fillColor: const Color(0xFFF8FAFC),
                    contentPadding: const EdgeInsets.symmetric(vertical: 0, horizontal: 16),
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
                  ),
                ),
                const SizedBox(height: 10),
                SingleChildScrollView(
                  scrollDirection: Axis.horizontal,
                  child: Row(
                    children: [
                      _buildFilterChip('all', 'Semua (${_allOrders.length})'),
                      const SizedBox(width: 8),
                      _buildFilterChip('dipesan', 'Dipesan'),
                      const SizedBox(width: 8),
                      _buildFilterChip('diproses', 'Diproses'),
                      const SizedBox(width: 8),
                      _buildFilterChip('diantar', 'Diantar'),
                      const SizedBox(width: 8),
                      _buildFilterChip('siap_diambil', 'Siap Diambil'),
                      const SizedBox(width: 8),
                      _buildFilterChip('selesai', 'Selesai'),
                      const SizedBox(width: 8),
                      _buildFilterChip('dibatalkan', 'Dibatalkan'),
                    ],
                  ),
                ),
              ],
            ),
          ),

          // 3. Orders List
          Expanded(
            child: RefreshIndicator(
              onRefresh: _loadOrders,
              color: AppColors.primary,
              backgroundColor: Colors.white,
              child: _isLoading
                  ? const Center(child: CircularProgressIndicator(color: AppColors.primary))
                  : filtered.isEmpty
                      ? Center(
                          child: Column(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              const Icon(Icons.receipt_long, size: 50, color: Color(0xFFCBD5E1)),
                              const SizedBox(height: 12),
                              Text(
                                _searchQuery.isNotEmpty
                                    ? 'Tidak ada pesanan cocok dengan pencarian'
                                    : 'Tidak ada pesanan pada kategori ini',
                                style: const TextStyle(color: AppColors.textSecondary),
                              ),
                            ],
                          ),
                        )
                      : ListView.separated(
                          padding: const EdgeInsets.all(16),
                          itemCount: filtered.length,
                          separatorBuilder: (_, __) => const SizedBox(height: 12),
                          itemBuilder: (ctx, index) {
                            final order = filtered[index];
                            return _buildOrderCard(order);
                          },
                        ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSummaryCard({
    required String title,
    required int count,
    required Color color,
    required String keyFilter,
  }) {
    final isSelected = _selectedFilter == keyFilter;
    return GestureDetector(
      onTap: () {
        setState(() {
          _selectedFilter = isSelected ? 'all' : keyFilter;
        });
      },
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 150),
        padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 8),
        decoration: BoxDecoration(
          color: isSelected ? color.withOpacity(0.1) : const Color(0xFFF8FAFC),
          borderRadius: BorderRadius.circular(12),
          border: Border.all(
            color: isSelected ? color : const Color(0xFFE2E8F0),
            width: isSelected ? 2 : 1,
          ),
        ),
        child: Column(
          children: [
            Text(
              '$count',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.bold,
                color: color,
              ),
            ),
            const SizedBox(height: 2),
            Text(
              title,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              style: TextStyle(
                fontSize: 10,
                color: isSelected ? color : AppColors.textSecondary,
                fontWeight: isSelected ? FontWeight.bold : FontWeight.w500,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildFilterChip(String key, String label) {
    final isSelected = _selectedFilter == key;
    return GestureDetector(
      onTap: () => setState(() => _selectedFilter = key),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 150),
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 7),
        decoration: BoxDecoration(
          color: isSelected ? AppColors.primary : const Color(0xFFF8FAFC),
          borderRadius: BorderRadius.circular(20),
          border: Border.all(
            color: isSelected ? AppColors.primary : const Color(0xFFE2E8F0),
          ),
        ),
        child: Text(
          label,
          style: TextStyle(
            fontSize: 12,
            fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
            color: isSelected ? Colors.white : AppColors.textSecondary,
          ),
        ),
      ),
    );
  }

  Widget _buildOrderCard(OrderModel order) {
    final isDelivery = (order.deliveryMethod == 'antar' || order.deliveryMethod == 'delivery');

    return Container(
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
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          borderRadius: BorderRadius.circular(16),
          onTap: () => _showOrderActionModal(order),
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.all(8),
                          decoration: BoxDecoration(
                            color: AppColors.primaryLight,
                            borderRadius: BorderRadius.circular(10),
                          ),
                          child: Icon(
                            isDelivery ? Icons.local_shipping : Icons.storefront,
                            color: AppColors.primary,
                            size: 18,
                          ),
                        ),
                        const SizedBox(width: 10),
                        Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              '#${order.orderNumber}',
                              style: const TextStyle(
                                fontWeight: FontWeight.bold,
                                fontSize: 14,
                                color: AppColors.textPrimary,
                              ),
                            ),
                            Text(
                              order.orderDate,
                              style: const TextStyle(fontSize: 11, color: AppColors.textMuted),
                            ),
                          ],
                        ),
                      ],
                    ),
                    _buildStatusBadge(order.status),
                  ],
                ),
                const Divider(color: Color(0xFFE2E8F0), height: 20),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              Flexible(
                                child: Text(
                                  order.customerName ?? 'Pelanggan',
                                  style: const TextStyle(
                                    fontWeight: FontWeight.w600,
                                    fontSize: 14,
                                    color: AppColors.textPrimary,
                                  ),
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                ),
                              ),
                              const SizedBox(width: 6),
                              if (order.customerLevel != null)
                                _buildMiniLevelBadge(order.customerLevel!),
                            ],
                          ),
                          const SizedBox(height: 3),
                          Text(
                            '${order.gallonQty} Galon • ${isDelivery ? 'Diantar' : 'Ambil Sendiri'}',
                            style: const TextStyle(fontSize: 12, color: AppColors.textSecondary),
                          ),
                        ],
                      ),
                    ),
                    Text(
                      currencyFormatter.format(order.totalPrice),
                      style: const TextStyle(
                        fontWeight: FontWeight.bold,
                        fontSize: 16,
                        color: AppColors.primary,
                      ),
                    ),
                  ],
                ),
                if (order.deliveryAddress != null && order.deliveryAddress!.isNotEmpty) ...[
                  const SizedBox(height: 8),
                  Row(
                    children: [
                      const Icon(Icons.location_on_outlined, size: 14, color: AppColors.textMuted),
                      const SizedBox(width: 4),
                      Expanded(
                        child: Text(
                          order.deliveryAddress!,
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: const TextStyle(fontSize: 11, color: AppColors.textSecondary),
                        ),
                      ),
                    ],
                  ),
                ],
                const SizedBox(height: 12),
                Row(
                  mainAxisAlignment: MainAxisAlignment.end,
                  children: [
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                      decoration: BoxDecoration(
                        color: AppColors.primaryLight,
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: const Row(
                        children: [
                          Text(
                            'Kelola & Ubah Status',
                            style: TextStyle(
                              fontSize: 11,
                              color: AppColors.primary,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          SizedBox(width: 4),
                          Icon(Icons.arrow_forward_ios, size: 10, color: AppColors.primary),
                        ],
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildStatusBadge(String status) {
    Color bg;
    Color fg;
    String label;

    switch (_normalizeStatus(status)) {
      case 'dipesan':
        bg = const Color(0xFFFEF3C7);
        fg = const Color(0xFFD97706);
        label = 'Dipesan';
        break;
      case 'diproses':
        bg = const Color(0xFFDBEAFE);
        fg = const Color(0xFF2563EB);
        label = 'Diproses';
        break;
      case 'diantar':
        bg = const Color(0xFFF3E8FF);
        fg = const Color(0xFF7C3AED);
        label = 'Diantar';
        break;
      case 'siap_diambil':
        bg = const Color(0xFFFFEDD5);
        fg = const Color(0xFFEA580C);
        label = 'Siap Diambil';
        break;
      case 'selesai':
        bg = const Color(0xFFDCFCE7);
        fg = const Color(0xFF16A34A);
        label = 'Selesai';
        break;
      case 'dibatalkan':
        bg = const Color(0xFFFEE2E2);
        fg = const Color(0xFFDC2626);
        label = 'Dibatalkan';
        break;
      default:
        bg = const Color(0xFFF1F5F9);
        fg = const Color(0xFF64748B);
        label = status;
        break;
    }

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: bg,
        borderRadius: BorderRadius.circular(12),
      ),
      child: Text(
        label,
        style: TextStyle(color: fg, fontSize: 11, fontWeight: FontWeight.bold),
      ),
    );
  }

  Widget _buildMiniLevelBadge(String level) {
    Color bg;
    Color fg;
    switch (level.toLowerCase()) {
      case 'platinum':
        bg = const Color(0xFFEDE9FE);
        fg = const Color(0xFF7C3AED);
        break;
      case 'gold':
        bg = const Color(0xFFFEF3C7);
        fg = const Color(0xFFD97706);
        break;
      case 'silver':
        bg = const Color(0xFFF1F5F9);
        fg = const Color(0xFF475569);
        break;
      default:
        bg = const Color(0xFFFFEDD5);
        fg = const Color(0xFFC2410C);
        break;
    }
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
      decoration: BoxDecoration(
        color: bg,
        borderRadius: BorderRadius.circular(6),
      ),
      child: Text(
        level.toUpperCase(),
        style: TextStyle(color: fg, fontSize: 8, fontWeight: FontWeight.bold),
      ),
    );
  }
}
