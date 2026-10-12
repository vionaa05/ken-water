import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'constants/app_colors.dart';
import 'services/storage_service.dart';
import 'screens/login_screen.dart';
import 'screens/customer/customer_home_screen.dart';
import 'screens/admin/admin_dashboard_screen.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  await StorageService.init();
  runApp(const KenWaterApp());
}

class KenWaterApp extends StatelessWidget {
  const KenWaterApp({super.key});

  @override
  Widget build(BuildContext context) {
    Widget initialScreen = const LoginScreen();

    if (StorageService.isLoggedIn()) {
      final user = StorageService.getUser();
      if (user != null) {
        if (user.isAdmin) {
          initialScreen = const AdminDashboardScreen();
        } else {
          initialScreen = const CustomerHomeScreen();
        }
      }
    }

    return MaterialApp(
      title: 'Ken Water Mobile',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        useMaterial3: true,
        colorScheme: ColorScheme.fromSeed(
          seedColor: AppColors.primary,
          primary: AppColors.primary,
          surface: AppColors.surface,
        ),
        scaffoldBackgroundColor: AppColors.background,
        textTheme: GoogleFonts.interTextTheme(Theme.of(context).textTheme),
        appBarTheme: const AppBarTheme(
          backgroundColor: Colors.white,
          foregroundColor: AppColors.textPrimary,
          elevation: 0.5,
          centerTitle: false,
        ),
      ),
      home: initialScreen,
    );
  }
}
