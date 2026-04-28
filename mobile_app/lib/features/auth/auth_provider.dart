import 'package:flutter/foundation.dart';
import 'package:mobile_app/core/api_client.dart';
import 'package:mobile_app/core/app_config.dart';
import 'package:mobile_app/core/cache_manager.dart';
import 'package:mobile_app/core/session_store.dart';

/// Centralised authentication state exposed via Provider.
class AuthProvider extends ChangeNotifier {
  AuthProvider({
    ApiClient? client,
    SessionStore? store,
  })  : _client = client ?? ApiClient(baseUrl: AppConfig.apiBaseUrl),
        _store = store ?? SessionStore();

  final ApiClient _client;
  final SessionStore _store;

  // ── Observable state ─────────────────────────────────────────────
  bool _isLoading = false;
  bool get isLoading => _isLoading;

  String? _error;
  String? get error => _error;

  bool _isLoggedIn = false;
  bool get isLoggedIn => _isLoggedIn;

  Map<String, dynamic>? _currentUser;
  Map<String, dynamic>? get currentUser => _currentUser;

  /// Expose the client for API classes that need the token.
  ApiClient get client => _client;

  // ── Login ────────────────────────────────────────────────────────
  Future<bool> login({required String username, required String password}) async {
    _setLoading(true);
    _error = null;
    try {
      final res = await _client.post('/api/v1/auth/login', body: {
        'username': username,
        'password': password,
      });
      final data = res['data'] as Map<String, dynamic>;
      final token = data['token']?.toString() ?? '';
      if (token.isEmpty) {
        _error = 'لم يتم استلام رمز المصادقة';
        return false;
      }
      _client.token = token;
      _currentUser = data['user'] as Map<String, dynamic>?;
      await _store.saveSession(token: token, username: username);
      _isLoggedIn = true;
      return true;
    } on ApiException catch (e) {
      _error = e.message;
      return false;
    } catch (e) {
      _error = 'تعذر الاتصال بالخادم';
      return false;
    } finally {
      _setLoading(false);
    }
  }

  // ── Restore Session ──────────────────────────────────────────────
  Future<bool> tryRestore() async {
    final token = await _store.readToken();
    if (token == null || token.isEmpty) return false;
    _client.token = token;
    _isLoggedIn = true;
    notifyListeners();
    return true;
  }

  // ── Logout ───────────────────────────────────────────────────────
  Future<void> logout() async {
    final token = await _store.readToken();
    if (token != null && token.isNotEmpty) {
      _client.token = token;
      try {
        await _client.post('/api/v1/auth/logout');
      } catch (_) {}
    }
    await _store.clear();
    await CacheManager.instance.clearAll();
    _isLoggedIn = false;
    _currentUser = null;
    _client.token = null;
    _error = null;
    notifyListeners();
  }

  // ── Helper ───────────────────────────────────────────────────────
  void _setLoading(bool value) {
    _isLoading = value;
    notifyListeners();
  }
}
