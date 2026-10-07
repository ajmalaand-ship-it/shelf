<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Privacy Policy for Shelf, published by Hindara.">
    <title>Privacy Policy — Shelf</title>
    <style>
        :root {
            color-scheme: light;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            line-height: 1.65;
            color: #24211d;
            background: #f7f3eb;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
        }

        main {
            width: min(46rem, calc(100% - 2rem));
            margin: 0 auto;
            padding: 3rem 0 4rem;
        }

        article {
            padding: clamp(1.25rem, 4vw, 3rem);
            border: 1px solid #ddd3c3;
            border-radius: 0.75rem;
            background: #fffdf9;
            box-shadow: 0 0.4rem 1.5rem rgb(55 44 29 / 8%);
        }

        h1, h2 {
            line-height: 1.25;
            color: #392e22;
        }

        h1 {
            margin: 0 0 0.35rem;
            font-size: clamp(2rem, 7vw, 3rem);
        }

        h2 {
            margin-top: 2rem;
            font-size: 1.25rem;
        }

        p, li {
            max-width: 68ch;
        }

        .brand {
            margin: 0;
            color: #6b4e2e;
            font-size: 1.15rem;
            font-weight: 700;
        }

        .slogan {
            margin-top: 0.25rem;
            color: #645b51;
        }

        .updated {
            margin: 1.5rem 0 2rem;
            color: #645b51;
        }

        a {
            color: #714b1f;
            text-underline-offset: 0.18em;
        }

        a:focus-visible {
            outline: 3px solid #a66b27;
            outline-offset: 3px;
        }

        @media (prefers-color-scheme: dark) {
            :root {
                color-scheme: dark;
                color: #f2ede5;
                background: #181512;
            }

            article {
                border-color: #4b4034;
                background: #231f1a;
                box-shadow: none;
            }

            h1, h2 {
                color: #fffaf2;
            }

            .brand, a {
                color: #e2b67e;
            }

            .slogan, .updated {
                color: #c8beb2;
            }
        }
    </style>
</head>
<body>
<main>
    <article>
        <header>
            <p class="brand">Shelf</p>
            <p class="slogan" dir="rtl" lang="ps">کتاب مو ژوند بدلوي</p>
            <h1>Privacy Policy</h1>
            <p class="updated">Last updated: September 29, 2026</p>
        </header>

        <p>Shelf is a bookstore and reader. You can browse books and read approved samples without an account.</p>
        <h2>Reader account information</h2>
        <p>We keep your email address, optional display name, sign-in method, email-verification time and account timestamps. For authentication we store a password hash when you use a password, a hashed Google sign-in identifier when linked, and hashed, expiring sign-in and email-action tokens. We never store plaintext passwords or Google ID tokens. Google profile photos, contacts, date of birth, address and phone number are not collected.</p>
        <p>Email/password and, when configured, Google sign-in are available. Google processes its own sign-in interaction under its privacy policy. Shelf checks Google identity tokens on the server. The app stores its Shelf sign-in token in protected device storage. Signing out revokes the account's active Shelf sign-in tokens.</p>
        <h2>Email and service operation</h2>
        <p>We send verification, password-reset, sign-in confirmation and deletion-confirmation emails through our hosting provider from noreply@shelf.services. Hosting infrastructure may process request metadata, such as IP addresses, times and diagnostic logs, to deliver and protect the service. These are not reader profile fields. We do not use advertising or analytics SDKs, sell personal data, or collect your reading history in this version.</p>
        <h2>Books and purchases</h2>
        <p>Books, author credits, covers and available audio are managed by the owner. Creating an account does not unlock books. Purchases are currently limited to owner testing with Google Play test payment methods, through RevenueCat. Shelf keeps per-book transaction, sale and refund records; Shelf does not collect payment-card details. Access is confirmed by the server for your specific reader account. A refunded or revoked download is removed at the next successful check; offline access requires a successful check at least every 30 days.</p>
        <h2>Deletion and retention</h2>
        <p>You can request deletion in Shelf's Account screen or on <a href="{{ route('account.delete') }}">our account-deletion page</a>. Confirm the email link to remove your reader profile, credentials, sessions and book access. Owner administration is separate. We retain only necessary purchase, refund, accounting and recovery evidence, without your profile name or email. Financial retention is still under review; the 90-day log and backup periods do not apply to financial history.</p>
        <p>A new account does not automatically receive deleted-account purchases. Contact support for owner-assisted recovery: we verify the claimant and original store transaction; matching emails alone is insufficient. Refunded or revoked purchases cannot be recovered.</p>
        <p>Routine security logs are kept for 90 days. Documented incidents may require reviewed exceptions. Local backups retain 14 copies; encrypted OneDrive backups normally expire after 90 days. The last verified recoverable set is protected when expiry would remove it, and that exception is reported. Pre-policy logs are conservatively dated when archived. Protected minimal deletion records cover retained backups, independently of the restored database. Deletions must be reapplied before reopening a recovered service; recovery stops if those records are unavailable.</p>
        <p>RevenueCat account metadata removal is retried and verified. Necessary pseudonymous purchase/refund evidence remains for refunds and approved recovery. Provider failures or immutable metadata require review; we do not delete purchase history to conceal an unresolved exception. Google Play retains its own records under its policies; Shelf does not promise to erase Google's store orders. Your device token and account downloads are removed when Shelf next checks the deleted account. Offline access cannot be remotely removed while a device is disconnected; the existing 30-day server-verification limit still applies.</p>
        <h2>Security and contact</h2>
        <p>We use HTTPS, password hashing, limited authentication attempts, expiring email links and restricted administration. Contact <a href="mailto:ajmalaand@gmail.com">ajmalaand@gmail.com</a> for privacy or deletion help, including if you no longer control your account email.</p>
    </article>
</main>
</body>
</html>
