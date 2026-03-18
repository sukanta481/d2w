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
        'fees' => null, 'office_amount' => null, 'commission' => 0,
        'amount' => null, 'paid_to_office' => null, 'payment_status' => null,
        'payment_mode_id' => null, 'report_status' => null, 'location' => null,
    ];

    if ($data['file_type'] === 'office') {
        $result['location'] = $data['location'] ?? null;
        $result['commission'] = ($result['location'] === 'kolkata') ? 300 : 350;
    } else {
        $fees = floatval($data['fees'] ?? 0);
        $result['fees'] = $fees;
        $result['commission'] = round($fees * 0.30, 2);
        $result['office_amount'] = round($fees * 0.70, 2);
        $result['report_status'] = !empty($data['report_status']) ? $data['report_status'] : null;
        $result['payment_mode_id'] = !empty($data['payment_mode_id']) ? $data['payment_mode_id'] : null;
        $result['payment_status'] = $data['payment_status'] ?? 'due';
        $result['paid_to_office'] = $data['paid_to_office'] ?? 'due';

        if ($result['payment_status'] === 'paid') {
            $result['amount'] = $fees;
        } elseif ($result['payment_status'] === 'partially') {
            $result['amount'] = floatval($data['amount'] ?? 0);
        }
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
        $stmt->execute([':bank_id' => intval($_GET['bank_id'])]);
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

    $countStmt = $db->prepare("SELECT COUNT(*) as total FROM inspection_files f {$where}");
    $countStmt->execute($params);
    $totalFiles = $countStmt->fetch()['total'];
    $totalPages = max(1, ceil($totalFiles / $perPage));

    $query = "SELECT f.*, ib.bank_name, ibr.branch_name, isrc.source_name, ipm.mode_name, ima.account_name as received_account_name
              FROM inspection_files f
              LEFT JOIN inspection_banks ib ON f.bank_id = ib.id
              LEFT JOIN inspection_branches ibr ON f.branch_id = ibr.id
              LEFT JOIN inspection_sources isrc ON f.source_id = isrc.id
              LEFT JOIN inspection_payment_modes ipm ON f.payment_mode_id = ipm.id
              LEFT JOIN inspection_my_accounts ima ON f.received_account_id = ima.id
              {$where}
              ORDER BY f.file_date DESC, f.id DESC
              LIMIT " . intval($perPage) . " OFFSET " . intval($offset);

    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $files = $stmt->fetchAll();

    $banks = $db->query("SELECT id, bank_name FROM inspection_banks WHERE status = 'active' ORDER BY bank_name")->fetchAll();
    $sources = $db->query("SELECT id, source_name FROM inspection_sources WHERE status = 'active' ORDER BY source_name")->fetchAll();
    $paymentModes = $db->query("SELECT id, mode_name FROM inspection_payment_modes WHERE status = 'active' ORDER BY mode_name")->fetchAll();
    $myAccounts = $db->query("SELECT id, account_name FROM inspection_my_accounts WHERE status = 'active' ORDER BY account_name")->fetchAll();

} catch(PDOException $e) {
    $files = $banks = $sources = $paymentModes = $myAccounts = [];
    $totalFiles = 0; $totalPages = 1;
    error_log("Files fetch error: " . $e->getMessage());
}

// Build query string for pagination links
$queryParams = $_GET;
unset($queryParams['page']);
$queryString = http_build_query($queryParams);

$pageTitle = 'Inspection Files';
$basePath = '../';
include __DIR__ . '/../includes/header.php';
?>

<div class="admin-content">
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h1 class="page-title">Inspection Files</h1>
            <p class="page-subtitle">Manage property inspection cases</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addFileModal">
            <i class="fas fa-plus me-2"></i>New File
        </button>
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
        <!-- Filters -->
        <form method="GET" class="row mb-4 g-2">
            <div class="col-md-3">
                <div class="d-flex gap-2">
                    <input type="text" name="search" class="form-control" placeholder="Search name, file#, address..." value="<?php echo htmlspecialchars($searchQuery); ?>">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                </div>
            </div>
            <div class="col-md-2"><input type="date" name="date_from" class="form-control" value="<?php echo htmlspecialchars($dateFrom); ?>" placeholder="From"></div>
            <div class="col-md-2"><input type="date" name="date_to" class="form-control" value="<?php echo htmlspecialchars($dateTo); ?>" placeholder="To"></div>
            <div class="col-md-1">
                <select name="file_type" class="form-select">
                    <option value="">Type</option>
                    <option value="office" <?php echo $typeFilter === 'office' ? 'selected' : ''; ?>>Office</option>
                    <option value="self" <?php echo $typeFilter === 'self' ? 'selected' : ''; ?>>Self</option>
                </select>
            </div>
            <div class="col-md-1">
                <select name="payment_status" class="form-select">
                    <option value="">Status</option>
                    <option value="due" <?php echo $statusFilter === 'due' ? 'selected' : ''; ?>>Due</option>
                    <option value="paid" <?php echo $statusFilter === 'paid' ? 'selected' : ''; ?>>Paid</option>
                    <option value="partially" <?php echo $statusFilter === 'partially' ? 'selected' : ''; ?>>Partial</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="bank_id" class="form-select">
                    <option value="">All Banks</option>
                    <?php foreach ($banks as $b): ?>
                        <option value="<?php echo $b['id']; ?>" <?php echo $bankFilter == $b['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($b['bank_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-1"><a href="files.php" class="btn btn-secondary w-100" title="Reset"><i class="fas fa-redo"></i></a></div>
        </form>

        <!-- Files Table -->
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>File #</th><th>Date</th><th>Type</th><th>Customer</th>
                        <th>Bank / Branch</th><th>Fees</th><th>Commission</th>
                        <th>Gross</th><th>Payment</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($files)): ?>
                        <?php foreach ($files as $file): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($file['file_number']); ?></strong></td>
                                <td><?php echo date('d M Y', strtotime($file['file_date'])); ?></td>
                                <td><span class="badge bg-<?php echo $file['file_type'] === 'office' ? 'info' : 'primary'; ?>"><?php echo ucfirst($file['file_type']); ?></span>
                                    <?php if ($file['location']): ?><br><small class="text-muted"><?php echo $file['location'] === 'kolkata' ? 'Kolkata' : 'Out of Kolkata'; ?></small><?php endif; ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($file['customer_name']); ?>
                                    <?php if ($file['customer_phone']): ?><br><small class="text-muted"><i class="fas fa-phone"></i> <?php echo htmlspecialchars($file['customer_phone']); ?></small><?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($file['bank_name']); ?><br><small class="text-muted"><?php echo htmlspecialchars($file['branch_name']); ?></small></td>
                                <td><?php echo $file['fees'] !== null ? '&#8377;' . number_format($file['fees'], 0) : '<span class="text-muted">NA</span>'; ?></td>
                                <td>&#8377;<?php echo number_format($file['commission'], 0); ?></td>
                                <td><strong>&#8377;<?php echo number_format($file['gross_amount'], 0); ?></strong></td>
                                <td><?php
                                    if ($file['file_type'] === 'office') {
                                        echo '<span class="text-muted">NA</span>';
                                    } else {
                                        $colors = ['due' => 'danger', 'paid' => 'success', 'partially' => 'warning'];
                                        echo '<span class="badge bg-' . ($colors[$file['payment_status']] ?? 'secondary') . '">' . ucfirst($file['payment_status'] ?? '-') . '</span>';
                                    }
                                ?></td>
                                <td>
                                    <button class="btn btn-sm btn-icon" data-bs-toggle="modal" data-bs-target="#viewFileModal<?php echo $file['id']; ?>" title="View"><i class="fas fa-eye"></i></button>
                                    <button class="btn btn-sm btn-icon" data-bs-toggle="modal" data-bs-target="#editFileModal<?php echo $file['id']; ?>" title="Edit"><i class="fas fa-edit"></i></button>
                                    <button class="btn btn-sm btn-icon text-danger" data-bs-toggle="modal" data-bs-target="#deleteFileModal<?php echo $file['id']; ?>" title="Delete"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>

                            <!-- View File Modal -->
                            <div class="modal fade" id="viewFileModal<?php echo $file['id']; ?>" tabindex="-1">
                                <div class="modal-dialog modal-lg"><div class="modal-content">
                                    <div class="modal-header"><h5 class="modal-title">File: <?php echo htmlspecialchars($file['file_number']); ?></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-4 mb-3"><strong>Date:</strong><br><?php echo date('d M Y', strtotime($file['file_date'])); ?></div>
                                            <div class="col-md-4 mb-3"><strong>File Type:</strong><br><span class="badge bg-<?php echo $file['file_type'] === 'office' ? 'info' : 'primary'; ?>"><?php echo ucfirst($file['file_type']); ?></span>
                                                <?php if ($file['location']): ?> - <?php echo $file['location'] === 'kolkata' ? 'Kolkata' : 'Out of Kolkata'; ?><?php endif; ?>
                                            </div>
                                            <div class="col-md-4 mb-3"><strong>Source:</strong><br><?php echo htmlspecialchars($file['source_name']); ?></div>
                                            <div class="col-md-6 mb-3"><strong>Customer:</strong><br><?php echo htmlspecialchars($file['customer_name']); ?><?php if ($file['customer_phone']): ?> | <?php echo htmlspecialchars($file['customer_phone']); ?><?php endif; ?></div>
                                            <div class="col-md-6 mb-3"><strong>Property Address:</strong><br><?php echo htmlspecialchars($file['property_address']); ?></div>
                                            <div class="col-md-4 mb-3"><strong>Property Value:</strong><br>&#8377;<?php echo number_format($file['property_value'], 0); ?></div>
                                            <div class="col-md-4 mb-3"><strong>Bank:</strong><br><?php echo htmlspecialchars($file['bank_name']); ?></div>
                                            <div class="col-md-4 mb-3"><strong>Branch:</strong><br><?php echo htmlspecialchars($file['branch_name']); ?></div>
                                        </div>
                                        <hr>
                                        <div class="row">
                                            <div class="col-md-3 mb-3"><strong>Fees:</strong><br><?php echo $file['fees'] !== null ? '&#8377;' . number_format($file['fees'], 2) : 'NA'; ?></div>
                                            <div class="col-md-3 mb-3"><strong>Report Status:</strong><br><?php echo $file['report_status'] ? ucfirst(str_replace('_', ' ', $file['report_status'])) : 'NA'; ?></div>
                                            <div class="col-md-3 mb-3"><strong>Payment Mode:</strong><br><?php echo $file['mode_name'] ?? 'NA'; ?></div>
                                            <div class="col-md-3 mb-3"><strong>Payment Status:</strong><br><?php
                                                if ($file['payment_status']) {
                                                    $colors = ['due' => 'danger', 'paid' => 'success', 'partially' => 'warning'];
                                                    echo '<span class="badge bg-' . ($colors[$file['payment_status']] ?? 'secondary') . '">' . ucfirst($file['payment_status']) . '</span>';
                                                } else { echo 'NA'; }
                                            ?></div>
                                            <div class="col-md-3 mb-3"><strong>Amount Received:</strong><br><?php echo $file['amount'] !== null ? '&#8377;' . number_format($file['amount'], 2) : 'NA'; ?></div>
                                            <div class="col-md-3 mb-3"><strong>Paid to Office:</strong><br><?php echo $file['paid_to_office'] ? ucfirst($file['paid_to_office']) : 'NA'; ?></div>
                                            <div class="col-md-3 mb-3"><strong>Office Amount:</strong><br><?php echo $file['office_amount'] !== null ? '&#8377;' . number_format($file['office_amount'], 2) : 'NA'; ?></div>
                                            <div class="col-md-3 mb-3"><strong>Received In:</strong><br><?php echo $file['received_account_name'] ?? '-'; ?></div>
                                        </div>
                                        <hr>
                                        <div class="row">
                                            <div class="col-md-4 mb-3"><strong>Commission:</strong><br><span class="text-success fw-bold">&#8377;<?php echo number_format($file['commission'], 2); ?></span></div>
                                            <div class="col-md-4 mb-3"><strong>Extra Amount:</strong><br>&#8377;<?php echo number_format($file['extra_amount'], 2); ?></div>
                                            <div class="col-md-4 mb-3"><strong>Gross Amount:</strong><br><span class="text-primary fw-bold fs-5">&#8377;<?php echo number_format($file['gross_amount'], 2); ?></span></div>
                                        </div>
                                        <?php if ($file['notes']): ?>
                                            <hr><strong>Notes:</strong><br><?php echo nl2br(htmlspecialchars($file['notes'])); ?>
                                        <?php endif; ?>
                                    </div>
                                </div></div>
                            </div>

                            <!-- Edit File Modal -->
                            <div class="modal fade" id="editFileModal<?php echo $file['id']; ?>" tabindex="-1">
                                <div class="modal-dialog modal-xl"><div class="modal-content"><form method="POST" id="editForm<?php echo $file['id']; ?>">
                                    <input type="hidden" name="file_id" value="<?php echo $file['id']; ?>">
                                    <div class="modal-header"><h5 class="modal-title">Edit: <?php echo htmlspecialchars($file['file_number']); ?></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-3 mb-3"><label class="form-label">Date</label><input type="date" name="file_date" class="form-control" value="<?php echo $file['file_date']; ?>" required></div>
                                            <div class="col-md-3 mb-3"><label class="form-label">File Type</label>
                                                <select name="file_type" class="form-select" required>
                                                    <option value="office" <?php echo $file['file_type'] === 'office' ? 'selected' : ''; ?>>Office</option>
                                                    <option value="self" <?php echo $file['file_type'] === 'self' ? 'selected' : ''; ?>>Self</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3 mb-3"><label class="form-label">Location</label>
                                                <select name="location" class="form-select">
                                                    <option value="">Select</option>
                                                    <option value="kolkata" <?php echo $file['location'] === 'kolkata' ? 'selected' : ''; ?>>Kolkata</option>
                                                    <option value="out_of_kolkata" <?php echo $file['location'] === 'out_of_kolkata' ? 'selected' : ''; ?>>Out of Kolkata</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3 mb-3"><label class="form-label">Customer Name</label><input type="text" name="customer_name" class="form-control" value="<?php echo htmlspecialchars($file['customer_name']); ?>" required></div>
                                            <div class="col-md-3 mb-3"><label class="form-label">Customer Phone</label><input type="text" name="customer_phone" class="form-control" value="<?php echo htmlspecialchars($file['customer_phone'] ?? ''); ?>"></div>
                                            <div class="col-md-6 mb-3"><label class="form-label">Property Address</label><textarea name="property_address" class="form-control" rows="1" required><?php echo htmlspecialchars($file['property_address']); ?></textarea></div>
                                            <div class="col-md-3 mb-3"><label class="form-label">Property Value (&#8377;)</label><input type="number" name="property_value" class="form-control" step="0.01" value="<?php echo $file['property_value']; ?>" required></div>
                                            <div class="col-md-3 mb-3"><label class="form-label">Bank</label>
                                                <select name="bank_id" class="form-select" required>
                                                    <option value="">Select Bank</option>
                                                    <?php foreach ($banks as $b): ?>
                                                        <option value="<?php echo $b['id']; ?>" <?php echo $file['bank_id'] == $b['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($b['bank_name']); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-3 mb-3"><label class="form-label">Branch</label>
                                                <select name="branch_id" class="form-select" data-selected="<?php echo $file['branch_id']; ?>" required>
                                                    <option value="<?php echo $file['branch_id']; ?>"><?php echo htmlspecialchars($file['branch_name']); ?></option>
                                                </select>
                                            </div>
                                            <div class="col-md-3 mb-3"><label class="form-label">Source</label>
                                                <select name="source_id" class="form-select" required>
                                                    <?php foreach ($sources as $s): ?>
                                                        <option value="<?php echo $s['id']; ?>" <?php echo $file['source_id'] == $s['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($s['source_name']); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-3 mb-3"><label class="form-label">Fees (&#8377;)</label><input type="number" name="fees" class="form-control" step="0.01" value="<?php echo $file['fees']; ?>"></div>
                                            <div class="col-md-3 mb-3"><label class="form-label">Report Status</label>
                                                <select name="report_status" class="form-select">
                                                    <option value="">Select</option>
                                                    <option value="draft" <?php echo $file['report_status'] === 'draft' ? 'selected' : ''; ?>>Draft</option>
                                                    <option value="final_soft" <?php echo $file['report_status'] === 'final_soft' ? 'selected' : ''; ?>>Final Soft Copy</option>
                                                    <option value="final_hard" <?php echo $file['report_status'] === 'final_hard' ? 'selected' : ''; ?>>Final Hard Copy</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3 mb-3"><label class="form-label">Payment Mode</label>
                                                <select name="payment_mode_id" class="form-select">
                                                    <option value="">Select</option>
                                                    <?php foreach ($paymentModes as $pm): ?>
                                                        <option value="<?php echo $pm['id']; ?>" <?php echo $file['payment_mode_id'] == $pm['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($pm['mode_name']); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-3 mb-3"><label class="form-label">Payment Status</label>
                                                <select name="payment_status" class="form-select">
                                                    <option value="">Select</option>
                                                    <option value="due" <?php echo $file['payment_status'] === 'due' ? 'selected' : ''; ?>>Due</option>
                                                    <option value="paid" <?php echo $file['payment_status'] === 'paid' ? 'selected' : ''; ?>>Paid</option>
                                                    <option value="partially" <?php echo $file['payment_status'] === 'partially' ? 'selected' : ''; ?>>Partially</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3 mb-3"><label class="form-label">Amount Received (&#8377;)</label><input type="number" name="amount" class="form-control" step="0.01" value="<?php echo $file['amount']; ?>"></div>
                                            <div class="col-md-3 mb-3"><label class="form-label">Paid to Office</label>
                                                <select name="paid_to_office" class="form-select">
                                                    <option value="">Select</option>
                                                    <option value="paid" <?php echo $file['paid_to_office'] === 'paid' ? 'selected' : ''; ?>>Paid</option>
                                                    <option value="due" <?php echo $file['paid_to_office'] === 'due' ? 'selected' : ''; ?>>Due</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3 mb-3"><label class="form-label">Office Amount (&#8377;)</label><input type="number" name="office_amount" class="form-control" step="0.01" value="<?php echo $file['office_amount']; ?>" readonly></div>
                                            <div class="col-md-3 mb-3"><label class="form-label">Commission (&#8377;)</label><input type="number" name="commission" class="form-control" step="0.01" value="<?php echo $file['commission']; ?>" readonly></div>
                                            <div class="col-md-3 mb-3"><label class="form-label">Extra Amount (&#8377;)</label><input type="number" name="extra_amount" class="form-control" step="0.01" value="<?php echo $file['extra_amount']; ?>"></div>
                                            <div class="col-md-3 mb-3"><label class="form-label">Gross Amount (&#8377;)</label><input type="number" name="gross_amount" class="form-control" step="0.01" value="<?php echo $file['gross_amount']; ?>" readonly></div>
                                            <div class="col-md-3 mb-3"><label class="form-label">Received In</label>
                                                <select name="received_account_id" class="form-select">
                                                    <option value="">Select Account</option>
                                                    <?php foreach ($myAccounts as $a): ?>
                                                        <option value="<?php echo $a['id']; ?>" <?php echo $file['received_account_id'] == $a['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($a['account_name']); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-12 mb-3"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2"><?php echo htmlspecialchars($file['notes'] ?? ''); ?></textarea></div>
                                        </div>
                                    </div>
                                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" name="update_file" class="btn btn-primary">Update File</button></div>
                                </form></div></div>
                            </div>

                            <!-- Delete File Modal -->
                            <div class="modal fade" id="deleteFileModal<?php echo $file['id']; ?>" tabindex="-1">
                                <div class="modal-dialog"><div class="modal-content"><form method="POST">
                                    <input type="hidden" name="delete_file" value="1"><input type="hidden" name="file_id" value="<?php echo $file['id']; ?>">
                                    <div class="modal-header"><h5 class="modal-title">Delete File</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                    <div class="modal-body"><p>Delete file <strong><?php echo htmlspecialchars($file['file_number']); ?></strong> (<?php echo htmlspecialchars($file['customer_name']); ?>)?</p><p class="text-muted mb-0"><i class="fas fa-exclamation-triangle text-warning me-1"></i>This action cannot be undone.</p></div>
                                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-danger">Delete</button></div>
                                </form></div></div>
                            </div>

                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="10" class="text-center text-muted py-5"><i class="fas fa-folder-open fa-3x mb-3 d-block"></i>No files found</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <nav class="mt-3">
                <ul class="pagination justify-content-center mb-0">
                    <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&<?php echo $queryString; ?>">Prev</a>
                    </li>
                    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                        <li class="page-item <?php echo $p === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $p; ?>&<?php echo $queryString; ?>"><?php echo $p; ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&<?php echo $queryString; ?>">Next</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>

        <div class="text-center text-muted mt-2"><small>Showing <?php echo count($files); ?> of <?php echo $totalFiles; ?> files</small></div>
    </div>
</div>

<!-- Add File Modal -->
<div class="modal fade" id="addFileModal" tabindex="-1">
    <div class="modal-dialog modal-xl"><div class="modal-content"><form method="POST" id="addForm">
        <div class="modal-header"><h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Create New Inspection File</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="row">
                <div class="col-md-3 mb-3"><label class="form-label">Date <span class="text-danger">*</span></label><input type="date" name="file_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required></div>
                <div class="col-md-3 mb-3"><label class="form-label">File Type <span class="text-danger">*</span></label>
                    <select name="file_type" class="form-select" required>
                        <option value="">Select Type</option>
                        <option value="office">Office</option>
                        <option value="self">Self</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3"><label class="form-label">Location</label>
                    <select name="location" class="form-select" disabled>
                        <option value="">Select</option>
                        <option value="kolkata">Kolkata</option>
                        <option value="out_of_kolkata">Out of Kolkata</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3"><label class="form-label">Customer Name <span class="text-danger">*</span></label><input type="text" name="customer_name" class="form-control" placeholder="Customer name" required></div>
                <div class="col-md-3 mb-3"><label class="form-label">Customer Phone</label><input type="text" name="customer_phone" class="form-control" placeholder="Phone number"></div>
                <div class="col-md-6 mb-3"><label class="form-label">Property Address <span class="text-danger">*</span></label><textarea name="property_address" class="form-control" rows="1" placeholder="Full property address" required></textarea></div>
                <div class="col-md-3 mb-3"><label class="form-label">Property Value (&#8377;) <span class="text-danger">*</span></label><input type="number" name="property_value" class="form-control" step="0.01" placeholder="0.00" required></div>
                <div class="col-md-3 mb-3"><label class="form-label">Bank <span class="text-danger">*</span></label>
                    <select name="bank_id" class="form-select" required>
                        <option value="">Select Bank</option>
                        <?php foreach ($banks as $b): ?>
                            <option value="<?php echo $b['id']; ?>"><?php echo htmlspecialchars($b['bank_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 mb-3"><label class="form-label">Branch <span class="text-danger">*</span></label>
                    <select name="branch_id" class="form-select" required>
                        <option value="">Select Bank first</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3"><label class="form-label">Source <span class="text-danger">*</span></label>
                    <select name="source_id" class="form-select" required>
                        <option value="">Select Source</option>
                        <?php foreach ($sources as $s): ?>
                            <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['source_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 mb-3"><label class="form-label">Fees (&#8377;)</label><input type="number" name="fees" class="form-control" step="0.01" placeholder="0.00" disabled></div>
                <div class="col-md-3 mb-3"><label class="form-label">Report Status</label>
                    <select name="report_status" class="form-select" disabled>
                        <option value="">Select</option>
                        <option value="draft">Draft</option>
                        <option value="final_soft">Final Soft Copy</option>
                        <option value="final_hard">Final Hard Copy</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3"><label class="form-label">Payment Mode</label>
                    <select name="payment_mode_id" class="form-select" disabled>
                        <option value="">Select</option>
                        <?php foreach ($paymentModes as $pm): ?>
                            <option value="<?php echo $pm['id']; ?>"><?php echo htmlspecialchars($pm['mode_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 mb-3"><label class="form-label">Payment Status</label>
                    <select name="payment_status" class="form-select" disabled>
                        <option value="">Select</option>
                        <option value="due">Due</option>
                        <option value="paid">Paid</option>
                        <option value="partially">Partially</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3"><label class="form-label">Amount Received (&#8377;)</label><input type="number" name="amount" class="form-control" step="0.01" placeholder="0.00" disabled></div>
                <div class="col-md-3 mb-3"><label class="form-label">Paid to Office</label>
                    <select name="paid_to_office" class="form-select" disabled>
                        <option value="">Select</option>
                        <option value="paid">Paid</option>
                        <option value="due">Due</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3"><label class="form-label">Office Amount (&#8377;)</label><input type="number" name="office_amount" class="form-control" step="0.01" readonly></div>
                <div class="col-md-3 mb-3"><label class="form-label">Commission (&#8377;)</label><input type="number" name="commission" class="form-control" step="0.01" readonly></div>
                <div class="col-md-3 mb-3"><label class="form-label">Extra Amount (&#8377;)</label><input type="number" name="extra_amount" class="form-control" step="0.01" placeholder="0.00" value="0"></div>
                <div class="col-md-3 mb-3"><label class="form-label">Gross Amount (&#8377;)</label><input type="number" name="gross_amount" class="form-control" step="0.01" readonly></div>
                <div class="col-md-3 mb-3"><label class="form-label">Received In</label>
                    <select name="received_account_id" class="form-select">
                        <option value="">Select Account</option>
                        <?php foreach ($myAccounts as $a): ?>
                            <option value="<?php echo $a['id']; ?>"><?php echo htmlspecialchars($a['account_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 mb-3"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2" placeholder="Optional notes..."></textarea></div>
            </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" name="add_file" class="btn btn-primary"><i class="fas fa-save me-1"></i>Create File</button></div>
    </form></div></div>
</div>

<script>
function initFileForm(formEl) {
    if (!formEl) return;

    const fileType = formEl.querySelector('[name="file_type"]');
    const location = formEl.querySelector('[name="location"]');
    const fees = formEl.querySelector('[name="fees"]');
    const reportStatus = formEl.querySelector('[name="report_status"]');
    const paymentMode = formEl.querySelector('[name="payment_mode_id"]');
    const paymentStatus = formEl.querySelector('[name="payment_status"]');
    const amount = formEl.querySelector('[name="amount"]');
    const paidToOffice = formEl.querySelector('[name="paid_to_office"]');
    const officeAmount = formEl.querySelector('[name="office_amount"]');
    const commission = formEl.querySelector('[name="commission"]');
    const extraAmount = formEl.querySelector('[name="extra_amount"]');
    const grossAmount = formEl.querySelector('[name="gross_amount"]');
    const bankSelect = formEl.querySelector('[name="bank_id"]');
    const branchSelect = formEl.querySelector('[name="branch_id"]');

    function toggleFields() {
        const isOffice = fileType.value === 'office';
        const isSelf = fileType.value === 'self';

        location.disabled = !isOffice;
        if (!isOffice) location.value = '';

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
        } else if (isSelf) {
            toggleAmount();
        }
        calcCommission();
    }

    function toggleAmount() {
        if (fileType.value === 'office') { amount.disabled = true; amount.value = ''; return; }
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
        let comm = 0, offAmt = 0;
        if (fileType.value === 'office') {
            if (location.value === 'kolkata') comm = 300;
            else if (location.value === 'out_of_kolkata') comm = 350;
        } else {
            const f = parseFloat(fees.value) || 0;
            comm = Math.round(f * 0.30 * 100) / 100;
            offAmt = Math.round(f * 0.70 * 100) / 100;
        }
        commission.value = comm ? comm.toFixed(2) : '';
        officeAmount.value = (fileType.value === 'self' && offAmt) ? offAmt.toFixed(2) : '';
        const extra = parseFloat(extraAmount.value) || 0;
        grossAmount.value = (comm + extra) ? (comm + extra).toFixed(2) : '';
    }

    function loadBranches(bankId, selectedBranchId) {
        branchSelect.innerHTML = '<option value="">Loading...</option>';
        if (!bankId) { branchSelect.innerHTML = '<option value="">Select Bank first</option>'; return; }
        fetch('files.php?ajax=branches&bank_id=' + bankId)
            .then(r => r.json())
            .then(branches => {
                branchSelect.innerHTML = '<option value="">Select Branch</option>';
                branches.forEach(b => {
                    const opt = document.createElement('option');
                    opt.value = b.id;
                    opt.textContent = b.branch_name;
                    if (selectedBranchId && b.id == selectedBranchId) opt.selected = true;
                    branchSelect.appendChild(opt);
                });
            })
            .catch(() => { branchSelect.innerHTML = '<option value="">Error loading</option>'; });
    }

    fileType.addEventListener('change', toggleFields);
    location.addEventListener('change', calcCommission);
    fees.addEventListener('input', () => { calcCommission(); toggleAmount(); });
    paymentStatus.addEventListener('change', toggleAmount);
    extraAmount.addEventListener('input', calcCommission);
    bankSelect.addEventListener('change', () => loadBranches(bankSelect.value));

    // Initialize on load
    if (fileType.value) toggleFields();

    // Load branches for edit forms
    if (bankSelect.value && branchSelect.dataset.selected) {
        loadBranches(bankSelect.value, branchSelect.dataset.selected);
    }
}

// Init add form
document.addEventListener('DOMContentLoaded', () => {
    const addForm = document.getElementById('addForm');
    if (addForm) initFileForm(addForm);
});

// Init edit forms when modals open
document.querySelectorAll('[id^="editFileModal"]').forEach(modal => {
    modal.addEventListener('shown.bs.modal', function() {
        const form = this.querySelector('form');
        if (form && !form.dataset.initialized) {
            initFileForm(form);
            form.dataset.initialized = 'true';
        }
    });
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
