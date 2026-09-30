import 'package:flutter/material.dart';

import '../accounts/account_controller.dart';
import '../accounts/account_screen.dart';
import '../accounts/account_service.dart';
import '../models/poetry_collection.dart';
import '../settings/reader_settings.dart';
import 'book_purchase_provider.dart';
import 'library_controller.dart';
import 'library_screen.dart';

class BookPriceLabel extends StatefulWidget {
  const BookPriceLabel({required this.book, super.key});
  final PoetryCollection book;
  @override
  State<BookPriceLabel> createState() => _BookPriceLabelState();
}

class _BookPriceLabelState extends State<BookPriceLabel> {
  int? reader;
  Future<StoreBookProduct?>? price;
  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    final next = AccountScope.of(context)?.user?.id;
    if (next == reader) return;
    reader = next;
    price = next == null
        ? null
        : LibraryScope.of(context)
              ?.product(widget.book)
              .timeout(const Duration(seconds: 20));
  }

  @override
  Widget build(BuildContext context) => FutureBuilder<StoreBookProduct?>(
    future: price,
    builder: (context, snapshot) => snapshot.data == null
        ? const SizedBox.shrink()
        : Text(
            snapshot.data!.price,
            textDirection: TextDirection.ltr,
            textAlign: TextAlign.center,
            style: Theme.of(context).textTheme.titleLarge,
          ),
  );
}

class PurchaseScreen extends StatefulWidget {
  const PurchaseScreen({required this.book, required this.settings, super.key});
  final PoetryCollection book;
  final ReaderSettings settings;
  @override
  State<PurchaseScreen> createState() => _PurchaseScreenState();
}

class _PurchaseScreenState extends State<PurchaseScreen> {
  bool agreed = false, loading = false;
  StoreBookProduct? product;
  String? message;
  int? _reader;
  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    final reader = AccountScope.of(context)?.user?.id;
    if (reader != _reader) {
      _reader = reader;
      agreed = false;
      product = null;
      if (reader != null) _load();
    }
  }

  Future<void> _load() async {
    if (loading) return;
    setState(() {
      loading = true;
      message = null;
    });
    try {
      final library = LibraryScope.of(context);
      final value = await library
          ?.product(widget.book)
          .timeout(const Duration(seconds: 20));
      if (mounted)
        setState(() {
          product = value;
          if (value == null)
            message = 'This book is not available in Google Play yet.';
        });
    } catch (_) {
      if (mounted)
        setState(
          () => message = 'Test purchases are not configured yet. Please try again after setup.',
        );
    } finally {
      if (mounted) setState(() => loading = false);
    }
  }

  Future<void> _buy() async {
    final library = LibraryScope.of(context);
    if (library == null || product == null || loading || !agreed) return;
    setState(() {
      loading = true;
      message = 'Opening Google Play…';
    });
    try {
      final result = await library.purchase(widget.book, product!, agreed);
      if (!mounted) return;
      if (library.owns(widget.book.id)) {
        await openOwnedBook(context, library, widget.book, widget.settings);
        if (mounted) Navigator.of(context).pop();
        return;
      }
      setState(
        () => message = switch (result) {
          StorePurchaseResult.cancelled =>
            'Purchase cancelled. Your book was not unlocked.',
          StorePurchaseResult.pending => 'Payment is pending with Google Play. Your book will appear after the store and Shelf confirm it.',
          StorePurchaseResult.failed => 'The purchase did not finish. No book was unlocked. Please try again.',
          StorePurchaseResult.confirming =>
            library.message ??
                'Confirming… Check My Library shortly; do not buy again.',
        },
      );
    } on AccountFailure catch (e) {
      if (mounted)
        setState(
          () => message = e.status == 403
              ? 'Verify your email first. If buying is blocked, contact Shelf support.'
              : 'The purchase could not be confirmed. Check My Library before trying again.',
        );
    } catch (_) {
      if (mounted)
        setState(
          () => message =
              'Connection problem. Check My Library before trying again.',
        );
    } finally {
      if (mounted) setState(() => loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final account = AccountScope.of(context)?.user;
    final library = LibraryScope.of(context);
    return Directionality(
      textDirection: TextDirection.ltr,
      child: Scaffold(
        appBar: AppBar(title: const Text('Buy this book')),
        body: ListView(
          padding: const EdgeInsets.all(20),
          children: [
            Text(
              widget.book.title,
              textDirection: TextDirection.rtl,
              textAlign: TextAlign.right,
              style: Theme.of(context).textTheme.headlineMedium,
            ),
            const SizedBox(height: 20),
            const Text(
              'Test mode — use only Google Play test payment methods.',
            ),
            const SizedBox(height: 16),
            if (account == null) ...[
              const Text(
                'Sign in to choose the Shelf account that will own this book.',
              ),
              FilledButton(
                onPressed: () async {
                  await Navigator.of(context).push(
                    MaterialPageRoute<void>(
                      builder: (_) => const AccountScreen(),
                    ),
                  );
                  if (mounted) setState(() {});
                },
                child: const Text('Sign in / Create account'),
              ),
            ] else ...[
              Text(
                'Buying for ${account.email}',
                key: const Key('purchase-account'),
              ),
              const SizedBox(height: 16),
              if (product != null)
                Text(
                  product!.price,
                  key: const Key('play-price'),
                  style: Theme.of(context).textTheme.headlineLarge,
                ),
              if (loading || library?.busy == true)
                const LinearProgressIndicator(),
              if (library?.busy == true && library?.message != null)
                Text(library!.message!),
              if (message != null)
                Padding(
                  padding: const EdgeInsets.symmetric(vertical: 16),
                  child: Text(message!, key: const Key('purchase-message')),
                ),
              CheckboxListTile(
                key: const Key('purchase-agreement'),
                value: agreed,
                onChanged: loading
                    ? null
                    : (v) => setState(() => agreed = v ?? false),
                controlAffinity: ListTileControlAffinity.leading,
                title: const Text(
                  'Read the free sample first. All sales are final. I agree.',
                ),
              ),
              FilledButton(
                key: const Key('purchase-buy'),
                style: FilledButton.styleFrom(
                  minimumSize: const Size.fromHeight(52),
                ),
                onPressed:
                    !agreed ||
                        loading ||
                        product == null ||
                        library?.busy == true ||
                        library?.owns(widget.book.id) == true
                    ? null
                    : _buy,
                child: Text(
                  loading
                      ? 'Confirming…'
                      : 'Buy${product == null ? '' : ' · ${product!.price}'}',
                ),
              ),
              if (product == null && !loading)
                OutlinedButton(
                  onPressed: _load,
                  child: const Text('Check availability'),
                ),
            ],
          ],
        ),
      ),
    );
  }
}
