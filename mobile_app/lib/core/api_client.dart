import 'dart:convert';

import 'package:http/http.dart' as http;

class ApiClient {
  ApiClient({
    required this.baseUrl,
    this.token,
  });

  final String baseUrl;
  String? token;

  Future<Map<String, dynamic>> get(String path, {Map<String, String>? query}) async {
    final uri = Uri.parse('$baseUrl$path').replace(queryParameters: query);
    final response = await http.get(uri, headers: _headers());
    return _decode(response);
  }

  Future<Map<String, dynamic>> post(String path, {Map<String, dynamic>? body}) async {
    final uri = Uri.parse('$baseUrl$path');
    final response = await http.post(
      uri,
      headers: _headers(includeJsonContentType: false),
      body: (body ?? <String, dynamic>{})
          .map((key, value) => MapEntry(key, value?.toString() ?? '')),
    );
    return _decode(response);
  }

  Map<String, String> _headers({bool includeJsonContentType = true}) {
    final headers = <String, String>{
      'Accept': 'application/json',
    };
    if (includeJsonContentType) {
      headers['Content-Type'] = 'application/json';
    }
    if (token != null && token!.isNotEmpty) {
      headers['Authorization'] = 'Bearer $token';
    }
    return headers;
  }

  Map<String, dynamic> _decode(http.Response response) {
    final decoded = jsonDecode(response.body) as Map<String, dynamic>;
    if (response.statusCode >= 400) {
      throw ApiException(
        statusCode: response.statusCode,
        message: decoded['message']?.toString() ?? 'Request failed',
      );
    }
    return decoded;
  }
}

class ApiException implements Exception {
  ApiException({required this.statusCode, required this.message});

  final int statusCode;
  final String message;

  @override
  String toString() => 'ApiException($statusCode): $message';
}
