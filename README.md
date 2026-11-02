# StaffWise HRM

StaffWise HRM is a self-hosted HR and payroll application for UK small businesses. It combines employee records, payroll processing, payslip generation, HMRC liability reporting, holiday tracking and configurable tax rates in a single web application.

The application is built with **Symfony 7.4** (PHP 8.4) and **MySQL 8.4**, and runs entirely on your own infrastructure using Docker. There is no cloud account, no external payroll service and no outbound data sharing: the database, payslips and audit history stay on the machine you run it on.

## Contents

- [Quick Start](#quick-start)
- [How to Use the App](#how-to-use-the-app)
- [Configuration](#configuration)
- [Database Access (phpMyAdmin)](#database-access-phpmyadmin)
- [Data Volumes and Backups](#data-volumes-and-backups)
- [Architecture](#architecture)
- [Payroll Calculation Assumptions](#payroll-calculation-assumptions)
- [Holiday Accrual](#holiday-accrual)
- [Common Commands](#common-commands)
- [Verification and Troubleshooting](#verification-and-troubleshooting)
- [Feature Catalogue](#feature-catalogue)
  - [1. Employee Management](#1-employee-management)
  - [2. Payroll Management](#2-payroll-management)
  - [3. Payslip Generation](#3-payslip-generation)
  - [4. HMRC Liability Reporting](#4-hmrc-liability-reporting)
  - [5. Holiday and Leave Management](#5-holiday-and-leave-management)
  - [6. UK Tax and Statutory Rate Management](#6-uk-tax-and-statutory-rate-management)
  - [7. Business Dashboard](#7-business-dashboard)
  - [8. Security and Data Management](#8-security-and-data-management)

## Quick Start

### Requirements

- Docker Engine with the Docker Compose plugin
- Roughly 1 GB of free disk space for the images and MySQL volume

PHP, Composer and MySQL do **not** need to be installed on the host. Everything runs in containers.

### Start the stack

```bash
docker compose up -d --build
```

The first build installs Composer dependencies in a separate build stage, then starts three services:

| Service | Purpose | Port |
| --- | --- | --- |
| `app` | PHP 8.4 + Apache, document root `public/` | `8067` (host) |
| `phpmyadmin` | phpMyAdmin database browser | `8068` (host) |
| `db` | MySQL 8.4 with a persistent named volume | internal only |

All services use `restart: "no"`, so Docker will not automatically start them again after a host reboot or daemon restart. Run `docker compose up -d` to start the stack when needed.

On startup the app container clears the Symfony cache, applies Doctrine migrations and seeds the default UK **2026/27** tax rates (only when no tax years exist yet). Wait for the `app` service to report healthy/started before opening the app:

```bash
docker compose ps
docker compose logs -f app
```

### Create the administrator PIN

Open <http://localhost:8067> and you are redirected to the one-time `/setup` screen, where you choose a 4–8 digit administrator PIN. The same account can be created or reset from the console:

```bash
docker compose exec -u www-data app php bin/console app:create-admin 2468
```

Replace `2468` with your own PIN. The PIN is hashed with Symfony's password hasher; only the hash is stored. Afterwards sign in at <http://localhost:8067/login>.

> The setup screen is only available while no administrator exists. Once an admin is present, `/setup` redirects to the login page.

### Stop and restart

```bash
docker compose start         # start existing containers again (restart policy is "no")
docker compose stop          # stop containers, keep all data
docker compose down          # remove containers, keep named volumes
docker compose down -v       # remove containers AND delete database/vendor/cache volumes
```

Because every service uses `restart: "no"`, containers stay stopped after a host reboot or Docker daemon restart. Use `docker compose up -d` (recreate/reconcile) or `docker compose start` (start existing containers) to bring the stack back.

`docker compose down -v` is destructive — it deletes all employees, payroll runs, payslips and tax years.

## How to Use the App

Typical first-run flow:

1. **Tax & rates** (`/tax-years`) — confirm the seeded `2026/27` rates are active, or create another tax year.
2. **Employees** (`/employees/new`) — add staff with pay rate, pay frequency, tax code, NI category, pension and student loan settings.
3. **Payroll** (`/payroll/new`) — create a weekly or monthly run for a tax year and period.
4. **Enter hours** — on the payroll run page, set hours worked, overtime hours and any bonus for each employee and recalculate.
5. **Review** — check PAYE, NI, pension, student loan, net pay and employer cost.
6. **Finalise** — finalising locks the run, generates the HMRC liability figures and posts holiday accrual to each employee's ledger.
7. **Payslips** (`/payslips/{id}`) — view or open the print view (`/payslips/{id}/print`) to print or save as PDF from the browser.
8. **HMRC** (`/hmrc`) — review per-period and per-tax-year liabilities, payment references and due dates.
9. **Holidays** (`/holidays/{employeeId}`) — book leave, mark it taken, cancel it, or make manual balance adjustments.

Useful URLs:

| URL | Screen |
| --- | --- |
| `/` | Dashboard |
| `/employees` | Employee list and records |
| `/payroll` | Payroll run history |
| `/payroll/new` | Create a payroll run |
| `/payslips/{id}` | Payslip detail |
| `/payslips/{id}/print` | Print-ready payslip |
| `/hmrc` | HMRC liability overview |
| `/holidays` | Holiday balances |
| `/tax-years` | UK tax and statutory rates |
| `/settings` | PIN change and security |
| `/settings/audit` | Administrative audit trail |

Sessions expire after **15 minutes** of inactivity and the next page load redirects to the login screen. You can also lock immediately from the navigation bar (`/lock`).

## Configuration

Docker Compose reads these optional environment variables:

| Variable | Default | Meaning |
| --- | --- | --- |
| `APP_PORT` | `8067` | Host port mapped to the Symfony app |
| `PHPMYADMIN_PORT` | `8068` | Host port mapped to phpMyAdmin |
| `APP_SECRET` | built-in development value | Symfony application secret |
| `RUN_MIGRATIONS` | `1` | Run cache clear, migrations and rate seeding on container start |

The database connection is defined in `compose.yaml`:

```text
mysql://staffwise:staffwise@db:3306/staffwise?serverVersion=8.4&charset=utf8mb4
```

For anything other than local evaluation, change the MySQL credentials in `compose.yaml` and set a strong `APP_SECRET`. The defaults are development convenience values and are committed to the repository.

## Database Access (phpMyAdmin)

phpMyAdmin is included as an optional database browser for inspecting and exporting the `staffwise` schema:

- **URL:** <http://localhost:8068>
- **Server:** `db`
- **Username:** `staffwise`
- **Password:** `staffwise`

The container is configured with `PMA_HOST=db`, `PMA_USER` and `PMA_PASSWORD`, so it connects to the MySQL service automatically and lands on the `staffwise` database. The connection is internal to the Compose network; only the phpMyAdmin web interface is published, on host port **8068**.

The image is pinned to `phpmyadmin:latest` in `compose.yaml`; change it to a specific tag if you need reproducible builds. Remove the `phpmyadmin` service or stop just that container if you do not want a database browser exposed:

```bash
docker compose stop phpmyadmin
```

## Data Volumes and Backups

All persistent state lives in named Docker volumes:

| Volume | Contents |
| --- | --- |
| `staffwise-hrm_db_data` | MySQL data directory (employees, payroll, tax years, audit log) |
| `staffwise-hrm_vendor_data` | Composer dependencies |
| `staffwise-hrm_var_data` | Symfony cache, logs and share directory |

Create a SQL backup without stopping the app:

```bash
docker compose exec -T db mysqldump \
  -ustaffwise -pstaffwise --single-transaction --no-tablespaces \
  --default-character-set=utf8mb4 staffwise \
  > staffwise-$(date +%F).sql
```

Restore into an empty database:

```bash
docker compose exec -T db mysql \
  -ustaffwise -pstaffwise --default-character-set=utf8mb4 staffwise \
  < staffwise-2026-11-01.sql
```

Copy the dump somewhere safe; `docker compose down -v` and volume removal are permanent.

## Architecture

```text
src/
├── Command/           CreateAdminCommand, SeedRatesCommand
├── Controller/        Dashboard, Employee, Payroll, Payslip, Hmrc, Holiday,
│                      TaxYear, Security, Settings
├── Entity/            AdminUser, AuditLog, Employee, HolidayBooking,
│                      HolidayLedgerEntry, PayrollRun, Payslip, TaxYear
├── EventSubscriber/   AutoLockSubscriber (15-minute inactivity lock)
├── Form/              EmployeeType, HolidayBookingType, HolidayAdjustmentType,
│                      PayrollRunType, TaxYearType
├── Migrations/        Version20261101231237 (full initial schema)
├── Repository/        Doctrine repositories for every aggregate
└── Service/           PayrollCalculator, PayrollResult, HolidayService,
                       AuditLogger, TaxYearSeeder
```

Key design points:

- **Doctrine ORM** maps all domain entities; schema changes are versioned through Doctrine Migrations.
- **Money is stored as `DECIMAL`** and calculated in PHP floats rounded to two decimals per stage. Values are never persisted as strings.
- **Payroll runs** aggregate `Payslip` rows and cache their own totals (`grossTotal`, `payeTotal`, `employeeNiTotal`, `employerNiTotal`, `studentLoanTotal`, `netTotal`, `employerCostTotal`) plus the applied Employment Allowance. Totals are recomputed whenever a payslip is recalculated.
- **Draft vs finalised** — only draft runs can be edited or deleted. Finalising sets a timestamp, locks the payslips, makes payslips printable and posts holiday accrual.
- **Holiday ledger** — every accrual, booking, cancellation and manual adjustment is an immutable `HolidayLedgerEntry`, so balances are always derived from history rather than overwritten.
- **Tax years are data, not code** — PAYE bands, NI thresholds/rates, pension bands, student loan thresholds and holiday accrual rate all come from the active `TaxYear` record.
- **Audit logging** — employee, payroll, holiday, tax-year, PIN and setup actions are written to `AuditLog` and shown at `/settings/audit`.
- **CSRF** — all mutating forms are protected with session-backed CSRF tokens.
- **PIN authentication** — a single administrator account (`admin`) authenticates via Symfony `form_login`; the numeric PIN is the credential and is hashed at rest.

## Payroll Calculation Assumptions

`App\Service\PayrollCalculator` computes each payslip from the employee's hourly rate, entered hours, overtime hours, bonus and the active tax year.

- **Basic pay** = `hours worked × hourly rate`
- **Overtime pay** = `overtime hours × hourly rate × 1.5`
- **Gross pay** = basic + overtime + bonus
- Thresholds are **annualised** (`period gross × periods per year`, 52 weekly or 12 monthly), tax/NI/etc. are computed on the annual figure, then **de-annualised** by dividing by the same periods. This is the "week 1 / month 1" non-cumulative approach, so each run is self-contained and reproducible.
- **PAYE** uses the tax code's allowance where recognised (`1257L`, `K…`, `BR`, `D0`, `D1`, `NT`, `0T`), applies the 50p-per-£1 allowance taper above £100,000, then taxes the basic, higher and additional bands. Band widths are derived from the configured basic/higher limits minus the personal allowance.
- **Employee NI** charges the main rate between the primary threshold and upper earnings limit, then the upper rate above it. Category `C` is treated as zero employee NI; all other categories use the standard rate.
- **Employer NI** charges the employer rate above the employer threshold.
- **Pension** applies only to opted-in employees, on qualifying earnings between the configured lower and upper bands. Employee/employer percentages fall back from the employee record to the tax year default.
- **Student loans** use the plan threshold and rate from the tax year: Plan 1, 2, 4, 5 and Postgraduate; `none` means no deduction.
- **Net pay** = gross − PAYE − employee NI − employee pension − student loan.
- **Employer cost** = gross + employer NI + employer pension.
- **Employment Allowance** is applied at run level to the employer NI portion of the HMRC liability, not per payslip.
- All monetary results are clamped at zero after calculation, so rounding can never produce a negative deduction or net pay.

## Holiday Accrual

- Entitlement is based on `contracted weekly hours × holiday entitlement weeks`.
- Accrual is posted when a payroll run is **finalised**, using `hours worked × holiday accrual rate` (default 12.07%) from the tax year.
- Bookings reserve hours; marking a booking **taken** converts the reservation to used leave; **cancelling** restores the balance.
- Manual adjustments accept positive or negative hours and require a reason.
- A booking that exceeds the available balance is still saved but flagged as awaiting further accrual, rather than silently blocked.
- Balances shown on the dashboard and holiday pages are derived from the ledger.

## Common Commands

Run console commands as `www-data` so cache and log files stay writable:

```bash
# Apply migrations
docker compose exec -u www-data app php bin/console doctrine:migrations:migrate --no-interaction

# Create or reset the administrator PIN
docker compose exec -u www-data app php bin/console app:create-admin 2468

# Seed default UK rates (no-op when tax years already exist)
docker compose exec -u www-data app php bin/console app:seed-rates

# Validate entity mapping and database schema
docker compose exec -u www-data app php bin/console doctrine:schema:validate

# Lint Twig templates
docker compose exec -u www-data app php bin/console lint:twig templates

# Clear the production cache
docker compose exec -u www-data app php bin/console cache:clear
```

PHP, config, Twig and service changes are picked up from the bind mount. Because the app runs with `APP_ENV=prod`, restart the container after changing PHP or configuration so the cached container is rebuilt:

```bash
docker compose restart app
```

After changing `composer.json`/`composer.lock`, rebuild the vendor volume:

```bash
docker compose down
docker volume rm staffwise-hrm_vendor_data
docker compose build --no-cache
docker compose up -d
```

## Verification and Troubleshooting

Quick health checks:

```bash
docker compose ps
docker compose logs --tail=100 app
curl -s -o /dev/null -w '%{http_code}\n' http://localhost:8067/login
curl -s -o /dev/null -w '%{http_code}\n' http://localhost:8068/
```

Expected results:

- `docker compose ps` shows `app`, `db` and `phpmyadmin` running, with `db` healthy.
- `curl` against `http://localhost:8067/login` returns `200`.
- `curl` against `http://localhost:8068/` returns `200` (phpMyAdmin login page).
- `app` logs show no `FATAL`, `Exception` or deprecation entries after startup.

Common issues:

| Symptom | Fix |
| --- | --- |
| `503` / "Service Unavailable" | MySQL not healthy yet; wait for the health check or check `docker compose logs db`. |
| Port `8067` or `8068` already in use | Start with other ports: `APP_PORT=8077 PHPMYADMIN_PORT=8078 docker compose up -d`. |
| Redirected to `/setup` repeatedly | No admin exists yet; complete setup or run `app:create-admin`. |
| Login rejects every PIN | Reset it with `app:create-admin <new-pin>`. |
| Changes not visible | `docker compose restart app` to rebuild the prod cache. |
| `Class ... not found` after dependency changes | Rebuild the `vendor_data` volume (see above). |
| Stack did not come back after reboot | Expected with `restart: "no"`; run `docker compose up -d` or `docker compose start`. |
| phpMyAdmin shows "Cannot log in to the MySQL server" | Ensure `db` is healthy and `PMA_HOST=db`; check `docker compose logs phpmyadmin`. |

## Feature Catalogue

### 1. Employee Management

**Centralised Employee Records**

Maintain a structured digital record of every employee, including personal information, contact details, employment history and payroll settings.

- Employee profile creation and management.
- Personal and contact information.
- National Insurance (NI) number management.
- Job title, department and employment dates.
- Employee status tracking (active/inactive).
- Individual hourly pay rates.
- Tax code and National Insurance category.
- Pension and student loan configuration.
- Weekly or monthly pay frequency.
- Contracted weekly hours and holiday entitlement weeks.

### 2. Payroll Management

**Automated Payroll Calculation**

Calculate employee earnings, statutory deductions and take-home pay using configurable UK payroll rates.

- Weekly and monthly payroll processing.
- Working hours and overtime entry.
- Bonus and additional payment calculations.
- Gross salary calculation.
- PAYE income tax calculation.
- Employee and employer National Insurance.
- Workplace pension contributions.
- Student loan deductions.
- Net salary and employer employment cost.
- Payroll preview before finalisation.
- Payroll history and audit records.
- Employment Allowance applied at run level.

### 3. Payslip Generation

**Digital Payslips**

Generate individual payslips containing a transparent breakdown of employee earnings and deductions for each payroll period.

- Individual employee payslip generation.
- Gross earnings breakdown (basic, overtime, bonus).
- PAYE and NI deductions.
- Pension and student loan deductions.
- Net take-home pay.
- Employer contribution information.
- Print-ready payslip documents.
- Save payslips as PDF using the browser's print dialog.

### 4. HMRC Liability Reporting

**HMRC Payment Overview**

Provide a consolidated view of payroll-related tax and statutory liabilities, helping business owners understand their payment obligations.

- PAYE liability reporting.
- Employee National Insurance contributions.
- Employer National Insurance contributions.
- Student loan deductions.
- Employment Allowance adjustments.
- Total HMRC liability per payroll period.
- Payment reference and due-date information.
- Historical liability reports by tax year.

### 5. Holiday and Leave Management

**Employee Holiday Tracking**

Monitor employee holiday entitlement, accrued leave, booked holidays and remaining balances through an integrated holiday ledger.

- Holiday entitlement configuration.
- Automatic holiday accrual based on hours worked.
- Holiday booking by date range.
- Accrued, booked, taken and remaining balance tracking.
- Holiday pay calculations via payroll.
- Leave cancellation and balance restoration.
- Manual holiday balance adjustments.
- Individual employee holiday history.

### 6. UK Tax and Statutory Rate Management

**Configurable Tax and Payroll Rules**

Manage changing UK payroll regulations through effective-dated rate configurations rather than hard-coded calculation values.

- PAYE income tax bands and thresholds.
- Personal allowance configuration.
- National Insurance thresholds and rates.
- Pension contribution thresholds.
- Student loan repayment thresholds.
- Statutory holiday accrual rates.
- Employment Allowance configuration.
- Tax-year-specific rate management.
- Rate validation before activation.
- Historical rate versioning and audit logs.

### 7. Business Dashboard

**Centralised Business Overview**

Access essential workforce and payroll information from a single dashboard.

- Total employee headcount.
- Upcoming payroll dates.
- Current tax-rate configuration.
- Recent payroll summaries.
- Latest and active-tax-year HMRC liability.
- Employee holiday balances requiring attention.
- Quick access to employee and payroll management.

### 8. Security and Data Management

**Privacy-Focused Data Management**

Keep employee and payroll information on your own infrastructure, with application-level access protection and a security-focused data architecture.

- Administrator PIN authentication.
- Automatic application locking after inactivity.
- Self-hosted MySQL database in Docker.
- Hashed PIN storage.
- Employee data protection considerations.
- Administrative activity audit trail.
- No external services or mandatory cloud account.
