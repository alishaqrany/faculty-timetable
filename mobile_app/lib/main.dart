import 'package:flutter/material.dart';
import 'package:mobile_app/features/auth/login_page.dart';
import 'package:mobile_app/features/home/home_page.dart';

void main() {
  runApp(const TimetableApp());
}

class TimetableApp extends StatelessWidget {
  const TimetableApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Timetable Mobile',
      theme: ThemeData(
        colorScheme: ColorScheme.fromSeed(seedColor: Colors.indigo),
      ),
      locale: const Locale('ar'),
      home: const AppEntryPoint(),
    );
  }
}

class AppEntryPoint extends StatelessWidget {
  const AppEntryPoint({super.key});

  @override
  Widget build(BuildContext context) {
    return const LoginPage(onLoginSuccess: HomePage.new);
  }
}
