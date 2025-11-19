<?php
require_once 'includes/auth.php';
$auth->requireLogin();
require_once 'config/database.php';
$database = new Database();
$db = $database->connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['add_service'])) {
            $stmt = $db->prepare("INSERT INTO services (title, short_description, full_description, icon, features, pricing_info, display_order, status, created_by)
                                  VALUES (:title, :short_description, :full_description, :icon, :features, :pricing_info, :display_order, :status, :created_by)");
            $stmt->execute([':title' => $_POST['title'], ':short_description' => $_POST['short_description'], ':full_description' => $_POST['full_description'] ?? null,
                ':icon' => $_POST['icon'], ':features' => $_POST['features'] ?? null, ':pricing_info' => $_POST['pricing_info'] ?? null,
                ':display_order' => $_POST['display_order'] ?? 0, ':status' => $_POST['status'], ':created_by' => $auth->getUserId()]);
            $successMessage = "Service added successfully!";
        }
        if (isset($_POST['update_service'])) {
            $stmt = $db->prepare("UPDATE services SET title = :title, short_description = :short_description, full_description = :full_description,
                                  icon = :icon, features = :features, pricing_info = :pricing_info, display_order = :display_order, status = :status WHERE id = :id");
            $stmt->execute([':title' => $_POST['title'], ':short_description' => $_POST['short_description'], ':full_description' => $_POST['full_description'] ?? null,
                ':icon' => $_POST['icon'], ':features' => $_POST['features'] ?? null, ':pricing_info' => $_POST['pricing_info'] ?? null,
                ':display_order' => $_POST['display_order'] ?? 0, ':status' => $_POST['status'], ':id' => $_POST['service_id']]);
            $successMessage = "Service updated successfully!";
        }
        if (isset($_POST['delete_service'])) {
            $stmt = $db->prepare("DELETE FROM services WHERE id = :id");
            $stmt->execute([':id' => $_POST['service_id']]);
            $successMessage = "Service deleted successfully!";
        }
    } catch(PDOException $e) { $errorMessage = "Error: " . $e->getMessage(); }
}

$stmt = $db->query("SELECT * FROM services ORDER BY display_order ASC, id ASC");
$services = $stmt->fetchAll();
$pageTitle = 'Services Management';
include 'includes/header.php';
?>

<div class="admin-content">
    <div class="page-header d-flex justify-content-between align-items-center">
        <div><h1 class="page-title">Services Management</h1><p class="page-subtitle">Manage your service offerings</p></div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addServiceModal"><i class="fas fa-plus me-2"></i>Add New Service</button>
    </div>

    <?php if (isset($successMessage)): ?>
        <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i><?php echo $successMessage; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <div class="content-card">
        <div class="table-responsive">
            <table class="data-table">
                <thead><tr><th>Order</th><th>Icon</th><th>Title</th><th>Description</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach ($services as $service): ?>
                        <tr>
                            <td><?php echo $service['display_order']; ?></td>
                            <td><i class="<?php echo htmlspecialchars($service['icon']); ?> fa-2x text-primary"></i></td>
                            <td><strong><?php echo htmlspecialchars($service['title']); ?></strong></td>
                            <td><?php echo htmlspecialchars(substr($service['short_description'], 0, 60)); ?>...</td>
                            <td><span class="badge bg-<?php echo $service['status'] === 'active' ? 'success' : 'secondary'; ?>"><?php echo ucfirst($service['status']); ?></span></td>
                            <td>
                                <button class="btn btn-sm btn-icon" data-bs-toggle="modal" data-bs-target="#editServiceModal<?php echo $service['id']; ?>"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-sm btn-icon text-danger" data-bs-toggle="modal" data-bs-target="#deleteServiceModal<?php echo $service['id']; ?>"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>

                        <!-- Edit Modal -->
                        <div class="modal fade" id="editServiceModal<?php echo $service['id']; ?>" tabindex="-1">
                            <div class="modal-dialog modal-lg"><div class="modal-content"><form method="POST">
                                <div class="modal-header"><h5 class="modal-title">Edit Service</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                <div class="modal-body">
                                    <input type="hidden" name="service_id" value="<?php echo $service['id']; ?>">
                                    <div class="row">
                                        <div class="col-md-8 mb-3"><label class="form-label">Title *</label><input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($service['title']); ?>" required></div>
                                        <div class="col-md-4 mb-3"><label class="form-label">Display Order</label><input type="number" name="display_order" class="form-control" value="<?php echo $service['display_order']; ?>"></div>
                                        <div class="col-md-12 mb-3"><label class="form-label">Short Description *</label><textarea name="short_description" class="form-control" rows="2" required><?php echo htmlspecialchars($service['short_description']); ?></textarea></div>
                                        <div class="col-md-12 mb-3"><label class="form-label">Full Description</label><textarea name="full_description" class="form-control" rows="4"><?php echo htmlspecialchars($service['full_description'] ?? ''); ?></textarea></div>
                                        <div class="col-md-6 mb-3"><label class="form-label">Icon Class *</label><input type="text" name="icon" class="form-control" value="<?php echo htmlspecialchars($service['icon']); ?>" required></div>
                                        <div class="col-md-6 mb-3"><label class="form-label">Status *</label><select name="status" class="form-select" required><option value="active" <?php echo $service['status'] === 'active' ? 'selected' : ''; ?>>Active</option><option value="inactive" <?php echo $service['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option></select></div>
                                        <div class="col-md-12 mb-3"><label class="form-label">Features (one per line)</label><textarea name="features" class="form-control" rows="3"><?php echo htmlspecialchars($service['features'] ?? ''); ?></textarea></div>
                                        <div class="col-md-12 mb-3"><label class="form-label">Pricing Info</label><textarea name="pricing_info" class="form-control" rows="2"><?php echo htmlspecialchars($service['pricing_info'] ?? ''); ?></textarea></div>
                                    </div>
                                </div>
                                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" name="update_service" class="btn btn-primary">Update Service</button></div>
                            </form></div></div>
                        </div>

                        <!-- Delete Modal -->
                        <div class="modal fade" id="deleteServiceModal<?php echo $service['id']; ?>" tabindex="-1">
                            <div class="modal-dialog"><div class="modal-content"><form method="POST">
                                <div class="modal-header"><h5 class="modal-title">Delete Service</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                <div class="modal-body">
                                    <input type="hidden" name="service_id" value="<?php echo $service['id']; ?>">
                                    <p>Delete <strong><?php echo htmlspecialchars($service['title']); ?></strong>?</p>
                                </div>
                                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" name="delete_service" class="btn btn-danger">Delete</button></div>
                            </form></div></div>
                        </div>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Service Modal -->
<div class="modal fade" id="addServiceModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content"><form method="POST">
    <div class="modal-header"><h5 class="modal-title">Add New Service</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body"><div class="row">
        <div class="col-md-8 mb-3"><label class="form-label">Title *</label><input type="text" name="title" class="form-control" required></div>
        <div class="col-md-4 mb-3"><label class="form-label">Display Order</label><input type="number" name="display_order" class="form-control" value="0"></div>
        <div class="col-md-12 mb-3"><label class="form-label">Short Description *</label><textarea name="short_description" class="form-control" rows="2" required></textarea></div>
        <div class="col-md-12 mb-3"><label class="form-label">Full Description</label><textarea name="full_description" class="form-control" rows="4"></textarea></div>
        <div class="col-md-6 mb-3"><label class="form-label">Icon Class *</label><input type="text" name="icon" class="form-control" value="fas fa-cog" required></div>
        <div class="col-md-6 mb-3"><label class="form-label">Status *</label><select name="status" class="form-select" required><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
        <div class="col-md-12 mb-3"><label class="form-label">Features (one per line)</label><textarea name="features" class="form-control" rows="3"></textarea></div>
        <div class="col-md-12 mb-3"><label class="form-label">Pricing Info</label><textarea name="pricing_info" class="form-control" rows="2"></textarea></div>
    </div></div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" name="add_service" class="btn btn-primary">Add Service</button></div>
</form></div></div></div>

<?php include 'includes/footer.php'; ?>
