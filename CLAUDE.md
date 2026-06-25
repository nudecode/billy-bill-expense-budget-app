# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Working with Glen — read this first

Glen is not a professional developer but can read and understand code. He knows enough to follow along and enough to get into trouble.

**Do not just agree with him.** If he suggests something that will cause a problem — a security hole, technical debt, a broken workflow — say so directly and explain why before doing it. He explicitly asked to be challenged.

**Always explain WHY, not just what.** "We need an index on this column" is incomplete. Explain what happens without it: "Without an index, every time the app looks up your payments it has to read every single row in the table — fine with 100 rows, painful with 10,000." Make the consequence real.

**Don't assume gaps are understood.** If something non-obvious is happening, name it and explain it in plain English alongside any code.

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

## Development workflow

Every piece of work follows this path. Do not skip steps.

### 1. Start from a GitHub issue

```bash
gh issue list                        # see what's open
gh issue view 42                     # read the detail before touching any code
```

Never start coding without an issue. The issue is the record of *why* the work exists.

### 2. Create a branch off `staging` (not `main`)

```bash
git checkout staging
git pull origin staging              # make sure you're starting from the latest
git checkout -b feature/42-add-biller-form
```

**Why `staging` not `main`?** `main` is production code. If you branch off `main`, your new work is sitting on top of production — when you PR back it goes straight to live users with no safety net. Branching off `staging` means your work merges into the test environment first.

**Naming:** `feature/42-description` for new things, `fix/42-description` for bugs. The number is the GitHub issue number — it creates a traceable link from code back to the reason it was written.

### 3. Write the code AND the tests together

Don't write all the code first and tests later. Write a failing test, make it pass, move on. This is called TDD (Test-Driven Development) and it matters because:
- It forces you to think about what "done" actually looks like before you start
- It catches bugs at the moment they're introduced, not three weeks later
- It means you have proof the feature works when you open the PR

```bash
php artisan test --filter=BillerTest  # run just your test while working
php artisan test                       # run everything before pushing
```

### 4. Lint before pushing

```bash
./vendor/bin/pint                     # auto-fixes code style
```

Pint enforces consistent formatting across the whole codebase. It's not about preference — it means any future developer (or Claude instance) reading the code doesn't have to mentally parse inconsistent spacing and conventions.

### 5. Push and open a PR to `staging`

```bash
git push -u origin feature/42-add-biller-form
gh pr create --base staging --title "Add biller create/edit form" --body "Closes #42"
```

**Why `--base staging`?** The PR target is `staging`, not `main`. This is the safety net: the code gets reviewed and (eventually) tested on the staging server before it ever touches production.

The `Closes #42` in the body automatically closes the issue when the PR merges — no manual cleanup needed.

### 6. Merge staging → main for production

Once the feature is tested on the staging server:

```bash
gh pr create --base main --head staging --title "Release: biller forms" --body "Merges tested staging into production"
```

### Testing approach

**Unit tests** — test the service layer in isolation (no database, no HTTP):
- `RecurringBillService` date arithmetic: weekly/fortnightly/monthly/quarterly instances for edge cases (month boundaries, end dates, leap years)
- `RecurringIncomeService` same

**Feature tests** — test Livewire components through HTTP (uses test database):
- Each Livewire action (`markPaid`, `save`, `delete`) gets a test asserting the database changed correctly
- Each page gets a smoke test asserting it loads and shows the right data for the authenticated user
- Always test that a user cannot see another user's data (the `user_id` scoping)

Test files live in `tests/Feature/` and `tests/Unit/`. Name them after what they test: `RecurringBillServiceTest.php`, `BillsIndexTest.php`.

## What's not built yet

- CRUD forms (Add/Edit/Delete) for all sections — buttons exist in the UI but are not wired up
- Confirmation modal when changing a recurring bill/income end date (should warn that future instances will be recalculated)
- One-off bill/income entry
- Mark recurring income as received
