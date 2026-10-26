<div align="center">

# 🍽️ Analytics Demo

### Restaurant Tracking System

A small end-to-end analytics pipeline built with **Laravel 8** and **Next.js**, integrated into a demo restaurant app.

![Laravel](https://img.shields.io/badge/Laravel-8.83.29-FF2D20?logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-7.3%2B%20%2F%208.x-777BB4?logo=php&logoColor=white)
![Next.js](https://img.shields.io/badge/Next.js-12-000000?logo=nextdotjs&logoColor=white)
![React](https://img.shields.io/badge/React-17-61DAFB?logo=react&logoColor=black)
![MySQL](https://img.shields.io/badge/MySQL-MariaDB%20%7C%20SQLite-4479A1?logo=mysql&logoColor=white)
![Tests](https://img.shields.io/badge/tests-6%20passing-brightgreen)

</div>

---

## 📑 Table of Contents

- [✨ Features](#-features)
- [🏗️ Architecture](#️-architecture)
- [📦 Tech Stack](#-tech-stack)
- [✅ Prerequisites](#-prerequisites)
- [🚀 Backend Setup (Laravel)](#-backend-setup-laravel)
- [🎨 Frontend Setup (Next.js Dashboard)](#-frontend-setup-nextjs-dashboard)
- [🌐 URLs & API](#-urls--api)
- [🧪 Running Tests](#-running-tests)
- [📁 Project Structure](#-project-structure)
- [🔄 End-to-End Smoke Test](#-end-to-end-smoke-test)
- [🛠️ Troubleshooting](#️-troubleshooting)
- [🔒 Security Notes](#-security-notes)
- [📝 Assumptions & Limitations](#-assumptions--limitations)

---

## ✨ Features

| | Component | Description |
|---|-----------|-------------|
| 🌐 | **Browser SDK** | Captures page views and custom events |
| 🖥️ | **Server helper** | Sends business events (e.g. `order_created`) asynchronously |
| 📥 | **Ingestion API** | Validates, throttles and stores events |
| 📊 | **Daily aggregates** | Pre-computed rollups power a fast dashboard |
| 📈 | **Next.js dashboard** | Traffic, unique visitors, popular pages and event trends with date-range filtering |

---

## 🏗️ Architecture

```text
┌──────────────┐    POST /api/events     ┌──────────────────┐
│  Browser SDK │ ──────────────────────▶ │                  │
└──────────────┘                         │   Laravel API    │      ┌──────────────┐
                                         │  (validate +     │ ───▶ │ events table │
┌──────────────┐    queued job           │   throttle)      │      └──────┬───────┘
│ Server helper│ ──────────────────────▶ │                  │             │
└──────────────┘                         └──────────────────┘             ▼
                                                                 ┌─────────────────┐
┌──────────────┐    GET /api/analytics/*                         │ UpdateDaily     │
│   Next.js    │ ◀────────────────────── daily_event_aggregates ◀│ Aggregates job  │
│  Dashboard   │                                                 └─────────────────┘
└──────────────┘
```

---

## 📦 Tech Stack

| Layer    | Technology                                |
|----------|-------------------------------------------|
| Backend  | Laravel **8.83.29**, PHP **7.3+ / 8.0+**  |
| Frontend | Next.js 12 (React 17)                     |
| Charts   | Recharts                                  |
| Database | MySQL / MariaDB (or SQLite)               |
| Queue    | Database driver (default in Laravel 8)    |

---

## ✅ Prerequisites

Make sure you have these installed:

- **PHP** ≥ 7.3 (recommended 8.0 / 8.1)
- **Composer** 2.x
- **Node.js** ≥ 14 and **npm** ≥ 6
- **MySQL / MariaDB** (or SQLite, see below)
- **Git** (optional)

Verify your setup:

```bash
php -v
composer -V
node -v
npm -v
```

---

## 🚀 Backend Setup (Laravel)

> 💡 Examples use Windows paths (`C:\analytics-demo`). On macOS/Linux, use `cp` instead of `copy` and your own project path.

### 1️⃣ Install PHP dependencies

```bash
cd C:\analytics-demo
composer install
```

### 2️⃣ Configure the environment

```bash
copy .env.example .env
php artisan key:generate
```

Edit `.env` and set:

```env
APP_NAME="Analytics Demo"
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=analytics_demo
DB_USERNAME=root
DB_PASSWORD=

QUEUE_CONNECTION=database

# Analytics tokens (change for production!)
ANALYTICS_INGEST_TOKEN=dev-ingest-token
ANALYTICS_READ_TOKEN=dev-read-token
ANALYTICS_PUBLIC_TOKEN=dev-public-token
ANALYTICS_INGEST_URL=/api/events
```

<details>
<summary><b>Using SQLite instead of MySQL?</b></summary>

<br>

Set `DB_CONNECTION=sqlite` in `.env`, then create the database file:

```bash
# Windows
type nul > database\database.sqlite

# macOS / Linux
touch database/database.sqlite
```

</details>

### 3️⃣ Create the database (MySQL)

```bash
mysql -u root -e "CREATE DATABASE analytics_demo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### 4️⃣ Run migrations

```bash
php artisan migrate
```

Both migrations should succeed:

```text
✔ 2024_01_01_000001_create_events_table
✔ 2024_01_01_000002_create_daily_event_aggregates_table
```

### 5️⃣ Clear caches

> ⚠️ Do this any time you edit `Kernel.php` or `.env`.

```bash
php artisan config:clear
php artisan route:clear
php artisan cache:clear
composer dump-autoload
```

### 6️⃣ Start the backend services

Open **two** terminals inside the project folder:

| Terminal | Purpose | Command |
|----------|---------|---------|
| **1** | Web server | `php artisan serve --port=8000` |
| **2** | Queue worker *(required for `order_created` events and daily aggregates)* | `php artisan queue:work --tries=3` |

---

## 🎨 Frontend Setup (Next.js Dashboard)

```bash
cd C:\analytics-demo\dashboard
npm install
```

Create `dashboard/.env.local`:

```env
NEXT_PUBLIC_ANALYTICS_READ_TOKEN=dev-read-token
```

Start the dashboard:

```bash
npm run dev
```

---

## 🌐 URLs & API

### Access points

| Service | URL | Purpose |
|---------|-----|---------|
| 🏠 **Restaurant home** | http://127.0.0.1:8000/ | Landing page, fires `page_view` |
| 📖 **Menu** | http://127.0.0.1:8000/menu | Fires `page_view`, `menu_viewed` |
| 🛒 **Order form** | http://127.0.0.1:8000/order | Place an order, fires `order_created` |
| 📊 **Analytics dashboard** | http://127.0.0.1:3000/ | Visualises captured events |

### API endpoints

| Method | Endpoint | Purpose |
|--------|----------|---------|
| `POST` | `/api/events` | Ingestion (used by SDK + server helper) |
| `GET` | `/api/analytics/overview` | Dashboard summary data |
| `GET` | `/api/analytics/trend` | Time series |
| `GET` | `/api/analytics/popular-pages` | Top pages |
| `GET` | `/api/analytics/events` | Event name totals |

### 🔑 Authentication

All `/api/*` endpoints require a bearer token:

| Endpoint | Accepted tokens |
|----------|-----------------|
| `POST /api/events` | `ANALYTICS_INGEST_TOKEN` **or** `ANALYTICS_PUBLIC_TOKEN` |
| `GET /api/analytics/*` | `ANALYTICS_READ_TOKEN` |

### ⚡ Quick test with curl

**Overview (last 7 days):**

```bash
curl -H "Authorization: Bearer dev-read-token" \
     "http://127.0.0.1:8000/api/analytics/overview"
```

**Send an event:**

```bash
curl -X POST "http://127.0.0.1:8000/api/events?token=dev-public-token" \
     -H "Content-Type: application/json" \
     -d '{
       "events": [{
         "event_id": "11111111-1111-4111-8111-111111111111",
         "event_name": "page_view",
         "properties": { "path": "/test" },
         "anonymous_id": "22222222-2222-4222-8222-222222222222",
         "session_id": "33333333-3333-4333-8333-333333333333",
         "page_url": "http://127.0.0.1:8000/test",
         "occurred_at": "2026-10-26T12:00:00Z"
       }]
     }'
```

> 🪟 **Windows CMD:** replace `\` line continuations with `^` and escape the JSON quotes (`\"`), or use PowerShell / Git Bash.

**Expected responses:**

```jsonc
// GET /api/analytics/overview (empty DB)
{ "page_views": 0, "unique_visitors": 0, "sessions": 0 }

// POST /api/events  →  HTTP 202
{ "accepted": 1 }
```

---

## 🧪 Running Tests

Tests live in `tests/Feature/` and cover the ingestion pipeline and every analytics query endpoint.

### Run the full suite

```bash
cd C:\analytics-demo
php artisan test
```

Expected output:

```text
PASS  Tests\Unit\ExampleTest
✓ basic test

PASS  Tests\Feature\EventIngestionTest
✓ rejects missing token
✓ accepts valid event
✓ deduplicates by event_id
✓ rejects invalid event

PASS  Tests\Feature\AnalyticsQueryTest
✓ overview counts page views and uniques
✓ popular pages returns paths

Tests:   6 passed
Time:    ~1.5s
```

### Other ways to run tests

| Goal | Command |
|------|---------|
| Single test file | `php artisan test tests/Feature/EventIngestionTest.php` |
| Single test method | `php artisan test --filter test_deduplicates_by_event_id` |
| PHPUnit directly | `vendor\bin\phpunit --testdox` |

### Test structure

| File | What it covers |
|------|----------------|
| `tests/Feature/EventIngestionTest.php` | Auth, validation, successful ingestion, deduplication |
| `tests/Feature/AnalyticsQueryTest.php` | Overview counts, popular pages aggregation |

### Test environment notes

- Tests use `RefreshDatabase`: every test runs against a **fresh, empty** in-memory database (SQLite by default via `phpunit.xml`).
- No queue worker is required. `UpdateDailyAggregates` is dispatched, but only affects the DB if you assert on aggregates (current tests use raw `Event` rows).
- Tests never hit the real ingestion endpoint; they call `Event::create()` directly, which keeps them fast and deterministic.

---

## 📁 Project Structure

```text
analytics-demo/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AnalyticsController.php        # Dashboard queries
│   │   │   ├── EventIngestionController.php   # POST /api/events
│   │   │   └── DemoController.php             # Restaurant pages
│   │   ├── Middleware/
│   │   │   └── VerifyAnalyticsToken.php       # Bearer token check
│   │   ├── Requests/
│   │   │   └── IngestEventsRequest.php        # Payload validation
│   │   └── Kernel.php                         # Route middleware aliases
│   ├── Jobs/
│   │   └── UpdateDailyAggregates.php          # Recomputes daily rollups
│   ├── Models/
│   │   ├── Event.php
│   │   └── DailyEventAggregate.php
│   └── Support/
│       └── Analytics.php                      # Server-side helper
├── config/
│   ├── analytics.php                          # Token + endpoint config
│   └── cors.php                               # Allowed origins
├── database/
│   └── migrations/                            # events + aggregates tables
├── public/
│   └── js/analytics.js                        # Browser SDK
├── resources/
│   └── views/                                 # Blade demo pages
├── routes/
│   ├── api.php                                # Ingestion + analytics endpoints
│   └── web.php                                # Demo restaurant routes
├── tests/
│   └── Feature/
│       ├── EventIngestionTest.php
│       └── AnalyticsQueryTest.php
├── dashboard/                                 # Next.js dashboard
│   ├── lib/api.js
│   ├── pages/index.js
│   ├── next.config.js
│   └── package.json
└── README.md
```

---

## 🔄 End-to-End Smoke Test

**1. Start all three processes** in separate terminals:

```bash
php artisan serve --port=8000         # terminal 1
php artisan queue:work --tries=3      # terminal 2
cd dashboard && npm run dev           # terminal 3
```

**2. Visit the restaurant pages** to generate events:

| Page | Events fired |
|------|--------------|
| http://127.0.0.1:8000/ | `page_view` |
| http://127.0.0.1:8000/menu | `page_view` + `menu_viewed` |
| http://127.0.0.1:8000/order | place an order → `order_created` |

**3. Wait ~5 seconds** for the queue worker to run `UpdateDailyAggregates`.

**4. Open the dashboard** at http://127.0.0.1:3000 and check:

- [x] Overview cards show non-zero page views, unique visitors and sessions
- [x] Trend chart shows today's spike
- [x] Popular Pages lists `/menu` and `/order`
- [x] Event Breakdown lists `page_view`, `menu_viewed`, `order_created`

**5. Verify in the database:**

```bash
php artisan tinker
>>> App\Models\Event::count();
>>> App\Models\DailyEventAggregate::all()->toArray();
```

---

## 🛠️ Troubleshooting

| Error | Fix |
|-------|-----|
| `Failed opening required 'server.php'` | File missing from project root. Restore it (see Laravel 8 skeleton) or use `php artisan serve` |
| `Target class [Illuminate\Http\Middleware\HandleCors] does not exist` | In `app/Http/Kernel.php`, use `\Fruitcake\Cors\HandleCors::class` (Laravel 8) |
| `Target class [analytics.token] does not exist` | Add `'analytics.token' => \App\Http\Middleware\VerifyAnalyticsToken::class` to `$routeMiddleware` |
| `Target class [throttle] does not exist` | Add `'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class` to `$routeMiddleware` |
| `API /api/analytics/overview failed: 500` | Run `php artisan migrate`, confirm `DB_CONNECTION` matches the migrated DB, check `storage/logs/laravel.log` |
| Events saved but dashboard stays at zero | Start `php artisan queue:work`; aggregates only run via the queue |
| `order_created` not appearing | Ensure the queue worker is running and `.env` has `QUEUE_CONNECTION=database` |
| CORS error in dashboard | Confirm `config/cors.php` includes `http://localhost:3000`, then `php artisan config:clear` |
| Changes to `Kernel.php` / `.env` not applied | `php artisan config:clear && php artisan route:clear && composer dump-autoload`, then delete `bootstrap/cache/*.php` |

<details>
<summary><b>🔍 Diagnostic commands</b></summary>

<br>

```bash
# List all API routes with attached middleware
php artisan route:list --path=api

# Confirm middleware aliases are registered (tinker)
php artisan tinker --execute "dump(app('router')->getMiddleware()['throttle'] ?? 'MISSING');"
php artisan tinker --execute "dump(app('router')->getMiddleware()['analytics.token'] ?? 'MISSING');"

# Check tables exist
php artisan tinker --execute "dump(Schema::hasTable('events'), Schema::hasTable('daily_event_aggregates'));"

# Tail the log
type storage\logs\laravel.log
```

</details>

---

## 🔒 Security Notes

- 🔑 **Tokens are shared secrets.** Replace the `dev-*` tokens in `.env` before deploying.
- 🌍 **The public ingest token** is embedded in the browser SDK by design (it is rate-limited and can only write events).
- 🚫 **The read token** must never appear in browser JavaScript. The dashboard injects it via the `Authorization` header from a server-side env var or a proxy.
- 🧹 **IP storage is raw in the demo.** In production, truncate IPv4 to `/24` and IPv6 to `/48`, and drop `user_agent` after 30 days.
- ⏱️ **Rate limiting** is 120 requests/min per IP for ingestion.

---

## 📝 Assumptions & Limitations

- ⏳ Analytics is **near-real-time**: aggregates refresh when the queue job runs, not instantly.
- 🔬 No funnel / cohort analysis. The schema supports it, the dashboard does not render it.
- 🍪 No GDPR consent banner. Add one before production.
- 🗄️ Single-table `events` store. Production should partition by month beyond ~10M rows.

---

<div align="center">

Made with ❤️ using Laravel &amp; Next.js

</div>
