# Analytics Demo — Restaurant Tracking System

A small end‑to‑end analytics pipeline built on **Laravel 8.83.29** and **Next.js**, integrated into a demo restaurant app.

- **Browser SDK** captures page views and custom events.
- **Server helper** sends business events (e.g. `order_created`) asynchronously.
- **Ingestion API** validates, throttles and stores events.
- **Pre‑computed daily aggregates** power a fast dashboard.
- **Next.js dashboard** visualises traffic, unique visitors, popular pages and event trends with date‑range filtering.

---

## 📦 Tech Stack

| Layer     | Technology                              |
|-----------|-----------------------------------------|
| Backend   | Laravel **8.83.29**, PHP **7.3+ / 8.0+** |
| Frontend  | Next.js 12 (React 17)                   |
| Charts    | Recharts                                |
| Database  | MySQL / MariaDB (or SQLite)             |
| Queue     | Database driver (default in Laravel 8)  |

---

## ✅ Prerequisites

Make sure you have these installed:

- **PHP ≥ 7.3** (recommended 8.0/8.1)
- **Composer 2.x**
- **Node.js ≥ 14** and **npm ≥ 6**
- **MySQL / MariaDB** (or use SQLite — see below)
- **Git** (optional)

Verify:

```bash
php -v
composer -V
node -v
npm -v
```

---

## 🚀 Setup — Backend (Laravel)

### 1. Install PHP dependencies

```bash
cd C:\analytics-demo
composer install
```

### 2. Configure environment

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

> **Using SQLite instead?** Set `DB_CONNECTION=sqlite`, then create the file:
> ```bash
> type nul > database\database.sqlite
> ```

### 3. Create the database (MySQL)

```bash
mysql -u root -e "CREATE DATABASE analytics_demo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### 4. Run migrations

```bash
php artisan migrate
```

You should see both migrations succeed:

```
✔ 2024_01_01_000001_create_events_table
✔ 2024_01_01_000002_create_daily_event_aggregates_table
```

### 5. Clear caches (do this any time you edit `Kernel.php` or `.env`)

```bash
php artisan config:clear
php artisan route:clear
php artisan cache:clear
composer dump-autoload
```

### 6. Start the backend services

Open **two** terminals inside `C:\analytics-demo`:

**Terminal 1 — web server**

```bash
php artisan serve --port=8000
```

**Terminal 2 — queue worker** (required for `order_created` events and daily aggregates)

```bash
php artisan queue:work --tries=3
```

---

## 🎨 Setup — Frontend (Next.js Dashboard)

```bash
cd C:\analytics-demo\dashboard
npm install
```

Create `.env.local`:

```env
NEXT_PUBLIC_ANALYTICS_READ_TOKEN=dev-read-token
```

Start the dashboard:

```bash
npm run dev
```

---

## 🌐 URLs — Access Points

Once everything is running:

| Service                       | URL                                          | Purpose                                    |
|-------------------------------|----------------------------------------------|--------------------------------------------|
| **Restaurant demo (home)**    | http://127.0.0.1:8000/                       | Landing page — fires `page_view`           |
| **Menu**                      | http://127.0.0.1:8000/menu                   | Menu page — fires `page_view`, `menu_viewed` |
| **Order form**                | http://127.0.0.1:8000/order                  | Place an order — fires `order_created`     |
| **Analytics Dashboard**       | http://127.0.0.1:3000/                       | Visualises captured events                 |
| **Ingestion API**             | `POST http://127.0.0.1:8000/api/events`      | Used by SDK + server helper                |
| **Overview API**              | `GET http://127.0.0.1:8000/api/analytics/overview` | Dashboard data                        |
| **Trend API**                 | `GET http://127.0.0.1:8000/api/analytics/trend`    | Time series                           |
| **Popular Pages API**         | `GET http://127.0.0.1:8000/api/analytics/popular-pages` | Top pages                         |
| **Event Breakdown API**       | `GET http://127.0.0.1:8000/api/analytics/events`   | Event name totals                     |

### API Authentication

All `/api/*` endpoints require a bearer token:

| Endpoint                    | Accepted Tokens                        |
|-----------------------------|----------------------------------------|
| `POST /api/events`          | `ANALYTICS_INGEST_TOKEN` **or** `ANALYTICS_PUBLIC_TOKEN` |
| `GET  /api/analytics/*`     | `ANALYTICS_READ_TOKEN`                 |

**Quick test with curl:**

```bash
# Overview (last 7 days)
curl -H "Authorization: Bearer dev-read-token" ^
     "http://127.0.0.1:8000/api/analytics/overview"

# Send an event via the ingestion API
curl -X POST "http://127.0.0.1:8000/api/events?token=dev-public-token" ^
     -H "Content-Type: application/json" ^
     -d "{\"events\":[{\"event_id\":\"11111111-1111-4111-8111-111111111111\",\"event_name\":\"page_view\",\"properties\":{\"path\":\"/test\"},\"anonymous_id\":\"22222222-2222-4222-8222-222222222222\",\"session_id\":\"33333333-3333-4333-8333-333333333333\",\"page_url\":\"http://127.0.0.1:8000/test\",\"occurred_at\":\"2026-10-26T12:00:00Z\"}]}"
```

Expected responses:

```json
// overview (empty DB)
{"page_views":0,"unique_visitors":0,"sessions":0}

// POST /api/events
{"accepted":1}    // HTTP 202
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

```
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

### Run a single test file

```bash
php artisan test tests/Feature/EventIngestionTest.php
```

### Run a single test method

```bash
php artisan test --filter test_deduplicates_by_event_id
```

### Using PHPUnit directly

```bash
vendor\bin\phpunit --testdox
```

### Test structure

| File                                                | What it covers                                     |
|-----------------------------------------------------|----------------------------------------------------|
| `tests/Feature/EventIngestionTest.php`              | Auth, validation, successful ingestion, deduplication |
| `tests/Feature/AnalyticsQueryTest.php`              | Overview counts, popular pages aggregation          |

### Test environment notes

- Tests use `RefreshDatabase` — every test runs against a **fresh, empty** in‑memory database (SQLite by default via `phpunit.xml`).
- No queue worker is required for tests; `UpdateDailyAggregates` is dispatched but only affects the DB if you assert on aggregates (current tests use raw `Event` rows).
- Tests never hit the real ingestion endpoint — they call `Event::create()` directly. This keeps them fast and deterministic.

---

## 📁 Project Structure

```
analytics-demo/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   │── AnalyticsController.php       # Dashboard queries
│   │   │   │── EventIngestionController.php  # POST /api/events
│   │   │   └── DemoController.php                # Restaurant pages
│   │   ├── Middleware/
│   │   │   └── VerifyAnalyticsToken.php          # Bearer token check
│   │   ├── Requests/
│   │   │   └── IngestEventsRequest.php           # Payload validation
│   │   └── Kernel.php                            # Route middleware aliases
│   ├── Jobs/
│   │   └── UpdateDailyAggregates.php             # Recomputes daily rollups
│   ├── Models/
│   │   ├── Event.php
│   │   └── DailyEventAggregate.php
│   └── Support/
│       └── Analytics.php                         # Server-side helper
├── config/
│   ├── analytics.php                             # Token + endpoint config
│   └── cors.php                                  # Allowed origins
├── database/
│   └── migrations/                               # events + aggregates tables
├── public/
│   └── js/analytics.js                           # Browser SDK
├── resources/views/                              # Blade demo pages
├── routes/
│   ├── api.php                                   # Ingestion + analytics endpoints
│   └── web.php                                   # Demo restaurant routes
├── tests/Feature/
│   ├── EventIngestionTest.php
│   └── AnalyticsQueryTest.php
├── dashboard/                                    # Next.js dashboard
│   ├── lib/api.js
│   ├── pages/index.js
│   ├── next.config.js
│   └── package.json
└── README.md
```

---

## 🔄 Full End-to-End Smoke Test

1. **Start all three processes** in three terminals:

   ```bash
   php artisan serve --port=8000         # terminal 1
   php artisan queue:work --tries=3      # terminal 2
   cd dashboard && npm run dev           # terminal 3
   ```

2. **Visit the restaurant pages** to generate events:
   - http://127.0.0.1:8000/ → `page_view`
   - http://127.0.0.1:8000/menu → `page_view` + `menu_viewed`
   - http://127.0.0.1:8000/order → place an order → `order_created`

3. **Wait ~5 seconds** for the queue worker to run `UpdateDailyAggregates`.

4. **Open the dashboard** http://127.0.0.1:3000:
   - Overview cards show non-zero page views, unique visitors, sessions.
   - Trend chart shows today's spike.
   - Popular Pages lists `/menu` and `/order`.
   - Event Breakdown lists `page_view`, `menu_viewed`, `order_created`.

5. **Verify in the DB**:

   ```bash
   php artisan tinker
   >>> App\Models\Event::count();
   >>> App\Models\DailyEventAggregate::all()->toArray();
   ```

---

## 🛠️ Common Troubleshooting

| Error | Fix |
|-------|-----|
| `Failed opening required 'server.php'` | File missing from project root — restore it (see Laravel 8 skeleton), or use `php artisan serve` |
| `Target class [Illuminate\Http\Middleware\HandleCors] does not exist` | In `app/Http/Kernel.php`, use `\Fruitcake\Cors\HandleCors::class` (Laravel 8) |
| `Target class [analytics.token] does not exist` | Add `'analytics.token' => \App\Http\Middleware\VerifyAnalyticsToken::class` to `$routeMiddleware` |
| `Target class [throttle] does not exist` | Add `'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class` to `$routeMiddleware` |
| `API /api/analytics/overview failed: 500` | Run `php artisan migrate`; confirm `DB_CONNECTION` matches the DB you migrated; check `storage/logs/laravel.log` |
| Events saved but dashboard stays at zero | Start `php artisan queue:work` — aggregates only run via the queue |
| `order_created` not appearing | Ensure the queue worker is running, and `.env` has `QUEUE_CONNECTION=database` |
| CORS error in dashboard | Confirm `config/cors.php` includes `http://localhost:3000`, then `php artisan config:clear` |
| Changes to `Kernel.php` / `.env` not applied | `php artisan config:clear && php artisan route:clear && composer dump-autoload`, then delete `bootstrap/cache/*.php` |

### Diagnostic commands

```bash
# List all API routes with attached middleware
php artisan route:list --path=api

# Confirm the alias is registered (tinker)
php artisan tinker --execute "dump(app('router')->getMiddleware()['throttle'] ?? 'MISSING');"
php artisan tinker --execute "dump(app('router')->getMiddleware()['analytics.token'] ?? 'MISSING');"

# Check tables exist
php artisan tinker --execute "dump(Schema::hasTable('events'), Schema::hasTable('daily_event_aggregates'));"

# Tail the log
type storage\logs\laravel.log
```

---

## 🔒 Security Notes

- **Tokens are shared secrets.** Replace the `dev-*` tokens in `.env` before deploying.
- **Public ingest token** is embedded in the browser SDK — this is by design (it's rate-limited and only allows writing events).
- **Read token** must never appear in browser JavaScript; the dashboard injects it via `Authorization` header from a server-side env var or a proxy.
- **IP storage** is raw in the demo. In production, truncate IPv4 to `/24` and IPv6 to `/48`, and drop `user_agent` after 30 days.
- **Rate limiting** is 120 requests/min per IP for ingestion.

---

## 📝 Assumptions & Limitations

- Analytics is **near-real-time** (aggregates refresh when the queue job runs, not instantly).
- No funnel / cohort analysis — schema supports it, dashboard does not render it.
- No GDPR consent banner — add one before production.
- Single-table `events` store; production should partition by month beyond ~10M rows.

---
#   a n a l y t i c s - d e m o  
 