import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

import '../l10n/app_strings.dart';
import '../services/api_config.dart';

const supportEmail = 'ajmalaand@gmail.com';
const _external = MethodChannel('services.shelf.app/external');

Future<bool> openShelfExternal(Uri uri) async {
  try {
    return await _external.invokeMethod<bool>('open', uri.toString()) ?? false;
  } on PlatformException {
    return false;
  } on MissingPluginException {
    return false;
  }
}

class PrivacySupportEntries extends StatelessWidget {
  const PrivacySupportEntries({super.key});

  Future<void> _showAddress(
    BuildContext context,
    String address, {
    required bool email,
    required bool failed,
  }) async {
    final s = AppStrings.of(context);
    await showDialog<void>(
      context: context,
      builder: (dialogContext) => AlertDialog(
        title: Text(email ? s.support : s.privacyPolicy),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            if (failed) Text(email ? s.emailUnavailable : s.browserUnavailable),
            const SizedBox(height: 12),
            SelectableText(address, textDirection: TextDirection.ltr),
          ],
        ),
        actions: [
          TextButton.icon(
            key: const Key('copy-contact-address'),
            icon: const Icon(Icons.copy),
            label: Text(s.copyAddress),
            onPressed: () async {
              await Clipboard.setData(ClipboardData(text: address));
              if (dialogContext.mounted) {
                ScaffoldMessenger.of(context)
                    .showSnackBar(SnackBar(content: Text(s.addressCopied)));
              }
            },
          ),
          if (email && !failed)
            TextButton(
              key: const Key('open-support-email'),
              child: Text(s.openEmail),
              onPressed: () async {
                final opened = await openShelfExternal(
                  Uri(scheme: 'mailto', path: supportEmail),
                );
                if (!opened && dialogContext.mounted) {
                  Navigator.pop(dialogContext);
                  if (context.mounted) {
                    await _showAddress(
                      context,
                      address,
                      email: true,
                      failed: true,
                    );
                  }
                }
              },
            ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final s = AppStrings.of(context);
    final privacy = apiBaseUri().resolve('/privacy');
    return Column(
      children: [
        ListTile(
          key: const Key('settings-privacy'),
          leading: const Icon(Icons.privacy_tip_outlined),
          title: Text(s.privacyPolicy),
          trailing: const Icon(Icons.open_in_new),
          onTap: () async {
            if (!await openShelfExternal(privacy) && context.mounted) {
              await _showAddress(
                context,
                privacy.toString(),
                email: false,
                failed: true,
              );
            }
          },
        ),
        ListTile(
          key: const Key('settings-support'),
          leading: const Icon(Icons.help_outline),
          title: Text(s.support),
          subtitle: const Text(supportEmail, textDirection: TextDirection.ltr),
          onTap: () =>
              _showAddress(context, supportEmail, email: true, failed: false),
        ),
      ],
    );
  }
}
