import 'package:flutter/material.dart';

import '../l10n/app_strings.dart';
import 'shelf_assets.dart';

class UntitledPoemMarker extends StatelessWidget {
  const UntitledPoemMarker({required this.color, super.key});
  final Color color;

  @override
  Widget build(BuildContext context) => Semantics(
    container: true,
    label: AppStrings.of(context)
        .choose('Untitled poem', 'بې سرلیکه شعر', 'شعر بدون عنوان'),
    image: true,
    child: ExcludeSemantics(
      child: ShelfActionIcon(
        'poetry',
        key: const Key('untitled-poem-indicator'),
        size: 34,
        dark: color.computeLuminance() > .5,
      ),
    ),
  );
}
