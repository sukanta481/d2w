<?php
require_once 'includes/auth.php';
$auth->requireLogin();

require_once 'config/database.php';
$database = new Database();
$db = $database->connect();

// ==========================================
// DATE FILTER HANDLING
// ==========================================
$datePreset = $_GET['preset'] ?? '';
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';

// Quick preset logic
if ($datePreset === '7d') {
    $dateFrom = date('Y-m-d', strtotime('-7 days'));
    $dateTo = date('Y-m-d');
} elseif ($datePreset === '30d') {
    $dateFrom = date('Y-m-d', strtotime('-30 days'));
    $dateTo = date('Y-m-d');
} elseif ($datePreset === '60d') {
    $dateFrom = date('Y-m-d', strtotime('-60 days'));
    $dateTo = date('Y-m-d');
} elseif ($datePreset === '1y') {
    $dateFrom = date('Y-m-d', strtotime('-1 year'));
    $dateTo = date('Y-m-d');
} elseif ($datePreset === 'this_month') {
    $dateFrom = date('Y-m-01');
    $dateTo = date('Y-m-d');
} elseif ($datePreset === 'last_month') {
    $dateFrom = date('Y-m-01', strtotime('first day of last month'));
    $dateTo = date('Y-m-t', strtotime('last day of last month'));
}

$hasDateFilter = !empty($dateFrom) && !empty($dateTo);

// Build date clauses for each table
$billDateClause = $hasDateFilter ? " AND b.bill_date BETWEEN :df AND :dt" : "";
$billPayDateClause = $hasDateFilter ? " AND payment_date BETWEEN :df AND :dt" : "";
$inspDateClause = $hasDateFilter ? " AND f.file_date BETWEEN :df AND :dt" : "";
$inspDateClauseShort = $hasDateFilter ? " AND file_date BETWEEN :df AND :dt" : "";
$expDateClause = $hasDateFilter ? " AND expense_date BETWEEN :df AND :dt" : "";
$dateParams = $hasDateFilter ? [':df' => $dateFrom, ':dt' => $dateTo] : [];

// Helper to bind date params
function bindDateParams($stmt, $params) {
    foreach ($params as $k => $v) { $stmt->bindValue($k, $v); }
}

// Filter label for display
$filterLabel = 'All Time';
if ($datePreset === '7d') $filterLabel = 'Last 7 Days';
elseif ($datePreset === '30d') $filterLabel = 'Last 30 Days';
elseif ($datePreset === '60d') $filterLabel = 'Last 60 Days';
elseif ($datePreset === '1y') $filterLabel = 'Last Year';
elseif ($datePreset === 'this_month') $filterLabel = 'This Month';
elseif ($datePreset === 'last_month') $filterLabel = 'Last Month';
elseif ($hasDateFilter) $filterLabel = date('M d, Y', strtotime($dateFrom)) . ' — ' . date('M d, Y', strtotime($dateTo));

// ==========================================
// TAB 1: MAIN DASHBOARD DATA
// ==========================================
$mainData = [
    'billing_earned' => 0, 'inspection_earned' => 0, 'general_income' => 0,
    'total_earnings' => 0, 'total_expenses' => 0, 'net_profit' => 0,
    'pending_payments' => 0, 'monthly_earnings' => [], 'monthly_expenses' => [],
    'recent_expenses' => []
];

try {
    // Billing paid
    $sql = "SELECT COALESCE(SUM(paid_amount), 0) as total FROM bills b WHERE payment_status IN ('paid', 'partial')" . $billDateClause;
    $stmt = $db->prepare($sql); bindDateParams($stmt, $dateParams); $stmt->execute();
    $mainData['billing_earned'] = $stmt->fetch()['total'];

    // Inspection earnings
    $sql = "SELECT COALESCE(SUM(f.gross_amount), 0) as total FROM inspection_files f WHERE 1=1" . $inspDateClause;
    $stmt = $db->prepare($sql); bindDateParams($stmt, $dateParams); $stmt->execute();
    $mainData['inspection_earned'] = $stmt->fetch()['total'];

    // General income
    $sql = "SELECT COALESCE(SUM(amount), 0) as total FROM expenses WHERE type = 'income'" . $expDateClause;
    $stmt = $db->prepare($sql); bindDateParams($stmt, $dateParams); $stmt->execute();
    $mainData['general_income'] = $stmt->fetch()['total'];

    $mainData['total_earnings'] = $mainData['billing_earned'] + $mainData['inspection_earned'] + $mainData['general_income'];

    // Total expenses
    $sql = "SELECT COALESCE(SUM(amount), 0) as total FROM expenses WHERE type = 'expense'" . $expDateClause;
    $stmt = $db->prepare($sql); bindDateParams($stmt, $dateParams); $stmt->execute();
    $mainData['total_expenses'] = $stmt->fetch()['total'];

    $mainData['net_profit'] = $mainData['total_earnings'] - $mainData['total_expenses'];

    // Pending payments (always show current pending, not filtered)
    $stmt = $db->query("SELECT COALESCE(SUM(total_amount - paid_amount), 0) as total FROM bills WHERE payment_status IN ('unpaid', 'partial')");
    $pendingBills = $stmt->fetch()['total'];
    $stmt = $db->query("SELECT COALESCE(SUM(COALESCE(fees, 0) - COALESCE(amount, 0)), 0) as total FROM inspection_files WHERE file_type = 'self' AND payment_status IN ('due', 'partially')");
    $pendingInspection = $stmt->fetch()['total'];
    $mainData['pending_payments'] = $pendingBills + $pendingInspection;

    // Monthly earnings (last 6 months) - billing
    $stmt = $db->query("SELECT DATE_FORMAT(payment_date, '%Y-%m') as month, SUM(paid_amount) as total
        FROM bills WHERE payment_date IS NOT NULL AND payment_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
        GROUP BY month ORDER BY month");
    $billingMonthly = [];
    while ($row = $stmt->fetch()) { $billingMonthly[$row['month']] = floatval($row['total']); }

    // Monthly earnings - inspection
    $stmt = $db->query("SELECT DATE_FORMAT(file_date, '%Y-%m') as month, SUM(gross_amount) as total
        FROM inspection_files WHERE file_date IS NOT NULL AND file_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
        GROUP BY month ORDER BY month");
    $inspectionMonthly = [];
    while ($row = $stmt->fetch()) { $inspectionMonthly[$row['month']] = floatval($row['total']); }

    // Monthly income
    $stmt = $db->query("SELECT DATE_FORMAT(expense_date, '%Y-%m') as month, SUM(amount) as total
        FROM expenses WHERE type = 'income' AND expense_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
        GROUP BY month ORDER BY month");
    $incomeMonthly = [];
    while ($row = $stmt->fetch()) { $incomeMonthly[$row['month']] = floatval($row['total']); }

    // Monthly expenses
    $stmt = $db->query("SELECT DATE_FORMAT(expense_date, '%Y-%m') as month, SUM(amount) as total
        FROM expenses WHERE type = 'expense' AND expense_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
        GROUP BY month ORDER BY month");
    $expensesMonthly = [];
    while ($row = $stmt->fetch()) { $expensesMonthly[$row['month']] = floatval($row['total']); }

    $months = [];
    for ($i = 5; $i >= 0; $i--) { $months[] = date('Y-m', strtotime("-{$i} months")); }
    foreach ($months as $m) {
        $mainData['monthly_earnings'][] = ($billingMonthly[$m] ?? 0) + ($inspectionMonthly[$m] ?? 0) + ($incomeMonthly[$m] ?? 0);
        $mainData['monthly_expenses'][] = $expensesMonthly[$m] ?? 0;
    }

    // Recent 5 records
    $stmt = $db->query("SELECT * FROM expenses ORDER BY expense_date DESC, id DESC LIMIT 5");
    $mainData['recent_expenses'] = $stmt->fetchAll();

} catch(PDOException $e) { error_log("Dashboard Main Error: " . $e->getMessage()); }

// ==========================================
// TAB 2: BIZNEXA DATA
// ==========================================
$biznexaData = ['total_billed'=>0,'paid_amount'=>0,'unpaid_amount'=>0,'total_bills'=>0,'total_expenses'=>0,'net_profit'=>0,'recent_bills'=>[]];
try {
    $sql = "SELECT COUNT(*) as cnt, COALESCE(SUM(b.total_amount),0) as billed, COALESCE(SUM(b.paid_amount),0) as paid FROM bills b WHERE 1=1" . $billDateClause;
    $stmt = $db->prepare($sql); bindDateParams($stmt, $dateParams); $stmt->execute();
    $row = $stmt->fetch();
    $biznexaData['total_bills'] = $row['cnt'];
    $biznexaData['total_billed'] = $row['billed'];
    $biznexaData['paid_amount'] = $row['paid'];
    $biznexaData['unpaid_amount'] = $row['billed'] - $row['paid'];

    $sql = "SELECT COALESCE(SUM(amount),0) as total FROM expenses WHERE category='biznexa' AND type='expense'" . $expDateClause;
    $stmt = $db->prepare($sql); bindDateParams($stmt, $dateParams); $stmt->execute();
    $biznexaData['total_expenses'] = $stmt->fetch()['total'];
    $biznexaData['net_profit'] = $biznexaData['paid_amount'] - $biznexaData['total_expenses'];

    $stmt = $db->query("SELECT b.*, c.name as client_name FROM bills b LEFT JOIN clients c ON b.client_id = c.id ORDER BY b.created_at DESC LIMIT 5");
    $biznexaData['recent_bills'] = $stmt->fetchAll();
} catch(PDOException $e) { error_log("Dashboard BizNexa Error: " . $e->getMessage()); }

// ==========================================
// TAB 3: INSPECTION DATA
// ==========================================
$inspData = ['total_fees'=>0,'total_earnings'=>0,'pending_amount'=>0,'total_files'=>0,'total_expenses'=>0,'net_profit'=>0,'recent_files'=>[]];
try {
    $sql = "SELECT COUNT(*) as cnt, COALESCE(SUM(f.fees),0) as fees, COALESCE(SUM(f.gross_amount),0) as earnings FROM inspection_files f WHERE 1=1" . $inspDateClause;
    $stmt = $db->prepare($sql); bindDateParams($stmt, $dateParams); $stmt->execute();
    $row = $stmt->fetch();
    $inspData['total_files'] = $row['cnt'];
    $inspData['total_fees'] = $row['fees'];
    $inspData['total_earnings'] = $row['earnings'];

    $stmt = $db->query("SELECT COALESCE(SUM(COALESCE(fees,0) - COALESCE(amount,0)), 0) as total FROM inspection_files WHERE file_type = 'self' AND payment_status IN ('due', 'partially')");
    $inspData['pending_amount'] = $stmt->fetch()['total'];

    $sql = "SELECT COALESCE(SUM(amount),0) as total FROM expenses WHERE category='inspection' AND type='expense'" . $expDateClause;
    $stmt = $db->prepare($sql); bindDateParams($stmt, $dateParams); $stmt->execute();
    $inspData['total_expenses'] = $stmt->fetch()['total'];
    $inspData['net_profit'] = $inspData['total_earnings'] - $inspData['total_expenses'];

    $stmt = $db->query("SELECT f.*, ib.bank_name, isrc.source_name FROM inspection_files f LEFT JOIN inspection_banks ib ON f.bank_id = ib.id LEFT JOIN inspection_sources isrc ON f.source_id = isrc.id ORDER BY f.file_date DESC, f.id DESC LIMIT 5");
    $inspData['recent_files'] = $stmt->fetchAll();
} catch(PDOException $e) { error_log("Dashboard Inspection Error: " . $e->getMessage()); }

// ==========================================
// TAB 4: GENERAL DATA
// ==========================================
$generalData = ['total_income'=>0,'total_expenses'=>0,'net_balance'=>0,'total_records'=>0,'recent_records'=>[]];
try {
    $sql = "SELECT COUNT(*) as cnt FROM expenses WHERE category='general'" . $expDateClause;
    $stmt = $db->prepare($sql); bindDateParams($stmt, $dateParams); $stmt->execute();
    $generalData['total_records'] = $stmt->fetch()['cnt'];

    $sql = "SELECT COALESCE(SUM(amount),0) as total FROM expenses WHERE category='general' AND type='income'" . $expDateClause;
    $stmt = $db->prepare($sql); bindDateParams($stmt, $dateParams); $stmt->execute();
    $generalData['total_income'] = $stmt->fetch()['total'];

    $sql = "SELECT COALESCE(SUM(amount),0) as total FROM expenses WHERE category='general' AND type='expense'" . $expDateClause;
    $stmt = $db->prepare($sql); bindDateParams($stmt, $dateParams); $stmt->execute();
    $generalData['total_expenses'] = $stmt->fetch()['total'];

    $generalData['net_balance'] = $generalData['total_income'] - $generalData['total_expenses'];

    $stmt = $db->query("SELECT * FROM expenses WHERE category = 'general' ORDER BY expense_date DESC, id DESC LIMIT 10");
    $generalData['recent_records'] = $stmt->fetchAll();
} catch(PDOException $e) { error_log("Dashboard General Error: " . $e->getMessage()); }

$monthLabels = [];
for ($i = 5; $i >= 0; $i--) { $monthLabels[] = date('M Y', strtotime("-{$i} months")); }

// Preserve tab hash on filter submit
$currentTab = $_GET['tab'] ?? '';

$pageTitle = 'Dashboard';
include 'includes/header.php';
?>

<style>
/* Dashboard Tab Styles (inline for cache-safety) */
.dashboard-tabs { display:flex; gap:4px; background:#fff; border-radius:14px; padding:6px; margin-bottom:15px; box-shadow:0 2px 10px rgba(0,0,0,.05); }
.tab-btn { flex:1; padding:12px 20px; border:none; background:transparent; border-radius:10px; font-weight:600; font-size:.9rem; color:#6c757d; cursor:pointer; transition:all .3s ease; font-family:'Inter',sans-serif; }
.tab-btn:hover { background:#f8f9fa; color:#2C3E50; }
.tab-btn.active { background:linear-gradient(135deg,#0d6efd,#0056b3); color:#fff; box-shadow:0 4px 12px rgba(13,110,253,.3); }
.tab-panel { display:none; animation:fadeInTab .4s ease; }
.tab-panel.active { display:block; }
@keyframes fadeInTab { from{opacity:0;transform:translateY(10px)} to{opacity:1;transform:translateY(0)} }
.chart-container { position:relative; height:320px; width:100%; }
.breakdown-item { display:flex; justify-content:space-between; align-items:center; padding:12px 0; border-bottom:1px solid #f0f0f0; }
.breakdown-item:last-child { border-bottom:none; }
.breakdown-item.total { border-top:2px solid #e9ecef; border-bottom:none; padding-top:15px; margin-top:5px; }
.breakdown-label { font-size:.95rem; color:#2C3E50; }
.breakdown-value { font-size:1.1rem; font-weight:700; color:#2C3E50; }
.recent-expense-item { display:flex; justify-content:space-between; align-items:center; padding:10px 0; border-bottom:1px solid #f0f0f0; }
.recent-expense-item:last-child { border-bottom:none; }
.recent-expense-info { display:flex; flex-direction:column; gap:2px; }
.recent-expense-info strong { font-size:.9rem; color:#2C3E50; }
.recent-expense-info small { color:#6c757d; font-size:.75rem; }
.recent-expense-amount { font-weight:700; font-size:.95rem; }
.category-badge-biznexa { background:#0d6efd; color:#fff; }
.category-badge-inspection { background:#ffc107; color:#212529; }
.category-badge-general { background:#8b5cf6; color:#fff; }
.type-badge-income { background:#10b981; color:#fff; }
.type-badge-expense { background:#dc3545; color:#fff; }

/* Date Filter Bar */
.date-filter-bar { background:#fff; border-radius:12px; padding:12px 16px; margin-bottom:20px; box-shadow:0 1px 6px rgba(0,0,0,.04); border:1px solid #e9ecef; display:flex; align-items:center; flex-wrap:wrap; gap:8px; }
.filter-presets { display:flex; gap:4px; flex-wrap:wrap; }
.preset-btn { padding:6px 14px; border:1px solid #dee2e6; background:#fff; border-radius:8px; font-size:.8rem; font-weight:500; color:#6c757d; cursor:pointer; transition:all .2s; font-family:'Inter',sans-serif; text-decoration:none; }
.preset-btn:hover { background:#f8f9fa; border-color:#adb5bd; color:#2C3E50; text-decoration:none; }
.preset-btn.active { background:#0d6efd; color:#fff; border-color:#0d6efd; }
.filter-separator { width:1px; height:28px; background:#dee2e6; margin:0 6px; }
.date-inputs { display:flex; gap:6px; align-items:center; }
.date-inputs input { padding:5px 10px; border:1px solid #dee2e6; border-radius:8px; font-size:.8rem; font-family:'Inter',sans-serif; color:#2C3E50; }
.date-inputs .btn-apply { padding:6px 14px; background:#0d6efd; color:#fff; border:none; border-radius:8px; font-size:.8rem; font-weight:500; cursor:pointer; }
.date-inputs .btn-apply:hover { background:#0056b3; }
.filter-active-label { font-size:.8rem; color:#0d6efd; font-weight:600; margin-left:auto; }
.filter-active-label i { margin-right:4px; }
.btn-reset-filter { padding:5px 12px; border:1px solid #dc3545; background:transparent; color:#dc3545; border-radius:8px; font-size:.75rem; cursor:pointer; text-decoration:none; }
.btn-reset-filter:hover { background:#dc3545; color:#fff; text-decoration:none; }

/* Tablet */
@media(max-width:992px){
    .dashboard-tabs { flex-wrap:wrap; }
    .tab-btn { flex:1 1 45%; font-size:.8rem; padding:10px 14px; }
    .stats-grid { grid-template-columns:repeat(2,1fr) !important; }
    .stat-value { font-size:1.4rem !important; }
    .chart-container { height:260px; }
    .date-filter-bar { padding:10px 12px; }
}
/* Mobile */
@media(max-width:768px){
    .dashboard-tabs { flex-direction:column; gap:6px; padding:8px; }
    .tab-btn { width:100%; text-align:center; padding:10px 16px; font-size:.85rem; }
    .stats-grid { grid-template-columns:1fr 1fr !important; gap:.75rem !important; }
    .stat-card { padding:12px !important; }
    .stat-value { font-size:1.15rem !important; }
    .stat-label { font-size:.75rem !important; }
    .stat-icon { width:36px!important; height:36px!important; font-size:.8rem!important; }
    .chart-container { height:220px; }
    .content-card { padding:15px !important; }
    .card-title { font-size:1rem !important; }
    .breakdown-label { font-size:.85rem; }
    .breakdown-value { font-size:.95rem; }
    .recent-expense-info strong { font-size:.8rem; }
    .recent-expense-amount { font-size:.85rem; }
    .page-title { font-size:1.5rem !important; }
    .page-subtitle { font-size:.85rem !important; }
    .data-table { font-size:.8rem; }
    .data-table th, .data-table td { padding:8px 6px !important; }
    [style*="font-size:2rem"] { font-size:1.5rem !important; }
    [style*="font-size:1.75rem"] { font-size:1.25rem !important; }
    .date-filter-bar { flex-direction:column; align-items:stretch; }
    .filter-separator { display:none; }
    .filter-presets { justify-content:center; }
    .date-inputs { justify-content:center; flex-wrap:wrap; }
    .filter-active-label { margin-left:0; text-align:center; }
}
/* Small Mobile */
@media(max-width:480px){
    .stats-grid { grid-template-columns:1fr !important; }
    .stat-value { font-size:1.3rem !important; }
    .chart-container { height:180px; }
    .page-header { flex-direction:column; align-items:flex-start !important; gap:10px; }
    .row > [class*="col-"] { padding-left:8px; padding-right:8px; }
    .btn-sm { font-size:.75rem; padding:4px 8px; }
    .preset-btn { padding:5px 10px; font-size:.7rem; }
}
</style>

<div class="admin-content">
    <div class="page-header">
        <h1 class="page-title">Dashboard</h1>
        <p class="page-subtitle">Welcome back, <?php echo htmlspecialchars($_SESSION['admin_full_name']); ?>!</p>
    </div>

    <!-- Dashboard Tabs -->
    <div class="dashboard-tabs">
        <button class="tab-btn active" data-tab="main" onclick="switchTab('main')">
            <i class="fas fa-chart-pie me-2"></i>Main Dashboard
        </button>
        <button class="tab-btn" data-tab="biznexa" onclick="switchTab('biznexa')">
            <i class="fas fa-building me-2"></i>BizNexa Agency
        </button>
        <button class="tab-btn" data-tab="inspection" onclick="switchTab('inspection')">
            <i class="fas fa-search-location me-2"></i>Inspection
        </button>
        <button class="tab-btn" data-tab="general" onclick="switchTab('general')">
            <i class="fas fa-wallet me-2"></i>General
        </button>
    </div>

    <!-- Date Filter Bar -->
    <div class="date-filter-bar">
        <div class="filter-presets">
            <a href="?preset=7d" class="preset-btn <?php echo $datePreset === '7d' ? 'active' : ''; ?>">Last 7 Days</a>
            <a href="?preset=30d" class="preset-btn <?php echo $datePreset === '30d' ? 'active' : ''; ?>">Last 30 Days</a>
            <a href="?preset=60d" class="preset-btn <?php echo $datePreset === '60d' ? 'active' : ''; ?>">Last 60 Days</a>
            <a href="?preset=this_month" class="preset-btn <?php echo $datePreset === 'this_month' ? 'active' : ''; ?>">This Month</a>
            <a href="?preset=last_month" class="preset-btn <?php echo $datePreset === 'last_month' ? 'active' : ''; ?>">Last Month</a>
            <a href="?preset=1y" class="preset-btn <?php echo $datePreset === '1y' ? 'active' : ''; ?>">Last Year</a>
        </div>
        <div class="filter-separator"></div>
        <form class="date-inputs" method="GET" id="customDateForm">
            <input type="date" name="date_from" value="<?php echo htmlspecialchars($dateFrom); ?>" placeholder="From">
            <input type="date" name="date_to" value="<?php echo htmlspecialchars($dateTo); ?>" placeholder="To">
            <input type="hidden" name="tab" id="dateFilterTab" value="<?php echo htmlspecialchars($currentTab); ?>">
            <button type="submit" class="btn-apply"><i class="fas fa-filter me-1"></i>Apply</button>
        </form>
        <?php if ($hasDateFilter): ?>
            <span class="filter-active-label"><i class="fas fa-calendar-check"></i><?php echo $filterLabel; ?></span>
            <a href="dashboard.php" class="btn-reset-filter"><i class="fas fa-times me-1"></i>Clear</a>
        <?php endif; ?>
    </div>

    <!-- ============================================ -->
    <!-- TAB 1: MAIN DASHBOARD                        -->
    <!-- ============================================ -->
    <div class="tab-panel active" id="tab-main">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-card-header">
                    <div>
                        <div class="stat-value">₹<?php echo number_format($mainData['total_earnings'], 0); ?></div>
                        <div class="stat-label">Total Earnings</div>
                    </div>
                    <div class="stat-icon success"><i class="fas fa-rupee-sign"></i></div>
                </div>
                <div class="stat-change positive">
                    <i class="fas fa-arrow-up"></i>
                    <span><?php echo $filterLabel; ?></span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-card-header">
                    <div>
                        <div class="stat-value">₹<?php echo number_format($mainData['total_expenses'], 0); ?></div>
                        <div class="stat-label">Total Expenses</div>
                    </div>
                    <div class="stat-icon danger"><i class="fas fa-receipt"></i></div>
                </div>
                <div class="stat-change negative">
                    <i class="fas fa-arrow-down"></i>
                    <span><?php echo $filterLabel; ?></span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-card-header">
                    <div>
                        <div class="stat-value" style="color: <?php echo $mainData['net_profit'] >= 0 ? 'var(--success)' : 'var(--danger)'; ?>">
                            ₹<?php echo number_format($mainData['net_profit'], 0); ?>
                        </div>
                        <div class="stat-label">Net Profit</div>
                    </div>
                    <div class="stat-icon" style="background: <?php echo $mainData['net_profit'] >= 0 ? 'rgba(16,185,129,0.1)' : 'rgba(220,53,69,0.1)'; ?>; color: <?php echo $mainData['net_profit'] >= 0 ? 'var(--success)' : 'var(--danger)'; ?>">
                        <i class="fas fa-wallet"></i>
                    </div>
                </div>
                <div class="stat-change <?php echo $mainData['net_profit'] >= 0 ? 'positive' : 'negative'; ?>">
                    <i class="fas fa-<?php echo $mainData['net_profit'] >= 0 ? 'arrow-up' : 'arrow-down'; ?>"></i>
                    <span>Earnings - Expenses</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-card-header">
                    <div>
                        <div class="stat-value">₹<?php echo number_format($mainData['pending_payments'], 0); ?></div>
                        <div class="stat-label">Pending Payments</div>
                    </div>
                    <div class="stat-icon warning"><i class="fas fa-clock"></i></div>
                </div>
                <div class="stat-change">
                    <i class="fas fa-info-circle"></i>
                    <span>Bills + Inspection due</span>
                </div>
            </div>
        </div>

        <!-- Chart + Recent -->
        <div class="row">
            <div class="col-lg-8 mb-4">
                <div class="content-card">
                    <div class="card-header-flex">
                        <h2 class="card-title">Earnings vs Expenses (6 Months)</h2>
                    </div>
                    <div class="chart-container">
                        <canvas id="mainChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mb-4">
                <div class="content-card">
                    <div class="card-header-flex">
                        <h2 class="card-title">Recent Records</h2>
                        <a href="expenses.php" class="btn btn-sm btn-primary">View All</a>
                    </div>
                    <?php if (!empty($mainData['recent_expenses'])): ?>
                        <?php foreach ($mainData['recent_expenses'] as $exp): ?>
                            <div class="recent-expense-item">
                                <div class="recent-expense-info">
                                    <strong><?php echo htmlspecialchars($exp['title']); ?></strong>
                                    <small><?php echo date('M d', strtotime($exp['expense_date'])); ?> ·
                                        <span class="badge category-badge-<?php echo $exp['category']; ?>" style="font-size:0.6rem;padding:2px 5px;"><?php
                                            $catLabels = ['biznexa'=>'BizNexa','inspection'=>'Inspection','general'=>'General'];
                                            echo $catLabels[$exp['category']] ?? $exp['category'];
                                        ?></span>
                                    </small>
                                </div>
                                <div class="recent-expense-amount <?php echo $exp['type'] === 'income' ? 'text-success' : 'text-danger'; ?>">
                                    <?php echo $exp['type'] === 'income' ? '+' : '-'; ?>₹<?php echo number_format($exp['amount'], 0); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted text-center">No records yet</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Earnings + Expense Breakdown -->
        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="content-card">
                    <div class="card-header-flex"><h2 class="card-title">Earnings Breakdown</h2></div>
                    <div class="breakdown-item">
                        <div class="breakdown-label"><i class="fas fa-building text-primary me-2"></i>BizNexa Agency</div>
                        <div class="breakdown-value">₹<?php echo number_format($mainData['billing_earned'], 0); ?></div>
                    </div>
                    <div class="breakdown-item">
                        <div class="breakdown-label"><i class="fas fa-search-location text-warning me-2"></i>Inspection</div>
                        <div class="breakdown-value">₹<?php echo number_format($mainData['inspection_earned'], 0); ?></div>
                    </div>
                    <div class="breakdown-item">
                        <div class="breakdown-label"><i class="fas fa-wallet" style="color:#8b5cf6"></i> General Income</div>
                        <div class="breakdown-value">₹<?php echo number_format($mainData['general_income'], 0); ?></div>
                    </div>
                    <div class="breakdown-item total">
                        <div class="breakdown-label"><strong>Total</strong></div>
                        <div class="breakdown-value"><strong>₹<?php echo number_format($mainData['total_earnings'], 0); ?></strong></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mb-4">
                <div class="content-card">
                    <div class="card-header-flex"><h2 class="card-title">Expense Breakdown</h2></div>
                    <?php
                    try {
                        $sql = "SELECT COALESCE(SUM(amount),0) as total FROM expenses WHERE category='biznexa' AND type='expense'" . $expDateClause;
                        $stmt = $db->prepare($sql); bindDateParams($stmt, $dateParams); $stmt->execute();
                        $biznexaExp = $stmt->fetch()['total'];
                        $sql = "SELECT COALESCE(SUM(amount),0) as total FROM expenses WHERE category='inspection' AND type='expense'" . $expDateClause;
                        $stmt = $db->prepare($sql); bindDateParams($stmt, $dateParams); $stmt->execute();
                        $inspectionExp = $stmt->fetch()['total'];
                        $sql = "SELECT COALESCE(SUM(amount),0) as total FROM expenses WHERE category='general' AND type='expense'" . $expDateClause;
                        $stmt = $db->prepare($sql); bindDateParams($stmt, $dateParams); $stmt->execute();
                        $generalExp = $stmt->fetch()['total'];
                    } catch(PDOException $e) { $biznexaExp = $inspectionExp = $generalExp = 0; }
                    ?>
                    <div class="breakdown-item">
                        <div class="breakdown-label"><i class="fas fa-building text-primary me-2"></i>BizNexa</div>
                        <div class="breakdown-value text-danger">₹<?php echo number_format($biznexaExp, 0); ?></div>
                    </div>
                    <div class="breakdown-item">
                        <div class="breakdown-label"><i class="fas fa-search-location text-warning me-2"></i>Inspection</div>
                        <div class="breakdown-value text-danger">₹<?php echo number_format($inspectionExp, 0); ?></div>
                    </div>
                    <div class="breakdown-item">
                        <div class="breakdown-label"><i class="fas fa-wallet" style="color:#8b5cf6"></i> General</div>
                        <div class="breakdown-value text-danger">₹<?php echo number_format($generalExp, 0); ?></div>
                    </div>
                    <div class="breakdown-item total">
                        <div class="breakdown-label"><strong>Total</strong></div>
                        <div class="breakdown-value text-danger"><strong>₹<?php echo number_format($mainData['total_expenses'], 0); ?></strong></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- TAB 2: BIZNEXA AGENCY                        -->
    <!-- ============================================ -->
    <div class="tab-panel" id="tab-biznexa">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-card-header">
                    <div>
                        <div class="stat-value">₹<?php echo number_format($biznexaData['total_billed'], 0); ?></div>
                        <div class="stat-label">Total Billed</div>
                    </div>
                    <div class="stat-icon primary"><i class="fas fa-file-invoice-dollar"></i></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-card-header">
                    <div>
                        <div class="stat-value">₹<?php echo number_format($biznexaData['paid_amount'], 0); ?></div>
                        <div class="stat-label">Paid Amount</div>
                    </div>
                    <div class="stat-icon success"><i class="fas fa-check-circle"></i></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-card-header">
                    <div>
                        <div class="stat-value">₹<?php echo number_format($biznexaData['unpaid_amount'], 0); ?></div>
                        <div class="stat-label">Unpaid Amount</div>
                    </div>
                    <div class="stat-icon danger"><i class="fas fa-exclamation-circle"></i></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-card-header">
                    <div>
                        <div class="stat-value"><?php echo $biznexaData['total_bills']; ?></div>
                        <div class="stat-label">Total Bills</div>
                    </div>
                    <div class="stat-icon" style="background:#e0f2fe;color:#0284c7;"><i class="fas fa-file-alt"></i></div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-4 mb-4">
                <div class="content-card" style="background: linear-gradient(135deg, #0d6efd, #0056b3); color: white;">
                    <h5 style="opacity:0.85; margin-bottom:5px;"><i class="fas fa-chart-line me-2"></i>BizNexa Profit</h5>
                    <div style="font-size:2rem; font-weight:700;">₹<?php echo number_format($biznexaData['net_profit'], 0); ?></div>
                    <small style="opacity:0.8;">Paid ₹<?php echo number_format($biznexaData['paid_amount'], 0); ?> − Expenses ₹<?php echo number_format($biznexaData['total_expenses'], 0); ?></small>
                </div>
            </div>
            <div class="col-lg-4 mb-4">
                <div class="content-card">
                    <div class="card-header-flex"><h2 class="card-title">BizNexa Expenses</h2></div>
                    <div style="font-size:1.75rem; font-weight:700; color:var(--danger);">₹<?php echo number_format($biznexaData['total_expenses'], 0); ?></div>
                    <a href="expenses.php?category=biznexa" class="btn btn-sm btn-outline-primary mt-2">View All</a>
                </div>
            </div>
            <div class="col-lg-4 mb-4">
                <div class="content-card">
                    <div class="card-header-flex"><h2 class="card-title">Quick Actions</h2></div>
                    <div class="d-flex flex-column gap-2">
                        <a href="billing.php?action=new" class="btn btn-sm btn-primary"><i class="fas fa-plus me-2"></i>New Bill</a>
                        <a href="clients.php" class="btn btn-sm btn-outline-primary"><i class="fas fa-users me-2"></i>Manage Clients</a>
                        <a href="expenses.php" class="btn btn-sm btn-outline-danger"><i class="fas fa-receipt me-2"></i>Add Expense</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="content-card">
            <div class="card-header-flex">
                <h2 class="card-title">Recent Bills</h2>
                <a href="billing.php" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead><tr><th>Bill #</th><th>Client</th><th>Amount</th><th>Status</th><th>Payment</th><th>Date</th></tr></thead>
                    <tbody>
                        <?php if (!empty($biznexaData['recent_bills'])): ?>
                            <?php foreach ($biznexaData['recent_bills'] as $bill): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($bill['bill_number']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($bill['client_name']); ?></td>
                                    <td><strong>₹<?php echo number_format($bill['total_amount'], 2); ?></strong></td>
                                    <td><span class="badge bg-<?php echo $bill['status'] === 'paid' ? 'success' : ($bill['status'] === 'sent' ? 'info' : 'secondary'); ?>"><?php echo ucfirst($bill['status']); ?></span></td>
                                    <td><span class="badge bg-<?php echo $bill['payment_status'] === 'paid' ? 'success' : ($bill['payment_status'] === 'partial' ? 'warning' : 'danger'); ?>"><?php echo ucfirst($bill['payment_status']); ?></span></td>
                                    <td><?php echo date('M d, Y', strtotime($bill['bill_date'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center text-muted">No bills yet</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- TAB 3: INSPECTION                            -->
    <!-- ============================================ -->
    <div class="tab-panel" id="tab-inspection">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-card-header">
                    <div>
                        <div class="stat-value">₹<?php echo number_format($inspData['total_fees'], 0); ?></div>
                        <div class="stat-label">Total Fees Worked</div>
                    </div>
                    <div class="stat-icon" style="background:#ede9fe;color:#7c3aed;"><i class="fas fa-file-invoice-dollar"></i></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-card-header">
                    <div>
                        <div class="stat-value">₹<?php echo number_format($inspData['total_earnings'], 0); ?></div>
                        <div class="stat-label">Total Earnings</div>
                    </div>
                    <div class="stat-icon success"><i class="fas fa-rupee-sign"></i></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-card-header">
                    <div>
                        <div class="stat-value">₹<?php echo number_format($inspData['pending_amount'], 0); ?></div>
                        <div class="stat-label">Pending Amount</div>
                    </div>
                    <div class="stat-icon danger"><i class="fas fa-exclamation-circle"></i></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-card-header">
                    <div>
                        <div class="stat-value"><?php echo $inspData['total_files']; ?></div>
                        <div class="stat-label">Total Files</div>
                    </div>
                    <div class="stat-icon primary"><i class="fas fa-folder-open"></i></div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-4 mb-4">
                <div class="content-card" style="background: linear-gradient(135deg, #f59e0b, #d97706); color: white;">
                    <h5 style="opacity:0.85; margin-bottom:5px;"><i class="fas fa-chart-line me-2"></i>Inspection Profit</h5>
                    <div style="font-size:2rem; font-weight:700;">₹<?php echo number_format($inspData['net_profit'], 0); ?></div>
                    <small style="opacity:0.8;">Earned ₹<?php echo number_format($inspData['total_earnings'], 0); ?> − Expenses ₹<?php echo number_format($inspData['total_expenses'], 0); ?></small>
                </div>
            </div>
            <div class="col-lg-4 mb-4">
                <div class="content-card">
                    <div class="card-header-flex"><h2 class="card-title">Inspection Expenses</h2></div>
                    <div style="font-size:1.75rem; font-weight:700; color:var(--danger);">₹<?php echo number_format($inspData['total_expenses'], 0); ?></div>
                    <a href="expenses.php?category=inspection" class="btn btn-sm btn-outline-primary mt-2">View All</a>
                </div>
            </div>
            <div class="col-lg-4 mb-4">
                <div class="content-card">
                    <div class="card-header-flex"><h2 class="card-title">Quick Actions</h2></div>
                    <div class="d-flex flex-column gap-2">
                        <a href="inspection/files.php" class="btn btn-sm btn-warning"><i class="fas fa-plus me-2"></i>New File</a>
                        <a href="inspection/index.php" class="btn btn-sm btn-outline-warning"><i class="fas fa-chart-line me-2"></i>Full Dashboard</a>
                        <a href="expenses.php" class="btn btn-sm btn-outline-danger"><i class="fas fa-receipt me-2"></i>Add Expense</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="content-card">
            <div class="card-header-flex">
                <h2 class="card-title">Recent Inspection Files</h2>
                <a href="inspection/files.php" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead><tr><th>File #</th><th>Date</th><th>Customer</th><th>Bank</th><th>Type</th><th>Commission</th><th>Status</th></tr></thead>
                    <tbody>
                        <?php if (!empty($inspData['recent_files'])): ?>
                            <?php foreach ($inspData['recent_files'] as $file): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($file['file_number']); ?></strong></td>
                                    <td><?php echo $file['file_date'] ? date('d M', strtotime($file['file_date'])) : '-'; ?></td>
                                    <td><?php echo htmlspecialchars($file['customer_name'] ?? '-'); ?></td>
                                    <td><small><?php echo htmlspecialchars($file['bank_name'] ?? '-'); ?></small></td>
                                    <td><span class="badge bg-<?php echo ($file['file_type'] ?? '') === 'office' ? 'info' : 'primary'; ?>"><?php echo ucfirst($file['file_type'] ?? '-'); ?></span></td>
                                    <td>₹<?php echo number_format($file['commission'] ?? 0, 0); ?></td>
                                    <td><?php
                                        if (($file['file_type'] ?? '') === 'office') { echo '<span class="text-muted">NA</span>'; }
                                        else { $colors = ['due'=>'danger','paid'=>'success','partially'=>'warning']; echo '<span class="badge bg-'.($colors[$file['payment_status']??'']??'secondary').'">'.ucfirst($file['payment_status']??'-').'</span>'; }
                                    ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="text-center text-muted">No files yet</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- TAB 4: GENERAL                               -->
    <!-- ============================================ -->
    <div class="tab-panel" id="tab-general">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-card-header">
                    <div>
                        <div class="stat-value" style="color:#10b981;">₹<?php echo number_format($generalData['total_income'], 0); ?></div>
                        <div class="stat-label">Total Income</div>
                    </div>
                    <div class="stat-icon success"><i class="fas fa-arrow-down"></i></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-card-header">
                    <div>
                        <div class="stat-value" style="color:#dc3545;">₹<?php echo number_format($generalData['total_expenses'], 0); ?></div>
                        <div class="stat-label">Total Expenses</div>
                    </div>
                    <div class="stat-icon danger"><i class="fas fa-arrow-up"></i></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-card-header">
                    <div>
                        <div class="stat-value" style="color: <?php echo $generalData['net_balance'] >= 0 ? '#10b981' : '#dc3545'; ?>">
                            ₹<?php echo number_format($generalData['net_balance'], 0); ?>
                        </div>
                        <div class="stat-label">Net Balance</div>
                    </div>
                    <div class="stat-icon" style="background:<?php echo $generalData['net_balance'] >= 0 ? 'rgba(16,185,129,0.1)' : 'rgba(220,53,69,0.1)'; ?>;color:<?php echo $generalData['net_balance'] >= 0 ? '#10b981' : '#dc3545'; ?>"><i class="fas fa-balance-scale"></i></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-card-header">
                    <div>
                        <div class="stat-value"><?php echo $generalData['total_records']; ?></div>
                        <div class="stat-label">Total Records</div>
                    </div>
                    <div class="stat-icon" style="background:#ede9fe;color:#8b5cf6;"><i class="fas fa-list-alt"></i></div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-4 mb-4">
                <div class="content-card" style="background: linear-gradient(135deg, #8b5cf6, #6d28d9); color: white;">
                    <h5 style="opacity:0.85; margin-bottom:5px;"><i class="fas fa-wallet me-2"></i>General Balance</h5>
                    <div style="font-size:2rem; font-weight:700;">₹<?php echo number_format($generalData['net_balance'], 0); ?></div>
                    <small style="opacity:0.8;">Income ₹<?php echo number_format($generalData['total_income'], 0); ?> − Expenses ₹<?php echo number_format($generalData['total_expenses'], 0); ?></small>
                </div>
            </div>
            <div class="col-lg-4 mb-4">
                <div class="content-card">
                    <div class="card-header-flex"><h2 class="card-title">Quick Actions</h2></div>
                    <div class="d-flex flex-column gap-2">
                        <a href="expenses.php?category=general" class="btn btn-sm btn-outline-primary"><i class="fas fa-list me-2"></i>All General Records</a>
                        <a href="expenses.php?category=general&type=income" class="btn btn-sm btn-outline-success"><i class="fas fa-plus me-2"></i>View Income</a>
                        <a href="expenses.php?category=general&type=expense" class="btn btn-sm btn-outline-danger"><i class="fas fa-minus me-2"></i>View Expenses</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mb-4">
                <div class="content-card">
                    <div class="card-header-flex"><h2 class="card-title">Add New</h2></div>
                    <div class="d-flex flex-column gap-2">
                        <a href="expenses.php" class="btn btn-sm btn-primary"><i class="fas fa-plus me-2"></i>Add Record</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="content-card">
            <div class="card-header-flex">
                <h2 class="card-title">Recent General Records</h2>
                <a href="expenses.php?category=general" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead><tr><th>Date</th><th>Title</th><th>Type</th><th>Amount</th><th>Description</th></tr></thead>
                    <tbody>
                        <?php if (!empty($generalData['recent_records'])): ?>
                            <?php foreach ($generalData['recent_records'] as $rec): ?>
                                <tr>
                                    <td><?php echo date('M d, Y', strtotime($rec['expense_date'])); ?></td>
                                    <td><strong><?php echo htmlspecialchars($rec['title']); ?></strong></td>
                                    <td><span class="badge type-badge-<?php echo $rec['type']; ?>"><?php echo ucfirst($rec['type']); ?></span></td>
                                    <td><strong class="<?php echo $rec['type'] === 'income' ? 'text-success' : 'text-danger'; ?>"><?php echo $rec['type'] === 'income' ? '+' : '-'; ?>₹<?php echo number_format($rec['amount'], 2); ?></strong></td>
                                    <td><small class="text-muted"><?php echo htmlspecialchars($rec['description'] ?? '-'); ?></small></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center text-muted">No general records yet</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<script>
// Preserve tab + filter on tab switch
function switchTab(tabName) {
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    document.querySelector(`.tab-btn[data-tab="${tabName}"]`).classList.add('active');
    document.querySelectorAll('.tab-panel').forEach(panel => panel.classList.remove('active'));
    document.getElementById('tab-' + tabName).classList.add('active');
    window.location.hash = tabName;
    // Update hidden tab field in custom date form
    document.getElementById('dateFilterTab').value = tabName;
    if (tabName === 'main' && !window.mainChartRendered) { renderMainChart(); }
}

// Preserve tab hash in preset links
document.addEventListener('DOMContentLoaded', () => {
    const hash = window.location.hash.replace('#', '');
    const savedTab = '<?php echo htmlspecialchars($currentTab); ?>';
    const activeTab = ['main','biznexa','inspection','general'].includes(hash) ? hash :
                      (['main','biznexa','inspection','general'].includes(savedTab) ? savedTab : 'main');
    switchTab(activeTab);

    // Add hash to preset links so tab is preserved
    document.querySelectorAll('.preset-btn').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const url = new URL(this.href, window.location.origin);
            url.hash = activeTab;
            window.location.href = url.toString();
        });
    });
});

function renderMainChart() {
    const ctx = document.getElementById('mainChart');
    if (!ctx) return;
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($monthLabels); ?>,
            datasets: [
                { label:'Earnings', data:<?php echo json_encode($mainData['monthly_earnings']); ?>, backgroundColor:'rgba(16,185,129,0.7)', borderColor:'rgba(16,185,129,1)', borderWidth:1, borderRadius:6 },
                { label:'Expenses', data:<?php echo json_encode($mainData['monthly_expenses']); ?>, backgroundColor:'rgba(220,53,69,0.7)', borderColor:'rgba(220,53,69,1)', borderWidth:1, borderRadius:6 }
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { position:'top', labels:{ usePointStyle:true, padding:20 } } },
            scales: { y: { beginAtZero:true, ticks:{ callback:function(v){ return '₹'+v.toLocaleString('en-IN'); } } } }
        }
    });
    window.mainChartRendered = true;
}
</script>

<?php include 'includes/footer.php'; ?>
