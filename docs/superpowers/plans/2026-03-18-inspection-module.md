# Inspection Module Implementation Plan

> **For agentic workers:** REQUIRED: Use superpowers:subagent-driven-development (if subagents available) or superpowers:executing-plans to implement this plan. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build a complete Inspection section in the admin panel for tracking property inspection files, payments, and earnings.

**Architecture:** New `admin/inspection/` subdirectory with 3 pages (dashboard, files, masters) sharing existing admin infrastructure via `$basePath` path variable. 6 new database tables. Only existing file change: `header.php` and `footer.php` for `$basePath` support and sidebar links.

**Tech Stack:** PHP 8+ / PDO / Bootstrap 5.3 / Font Awesome 6.4 / Vanilla JS

**Spec:** `docs/superpowers/specs/2026-03-18-inspection-module-design.md`

---

## Chunk 1: Database & Infrastructure

### Task 1: Create SQL Migration Script

**Files:**
- Create: `admin/sql/inspection_tables.sql`

- [ ] **Step 1: Write the migration SQL**

Create `admin/sql/inspection_tables.sql` with all 6 tables:

```sql
-- Inspection Module Tables
-- Run this migration to add inspection functionality

CREATE TABLE IF NOT EXISTS inspection_banks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bank_name VARCHAR(150) NOT NULL,
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_bank_name (bank_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS inspection_branches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bank_id INT NOT NULL,
    branch_name VARCHAR(150) NOT NULL,
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_bank_branch (bank_id, branch_name),
    FOREIGN KEY (bank_id) REFERENCES inspection_banks(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS inspection_sources (
    id INT AUTO_INCREMENT PRIMARY KEY,
    source_name VARCHAR(150) NOT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_source_name (source_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS inspection_payment_modes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mode_name VARCHAR(100) NOT NULL,
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_mode_name (mode_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS inspection_my_accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    account_name VARCHAR(150) NOT NULL,
    bank_name VARCHAR(150) NOT NULL,
    account_number VARCHAR(50) NOT NULL,
    ifsc_code VARCHAR(20) DEFAULT NULL,
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS inspection_files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    file_number VARCHAR(20) NOT NULL,
    file_date DATE NOT NULL,
    file_type ENUM('office','self') NOT NULL,
    location ENUM('kolkata','out_of_kolkata') DEFAULT NULL,
    customer_name VARCHAR(150) NOT NULL,
    customer_phone VARCHAR(20) DEFAULT NULL,
    property_address TEXT NOT NULL,
    property_value DECIMAL(15,2) NOT NULL,
    bank_id INT NOT NULL,
    branch_id INT NOT NULL,
    source_id INT NOT NULL,
    fees DECIMAL(10,2) DEFAULT NULL,
    report_status ENUM('draft','final_soft','final_hard') DEFAULT NULL,
    payment_mode_id INT DEFAULT NULL,
    payment_status ENUM('due','paid','partially') DEFAULT NULL,
    amount DECIMAL(10,2) DEFAULT NULL,
    paid_to_office ENUM('paid','due') DEFAULT NULL,
    office_amount DECIMAL(10,2) DEFAULT NULL,
    commission DECIMAL(10,2) NOT NULL,
    extra_amount DECIMAL(10,2) DEFAULT 0,
    gross_amount DECIMAL(10,2) NOT NULL,
    received_account_id INT DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_file_number (file_number),
    INDEX idx_file_type (file_type),
    INDEX idx_payment_status (payment_status),
    INDEX idx_bank_id (bank_id),
    INDEX idx_source_id (source_id),
    INDEX idx_file_date (file_date),
    FOREIGN KEY (bank_id) REFERENCES inspection_banks(id) ON DELETE RESTRICT,
    FOREIGN KEY (branch_id) REFERENCES inspection_branches(id) ON DELETE RESTRICT,
    FOREIGN KEY (source_id) REFERENCES inspection_sources(id) ON DELETE RESTRICT,
    FOREIGN KEY (payment_mode_id) REFERENCES inspection_payment_modes(id) ON DELETE RESTRICT,
    FOREIGN KEY (received_account_id) REFERENCES inspection_my_accounts(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed default payment modes
INSERT IGNORE INTO inspection_payment_modes (mode_name) VALUES ('Cash'), ('UPI'), ('Bank Transfer'), ('Cheque');
```

- [ ] **Step 2: Run migration against local database**

```bash
cd c:/xampp/htdocs/d2w
mysql -u root d2w_cms < admin/sql/inspection_tables.sql
```

Expected: Tables created successfully, no errors.

- [ ] **Step 3: Verify tables exist**

```bash
mysql -u root d2w_cms -e "SHOW TABLES LIKE 'inspection_%';"
```

Expected: 6 tables listed.

- [ ] **Step 4: Commit**

```bash
git add admin/sql/inspection_tables.sql
git commit -m "feat: add inspection module database migration (6 tables)"
```

---

### Task 2: Update Header & Footer for $basePath Support

**Files:**
- Modify: `admin/includes/header.php` (lines 10, 17-18, 29, 35, 55, 61, 81, 87, 93, 99, 105, 115, 121, 127, 152, 157, 161)
- Modify: `admin/includes/footer.php` (line 5)

- [ ] **Step 1: Add $basePath default at top of header.php**

At the very top of `admin/includes/header.php` (line 1, before `<!DOCTYPE html>`), add:

```php
<?php if (!isset($basePath)) $basePath = ''; ?>
```

- [ ] **Step 2: Update asset path in header.php**

Line 10 — change:
```html
<link rel="stylesheet" href="assets/css/admin.css">
```
to:
```html
<link rel="stylesheet" href="<?php echo $basePath; ?>assets/css/admin.css">
```

- [ ] **Step 3: Update sidebar brand link and logo**

Line 17 — change `href="index.php"` to `href="<?php echo $basePath; ?>index.php"`
Line 18 — change `src="../assets/images/logo.png"` to `src="<?php echo $basePath; ?>../assets/images/logo.png"`

- [ ] **Step 4: Update all existing sidebar menu links**

Prefix every `href="*.php"` in sidebar nav with `<?php echo $basePath; ?>`:
- Line 29: `href="<?php echo $basePath; ?>index.php"`
- Line 35: `href="<?php echo $basePath; ?>leads.php"`
- Line 55: `href="<?php echo $basePath; ?>clients.php"`
- Line 61: `href="<?php echo $basePath; ?>billing.php"`
- Line 81: `href="<?php echo $basePath; ?>projects.php"`
- Line 87: `href="<?php echo $basePath; ?>ai-agents.php"`
- Line 93: `href="<?php echo $basePath; ?>services.php"`
- Line 99: `href="<?php echo $basePath; ?>testimonials.php"`
- Line 105: `href="<?php echo $basePath; ?>blog.php"`
- Line 115: `href="<?php echo $basePath; ?>settings.php"`
- Line 121: `href="<?php echo $basePath; ?>profile.php"`
- Line 127: `href="<?php echo $basePath; ?>logout.php"`

Also update header-right links:
- Line 152: `href="<?php echo $basePath; ?>leads.php?status=new"`
- Line 157: `href="<?php echo $basePath; ?>settings.php"`
- Line 161: `onclick="window.location.href='<?php echo $basePath; ?>profile.php'"`

- [ ] **Step 5: Add Inspection sidebar section**

After the Billing section (after line 76, before the Content section), insert:

```php
                <div class="menu-section-title">Inspection</div>
                <ul>
                    <li class="menu-item">
                        <a href="<?php echo $basePath; ?>inspection/index.php" class="menu-link <?php echo (strpos($_SERVER['PHP_SELF'], 'inspection/') !== false && basename($_SERVER['PHP_SELF']) === 'index.php') ? 'active' : ''; ?>">
                            <i class="fas fa-chart-line"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="<?php echo $basePath; ?>inspection/files.php" class="menu-link <?php echo (strpos($_SERVER['PHP_SELF'], 'inspection/') !== false && basename($_SERVER['PHP_SELF']) === 'files.php') ? 'active' : ''; ?>">
                            <i class="fas fa-folder-open"></i>
                            <span>Files</span>
                            <?php
                            try {
                                $stmt = $db->query("SELECT COUNT(*) as count FROM inspection_files WHERE file_type = 'self' AND payment_status IN ('due', 'partially')");
                                $pendingCount = $stmt->fetch()['count'];
                                if ($pendingCount > 0) {
                                    echo '<span class="menu-badge bg-danger">' . $pendingCount . '</span>';
                                }
                            } catch(Exception $e) {}
                            ?>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="<?php echo $basePath; ?>inspection/masters.php" class="menu-link <?php echo (strpos($_SERVER['PHP_SELF'], 'inspection/') !== false && basename($_SERVER['PHP_SELF']) === 'masters.php') ? 'active' : ''; ?>">
                            <i class="fas fa-database"></i>
                            <span>Masters</span>
                        </a>
                    </li>
                </ul>
```

- [ ] **Step 6: Update footer.php asset path**

In `admin/includes/footer.php`, line 5 — change:
```html
<script src="assets/js/admin.js"></script>
```
to:
```html
<script src="<?php echo isset($basePath) ? $basePath : ''; ?>assets/js/admin.js"></script>
```

- [ ] **Step 7: Verify existing admin pages still work**

Open in browser:
- `http://localhost/d2w/admin/index.php` — should load normally, sidebar shows new Inspection section
- `http://localhost/d2w/admin/leads.php` — should work unchanged
- Check that all sidebar links still navigate correctly

- [ ] **Step 8: Commit**

```bash
git add admin/includes/header.php admin/includes/footer.php
git commit -m "feat: add basePath support and inspection sidebar links"
```

---

### Task 3: Create Inspection Directory

**Files:**
- Create: `admin/inspection/` directory

- [ ] **Step 1: Create the directory**

```bash
mkdir -p c:/xampp/htdocs/d2w/admin/inspection
```

- [ ] **Step 2: No commit needed** (empty dirs aren't tracked by git)

---

## Chunk 2: Masters Page

### Task 4: Build Masters Page (inspection/masters.php)

**Files:**
- Create: `admin/inspection/masters.php`

This is the largest single file. It contains 5 tabbed sections (Banks, Branches, Sources, Payment Modes, My Accounts), each with their own Add/Edit/Delete modals. Follows the exact same pattern as `admin/leads.php`.

- [ ] **Step 1: Write the PHP backend (POST handlers + data fetching)**

Create `admin/inspection/masters.php`. The top section handles all CRUD operations:

```php
<?php
require_once __DIR__ . '/../includes/auth.php';
$auth->requireLogin();

require_once __DIR__ . '/../config/database.php';
$database = new Database();
$db = $database->connect();

$successMessage = '';
$errorMessage = '';

// ===== BANKS CRUD =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_bank'])) {
    try {
        $stmt = $db->prepare("INSERT INTO inspection_banks (bank_name) VALUES (:name)");
        $stmt->execute([':name' => trim($_POST['bank_name'])]);
        $auth->logActivity($auth->getUserId(), 'create', 'inspection_banks', $db->lastInsertId(), "Added bank: " . $_POST['bank_name']);
        $successMessage = "Bank added successfully!";
    } catch(PDOException $e) {
        $errorMessage = strpos($e->getMessage(), 'Duplicate') !== false ? "Bank name already exists!" : "Error: " . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_bank'])) {
    try {
        $stmt = $db->prepare("UPDATE inspection_banks SET bank_name = :name WHERE id = :id");
        $stmt->execute([':name' => trim($_POST['bank_name']), ':id' => $_POST['bank_id']]);
        $auth->logActivity($auth->getUserId(), 'update', 'inspection_banks', $_POST['bank_id'], "Updated bank");
        $successMessage = "Bank updated successfully!";
    } catch(PDOException $e) {
        $errorMessage = strpos($e->getMessage(), 'Duplicate') !== false ? "Bank name already exists!" : "Error: " . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_bank'])) {
    try {
        $stmt = $db->prepare("DELETE FROM inspection_banks WHERE id = :id");
        $stmt->execute([':id' => $_POST['bank_id']]);
        $auth->logActivity($auth->getUserId(), 'delete', 'inspection_banks', $_POST['bank_id'], "Deleted bank");
        $successMessage = "Bank deleted successfully!";
    } catch(PDOException $e) {
        $errorMessage = strpos($e->getMessage(), 'a]foreign key constraint') !== false
            ? "Cannot delete: this bank is used in inspection files!"
            : "Error: " . $e->getMessage();
    }
}

// ===== BRANCHES CRUD =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_branch'])) {
    try {
        $stmt = $db->prepare("INSERT INTO inspection_branches (bank_id, branch_name) VALUES (:bank_id, :name)");
        $stmt->execute([':bank_id' => $_POST['bank_id'], ':name' => trim($_POST['branch_name'])]);
        $auth->logActivity($auth->getUserId(), 'create', 'inspection_branches', $db->lastInsertId(), "Added branch: " . $_POST['branch_name']);
        $successMessage = "Branch added successfully!";
    } catch(PDOException $e) {
        $errorMessage = strpos($e->getMessage(), 'Duplicate') !== false ? "Branch already exists for this bank!" : "Error: " . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_branch'])) {
    try {
        $stmt = $db->prepare("UPDATE inspection_branches SET bank_id = :bank_id, branch_name = :name WHERE id = :id");
        $stmt->execute([':bank_id' => $_POST['bank_id'], ':name' => trim($_POST['branch_name']), ':id' => $_POST['branch_id']]);
        $auth->logActivity($auth->getUserId(), 'update', 'inspection_branches', $_POST['branch_id'], "Updated branch");
        $successMessage = "Branch updated successfully!";
    } catch(PDOException $e) {
        $errorMessage = strpos($e->getMessage(), 'Duplicate') !== false ? "Branch already exists for this bank!" : "Error: " . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_branch'])) {
    try {
        $stmt = $db->prepare("DELETE FROM inspection_branches WHERE id = :id");
        $stmt->execute([':id' => $_POST['branch_id']]);
        $auth->logActivity($auth->getUserId(), 'delete', 'inspection_branches', $_POST['branch_id'], "Deleted branch");
        $successMessage = "Branch deleted successfully!";
    } catch(PDOException $e) {
        $errorMessage = strpos($e->getMessage(), 'foreign key constraint') !== false
            ? "Cannot delete: this branch is used in inspection files!"
            : "Error: " . $e->getMessage();
    }
}

// ===== SOURCES CRUD =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_source'])) {
    try {
        $stmt = $db->prepare("INSERT INTO inspection_sources (source_name, phone) VALUES (:name, :phone)");
        $stmt->execute([':name' => trim($_POST['source_name']), ':phone' => trim($_POST['phone']) ?: null]);
        $auth->logActivity($auth->getUserId(), 'create', 'inspection_sources', $db->lastInsertId(), "Added source: " . $_POST['source_name']);
        $successMessage = "Source added successfully!";
    } catch(PDOException $e) {
        $errorMessage = strpos($e->getMessage(), 'Duplicate') !== false ? "Source name already exists!" : "Error: " . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_source'])) {
    try {
        $stmt = $db->prepare("UPDATE inspection_sources SET source_name = :name, phone = :phone WHERE id = :id");
        $stmt->execute([':name' => trim($_POST['source_name']), ':phone' => trim($_POST['phone']) ?: null, ':id' => $_POST['source_id']]);
        $auth->logActivity($auth->getUserId(), 'update', 'inspection_sources', $_POST['source_id'], "Updated source");
        $successMessage = "Source updated successfully!";
    } catch(PDOException $e) {
        $errorMessage = strpos($e->getMessage(), 'Duplicate') !== false ? "Source name already exists!" : "Error: " . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_source'])) {
    try {
        $stmt = $db->prepare("DELETE FROM inspection_sources WHERE id = :id");
        $stmt->execute([':id' => $_POST['source_id']]);
        $auth->logActivity($auth->getUserId(), 'delete', 'inspection_sources', $_POST['source_id'], "Deleted source");
        $successMessage = "Source deleted successfully!";
    } catch(PDOException $e) {
        $errorMessage = strpos($e->getMessage(), 'foreign key constraint') !== false
            ? "Cannot delete: this source is used in inspection files!"
            : "Error: " . $e->getMessage();
    }
}

// ===== PAYMENT MODES CRUD =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_payment_mode'])) {
    try {
        $stmt = $db->prepare("INSERT INTO inspection_payment_modes (mode_name) VALUES (:name)");
        $stmt->execute([':name' => trim($_POST['mode_name'])]);
        $auth->logActivity($auth->getUserId(), 'create', 'inspection_payment_modes', $db->lastInsertId(), "Added payment mode: " . $_POST['mode_name']);
        $successMessage = "Payment mode added successfully!";
    } catch(PDOException $e) {
        $errorMessage = strpos($e->getMessage(), 'Duplicate') !== false ? "Payment mode already exists!" : "Error: " . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_payment_mode'])) {
    try {
        $stmt = $db->prepare("UPDATE inspection_payment_modes SET mode_name = :name WHERE id = :id");
        $stmt->execute([':name' => trim($_POST['mode_name']), ':id' => $_POST['mode_id']]);
        $auth->logActivity($auth->getUserId(), 'update', 'inspection_payment_modes', $_POST['mode_id'], "Updated payment mode");
        $successMessage = "Payment mode updated successfully!";
    } catch(PDOException $e) {
        $errorMessage = strpos($e->getMessage(), 'Duplicate') !== false ? "Payment mode already exists!" : "Error: " . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_payment_mode'])) {
    try {
        $stmt = $db->prepare("DELETE FROM inspection_payment_modes WHERE id = :id");
        $stmt->execute([':id' => $_POST['mode_id']]);
        $auth->logActivity($auth->getUserId(), 'delete', 'inspection_payment_modes', $_POST['mode_id'], "Deleted payment mode");
        $successMessage = "Payment mode deleted successfully!";
    } catch(PDOException $e) {
        $errorMessage = strpos($e->getMessage(), 'foreign key constraint') !== false
            ? "Cannot delete: this payment mode is used in inspection files!"
            : "Error: " . $e->getMessage();
    }
}

// ===== MY ACCOUNTS CRUD =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_account'])) {
    try {
        $stmt = $db->prepare("INSERT INTO inspection_my_accounts (account_name, bank_name, account_number, ifsc_code) VALUES (:name, :bank, :acno, :ifsc)");
        $stmt->execute([
            ':name' => trim($_POST['account_name']),
            ':bank' => trim($_POST['account_bank_name']),
            ':acno' => trim($_POST['account_number']),
            ':ifsc' => trim($_POST['ifsc_code']) ?: null
        ]);
        $auth->logActivity($auth->getUserId(), 'create', 'inspection_my_accounts', $db->lastInsertId(), "Added account: " . $_POST['account_name']);
        $successMessage = "Account added successfully!";
    } catch(PDOException $e) {
        $errorMessage = "Error: " . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_account'])) {
    try {
        $stmt = $db->prepare("UPDATE inspection_my_accounts SET account_name = :name, bank_name = :bank, account_number = :acno, ifsc_code = :ifsc WHERE id = :id");
        $stmt->execute([
            ':name' => trim($_POST['account_name']),
            ':bank' => trim($_POST['account_bank_name']),
            ':acno' => trim($_POST['account_number']),
            ':ifsc' => trim($_POST['ifsc_code']) ?: null,
            ':id' => $_POST['account_id']
        ]);
        $auth->logActivity($auth->getUserId(), 'update', 'inspection_my_accounts', $_POST['account_id'], "Updated account");
        $successMessage = "Account updated successfully!";
    } catch(PDOException $e) {
        $errorMessage = "Error: " . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_account'])) {
    try {
        $stmt = $db->prepare("DELETE FROM inspection_my_accounts WHERE id = :id");
        $stmt->execute([':id' => $_POST['account_id']]);
        $auth->logActivity($auth->getUserId(), 'delete', 'inspection_my_accounts', $_POST['account_id'], "Deleted account");
        $successMessage = "Account deleted successfully!";
    } catch(PDOException $e) {
        $errorMessage = strpos($e->getMessage(), 'foreign key constraint') !== false
            ? "Cannot delete: this account is referenced in inspection files!"
            : "Error: " . $e->getMessage();
    }
}

// ===== FETCH ALL DATA =====
try {
    $banks = $db->query("SELECT * FROM inspection_banks ORDER BY bank_name")->fetchAll();
    $branches = $db->query("SELECT b.*, ib.bank_name FROM inspection_branches b JOIN inspection_banks ib ON b.bank_id = ib.id ORDER BY ib.bank_name, b.branch_name")->fetchAll();
    $sources = $db->query("SELECT * FROM inspection_sources ORDER BY source_name")->fetchAll();
    $paymentModes = $db->query("SELECT * FROM inspection_payment_modes ORDER BY mode_name")->fetchAll();
    $accounts = $db->query("SELECT * FROM inspection_my_accounts ORDER BY account_name")->fetchAll();
} catch(PDOException $e) {
    $banks = $branches = $sources = $paymentModes = $accounts = [];
    error_log("Masters fetch error: " . $e->getMessage());
}

$pageTitle = 'Inspection Masters';
$basePath = '../';
include __DIR__ . '/../includes/header.php';
?>
```

- [ ] **Step 2: Write the HTML — page header, alerts, and tab navigation**

Append to the same file after the PHP block:

```html
<div class="admin-content">
    <div class="page-header">
        <h1 class="page-title">Inspection Masters</h1>
        <p class="page-subtitle">Manage banks, branches, sources, payment modes & accounts</p>
    </div>

    <?php if ($successMessage): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?php echo $successMessage; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if ($errorMessage): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?php echo $errorMessage; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="content-card">
        <ul class="nav nav-tabs mb-4" role="tablist">
            <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab-banks">Banks</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-branches">Branches</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-sources">Sources</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-payment-modes">Payment Modes</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-accounts">My Accounts</a></li>
        </ul>

        <div class="tab-content">
```

- [ ] **Step 3: Write Banks tab content**

```html
            <!-- BANKS TAB -->
            <div class="tab-pane fade show active" id="tab-banks">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Banks</h5>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addBankModal">
                        <i class="fas fa-plus me-1"></i>Add Bank
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr><th>Bank Name</th><th>Status</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($banks)): ?>
                                <?php foreach ($banks as $bank): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($bank['bank_name']); ?></td>
                                        <td><span class="badge bg-<?php echo $bank['status'] === 'active' ? 'success' : 'secondary'; ?>"><?php echo ucfirst($bank['status']); ?></span></td>
                                        <td>
                                            <button class="btn btn-sm btn-icon" data-bs-toggle="modal" data-bs-target="#editBankModal<?php echo $bank['id']; ?>"><i class="fas fa-edit"></i></button>
                                            <button class="btn btn-sm btn-icon text-danger" data-bs-toggle="modal" data-bs-target="#deleteBankModal<?php echo $bank['id']; ?>"><i class="fas fa-trash"></i></button>
                                        </td>
                                    </tr>
                                    <!-- Edit Bank Modal -->
                                    <div class="modal fade" id="editBankModal<?php echo $bank['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog"><div class="modal-content"><form method="POST">
                                            <div class="modal-header"><h5 class="modal-title">Edit Bank</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                            <div class="modal-body">
                                                <input type="hidden" name="bank_id" value="<?php echo $bank['id']; ?>">
                                                <div class="mb-3"><label class="form-label">Bank Name</label><input type="text" name="bank_name" class="form-control" value="<?php echo htmlspecialchars($bank['bank_name']); ?>" required></div>
                                            </div>
                                            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" name="update_bank" class="btn btn-primary">Update</button></div>
                                        </form></div></div>
                                    </div>
                                    <!-- Delete Bank Modal -->
                                    <div class="modal fade" id="deleteBankModal<?php echo $bank['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog"><div class="modal-content"><form method="POST">
                                            <input type="hidden" name="delete_bank" value="1"><input type="hidden" name="bank_id" value="<?php echo $bank['id']; ?>">
                                            <div class="modal-header"><h5 class="modal-title">Delete Bank</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                            <div class="modal-body"><p>Delete <strong><?php echo htmlspecialchars($bank['bank_name']); ?></strong>?</p><p class="text-muted mb-0"><i class="fas fa-exclamation-triangle text-warning me-1"></i>Cannot delete if used in files.</p></div>
                                            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-danger">Delete</button></div>
                                        </form></div></div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="3" class="text-center text-muted py-4">No banks added yet</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
```

- [ ] **Step 4: Write Branches tab content**

Same pattern as banks, but with bank_id dropdown (parent relationship). Include a cascading bank filter dropdown above the table. Each row shows bank name + branch name. Add/Edit modals include a bank dropdown select.

- [ ] **Step 5: Write Sources tab content**

Same pattern as banks, with additional `phone` field in the form.

- [ ] **Step 6: Write Payment Modes tab content**

Same pattern as banks, single `mode_name` field.

- [ ] **Step 7: Write My Accounts tab content**

Same pattern but with 4 fields: account_name, bank_name, account_number, ifsc_code.

- [ ] **Step 8: Write the Add Bank modal and close out HTML**

```html
        </div><!-- /tab-content -->
    </div><!-- /content-card -->
</div><!-- /admin-content -->

<!-- Add Bank Modal -->
<div class="modal fade" id="addBankModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content"><form method="POST">
        <div class="modal-header"><h5 class="modal-title">Add Bank</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="mb-3"><label class="form-label">Bank Name</label><input type="text" name="bank_name" class="form-control" required></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" name="add_bank" class="btn btn-primary">Add Bank</button></div>
    </form></div></div>
</div>

<!-- Add Branch Modal -->
<!-- Add Source Modal -->
<!-- Add Payment Mode Modal -->
<!-- Add Account Modal -->
<!-- (Same pattern for each, with appropriate fields) -->

<?php include __DIR__ . '/../includes/footer.php'; ?>
```

- [ ] **Step 9: Verify in browser**

Open `http://localhost/d2w/admin/inspection/masters.php`
- All 5 tabs should render
- Add a test bank, verify it appears
- Add a branch linked to that bank
- Edit and delete should work
- Sidebar should highlight "Masters" as active

- [ ] **Step 10: Commit**

```bash
git add admin/inspection/masters.php
git commit -m "feat: add inspection masters page with 5-tab CRUD"
```

---

## Chunk 3: Files Page

### Task 5: Build Files Page — Backend & AJAX Endpoint (inspection/files.php)

**Files:**
- Create: `admin/inspection/files.php`

- [ ] **Step 1: Write PHP backend — file number generator + POST handlers**

Create `admin/inspection/files.php`:

```php
<?php
require_once __DIR__ . '/../includes/auth.php';
$auth->requireLogin();

require_once __DIR__ . '/../config/database.php';
$database = new Database();
$db = $database->connect();

$successMessage = '';
$errorMessage = '';

// Generate next file number: INS-YYYY-NNNN
function generateFileNumber($db) {
    $year = date('Y');
    $stmt = $db->prepare("SELECT file_number FROM inspection_files WHERE file_number LIKE :prefix ORDER BY id DESC LIMIT 1");
    $stmt->execute([':prefix' => "INS-{$year}-%"]);
    $last = $stmt->fetch();
    if ($last) {
        $num = (int)substr($last['file_number'], -4) + 1;
    } else {
        $num = 1;
    }
    return sprintf("INS-%s-%04d", $year, $num);
}

// Server-side commission/amount calculation
function calculateAmounts($data) {
    $result = [
        'fees' => null,
        'office_amount' => null,
        'commission' => 0,
        'amount' => null,
        'paid_to_office' => null,
        'payment_status' => null,
        'payment_mode_id' => null,
        'report_status' => null,
        'location' => null,
    ];

    if ($data['file_type'] === 'office') {
        $result['location'] = $data['location'];
        $result['commission'] = ($data['location'] === 'kolkata') ? 300 : 350;
    } else {
        // Self
        $fees = floatval($data['fees'] ?? 0);
        $result['fees'] = $fees;
        $result['commission'] = round($fees * 0.30, 2);
        $result['office_amount'] = round($fees * 0.70, 2);
        $result['report_status'] = $data['report_status'] ?? null;
        $result['payment_mode_id'] = !empty($data['payment_mode_id']) ? $data['payment_mode_id'] : null;
        $result['payment_status'] = $data['payment_status'] ?? 'due';
        $result['paid_to_office'] = $data['paid_to_office'] ?? 'due';

        if ($result['payment_status'] === 'paid') {
            $result['amount'] = $fees;
        } elseif ($result['payment_status'] === 'partially') {
            $result['amount'] = floatval($data['amount'] ?? 0);
        }
        // 'due' => amount stays null
    }

    $extra = floatval($data['extra_amount'] ?? 0);
    $result['extra_amount'] = $extra;
    $result['gross_amount'] = round($result['commission'] + $extra, 2);

    return $result;
}

// Handle AJAX: get branches by bank
if (isset($_GET['ajax']) && $_GET['ajax'] === 'branches' && isset($_GET['bank_id'])) {
    header('Content-Type: application/json');
    try {
        $stmt = $db->prepare("SELECT id, branch_name FROM inspection_branches WHERE bank_id = :bank_id AND status = 'active' ORDER BY branch_name");
        $stmt->execute([':bank_id' => $_GET['bank_id']]);
        echo json_encode($stmt->fetchAll());
    } catch(Exception $e) {
        echo json_encode([]);
    }
    exit;
}

// ===== ADD FILE =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_file'])) {
    try {
        $calc = calculateAmounts($_POST);
        $fileNumber = generateFileNumber($db);

        // Validate branch belongs to bank
        $stmt = $db->prepare("SELECT id FROM inspection_branches WHERE id = :bid AND bank_id = :bankid");
        $stmt->execute([':bid' => $_POST['branch_id'], ':bankid' => $_POST['bank_id']]);
        if (!$stmt->fetch()) {
            throw new Exception("Selected branch does not belong to selected bank.");
        }

        $stmt = $db->prepare("INSERT INTO inspection_files
            (file_number, file_date, file_type, location, customer_name, customer_phone, property_address, property_value,
             bank_id, branch_id, source_id, fees, report_status, payment_mode_id, payment_status, amount,
             paid_to_office, office_amount, commission, extra_amount, gross_amount, received_account_id, notes)
            VALUES
            (:file_number, :file_date, :file_type, :location, :customer_name, :customer_phone, :property_address, :property_value,
             :bank_id, :branch_id, :source_id, :fees, :report_status, :payment_mode_id, :payment_status, :amount,
             :paid_to_office, :office_amount, :commission, :extra_amount, :gross_amount, :received_account_id, :notes)");

        $stmt->execute([
            ':file_number' => $fileNumber,
            ':file_date' => $_POST['file_date'],
            ':file_type' => $_POST['file_type'],
            ':location' => $calc['location'],
            ':customer_name' => trim($_POST['customer_name']),
            ':customer_phone' => trim($_POST['customer_phone']) ?: null,
            ':property_address' => trim($_POST['property_address']),
            ':property_value' => floatval($_POST['property_value']),
            ':bank_id' => $_POST['bank_id'],
            ':branch_id' => $_POST['branch_id'],
            ':source_id' => $_POST['source_id'],
            ':fees' => $calc['fees'],
            ':report_status' => $calc['report_status'],
            ':payment_mode_id' => $calc['payment_mode_id'],
            ':payment_status' => $calc['payment_status'],
            ':amount' => $calc['amount'],
            ':paid_to_office' => $calc['paid_to_office'],
            ':office_amount' => $calc['office_amount'],
            ':commission' => $calc['commission'],
            ':extra_amount' => $calc['extra_amount'],
            ':gross_amount' => $calc['gross_amount'],
            ':received_account_id' => !empty($_POST['received_account_id']) ? $_POST['received_account_id'] : null,
            ':notes' => trim($_POST['notes']) ?: null,
        ]);

        $auth->logActivity($auth->getUserId(), 'create', 'inspection_files', $db->lastInsertId(), "Created file {$fileNumber}");
        $successMessage = "File {$fileNumber} created successfully!";
    } catch(Exception $e) {
        $errorMessage = "Error: " . $e->getMessage();
    }
}

// ===== UPDATE FILE =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_file'])) {
    try {
        $calc = calculateAmounts($_POST);

        // Validate branch belongs to bank
        $stmt = $db->prepare("SELECT id FROM inspection_branches WHERE id = :bid AND bank_id = :bankid");
        $stmt->execute([':bid' => $_POST['branch_id'], ':bankid' => $_POST['bank_id']]);
        if (!$stmt->fetch()) {
            throw new Exception("Selected branch does not belong to selected bank.");
        }

        $stmt = $db->prepare("UPDATE inspection_files SET
            file_date = :file_date, file_type = :file_type, location = :location,
            customer_name = :customer_name, customer_phone = :customer_phone,
            property_address = :property_address, property_value = :property_value,
            bank_id = :bank_id, branch_id = :branch_id, source_id = :source_id,
            fees = :fees, report_status = :report_status, payment_mode_id = :payment_mode_id,
            payment_status = :payment_status, amount = :amount, paid_to_office = :paid_to_office,
            office_amount = :office_amount, commission = :commission, extra_amount = :extra_amount,
            gross_amount = :gross_amount, received_account_id = :received_account_id, notes = :notes
            WHERE id = :id");

        $stmt->execute([
            ':file_date' => $_POST['file_date'],
            ':file_type' => $_POST['file_type'],
            ':location' => $calc['location'],
            ':customer_name' => trim($_POST['customer_name']),
            ':customer_phone' => trim($_POST['customer_phone']) ?: null,
            ':property_address' => trim($_POST['property_address']),
            ':property_value' => floatval($_POST['property_value']),
            ':bank_id' => $_POST['bank_id'],
            ':branch_id' => $_POST['branch_id'],
            ':source_id' => $_POST['source_id'],
            ':fees' => $calc['fees'],
            ':report_status' => $calc['report_status'],
            ':payment_mode_id' => $calc['payment_mode_id'],
            ':payment_status' => $calc['payment_status'],
            ':amount' => $calc['amount'],
            ':paid_to_office' => $calc['paid_to_office'],
            ':office_amount' => $calc['office_amount'],
            ':commission' => $calc['commission'],
            ':extra_amount' => $calc['extra_amount'],
            ':gross_amount' => $calc['gross_amount'],
            ':received_account_id' => !empty($_POST['received_account_id']) ? $_POST['received_account_id'] : null,
            ':notes' => trim($_POST['notes']) ?: null,
            ':id' => $_POST['file_id'],
        ]);

        $auth->logActivity($auth->getUserId(), 'update', 'inspection_files', $_POST['file_id'], "Updated file");
        $successMessage = "File updated successfully!";
    } catch(Exception $e) {
        $errorMessage = "Error: " . $e->getMessage();
    }
}

// ===== DELETE FILE =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_file'])) {
    try {
        $stmt = $db->prepare("DELETE FROM inspection_files WHERE id = :id");
        $stmt->execute([':id' => $_POST['file_id']]);
        $auth->logActivity($auth->getUserId(), 'delete', 'inspection_files', $_POST['file_id'], "Deleted file");
        $successMessage = "File deleted successfully!";
    } catch(PDOException $e) {
        $errorMessage = "Error: " . $e->getMessage();
    }
}
```

- [ ] **Step 2: Write data fetching with filters and pagination**

Append after delete handler:

```php
// ===== FILTERS & PAGINATION =====
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 25;
$offset = ($page - 1) * $perPage;

$typeFilter = $_GET['file_type'] ?? '';
$statusFilter = $_GET['payment_status'] ?? '';
$bankFilter = $_GET['bank_id'] ?? '';
$sourceFilter = $_GET['source_id'] ?? '';
$searchQuery = $_GET['search'] ?? '';
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';

try {
    $where = "WHERE 1=1";
    $params = [];

    if ($typeFilter) { $where .= " AND f.file_type = :type"; $params[':type'] = $typeFilter; }
    if ($statusFilter) { $where .= " AND f.payment_status = :status"; $params[':status'] = $statusFilter; }
    if ($bankFilter) { $where .= " AND f.bank_id = :bank"; $params[':bank'] = $bankFilter; }
    if ($sourceFilter) { $where .= " AND f.source_id = :source"; $params[':source'] = $sourceFilter; }
    if ($dateFrom) { $where .= " AND f.file_date >= :dfrom"; $params[':dfrom'] = $dateFrom; }
    if ($dateTo) { $where .= " AND f.file_date <= :dto"; $params[':dto'] = $dateTo; }
    if ($searchQuery) {
        $where .= " AND (f.customer_name LIKE :search OR f.file_number LIKE :search OR f.property_address LIKE :search)";
        $params[':search'] = "%{$searchQuery}%";
    }

    // Count total
    $countStmt = $db->prepare("SELECT COUNT(*) as total FROM inspection_files f {$where}");
    $countStmt->execute($params);
    $totalFiles = $countStmt->fetch()['total'];
    $totalPages = ceil($totalFiles / $perPage);

    // Fetch page
    $query = "SELECT f.*, ib.bank_name, ibr.branch_name, isrc.source_name, ipm.mode_name
              FROM inspection_files f
              LEFT JOIN inspection_banks ib ON f.bank_id = ib.id
              LEFT JOIN inspection_branches ibr ON f.branch_id = ibr.id
              LEFT JOIN inspection_sources isrc ON f.source_id = isrc.id
              LEFT JOIN inspection_payment_modes ipm ON f.payment_mode_id = ipm.id
              {$where}
              ORDER BY f.file_date DESC, f.id DESC
              LIMIT {$perPage} OFFSET {$offset}";

    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $files = $stmt->fetchAll();

    // Fetch dropdown data for forms
    $banks = $db->query("SELECT id, bank_name FROM inspection_banks WHERE status = 'active' ORDER BY bank_name")->fetchAll();
    $sources = $db->query("SELECT id, source_name FROM inspection_sources WHERE status = 'active' ORDER BY source_name")->fetchAll();
    $paymentModes = $db->query("SELECT id, mode_name FROM inspection_payment_modes WHERE status = 'active' ORDER BY mode_name")->fetchAll();
    $myAccounts = $db->query("SELECT id, account_name FROM inspection_my_accounts WHERE status = 'active' ORDER BY account_name")->fetchAll();

} catch(PDOException $e) {
    $files = $banks = $sources = $paymentModes = $myAccounts = [];
    $totalFiles = 0; $totalPages = 0;
    error_log("Files fetch error: " . $e->getMessage());
}

$pageTitle = 'Inspection Files';
$basePath = '../';
include __DIR__ . '/../includes/header.php';
```

- [ ] **Step 3: Write the HTML — page header, filters, data table**

The HTML section contains:
- Page header with "Add New File" button
- Alert messages
- Filter row: search input, date range (from/to), file type dropdown, payment status dropdown, bank dropdown, source dropdown, reset button
- Responsive data table with columns: File #, Date, Type, Customer, Bank/Branch, Source, Fees, Commission, Gross, Payment Status, Actions
- Pagination controls at bottom
- For each row: View modal (read-only detail), Edit modal (prefilled form), Delete modal (confirmation)

Key table structure:
```html
<div class="table-responsive">
    <table class="data-table">
        <thead>
            <tr>
                <th>File #</th><th>Date</th><th>Type</th><th>Customer</th>
                <th>Bank / Branch</th><th>Fees</th><th>Commission</th>
                <th>Gross</th><th>Status</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($files as $file): ?>
            <tr>
                <td><strong><?php echo htmlspecialchars($file['file_number']); ?></strong></td>
                <td><?php echo date('d M Y', strtotime($file['file_date'])); ?></td>
                <td><span class="badge bg-<?php echo $file['file_type'] === 'office' ? 'info' : 'primary'; ?>"><?php echo ucfirst($file['file_type']); ?></span></td>
                <td><?php echo htmlspecialchars($file['customer_name']); ?></td>
                <td><?php echo htmlspecialchars($file['bank_name']); ?><br><small class="text-muted"><?php echo htmlspecialchars($file['branch_name']); ?></small></td>
                <td><?php echo $file['fees'] !== null ? '₹' . number_format($file['fees'], 2) : '<span class="text-muted">NA</span>'; ?></td>
                <td>₹<?php echo number_format($file['commission'], 2); ?></td>
                <td><strong>₹<?php echo number_format($file['gross_amount'], 2); ?></strong></td>
                <td><?php
                    if ($file['file_type'] === 'office') {
                        echo '<span class="text-muted">NA</span>';
                    } else {
                        $statusColors = ['due' => 'danger', 'paid' => 'success', 'partially' => 'warning'];
                        echo '<span class="badge bg-' . ($statusColors[$file['payment_status']] ?? 'secondary') . '">' . ucfirst($file['payment_status']) . '</span>';
                    }
                ?></td>
                <td>
                    <button class="btn btn-sm btn-icon" data-bs-toggle="modal" data-bs-target="#viewFileModal<?php echo $file['id']; ?>"><i class="fas fa-eye"></i></button>
                    <button class="btn btn-sm btn-icon" data-bs-toggle="modal" data-bs-target="#editFileModal<?php echo $file['id']; ?>"><i class="fas fa-edit"></i></button>
                    <button class="btn btn-sm btn-icon text-danger" data-bs-toggle="modal" data-bs-target="#deleteFileModal<?php echo $file['id']; ?>"><i class="fas fa-trash"></i></button>
                </td>
            </tr>
            <!-- View/Edit/Delete modals for this row -->
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<!-- Pagination -->
```

- [ ] **Step 4: Write the Add New File modal with all fields**

Large modal (`modal-xl`) with two-column layout:
- Row 1: Date (date input), File Type (select: office/self)
- Row 2: Location (select: kolkata/out_of_kolkata — disabled when self), Customer Name
- Row 3: Customer Phone, Property Address (textarea)
- Row 4: Property Value, Bank (select — triggers branch AJAX), Branch (select — populated by AJAX)
- Row 5: Source (select), Fees (number — disabled when office)
- Row 6: Report Status (select — disabled when office), Payment Mode (select — disabled when office)
- Row 7: Payment Status (select — disabled when office), Amount (number — disabled unless partially)
- Row 8: Paid to Office (select — disabled when office), Office Amount (readonly, auto-calc)
- Row 9: Commission (readonly, auto-calc), Extra Amount (number, optional)
- Row 10: Gross Amount (readonly, auto-calc), Received Account (select)
- Row 11: Notes (textarea, full width)

- [ ] **Step 5: Write the inline JavaScript for business rules**

Append before footer include:

```html
<script>
// Business rule logic for Add and Edit forms
function initFileForm(prefix) {
    const form = document.getElementById(prefix + 'Form');
    if (!form) return;

    const fileType = form.querySelector('[name="file_type"]');
    const location = form.querySelector('[name="location"]');
    const fees = form.querySelector('[name="fees"]');
    const reportStatus = form.querySelector('[name="report_status"]');
    const paymentMode = form.querySelector('[name="payment_mode_id"]');
    const paymentStatus = form.querySelector('[name="payment_status"]');
    const amount = form.querySelector('[name="amount"]');
    const paidToOffice = form.querySelector('[name="paid_to_office"]');
    const officeAmount = form.querySelector('[name="office_amount"]');
    const commission = form.querySelector('[name="commission"]');
    const extraAmount = form.querySelector('[name="extra_amount"]');
    const grossAmount = form.querySelector('[name="gross_amount"]');
    const bankSelect = form.querySelector('[name="bank_id"]');
    const branchSelect = form.querySelector('[name="branch_id"]');

    function toggleFields() {
        const isOffice = fileType.value === 'office';

        // Location: enabled for office, disabled for self
        location.disabled = !isOffice;
        if (!isOffice) location.value = '';

        // These fields are NA for office
        fees.disabled = isOffice;
        reportStatus.disabled = isOffice;
        paymentMode.disabled = isOffice;
        paymentStatus.disabled = isOffice;
        paidToOffice.disabled = isOffice;

        if (isOffice) {
            fees.value = '';
            reportStatus.value = '';
            paymentMode.value = '';
            paymentStatus.value = '';
            amount.value = '';
            amount.disabled = true;
            paidToOffice.value = '';
            officeAmount.value = '';
        } else {
            // Self: payment status controls amount
            toggleAmount();
        }
        calcCommission();
    }

    function toggleAmount() {
        if (fileType.value === 'office') {
            amount.disabled = true;
            amount.value = '';
            return;
        }
        if (paymentStatus.value === 'partially') {
            amount.disabled = false;
        } else if (paymentStatus.value === 'paid') {
            amount.disabled = true;
            amount.value = fees.value || '';
        } else {
            amount.disabled = true;
            amount.value = '';
        }
    }

    function calcCommission() {
        let comm = 0;
        let offAmt = 0;

        if (fileType.value === 'office') {
            if (location.value === 'kolkata') comm = 300;
            else if (location.value === 'out_of_kolkata') comm = 350;
        } else {
            const f = parseFloat(fees.value) || 0;
            comm = Math.round(f * 0.30 * 100) / 100;
            offAmt = Math.round(f * 0.70 * 100) / 100;
        }

        commission.value = comm.toFixed(2);
        officeAmount.value = fileType.value === 'self' ? offAmt.toFixed(2) : '';

        const extra = parseFloat(extraAmount.value) || 0;
        grossAmount.value = (comm + extra).toFixed(2);
    }

    function loadBranches(bankId, selectedBranchId) {
        branchSelect.innerHTML = '<option value="">Select Branch</option>';
        if (!bankId) return;
        fetch('files.php?ajax=branches&bank_id=' + bankId)
            .then(r => r.json())
            .then(branches => {
                branches.forEach(b => {
                    const opt = document.createElement('option');
                    opt.value = b.id;
                    opt.textContent = b.branch_name;
                    if (selectedBranchId && b.id == selectedBranchId) opt.selected = true;
                    branchSelect.appendChild(opt);
                });
            });
    }

    fileType.addEventListener('change', toggleFields);
    location.addEventListener('change', calcCommission);
    fees.addEventListener('input', () => { calcCommission(); toggleAmount(); });
    paymentStatus.addEventListener('change', toggleAmount);
    extraAmount.addEventListener('input', calcCommission);
    bankSelect.addEventListener('change', () => loadBranches(bankSelect.value));

    // Initialize state on page load
    toggleFields();

    // If editing, load branches for pre-selected bank
    if (bankSelect.value) {
        loadBranches(bankSelect.value, branchSelect.dataset.selected || '');
    }
}

// Init add form
document.addEventListener('DOMContentLoaded', () => {
    initFileForm('add');
});

// Init edit forms when modals open
document.querySelectorAll('[id^="editFileModal"]').forEach(modal => {
    modal.addEventListener('shown.bs.modal', () => {
        const formId = modal.querySelector('form').id;
        initFileForm(formId.replace('Form', ''));
    });
});
</script>
```

- [ ] **Step 6: Verify in browser**

Open `http://localhost/d2w/admin/inspection/files.php`:
- Add a file with type "office" + kolkata → commission should auto-fill 300
- Add a file with type "self" + fees 1000 → commission 300, office amount 700
- Change payment status to "partially" → amount field enables
- Bank change → branches load via AJAX
- Edit and delete should work
- Pagination appears if >25 records
- Filters work correctly

- [ ] **Step 7: Commit**

```bash
git add admin/inspection/files.php
git commit -m "feat: add inspection files CRUD with business rule logic"
```

---

## Chunk 4: Dashboard Page

### Task 6: Build Dashboard Page (inspection/index.php)

**Files:**
- Create: `admin/inspection/index.php`

- [ ] **Step 1: Write PHP backend — stats queries with month/year filter**

Create `admin/inspection/index.php`:

```php
<?php
require_once __DIR__ . '/../includes/auth.php';
$auth->requireLogin();

require_once __DIR__ . '/../config/database.php';
$database = new Database();
$db = $database->connect();

// Month/Year filter
$filterMonth = $_GET['month'] ?? date('m');
$filterYear = $_GET['year'] ?? date('Y');
$filterType = $_GET['file_type'] ?? '';

$monthStart = "{$filterYear}-{$filterMonth}-01";
$monthEnd = date('Y-m-t', strtotime($monthStart));

try {
    // Base WHERE for month
    $monthWhere = "WHERE f.file_date BETWEEN :start AND :end";
    $monthParams = [':start' => $monthStart, ':end' => $monthEnd];

    if ($filterType) {
        $monthWhere .= " AND f.file_type = :type";
        $monthParams[':type'] = $filterType;
    }

    // Stat 1: Total Files
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM inspection_files f {$monthWhere}");
    $stmt->execute($monthParams);
    $totalFiles = $stmt->fetch()['total'];

    // Stat 2: Total Earnings (sum of gross_amount)
    $stmt = $db->prepare("SELECT COALESCE(SUM(f.gross_amount), 0) as total FROM inspection_files f {$monthWhere}");
    $stmt->execute($monthParams);
    $totalEarnings = $stmt->fetch()['total'];

    // Stat 3: Pending Payments (self files with due/partially)
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM inspection_files f {$monthWhere} AND f.file_type = 'self' AND f.payment_status IN ('due', 'partially')");
    $stmt->execute($monthParams);
    $pendingPayments = $stmt->fetch()['total'];

    // Stat 4: Active Sources
    $stmt = $db->prepare("SELECT COUNT(DISTINCT f.source_id) as total FROM inspection_files f {$monthWhere}");
    $stmt->execute($monthParams);
    $activeSources = $stmt->fetch()['total'];

    // Source-wise summary
    $stmt = $db->prepare("SELECT isrc.source_name, COUNT(*) as file_count, COALESCE(SUM(f.gross_amount), 0) as total_earnings
        FROM inspection_files f
        JOIN inspection_sources isrc ON f.source_id = isrc.id
        {$monthWhere}
        GROUP BY f.source_id, isrc.source_name
        ORDER BY total_earnings DESC");
    $stmt->execute($monthParams);
    $sourceSummary = $stmt->fetchAll();

    // Recent 10 files
    $stmt = $db->prepare("SELECT f.*, ib.bank_name, isrc.source_name
        FROM inspection_files f
        LEFT JOIN inspection_banks ib ON f.bank_id = ib.id
        LEFT JOIN inspection_sources isrc ON f.source_id = isrc.id
        {$monthWhere}
        ORDER BY f.file_date DESC, f.id DESC LIMIT 10");
    $stmt->execute($monthParams);
    $recentFiles = $stmt->fetchAll();

} catch(PDOException $e) {
    $totalFiles = $totalEarnings = $pendingPayments = $activeSources = 0;
    $sourceSummary = $recentFiles = [];
    error_log("Dashboard error: " . $e->getMessage());
}

$pageTitle = 'Inspection Dashboard';
$basePath = '../';
include __DIR__ . '/../includes/header.php';
?>
```

- [ ] **Step 2: Write the HTML — stat cards + filter bar**

```html
<div class="admin-content">
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h1 class="page-title">Inspection Dashboard</h1>
            <p class="page-subtitle"><?php echo date('F Y', strtotime($monthStart)); ?> Overview</p>
        </div>
        <a href="files.php" class="btn btn-primary"><i class="fas fa-plus me-2"></i>New File</a>
    </div>

    <!-- Month/Year Filter -->
    <form method="GET" class="row mb-4 g-2">
        <div class="col-auto">
            <select name="month" class="form-select">
                <?php for ($m = 1; $m <= 12; $m++): ?>
                    <option value="<?php echo str_pad($m, 2, '0', STR_PAD_LEFT); ?>" <?php echo $filterMonth == str_pad($m, 2, '0', STR_PAD_LEFT) ? 'selected' : ''; ?>>
                        <?php echo date('F', mktime(0, 0, 0, $m, 1)); ?>
                    </option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="col-auto">
            <select name="year" class="form-select">
                <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                    <option value="<?php echo $y; ?>" <?php echo $filterYear == $y ? 'selected' : ''; ?>><?php echo $y; ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="col-auto">
            <select name="file_type" class="form-select">
                <option value="">All Types</option>
                <option value="office" <?php echo $filterType === 'office' ? 'selected' : ''; ?>>Office</option>
                <option value="self" <?php echo $filterType === 'self' ? 'selected' : ''; ?>>Self</option>
            </select>
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary"><i class="fas fa-filter me-1"></i>Filter</button>
        </div>
    </form>

    <!-- Stat Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-card-header">
                <div><div class="stat-value"><?php echo $totalFiles; ?></div><div class="stat-label">Total Files</div></div>
                <div class="stat-icon primary"><i class="fas fa-folder-open"></i></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-header">
                <div><div class="stat-value">₹<?php echo number_format($totalEarnings, 0); ?></div><div class="stat-label">Total Earnings</div></div>
                <div class="stat-icon success"><i class="fas fa-rupee-sign"></i></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-header">
                <div><div class="stat-value"><?php echo $pendingPayments; ?></div><div class="stat-label">Pending Payments</div></div>
                <div class="stat-icon warning"><i class="fas fa-clock"></i></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-header">
                <div><div class="stat-value"><?php echo $activeSources; ?></div><div class="stat-label">Active Sources</div></div>
                <div class="stat-icon info"><i class="fas fa-users"></i></div>
            </div>
        </div>
    </div>
```

- [ ] **Step 3: Write source summary table and recent files table**

```html
    <div class="row mt-4">
        <!-- Source Summary -->
        <div class="col-lg-5">
            <div class="content-card">
                <div class="card-header-flex"><h5>Source-wise Summary</h5></div>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead><tr><th>Source</th><th>Files</th><th>Earnings</th></tr></thead>
                        <tbody>
                            <?php if (!empty($sourceSummary)): ?>
                                <?php foreach ($sourceSummary as $row): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['source_name']); ?></td>
                                        <td><?php echo $row['file_count']; ?></td>
                                        <td>₹<?php echo number_format($row['total_earnings'], 0); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="3" class="text-center text-muted py-3">No data for this period</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recent Files -->
        <div class="col-lg-7">
            <div class="content-card">
                <div class="card-header-flex">
                    <h5>Recent Files</h5>
                    <a href="files.php" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead><tr><th>File #</th><th>Date</th><th>Customer</th><th>Type</th><th>Commission</th><th>Status</th></tr></thead>
                        <tbody>
                            <?php if (!empty($recentFiles)): ?>
                                <?php foreach ($recentFiles as $file): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($file['file_number']); ?></strong></td>
                                        <td><?php echo date('d M', strtotime($file['file_date'])); ?></td>
                                        <td><?php echo htmlspecialchars($file['customer_name']); ?></td>
                                        <td><span class="badge bg-<?php echo $file['file_type'] === 'office' ? 'info' : 'primary'; ?>"><?php echo ucfirst($file['file_type']); ?></span></td>
                                        <td>₹<?php echo number_format($file['commission'], 0); ?></td>
                                        <td><?php
                                            if ($file['file_type'] === 'office') {
                                                echo '<span class="text-muted">NA</span>';
                                            } else {
                                                $colors = ['due' => 'danger', 'paid' => 'success', 'partially' => 'warning'];
                                                echo '<span class="badge bg-' . ($colors[$file['payment_status']] ?? 'secondary') . '">' . ucfirst($file['payment_status']) . '</span>';
                                            }
                                        ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center text-muted py-3">No files for this period</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
```

- [ ] **Step 4: Verify in browser**

Open `http://localhost/d2w/admin/inspection/index.php`:
- Stat cards show correct numbers
- Month/year filter works
- Source summary populates after adding files
- Recent files table shows latest entries
- All sidebar links work from this page

- [ ] **Step 5: Commit**

```bash
git add admin/inspection/index.php
git commit -m "feat: add inspection dashboard with stats and source summary"
```

---

## Chunk 5: Final Verification

### Task 7: End-to-End Testing & Final Commit

- [ ] **Step 1: Full workflow test**

1. Navigate to Masters → add 2 banks, 3 branches, 2 sources, verify payment modes seeded
2. Navigate to Files → create an "office/kolkata" file → verify commission = 300, NA fields disabled
3. Create a "self" file with fees 1000 → verify commission = 300, office amount = 700
4. Set payment status to "partially" → verify amount field enables
5. Add extra amount 50 → verify gross = 350
6. Navigate to Dashboard → verify all stats reflect the test data
7. Edit a file → verify data prefills correctly, branch loads for selected bank
8. Delete a file → verify removed
9. Try deleting a bank that's used in a file → verify error message

- [ ] **Step 2: Test subdirectory navigation**

From inspection pages:
- Click "Dashboard" in sidebar → goes to admin dashboard (not inspection)
- Click "Leads" → goes to leads page
- Click inspection links → navigate within inspection section
- Verify active state highlights correctly on each page

- [ ] **Step 3: Mobile responsiveness check**

Open browser dev tools, test at 576px and 768px widths:
- Sidebar collapses on mobile
- Tables scroll horizontally
- Modals display correctly
- Filter form stacks on small screens

- [ ] **Step 4: Final commit with all files**

```bash
git add -A
git status
git commit -m "feat: complete inspection module - dashboard, files, masters"
```
