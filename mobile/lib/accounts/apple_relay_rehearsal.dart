import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

class RelayRehearsalStatus {
  const RelayRehearsalStatus({this.available = false, this.saved = false});
  final bool available, saved;
}

abstract interface class RelayRehearsalProvider {
  Future<RelayRehearsalStatus> status();
  Future<bool> prepare();
  Future<bool> copy();
  Future<bool> forget();
}

class NativeRelayRehearsalProvider implements RelayRehearsalProvider {
  const NativeRelayRehearsalProvider();
  static const _channel = MethodChannel('services.shelf.app/identity');
  @override
  Future<RelayRehearsalStatus> status() async {
    if (defaultTargetPlatform != TargetPlatform.iOS) {
      return const RelayRehearsalStatus();
    }
    try {
      final value = await _channel.invokeMapMethod<String, dynamic>(
        'relayRehearsalStatus',
      );
      return RelayRehearsalStatus(
        available: value?['available'] == true,
        saved: value?['saved'] == true,
      );
    } on PlatformException {
      return const RelayRehearsalStatus();
    } on MissingPluginException {
      return const RelayRehearsalStatus();
    }
  }

  @override
  Future<bool> prepare() async =>
      await _channel.invokeMethod<bool>('prepareRelayAddress') == true;
  @override
  Future<bool> copy() async =>
      await _channel.invokeMethod<bool>('copyRelayAddress') == true;
  @override
  Future<bool> forget() async =>
      await _channel.invokeMethod<bool>('forgetRelayAddress') == true;
}

/// No AccountService, session store or network dependency: address preparation only.
class AppleRelayRehearsal extends StatefulWidget {
  const AppleRelayRehearsal({
    super.key,
    this.provider = const NativeRelayRehearsalProvider(),
  });
  final RelayRehearsalProvider provider;
  @override
  State<AppleRelayRehearsal> createState() => _AppleRelayRehearsalState();
}

class _AppleRelayRehearsalState extends State<AppleRelayRehearsal> {
  RelayRehearsalStatus _status = const RelayRehearsalStatus();
  bool _busy = false;
  String? _message;
  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    try {
      final status = await widget.provider.status();
      if (mounted) setState(() => _status = status);
    } catch (_) {
      // Unavailable outside the specific native internal build; fail closed.
    }
  }

  Future<void> _perform(String action) async {
    if (_busy) return;
    setState(() {
      _busy = true;
      _message = null;
    });
    try {
      final success = switch (action) {
        'copy' => await widget.provider.copy(),
        'forget' => await widget.provider.forget(),
        _ => await widget.provider.prepare(),
      };
      if (mounted) {
        setState(() {
          _message = success
              ? switch (action) {
                  'copy' => 'Copied for one minute on this iPhone only. Paste into your private password manager.',
                  'forget' => 'Saved address removed from this iPhone. Apple authorization is unchanged.',
                  _ => 'Address saved privately on this iPhone. No Shelf account was created. Email delivery is not yet tested.',
                }
              : action == 'prepare'
              ? 'Cancelled. Your Shelf account is unchanged.'
              : 'Please try again.';
        });
      }
      await _load();
    } on PlatformException catch (error) {
      if (mounted) {
        setState(() {
          _message = error.code == 'relay_missing'
              ? 'Apple did not return a private relay address. Use your existing Shelf relay address if available. Do not revoke a real account.'
              : 'The Apple email check could not finish. Please try again.';
        });
      }
    } catch (_) {
      if (mounted) {
        setState(
          () => _message =
              'The Apple email check could not finish. Please try again.',
        );
      }
    } finally {
      if (mounted) setState(() => _busy = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    if (!_status.available) return const SizedBox.shrink();
    return Padding(
      padding: const EdgeInsets.only(top: 24),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          const Text(
            'Owner Apple email check',
            style: TextStyle(fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 8),
          Text(
            _status.saved
                ? 'Your relay address is saved privately. Transfer only the address for the email check.'
                : 'Choose Hide My Email in Apple’s next screen. This does not sign you in to Shelf.',
          ),
          if (_busy) const LinearProgressIndicator(),
          if (_message != null)
            Padding(
              padding: const EdgeInsets.symmetric(vertical: 8),
              child: Text(_message!, key: const Key('relay-rehearsal-message')),
            ),
          if (!_status.saved)
            OutlinedButton(
              key: const Key('relay-rehearsal-start'),
              onPressed: _busy ? null : () => _perform('prepare'),
              child: const Text('Prepare Apple email check'),
            )
          else ...[
            OutlinedButton(
              key: const Key('relay-rehearsal-copy'),
              onPressed: _busy ? null : () => _perform('copy'),
              child: const Text('Copy address for private transfer'),
            ),
            TextButton(
              key: const Key('relay-rehearsal-forget'),
              onPressed: _busy ? null : () => _perform('forget'),
              child: const Text('Remove saved address'),
            ),
          ],
          const Text(
            'No email is sent by this check. Keep the address out of chat and screenshots.',
          ),
        ],
      ),
    );
  }
}
