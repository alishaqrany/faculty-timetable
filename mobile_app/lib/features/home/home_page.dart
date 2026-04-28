import 'package:flutter/material.dart';
import 'package:mobile_app/features/auth/auth_service.dart';
import 'package:mobile_app/features/auth/login_page.dart';
import 'package:mobile_app/features/student/section_timetable_page.dart';
import 'package:mobile_app/features/student/today_lectures_page.dart';
import 'package:mobile_app/features/student/year_timetable_page.dart';

class HomePage extends StatelessWidget {
  const HomePage(this.authService, {super.key});

  final AuthService authService;

  Future<void> _logout(BuildContext context) async {
    await authService.logout();
    if (!context.mounted) return;
    Navigator.of(context).pushAndRemoveUntil(
      MaterialPageRoute(builder: (_) => const LoginPage(onLoginSuccess: HomePage.new)),
      (_) => false,
    );
  }

  @override
  Widget build(BuildContext context) {
    return DefaultTabController(
      length: 3,
      child: Scaffold(
        appBar: AppBar(
          title: const Text('Timetable Mobile'),
          actions: [
            IconButton(
              onPressed: () => _logout(context),
              icon: const Icon(Icons.logout),
            ),
          ],
          bottom: const TabBar(
            tabs: [
              Tab(text: 'جدول الفرقة'),
              Tab(text: 'جدول السكشن'),
              Tab(text: 'محاضرات اليوم'),
            ],
          ),
        ),
        body: TabBarView(
          children: [
            YearTimetablePage(authService: authService),
            SectionTimetablePage(authService: authService),
            TodayLecturesPage(authService: authService),
          ],
        ),
      ),
    );
  }
}
