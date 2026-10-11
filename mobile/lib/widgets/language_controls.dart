import 'package:flutter/material.dart';

import '../l10n/app_strings.dart';
import '../settings/interface_language.dart';

Future<void> chooseInterfaceLanguage(
  BuildContext context,
  InterfaceLanguageSettings settings,
  InterfaceLanguage language,
) async {
  try {
    await settings.select(language);
  } catch (_) {
    if (context.mounted)
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(AppStrings.of(context).languageSaveError)),
      );
  }
}

class LanguageButton extends StatelessWidget {
  const LanguageButton({super.key});
  @override
  Widget build(BuildContext context) {
    final settings = InterfaceLanguageScope.of(context);
    return PopupMenuButton<InterfaceLanguage>(
      enabled: settings != null && !settings.saving,
      tooltip: AppStrings.of(context).interfaceLanguage,
      onSelected: (language) =>
          chooseInterfaceLanguage(context, settings!, language),
      itemBuilder: (_) => const [
        PopupMenuItem(value: InterfaceLanguage.en, child: Text('English')),
        PopupMenuItem(value: InterfaceLanguage.ps, child: Text('پښتو')),
        PopupMenuItem(value: InterfaceLanguage.dari, child: Text('دری')),
      ],
      child: SizedBox(
        height: 48,
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 12),
          child: Directionality(
            textDirection: TextDirection.ltr,
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                Text((settings?.language ?? InterfaceLanguage.ps).label),
                const Icon(Icons.arrow_drop_down, size: 20),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

class LanguageChoiceScreen extends StatelessWidget {
  const LanguageChoiceScreen({required this.settings, super.key});
  final InterfaceLanguageSettings settings;
  @override
  Widget build(BuildContext context) => Scaffold(
    body: SafeArea(
      child: Center(
        child: SingleChildScrollView(
          child: Padding(
            padding: const EdgeInsets.all(28),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Text(
                  AppStrings.appName,
                  style: Theme.of(context).textTheme.headlineLarge,
                ),
                const SizedBox(height: 24),
                const Text(
                  AppStrings.chooseLanguage,
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: 24),
                FilledButton(
                  key: const Key('choose-pashto'),
                  onPressed: settings.saving
                      ? null
                      : () => chooseInterfaceLanguage(
                          context,
                          settings,
                          InterfaceLanguage.ps,
                        ),
                  child: const Text(AppStrings.pashto),
                ),
                const SizedBox(height: 12),
                OutlinedButton(
                  key: const Key('choose-dari'),
                  onPressed: settings.saving
                      ? null
                      : () => chooseInterfaceLanguage(
                          context,
                          settings,
                          InterfaceLanguage.dari,
                        ),
                  child: const Text('دری'),
                ),
                const SizedBox(height: 12),
                OutlinedButton(
                  key: const Key('choose-english'),
                  onPressed: settings.saving
                      ? null
                      : () => chooseInterfaceLanguage(
                          context,
                          settings,
                          InterfaceLanguage.en,
                        ),
                  child: const Text(AppStrings.english),
                ),
              ],
            ),
          ),
        ),
      ),
    ),
  );
}
