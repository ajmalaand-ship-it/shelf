import 'package:flutter/material.dart';

import '../l10n/app_strings.dart';
import 'account_controller.dart';
import 'account_service.dart';

enum _AccountForm { login, register, forgot, password }

void openAccount(BuildContext context) =>
    Navigator.of(context)
        .push(MaterialPageRoute<void>(builder: (_) => const AccountScreen()));

class AccountScreen extends StatefulWidget {
  const AccountScreen({super.key});
  @override
  State<AccountScreen> createState() => _AccountScreenState();
}

class _AccountScreenState extends State<AccountScreen>
    with WidgetsBindingObserver {
  final _email = TextEditingController(),
      _name = TextEditingController(),
      _password = TextEditingController(),
      _confirmation = TextEditingController(),
      _current = TextEditingController();
  _AccountForm _form = _AccountForm.login;
  String? _message;
  bool _loading = false;
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    for (final c in [_email, _name, _password, _confirmation, _current]) {
      c.dispose();
    }
    super.dispose();
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    if (state == AppLifecycleState.resumed) {
      final c = AccountScope.of(context);
      if (c != null && !c.busy) _perform(c.refresh);
    }
  }

  Future<void> _perform(
    Future<void> Function() action, {
    String? success,
  }) async {
    if (_loading) return;
    setState(() {
      _message = null;
      _loading = true;
    });
    try {
      await action();
      if (mounted)
        setState(() {
          _message = success;
        });
    } on AccountFailure catch (error) {
      if (mounted)
        setState(
          () => _message = AppStrings.of(context).accountError(error.status),
        );
    } catch (_) {
      if (mounted)
        setState(
          () => _message = AppStrings.of(context).accountConnectionError,
        );
    } finally {
      if (mounted) {
        _password.clear();
        _confirmation.clear();
        _current.clear();
        setState(() => _loading = false);
      }
    }
  }

  void _show(_AccountForm form) {
    setState(() {
      _form = form;
      _message = null;
    });
    _password.clear();
    _confirmation.clear();
    _current.clear();
  }

  @override
  Widget build(BuildContext context) {
    final c = AccountScope.of(context);
    final s = AppStrings.of(context);
    final blocked = _loading || c?.busy == true;
    return Scaffold(
      appBar: AppBar(title: Text(s.account)),
      body: ListView(
        padding: const EdgeInsets.all(20),
        children: [
          if (c == null || !c.initialized || !c.configuration.enabled) ...[
            Text(s.accountsUnavailable),
            if (c != null)
              FilledButton(
                onPressed: blocked ? null : () => _perform(c.initialize),
                child: Text(s.retry),
              ),
          ] else ...[
            if (c.configuration.ownerTestingOnly) Text(s.ownerAccountTesting),
            if (_message != null)
              Padding(
                padding: const EdgeInsets.symmetric(vertical: 12),
                child: Text(_message!, key: const Key('account-message')),
              ),
            if (blocked) const LinearProgressIndicator(),
            if (c.user case final user?) ...[
              Text(
                user.name ?? user.email,
                key: const Key('account-identity'),
                textDirection: TextDirection.rtl,
              ),
              if (user.name != null)
                Text(user.email, textDirection: TextDirection.ltr),
              Text(user.verified ? s.emailVerified : s.verifyEmail),
              if (!user.verified)
                TextButton(
                  onPressed: blocked
                      ? null
                      : () => _perform(
                          c.resendVerification,
                          success: s.emailSent,
                        ),
                  child: Text(s.resendVerification),
                ),
              TextButton(
                onPressed: blocked ? null : () => _perform(c.refresh),
                child: Text(s.refreshAccount),
              ),
              if (_form == _AccountForm.password) ...[
                _field(
                  _current,
                  s.currentPassword,
                  'current-password',
                  password: true,
                ),
                _field(
                  _password,
                  s.newPassword,
                  'account-password',
                  password: true,
                ),
                _field(
                  _confirmation,
                  s.confirmPassword,
                  'confirm-password',
                  password: true,
                ),
                Text(s.passwordRule),
                FilledButton(
                  onPressed: blocked
                      ? null
                      : () => _perform(
                          () => c.changePassword(
                            _current.text,
                            _password.text,
                            _confirmation.text,
                          ),
                          success: s.passwordChanged,
                        ),
                  child: Text(s.changePassword),
                ),
              ] else
                TextButton(
                  onPressed: blocked
                      ? null
                      : () => user.method == 'google'
                            ? _perform(
                                () => c.forgotPassword(user.email),
                                success: s.emailSent,
                              )
                            : _show(_AccountForm.password),
                  child: Text(s.changePassword),
                ),
              OutlinedButton(
                key: const Key('account-sign-out'),
                onPressed: blocked
                    ? null
                    : () => _perform(() async {
                        await c.signOut();
                        if (mounted) _show(_AccountForm.login);
                      }),
                child: Text(s.signOut),
              ),
              TextButton(
                key: const Key('account-delete'),
                onPressed: blocked
                    ? null
                    : () async {
                        final confirmed = await showDialog<bool>(
                          context: context,
                          builder: (context) => AlertDialog(
                            title: Text(s.deleteAccount),
                            content: Text(s.deleteAccountExplanation),
                            actions: [
                              TextButton(
                                onPressed: () => Navigator.pop(context, false),
                                child: Text(s.cancel),
                              ),
                              FilledButton(
                                onPressed: () => Navigator.pop(context, true),
                                child: Text(s.confirmDelete),
                              ),
                            ],
                          ),
                        );
                        if (confirmed == true && mounted)
                          await _perform(
                            c.requestDeletion,
                            success: s.deleteEmailSent,
                          );
                      },
                child: Text(s.deleteAccount),
              ),
            ] else ...[
              if (_form == _AccountForm.password) Text(s.passwordChanged),
              if (_form == _AccountForm.register)
                _field(_name, s.optionalName, 'account-name'),
              _field(_email, s.email, 'account-email', email: true),
              if (_form != _AccountForm.forgot)
                _field(
                  _password,
                  s.password,
                  'account-password',
                  password: true,
                ),
              if (_form == _AccountForm.register) ...[
                _field(
                  _confirmation,
                  s.confirmPassword,
                  'confirm-password',
                  password: true,
                ),
                Text(s.passwordRule),
              ],
              FilledButton(
                key: const Key('account-submit'),
                onPressed: blocked
                    ? null
                    : () => _perform(
                        () async {
                          switch (_form) {
                            case _AccountForm.register:
                              await c.register(
                                _email.text,
                                _password.text,
                                _confirmation.text,
                                _name.text,
                              );
                              if (mounted) _show(_AccountForm.login);
                              break;
                            case _AccountForm.forgot:
                              await c.forgotPassword(_email.text);
                              break;
                            default:
                              await c.signIn(_email.text, _password.text);
                              if (mounted) _show(_AccountForm.login);
                          }
                        },
                        success:
                            _form == _AccountForm.register ||
                                _form == _AccountForm.forgot
                            ? s.emailSent
                            : null,
                      ),
                child: Text(
                  _form == _AccountForm.register
                      ? s.createAccount
                      : _form == _AccountForm.forgot
                      ? s.resetPassword
                      : s.signIn,
                ),
              ),
              if (c.configuration.googleEnabled && _form == _AccountForm.login)
                OutlinedButton(
                  key: const Key('google-sign-in'),
                  onPressed: blocked ? null : () => _perform(c.googleSignIn),
                  child: Text(s.googleSignIn),
                ),
              TextButton(
                onPressed: blocked ? null : () => _show(_AccountForm.login),
                child: Text(s.signIn),
              ),
              TextButton(
                onPressed: blocked ? null : () => _show(_AccountForm.register),
                child: Text(s.createAccount),
              ),
              TextButton(
                onPressed: blocked ? null : () => _show(_AccountForm.forgot),
                child: Text(s.forgotPassword),
              ),
            ],
          ],
        ],
      ),
    );
  }

  Widget _field(
    TextEditingController controller,
    String label,
    String key, {
    bool password = false,
    bool email = false,
  }) => Padding(
    padding: const EdgeInsets.symmetric(vertical: 8),
    child: TextField(
      key: Key(key),
      controller: controller,
      obscureText: password,
      autocorrect: !password && !email,
      enableSuggestions: !password && !email,
      textDirection: email || password ? TextDirection.ltr : TextDirection.rtl,
      keyboardType: email ? TextInputType.emailAddress : null,
      decoration: InputDecoration(labelText: label),
    ),
  );
}
