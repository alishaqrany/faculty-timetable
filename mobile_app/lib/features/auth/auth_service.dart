import 'package:mobile_app/core/api_client.dart';
import 'package:mobile_app/core/app_config.dart';
import 'package:mobile_app/core/session_store.dart';

class AuthService {
  AuthService({
    ApiClient? client,
    SessionStore? store,
  })  : _client = client ?? ApiClient(baseUrl: AppConfig.apiBaseUrl),
        _store = store ?? SessionStore();

  final ApiClient _client;
  final SessionStore _store;

  Future<void> login({required String username, required String password}) async {
    final res = await _client.post('/api/v1/auth/login', body: {
      'username': username,
      'password': password,
    });
    final data = res['data'] as Map<String, dynamic>;
    final token = data['token']?.toString() ?? '';
    if (token.isEmpty) {
      throw ApiException(statusCode: 500, message: 'Token missing');
    }
    _client.token = token;
    await _store.saveSession(token: token, username: username);
  }

  Future<String?> restoreToken() async {
    final token = await _store.readToken();
    _client.token = token;
    return token;
  }

  Future<void> logout() async {
    final token = await _store.readToken();
    if (token != null && token.isNotEmpty) {
      _client.token = token;
      try {
        await _client.post('/api/v1/auth/logout');
      } catch (_) {}
    }
    await _store.clear();
  }

  ApiClient get client => _client;
}
