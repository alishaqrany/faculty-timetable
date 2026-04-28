import 'package:flutter/material.dart';
import 'package:mobile_app/features/auth/auth_service.dart';

class LoginPage extends StatefulWidget {
  const LoginPage({super.key, required this.onLoginSuccess});

  final Widget Function(AuthService authService) onLoginSuccess;

  @override
  State<LoginPage> createState() => _LoginPageState();
}

class _LoginPageState extends State<LoginPage> {
  final _auth = AuthService();
  final _userCtrl = TextEditingController();
  final _passCtrl = TextEditingController();
  bool _loading = false;
  String? _error;

  @override
  void initState() {
    super.initState();
    _tryRestore();
  }

  Future<void> _tryRestore() async {
    final token = await _auth.restoreToken();
    if (!mounted || token == null || token.isEmpty) return;
    Navigator.of(context).pushReplacement(
      MaterialPageRoute(builder: (_) => widget.onLoginSuccess(_auth)),
    );
  }

  Future<void> _login() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      await _auth.login(username: _userCtrl.text.trim(), password: _passCtrl.text);
      if (!mounted) return;
      Navigator.of(context).pushReplacement(
        MaterialPageRoute(builder: (_) => widget.onLoginSuccess(_auth)),
      );
    } catch (e) {
      setState(() => _error = e.toString());
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('تسجيل الدخول')),
      body: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            TextField(
              controller: _userCtrl,
              decoration: const InputDecoration(labelText: 'اسم المستخدم'),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: _passCtrl,
              decoration: const InputDecoration(labelText: 'كلمة المرور'),
              obscureText: true,
            ),
            const SizedBox(height: 16),
            if (_error != null)
              Text(_error!, style: const TextStyle(color: Colors.red)),
            const SizedBox(height: 8),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: _loading ? null : _login,
                child: _loading
                    ? const CircularProgressIndicator()
                    : const Text('دخول'),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
