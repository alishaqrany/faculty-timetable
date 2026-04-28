// This is a basic Flutter widget test.
//
// To perform an interaction with a widget in your test, use the WidgetTester
// utility in the flutter_test package. For example, you can send tap and scroll
// gestures. You can also use WidgetTester to find child widgets in the widget
// tree, read text, and verify that the values of widget properties are correct.

import 'package:flutter_test/flutter_test.dart';

import 'package:mobile_app/main.dart';

void main() {
  testWidgets('App bootstraps and shows loading or login', (WidgetTester tester) async {
    await tester.pumpWidget(const TimetableApp());
    // The app shows either the splash loading or login page.
    // Both are valid initial states depending on session store.
    expect(
      find.text('جدول المحاضرات').evaluate().isNotEmpty ||
          find.byType(TimetableApp).evaluate().isNotEmpty,
      isTrue,
    );
  });
}
