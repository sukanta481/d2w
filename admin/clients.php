<?php
require_once 'includes/auth.php';
$auth->requireLogin();

require_once 'config/database.php';
$database = new Database();
$db = $database->connect();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['add_client'])) {
            $stmt = $db->prepare("INSERT INTO clients (name, email, phone, company, address, gst_number, status, created_by)
                VALUES (:name, :email, :phone, :company, :address, :gst_number, :status, :created_by)");
            $stmt->execute([
                ':name' => $_POST['name'],
                ':email' => !empty($_POST['email']) ? $_POST['email'] : null,
                ':phone' => $_POST['phone'] ?? null,
                ':company' => $_POST['company'] ?? null,
                ':address' => $_POST['address'] ?? null,
                ':gst_number' => $_POST['gst_number'] ?? null,
                ':status' => $_POST['status'] ?? 'active',
                ':created_by' => $auth->getUserId()
            ]);

            $auth->logActivity($auth->getUserId(), 'create', 'clients', $db->lastInsertId(), 'Created client: ' . $_POST['name']);
            $successMessage = "Client added successfully!";
        }

        if (isset($_POST['update_client'])) {
            $stmt = $db->prepare("UPDATE clients SET name = :name, email = :email, phone = :phone, 
                company = :company, address = :address, gst_number = :gst_number, status = :status
                WHERE id = :id");
            $stmt->execute([
                ':id' => $_POST['client_id'],
                ':name' => $_POST['name'],
                ':email' => !empty($_POST['email']) ? $_POST['email'] : null,
                ':phone' => $_POST['phone'] ?? null,
                ':company' => $_POST['company'] ?? null,
                ':address' => $_POST['address'] ?? null,
                ':gst_number' => $_POST['gst_number'] ?? null,
                ':status' => $_POST['status'] ?? 'active'
            ]);

            $auth->logActivity($auth->getUserId(), 'update', 'clients', $_POST['client_id'], 'Updated client: ' . $_POST['name']);
            $successMessage = "Client updated successfully!";
        }

        if (isset($_POST['delete_client'])) {
            // Check if client has bills
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM bills WHERE client_id = :id");
            $stmt->execute([':id' => $_POST['client_id']]);
            $billCount = $stmt->fetch()['count'];

            if ($billCount > 0) {
                $errorMessage = "Cannot delete client with existing bills. Delete the bills first or set client as inactive.";
            } else {
                $stmt = $db->prepare("DELETE FROM clients WHERE id = :id");
                $stmt->execute([':id' => $_POST['client_id']]);

                $auth->logActivity($auth->getUserId(), 'delete', 'clients', $_POST['client_id'], 'Deleted client');
                $successMessage = "Client deleted successfully!";
            }
        }
    } catch(PDOException $e) {
        $errorMessage = "Error: " . $e->getMessage();
    }
}

// Get clients with filters
$statusFilter = $_GET['status'] ?? '';
$searchQuery = $_GET['search'] ?? '';

try {
    $query = "SELECT c.*, 
              (SELECT COUNT(*) FROM bills WHERE client_id = c.id) as bill_count,
              (SELECT COALESCE(SUM(total_amount), 0) FROM bills WHERE client_id = c.id AND payment_status = 'paid') as total_paid
              FROM clients c WHERE 1=1";
    $params = [];

    if ($statusFilter) {
        $query .= " AND c.status = :status";
        $params[':status'] = $statusFilter;
    }

    if ($searchQuery) {
        $query .= " AND (c.name LIKE :search OR c.email LIKE :search OR c.company LIKE :search OR c.phone LIKE :search)";
        $params[':search'] = '%' . $searchQuery . '%';
    }

    $query .= " ORDER BY c.created_at DESC";
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $clients = $stmt->fetchAll();

} catch(PDOException $e) {
    $clients = [];
    error_log("Clients Error: " . $e->getMessage());
}

$pageTitle = 'Clients Management';
include 'includes/header.php';
?>

<div class="admin-content">
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h1 class="page-title">Clients Management</h1>
            <p class="page-subtitle">Manage your client database</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addClientModal">
            <i class="fas fa-plus me-2"></i>Add Client
        </button>
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

    <div class="content-card">
        <!-- Filters -->
        <div class="row mb-4">
            <div class="col-md-5">
                <form method="GET" class="d-flex gap-2">
                    <input type="text" name="search" class="form-control" placeholder="Search clients..."
                           value="<?php echo htmlspecialchars($searchQuery); ?>">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
            <div class="col-md-3">
                <select class="form-select" onchange="window.location.href='?status=' + this.value">
                    <option value="">All Status</option>
                    <option value="active" <?php echo $statusFilter === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo $statusFilter === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>
            <div class="col-md-2">
                <a href="clients.php" class="btn btn-secondary w-100">
                    <i class="fas fa-redo me-2"></i>Reset
                </a>
            </div>
            <div class="col-md-2">
                <a href="billing.php" class="btn btn-outline-primary w-100">
                    <i class="fas fa-file-invoice me-2"></i>Bills
                </a>
            </div>
        </div>

        <!-- Clients Table -->
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Contact</th>
                        <th>Company</th>
                        <th>Bills</th>
                        <th>Total Paid</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($clients)): ?>
                        <?php foreach ($clients as $client): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($client['name']); ?></strong>
                                    <?php if ($client['gst_number']): ?>
                                        <br><small class="text-muted">GST: <?php echo htmlspecialchars($client['gst_number']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($client['email'])): ?>
                                        <a href="mailto:<?php echo htmlspecialchars($client['email']); ?>"><?php echo htmlspecialchars($client['email']); ?></a>
                                    <?php endif; ?>
                                    <?php if ($client['phone']): ?>
                                        <?php if (!empty($client['email'])): ?><br><?php endif; ?>
                                        <a href="tel:<?php echo htmlspecialchars($client['phone']); ?>"><?php echo htmlspecialchars($client['phone']); ?></a>
                                    <?php endif; ?>
                                    <?php if (empty($client['email']) && empty($client['phone'])): ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($client['company'] ?? '-'); ?></td>
                                <td>
                                    <a href="billing.php?client_id=<?php echo $client['id']; ?>" class="text-decoration-none">
                                        <span class="badge bg-info"><?php echo $client['bill_count']; ?> bills</span>
                                    </a>
                                </td>
                                <td>
                                    <strong>₹<?php echo number_format($client['total_paid'], 2); ?></strong>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $client['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                        <?php echo ucfirst($client['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-icon" data-bs-toggle="modal"
                                            data-bs-target="#editClientModal<?php echo $client['id']; ?>"
                                            title="Edit Client">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <a href="billing.php?action=new&client_id=<?php echo $client['id']; ?>" 
                                       class="btn btn-sm btn-icon" title="Create Bill">
                                        <i class="fas fa-file-invoice-dollar"></i>
                                    </a>
                                    <button class="btn btn-sm btn-icon text-danger" data-bs-toggle="modal"
                                            data-bs-target="#deleteClientModal<?php echo $client['id']; ?>"
                                            title="Delete Client">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>

                            <!-- Edit Client Modal -->
                            <div class="modal fade" id="editClientModal<?php echo $client['id']; ?>" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <form method="POST">
                                            <input type="hidden" name="update_client" value="1">
                                            <input type="hidden" name="client_id" value="<?php echo $client['id']; ?>">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit Client</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">Name *</label>
                                                        <input type="text" class="form-control" name="name" required
                                                               value="<?php echo htmlspecialchars($client['name']); ?>">
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">Email</label>
                                                        <input type="email" class="form-control" name="email"
                                                               value="<?php echo htmlspecialchars($client['email'] ?? ''); ?>"
                                                               placeholder="client@example.com (optional)">
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">Phone</label>
                                                        <input type="text" class="form-control" name="phone"
                                                               value="<?php echo htmlspecialchars($client['phone'] ?? ''); ?>">
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">Company</label>
                                                        <input type="text" class="form-control" name="company"
                                                               value="<?php echo htmlspecialchars($client['company'] ?? ''); ?>">
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">GST Number</label>
                                                        <input type="text" class="form-control" name="gst_number"
                                                               value="<?php echo htmlspecialchars($client['gst_number'] ?? ''); ?>">
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">Status</label>
                                                        <select class="form-select" name="status">
                                                            <option value="active" <?php echo $client['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                                            <option value="inactive" <?php echo $client['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Address</label>
                                                    <textarea class="form-control" name="address" rows="2"><?php echo htmlspecialchars($client['address'] ?? ''); ?></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary">Update Client</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Delete Client Modal -->
                            <div class="modal fade" id="deleteClientModal<?php echo $client['id']; ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST">
                                            <input type="hidden" name="delete_client" value="1">
                                            <input type="hidden" name="client_id" value="<?php echo $client['id']; ?>">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Delete Client</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Are you sure you want to delete <strong><?php echo htmlspecialchars($client['name']); ?></strong>?</p>
                                                <?php if ($client['bill_count'] > 0): ?>
                                                    <div class="alert alert-warning">
                                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                                        This client has <?php echo $client['bill_count']; ?> bill(s). You must delete or reassign them first.
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-danger" <?php echo $client['bill_count'] > 0 ? 'disabled' : ''; ?>>Delete</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">No clients found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Client Modal -->
<div class="modal fade" id="addClientModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="add_client" value="1">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Client</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Name *</label>
                            <input type="text" class="form-control" name="name" required placeholder="Client name">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" placeholder="client@example.com (optional)">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" class="form-control" name="phone" placeholder="+91 XXXXX XXXXX">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Company</label>
                            <input type="text" class="form-control" name="company" placeholder="Company name">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">GST Number</label>
                            <input type="text" class="form-control" name="gst_number" placeholder="GST number (optional)">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea class="form-control" name="address" rows="2" placeholder="Client address"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Client</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
