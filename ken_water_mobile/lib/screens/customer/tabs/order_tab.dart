import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:intl/intl.dart';
import '../../../constants/app_colors.dart';
import '../../../services/api_service.dart';
import '../../../services/storage_service.dart';

class OrderTab extends StatefulWidget {
  const OrderTab({super.key});

  @override
  State<OrderTab> createState() => _OrderTabState();
}

class _OrderTabState extends State<OrderTab> {
  final _formKey = GlobalKey<FormState>();
  final _qtyController = TextEditingController(text: '1');
  final _addressController = TextEditingController();
  final _notesController = TextEditingController();

  String _deliveryMethod = 'antar';
  String _paymentMethod = 'tunai';
  bool _isLoading = false;
  double _pricePerGallon = 5000;

  List<dynamic> _vouchers = [];
  Map<String, dynamic>? _selectedVoucher;
  bool _loadingVouchers = false;

  final currencyFormatter =
      NumberFormat.currency(locale: 'id_ID', symbol: 'Rp ', decimalDigits: 0);

  @override
  void initState() {
    super.initState();
    final user = StorageService.getUser();
    if (user?.address != null) {
      _addressController.text = user!.address!;
    }
    _loadSettings();
    _loadVouchers();
  }

  Future<void> _loadSettings() async {
    final res = await ApiService.getCustomerSettings();
    if (mounted) {
      setState(() {
        if (res['success'] == true) {
          _pricePerGallon = (res['settings']['price_per_gallon'] as num?)?.toDouble() ?? 5000;
        }
      });
    }
  }

  Future<void> _loadVouchers() async {
    setState(() => _loadingVouchers = true);
    final res = await ApiService.getVouchers();
    if (mounted) {
      setState(() {
        _loadingVouchers = false;
        if (res['success'] == true) {
          final list = res['vouchers'] as List? ?? [];
          _vouchers = list
              .where((v) => v['is_active'] == true && v['is_used'] != true)
              .toList();
        }
      });
    }
  }

  @override
  void dispose() {
    _qtyController.dispose();
    _addressController.dispose();
    _notesController.dispose();
    super.dispose();
  }

  double get _subtotalPrice {
    final qty = int.tryParse(_qtyController.text) ?? 1;
    return qty * _pricePerGallon;
  }

  double get _discountAmount {
    if (_selectedVoucher == null) return 0;
    final minPurchase =
        (_selectedVoucher!['min_purchase'] as num?)?.toDouble() ?? 0;
    if (_subtotalPrice < minPurchase) return 0;

    final type = _selectedVoucher!['discount_type'] ?? 'nominal';
    final val = (_selectedVoucher!['discount_value'] as num?)?.toDouble() ?? 0;
    if (type == 'persen') {
      return (_subtotalPrice * (val / 100)).clamp(0, _subtotalPrice);
    } else {
      return val.clamp(0, _subtotalPrice);
    }
  }

  double get _totalPrice =>
      (_subtotalPrice - _discountAmount).clamp(0, double.infinity);

  Future<void> _submitOrder() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _isLoading = true);

    final result = await ApiService.createOrder(
      gallonQty: int.parse(_qtyController.text),
      deliveryMethod: _deliveryMethod,
      deliveryAddress:
          _deliveryMethod == 'antar' ? _addressController.text : null,
      paymentMethod: _paymentMethod,
      notes: _notesController.text.isEmpty ? null : _notesController.text,
      voucherId: _selectedVoucher?['id'],
    );

    if (!mounted) return;
    setState(() => _isLoading = false);

    if (result['success'] == true) {
      _showSuccessDialog(result['message'] ?? 'Pesanan berhasil!');
      _formKey.currentState!.reset();
      _qtyController.text = '1';
      _notesController.clear();
      setState(() {
        _selectedVoucher = null;
        _deliveryMethod = 'antar';
        _paymentMethod = 'tunai';
      });
      _loadVouchers();
    } else {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(
        content: Text(result['message'] ?? 'Gagal membuat pesanan'),
        backgroundColor: AppColors.error,
        behavior: SnackBarBehavior.floating,
      ));
    }
  }

  void _showSuccessDialog(String message) {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                color: AppColors.success.withOpacity(0.1),
                shape: BoxShape.circle,
              ),
              child: const Icon(Icons.check_circle_rounded,
                  color: AppColors.success, size: 52),
            ),
            const SizedBox(height: 16),
            Text('Pesanan Berhasil!',
                style: GoogleFonts.inter(
                    fontSize: 18, fontWeight: FontWeight.bold)),
            const SizedBox(height: 8),
            Text(message,
                style: GoogleFonts.inter(
                    color: AppColors.textSecondary, fontSize: 14),
                textAlign: TextAlign.center),
          ],
        ),
        actions: [
          SizedBox(
            width: double.infinity,
            child: ElevatedButton(
              onPressed: () => Navigator.pop(ctx),
              style: ElevatedButton.styleFrom(
                backgroundColor: AppColors.primary,
                shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12)),
              ),
              child: Text('Selesai',
                  style: GoogleFonts.inter(
                      color: Colors.white, fontWeight: FontWeight.bold)),
            ),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final qty = int.tryParse(_qtyController.text) ?? 1;

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0.5,
        title: Text('Pesan Galon',
            style: GoogleFonts.inter(
                fontWeight: FontWeight.bold,
                fontSize: 18,
                color: AppColors.textPrimary)),
        centerTitle: false,
        automaticallyImplyLeading: false,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Hero image / info card
              Container(
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                  gradient: const LinearGradient(
                    colors: [Color(0xFF0284C7), Color(0xFF0EA5E9)],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  ),
                  borderRadius: BorderRadius.circular(18),
                  boxShadow: [
                    BoxShadow(
                      color: AppColors.primary.withOpacity(0.3),
                      blurRadius: 16,
                      offset: const Offset(0, 6),
                    ),
                  ],
                ),
                child: Row(
                  children: [
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text('Isi Ulang Air Galon',
                              style: GoogleFonts.inter(
                                color: Colors.white,
                                fontSize: 18,
                                fontWeight: FontWeight.bold,
                              )),
                          const SizedBox(height: 6),
                          Text(
                            '${currencyFormatter.format(_pricePerGallon)} / galon',
                            style: GoogleFonts.inter(
                                color: Colors.white70, fontSize: 13),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            '+ Poin loyalitas untuk setiap pembelian',
                            style: GoogleFonts.inter(
                                color: Colors.white60, fontSize: 11),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(width: 16),
                    Container(
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(
                        color: Colors.white.withOpacity(0.15),
                        borderRadius: BorderRadius.circular(16),
                      ),
                      child: const Icon(Icons.water_drop_rounded,
                          color: Colors.white, size: 36),
                    ),
                  ],
                ),
              ),

              const SizedBox(height: 24),

              // Jumlah Galon
              _buildSectionTitle('Jumlah Galon'),
              const SizedBox(height: 10),
              Row(
                children: [
                  _buildQtyButton(
                    icon: Icons.remove_rounded,
                    onTap: () {
                      final current = int.tryParse(_qtyController.text) ?? 1;
                      if (current > 1) {
                        setState(() => _qtyController.text =
                            (current - 1).toString());
                      }
                    },
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: TextFormField(
                      controller: _qtyController,
                      keyboardType: TextInputType.number,
                      textAlign: TextAlign.center,
                      style: GoogleFonts.inter(
                          fontSize: 22, fontWeight: FontWeight.bold),
                      onChanged: (_) => setState(() {}),
                      decoration: InputDecoration(
                        border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12)),
                        contentPadding:
                            const EdgeInsets.symmetric(vertical: 14),
                      ),
                      validator: (v) {
                        final n = int.tryParse(v ?? '');
                        if (n == null || n < 1) return 'Min. 1 galon';
                        if (n > 50) return 'Maks. 50 galon';
                        return null;
                      },
                    ),
                  ),
                  const SizedBox(width: 12),
                  _buildQtyButton(
                    icon: Icons.add_rounded,
                    onTap: () {
                      final current = int.tryParse(_qtyController.text) ?? 1;
                      if (current < 50) {
                        setState(() => _qtyController.text =
                            (current + 1).toString());
                      }
                    },
                  ),
                ],
              ),
              const SizedBox(height: 8),
              Center(
                child: Text(
                  'Total: ${currencyFormatter.format(_totalPrice)}',
                  style: GoogleFonts.inter(
                    color: AppColors.primary,
                    fontWeight: FontWeight.bold,
                    fontSize: 16,
                  ),
                ),
              ),

              const SizedBox(height: 24),

              // Metode Pengiriman
              _buildSectionTitle('Metode Pengiriman'),
              const SizedBox(height: 10),
              Row(
                children: [
                  Expanded(
                    child: _buildSelectCard(
                      selected: _deliveryMethod == 'antar',
                      icon: Icons.directions_bike_rounded,
                      title: 'Diantar',
                      subtitle: 'Ke alamat Anda',
                      onTap: () =>
                          setState(() => _deliveryMethod = 'antar'),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: _buildSelectCard(
                      selected: _deliveryMethod == 'ambil_sendiri',
                      icon: Icons.store_rounded,
                      title: 'Ambil Sendiri',
                      subtitle: 'Di depo kami',
                      onTap: () =>
                          setState(() => _deliveryMethod = 'ambil_sendiri'),
                    ),
                  ),
                ],
              ),

              if (_deliveryMethod == 'antar') ...[
                const SizedBox(height: 16),
                _buildSectionTitle('Alamat Pengiriman'),
                const SizedBox(height: 10),
                TextFormField(
                  controller: _addressController,
                  maxLines: 3,
                  decoration: InputDecoration(
                    hintText: 'Masukkan alamat pengiriman lengkap...',
                    hintStyle: GoogleFonts.inter(
                        color: AppColors.textMuted, fontSize: 14),
                    border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12)),
                    prefixIcon: const Padding(
                      padding: EdgeInsets.only(bottom: 36, left: 8, right: 4),
                      child: Icon(Icons.location_on_outlined,
                          color: AppColors.primary),
                    ),
                    filled: true,
                    fillColor: Colors.white,
                  ),
                  validator: (v) =>
                      _deliveryMethod == 'antar' && (v?.isEmpty ?? true)
                          ? 'Alamat pengiriman wajib diisi'
                          : null,
                ),
              ],

              const SizedBox(height: 24),

              // Metode Pembayaran
              _buildSectionTitle('Metode Pembayaran'),
              const SizedBox(height: 10),
              Row(
                children: [
                  Expanded(
                    child: _buildSelectCard(
                      selected: _paymentMethod == 'tunai',
                      icon: Icons.money_rounded,
                      title: 'Tunai',
                      subtitle: 'Bayar tunai',
                      onTap: () =>
                          setState(() => _paymentMethod = 'tunai'),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: _buildSelectCard(
                      selected: _paymentMethod == 'transfer',
                      icon: Icons.account_balance_rounded,
                      title: 'Transfer',
                      subtitle: 'Bank / e-wallet',
                      onTap: () =>
                          setState(() => _paymentMethod = 'transfer'),
                    ),
                  ),
                ],
              ),

              const SizedBox(height: 20),

              // Voucher Diskon
              _buildSectionTitle('Voucher Diskon'),
              const SizedBox(height: 10),
              _buildVoucherSection(),

              const SizedBox(height: 20),

              // Catatan
              _buildSectionTitle('Catatan (Opsional)'),
              const SizedBox(height: 10),
              TextFormField(
                controller: _notesController,
                maxLines: 2,
                decoration: InputDecoration(
                  hintText: 'Tulis catatan untuk driver atau penjual...',
                  hintStyle: GoogleFonts.inter(
                      color: AppColors.textMuted, fontSize: 14),
                  border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12)),
                  filled: true,
                  fillColor: Colors.white,
                ),
              ),

              const SizedBox(height: 24),

              // Order Summary
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: AppColors.primaryLight,
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(color: AppColors.primary.withOpacity(0.2)),
                ),
                child: Column(
                  children: [
                    _buildSummaryRow(
                        'Jumlah Galon', '$qty galon'),
                    const SizedBox(height: 8),
                    _buildSummaryRow('Harga per Galon',
                        currencyFormatter.format(_pricePerGallon)),
                    const SizedBox(height: 8),
                    _buildSummaryRow('Subtotal',
                        currencyFormatter.format(_subtotalPrice)),
                    if (_selectedVoucher != null) ...[
                      const SizedBox(height: 8),
                      _buildSummaryRow(
                        'Diskon Voucher (${_selectedVoucher!['code'] ?? 'Kupon'})',
                        '- ${currencyFormatter.format(_discountAmount)}',
                        valueColor: AppColors.success,
                        isBold: true,
                      ),
                    ],
                    const SizedBox(height: 8),
                    const Divider(),
                    const SizedBox(height: 4),
                    _buildSummaryRow(
                        'Total Pembayaran',
                        currencyFormatter.format(_totalPrice),
                        isBold: true),
                    const SizedBox(height: 4),
                    _buildSummaryRow('+', '$qty poin loyalty',
                        valueColor: AppColors.success),
                  ],
                ),
              ),

              const SizedBox(height: 24),

              // Submit Button
              SizedBox(
                width: double.infinity,
                height: 54,
                child: ElevatedButton(
                  onPressed: _isLoading ? null : _submitOrder,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppColors.primary,
                    shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(14)),
                    elevation: 3,
                  ),
                  child: _isLoading
                      ? const CircularProgressIndicator(
                          color: Colors.white, strokeWidth: 2.5)
                      : Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            const Icon(Icons.shopping_cart_rounded,
                                color: Colors.white),
                            const SizedBox(width: 10),
                            Text('Buat Pesanan',
                                style: GoogleFonts.inter(
                                  fontSize: 16,
                                  fontWeight: FontWeight.bold,
                                  color: Colors.white,
                                )),
                          ],
                        ),
                ),
              ),
              const SizedBox(height: 32),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildSectionTitle(String title) {
    return Text(title,
        style: GoogleFonts.inter(
            fontSize: 14,
            fontWeight: FontWeight.bold,
            color: AppColors.textPrimary));
  }

  Widget _buildQtyButton(
      {required IconData icon, required VoidCallback onTap}) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        width: 48,
        height: 48,
        decoration: BoxDecoration(
          color: AppColors.primary,
          borderRadius: BorderRadius.circular(12),
        ),
        child: Icon(icon, color: Colors.white, size: 24),
      ),
    );
  }

  Widget _buildSelectCard({
    required bool selected,
    required IconData icon,
    required String title,
    required String subtitle,
    required VoidCallback onTap,
  }) {
    return GestureDetector(
      onTap: onTap,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: selected ? AppColors.primaryLight : Colors.white,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(
            color: selected ? AppColors.primary : const Color(0xFFE2E8F0),
            width: selected ? 2 : 1,
          ),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Icon(icon,
                color: selected ? AppColors.primary : AppColors.textSecondary,
                size: 22),
            const SizedBox(height: 6),
            Text(title,
                style: GoogleFonts.inter(
                  fontSize: 13,
                  fontWeight: FontWeight.bold,
                  color: selected ? AppColors.primary : AppColors.textPrimary,
                )),
            Text(subtitle,
                style: GoogleFonts.inter(
                    fontSize: 11, color: AppColors.textSecondary)),
          ],
        ),
      ),
    );
  }

  Widget _buildSummaryRow(String label, String value,
      {bool isBold = false, Color? valueColor}) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Expanded(
          child: Text(
            label,
            style: GoogleFonts.inter(
              fontSize: 13,
              color: AppColors.textSecondary,
              fontWeight: isBold ? FontWeight.bold : FontWeight.normal,
            ),
          ),
        ),
        const SizedBox(width: 10),
        Text(
          value,
          style: GoogleFonts.inter(
            fontSize: 13,
            fontWeight: isBold ? FontWeight.bold : FontWeight.w600,
            color: valueColor ?? (isBold ? AppColors.primary : AppColors.textPrimary),
          ),
        ),
      ],
    );
  }

  Widget _buildVoucherSection() {
    if (_selectedVoucher != null) {
      final code = _selectedVoucher!['code'] ?? 'VOUCHER';
      final name = _selectedVoucher!['name'] ?? 'Diskon Spesial';
      final isPercent = _selectedVoucher!['discount_type'] == 'persen';
      final val = _selectedVoucher!['discount_value'] ?? 0;
      final discountBadge = isPercent ? '$val%' : currencyFormatter.format(val);

      return Container(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
        decoration: BoxDecoration(
          color: const Color(0xFFF0FDF4),
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: const Color(0xFF86EFAC)),
        ),
        child: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: const Color(0xFFDCFCE7),
                borderRadius: BorderRadius.circular(8),
              ),
              child: const Icon(Icons.confirmation_num_rounded,
                  color: Color(0xFF16A34A), size: 22),
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
                          code,
                          style: GoogleFonts.inter(
                              fontSize: 13,
                              fontWeight: FontWeight.bold,
                              color: const Color(0xFF15803D)),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                      const SizedBox(width: 6),
                      Container(
                        padding: const EdgeInsets.symmetric(
                            horizontal: 6, vertical: 2),
                        decoration: BoxDecoration(
                          color: const Color(0xFFBBF7D0),
                          borderRadius: BorderRadius.circular(4),
                        ),
                        child: Text('Hemat $discountBadge',
                            style: GoogleFonts.inter(
                                fontSize: 10,
                                fontWeight: FontWeight.bold,
                                color: const Color(0xFF14532D))),
                      ),
                    ],
                  ),
                  const SizedBox(height: 2),
                  Text(name,
                      style: GoogleFonts.inter(
                          fontSize: 12, color: AppColors.textSecondary),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis),
                ],
              ),
            ),
            IconButton(
              icon: const Icon(Icons.close_rounded,
                  color: AppColors.textMuted, size: 20),
              onPressed: () => setState(() => _selectedVoucher = null),
              tooltip: 'Hapus Voucher',
            ),
          ],
        ),
      );
    }

    final count = _vouchers.length;

    return InkWell(
      onTap: _openVoucherPicker,
      borderRadius: BorderRadius.circular(12),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: const Color(0xFFE2E8F0)),
        ),
        child: Row(
          children: [
            const Icon(Icons.confirmation_num_outlined,
                color: AppColors.primary, size: 22),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('Gunakan Voucher Diskon',
                      style: GoogleFonts.inter(
                          fontSize: 13,
                          fontWeight: FontWeight.w600,
                          color: AppColors.textPrimary)),
                  Text(
                    _loadingVouchers
                        ? 'Memeriksa voucher...'
                        : (count > 0
                            ? '$count voucher tersedia untuk Anda'
                            : 'Belum ada voucher aktif'),
                    style: GoogleFonts.inter(
                        fontSize: 11,
                        color: count > 0
                            ? AppColors.primary
                            : AppColors.textMuted),
                  ),
                ],
              ),
            ),
            const Icon(Icons.chevron_right_rounded,
                color: AppColors.textMuted, size: 20),
          ],
        ),
      ),
    );
  }

  void _openVoucherPicker() {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (ctx) {
        return StatefulBuilder(
          builder: (context, setModalState) {
            return SafeArea(
              child: Container(
                padding: const EdgeInsets.fromLTRB(20, 16, 20, 20),
                constraints: BoxConstraints(
                  maxHeight: MediaQuery.of(context).size.height * 0.7,
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
                          color: Colors.grey.shade300,
                          borderRadius: BorderRadius.circular(2),
                        ),
                      ),
                    ),
                    const SizedBox(height: 16),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text('Pilih Voucher',
                            style: GoogleFonts.inter(
                                fontSize: 16, fontWeight: FontWeight.bold)),
                        if (_selectedVoucher != null)
                          TextButton(
                            onPressed: () {
                              setState(() => _selectedVoucher = null);
                              Navigator.pop(ctx);
                            },
                            child: Text('Reset',
                                style: GoogleFonts.inter(
                                    color: AppColors.error,
                                    fontSize: 13,
                                    fontWeight: FontWeight.bold)),
                          ),
                      ],
                    ),
                    const SizedBox(height: 12),
                    if (_vouchers.isEmpty)
                      Padding(
                        padding: const EdgeInsets.symmetric(vertical: 36),
                        child: Center(
                          child: Column(
                            children: [
                              Icon(Icons.confirmation_num_outlined,
                                  size: 48, color: Colors.grey.shade300),
                              const SizedBox(height: 12),
                              Text('Belum ada voucher aktif',
                                  style: GoogleFonts.inter(
                                      color: AppColors.textSecondary,
                                      fontSize: 14)),
                            ],
                          ),
                        ),
                      )
                    else
                      Flexible(
                        child: ListView.separated(
                          shrinkWrap: true,
                          itemCount: _vouchers.length,
                          separatorBuilder: (_, __) =>
                              const SizedBox(height: 10),
                          itemBuilder: (context, idx) {
                            final v = _vouchers[idx];
                            final isSelected =
                                _selectedVoucher?['id'] == v['id'];
                            final isPercent = v['discount_type'] == 'persen';
                            final val = v['discount_value'] ?? 0;
                            final badge = isPercent
                                ? 'Diskon $val%'
                                : 'Potongan ${currencyFormatter.format(val)}';
                            final minBuy =
                                (v['min_purchase'] as num?)?.toDouble() ?? 0;
                            final eligible = _subtotalPrice >= minBuy;

                            return InkWell(
                              onTap: eligible
                                  ? () {
                                      setState(() => _selectedVoucher = v);
                                      Navigator.pop(ctx);
                                    }
                                  : null,
                              borderRadius: BorderRadius.circular(12),
                              child: Container(
                                padding: const EdgeInsets.all(14),
                                decoration: BoxDecoration(
                                  color: isSelected
                                      ? AppColors.primaryLight
                                      : (eligible
                                          ? Colors.white
                                          : Colors.grey.shade50),
                                  borderRadius: BorderRadius.circular(12),
                                  border: Border.all(
                                    color: isSelected
                                        ? AppColors.primary
                                        : (eligible
                                            ? const Color(0xFFE2E8F0)
                                            : Colors.grey.shade200),
                                    width: isSelected ? 2 : 1,
                                  ),
                                ),
                                child: Row(
                                  children: [
                                    Container(
                                      padding: const EdgeInsets.all(10),
                                      decoration: BoxDecoration(
                                        color: eligible
                                            ? AppColors.primary.withOpacity(0.1)
                                            : Colors.grey.shade200,
                                        shape: BoxShape.circle,
                                      ),
                                      child: Icon(Icons.confirmation_num_rounded,
                                          color: eligible
                                              ? AppColors.primary
                                              : Colors.grey,
                                          size: 20),
                                    ),
                                    const SizedBox(width: 12),
                                    Expanded(
                                      child: Column(
                                        crossAxisAlignment:
                                            CrossAxisAlignment.start,
                                        children: [
                                          Row(
                                            children: [
                                              Text(v['code'] ?? '',
                                                  style: GoogleFonts.inter(
                                                      fontWeight: FontWeight.bold,
                                                      fontSize: 13,
                                                      color: eligible
                                                          ? AppColors.textPrimary
                                                          : Colors.grey)),
                                              const SizedBox(width: 6),
                                              Container(
                                                padding: const EdgeInsets
                                                    .symmetric(
                                                    horizontal: 6,
                                                    vertical: 2),
                                                decoration: BoxDecoration(
                                                  color: eligible
                                                      ? const Color(0xFFDCFCE7)
                                                      : Colors.grey.shade200,
                                                  borderRadius:
                                                      BorderRadius.circular(4),
                                                ),
                                                child: Text(badge,
                                                    style: GoogleFonts.inter(
                                                        fontSize: 10,
                                                        fontWeight:
                                                            FontWeight.bold,
                                                        color: eligible
                                                            ? const Color(
                                                                0xFF166534)
                                                            : Colors.grey)),
                                              ),
                                            ],
                                          ),
                                          const SizedBox(height: 4),
                                          Text(v['name'] ?? '',
                                              style: GoogleFonts.inter(
                                                  fontSize: 12,
                                                  color: eligible
                                                      ? AppColors.textSecondary
                                                      : Colors.grey)),
                                          if (minBuy > 0) ...[
                                            const SizedBox(height: 2),
                                            Text(
                                              'Min. belanja ${currencyFormatter.format(minBuy)}${!eligible ? ' (Belum memenuhi)' : ''}',
                                              style: GoogleFonts.inter(
                                                  fontSize: 11,
                                                  color: !eligible
                                                      ? AppColors.error
                                                      : AppColors.textMuted),
                                            ),
                                          ],
                                        ],
                                      ),
                                    ),
                                    if (isSelected)
                                      const Icon(Icons.check_circle_rounded,
                                          color: AppColors.primary, size: 22),
                                  ],
                                ),
                              ),
                            );
                          },
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
}
