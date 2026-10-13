# shellnet-portal

Customer self-service portal and staff dashboard for a Mikrotik hotspot ISP: voucher sales and redemption, subscription plans, coupons, device limits, RADIUS-backed authentication, Paystack payments, and operational reports.

## Features

- **Customer portal** (`/portal`) — branded landing, registration/login, plan checkout, voucher redemption, account page, device-limit enforcement
- **Staff dashboard** — customers, vouchers, plans, subscriptions, transactions, messaging, reports
- **Vouchers & coupons** — batch generation, redemption tracking, coupon discounts
- **RADIUS** — radcheck/radreply/usergroup/accounting tables, NAS and router records, IP pools, bandwidth profiles
- **Payments** — Paystack checkout, callbacks, webhooks, retry/resume for stuck transactions
- **Messaging** — customer messages and delivery log
- **Auth** — staff via Fortify, customers via a separate customer guard

## Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.3+, Laravel 13, Livewire 4 + Flux, Fortify |
| Mikrotik | RouterOS API (`routeros-api-php`) |
| Payments | Paystack |
| Frontend assets | Vite 8, Tailwind CSS 4, Chart.js, MapLibre |
| Database | SQLite by default (MySQL/Postgres via `DB_*`) |
| Tests | Pest 4 (Laravel plugin), Pint for style |

## Layout

- `app/Livewire/` — interactive UI components
- `app/Http/Controllers/Portal/` — customer portal endpoints
- `app/Models/` — vouchers, plans, subscriptions, RADIUS tables, routers, messaging
- `app/Services/` — RouterOS, payments, voucher logic
- `routes/portal.php` — customer portal routes; `routes/web.php` — staff app + Paystack webhooks
- `resources/views/` — Blade pages; `resources/js|css/` — Vite entry points
- `database/migrations|seeders|factories/` — schema, seeders, factories

## Run it

Prerequisites: PHP 8.3+ with `composer`, Node 20+ with `pnpm`.

```bash
composer setup        # install, .env, key, migrate, frontend build
composer dev          # app server + queue + logs + Vite, concurrently
```

Useful commands (see `composer.json` scripts):

```bash
php artisan test          # full check: Pint + Pest suite
php artisan migrate       # database migrations
pnpm run build            # production frontend assets
```

Environment: copy `.env.example` to `.env` and set `DB_*` for MySQL/Postgres (SQLite works out of the box), `MAIL_*` for mail, and `PAYSTACK_*` for live payments.

## License

MIT — see LICENSE.
