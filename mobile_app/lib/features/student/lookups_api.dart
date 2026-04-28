import 'package:mobile_app/core/api_client.dart';

class LookupsApi {
  const LookupsApi(this.client);

  final ApiClient client;

  Future<List<dynamic>> departments() async => _list('/api/v1/lookups/departments');
  Future<List<dynamic>> levels() async => _list('/api/v1/lookups/levels');
  Future<List<dynamic>> sections() async => _list('/api/v1/lookups/sections');

  Future<List<dynamic>> _list(String path) async {
    final res = await client.get(path);
    return (res['data'] as List<dynamic>? ?? <dynamic>[]);
  }
}
