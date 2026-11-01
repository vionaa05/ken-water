import 'dart:async';
import 'package:flutter/material.dart';
import '../../constants/app_colors.dart';
import '../../services/api_service.dart';
import 'tabs/admin_home_tab.dart';
import 'tabs/admin_orders_tab.dart';
import 'tabs/admin_customers_tab.dart';
import 'tabs/admin_complaints_tab.dart';
import 'tabs/admin_redemptions_tab.dart';

class AdminMainScreen extends StatefulWidget {
  const AdminMainScreen({super.key});

  @override
  State<AdminMainScreen> createState() => AdminMainScreenState();
}

class AdminMainScreenState extends State<AdminMainScreen> {
  int _currentIndex = 0;
  int _pendingOrdersCount = 0;
  int _pendingComplaintsCount = 0;
  int _pendingRedemptionsCount = 0;
  Timer? _pollingTimer;

  void switchTab(int index, {String? customerFilter}) {
    if (mounted) {
      setState(() => _currentIndex = index);
      if (index == 1) {
        AdminOrdersTab.globalKey.currentState?.reloadOrders();
      } else if (index == 2 && customerFilter != null) {
        AdminCustomersTab.globalKey.currentState?.setFilter(customerFilter);
      }
    }
  }

  late final List<Widget> _screens;

  @override
  void initState() {
    super.initState();
    _screens = [
      const AdminHomeTab(),
      AdminOrdersTab(key: AdminOrdersTab.globalKey),
      AdminCustomersTab(key: AdminCustomersTab.globalKey),
      const AdminComplaintsTab(),
      const AdminRedemptionsTab(),
    ];
    _fetchBadges();
    _pollingTimer = Timer.periodic(const Duration(seconds: 8), (_) {
      if (mounted) _fetchBadges();
    });
  }

  @override
  void dispose() {
    _pollingTimer?.cancel();
    super.dispose();
  }

  Future<void> _fetchBadges() async {
    final res = await ApiService.getAdminDashboard();
    if (mounted && res['success'] == true && res['summary'] != null) {
      final summary = res['summary'] as Map<String, dynamic>;
      setState(() {
        _pendingOrdersCount = (summary['pending_orders_count'] ?? summary['active_orders_count'] ?? 0) as int;
        _pendingComplaintsCount = (summary['pending_complaints_count'] ?? 0) as int;
        _pendingRedemptionsCount = (summary['pending_redemptions_count'] ?? 0) as int;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      body: IndexedStack(
        index: _currentIndex,
        children: _screens,
      ),
      bottomNavigationBar: Container(
        decoration: const BoxDecoration(
          color: Colors.white,
          border: Border(
            top: BorderSide(color: Color(0xFFE2E8F0), width: 1),
          ),
          boxShadow: [
            BoxShadow(
              color: Color(0x0A000000),
              blurRadius: 16,
              offset: Offset(0, -4),
            ),
          ],
        ),
        child: SafeArea(
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 8),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceAround,
              children: [
                _buildNavItem(0, Icons.dashboard_rounded, Icons.dashboard_outlined, 'Dashboard'),
                _buildNavItem(1, Icons.receipt_long_rounded, Icons.receipt_long_outlined, 'Pesanan', badgeCount: _pendingOrdersCount),
                _buildNavItem(2, Icons.people_rounded, Icons.people_outline_rounded, 'Pelanggan'),
                _buildNavItem(3, Icons.headset_mic_rounded, Icons.headset_mic_outlined, 'Keluhan', badgeCount: _pendingComplaintsCount),
                _buildNavItem(4, Icons.card_giftcard_rounded, Icons.card_giftcard_outlined, 'Reward', badgeCount: _pendingRedemptionsCount),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildNavItem(
      int index, IconData activeIcon, IconData inactiveIcon, String label,
      {int badgeCount = 0}) {
    final isActive = _currentIndex == index;
    return GestureDetector(
      onTap: () {
        setState(() => _currentIndex = index);
        if (index == 1) {
          AdminOrdersTab.globalKey.currentState?.reloadOrders();
        }
      },
      behavior: HitTestBehavior.opaque,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
        decoration: BoxDecoration(
          color: isActive
              ? AppColors.primary.withOpacity(0.12)
              : Colors.transparent,
          borderRadius: BorderRadius.circular(12),
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Stack(
              clipBehavior: Clip.none,
              children: [
                Icon(
                  isActive ? activeIcon : inactiveIcon,
                  color: isActive ? AppColors.primary : AppColors.textSecondary,
                  size: 22,
                ),
                if (badgeCount > 0)
                  Positioned(
                    right: -8,
                    top: -6,
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 1.5),
                      decoration: BoxDecoration(
                        color: AppColors.error,
                        borderRadius: BorderRadius.circular(10),
                        border: Border.all(color: Colors.white, width: 1.5),
                      ),
                      constraints: const BoxConstraints(
                        minWidth: 16,
                        minHeight: 16,
                      ),
                      child: Text(
                        badgeCount > 99 ? '99+' : '$badgeCount',
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
            const SizedBox(height: 3),
            Text(
              label,
              style: TextStyle(
                fontSize: 10,
                fontWeight: isActive ? FontWeight.w600 : FontWeight.normal,
                color: isActive ? AppColors.primary : AppColors.textSecondary,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
