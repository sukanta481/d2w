<?php
require_once 'includes/auth.php';
$auth->requireLogin();
require_once __DIR__ . '/../includes/csrf.php';

require_once 'config/database.php';
$database = new Database();
$db = $database->connect();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error'] = 'Invalid form submission. Please refresh and try again.';
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    }
    try {
        if (isset($_POST['add_expense'])) {
            $stmt = $db->prepare("INSERT INTO expenses (category, type, title, description, amount, expense_date, created_by)
                VALUES (:category, :type, :title, :description, :amount, :expense_date, :created_by)");
            $stmt->execute([
                ':category' => $_POST['category'],
                ':type' => $_POST['type'],
                ':title' => $_POST['title'],
                ':description' => $_POST['description'] ?: null,
                ':amount' => floatval($_POST['amount']),
                ':expense_date' => $_POST['expense_date'],
                ':created_by' => $auth->getUserId()
            ]);
            $auth->logActivity($auth->getUserId(), 'create', 'expenses', $db->lastInsertId(), 'Added ' . $_POST['type'] . ': ' . $_POST['title']);
            $successMessage = ucfirst($_POST['type']) . " added successfully!";
        }

        if (isset($_POST['update_expense'])) {
            $stmt = $db->prepare("UPDATE expenses SET category = :category, type = :type, title = :title, description = :description,
                amount = :amount, expense_date = :expense_date WHERE id = :id");
            $stmt->execute([
                ':id' => $_POST['expense_id'],
                ':category' => $_POST['category'],
                ':type' => $_POST['type'],
                ':title' => $_POST['title'],
                ':description' => $_POST['description'] ?: null,
                ':amount' => floatval($_POST['amount']),
                ':expense_date' => $_POST['expense_date']
            ]);
            $auth->logActivity($auth->getUserId(), 'update', 'expenses', $_POST['expense_id'], 'Updated record');
            $successMessage = "Record updated successfully!";
        }

        if (isset($_POST['delete_expense'])) {
            $stmt = $db->prepare("DELETE FROM expenses WHERE id = :id");
            $stmt->execute([':id' => $_POST['expense_id']]);
            $auth->logActivity($auth->getUserId(), 'delete', 'expenses', $_POST['expense_id'], 'Deleted record');
            $successMessage = "Record deleted successfully!";
        }

    } catch(PDOException $e) {
        $errorMessage = "Error: " . $e->getMessage();
    }
}

// Filters
$categoryFilter = $_GET['category'] ?? '';
$typeFilter = $_GET['type'] ?? '';
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';
$searchQuery = $_GET['search'] ?? '';

// Fetch records
try {
    $query = "SELECT e.*, au.full_name as created_by_name FROM expenses e
              LEFT JOIN admin_users au ON e.created_by = au.id WHERE 1=1";
    $params = [];

    if ($categoryFilter) {
        $query .= " AND e.category = :category";
        $params[':category'] = $categoryFilter;
    }
    if ($typeFilter) {
        $query .= " AND e.type = :type";
        $params[':type'] = $typeFilter;
    }
    if ($dateFrom) {
        $query .= " AND e.expense_date >= :date_from";
        $params[':date_from'] = $dateFrom;
    }
    if ($dateTo) {
        $query .= " AND e.expense_date <= :date_to";
        $params[':date_to'] = $dateTo;
    }
    if ($searchQuery) {
        $query .= " AND (e.title LIKE :search OR e.description LIKE :search)";
        $params[':search'] = '%' . $searchQuery . '%';
    }

    $query .= " ORDER BY e.expense_date DESC, e.id DESC";
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $expenses = $stmt->fetchAll();

    // Summary stats
    $totalIncome = 0;
    $totalExpenseAmt = 0;
    $biznexaTotal = 0;
    $inspectionTotal = 0;
    $generalIncome = 0;
    $generalExpense = 0;

    foreach ($expenses as $exp) {
        if ($exp['type'] === 'income') {
            $totalIncome += $exp['amount'];
            if ($exp['category'] === 'general') $generalIncome += $exp['amount'];
        } else {
            $totalExpenseAmt += $exp['amount'];
            if ($exp['category'] === 'biznexa') $biznexaTotal += $exp['amount'];
            elseif ($exp['category'] === 'inspection') $inspectionTotal += $exp['amount'];
            elseif ($exp['category'] === 'general') $generalExpense += $exp['amount'];
        }
    }

} catch(PDOException $e) {
    $expenses = [];
    $totalIncome = $totalExpenseAmt = $biznexaTotal = $inspectionTotal = $generalIncome = $generalExpense = 0;
    error_log("Expenses Error: " . $e->getMessage());
}

$pageTitle = 'Expenses & Income';
include 'includes/header.php';
?>

<style>
.expense-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}
.expense-stat-card {
    background: white;
    border-radius: 12px;
    padding: 1.25rem;
    border: 1px solid #e9ecef;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}
.expense-stat-card.total-expense { border-left: 4px solid #dc3545; }
.expense-stat-card.total-income { border-left: 4px solid #10b981; }
.expense-stat-card.biznexa { border-left: 4px solid #0d6efd; }
.expense-stat-card.inspection { border-left: 4px solid #ffc107; }
.expense-stat-card.general { border-left: 4px solid #8b5cf6; }
.expense-stat-card .stat-label { font-size: 0.85rem; color: #6c757d; margin-bottom: 0.25rem; }
.expense-stat-card .stat-value { font-size: 1.5rem; font-weight: 700; color: #2C3E50; }
.expense-stat-card .stat-value.income-val { color: #10b981; }
.expense-stat-card .stat-value.expense-val { color: #dc3545; }

.filter-section {
    background: white;
    border-radius: 12px;
    padding: 1.25rem;
    margin-bottom: 1.5rem;
    border: 1px solid #e9ecef;
}
.filter-row { display: flex; flex-wrap: wrap; gap: 1rem; align-items: flex-end; }
.filter-group { flex: 1; min-width: 130px; }
.filter-group label { font-size: 0.85rem; color: #6c757d; margin-bottom: 0.25rem; display: block; }
.filter-actions { display: flex; gap: 0.5rem; flex-wrap: wrap; }

.category-badge-biznexa { background: #0d6efd; color: #fff; }
.category-badge-inspection { background: #ffc107; color: #212529; }
.category-badge-general { background: #8b5cf6; color: #fff; }
.type-badge-income { background: #10b981; color: #fff; }
.type-badge-expense { background: #dc3545; color: #fff; }

/* Tablet */
@media (max-width: 768px) {
    .filter-group { min-width: 100%; }
    .expense-stats { grid-template-columns: repeat(2, 1fr); }
    .expense-stat-card .stat-value { font-size: 1.2rem; }
    .expense-stat-card { padding: 1rem; }
    .page-title { font-size: 1.5rem !important; }
    .page-subtitle { font-size: .85rem !important; }
    .data-table { font-size: .8rem; }
    .data-table th, .data-table td { padding: 8px 6px !important; white-space: nowrap; }
    .content-card { padding: 12px !important; }
}
/* Mobile */
@media (max-width: 576px) {
    .expense-stats { grid-template-columns: 1fr; }
    .expense-stat-card .stat-value { font-size: 1.3rem; }
    .filter-section { padding: 1rem; }
    .filter-row { gap: .75rem; }
    .page-header { flex-direction: column !important; align-items: flex-start !important; gap: 10px; }
    .btn { font-size: .85rem; }
}
/* Small Mobile */
@media (max-width: 480px) {
    .page-title { font-size: 1.3rem !important; }
    .data-table { font-size: .75rem; }
    .data-table th, .data-table td { padding: 6px 4px !important; }
    .btn-sm { font-size: .75rem; padding: 4px 8px; }
    .modal-dialog { margin: 10px; }
}
</style>

<div class="admin-content">
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h1 class="page-title">Expenses & Income</h1>
            <p class="page-subtitle">Track all income and expenses — BizNexa, Inspection & General</p>
        </div>
        <div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
                <i class="fas fa-plus me-2"></i>Add Record
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

    <!-- Stats -->
    <div class="expense-stats">
        <div class="expense-stat-card total-income">
            <div class="stat-label">Total Income</div>
            <div class="stat-value income-val">₹<?php echo number_format($totalIncome, 2); ?></div>
        </div>
        <div class="expense-stat-card total-expense">
            <div class="stat-label">Total Expenses</div>
            <div class="stat-value expense-val">₹<?php echo number_format($totalExpenseAmt, 2); ?></div>
        </div>
        <div class="expense-stat-card biznexa">
            <div class="stat-label">BizNexa Expenses</div>
            <div class="stat-value">₹<?php echo number_format($biznexaTotal, 2); ?></div>
        </div>
        <div class="expense-stat-card inspection">
            <div class="stat-label">Inspection Expenses</div>
            <div class="stat-value">₹<?php echo number_format($inspectionTotal, 2); ?></div>
        </div>
        <div class="expense-stat-card general">
            <div class="stat-label">General (Inc / Exp)</div>
            <div class="stat-value"><span class="income-val" style="font-size:1.1rem;">₹<?php echo number_format($generalIncome, 0); ?></span> / <span class="expense-val" style="font-size:1.1rem;">₹<?php echo number_format($generalExpense, 0); ?></span></div>
        </div>
    </div>

    <!-- Filters -->
    <div class="filter-section">
        <form method="GET">
            <div class="filter-row">
                <div class="filter-group" style="flex: 2;">
                    <label>Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Title..."
                           value="<?php echo htmlspecialchars($searchQuery); ?>">
                </div>
                <div class="filter-group">
                    <label>Category</label>
                    <select name="category" class="form-select">
                        <option value="">All</option>
                        <option value="biznexa" <?php echo $categoryFilter === 'biznexa' ? 'selected' : ''; ?>>BizNexa</option>
                        <option value="inspection" <?php echo $categoryFilter === 'inspection' ? 'selected' : ''; ?>>Inspection</option>
                        <option value="general" <?php echo $categoryFilter === 'general' ? 'selected' : ''; ?>>General</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Type</label>
                    <select name="type" class="form-select">
                        <option value="">All</option>
                        <option value="income" <?php echo $typeFilter === 'income' ? 'selected' : ''; ?>>Income</option>
                        <option value="expense" <?php echo $typeFilter === 'expense' ? 'selected' : ''; ?>>Expense</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Date From</label>
                    <input type="date" name="date_from" class="form-control" value="<?php echo $dateFrom; ?>">
                </div>
                <div class="filter-group">
                    <label>Date To</label>
                    <input type="date" name="date_to" class="form-control" value="<?php echo $dateTo; ?>">
                </div>
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-filter me-1"></i>Filter</button>
                    <a href="expenses.php" class="btn btn-secondary"><i class="fas fa-redo me-1"></i>Reset</a>
                </div>
            </div>
        </form>
    </div>

    <!-- Records Table -->
    <div class="content-card">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Category</th>
                        <th>Amount</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($expenses)): ?>
                        <?php foreach ($expenses as $expense): ?>
                            <tr>
                                <td><?php echo date('M d, Y', strtotime($expense['expense_date'])); ?></td>
                                <td><strong><?php echo htmlspecialchars($expense['title']); ?></strong></td>
                                <td>
                                    <span class="badge type-badge-<?php echo $expense['type']; ?>">
                                        <?php echo ucfirst($expense['type']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge category-badge-<?php echo $expense['category']; ?>">
                                        <?php
                                            $catLabels = ['biznexa' => 'BizNexa', 'inspection' => 'Inspection', 'general' => 'General'];
                                            echo $catLabels[$expense['category']] ?? $expense['category'];
                                        ?>
                                    </span>
                                </td>
                                <td>
                                    <strong class="<?php echo $expense['type'] === 'income' ? 'text-success' : 'text-danger'; ?>">
                                        <?php echo $expense['type'] === 'income' ? '+' : '-'; ?>₹<?php echo number_format($expense['amount'], 2); ?>
                                    </strong>
                                </td>
                                <td><small class="text-muted"><?php echo htmlspecialchars($expense['description'] ?? '-'); ?></small></td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                                data-bs-target="#editExpenseModal<?php echo $expense['id']; ?>" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal"
                                                data-bs-target="#deleteExpenseModal<?php echo $expense['id']; ?>" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Edit Modal -->
                            <div class="modal fade" id="editExpenseModal<?php echo $expense['id']; ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST"><?php echo csrfField(); ?>
                                            <input type="hidden" name="update_expense" value="1">
                                            <input type="hidden" name="expense_id" value="<?php echo $expense['id']; ?>">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit Record</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">Type *</label>
                                                        <select class="form-select" name="type" required>
                                                            <option value="expense" <?php echo $expense['type'] === 'expense' ? 'selected' : ''; ?>>Expense</option>
                                                            <option value="income" <?php echo $expense['type'] === 'income' ? 'selected' : ''; ?>>Income</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">Category *</label>
                                                        <select class="form-select" name="category" required>
                                                            <option value="biznexa" <?php echo $expense['category'] === 'biznexa' ? 'selected' : ''; ?>>BizNexa</option>
                                                            <option value="inspection" <?php echo $expense['category'] === 'inspection' ? 'selected' : ''; ?>>Inspection</option>
                                                            <option value="general" <?php echo $expense['category'] === 'general' ? 'selected' : ''; ?>>General</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Title *</label>
                                                    <input type="text" class="form-control" name="title" required
                                                           value="<?php echo htmlspecialchars($expense['title']); ?>">
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">Amount (₹) *</label>
                                                        <input type="number" class="form-control" name="amount" step="0.01" min="0" required
                                                               value="<?php echo $expense['amount']; ?>">
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">Date *</label>
                                                        <input type="date" class="form-control" name="expense_date" required
                                                               value="<?php echo $expense['expense_date']; ?>">
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Description</label>
                                                    <textarea class="form-control" name="description" rows="2"><?php echo htmlspecialchars($expense['description'] ?? ''); ?></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary">Update</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Delete Modal -->
                            <div class="modal fade" id="deleteExpenseModal<?php echo $expense['id']; ?>" tabindex="-1">
                                <div class="modal-dialog modal-sm">
                                    <div class="modal-content">
                                        <form method="POST"><?php echo csrfField(); ?>
                                            <input type="hidden" name="delete_expense" value="1">
                                            <input type="hidden" name="expense_id" value="<?php echo $expense['id']; ?>">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Delete Record</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Are you sure you want to delete <strong><?php echo htmlspecialchars($expense['title']); ?></strong>?</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-danger">Delete</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">No records yet</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Record Modal -->
<div class="modal fade" id="addExpenseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST"><?php echo csrfField(); ?>
                <input type="hidden" name="add_expense" value="1">
                <div class="modal-header">
                    <h5 class="modal-title">Add Record</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Type *</label>
                            <select class="form-select" name="type" required>
                                <option value="expense">Expense</option>
                                <option value="income">Income</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Category *</label>
                            <select class="form-select" name="category" required>
                                <option value="">Select Category</option>
                                <option value="biznexa">BizNexa</option>
                                <option value="inspection">Inspection</option>
                                <option value="general">General</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Title *</label>
                        <input type="text" class="form-control" name="title" required placeholder="e.g. Freelance Payment, Fuel Cost">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Amount (₹) *</label>
                            <input type="number" class="form-control" name="amount" step="0.01" min="0" required placeholder="0.00">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date *</label>
                            <input type="date" class="form-control" name="expense_date" required value="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="2" placeholder="Optional details..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Record</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
