# Akaunting — complete feature surface (this fork)

Source: `akaunting/akaunting` @ `31fe947b` (2026-11-19, master), route table dumped from the live
instance with `php artisan route:list --json`.

**613 routes total** — 477 under `{company_id}` (the books), 104 under `/api` (REST + Sanctum),
73 `modals.*` (AJAX dialogs), 36 `portal.*` (customer/vendor self-service), 13 `wizard.*` (first-run),
11 `updates.*`, plus auth/install/system.

## Books (company-scoped, 477 routes)

| Area | Routes | What it covers |
|---|---|---|
| Invoices | 16 | create/edit/duplicate/print/PDF/email, payments, reminders, recurring, totals recalc, history |
| Bills | 15 | vendor bills, payments, recurring bills, history |
| Customers | 14 | CRUD, contacts, portal access, statements, balance |
| Vendors | 14 | CRUD, contacts, portal access, balance |
| Items | 13 | products & services, SKU, sale/purchase price, tax, category, stock flag |
| Accounts | 15 | bank/cash/credit accounts, transfers in/out, performance, currency, reconcile entry |
| Transactions | 15 | income/expense/transfer, duplicate, email, print, PDF, export, import, split, reconcile, connect to bank feed |
| Transfers | 12 | account-to-account moves, payments link |
| Reconciliations | 7 | statement reconcile sessions against account |
| Recurring invoices | 11 | schedule, interval, auto-send, end conditions |
| Recurring bills | 11 | same for purchases |
| Recurring transactions | 11 | same for banking |
| Categories | 11 | income/expense/transfer/item categories, per-type |
| Taxes | 11 | rates, per-item and per-document, compound |
| Currencies | 10 | multi-currency, rates, default, precision |
| Dashboards | 10 | multiple dashboards, widgets, switch/enable/disable |
| Companies | 11 | create/switch, logo, settings, currency, locale, delete guards |
| Users | 14 | invite, roles, permissions, enable/disable, password reset, delete guards |
| Settings | 17 | company, default, localisation, invoice, email, email templates, module, schedule |
| Reports | 13 | 6 report classes (below), show/print/PDF/export/duplicate/custom fields |
| Apps | 40 | marketplace, install/unzip/copy, enable/disable/uninstall, reviews, releases, API key |
| Portal | 36 | customer/vendor login, own invoices, own payments, profile |
| Bulk actions | 1 | mass delete/enable across list screens |
| Uploads | 5 | attachments, inline preview, download, media library |
| Modals | 73 | all AJAX dialog endpoints (contact/item/tax/account pickers, quick-create) |

## Reports (`app/Reports`)

`ProfitLoss`, `IncomeSummary`, `ExpenseSummary`, `IncomeExpenseSummary`, `TaxSummary`, `DiscountSummary`
— each renderable as HTML, print, PDF, CSV/Excel export, and duplicable into a saved custom report
(`reports.*` includes `duplicate`, `fields`, `export`, `pdf`, `print`). Report cache TTL 24 h
(`REPORT_CACHE_ENABLED`, `REPORT_CACHE_TTL`).

## Dashboard widgets (`app/Widgets`)

`AccountBalance`, `BankFeeds`, `CashFlow`, `Currencies`, `ExpensesByCategory`, `Payables`,
`ProfitLoss`, `Receivables` — per-dashboard layout, sortable, enable/disable.

## REST API (`/api`, 104 routes)

Sanctum bearer auth, full CRUD over every document type plus settings, contacts, items, accounts,
transactions, reports; search, bulk export. `/api` is the same codebase — nothing gated.

## Modules

Shipped in the image: `offline-payments`, `paypal-standard` (both enabled). Module system supports
local zip install (`apps.unzip` → `apps.copy` → `apps.install`), enable/disable, uninstall, migrations,
own routes for admin and portal. `MODULE_PROTECTED` defaults to `double-entry`, so that module can never
be disabled or removed once present.

## What is *not* in this repo

Akaunting's paid marketplace apps (e.g. payroll, expense approvals, inventory, double-entry) are
separate commercial packages distributed from `api.akaunting.com`. The core repo contains the entire
platform and the module framework; paid apps are add-ons. This fork removes the plan/subscription
enforcement (see `LOCAL-UNLIMITED.md`) so the platform itself is unrestricted and any module zip can be
installed locally, but it does not include the commercial app source.
