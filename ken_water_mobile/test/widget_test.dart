import 'package:flutter_test/flutter_test.dart';
import 'package:ken_water_mobile/main.dart';

void main() {
  testWidgets('App smoke test', (WidgetTester tester) async {
    await tester.pumpWidget(const KenWaterApp());
    expect(find.byType(KenWaterApp), findsOneWidget);
  });
}
