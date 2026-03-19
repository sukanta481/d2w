<?php
require_once __DIR__ . '/../includes/auth.php';
$auth->requireLogin();

require_once __DIR__ . '/../config/database.php';
$database = new Database();
$db = $database->connect();

// Filters
$filterMonth = $_GET['month'] ?? '';
$filterYear = $_GET['year'] ?? '';
$filterType = $_GET['file_type'] ?? '';
$filterDateBasis = $_GET['date_basis'] ?? 'file';
$hasDateFilter = ($filterMonth !== '' && $filterYear !== '');

$dateField = $filterDateBasis === 'file' ? 'f.file_date' : 'DATE(f.updated_at)';
$baseFilesQuery = [];
if ($hasDateFilter) {
    $monthStart = "{$filterYear}-{$filterMonth}-01";
    $monthEnd = date('Y-m-t', strtotime($monthStart));
    $baseFilesQuery['date_from'] = $monthStart;
    $baseFilesQuery['date_to'] = $monthEnd;
    $baseFilesQuery['date_basis'] = $filterDateBasis;
}
if ($filterType) {
    $baseFilesQuery['file_type'] = $filterType;
}
$allFilesUrl = 'files.php?' . http_build_query($baseFilesQuery);
$pendingFilesQuery = $baseFilesQuery;
$pendingFilesQuery['status_group'] = 'pending';
$pendingFilesUrl = 'files.php?' . http_build_query($pendingFilesQuery);
$totalFilesUrl = 'files.php?' . http_build_query($baseFilesQuery + ['metric' => 'total_files']);
$totalEarningsUrl = 'files.php?' . http_build_query($baseFilesQuery + ['metric' => 'total_earnings']);
$activeSourcesUrl = 'files.php?' . http_build_query($baseFilesQuery + ['metric' => 'active_sources']);
$pendingMetricUrl = 'files.php?' . http_build_query($pendingFilesQuery + ['metric' => 'pending_payments']);

try {
    $where = "WHERE 1=1";
    $params = [];

    if ($hasDateFilter) {
        $where .= " AND {$dateField} BETWEEN :start AND :end";
        $params[':start'] = $monthStart;
        $params[':end'] = $monthEnd;
    }

    if ($filterType) {
        $where .= " AND f.file_type = :type";
        $params[':type'] = $filterType;
    }

    // Stat 1: Total Files
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM inspection_files f {$where}");
    $stmt->execute($params);
    $totalFiles = $stmt->fetch()['total'];

    // Stat 2: Total Earnings (gross_amount)
    $stmt = $db->prepare("SELECT COALESCE(SUM(f.gross_amount), 0) as total FROM inspection_files f {$where}");
    $stmt->execute($params);
    $totalEarnings = $stmt->fetch()['total'];

    // Stat 3: Pending Payments (self files with due/partially)
    $pendingWhere = $where . " AND f.file_type = 'self' AND f.payment_status IN ('due', 'partially')";
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM inspection_files f {$pendingWhere}");
    $stmt->execute($params);
    $pendingPayments = $stmt->fetch()['total'];

    // Stat 4: Active Sources
    $stmt = $db->prepare("SELECT COUNT(DISTINCT f.source_id) as total FROM inspection_files f {$where}");
    $stmt->execute($params);
    $activeSources = $stmt->fetch()['total'];

    // Source-wise summary
    $stmt = $db->prepare("SELECT isrc.source_name, COUNT(*) as file_count, COALESCE(SUM(f.gross_amount), 0) as total_earnings
        FROM inspection_files f
        JOIN inspection_sources isrc ON f.source_id = isrc.id
        {$where}
        GROUP BY f.source_id, isrc.source_name
        ORDER BY total_earnings DESC");
    $stmt->execute($params);
    $sourceSummary = $stmt->fetchAll();

    // Recent 10 files
    $stmt = $db->prepare("SELECT f.*, ib.bank_name, isrc.source_name
        FROM inspection_files f
        LEFT JOIN inspection_banks ib ON f.bank_id = ib.id
        LEFT JOIN inspection_sources isrc ON f.source_id = isrc.id
        {$where}
        ORDER BY f.file_date DESC, f.id DESC LIMIT 10");
    $stmt->execute($params);
    $recentFiles = $stmt->fetchAll();

} catch(PDOException $e) {
    $totalFiles = $totalEarnings = $pendingPayments = $activeSources = 0;
    $sourceSummary = $recentFiles = [];
    error_log("Dashboard error: " . $e->getMessage());
}

$pageTitle = 'Inspection Dashboard';
$basePath = '../';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/_responsive.php';
?>

<div class="admin-content inspection-page inspection-dashboard-page">
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h1 class="page-title">Inspection Dashboard</h1>
            <p class="page-subtitle"><?php echo $hasDateFilter ? date('F Y', strtotime($monthStart)) . ' Overview' : 'All Time Overview'; ?></p>
        </div>
        <div class="inspection-toolbar">
            <a href="files.php" class="btn btn-primary"><i class="fas fa-plus me-2"></i>New File</a>
        </div>
    </div>

    <!-- Month/Year Filter -->
    <form method="GET" class="row mb-4 g-2 inspection-filter-form">
        <div class="col-auto">
            <select name="month" class="form-select">
                <option value="">All Months</option>
                <?php for ($m = 1; $m <= 12; $m++): ?>
                    <option value="<?php echo str_pad($m, 2, '0', STR_PAD_LEFT); ?>" <?php echo $filterMonth == str_pad($m, 2, '0', STR_PAD_LEFT) ? 'selected' : ''; ?>>
                        <?php echo date('F', mktime(0, 0, 0, $m, 1)); ?>
                    </option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="col-auto">
            <select name="year" class="form-select">
                <option value="">All Years</option>
                <?php for ($y = date('Y') + 1; $y >= 2020; $y--): ?>
                    <option value="<?php echo $y; ?>" <?php echo $filterYear == (string)$y ? 'selected' : ''; ?>><?php echo $y; ?></option>
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
            <select name="date_basis" class="form-select">
                <option value="updated" <?php echo $filterDateBasis === 'updated' ? 'selected' : ''; ?>>Updated Month</option>
                <option value="file" <?php echo $filterDateBasis === 'file' ? 'selected' : ''; ?>>File Date Month</option>
            </select>
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary"><i class="fas fa-filter me-1"></i>Filter</button>
        </div>
        <div class="col-auto">
            <a href="index.php" class="btn btn-secondary"><i class="fas fa-redo"></i></a>
        </div>
    </form>

    <!-- Stat Cards -->
    <div class="stats-grid">
        <a href="<?php echo htmlspecialchars($totalFilesUrl); ?>" class="stat-card text-decoration-none text-reset">
            <div class="stat-card-header">
                <div><div class="stat-value"><?php echo $totalFiles; ?></div><div class="stat-label">Total Files</div></div>
                <div class="stat-icon primary"><i class="fas fa-folder-open"></i></div>
            </div>
        </a>
        <a href="<?php echo htmlspecialchars($totalEarningsUrl); ?>" class="stat-card text-decoration-none text-reset">
            <div class="stat-card-header">
                <div><div class="stat-value">&#8377;<?php echo number_format($totalEarnings, 0); ?></div><div class="stat-label">Total Earnings</div></div>
                <div class="stat-icon success"><i class="fas fa-rupee-sign"></i></div>
            </div>
        </a>
        <a href="<?php echo htmlspecialchars($pendingMetricUrl); ?>" class="stat-card text-decoration-none text-reset">
            <div class="stat-card-header">
                <div><div class="stat-value"><?php echo $pendingPayments; ?></div><div class="stat-label">Pending Payments</div></div>
                <div class="stat-icon warning"><i class="fas fa-clock"></i></div>
            </div>
        </a>
        <a href="<?php echo htmlspecialchars($activeSourcesUrl); ?>" class="stat-card text-decoration-none text-reset">
            <div class="stat-card-header">
                <div><div class="stat-value"><?php echo $activeSources; ?></div><div class="stat-label">Active Sources</div></div>
                <div class="stat-icon info"><i class="fas fa-users"></i></div>
            </div>
        </a>
    </div>

    <div class="row mt-4">
        <!-- Source Summary -->
        <div class="col-lg-5">
            <div class="content-card">
                <div class="card-header-flex"><h5>Source-wise Summary</h5></div>
                <div class="table-responsive">
                    <div class="inspection-table-mobile-note">Source summary is shown as stacked cards on mobile.</div>
                    <table class="data-table inspection-table">
                        <thead><tr><th>Source</th><th>Files</th><th>Earnings</th></tr></thead>
                        <tbody>
                            <?php if (!empty($sourceSummary)): ?>
                                <?php foreach ($sourceSummary as $row): ?>
                                    <tr>
                                        <td data-label="Source"><?php echo htmlspecialchars($row['source_name']); ?></td>
                                        <td data-label="Files"><span class="badge bg-primary"><?php echo $row['file_count']; ?></span></td>
                                        <td data-label="Earnings"><strong>&#8377;<?php echo number_format($row['total_earnings'], 0); ?></strong></td>
                                    </tr>
                                <?php endforeach; ?>
                                <tr class="table-light">
                                    <td data-label="Source"><strong>Total</strong></td>
                                    <td data-label="Files"><strong><?php echo array_sum(array_column($sourceSummary, 'file_count')); ?></strong></td>
                                    <td data-label="Earnings"><strong>&#8377;<?php echo number_format(array_sum(array_column($sourceSummary, 'total_earnings')), 0); ?></strong></td>
                                </tr>
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
                    <div class="inspection-table-mobile-note">Recent files are shown as stacked cards on mobile.</div>
                    <table class="data-table inspection-table">
                        <thead><tr><th>File #</th><th>Date</th><th>Customer</th><th>Bank</th><th>Type</th><th>Commission</th><th>Status</th></tr></thead>
                        <tbody>
                            <?php if (!empty($recentFiles)): ?>
                                <?php foreach ($recentFiles as $file): ?>
                                    <tr>
                                        <td data-label="File #"><strong><?php echo htmlspecialchars($file['file_number']); ?></strong></td>
                                        <td data-label="Date"><?php echo date('d M', strtotime($file['file_date'])); ?></td>
                                        <td data-label="Customer"><?php echo htmlspecialchars($file['customer_name']); ?></td>
                                        <td data-label="Bank"><small><?php echo htmlspecialchars($file['bank_name']); ?></small></td>
                                        <td data-label="Type"><span class="badge bg-<?php echo $file['file_type'] === 'office' ? 'info' : 'primary'; ?>"><?php echo ucfirst($file['file_type']); ?></span></td>
                                        <td data-label="Commission">&#8377;<?php echo number_format($file['commission'], 0); ?></td>
                                        <td data-label="Status"><?php
                                            if ($file['file_type'] === 'office') {
                                                echo '<span class="text-muted">NA</span>';
                                            } else {
                                                $colors = ['due' => 'danger', 'paid' => 'success', 'partially' => 'warning'];
                                                echo '<span class="badge bg-' . ($colors[$file['payment_status']] ?? 'secondary') . '">' . ucfirst($file['payment_status'] ?? '-') . '</span>';
                                            }
                                        ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="7" class="text-center text-muted py-3">No files for this period</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
