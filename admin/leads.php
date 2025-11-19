<?php
require_once 'includes/auth.php';
$auth->requireLogin();

require_once 'config/database.php';
$database = new Database();
$db = $database->connect();

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    try {
        $leadId = $_POST['lead_id'];
        $newStatus = $_POST['status'];
        $notes = $_POST['notes'] ?? '';

        $stmt = $db->prepare("UPDATE leads SET status = :status, notes = :notes WHERE id = :id");
        $stmt->bindParam(':status', $newStatus);
        $stmt->bindParam(':notes', $notes);
        $stmt->bindParam(':id', $leadId);
        $stmt->execute();

        $auth->logActivity($auth->getUserId(), 'update', 'leads', $leadId, "Updated lead status to {$newStatus}");

        $successMessage = "Lead status updated successfully!";
    } catch(PDOException $e) {
        $errorMessage = "Error updating lead: " . $e->getMessage();
    }
}

// Get leads with filters
$statusFilter = $_GET['status'] ?? '';
$priorityFilter = $_GET['priority'] ?? '';
$searchQuery = $_GET['search'] ?? '';

try {
    $query = "SELECT * FROM leads WHERE 1=1";
    $params = [];

    if ($statusFilter) {
        $query .= " AND status = :status";
        $params[':status'] = $statusFilter;
    }

    if ($priorityFilter) {
        $query .= " AND priority = :priority";
        $params[':priority'] = $priorityFilter;
    }

    if ($searchQuery) {
        $query .= " AND (name LIKE :search OR email LIKE :search OR company LIKE :search)";
        $params[':search'] = "%{$searchQuery}%";
    }

    $query .= " ORDER BY created_at DESC";

    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $leads = $stmt->fetchAll();

} catch(PDOException $e) {
    $leads = [];
    error_log("Leads Error: " . $e->getMessage());
}

$pageTitle = 'Leads Management';
include 'includes/header.php';
?>

<div class="admin-content">
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h1 class="page-title">Leads Management</h1>
            <p class="page-subtitle">Manage and track your customer inquiries</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addLeadModal">
            <i class="fas fa-plus me-2"></i>Add New Lead
        </button>
    </div>

    <?php if (isset($successMessage)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?php echo $successMessage; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($errorMessage)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?php echo $errorMessage; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="content-card">
        <!-- Filters -->
        <div class="row mb-4">
            <div class="col-md-4">
                <form method="GET" class="d-flex gap-2">
                    <input type="text" name="search" class="form-control" placeholder="Search leads..."
                           value="<?php echo htmlspecialchars($searchQuery); ?>">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
            <div class="col-md-3">
                <select class="form-select" onchange="window.location.href='?status=' + this.value">
                    <option value="">All Status</option>
                    <option value="new" <?php echo $statusFilter === 'new' ? 'selected' : ''; ?>>New</option>
                    <option value="contacted" <?php echo $statusFilter === 'contacted' ? 'selected' : ''; ?>>Contacted</option>
                    <option value="qualified" <?php echo $statusFilter === 'qualified' ? 'selected' : ''; ?>>Qualified</option>
                    <option value="proposal_sent" <?php echo $statusFilter === 'proposal_sent' ? 'selected' : ''; ?>>Proposal Sent</option>
                    <option value="won" <?php echo $statusFilter === 'won' ? 'selected' : ''; ?>>Won</option>
                    <option value="lost" <?php echo $statusFilter === 'lost' ? 'selected' : ''; ?>>Lost</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" onchange="window.location.href='?priority=' + this.value">
                    <option value="">All Priority</option>
                    <option value="low" <?php echo $priorityFilter === 'low' ? 'selected' : ''; ?>>Low</option>
                    <option value="medium" <?php echo $priorityFilter === 'medium' ? 'selected' : ''; ?>>Medium</option>
                    <option value="high" <?php echo $priorityFilter === 'high' ? 'selected' : ''; ?>>High</option>
                </select>
            </div>
            <div class="col-md-2">
                <a href="leads.php" class="btn btn-secondary w-100">
                    <i class="fas fa-redo me-2"></i>Reset
                </a>
            </div>
        </div>

        <!-- Leads Table -->
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email / Phone</th>
                        <th>Company</th>
                        <th>Service</th>
                        <th>Status</th>
                        <th>Priority</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($leads)): ?>
                        <?php foreach ($leads as $lead): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($lead['name']); ?></strong>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($lead['email']); ?>
                                    <?php if ($lead['phone']): ?>
                                        <br><small class="text-muted">
                                            <i class="fas fa-phone"></i> <?php echo htmlspecialchars($lead['phone']); ?>
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($lead['company'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($lead['service_type'] ?? 'General'); ?></td>
                                <td>
                                    <span class="badge bg-<?php
                                        echo $lead['status'] === 'new' ? 'primary' :
                                             ($lead['status'] === 'contacted' ? 'info' :
                                             ($lead['status'] === 'qualified' ? 'warning' :
                                             ($lead['status'] === 'won' ? 'success' : 'secondary')));
                                    ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $lead['status'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?php
                                        echo $lead['priority'] === 'high' ? 'danger' :
                                             ($lead['priority'] === 'medium' ? 'warning' : 'secondary');
                                    ?>">
                                        <?php echo ucfirst($lead['priority']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($lead['created_at'])); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-icon" data-bs-toggle="modal"
                                            data-bs-target="#viewLeadModal<?php echo $lead['id']; ?>"
                                            title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-icon" data-bs-toggle="modal"
                                            data-bs-target="#updateStatusModal<?php echo $lead['id']; ?>"
                                            title="Update Status">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </td>
                            </tr>

                            <!-- View Lead Modal -->
                            <div class="modal fade" id="viewLeadModal<?php echo $lead['id']; ?>" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Lead Details</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <strong>Name:</strong> <?php echo htmlspecialchars($lead['name']); ?>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <strong>Email:</strong> <?php echo htmlspecialchars($lead['email']); ?>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <strong>Phone:</strong> <?php echo htmlspecialchars($lead['phone'] ?? 'N/A'); ?>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <strong>Company:</strong> <?php echo htmlspecialchars($lead['company'] ?? 'N/A'); ?>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <strong>Service Type:</strong> <?php echo htmlspecialchars($lead['service_type'] ?? 'N/A'); ?>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <strong>Budget Range:</strong> <?php echo htmlspecialchars($lead['budget_range'] ?? 'N/A'); ?>
                                                </div>
                                                <div class="col-12 mb-3">
                                                    <strong>Message:</strong>
                                                    <p><?php echo nl2br(htmlspecialchars($lead['message'])); ?></p>
                                                </div>
                                                <?php if ($lead['notes']): ?>
                                                    <div class="col-12 mb-3">
                                                        <strong>Notes:</strong>
                                                        <p><?php echo nl2br(htmlspecialchars($lead['notes'])); ?></p>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Update Status Modal -->
                            <div class="modal fade" id="updateStatusModal<?php echo $lead['id']; ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Update Lead Status</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <input type="hidden" name="lead_id" value="<?php echo $lead['id']; ?>">

                                                <div class="mb-3">
                                                    <label class="form-label">Status</label>
                                                    <select name="status" class="form-select" required>
                                                        <option value="new" <?php echo $lead['status'] === 'new' ? 'selected' : ''; ?>>New</option>
                                                        <option value="contacted" <?php echo $lead['status'] === 'contacted' ? 'selected' : ''; ?>>Contacted</option>
                                                        <option value="qualified" <?php echo $lead['status'] === 'qualified' ? 'selected' : ''; ?>>Qualified</option>
                                                        <option value="proposal_sent" <?php echo $lead['status'] === 'proposal_sent' ? 'selected' : ''; ?>>Proposal Sent</option>
                                                        <option value="won" <?php echo $lead['status'] === 'won' ? 'selected' : ''; ?>>Won</option>
                                                        <option value="lost" <?php echo $lead['status'] === 'lost' ? 'selected' : ''; ?>>Lost</option>
                                                    </select>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Notes</label>
                                                    <textarea name="notes" class="form-control" rows="4"
                                                              placeholder="Add notes about this lead..."><?php echo htmlspecialchars($lead['notes'] ?? ''); ?></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                No leads found
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
