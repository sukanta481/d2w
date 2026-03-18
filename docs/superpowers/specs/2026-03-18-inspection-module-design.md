# Inspection Module Design Specification

**Date:** 2026-03-18
**Status:** Approved
**Scope:** New admin section for property inspection income tracking

## Overview

Add a completely separate "Inspection" section to the admin panel. The admin is divided into two income sources: Agency (existing, untouched) and Inspection (new). The inspection section manages property inspection files, tracks payments, and provides earnings overview.

## Database Schema (6 new tables)

### inspection_banks
| Column | Type | Notes |
|--------|------|-------|
| id | INT AUTO_INCREMENT PK | |
| bank_name | VARCHAR(150) NOT NULL | UNIQUE |
| status | ENUM('active','inactive') | Default 'active' |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP |
| updated_at | TIMESTAMP | ON UPDATE CURRENT_TIMESTAMP |

### inspection_branches
| Column | Type | Notes |
|--------|------|-------|
| id | INT AUTO_INCREMENT PK | |
| bank_id | INT NOT NULL FK → inspection_banks(id) | ON DELETE RESTRICT |
| branch_name | VARCHAR(150) NOT NULL | UNIQUE(bank_id, branch_name) |
| status | ENUM('active','inactive') | Default 'active' |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP |
| updated_at | TIMESTAMP | ON UPDATE CURRENT_TIMESTAMP |

### inspection_sources
| Column | Type | Notes |
|--------|------|-------|
| id | INT AUTO_INCREMENT PK | |
| source_name | VARCHAR(150) NOT NULL | UNIQUE |
| phone | VARCHAR(20) | Nullable |
| status | ENUM('active','inactive') | Default 'active' |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP |
| updated_at | TIMESTAMP | ON UPDATE CURRENT_TIMESTAMP |

### inspection_payment_modes
| Column | Type | Notes |
|--------|------|-------|
| id | INT AUTO_INCREMENT PK | |
| mode_name | VARCHAR(100) NOT NULL | UNIQUE. e.g. Cash, UPI, Bank Transfer |
| status | ENUM('active','inactive') | Default 'active' |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP |
| updated_at | TIMESTAMP | ON UPDATE CURRENT_TIMESTAMP |

### inspection_my_accounts
| Column | Type | Notes |
|--------|------|-------|
| id | INT AUTO_INCREMENT PK | |
| account_name | VARCHAR(150) NOT NULL | Display label |
| bank_name | VARCHAR(150) NOT NULL | |
| account_number | VARCHAR(50) NOT NULL | |
| ifsc_code | VARCHAR(20) | Nullable |
| status | ENUM('active','inactive') | Default 'active' |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP |
| updated_at | TIMESTAMP | ON UPDATE CURRENT_TIMESTAMP |

### inspection_files
| Column | Type | Notes |
|--------|------|-------|
| id | INT AUTO_INCREMENT PK | |
| file_number | VARCHAR(20) NOT NULL | Auto-generated: INS-YYYY-NNNN |
| file_date | DATE NOT NULL | |
| file_type | ENUM('office','self') NOT NULL | |
| location | ENUM('kolkata','out_of_kolkata') | NULL when file_type='self' |
| customer_name | VARCHAR(150) NOT NULL | |
| customer_phone | VARCHAR(20) | Nullable |
| property_address | TEXT NOT NULL | |
| property_value | DECIMAL(15,2) NOT NULL | Property valuation amount (informational) |
| bank_id | INT NOT NULL FK → inspection_banks(id) | ON DELETE RESTRICT |
| branch_id | INT NOT NULL FK → inspection_branches(id) | ON DELETE RESTRICT |
| source_id | INT NOT NULL FK → inspection_sources(id) | ON DELETE RESTRICT |
| fees | DECIMAL(10,2) | NULL when file_type='office' |
| report_status | ENUM('draft','final_soft','final_hard') | NULL when file_type='office' |
| payment_mode_id | INT FK → inspection_payment_modes(id) | NULL when file_type='office' |
| payment_status | ENUM('due','paid','partially') | NULL when file_type='office', Default 'due' for self |
| amount | DECIMAL(10,2) | Amount received. Set for 'paid' (=fees) and 'partially' (user-entered). NULL for 'due' and office types |
| paid_to_office | ENUM('paid','due') | NULL when file_type='office' |
| office_amount | DECIMAL(10,2) | Auto: 70% of fees when self, NULL when office |
| commission | DECIMAL(10,2) NOT NULL | Auto: self=30% of fees, office kolkata=300, office out_of_kolkata=350 |
| extra_amount | DECIMAL(10,2) DEFAULT 0 | Optional, manually entered, all file types |
| gross_amount | DECIMAL(10,2) NOT NULL | commission + extra_amount. Always recomputed on save |
| received_account_id | INT FK → inspection_my_accounts(id) | Nullable, ON DELETE SET NULL |
| notes | TEXT | Nullable |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP |
| updated_at | TIMESTAMP | ON UPDATE CURRENT_TIMESTAMP |

**Indexes:** file_number (UNIQUE), file_type, payment_status, bank_id, source_id, file_date

## Business Rules

### File Type: Office
- Location dropdown ENABLED (kolkata / out_of_kolkata)
- Fields set to NA (disabled + NULL in DB): fees, report_status, payment_mode, payment_status, amount, paid_to_office, office_amount
- Commission: ₹300 if kolkata, ₹350 if out_of_kolkata
- Gross Amount = commission + extra_amount

### File Type: Self
- Location dropdown DISABLED (NULL in DB)
- All fields enabled: fees, report_status, payment_mode, payment_status, amount, paid_to_office
- Commission: 30% of fees
- Office amount: 70% of fees
- Gross Amount = commission + extra_amount

### Payment Status (Self files only)
- "due" → amount field DISABLED, amount = NULL in DB
- "paid" → amount field DISABLED, amount auto-set = fees
- "partially" → amount field ENABLED, user enters partial amount received

### Extra Amount
- Always optional, manually entered, applies to both office and self
- Gross Amount = commission + extra_amount (auto-calculated live, recomputed on every save)

### File Number Generation
- Format: INS-YYYY-NNNN (e.g. INS-2026-0001)
- Auto-generated on create, sequential per year

### Branch-Bank Validation
- Files form: branch dropdown is cascading — filtered by selected bank (JS fetch)
- Server-side: validate that selected branch belongs to selected bank before save

### Master Deletion Protection
- Masters (banks, branches, sources, payment modes) cannot be deleted if referenced by any inspection file
- ON DELETE RESTRICT on all FKs. UI shows error message if deletion blocked
- Soft-delete via status='inactive' is the preferred approach (hides from dropdowns, preserves data)

## Subdirectory Include Path Strategy

Since inspection pages live in `admin/inspection/`, all include paths must go up one level:

```php
// Every inspection page starts with:
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
// ...
$pageTitle = 'Page Name';
$basePath = '../';  // Used by header/footer for asset paths and nav links
include __DIR__ . '/../includes/header.php';
```

### Header/Footer Path Handling
- `header.php` and `footer.php` will use `$basePath` variable (defaults to '' if not set) to prefix all asset URLs and navigation links
- Existing pages don't set `$basePath`, so they get `''` (current behavior preserved)
- Inspection pages set `$basePath = '../'` before including header

### Sidebar Active State Detection
- Existing pages: `basename($_SERVER['PHP_SELF']) === 'leads.php'` (unchanged)
- Inspection pages: `strpos($_SERVER['PHP_SELF'], 'inspection/') !== false && basename(...) === 'files.php'`

## File Structure

```
admin/
├── inspection/
│   ├── index.php          # Dashboard - stats, filters, recent files
│   ├── files.php          # File CRUD - add/edit/delete inspection cases
│   └── masters.php        # Master data CRUD - tabbed (banks, branches, sources, payment modes, accounts)
└── sql/
    └── inspection_tables.sql   # Migration script
```

## Existing File Changes

**ONLY ONE existing file modified:** `admin/includes/header.php`
- Add `$basePath` variable support (defaults to `''` if not set) for asset URLs and nav links
- Add "Inspection" menu section to sidebar navigation with 3 links:
  - Dashboard → `{$basePath}inspection/index.php` (icon: fa-chart-line)
  - Files → `{$basePath}inspection/files.php` (icon: fa-folder-open) with pending payment count badge
  - Masters → `{$basePath}inspection/masters.php` (icon: fa-database)
- Active state for inspection links uses `strpos` check for `inspection/` in path

**`admin/includes/footer.php`** — add `$basePath` support for JS asset paths (minor change).

**No other existing files are changed.**

## Page Designs

### Dashboard (inspection/index.php)
- Includes via `__DIR__ . '/../includes/...'` paths
- **4 stat cards:** Total Files (month), Total Gross Earnings (month) = SUM(gross_amount), Pending Payments count (self files where payment_status='due' or 'partially'), Active Sources count
- **Filter bar:** Month/Year selector, file type filter
- **Source-wise summary table:** source name, file count, total gross earnings
- **Recent files table (last 10):** file_number, date, customer, bank, type, commission, payment status badge

### Masters (inspection/masters.php)
- **5 Bootstrap tabs:** Banks, Branches, Sources, Payment Modes, My Accounts
- Each tab: Add button → Bootstrap modal form, data table with inline edit/delete modals
- Branches tab: bank dropdown filter, shows parent bank name
- Delete checks for dependent files before allowing (shows count if blocked)
- Follows exact same CRUD modal pattern as existing admin pages

### Files (inspection/files.php)
- **Add New File button** → large modal (modal-xl) with form
- **Filter bar:** date range, file type, payment status, bank, source
- **Responsive data table** with key columns, server-side pagination (25 per page)
- **Row actions:** View (detail modal), Edit (prefilled modal), Delete (confirmation modal)
- **Inline JavaScript** for business rule enforcement:
  - file_type change → toggle field states (office disables fees/payment fields, enables location; self does reverse)
  - location change → auto-calc commission (300/350)
  - fees change → auto-calc commission (30%) + office_amount (70%)
  - payment_status change → toggle amount field (only enabled for 'partially')
  - extra_amount change → recalc gross_amount
  - bank change → fetch branches via AJAX, reset branch dropdown
  - All calculations recomputed on every save (server-side validation mirrors JS logic)

## Dashboard Metrics Definition

| Metric | Formula |
|--------|---------|
| Total Files | COUNT(*) for selected month |
| Total Earnings | SUM(gross_amount) for selected month |
| Pending Payments | COUNT(*) WHERE file_type='self' AND payment_status IN ('due','partially') |
| Active Sources | COUNT(DISTINCT source_id) for selected month |

## Tech Stack (No additions)
- PHP 8+ with PDO (existing pattern)
- Bootstrap 5.3 (existing CDN)
- Font Awesome 6.4 (existing CDN)
- jQuery not used — vanilla JS (existing pattern)
- No new CSS/JS files — reuses admin.css and admin.js
