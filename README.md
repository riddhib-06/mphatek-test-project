# Mphatek - Technical Assessment

A small Laravel 12 website with:

1. FD Calculator
2. SIP Calculator (monthly / quarterly / yearly)
3. Product-wise lead tracking in GA4 with lead source tracking
4. Dynamic XML sitemap

No Node/npm is needed. The CSS and JS are plain files in `public/`.

## Setup

Uses MySQL (XAMPP). Start MySQL in the XAMPP control panel, then:

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Create the database `mphatek_test` (utf8mb4), then create the tables in **one** of these ways:

- **Laravel migrations (recommended):** `php artisan migrate --seed`
- **SQL file:** import `database/sql/mphatek_test_full.sql` in phpMyAdmin (tables + sample data),
  or `database/sql/mphatek_test_schema.sql` for empty tables only.

```bash
php artisan serve
```

Open http://127.0.0.1:8000

DB settings are the `DB_*` values in `.env` (default: `root`, no password, `127.0.0.1:3306`).
Tests use an in-memory SQLite database, so they don't touch the MySQL data.

Run tests:

```bash
php artisan test
```

## Project structure

```
app/
  Http/Controllers/
    PageController.php        home page + all CMS pages (products, blogs, branches, static)
    CalculatorController.php  FD & SIP pages and their calculate endpoints
    LeadController.php        saves enquiry form, lead report
    SitemapController.php     sitemap index + one sitemap per page type
  Http/Middleware/
    CaptureLeadSource.php     remembers utm_source / referrer in the session
  Models/
    Page.php                  every page of the site lives in one table
    FdRate.php                FD interest rate slabs
    Lead.php                  enquiries
  Services/
    FdCalculator.php          FD formulas
    SipCalculator.php         SIP formula
database/
  migrations/                 pages, fd_rates, leads tables
  seeders/                    sample pages and FD rates
  sql/                        MySQL export: mphatek_test_schema.sql (structure), mphatek_test_full.sql (structure + data)
resources/views/
  layouts/app.blade.php       main layout
  partials/ga4.blade.php      GA4 tag + events
  partials/enquiry-form.blade.php
  calculators/fd.blade.php
  calculators/sip.blade.php
  sitemap/*.blade.php         XML templates
public/
  css/style.css
  js/calculator.js            sends the form via fetch and shows the result
routes/web.php
tests/
```

## 1. FD Calculator

URL: `/calculators/fd-calculator`

Inputs: deposit amount, tenure (days / months / years), customer type (general / senior),
interest rate (auto or manual) and payout type (cumulative / monthly / quarterly).

How it works:

- On every change, `public/js/calculator.js` posts the form to the same URL.
- `CalculatorController@calculateFd` validates the input, converts the tenure into days
  and gets the rate.
- **Auto rate**: looked up from the `fd_rates` table by tenure and customer type
  (`FdRate::forTenure()`). The rate table is also shown on the page. To change the rates, edit
  the table or `FdRateSeeder`.
- **Manual rate**: the rate typed by the user is used.
- `FdCalculator` does the maths:
  - Cumulative, 6 months or more: compounded quarterly, `A = P × (1 + r/400)^(4 × years)`
  - Cumulative, under 6 months: simple interest, `I = P × r × years / 100`
  - Non-cumulative: simple interest paid out every month/quarter.
    Payout = `P × r / 100 / 12` (monthly) or `/ 4` (quarterly). Principal is returned on maturity.

Rules: tenure 7 days - 10 years, monthly payout needs at least 1 month, quarterly at least 3 months.

## 2. SIP Calculator

URL: `/calculators/sip-calculator`

Inputs: amount, frequency (monthly / quarterly / yearly), duration (years or months),
expected annual return.

Formula (`SipCalculator`):

```
i  = annual rate / 100 / instalments per year   (12, 4 or 1)
n  = number of instalments in the duration
FV = P × [((1 + i)^n − 1) / i] × (1 + i)
```

Invested = `P × n`, returns = `FV − invested`.
Example: ₹5,000 monthly for 1 year at 12% = ₹64,046.64.

## 3. Product-wise lead tracking (GA4) + lead source

Every product page (`/products/{slug}`) has an enquiry form.

**Lead source** - `CaptureLeadSource` middleware runs on every page visit:

- If the URL has `utm_source`, it saves utm_source / utm_medium / utm_campaign in the session.
- Otherwise, on the first visit, it saves the referring website (e.g. `google.com`) or `direct`.

When the form is submitted, `LeadController@store` saves the lead in the `leads` table with the
product name and the source data, so it is stored even without GA4.

**GA4 events** (`resources/views/partials/ga4.blade.php`):

| Event | When | Parameters |
|---|---|---|
| `view_product` | product page is opened | product_name, product_category |
| `generate_lead` | enquiry submitted successfully | product_name, product_category, lead_source, lead_medium, lead_campaign |

The `generate_lead` event is fired on the page that loads after the redirect, so it is only sent
when the lead was actually saved (not on a failed validation).

**GA4 setup:**

1. Put your measurement id in `.env`: `GA_MEASUREMENT_ID=G-XXXXXXXXXX`
2. GA4 → Admin → Custom definitions → create event-scoped custom dimensions for
   `product_name`, `product_category`, `lead_source`, `lead_medium`, `lead_campaign`.
3. GA4 → Admin → Events → mark `generate_lead` as a **key event** (conversion).
4. Reports → Engagement → Events → `generate_lead`, add `product_name` as a dimension to see
   leads per product. Compare with `view_product` to see the enquiry rate for each product.
   GA4 also reads UTM tags on its own, so Traffic acquisition reports show source/medium too.

Test with GA4 DebugView or by typing `dataLayer` in the browser console.

Example test link: `/products/home-loan?utm_source=facebook&utm_medium=social&utm_campaign=diwali`

**Lead report** - `/admin/leads` shows lead counts by product, by source and product vs source
from the database. **UTM Builder** - `/admin/utm-builder` makes tracking links for each product.
Both are in the admin area (see below).

**Lead quality checks:** UTM values are lowercased and trimmed (so `Facebook` = `facebook`),
the same phone for the same product within 24 hours is not saved twice (and `generate_lead` is not
sent again), enquiries are limited to 5 per minute per IP, and only live product pages accept leads.

## 4. Dynamic XML sitemap

| URL | Contains |
|---|---|
| `/sitemap.xml` | sitemap index |
| `/sitemap-products.xml` | product pages |
| `/sitemap-blogs.xml` | blog pages |
| `/sitemap-branches.xml` | branch locator pages |
| `/sitemap-static.xml` | static pages (home, about, contact...) |
| `/sitemap-calculators.xml` | calculator pages |

The sitemap is built from the `pages` table on every request, so adding, editing or deleting a
page shows up straight away. `<lastmod>` comes from the page's `updated_at`.

What is left out and how:

| Excluded | How it is detected |
|---|---|
| 404 pages | `is_published = false` (these pages also return 404) |
| Redirected URLs (301/302) | `redirect_url` is set (the page redirects with `redirect_code`) |
| Noindex pages | `noindex = true` (page also gets `<meta name="robots" content="noindex">`) |
| Duplicate URLs | unique index on `type + slug`, plus `unique()` on the final URL, `/home` redirects to `/` |
| Canonicalized duplicates | `canonical_url` is set and points to a different URL |

The first three are in `Page::scopeIndexable()`, the canonical check is `Page::isCanonical()`.
The seeder adds one example of each so you can see them being skipped.

Try it: open **Admin → Pages → + Add page**, add a blog post, then open `/sitemap-blogs.xml` and the new
post is there. Tick *Noindex*, add a redirect or untick *Published* and it disappears again. The Pages list
shows for every page whether it is in the sitemap, and if not, why.

## Main tables

**pages** - type, title, slug, category, summary, content, is_published, noindex,
canonical_url, redirect_url, redirect_code

**fd_rates** - min_days, max_days, general_rate, senior_rate

**leads** - page_id, product, name, phone, email, source, medium, campaign, referrer, landing_page

## Admin area (CMS)

URL: `/admin` (link "Admin" in the top menu). Login with `ADMIN_USER` / `ADMIN_PASSWORD` from `.env`
(HTTP Basic Auth). If `ADMIN_PASSWORD` is empty the admin area is closed.

| Page | What it does |
|---|---|
| `/admin/pages` | Add, edit and delete pages (products, blogs, branches, static, calculators). Shows sitemap status per page. |
| `/admin/leads` | Lead report |
| `/admin/utm-builder` | Build UTM tracking links |

Checks when saving a page: slug must be lowercase-with-dashes and unique per type (no duplicate URLs),
calculator pages are limited to the calculators that exist in code, static slugs can not clash with site
routes (`admin`, `products`...), redirects can be 301 or 302, and `<script>` tags / `onclick` attributes /
`javascript:` links are removed from the content.
