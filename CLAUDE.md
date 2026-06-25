# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this app is

Billy is a personal bill and expense budget tracker. It replaces an MS Access database the user currently uses. It is based on the [HomeBudget app by Anishu](https://www.anishu.com/homebudget.html) which the user currently uses on mobile.

Multi-user (each user sees only their own data via `user_id` scoping on every query). Authentication via Laravel Breeze.

## Stack

- **Laravel 13** + **Livewire 4** + **Alpine.js** + **Tailwind CSS v3** + **Blade**
- **SQLite** locally (`database/database.sqlite`) — swap to **MySQL** for production via `.env`
- **Laravel Boost MCP** installed (`laravel/boost --dev`) — gives database schema, routes, artisan, tinker, and log tools
- Fonts: **Sora** (UI) + **JetBrains Mono** (all financial numbers)

## Essential commands

```bash
# Dev server (run both in separate terminals for hot reload)
php artisan serve --port=8765
npm run dev

# One-shot production build
npm run build

# Reset database with seeded demo data
php artisan migrate:fresh --seed

# Clear stale cache/sessions during development
php artisan cache:clear
php artisan tinker  # interactive PHP REPL against the app

# Code style
./vendor/bin/pint

# Tests
php artisan test
php artisan test --filter=SomeTestName
```

## Demo login

After `migrate:fresh --seed`: `glen@example.com` / `password`

## Architecture

### On-the-fly recurring generation (core concept)

Recurring bills and income are **never pre-stored as instances**. Only the *rule* is stored (`recurring_bills`, `recurring_income` tables with `start_date`, `end_date`, `frequency_id`). When a month is viewed, `RecurringBillService` and `RecurringIncomeService` walk the date range and generate `BillInstance` / `IncomeInstance` DTOs on the fly.

**Do not cache these DTOs** — Eloquent models with loaded relationships don't cleanly serialize/unserialize through PHP's cache serialisation. The services have stub `clearCache()` methods left for future implementation with a plain-array approach.

### Payments as the paid/unpaid source of truth

A recurring bill instance is considered **paid** when a row exists in the `payments` table matching `(recurring_bill_id, recurring_bill_date)`. This composite key is the natural identifier for "which occurrence was paid." The service indexes payments by `"{id}_{date}"` for O(1) lookup per instance.

For one-off bills, `one_off_bills.is_paid` + `date_paid` are the source of truth.

### Livewire component pattern

All pages are **Livewire full-page components** (`app/Livewire/`) using the `#[Layout('layouts.app')]` attribute. The layout receives `['title' => '...']` via the `render()` return chain:

```php
return view('livewire.dashboard', compact(...))->layout('layouts.app', ['title' => 'Dashboard']);
```

Month navigation is handled by each component individually via `#[Url(as: 'y')]` / `#[Url(as: 'm')]` public properties and `previousMonth()` / `nextMonth()` methods — the state lives in the URL so the browser back button works.

### Table naming gotcha

Two models have non-standard plural table names and require explicit `$table` declarations:
- `RecurringIncome` → `protected $table = 'recurring_income'` (not `recurring_incomes`)
- `OneOffIncome` → `protected $table = 'one_off_income'` (not `one_off_incomes`)

### Data scoping

Every Eloquent query against user-owned data **must** include `.where('user_id', auth()->id())`. The shared/lookup tables (`categories`, `subcategories`, `frequencies`) have no `user_id`.

### Layout

`resources/views/layouts/app.blade.php` — dark `slate-900` sidebar, frosted-glass topbar, `lg:ml-64` main area. Mobile: sidebar is off-canvas, toggled by Alpine.js `sidebarOpen` on `<body>`. The `<x-nav-item>` Blade component handles active-state highlighting via `request()->routeIs()`.

### Design tokens

Colour-coded by purpose (mirroring the prototype):
- Amber → bills/expenses
- Blue → income
- Emerald → paid/positive
- Red → unpaid/negative
- Violet → net balance
- Teal → billers count

Financial figures always use the `font-mono` class (JetBrains Mono).

## Key file locations

| Concern | Path |
|---|---|
| Route definitions | `routes/web.php` |
| Livewire components (PHP) | `app/Livewire/` |
| Livewire views (Blade) | `resources/views/livewire/` |
| Shared layout | `resources/views/layouts/app.blade.php` |
| Nav item component | `resources/views/components/nav-item.blade.php` |
| Recurring generation logic | `app/Services/RecurringBillService.php` |
| Income generation logic | `app/Services/RecurringIncomeService.php` |
| DTOs | `app/DTOs/BillInstance.php`, `app/DTOs/IncomeInstance.php` |
| Models | `app/Models/` |
| Seeders (real data) | `database/seeders/DemoUserSeeder.php` |

## What's built (as of Jun 25 2026)

- Full UI: green theme, collapsible sidebar (Option A: 256px → 64px icons), mobile bottom nav
- Calendar bills view: week strip (default) + month grid, day filtering, circle colour coding
- Biller totals with A-Z collapsible groups
- CRUD: Billers (full create/edit/delete) — in PR #20
- CRUD: Accounts (full create/edit/delete) — in PR #20
- Global toast notification (dispatch 'toast' event from any Livewire component)
- Font Awesome 6 Free via CDN

## CRUD modal pattern (copy this for every new form)

See `.claude/projects/.../memory/session-state.md` for the complete template.
Short version: Livewire boolean `$showModal` controls `@if($showModal)` render.
Alpine `x-init="$nextTick(() => open = true)"` drives the slide-up animation.
Bottom sheet on mobile, centred on `sm+`. `wire:loading` on save button.

## What's not built yet (issue queue)

| Issue | Work |
|---|---|
| #13 | Payment flow: receipt number, notes, detailed pay modal |
| #15 | CRUD: Recurring Bills (with end-date cascade logic) |
| #16 | CRUD: Recurring Income |
| #17 | CRUD: One-off bills + income (smart autocomplete suggestions) |
| #18 | Edit/delete individual bill instances (design question needed) |
| #7  | User-managed categories (schema decision: add user_id to categories) |
| #11 | Desktop full calendar view (bills in day cells) |
| #12 | Summary cards reflect day/week view context |

## Critical traps

**Never re-add Alpine to app.js.** Livewire 4 owns Alpine. These 3 lines in resources/js/app.js break all wire:click silently:
```js
import Alpine from 'alpinejs'; window.Alpine = Alpine; Alpine.start();
```

**Never use named functions in @php blocks.** PHP can't redeclare them across test runs.
Use closure variables: `$fn = function() {}` not `function fn() {}`

**Never cache BillInstance/IncomeInstance DTOs.** Eloquent models with loaded relationships don't serialize cleanly. Cache::remember will cause "incomplete object" errors on next request.
