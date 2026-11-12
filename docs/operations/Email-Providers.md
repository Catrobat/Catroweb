# Email Providers

Catroweb sends only transactional mail (verification, password reset, consent, admin, account management). To stay free, it chains several providers' free tiers: each mail goes to the first provider that still has room today, and a provider that fails hands the mail to the next one.

## How It Works

- `MAILER_PROVIDERS` lists the providers in the order they are tried, each with its daily limit: `main:300,mailjet:190,...`.
- `main` sends through `MAILER_DSN`. Every other provider sends through `MAILER_DSN_<NAME>` (`MAILER_DSN_MAILJET`, `MAILER_DSN_SES`, ...). An unset DSN falls back to `null://null`.
- The available slots are the ones in `EmailBudgetManager::PROVIDERS`: `main`, `mailjet`, `mailtrap`, `resend`, `mailgun`, `smtp2go`, `ses`.
- The app's daily limit is the sum of the provider limits. The per-type reserves (`EmailBudgetManager::TYPE_RESERVES`) still apply, and whatever the reserves don't take is shared.
- Sends per provider are counted in `email_daily_budget.provider_sent`. The admin page **System Management → Maintain** shows the total.

The defaults (`main:300`) are the old single-provider behavior.

## Free Tiers (checked 2026-11)

Keep every limit just under the free tier. For a monthly quota, use quota ÷ 31 so the month never runs out.

| Slot       | Provider | Free tier             | Limit to set | SMTP DSN                                                    |
| ---------- | -------- | --------------------- | ------------ | ----------------------------------------------------------- |
| `main`     | Brevo    | 300/day               | `300`        | `brevo+api://KEY@default` (current)                         |
| `mailjet`  | Mailjet  | 6,000/month, 200/day  | `190`        | `smtp://API_KEY:SECRET_KEY@in-v3.mailjet.com:587`           |
| `mailtrap` | Mailtrap | 4,000/month, 150/day  | `125`        | `smtp://api:TOKEN@live.smtp.mailtrap.io:587`                |
| `resend`   | Resend   | 3,000/month, 100/day  | `95`         | `smtp://resend:API_KEY@smtp.resend.com:587`                 |
| `mailgun`  | Mailgun  | 100/day               | `95`         | `smtp://USER:PASSWORD@smtp.eu.mailgun.org:587`              |
| `smtp2go`  | SMTP2GO  | 1,000/month           | `30`         | `smtp://USER:PASSWORD@mail.smtp2go.com:587`                 |
| `ses`      | AWS SES  | paid, $0.10 per 1,000 | spend cap    | `smtp://SMTP_USER:SMTP_PASSWORD@email-smtp.eu-central-1.amazonaws.com:587` |

All free slots together: about 835 mails a day. Free tiers change; check the provider's pricing page before relying on a number.

URL-encode credentials that contain `/`, `+`, `@` or `:` (`php -r 'echo rawurlencode($argv[1]);' 'secret'`).

Use **one account per provider**. Several free accounts at the same provider to get around its limit break most providers' terms and risk all of them being suspended at once.

## Adding a Provider

1. Sign up with a `catrobat.org` address, not a personal one (Cloudflare Email Routing forwards it).
2. Verify the sending domain `catrobat.org` in the provider and add its DKIM (and, if asked, return-path) records in Cloudflare DNS. Mail is sent from `support@catrobat.org`; without DKIM it fails DMARC and lands in spam.
3. Add the DSN to `/var/www/share/shared/.env.prod.local`, e.g. `MAILER_DSN_MAILJET=smtp://...`.
4. Append the provider to `MAILER_PROVIDERS` there, e.g. `MAILER_PROVIDERS=main:300,mailjet:190`.
5. Clear the cache (`bin/console cache:clear --env=prod`) and send a test: `bin/console mailer:test you@example.org --transport=mailjet --env=prod`.

## Paid Overflow: AWS SES

SES has no hard spending cap of its own, so the app's limit is the cap. Put `ses` **last**, so it only sends once every free tier is used up. The worst case per month is `limit × 31 × $0.0001`: `ses:2000` costs at most about $6.20 a month.

1. Create an AWS account (paid by the organization) and, in SES `eu-central-1`, verify the domain `catrobat.org` (Easy DKIM: three CNAME records in Cloudflare).
2. Request production access (new accounts are in the sandbox and may only mail verified addresses).
3. Create SMTP credentials (SES → SMTP settings) and set `MAILER_DSN_SES`.
4. Add an AWS Budgets alert as a second safety net.
5. Append `ses:<limit>` to `MAILER_PROVIDERS`.
