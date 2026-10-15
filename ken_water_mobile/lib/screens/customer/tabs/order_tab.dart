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

  final currencyFormatter =
      NumberFormat.currency(locale: 'id_ID', symbol: 'Rp ', decimalDigits: 0);

  @override
  void initState() {
    super.initState();
    final user = StorageService.getUser();
    if (user?.address != null) {
      _addressController.text = user!.address!;
    }
  }

  @override
  void dispose() {
    _qtyController.dispose();
    _addressController.dispose();
    _notesController.dispose();
    super.dispose();
  }

  double get _totalPrice =>
      (int.tryParse(_qtyController.text) ?? 1) * _pricePerGallon;

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
    );

    if (!mounted) return;
    setState(() => _isLoading = false);

    if (result['success'] == true) {
      _showSuccessDialog(result['message'] ?? 'Pesanan berhasil!');
      _formKey.currentState!.reset();
      _qtyController.text = '1';
      _notesController.clear();
      setState(() {
        _deliveryMethod = 'antar';
        _paymentMethod = 'tunai';
      });
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
      children: [
        Text(label,
            style: GoogleFonts.inter(
              fontSize: 13,
              color: AppColors.textSecondary,
              fontWeight: isBold ? FontWeight.bold : FontWeight.normal,
            )),
        Text(value,
            style: GoogleFonts.inter(
              fontSize: 13,
              fontWeight: isBold ? FontWeight.bold : FontWeight.w600,
              color: valueColor ?? (isBold ? AppColors.primary : AppColors.textPrimary),
            )),
      ],
    );
  }
}
