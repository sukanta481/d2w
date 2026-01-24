<?php
require_once 'includes/auth.php';
$auth->requireLogin();

require_once 'config/database.php';
$database = new Database();
$db = $database->connect();

// Get settings
$settings = [];
try {
    $stmt = $db->query("SELECT setting_key, setting_value FROM settings");
    while ($row = $stmt->fetch()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
} catch(PDOException $e) {}

$billPrefix = $settings['bill_prefix'] ?? 'INV';
$defaultTerms = $settings['bill_terms'] ?? 'Payment is due within 15 days of the invoice date.';

// Generate next bill number
function generateBillNumber($db, $prefix) {
    $year = date('Y');
    $stmt = $db->prepare("SELECT bill_number FROM bills WHERE bill_number LIKE :pattern ORDER BY id DESC LIMIT 1");
    $stmt->execute([':pattern' => $prefix . '-' . $year . '-%']);
    $lastBill = $stmt->fetch();
    
    if ($lastBill) {
        $lastNum = intval(substr($lastBill['bill_number'], -4));
        $nextNum = $lastNum + 1;
    } else {
        $nextNum = 1;
    }
    
    return $prefix . '-' . $year . '-' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['add_bill'])) {
            $billNumber = generateBillNumber($db, $billPrefix);
            
            // Calculate totals
            $subtotal = 0;
            $items = [];
            if (isset($_POST['item_description']) && is_array($_POST['item_description'])) {
                foreach ($_POST['item_description'] as $i => $desc) {
                    if (!empty($desc)) {
                        $qty = floatval($_POST['item_quantity'][$i] ?? 1);
                        $price = floatval($_POST['item_price'][$i] ?? 0);
                        $total = $qty * $price;
                        $subtotal += $total;
                        $items[] = [
                            'description' => $desc,
                            'quantity' => $qty,
                            'unit_price' => $price,
                            'total_price' => $total
                        ];
                    }
                }
            }
            
            // Check if GST is enabled
            $gstEnabled = isset($_POST['gst_enabled']) && $_POST['gst_enabled'];
            $taxPercent = $gstEnabled ? floatval($_POST['tax_percent'] ?? 18) : 0;
            $taxAmount = $subtotal * ($taxPercent / 100);
            $discountAmount = floatval($_POST['discount_amount'] ?? 0);
            $totalAmount = $subtotal + $taxAmount - $discountAmount;
            
            // Insert bill
            $stmt = $db->prepare("INSERT INTO bills (bill_number, client_id, bill_date, due_date, subtotal, 
                tax_percent, tax_amount, discount_amount, total_amount, notes, terms, status, 
                bank_payment_method_id, upi_payment_method_id, created_by)
                VALUES (:bill_number, :client_id, :bill_date, :due_date, :subtotal, :tax_percent, :tax_amount, 
                :discount_amount, :total_amount, :notes, :terms, :status, :bank_payment_method_id, :upi_payment_method_id, :created_by)");
            $stmt->execute([
                ':bill_number' => $billNumber,
                ':client_id' => $_POST['client_id'],
                ':bill_date' => $_POST['bill_date'],
                ':due_date' => $_POST['due_date'] ?: null,
                ':subtotal' => $subtotal,
                ':tax_percent' => $taxPercent,
                ':tax_amount' => $taxAmount,
                ':discount_amount' => $discountAmount,
                ':total_amount' => $totalAmount,
                ':notes' => $_POST['notes'] ?? null,
                ':terms' => $_POST['terms'] ?? $defaultTerms,
                ':status' => $_POST['status'] ?? 'draft',
                ':bank_payment_method_id' => !empty($_POST['bank_payment_method_id']) ? $_POST['bank_payment_method_id'] : null,
                ':upi_payment_method_id' => !empty($_POST['upi_payment_method_id']) ? $_POST['upi_payment_method_id'] : null,
                ':created_by' => $auth->getUserId()
            ]);
            
            $billId = $db->lastInsertId();
            
            // Insert bill items
            $itemStmt = $db->prepare("INSERT INTO bill_items (bill_id, description, quantity, unit_price, total_price, display_order)
                VALUES (:bill_id, :description, :quantity, :unit_price, :total_price, :display_order)");
            foreach ($items as $index => $item) {
                $itemStmt->execute([
                    ':bill_id' => $billId,
                    ':description' => $item['description'],
                    ':quantity' => $item['quantity'],
                    ':unit_price' => $item['unit_price'],
                    ':total_price' => $item['total_price'],
                    ':display_order' => $index
                ]);
            }
            
            $auth->logActivity($auth->getUserId(), 'create', 'bills', $billId, 'Created bill: ' . $billNumber);
            $successMessage = "Bill #" . $billNumber . " created successfully!";
        }
        
        if (isset($_POST['update_bill'])) {
            // Calculate totals
            $subtotal = 0;
            $items = [];
            if (isset($_POST['item_description']) && is_array($_POST['item_description'])) {
                foreach ($_POST['item_description'] as $i => $desc) {
                    if (!empty($desc)) {
                        $qty = floatval($_POST['item_quantity'][$i] ?? 1);
                        $price = floatval($_POST['item_price'][$i] ?? 0);
                        $total = $qty * $price;
                        $subtotal += $total;
                        $items[] = [
                            'description' => $desc,
                            'quantity' => $qty,
                            'unit_price' => $price,
                            'total_price' => $total
                        ];
                    }
                }
            }
            
            // Check if GST is enabled
            $gstEnabled = isset($_POST['gst_enabled']) && $_POST['gst_enabled'];
            $taxPercent = $gstEnabled ? floatval($_POST['tax_percent'] ?? 18) : 0;
            $taxAmount = $subtotal * ($taxPercent / 100);
            $discountAmount = floatval($_POST['discount_amount'] ?? 0);
            $totalAmount = $subtotal + $taxAmount - $discountAmount;
            
            // Update bill
            $stmt = $db->prepare("UPDATE bills SET client_id = :client_id, bill_date = :bill_date, 
                due_date = :due_date, subtotal = :subtotal, tax_percent = :tax_percent, tax_amount = :tax_amount, 
                discount_amount = :discount_amount, total_amount = :total_amount, notes = :notes, 
                terms = :terms, status = :status, payment_status = :payment_status,
                paid_amount = :paid_amount, payment_date = :payment_date,
                bank_payment_method_id = :bank_payment_method_id, upi_payment_method_id = :upi_payment_method_id
                WHERE id = :id");
            $stmt->execute([
                ':id' => $_POST['bill_id'],
                ':client_id' => $_POST['client_id'],
                ':bill_date' => $_POST['bill_date'],
                ':due_date' => $_POST['due_date'] ?: null,
                ':subtotal' => $subtotal,
                ':tax_percent' => $taxPercent,
                ':tax_amount' => $taxAmount,
                ':discount_amount' => $discountAmount,
                ':total_amount' => $totalAmount,
                ':notes' => $_POST['notes'] ?? null,
                ':terms' => $_POST['terms'] ?? $defaultTerms,
                ':status' => $_POST['status'] ?? 'draft',
                ':payment_status' => $_POST['payment_status'] ?? 'unpaid',
                ':paid_amount' => floatval($_POST['paid_amount'] ?? 0),
                ':payment_date' => !empty($_POST['payment_date']) ? $_POST['payment_date'] : null,
                ':bank_payment_method_id' => !empty($_POST['bank_payment_method_id']) ? $_POST['bank_payment_method_id'] : null,
                ':upi_payment_method_id' => !empty($_POST['upi_payment_method_id']) ? $_POST['upi_payment_method_id'] : null
            ]);
            
            // Delete old items and insert new ones
            $db->prepare("DELETE FROM bill_items WHERE bill_id = :id")->execute([':id' => $_POST['bill_id']]);
            
            $itemStmt = $db->prepare("INSERT INTO bill_items (bill_id, description, quantity, unit_price, total_price, display_order)
                VALUES (:bill_id, :description, :quantity, :unit_price, :total_price, :display_order)");
            foreach ($items as $index => $item) {
                $itemStmt->execute([
                    ':bill_id' => $_POST['bill_id'],
                    ':description' => $item['description'],
                    ':quantity' => $item['quantity'],
                    ':unit_price' => $item['unit_price'],
                    ':total_price' => $item['total_price'],
                    ':display_order' => $index
                ]);
            }
            
            $auth->logActivity($auth->getUserId(), 'update', 'bills', $_POST['bill_id'], 'Updated bill');
            $successMessage = "Bill updated successfully!";
        }
        
        if (isset($_POST['delete_bill'])) {
            $stmt = $db->prepare("DELETE FROM bills WHERE id = :id");
            $stmt->execute([':id' => $_POST['bill_id']]);
            
            $auth->logActivity($auth->getUserId(), 'delete', 'bills', $_POST['bill_id'], 'Deleted bill');
            $successMessage = "Bill deleted successfully!";
        }
        
        if (isset($_POST['mark_paid'])) {
            $billId = $_POST['bill_id'];
            $paymentMethod = $_POST['payment_method'] ?? 'cash';
            $paymentBankId = !empty($_POST['payment_bank_id']) ? $_POST['payment_bank_id'] : null;
            $paymentUpiId = !empty($_POST['payment_upi_id']) ? $_POST['payment_upi_id'] : null;
            $paymentDate = $_POST['payment_date'] ?? date('Y-m-d');
            $paymentReference = $_POST['payment_reference'] ?? null;
            $paymentNotes = $_POST['payment_notes'] ?? null;
            
            $stmt = $db->prepare("UPDATE bills SET 
                status = 'paid', 
                payment_status = 'paid', 
                paid_amount = total_amount,
                payment_date = :payment_date,
                payment_method = :payment_method,
                payment_bank_id = :payment_bank_id,
                payment_upi_id = :payment_upi_id,
                payment_reference = :payment_reference,
                payment_notes = :payment_notes
                WHERE id = :id");
            $stmt->execute([
                ':id' => $billId,
                ':payment_date' => $paymentDate,
                ':payment_method' => $paymentMethod,
                ':payment_bank_id' => $paymentBankId,
                ':payment_upi_id' => $paymentUpiId,
                ':payment_reference' => $paymentReference,
                ':payment_notes' => $paymentNotes
            ]);
            
            $auth->logActivity($auth->getUserId(), 'update', 'bills', $billId, 'Marked bill as paid via ' . $paymentMethod);
            $successMessage = "Bill marked as paid!";
        }
        
    } catch(PDOException $e) {
        $errorMessage = "Error: " . $e->getMessage();
    }
}

// Get filters
$clientFilter = $_GET['client_id'] ?? '';
$statusFilter = $_GET['status'] ?? '';
$paymentFilter = $_GET['payment_status'] ?? '';
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';
$searchQuery = $_GET['search'] ?? '';
$amountMin = $_GET['amount_min'] ?? '';
$amountMax = $_GET['amount_max'] ?? '';

// Get bills with filters
try {
    $query = "SELECT b.*, c.name as client_name, c.email as client_email, c.phone as client_phone
              FROM bills b 
              LEFT JOIN clients c ON b.client_id = c.id
              WHERE 1=1";
    $params = [];
    
    if ($clientFilter) {
        $query .= " AND b.client_id = :client_id";
        $params[':client_id'] = $clientFilter;
    }
    
    if ($statusFilter) {
        $query .= " AND b.status = :status";
        $params[':status'] = $statusFilter;
    }
    
    if ($paymentFilter) {
        $query .= " AND b.payment_status = :payment_status";
        $params[':payment_status'] = $paymentFilter;
    }
    
    if ($dateFrom) {
        $query .= " AND b.bill_date >= :date_from";
        $params[':date_from'] = $dateFrom;
    }
    
    if ($dateTo) {
        $query .= " AND b.bill_date <= :date_to";
        $params[':date_to'] = $dateTo;
    }
    
    if ($searchQuery) {
        $query .= " AND (b.bill_number LIKE :search OR c.name LIKE :search OR c.email LIKE :search)";
        $params[':search'] = '%' . $searchQuery . '%';
    }
    
    if ($amountMin !== '') {
        $query .= " AND b.total_amount >= :amount_min";
        $params[':amount_min'] = floatval($amountMin);
    }
    
    if ($amountMax !== '') {
        $query .= " AND b.total_amount <= :amount_max";
        $params[':amount_max'] = floatval($amountMax);
    }
    
    $query .= " ORDER BY b.created_at DESC";
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $bills = $stmt->fetchAll();
    
    // Get summary stats
    $totalBills = count($bills);
    $totalAmount = array_sum(array_column($bills, 'total_amount'));
    $paidAmount = array_sum(array_column($bills, 'paid_amount'));
    $unpaidAmount = $totalAmount - $paidAmount;
    
} catch(PDOException $e) {
    $bills = [];
    $totalBills = 0;
    $totalAmount = 0;
    $paidAmount = 0;
    $unpaidAmount = 0;
    error_log("Bills Error: " . $e->getMessage());
}

// Get clients for dropdown
try {
    $clientsStmt = $db->query("SELECT id, name, email, company FROM clients WHERE status = 'active' ORDER BY name");
    $clientsList = $clientsStmt->fetchAll();
} catch(PDOException $e) {
    $clientsList = [];
}

// Get payment methods for dropdown
try {
    $bankMethodsStmt = $db->query("SELECT * FROM payment_methods WHERE type = 'bank' AND is_active = 1 ORDER BY is_default DESC, name");
    $bankMethods = $bankMethodsStmt->fetchAll();
    
    $upiMethodsStmt = $db->query("SELECT * FROM payment_methods WHERE type = 'upi' AND is_active = 1 ORDER BY is_default DESC, name");
    $upiMethods = $upiMethodsStmt->fetchAll();
} catch(PDOException $e) {
    $bankMethods = [];
    $upiMethods = [];
}

// Check if we should show add bill modal
$showAddModal = isset($_GET['action']) && $_GET['action'] === 'new';
$preselectedClient = $_GET['client_id'] ?? '';

$pageTitle = 'Billing Management';
include 'includes/header.php';
?>

<style>
.billing-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}
.billing-stat-card {
    background: linear-gradient(135deg, var(--card-bg), var(--card-bg-hover));
    border-radius: 12px;
    padding: 1.25rem;
    border: 1px solid var(--border-color);
}
.billing-stat-card.total { border-left: 4px solid var(--primary-color); }
.billing-stat-card.paid { border-left: 4px solid #28a745; }
.billing-stat-card.unpaid { border-left: 4px solid #dc3545; }
.billing-stat-card.count { border-left: 4px solid #17a2b8; }
.stat-label { font-size: 0.85rem; color: #6c757d; margin-bottom: 0.25rem; }
.stat-value { font-size: 1.5rem; font-weight: 700; color: var(--text-color); }

.filter-section {
    background: var(--card-bg);
    border-radius: 12px;
    padding: 1.25rem;
    margin-bottom: 1.5rem;
    border: 1px solid var(--border-color);
}
.filter-row { display: flex; flex-wrap: wrap; gap: 1rem; align-items: flex-end; }
.filter-group { flex: 1; min-width: 150px; }
.filter-group label { font-size: 0.85rem; color: #6c757d; margin-bottom: 0.25rem; display: block; }
.filter-actions { display: flex; gap: 0.5rem; flex-wrap: wrap; }

.bill-items-container { margin-top: 1rem; }
.bill-item-row { 
    display: grid; 
    grid-template-columns: 2fr 1fr 1fr 1fr auto; 
    gap: 0.75rem; 
    margin-bottom: 0.75rem;
    align-items: center;
}
.bill-item-row input { font-size: 0.9rem; }
.item-total { font-weight: 600; text-align: right; min-width: 100px; }
.add-item-btn { 
    background: transparent; 
    border: 2px dashed var(--border-color); 
    padding: 0.75rem; 
    border-radius: 8px;
    color: var(--primary-color);
    cursor: pointer;
    transition: all 0.3s;
    width: 100%;
}
.add-item-btn:hover { border-color: var(--primary-color); background: rgba(247, 183, 49, 0.1); }

.totals-section {
    background: var(--bg-color);
    padding: 1rem;
    border-radius: 8px;
    margin-top: 1rem;
}
.total-row { display: flex; justify-content: space-between; padding: 0.5rem 0; }
.total-row.grand { font-size: 1.25rem; font-weight: 700; border-top: 2px solid var(--border-color); padding-top: 0.75rem; margin-top: 0.5rem; }

.status-badge { padding: 0.35rem 0.75rem; border-radius: 20px; font-size: 0.8rem; font-weight: 500; }
.status-draft { background: #6c757d; color: #fff; }
.status-sent { background: #17a2b8; color: #fff; }
.status-paid { background: #28a745; color: #fff; }
.status-overdue { background: #dc3545; color: #fff; }
.status-cancelled { background: #343a40; color: #fff; }

.action-btns { display: flex; gap: 0.25rem; flex-wrap: wrap; }
.action-btns .btn { padding: 0.35rem 0.5rem; }

.whatsapp-btn { background: #25D366; color: #fff; }
.whatsapp-btn:hover { background: #128C7E; color: #fff; }

@media (max-width: 768px) {
    .bill-item-row { grid-template-columns: 1fr; }
    .filter-group { min-width: 100%; }
}
</style>

<div class="admin-content">
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h1 class="page-title">Billing Management</h1>
            <p class="page-subtitle">Create and manage invoices for your clients</p>
        </div>
        <div class="d-flex gap-2">
            <a href="clients.php" class="btn btn-outline-primary">
                <i class="fas fa-users me-2"></i>Clients
            </a>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBillModal">
                <i class="fas fa-plus me-2"></i>New Bill
            </button>
        </div>
    </div>

    <?php if (isset($successMessage)): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i><?php echo $successMessage; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($errorMessage)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-2"></i><?php echo $errorMessage; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Stats Cards -->
    <div class="billing-stats">
        <div class="billing-stat-card count">
            <div class="stat-label">Total Bills</div>
            <div class="stat-value"><?php echo $totalBills; ?></div>
        </div>
        <div class="billing-stat-card total">
            <div class="stat-label">Total Amount</div>
            <div class="stat-value">₹<?php echo number_format($totalAmount, 2); ?></div>
        </div>
        <div class="billing-stat-card paid">
            <div class="stat-label">Paid Amount</div>
            <div class="stat-value">₹<?php echo number_format($paidAmount, 2); ?></div>
        </div>
        <div class="billing-stat-card unpaid">
            <div class="stat-label">Unpaid Amount</div>
            <div class="stat-value">₹<?php echo number_format($unpaidAmount, 2); ?></div>
        </div>
    </div>

    <!-- Advanced Filters -->
    <div class="filter-section">
        <form method="GET" id="filterForm">
            <div class="filter-row">
                <div class="filter-group" style="flex: 2;">
                    <label>Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Bill number, client name..."
                           value="<?php echo htmlspecialchars($searchQuery); ?>">
                </div>
                <div class="filter-group">
                    <label>Client</label>
                    <select name="client_id" class="form-select">
                        <option value="">All Clients</option>
                        <?php foreach ($clientsList as $client): ?>
                            <option value="<?php echo $client['id']; ?>" <?php echo $clientFilter == $client['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($client['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="draft" <?php echo $statusFilter === 'draft' ? 'selected' : ''; ?>>Draft</option>
                        <option value="sent" <?php echo $statusFilter === 'sent' ? 'selected' : ''; ?>>Sent</option>
                        <option value="paid" <?php echo $statusFilter === 'paid' ? 'selected' : ''; ?>>Paid</option>
                        <option value="overdue" <?php echo $statusFilter === 'overdue' ? 'selected' : ''; ?>>Overdue</option>
                        <option value="cancelled" <?php echo $statusFilter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Payment</label>
                    <select name="payment_status" class="form-select">
                        <option value="">All Payment</option>
                        <option value="unpaid" <?php echo $paymentFilter === 'unpaid' ? 'selected' : ''; ?>>Unpaid</option>
                        <option value="partial" <?php echo $paymentFilter === 'partial' ? 'selected' : ''; ?>>Partial</option>
                        <option value="paid" <?php echo $paymentFilter === 'paid' ? 'selected' : ''; ?>>Paid</option>
                    </select>
                </div>
            </div>
            <div class="filter-row mt-3">
                <div class="filter-group">
                    <label>Date From</label>
                    <input type="date" name="date_from" class="form-control" value="<?php echo $dateFrom; ?>">
                </div>
                <div class="filter-group">
                    <label>Date To</label>
                    <input type="date" name="date_to" class="form-control" value="<?php echo $dateTo; ?>">
                </div>
                <div class="filter-group">
                    <label>Min Amount (₹)</label>
                    <input type="number" name="amount_min" class="form-control" placeholder="0" 
                           value="<?php echo $amountMin; ?>" step="0.01">
                </div>
                <div class="filter-group">
                    <label>Max Amount (₹)</label>
                    <input type="number" name="amount_max" class="form-control" placeholder="Any"
                           value="<?php echo $amountMax; ?>" step="0.01">
                </div>
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter me-1"></i>Filter
                    </button>
                    <a href="billing.php" class="btn btn-secondary">
                        <i class="fas fa-redo me-1"></i>Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Bills Table -->
    <div class="content-card">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Bill #</th>
                        <th>Client</th>
                        <th>Date</th>
                        <th>Due Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($bills)): ?>
                        <?php foreach ($bills as $bill): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($bill['bill_number']); ?></strong>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($bill['client_name']); ?></strong>
                                    <br><small class="text-muted"><?php echo htmlspecialchars($bill['client_email']); ?></small>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($bill['bill_date'])); ?></td>
                                <td>
                                    <?php if ($bill['due_date']): ?>
                                        <?php 
                                        $dueDate = strtotime($bill['due_date']);
                                        $isOverdue = $dueDate < time() && $bill['payment_status'] !== 'paid';
                                        ?>
                                        <span class="<?php echo $isOverdue ? 'text-danger fw-bold' : ''; ?>">
                                            <?php echo date('M d, Y', $dueDate); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong>₹<?php echo number_format($bill['total_amount'], 2); ?></strong>
                                    <?php if ($bill['paid_amount'] > 0 && $bill['paid_amount'] < $bill['total_amount']): ?>
                                        <br><small class="text-success">Paid: ₹<?php echo number_format($bill['paid_amount'], 2); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo $bill['status']; ?>">
                                        <?php echo ucfirst($bill['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $bill['payment_status'] === 'paid' ? 'success' : 
                                            ($bill['payment_status'] === 'partial' ? 'warning' : 'danger'); 
                                    ?>">
                                        <?php echo ucfirst($bill['payment_status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-btns">
                                        <a href="generate-bill-pdf.php?id=<?php echo $bill['id']; ?>&view=1" 
                                           class="btn btn-sm btn-outline-primary" title="View PDF" target="_blank">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="generate-bill-pdf.php?id=<?php echo $bill['id']; ?>" 
                                           class="btn btn-sm btn-outline-secondary" title="Download PDF">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <button class="btn btn-sm btn-outline-info" 
                                                onclick="sendEmail(<?php echo $bill['id']; ?>)" title="Send Email">
                                            <i class="fas fa-envelope"></i>
                                        </button>
                                        <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $bill['client_phone'] ?? ''); ?>?text=<?php echo urlencode("Hello " . $bill['client_name'] . ",\n\nYour invoice #" . $bill['bill_number'] . " for ₹" . number_format($bill['total_amount'], 2) . " is ready.\n\nDownload: " . (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/generate-bill-pdf.php?id=' . $bill['id']); ?>" 
                                           class="btn btn-sm whatsapp-btn" title="Send via WhatsApp" target="_blank">
                                            <i class="fab fa-whatsapp"></i>
                                        </a>
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                                data-bs-target="#editBillModal<?php echo $bill['id']; ?>" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <?php if ($bill['payment_status'] !== 'paid'): ?>
                                            <button class="btn btn-sm btn-success" data-bs-toggle="modal"
                                                    data-bs-target="#markPaidModal<?php echo $bill['id']; ?>" title="Mark as Paid">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        <?php endif; ?>
                                        <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal"
                                                data-bs-target="#deleteBillModal<?php echo $bill['id']; ?>" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Edit Bill Modal -->
                            <?php 
                            $itemsStmt = $db->prepare("SELECT * FROM bill_items WHERE bill_id = :id ORDER BY display_order");
                            $itemsStmt->execute([':id' => $bill['id']]);
                            $billItems = $itemsStmt->fetchAll();
                            ?>
                            <div class="modal fade" id="editBillModal<?php echo $bill['id']; ?>" tabindex="-1">
                                <div class="modal-dialog modal-xl">
                                    <div class="modal-content">
                                        <form method="POST">
                                            <input type="hidden" name="update_bill" value="1">
                                            <input type="hidden" name="bill_id" value="<?php echo $bill['id']; ?>">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit Bill #<?php echo htmlspecialchars($bill['bill_number']); ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row">
                                                    <div class="col-md-4 mb-3">
                                                        <label class="form-label">Client *</label>
                                                        <select class="form-select" name="client_id" required>
                                                            <option value="">Select Client</option>
                                                            <?php foreach ($clientsList as $client): ?>
                                                                <option value="<?php echo $client['id']; ?>" 
                                                                    <?php echo $bill['client_id'] == $client['id'] ? 'selected' : ''; ?>>
                                                                    <?php echo htmlspecialchars($client['name']); ?>
                                                                    <?php if ($client['company']): ?>(<?php echo htmlspecialchars($client['company']); ?>)<?php endif; ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-2 mb-3">
                                                        <label class="form-label">Bill Date *</label>
                                                        <input type="date" class="form-control" name="bill_date" required
                                                               value="<?php echo $bill['bill_date']; ?>">
                                                    </div>
                                                    <div class="col-md-2 mb-3">
                                                        <label class="form-label">Due Date</label>
                                                        <input type="date" class="form-control" name="due_date"
                                                               value="<?php echo $bill['due_date']; ?>">
                                                    </div>
                                                    <div class="col-md-2 mb-3">
                                                        <label class="form-label">Status</label>
                                                        <select class="form-select" name="status">
                                                            <option value="draft" <?php echo $bill['status'] === 'draft' ? 'selected' : ''; ?>>Draft</option>
                                                            <option value="sent" <?php echo $bill['status'] === 'sent' ? 'selected' : ''; ?>>Sent</option>
                                                            <option value="paid" <?php echo $bill['status'] === 'paid' ? 'selected' : ''; ?>>Paid</option>
                                                            <option value="overdue" <?php echo $bill['status'] === 'overdue' ? 'selected' : ''; ?>>Overdue</option>
                                                            <option value="cancelled" <?php echo $bill['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-2 mb-3">
                                                        <label class="form-label">Payment Status</label>
                                                        <select class="form-select" name="payment_status">
                                                            <option value="unpaid" <?php echo $bill['payment_status'] === 'unpaid' ? 'selected' : ''; ?>>Unpaid</option>
                                                            <option value="partial" <?php echo $bill['payment_status'] === 'partial' ? 'selected' : ''; ?>>Partial</option>
                                                            <option value="paid" <?php echo $bill['payment_status'] === 'paid' ? 'selected' : ''; ?>>Paid</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <!-- Bill Items -->
                                                <h6 class="mt-3 mb-3">Bill Items</h6>
                                                <div class="bill-items-container" id="editItems<?php echo $bill['id']; ?>">
                                                    <div class="bill-item-row fw-bold mb-2 d-none d-md-grid">
                                                        <span>Description</span>
                                                        <span>Quantity</span>
                                                        <span>Unit Price (₹)</span>
                                                        <span>Total</span>
                                                        <span></span>
                                                    </div>
                                                    <?php foreach ($billItems as $item): ?>
                                                    <div class="bill-item-row">
                                                        <input type="text" class="form-control" name="item_description[]" 
                                                               placeholder="Description" value="<?php echo htmlspecialchars($item['description']); ?>" required>
                                                        <input type="number" class="form-control item-qty" name="item_quantity[]" 
                                                               placeholder="Qty" min="0.01" step="0.01" value="<?php echo $item['quantity']; ?>" required>
                                                        <input type="number" class="form-control item-price" name="item_price[]" 
                                                               placeholder="Price" min="0" step="0.01" value="<?php echo $item['unit_price']; ?>" required>
                                                        <span class="item-total">₹<?php echo number_format($item['total_price'], 2); ?></span>
                                                        <button type="button" class="btn btn-sm btn-outline-danger remove-item">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </div>
                                                    <?php endforeach; ?>
                                                </div>
                                                <button type="button" class="add-item-btn" onclick="addItemRow('editItems<?php echo $bill['id']; ?>')">
                                                    <i class="fas fa-plus me-2"></i>Add Item
                                                </button>

                                                <!-- Totals -->
                                                <div class="row mt-4">
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <label class="form-label">Notes</label>
                                                            <textarea class="form-control" name="notes" rows="2"><?php echo htmlspecialchars($bill['notes'] ?? ''); ?></textarea>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label">Paid Amount (₹)</label>
                                                                <input type="number" class="form-control" name="paid_amount" 
                                                                       step="0.01" min="0" value="<?php echo $bill['paid_amount']; ?>">
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label">Payment Date</label>
                                                                <input type="date" class="form-control" name="payment_date"
                                                                       value="<?php echo $bill['payment_date']; ?>">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="totals-section">
                                                            <div class="total-row">
                                                                <span>Subtotal:</span>
                                                                <span class="subtotal-display">₹<?php echo number_format($bill['subtotal'], 2); ?></span>
                                                            </div>
                                                            <div class="total-row">
                                                                <span>
                                                                    Tax (<input type="number" name="tax_percent" class="form-control form-control-sm d-inline-block" 
                                                                           style="width: 60px;" value="<?php echo $bill['tax_percent']; ?>" min="0" max="100" step="0.01">%):
                                                                </span>
                                                                <span class="tax-display">₹<?php echo number_format($bill['tax_amount'], 2); ?></span>
                                                            </div>
                                                            <div class="total-row">
                                                                <span>
                                                                    Discount: ₹<input type="number" name="discount_amount" class="form-control form-control-sm d-inline-block" 
                                                                           style="width: 100px;" value="<?php echo $bill['discount_amount']; ?>" min="0" step="0.01">
                                                                </span>
                                                                <span></span>
                                                            </div>
                                                            <div class="total-row grand">
                                                                <span>Total:</span>
                                                                <span class="grand-total-display">₹<?php echo number_format($bill['total_amount'], 2); ?></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Terms & Conditions</label>
                                                    <textarea class="form-control" name="terms" rows="2"><?php echo htmlspecialchars($bill['terms'] ?? $defaultTerms); ?></textarea>
                                                </div>

                                                <!-- Payment Methods Selection -->
                                                <div class="row mb-3">
                                                    <div class="col-md-12">
                                                        <h6 class="mb-3"><i class="fas fa-credit-card me-2"></i>Payment Methods on Invoice</h6>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">Bank Account</label>
                                                        <select class="form-select" name="bank_payment_method_id">
                                                            <option value="">-- No Bank Account --</option>
                                                            <?php foreach ($bankMethods as $bank): ?>
                                                                <option value="<?php echo $bank['id']; ?>" 
                                                                    <?php echo $bill['bank_payment_method_id'] == $bank['id'] ? 'selected' : ''; ?>>
                                                                    <?php echo htmlspecialchars($bank['name']); ?> - <?php echo htmlspecialchars($bank['bank_name']); ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">UPI Payment</label>
                                                        <select class="form-select" name="upi_payment_method_id">
                                                            <option value="">-- No UPI --</option>
                                                            <?php foreach ($upiMethods as $upi): ?>
                                                                <option value="<?php echo $upi['id']; ?>" 
                                                                    <?php echo $bill['upi_payment_method_id'] == $upi['id'] ? 'selected' : ''; ?>>
                                                                    <?php echo htmlspecialchars($upi['name']); ?> - <?php echo htmlspecialchars($upi['upi_id']); ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary">Update Bill</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Delete Bill Modal -->
                            <div class="modal fade" id="deleteBillModal<?php echo $bill['id']; ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST">
                                            <input type="hidden" name="delete_bill" value="1">
                                            <input type="hidden" name="bill_id" value="<?php echo $bill['id']; ?>">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Delete Bill</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Are you sure you want to delete bill <strong>#<?php echo htmlspecialchars($bill['bill_number']); ?></strong>?</p>
                                                <p class="text-muted mb-0">This action cannot be undone.</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-danger">Delete</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Mark as Paid Modal -->
                            <?php if ($bill['payment_status'] !== 'paid'): ?>
                            <div class="modal fade" id="markPaidModal<?php echo $bill['id']; ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST">
                                            <input type="hidden" name="mark_paid" value="1">
                                            <input type="hidden" name="bill_id" value="<?php echo $bill['id']; ?>">
                                            <div class="modal-header bg-success text-white">
                                                <h5 class="modal-title"><i class="fas fa-check-circle me-2"></i>Mark as Paid</h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="alert alert-info">
                                                    <strong>Bill:</strong> #<?php echo htmlspecialchars($bill['bill_number']); ?><br>
                                                    <strong>Amount:</strong> ₹<?php echo number_format($bill['total_amount'], 2); ?>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Payment Method *</label>
                                                    <select class="form-select" name="payment_method" id="paymentMethod<?php echo $bill['id']; ?>" 
                                                            onchange="togglePaymentFields(<?php echo $bill['id']; ?>)" required>
                                                        <option value="cash">💵 Cash</option>
                                                        <option value="bank">🏦 Bank Transfer</option>
                                                        <option value="upi">📱 UPI</option>
                                                        <option value="cheque">📄 Cheque</option>
                                                        <option value="other">📋 Other</option>
                                                    </select>
                                                </div>

                                                <div class="mb-3" id="bankSelectDiv<?php echo $bill['id']; ?>" style="display: none;">
                                                    <label class="form-label">Select Bank Account</label>
                                                    <select class="form-select" name="payment_bank_id">
                                                        <option value="">-- Select Bank --</option>
                                                        <?php foreach ($bankMethods as $bank): ?>
                                                            <option value="<?php echo $bank['id']; ?>">
                                                                <?php echo htmlspecialchars($bank['name']); ?> - 
                                                                <?php echo htmlspecialchars($bank['bank_name']); ?> 
                                                                (****<?php echo substr($bank['account_number'], -4); ?>)
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>

                                                <div class="mb-3" id="upiSelectDiv<?php echo $bill['id']; ?>" style="display: none;">
                                                    <label class="form-label">Select UPI Account Received To</label>
                                                    <select class="form-select" name="payment_upi_id">
                                                        <option value="">-- Select UPI ID --</option>
                                                        <?php foreach ($upiMethods as $upi): ?>
                                                            <option value="<?php echo $upi['id']; ?>">
                                                                <?php echo htmlspecialchars($upi['name']); ?> - 
                                                                <?php echo htmlspecialchars($upi['upi_id']); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Payment Received Date *</label>
                                                    <input type="date" class="form-control" name="payment_date" 
                                                           value="<?php echo date('Y-m-d'); ?>" required>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Transaction Reference / ID</label>
                                                    <input type="text" class="form-control" name="payment_reference" 
                                                           placeholder="UTR, Cheque No., Transaction ID, etc.">
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Notes (Optional)</label>
                                                    <textarea class="form-control" name="payment_notes" rows="2" 
                                                              placeholder="Any additional payment notes..."></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-success">
                                                    <i class="fas fa-check me-2"></i>Confirm Payment
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-file-invoice fa-3x mb-3 d-block opacity-50"></i>
                                No bills found. <a href="#" data-bs-toggle="modal" data-bs-target="#addBillModal">Create your first bill</a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Bill Modal -->
<div class="modal fade" id="addBillModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form method="POST" id="addBillForm">
                <input type="hidden" name="add_bill" value="1">
                <div class="modal-header">
                    <h5 class="modal-title">Create New Bill</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Client *</label>
                            <select class="form-select" name="client_id" required>
                                <option value="">Select Client</option>
                                <?php foreach ($clientsList as $client): ?>
                                    <option value="<?php echo $client['id']; ?>" 
                                        <?php echo $preselectedClient == $client['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($client['name']); ?>
                                        <?php if ($client['company']): ?>(<?php echo htmlspecialchars($client['company']); ?>)<?php endif; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">
                                <a href="clients.php" target="_blank">+ Add new client</a>
                            </small>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label">Bill Date *</label>
                            <input type="date" class="form-control" name="bill_date" required
                                   value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label">Due Date</label>
                            <input type="date" class="form-control" name="due_date"
                                   value="<?php echo date('Y-m-d', strtotime('+15 days')); ?>">
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <option value="draft">Draft</option>
                                <option value="sent">Sent</option>
                            </select>
                        </div>
                    </div>

                    <!-- Bill Items -->
                    <h6 class="mt-3 mb-2">Bill Items</h6>
                    <p class="text-muted small mb-3">
                        <i class="fas fa-lightbulb text-warning me-1"></i>
                        <strong>Tip:</strong> Break down your services into separate line items for clarity. 
                        E.g., instead of "Website + CRM", add "Website Frontend Design", "CRM Setup", "1-Year Hosting" as separate items.
                    </p>
                    <div class="bill-items-container" id="newBillItems">
                        <div class="bill-item-row fw-bold mb-2 d-none d-md-grid">
                            <span>Description</span>
                            <span>Quantity</span>
                            <span>Unit Price (₹)</span>
                            <span>Total</span>
                            <span></span>
                        </div>
                        <div class="bill-item-row">
                            <input type="text" class="form-control" name="item_description[]" placeholder="e.g., Website Design & Development" required>
                            <input type="number" class="form-control item-qty" name="item_quantity[]" placeholder="Qty" min="0.01" step="0.01" value="1" required>
                            <input type="number" class="form-control item-price" name="item_price[]" placeholder="Price" min="0" step="0.01" required>
                            <span class="item-total">₹0.00</span>
                            <button type="button" class="btn btn-sm btn-outline-danger remove-item">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <button type="button" class="add-item-btn" onclick="addItemRow('newBillItems')">
                        <i class="fas fa-plus me-2"></i>Add Item
                    </button>

                    <!-- Totals -->
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Notes (Optional)</label>
                                <textarea class="form-control" name="notes" rows="2" placeholder="Additional notes for the client..."></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="totals-section">
                                <div class="total-row">
                                    <span>Subtotal:</span>
                                    <span class="subtotal-display">₹0.00</span>
                                </div>
                                <div class="total-row" id="gstRow">
                                    <span class="d-flex align-items-center gap-2">
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" name="gst_enabled" id="gstToggle" onchange="toggleGST(this)">
                                            <label class="form-check-label" for="gstToggle">GST</label>
                                        </div>
                                        <span class="gst-input-wrapper" style="display: none;">
                                            (<input type="number" name="tax_percent" class="form-control form-control-sm d-inline-block" 
                                                   style="width: 50px;" value="18" min="0" max="100" step="0.01">%):
                                        </span>
                                    </span>
                                    <span class="tax-display">₹0.00</span>
                                </div>
                                <div class="total-row">
                                    <span>
                                        Discount: ₹<input type="number" name="discount_amount" class="form-control form-control-sm d-inline-block" 
                                               style="width: 100px;" value="0" min="0" step="0.01">
                                    </span>
                                    <span></span>
                                </div>
                                <div class="total-row grand">
                                    <span>Total:</span>
                                    <span class="grand-total-display">₹0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Terms & Conditions</label>
                        <textarea class="form-control" name="terms" rows="2"><?php echo htmlspecialchars($defaultTerms); ?></textarea>
                    </div>

                    <!-- Payment Methods Selection -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <h6 class="mb-3"><i class="fas fa-credit-card me-2"></i>Payment Methods to Show on Invoice</h6>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Bank Account</label>
                            <select class="form-select" name="bank_payment_method_id">
                                <option value="">-- No Bank Account --</option>
                                <?php foreach ($bankMethods as $bank): ?>
                                    <option value="<?php echo $bank['id']; ?>" <?php echo $bank['is_default'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($bank['name']); ?> - <?php echo htmlspecialchars($bank['bank_name']); ?>
                                        <?php echo $bank['is_default'] ? '(Default)' : ''; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (empty($bankMethods)): ?>
                                <small class="text-muted">No bank accounts added. <a href="profile.php#bank-accounts" target="_blank">Add one</a></small>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">UPI Payment</label>
                            <select class="form-select" name="upi_payment_method_id">
                                <option value="">-- No UPI --</option>
                                <?php foreach ($upiMethods as $upi): ?>
                                    <option value="<?php echo $upi['id']; ?>" <?php echo $upi['is_default'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($upi['name']); ?> - <?php echo htmlspecialchars($upi['upi_id']); ?>
                                        <?php echo $upi['is_default'] ? '(Default)' : ''; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (empty($upiMethods)): ?>
                                <small class="text-muted">No UPI methods added. <a href="profile.php#upi-methods" target="_blank">Add one</a></small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Bill</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Email Sending Modal -->
<div class="modal fade" id="sendEmailModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Send Bill via Email</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="emailStatus">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-2">Sending email...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Add new item row
function addItemRow(containerId) {
    const container = document.getElementById(containerId);
    const row = document.createElement('div');
    row.className = 'bill-item-row';
    row.innerHTML = `
        <input type="text" class="form-control" name="item_description[]" placeholder="Description" required>
        <input type="number" class="form-control item-qty" name="item_quantity[]" placeholder="Qty" min="0.01" step="0.01" value="1" required>
        <input type="number" class="form-control item-price" name="item_price[]" placeholder="Price" min="0" step="0.01" required>
        <span class="item-total">₹0.00</span>
        <button type="button" class="btn btn-sm btn-outline-danger remove-item">
            <i class="fas fa-times"></i>
        </button>
    `;
    container.appendChild(row);
    attachItemListeners(row);
}

// Calculate totals
function calculateTotals(form) {
    let subtotal = 0;
    const rows = form.querySelectorAll('.bill-item-row:not(.fw-bold)');
    
    rows.forEach(row => {
        const qty = parseFloat(row.querySelector('.item-qty')?.value) || 0;
        const price = parseFloat(row.querySelector('.item-price')?.value) || 0;
        const total = qty * price;
        subtotal += total;
        
        const totalSpan = row.querySelector('.item-total');
        if (totalSpan) {
            totalSpan.textContent = '₹' + total.toFixed(2);
        }
    });
    
    // Check if GST is enabled
    const gstToggle = form.querySelector('input[name="gst_enabled"]');
    const gstEnabled = gstToggle ? gstToggle.checked : false;
    
    let taxPercent = 0;
    if (gstEnabled) {
        taxPercent = parseFloat(form.querySelector('input[name="tax_percent"]')?.value) || 0;
    }
    
    const discountAmount = parseFloat(form.querySelector('input[name="discount_amount"]')?.value) || 0;
    const taxAmount = subtotal * (taxPercent / 100);
    const grandTotal = subtotal + taxAmount - discountAmount;
    
    const container = form.closest('.modal-content') || form;
    container.querySelector('.subtotal-display').textContent = '₹' + subtotal.toFixed(2);
    container.querySelector('.tax-display').textContent = '₹' + taxAmount.toFixed(2);
    container.querySelector('.grand-total-display').textContent = '₹' + grandTotal.toFixed(2);
}

// Toggle GST visibility and recalculate
function toggleGST(checkbox) {
    const form = checkbox.closest('form');
    const gstWrapper = form.querySelector('.gst-input-wrapper');
    if (gstWrapper) {
        gstWrapper.style.display = checkbox.checked ? 'inline' : 'none';
    }
    calculateTotals(form);
}

// Attach listeners to item row
function attachItemListeners(row) {
    const form = row.closest('form');
    
    row.querySelector('.item-qty')?.addEventListener('input', () => calculateTotals(form));
    row.querySelector('.item-price')?.addEventListener('input', () => calculateTotals(form));
    row.querySelector('.remove-item')?.addEventListener('click', function() {
        if (row.parentElement.querySelectorAll('.bill-item-row:not(.fw-bold)').length > 1) {
            row.remove();
            calculateTotals(form);
        }
    });
}

// Initialize all existing rows
document.querySelectorAll('.bill-item-row:not(.fw-bold)').forEach(attachItemListeners);

// Listen for tax and discount changes
document.querySelectorAll('input[name="tax_percent"], input[name="discount_amount"]').forEach(input => {
    input.addEventListener('input', function() {
        calculateTotals(this.closest('form'));
    });
});

// Send email function
function sendEmail(billId) {
    const modal = new bootstrap.Modal(document.getElementById('sendEmailModal'));
    modal.show();
    
    document.getElementById('emailStatus').innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2">Sending email...</p>
        </div>
    `;
    
    fetch('send-bill.php?action=email&id=' + billId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('emailStatus').innerHTML = `
                    <div class="text-center py-4">
                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                        <p class="mb-0">Email sent successfully!</p>
                    </div>
                `;
                setTimeout(() => modal.hide(), 2000);
            } else {
                document.getElementById('emailStatus').innerHTML = `
                    <div class="text-center py-4">
                        <i class="fas fa-times-circle fa-3x text-danger mb-3"></i>
                        <p class="mb-0">Failed to send email: ${data.message}</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            document.getElementById('emailStatus').innerHTML = `
                <div class="text-center py-4">
                    <i class="fas fa-times-circle fa-3x text-danger mb-3"></i>
                    <p class="mb-0">Error: ${error.message}</p>
                </div>
            `;
        });
}

// Toggle bank/upi select visibility based on payment method
function togglePaymentFields(billId) {
    const paymentMethod = document.getElementById('paymentMethod' + billId).value;
    const bankSelectDiv = document.getElementById('bankSelectDiv' + billId);
    const upiSelectDiv = document.getElementById('upiSelectDiv' + billId);
    
    // Hide both first
    bankSelectDiv.style.display = 'none';
    upiSelectDiv.style.display = 'none';
    
    // Show relevant one
    if (paymentMethod === 'bank') {
        bankSelectDiv.style.display = 'block';
    } else if (paymentMethod === 'upi') {
        upiSelectDiv.style.display = 'block';
    }
}

// Show add modal if URL has action=new
<?php if ($showAddModal): ?>
document.addEventListener('DOMContentLoaded', function() {
    new bootstrap.Modal(document.getElementById('addBillModal')).show();
});
<?php endif; ?>
</script>

<?php include 'includes/footer.php'; ?>
